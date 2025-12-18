<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Facades\Rag;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

final class RagImportPdfCommand extends Command
{
    protected $signature = 'rag:import:pdf'
        .' {path}'
        .' {--title=}'
        .' {--source_type=pdf}'
        .' {--source_ref=}'
        .' {--lang=en}'
        .' {--sync}'
        .' {--dry-run}';

    protected $description = 'Import PDF documents into the RAG knowledge base';

    public function handle(Filesystem $files): int
    {
        $path = (string) $this->argument('path');
        if (! $files->exists($path)) {
            warning('Path not found: '.$path);

            return self::INVALID;
        }

        $isDir = $files->isDirectory($path);
        $pdfFiles = $isDir ? collect($files->files($path))->filter(fn ($f): bool => mb_strtolower($files->extension($f->getPathname())) === 'pdf')->map->getPathname()->values()->all() : [$path];

        if ($pdfFiles === []) {
            warning('No PDF files found.');

            return self::INVALID;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sync = (bool) $this->option('sync');

        foreach ($pdfFiles as $pdf) {
            $title = (string) ($this->option('title') ?? $files->name($pdf));
            $sourceType = (string) ($this->option('source_type') ?? 'pdf');
            $sourceRef = (string) ($this->option('source_ref') ?? $pdf);

            // Minimal PDF text extraction using built-in stream filter (naive)
            $raw = $files->get($pdf);
            $textContent = $this->extractText($raw);

            if ($dryRun) {
                $this->components->twoColumnDetail('PDF', $pdf);
                $this->components->twoColumnDetail('Bytes', (string) mb_strlen($raw));
                $this->components->twoColumnDetail('Extracted (approx chars)', (string) mb_strlen($textContent));

                continue;
            }

            Rag::ingest([
                'title' => $title,
                'source_type' => $sourceType,
                'source_ref' => $sourceRef,
                'content' => $textContent,
                'meta' => ['lang' => (string) $this->option('lang')],
            ]);
        }

        if (! $dryRun) {
            $this->components->twoColumnDetail('Mode', $sync ? 'sync' : 'queue');
        }

        return self::SUCCESS;
    }

    private function extractText(string $raw): string
    {
        // Very naive PDF text extraction: find text showing operators (Tj/TJ) tokens
        // This is placeholder-level but PHP-native and deterministic.
        $text = '';
        if (preg_match_all('/\((.*?)\)\s*Tj/s', $raw, $m)) {
            $text .= implode("\n", array_map(strip_tags(...), $m[1]));
        }
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $raw, $m2)) {
            foreach ($m2[1] as $group) {
                if (preg_match_all('/\((.*?)\)/s', $group, $m3)) {
                    $text .= "\n".implode('', $m3[1]);
                }
            }
        }

        return mb_trim($text) !== '' ? $text : 'PDF content';
    }
}
