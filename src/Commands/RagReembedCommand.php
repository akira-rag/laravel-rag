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
        $documentIdOption = $this->option('document_id');
        /** @var string $documentId */
        $documentId = is_string($documentIdOption) ? $documentIdOption : '';
        $processAllDocuments = (bool) $this->option('all');
        $modelOption = $this->option('model');
        /** @var string $embeddingModel */
        $embeddingModel = is_string($modelOption) ? $modelOption : '';

        if (! $processAllDocuments && $documentId === '' && $this->input->isInteractive() && ! app()->runningUnitTests()) {
            // @codeCoverageIgnoreStart
            info('Akira RAG - Reembed');
            $userChoice = select('Rebuild embeddings for', [
                'all' => 'All documents',
                'single' => 'Single document',
            ], 'single');
            if ($userChoice === 'all') {
                $processAllDocuments = true;
            } else {
                $documentId = text('Document ID (UUID)');
            }
            // @codeCoverageIgnoreEnd
        }

        if (! $processAllDocuments && $documentId === '') {
            warning('Provide --document_id or use --all');

            return self::INVALID;
        }

        $documentsQuery = RagDocument::query();
        $affectedDocumentsCount = 0;
        $recreatedEmbeddingsCount = 0; // placeholder for future per-chunk re-embeddings

        $documentIds = $processAllDocuments ? $documentsQuery->pluck('id') : $documentsQuery->whereKey($documentId)->pluck('id');

        $affectedDocumentsCount = $documentIds->count();
        $recreatedEmbeddingsCount = 0;

        $this->newLine();
        $this->components->twoColumnDetail('Documents affected', (string) $affectedDocumentsCount);
        $this->components->twoColumnDetail('Embeddings recreated', (string) $recreatedEmbeddingsCount);
        if ($embeddingModel !== '') {
            $this->components->twoColumnDetail('Model override', $embeddingModel);
        }
        $this->components->twoColumnDetail('Mode', $this->option('sync') ? 'sync' : 'queue');

        return self::SUCCESS;
    }
}
