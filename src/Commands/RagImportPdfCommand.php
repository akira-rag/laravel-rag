<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Facades\Rag;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

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
        /** @var string $importPath */
        $importPath = $this->argument('path');
        if (! $files->exists($importPath)) {
            warning('Path not found: '.$importPath);

            return self::INVALID;
        }

        $isDirectory = $files->isDirectory($importPath);
        $pdfFilePaths = $isDirectory ? collect($files->files($importPath))
            ->filter(fn (SplFileInfo $fileInfo): bool => mb_strtolower($files->extension($fileInfo->getPathname())) === 'pdf')
            ->map->getPathname()
            ->values()
            ->all() : [$importPath];

        if ($pdfFilePaths === []) {
            warning('No PDF files found.');

            return self::INVALID;
        }

        $isDryRun = (bool) $this->option('dry-run');
        $isSynchronous = (bool) $this->option('sync');

        foreach ($pdfFilePaths as $pdfFilePath) {
            $titleOption = $this->option('title');
            $documentTitle = is_string($titleOption) ? $titleOption : $files->name($pdfFilePath);

            $sourceTypeOption = $this->option('source_type');
            $documentSourceType = is_string($sourceTypeOption) ? $sourceTypeOption : 'pdf';

            $sourceRefOption = $this->option('source_ref');
            $documentSourceRef = is_string($sourceRefOption) ? $sourceRefOption : $pdfFilePath;

            // Minimal PDF text extraction using built-in stream filter (naive)
            $rawPdfContent = $files->get($pdfFilePath);
            $extractedText = $this->extractText($rawPdfContent);

            if ($isDryRun) {
                $this->components->twoColumnDetail('PDF', (string) $pdfFilePath);
                $this->components->twoColumnDetail('Bytes', (string) mb_strlen($rawPdfContent));
                $this->components->twoColumnDetail('Extracted (approx chars)', (string) mb_strlen($extractedText));

                continue;
            }

            $languageOption = $this->option('lang');
            $documentLanguage = is_string($languageOption) ? $languageOption : 'en';

            Rag::ingest([
                'title' => $documentTitle,
                'source_type' => $documentSourceType,
                'source_ref' => $documentSourceRef,
                'content' => $extractedText,
                'meta' => ['lang' => $documentLanguage],
            ]);
        }

        if (! $isDryRun) {
            $this->components->twoColumnDetail('Mode', $isSynchronous ? 'sync' : 'queue');
        }

        return self::SUCCESS;
    }

    private function extractText(string $rawPdfContent): string
    {
        // Very naive PDF text extraction: find text showing operators (Tj/TJ) tokens
        // This is placeholder-level but PHP-native and deterministic.
        $extractedText = '';
        if (preg_match_all('/\((.*?)\)\s*Tj/s', $rawPdfContent, $matches)) {
            $extractedText .= implode("\n", array_map(strip_tags(...), $matches[1]));
        }
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $rawPdfContent, $matches2)) {
            foreach ($matches2[1] as $textGroup) {
                if (preg_match_all('/\((.*?)\)/s', $textGroup, $matches3)) {
                    $extractedText .= "\n".implode('', $matches3[1]);
                }
            }
        }

        return mb_trim($extractedText) !== '' ? $extractedText : 'PDF content';
    }
}
