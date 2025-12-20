<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
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

    public function handle(Filesystem $files): int
    {
        /** @var string $backupPath */
        $backupPath = $this->argument('path');
        if (! $files->exists($backupPath)) {
            warning('Backup not found: '.$backupPath);

            return self::INVALID;
        }

        $backupContent = $files->get($backupPath);
        if (str_ends_with($backupPath, '.gz')) {
            $decodedContent = @gzdecode($backupContent);
            if ($decodedContent !== false) {
                $backupContent = $decodedContent;
            }
        }

        if ($this->option('decrypt')) {
            /** @var Encrypter $encrypter */
            $encrypter = resolve(Encrypter::class);
            try {
                $backupContent = $encrypter->decryptString($backupContent);
            } catch (Throwable) {
                warning('Failed to decrypt backup. Invalid key or corrupted archive.');

                return self::FAILURE;
            }
        }

        $backupData = json_decode((string) $backupContent, true);
        if (! is_array($backupData)) {
            warning('Invalid backup content.');

            return self::INVALID;
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
            return self::INVALID;
        }
        /* @codeCoverageIgnoreEnd */

        // Transactional restore is application DB concern; here, simple upserts
        $backupDocuments = $backupData['documents'] ?? [];
        if (is_array($backupDocuments)) {
            foreach ($backupDocuments as $documentData) {
                if (is_array($documentData) && isset($documentData['id'])) {
                    /** @var array<string,mixed> $documentData */
                    RagDocument::query()->updateOrCreate(['id' => $documentData['id']], $documentData);
                }
            }
        }

        $backupChunks = $backupData['chunks'] ?? [];
        if (is_array($backupChunks)) {
            foreach ($backupChunks as $chunkData) {
                if (is_array($chunkData) && isset($chunkData['id'])) {
                    /** @var array<string,mixed> $chunkData */
                    RagChunk::query()->updateOrCreate(['id' => $chunkData['id']], $chunkData);
                }
            }
        }

        $backupEmbeddings = $backupData['embeddings'] ?? [];
        if (is_array($backupEmbeddings)) {
            foreach ($backupEmbeddings as $embeddingData) {
                if (is_array($embeddingData) && isset($embeddingData['id'])) {
                    /** @var array<string,mixed> $embeddingData */
                    RagEmbedding::query()->updateOrCreate(['id' => $embeddingData['id']], $embeddingData);
                }
            }
        }

        $backupQueries = $backupData['queries'] ?? [];
        if (is_array($backupQueries)) {
            foreach ($backupQueries as $queryData) {
                if (is_array($queryData) && isset($queryData['id'])) {
                    /** @var array<string,mixed> $queryData */
                    RagQuery::query()->updateOrCreate(['id' => $queryData['id']], $queryData);
                }
            }
        }

        $this->newLine();
        $this->components->twoColumnDetail('Restored documents', (string) $documentsCount);
        $this->components->twoColumnDetail('Restored chunks', (string) $chunksCount);
        $this->components->twoColumnDetail('Restored embeddings', (string) $embeddingsCount);
        $this->components->twoColumnDetail('Restored queries', (string) $queriesCount);

        return self::SUCCESS;
    }
}
