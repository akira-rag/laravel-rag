<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Facades\Rag;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

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

    public function handle(Filesystem $files, TenantContext $tenant): int
    {
        $tenantId = $tenant->current();
        Log::info('[rag:import:pdf] Starting PDF import', ['tenant' => $tenantId]);
        /** @var string $importPath */
        $importPath = $this->argument('path');
        if (! $files->exists($importPath)) {
            Log::warning('[rag:import:pdf] Path not found', ['tenant' => $tenantId, 'path' => $importPath]);
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
            Log::warning('[rag:import:pdf] No PDF files found', ['tenant' => $tenantId, 'path' => $importPath]);
            warning('No PDF files found.');

            return self::INVALID;
        }

        $isDryRun = (bool) $this->option('dry-run');
        $isSynchronous = (bool) $this->option('sync');

        $successCount = 0;
        $failureCount = 0;

        foreach ($pdfFilePaths as $pdfFilePath) {
            try {
                $titleOption = $this->option('title');
                $documentTitle = is_string($titleOption) ? $titleOption : $files->name($pdfFilePath);

                $sourceTypeOption = $this->option('source_type');
                $documentSourceType = is_string($sourceTypeOption) ? $sourceTypeOption : 'pdf';

                $sourceRefOption = $this->option('source_ref');
                $documentSourceRef = is_string($sourceRefOption) ? $sourceRefOption : $pdfFilePath;

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

                $successCount++;
            } catch (InvalidPayload $e) {
                Log::error('[rag:import:pdf] Invalid payload', ['tenant' => $tenantId, 'path' => $pdfFilePath, 'error' => $e->getMessage()]);
                warning("Failed to ingest {$pdfFilePath}: ".$e->getMessage());
                $failureCount++;
            } catch (Throwable $e) {
                Log::error('[rag:import:pdf] Unexpected error', ['tenant' => $tenantId, 'path' => $pdfFilePath, 'error' => $e->getMessage()]);
                warning("Failed to process {$pdfFilePath}: ".$e->getMessage());
                $failureCount++;
            }
        }

        if (! $isDryRun) {
            Log::info('[rag:import:pdf] Import completed', ['tenant' => $tenantId, 'success' => $successCount, 'failures' => $failureCount]);
            $this->components->twoColumnDetail('Mode', $isSynchronous ? 'sync' : 'queue');
            $this->components->twoColumnDetail('Imported', (string) $successCount);
            if ($failureCount > 0) {
                $this->components->twoColumnDetail('Failed', (string) $failureCount);
            }
        }

        return $failureCount > 0 ? self::FAILURE : self::SUCCESS;
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
