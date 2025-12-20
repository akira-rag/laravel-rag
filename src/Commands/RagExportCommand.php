<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Date;

use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

final class RagExportCommand extends Command
{
    protected $signature
        = 'rag:export'
        .' {--format=json}'
        .' {--output=}'
        .' {--include-embeddings}'
        .' {--include-audit}'
        .' {--compress}'
        .' {--encrypt : Encrypt export using application key}';

    protected $description = 'Export the RAG knowledge base for the current tenant';

    public function handle(TenantContext $tenant, Filesystem $files): int
    {

        $formatOption = $this->option('format');
        $exportFormat = is_string($formatOption) ? $formatOption : 'json';

        if ($exportFormat !== 'json') {
            warning('Only json format is supported at the moment.');

            return self::INVALID;
        }

        $outputOption = $this->option('output');
        $outputPath = is_string($outputOption) ? $outputOption : '';
        // @codeCoverageIgnoreStart
        if ($outputPath === '') {
            $suggestedPath = storage_path('app/rag/exports/'.($tenant->enabled() ? ($tenant->current() ?? 'unknown')
                    : 'single').'/export-'.Date::now()->format('Ymd-His').'.json');
            $outputPath = text('Output path', default: $suggestedPath);
        }
        // @codeCoverageIgnoreEnd

        $includeEmbeddings = (bool) $this->option('include-embeddings');
        $includeAudit = (bool) $this->option('include-audit');
        $shouldCompress = (bool) $this->option('compress');
        $shouldEncrypt = (bool) $this->option('encrypt');

        $exportPayload = [
            'meta' => [
                'version' => 'v1',
                'tenant' => $tenant->enabled() ? ($tenant->current() ?? null) : 'single',
                'timestamp' => Date::now()->toIso8601String(),
            ],
            'documents' => RagDocument::query()->orderBy('id')->get()->toArray(),
            'chunks' => RagChunk::query()->orderBy('id')->get()->toArray(),
            'embeddings' => $includeEmbeddings ? RagEmbedding::query()->orderBy('id')->get()->toArray() : [],
            'queries' => $includeAudit ? RagQuery::query()->orderBy('id')->get()->toArray() : [],
        ];

        $jsonContent = json_encode($exportPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // @codeCoverageIgnoreStart
        if ($jsonContent === false) {
            warning('Failed to encode export payload.');

            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        if ($shouldEncrypt) {
            /** @var Encrypter $encrypter */
            $encrypter = resolve(Encrypter::class);
            $jsonContent = $encrypter->encryptString($jsonContent);
        }

        $finalOutputPath = $outputPath;
        if ($shouldCompress) {
            if (! str_ends_with($finalOutputPath, '.gz')) {
                $finalOutputPath .= '.gz';
            }
            $compressedContent = gzencode((string) $jsonContent, 9);
            // @codeCoverageIgnoreStart
            if ($compressedContent === false) {
                warning('Failed to compress export.');

                return self::FAILURE;
            }
            // @codeCoverageIgnoreEnd
            $files->ensureDirectoryExists(dirname($finalOutputPath));
            $files->put($finalOutputPath, $compressedContent);
        } else {
            $files->ensureDirectoryExists(dirname($finalOutputPath));
            $files->put($finalOutputPath, $jsonContent);
        }

        $this->newLine();
        $this->components->twoColumnDetail('Format', $exportFormat.($shouldCompress ? '+gz' : ''));
        $this->components->twoColumnDetail('Encrypted', $shouldEncrypt ? 'yes' : 'no');
        $this->components->twoColumnDetail('Embeddings', $includeEmbeddings ? 'included' : 'omitted');
        $this->components->twoColumnDetail('Audit', $includeAudit ? 'included' : 'omitted');
        $this->components->twoColumnDetail('Output', $finalOutputPath);

        return self::SUCCESS;
    }
}
