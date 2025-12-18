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

        $path = (string) $this->argument('path');
        if (! $files->exists($path)) {
            warning('Backup not found: '.$path);

            return self::INVALID;
        }

        $content = $files->get($path);
        if (str_ends_with($path, '.gz')) {
            $decoded = @gzdecode($content);
            if ($decoded !== false) {
                $content = $decoded;
            }
        }

        if ($this->option('decrypt')) {
            /** @var Encrypter $crypt */
            $crypt = resolve(Encrypter::class);
            try {
                $content = $crypt->decryptString($content);
            } catch (Throwable) {
                warning('Failed to decrypt backup. Invalid key or corrupted archive.');

                return self::FAILURE;
            }
        }

        $data = json_decode((string) $content, true);
        if (! is_array($data)) {
            warning('Invalid backup content.');

            return self::INVALID;
        }

        $docs = is_array($data['documents'] ?? null) ? count($data['documents']) : 0;
        $chunks = is_array($data['chunks'] ?? null) ? count($data['chunks']) : 0;
        $embeds = is_array($data['embeddings'] ?? null) ? count($data['embeddings']) : 0;
        $queries = is_array($data['queries'] ?? null) ? count($data['queries']) : 0;

        if ($this->option('dry-run')) {
            $this->components->twoColumnDetail('Documents', (string) $docs);
            $this->components->twoColumnDetail('Chunks', (string) $chunks);
            $this->components->twoColumnDetail('Embeddings', (string) $embeds);
            $this->components->twoColumnDetail('Queries', (string) $queries);

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
        $documents = $data['documents'] ?? [];
        if (is_array($documents)) {
            foreach ($documents as $d) {
                if (is_array($d) && isset($d['id'])) {
                    /** @var array<string,mixed> $d */
                    RagDocument::query()->updateOrCreate(['id' => $d['id']], $d);
                }
            }
        }

        $dataChunks = $data['chunks'] ?? [];
        if (is_array($dataChunks)) {
            foreach ($dataChunks as $c) {
                if (is_array($c) && isset($c['id'])) {
                    /** @var array<string,mixed> $c */
                    RagChunk::query()->updateOrCreate(['id' => $c['id']], $c);
                }
            }
        }

        $embeddings = $data['embeddings'] ?? [];
        if (is_array($embeddings)) {
            foreach ($embeddings as $e) {
                if (is_array($e) && isset($e['id'])) {
                    /** @var array<string,mixed> $e */
                    RagEmbedding::query()->updateOrCreate(['id' => $e['id']], $e);
                }
            }
        }

        $dataQueries = $data['queries'] ?? [];
        if (is_array($dataQueries)) {
            foreach ($dataQueries as $q) {
                if (is_array($q) && isset($q['id'])) {
                    /** @var array<string,mixed> $q */
                    RagQuery::query()->updateOrCreate(['id' => $q['id']], $q);
                }
            }
        }

        $this->newLine();
        $this->components->twoColumnDetail('Restored documents', (string) $docs);
        $this->components->twoColumnDetail('Restored chunks', (string) $chunks);
        $this->components->twoColumnDetail('Restored embeddings', (string) $embeds);
        $this->components->twoColumnDetail('Restored queries', (string) $queries);

        return self::SUCCESS;
    }
}
