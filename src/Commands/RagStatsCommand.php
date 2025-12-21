<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Laravel\Prompts\warning;

final class RagStatsCommand extends Command
{
    protected $signature = 'rag:stats {--json : Output as JSON}';

    protected $description = 'Show statistics about the RAG knowledge base';

    public function handle(TenantContext $tenant): int
    {
        $tenantId = $tenant->current();
        Log::info('[rag:stats] Generating statistics', ['tenant' => $tenantId]);

        try {
            $documentsCount = RagDocument::query()->count();
            $chunksCount = RagChunk::query()->count();
            $embeddingsCount = RagEmbedding::query()->count();

            $knowledgeBaseVersion = 'v1';
            $tenancyEnabled = $tenant->enabled();
            $currentTenantId = $tenant->current();

            $ragConfig = config('rag');
            $embeddingModel = is_string($configValue = data_get($ragConfig, 'ai.embedding_model')) ? $configValue : '';
            $chatModel = is_string($configValue = data_get($ragConfig, 'ai.chat_model')) ? $configValue : '';

            $hybridConfig = data_get($ragConfig, 'retrieval.hybrid');
            if (! is_array($hybridConfig)) {
                $hybridConfig = ['enabled' => false, 'semantic_weight' => 0.0, 'keyword_weight' => 0.0];
            }

            $statsPayload = [
                'documents' => $documentsCount,
                'chunks' => $chunksCount,
                'embeddings' => $embeddingsCount,
                'kbVersion' => $knowledgeBaseVersion,
                'tenancy' => $tenancyEnabled ? 'multi' : 'single',
                'tenantId' => $tenancyEnabled ? ($currentTenantId ?? null) : 'single',
                'models' => [
                    'chat' => $chatModel,
                    'embedding' => $embeddingModel,
                ],
                'hybrid' => [
                    'enabled' => (bool) ($hybridConfig['enabled'] ?? false),
                    'semantic_weight' => (float) (isset($hybridConfig['semantic_weight'])
                    && (is_float($hybridConfig['semantic_weight'])
                        || is_int($hybridConfig['semantic_weight'])) ? $hybridConfig['semantic_weight'] : 0.0),
                    'keyword_weight' => (float) (isset($hybridConfig['keyword_weight'])
                    && (is_float($hybridConfig['keyword_weight'])
                        || is_int($hybridConfig['keyword_weight'])) ? $hybridConfig['keyword_weight'] : 0.0),
                ],
            ];

            if ($this->option('json')) {
                $jsonOutput = json_encode($statsPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if ($jsonOutput === false) {
                    Log::error('[rag:stats] Failed to encode JSON', ['tenant' => $tenantId]);
                    warning('Failed to encode statistics as JSON');

                    return self::FAILURE;
                }
                $this->output->writeln($jsonOutput);

                return self::SUCCESS;
            }

            $this->newLine();
            $this->components->twoColumnDetail('Documents', (string) $statsPayload['documents']);
            $this->components->twoColumnDetail('Chunks', (string) $statsPayload['chunks']);
            $this->components->twoColumnDetail('Embeddings', (string) $statsPayload['embeddings']);
            $this->components->twoColumnDetail('KB Version', $statsPayload['kbVersion']);
            $this->components->twoColumnDetail('Tenancy', $statsPayload['tenancy']);
            $this->components->twoColumnDetail('Tenant ID',
                is_string($statsPayload['tenantId']) ? $statsPayload['tenantId'] : 'null');
            $this->components->twoColumnDetail('Chat Model', $statsPayload['models']['chat']);
            $this->components->twoColumnDetail('Embedding Model', $statsPayload['models']['embedding']);
            $this->components->twoColumnDetail('Hybrid', $statsPayload['hybrid']['enabled'] ? 'enabled' : 'disabled');
            $this->components->twoColumnDetail('Hybrid Weights',
                'semantic='.$statsPayload['hybrid']['semantic_weight'].' keyword='.$statsPayload['hybrid']['keyword_weight']);

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('[rag:stats] Unexpected error', ['tenant' => $tenantId, 'error' => $e->getMessage()]);
            warning('Failed to generate statistics: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
