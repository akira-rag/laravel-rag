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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\warning;

final class RagRestoreCommand extends Command
{
    protected $signature
        = 'rag:restore'
        .' {path : Path to backup archive}'
        .' {--force : Skip confirmation prompt}'
        .' {--dry-run : Validate backup without restoring}'
        .' {--decrypt : Explicitly decrypt backup before restore}';

    protected $description = 'Restore a RAG knowledge base from a previous backup';

    public function handle(Filesystem $files, TenantContext $tenant): int
    {
        $tenantId = $tenant->current();
        Log::info('[rag:restore] Starting restore', ['tenant' => $tenantId]);
        /** @var string $backupPath */
        $backupPath = $this->argument('path');
        if (! $files->exists($backupPath)) {
            Log::warning('[rag:restore] Backup file not found', ['tenant' => $tenantId, 'path' => $backupPath]);
            warning('Backup not found: '.$backupPath);

            return self::INVALID;
        }

        if (! $files->isReadable($backupPath)) {
            Log::warning('[rag:restore] Backup file not readable', ['tenant' => $tenantId, 'path' => $backupPath]);
            warning('Backup file is not readable: '.$backupPath);

            return self::INVALID;
        }

        try {
            $backupContent = $files->get($backupPath);

            if (str_ends_with($backupPath, '.gz')) {
                $decodedContent = @gzdecode($backupContent);
                if ($decodedContent === false) {
                    Log::error('[rag:restore] Failed to decompress backup', ['tenant' => $tenantId, 'path' => $backupPath]);
                    warning('Failed to decompress backup archive');

                    return self::FAILURE;
                }
                $backupContent = $decodedContent;
            }

            if ($this->option('decrypt')) {
                /** @var Encrypter $encrypter */
                $encrypter = resolve(Encrypter::class);
                try {
                    $backupContent = $encrypter->decryptString($backupContent);
                } catch (Throwable $e) {
                    Log::error('[rag:restore] Decryption failed', ['tenant' => $tenantId, 'error' => $e->getMessage()]);
                    warning('Failed to decrypt backup. Invalid key or corrupted archive.');

                    return self::FAILURE;
                }
            }

            $backupData = json_decode((string) $backupContent, true);
            if (! is_array($backupData)) {
                Log::error('[rag:restore] Invalid backup format', ['tenant' => $tenantId]);
                warning('Invalid backup content.');

                return self::INVALID;
            }

            if (! isset($backupData['meta'])) {
                Log::warning('[rag:restore] Missing backup metadata', ['tenant' => $tenantId]);
                warning('Backup is missing metadata. This may be an invalid or corrupted backup.');
            }

            $backupTenant = $backupData['meta']['tenant'] ?? null;
            if ($tenant->enabled() && $backupTenant !== null && $backupTenant !== $tenantId) {
                Log::warning('[rag:restore] Tenant mismatch', [
                    'current_tenant' => $tenantId,
                    'backup_tenant' => $backupTenant,
                ]);
                warning("Tenant mismatch: current tenant is '{$tenantId}' but backup is from '{$backupTenant}'");

                if (! $this->option('force') && ! confirm('Continue with cross-tenant restore?', false)) {
                    return self::INVALID;
                }
            }
        } catch (Throwable $e) {
            Log::error('[rag:restore] Failed to read backup', ['tenant' => $tenantId, 'error' => $e->getMessage()]);
            warning('Failed to read backup file: '.$e->getMessage());

            return self::FAILURE;
        }

        $documentsCount = is_array($backupData['documents'] ?? null) ? count($backupData['documents']) : 0;
        $chunksCount = is_array($backupData['chunks'] ?? null) ? count($backupData['chunks']) : 0;
        $embeddingsCount = is_array($backupData['embeddings'] ?? null) ? count($backupData['embeddings']) : 0;
        $queriesCount = is_array($backupData['queries'] ?? null) ? count($backupData['queries']) : 0;

        if ($this->option('dry-run')) {
            $this->components->twoColumnDetail('Documents', (string) $documentsCount);
            $this->components->twoColumnDetail('Chunks', (string) $chunksCount);
            $this->components->twoColumnDetail('Embeddings', (string) $embeddingsCount);
            $this->components->twoColumnDetail('Queries', (string) $queriesCount);

            return self::SUCCESS;
        }

        /* @codeCoverageIgnoreStart */
        if (! $this->option('force')
            && ! confirm('Proceed with restore? Existing data will be preserved where possible.', false)
        ) {
            Log::info('[rag:restore] Restore cancelled by user', ['tenant' => $tenantId]);

            return self::INVALID;
        }
        /* @codeCoverageIgnoreEnd */

        try {
            DB::beginTransaction();

            $restoredDocuments = 0;
            $restoredChunks = 0;
            $restoredEmbeddings = 0;
            $restoredQueries = 0;

            $backupDocuments = $backupData['documents'] ?? [];
            if (is_array($backupDocuments)) {
                foreach ($backupDocuments as $documentData) {
                    if (is_array($documentData) && isset($documentData['id'])) {
                        /** @var array<string,mixed> $documentData */
                        RagDocument::query()->updateOrCreate(['id' => $documentData['id']], $documentData);
                        $restoredDocuments++;
                    }
                }
            }

            $backupChunks = $backupData['chunks'] ?? [];
            if (is_array($backupChunks)) {
                foreach ($backupChunks as $chunkData) {
                    if (is_array($chunkData) && isset($chunkData['id'])) {
                        /** @var array<string,mixed> $chunkData */
                        RagChunk::query()->updateOrCreate(['id' => $chunkData['id']], $chunkData);
                        $restoredChunks++;
                    }
                }
            }

            $backupEmbeddings = $backupData['embeddings'] ?? [];
            if (is_array($backupEmbeddings)) {
                foreach ($backupEmbeddings as $embeddingData) {
                    if (is_array($embeddingData) && isset($embeddingData['id'])) {
                        /** @var array<string,mixed> $embeddingData */
                        RagEmbedding::query()->updateOrCreate(['id' => $embeddingData['id']], $embeddingData);
                        $restoredEmbeddings++;
                    }
                }
            }

            $backupQueries = $backupData['queries'] ?? [];
            if (is_array($backupQueries)) {
                foreach ($backupQueries as $queryData) {
                    if (is_array($queryData) && isset($queryData['id'])) {
                        /** @var array<string,mixed> $queryData */
                        RagQuery::query()->updateOrCreate(['id' => $queryData['id']], $queryData);
                        $restoredQueries++;
                    }
                }
            }

            DB::commit();

            Log::info('[rag:restore] Restore completed', [
                'tenant' => $tenantId,
                'documents' => $restoredDocuments,
                'chunks' => $restoredChunks,
                'embeddings' => $restoredEmbeddings,
                'queries' => $restoredQueries,
            ]);

            $this->newLine();
            $this->components->twoColumnDetail('Restored documents', (string) $documentsCount);
            $this->components->twoColumnDetail('Restored chunks', (string) $chunksCount);
            $this->components->twoColumnDetail('Restored embeddings', (string) $embeddingsCount);
            $this->components->twoColumnDetail('Restored queries', (string) $queriesCount);

            return self::SUCCESS;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('[rag:restore] Restore failed', ['tenant' => $tenantId, 'error' => $e->getMessage()]);
            warning('Restore failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
