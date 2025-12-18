<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

final class RagRestoreCommand extends Command
{
    protected $signature = 'rag:restore'
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
            $crypt = app('encrypter');
            try {
                $content = $crypt->decryptString($content);
            } catch (\Throwable) {
                warning('Failed to decrypt backup. Invalid key or corrupted archive.');
                return self::FAILURE;
            }
        }

        $data = json_decode($content, true);
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

        if (! $this->option('force')) {
            if (! confirm('Proceed with restore? Existing data will be preserved where possible.', false)) {
                return self::INVALID;
            }
        }

        // Transactional restore is application DB concern; here, simple upserts
        foreach ($data['documents'] ?? [] as $d) {
            RagDocument::query()->updateOrCreate(['id' => $d['id']], $d);
        }
        foreach ($data['chunks'] ?? [] as $c) {
            RagChunk::query()->updateOrCreate(['id' => $c['id']], $c);
        }
        foreach ($data['embeddings'] ?? [] as $e) {
            RagEmbedding::query()->updateOrCreate(['id' => $e['id']], $e);
        }
        foreach ($data['queries'] ?? [] as $q) {
            RagQuery::query()->updateOrCreate(['id' => $q['id']], $q);
        }

        $this->newLine();
        $this->components->twoColumnDetail('Restored documents', (string) $docs);
        $this->components->twoColumnDetail('Restored chunks', (string) $chunks);
        $this->components->twoColumnDetail('Restored embeddings', (string) $embeds);
        $this->components->twoColumnDetail('Restored queries', (string) $queries);

        return self::SUCCESS;
    }
}

