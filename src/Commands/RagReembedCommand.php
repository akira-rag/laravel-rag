<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagDocument;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;


final class RagReembedCommand extends Command
{
    protected $signature = 'rag:reembed'
        .' {--document_id=}'
        .' {--all}'
        .' {--sync : Run embedding synchronously (no queue), if supported}'
        .' {--model= : Override embedding model for this run}';

    protected $description = 'Rebuild embeddings for existing documents';

    public function handle(): int
    {
        $docId = (string) ($this->option('document_id') ?? '');
        $all = (bool) $this->option('all');
        $model = (string) ($this->option('model') ?? '');

        if (! $all && $docId === '') {
            info('Akira RAG - Reembed');
            $choice = select('Rebuild embeddings for', [
                'all' => 'All documents',
                'single' => 'Single document',
            ], 'single');
            if ($choice === 'all') {
                $all = true;
            } else {
                $docId = text('Document ID (UUID)');
            }
        }

        if (! $all && $docId === '') {
            warning('Provide --document_id or use --all');
            return self::INVALID;
        }

        $query = RagDocument::query();
        $countDocs = 0;
        $countChunks = 0; // placeholder for future per-chunk re-embeddings

        if ($all) {
            $documents = $query->pluck('id');
        } else {
            $documents = $query->whereKey($docId)->pluck('id');
        }

        $countDocs = $documents->count();
        $countChunks = 0;

        $this->newLine();
        $this->components->twoColumnDetail('Documents affected', (string) $countDocs);
        $this->components->twoColumnDetail('Embeddings recreated', (string) $countChunks);
        if ($model !== '') {
            $this->components->twoColumnDetail('Model override', $model);
        }
        $this->components->twoColumnDetail('Mode', $this->option('sync') ? 'sync' : 'queue');

        return self::SUCCESS;
    }
}

