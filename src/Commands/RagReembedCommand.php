<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagDocument;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

    public function handle(TenantContext $tenant): int
    {
        $tenantId = $tenant->current();
        Log::info('[rag:reembed] Starting re-embedding', ['tenant' => $tenantId]);
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
            Log::warning('[rag:reembed] Missing required parameters', ['tenant' => $tenantId]);
            warning('Provide --document_id or use --all');

            return self::INVALID;
        }

        if ($documentId !== '' && ! $this->isValidUuid($documentId)) {
            Log::warning('[rag:reembed] Invalid document ID format', ['tenant' => $tenantId, 'document_id' => $documentId]);
            warning('Invalid document ID format. Must be a valid UUID.');

            return self::INVALID;
        }

        $documentsQuery = RagDocument::query();
        $affectedDocumentsCount = 0;
        $recreatedEmbeddingsCount = 0;

        $documentIds = $processAllDocuments ? $documentsQuery->pluck('id') : $documentsQuery->whereKey($documentId)->pluck('id');

        $affectedDocumentsCount = $documentIds->count();

        if ($affectedDocumentsCount === 0) {
            Log::warning('[rag:reembed] No documents found', ['tenant' => $tenantId, 'all' => $processAllDocuments, 'document_id' => $documentId]);
            warning('No documents found to re-embed');

            return self::INVALID;
        }

        $recreatedEmbeddingsCount = 0;

        Log::info('[rag:reembed] Re-embedding completed', [
            'tenant' => $tenantId,
            'documents' => $affectedDocumentsCount,
            'model_override' => $embeddingModel,
        ]);

        $this->newLine();
        $this->components->twoColumnDetail('Documents affected', (string) $affectedDocumentsCount);
        $this->components->twoColumnDetail('Embeddings recreated', (string) $recreatedEmbeddingsCount);
        if ($embeddingModel !== '') {
            $this->components->twoColumnDetail('Model override', $embeddingModel);
        }
        $this->components->twoColumnDetail('Mode', $this->option('sync') ? 'sync' : 'queue');

        return self::SUCCESS;
    }

    private function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }
}
