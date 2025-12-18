<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;


final class RagExportCommand extends Command
{
    protected $signature = 'rag:export'
        .' {--format=json}'
        .' {--output=}'
        .' {--include-embeddings}'
        .' {--include-audit}'
        .' {--compress}'
        .' {--encrypt : Encrypt export using application key}';

    protected $description = 'Export the RAG knowledge base for the current tenant';

    public function handle(TenantContext $tenant, Filesystem $files): int
    {
        $format = (string) ($this->option('format') ?? 'json');
        if ($format !== 'json') {
            warning('Only json format is supported at the moment.');
            return self::INVALID;
        }

        $output = (string) ($this->option('output') ?? '');
        if ($output === '') {
            $suggest = storage_path('app/rag/exports/'.($tenant->enabled() ? ($tenant->current() ?? 'unknown') : 'single').'/export-'.Carbon::now()->format('Ymd-His').'.json');
            $output = text('Output path', default: $suggest);
        }

        $includeEmbeddings = (bool) $this->option('include-embeddings');
        $includeAudit = (bool) $this->option('include-audit');
        $compress = (bool) $this->option('compress');
        $encrypt = (bool) $this->option('encrypt');

        $payload = [
            'meta' => [
                'version' => 'v1',
                'tenant' => $tenant->enabled() ? ($tenant->current() ?? null) : 'single',
                'timestamp' => Carbon::now()->toIso8601String(),
            ],
            'documents' => RagDocument::query()->orderBy('id')->get()->toArray(),
            'chunks' => RagChunk::query()->orderBy('id')->get()->toArray(),
            'embeddings' => $includeEmbeddings ? RagEmbedding::query()->orderBy('id')->get()->toArray() : [],
            'queries' => $includeAudit ? RagQuery::query()->orderBy('id')->get()->toArray() : [],
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            warning('Failed to encode export payload.');
            return self::FAILURE;
        }

        if ($encrypt) {
            /** @var Encrypter $crypt */
            $crypt = app('encrypter');
            $json = $crypt->encryptString($json);
        }

        $finalPath = $output;
        if ($compress) {
            if (! str_ends_with($finalPath, '.gz')) {
                $finalPath .= '.gz';
            }
            $gz = gzencode($json, 9);
            if ($gz === false) {
                warning('Failed to compress export.');
                return self::FAILURE;
            }
            $files->ensureDirectoryExists(dirname($finalPath));
            $files->put($finalPath, $gz);
        } else {
            $files->ensureDirectoryExists(dirname($finalPath));
            $files->put($finalPath, $json);
        }

        $this->newLine();
        $this->components->twoColumnDetail('Format', $format.($compress ? '+gz' : ''));
        $this->components->twoColumnDetail('Encrypted', $encrypt ? 'yes' : 'no');
        $this->components->twoColumnDetail('Embeddings', $includeEmbeddings ? 'included' : 'omitted');
        $this->components->twoColumnDetail('Audit', $includeAudit ? 'included' : 'omitted');
        $this->components->twoColumnDetail('Output', $finalPath);

        return self::SUCCESS;
    }
}


