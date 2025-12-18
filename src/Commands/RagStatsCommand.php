<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;

final class RagStatsCommand extends Command
{
    protected $signature = 'rag:stats {--json : Output as JSON}';

    protected $description = 'Show statistics about the RAG knowledge base';

    public function handle(TenantContext $tenant): int
    {
        $docs = RagDocument::query()->count();
        $chunks = RagChunk::query()->count();
        $embeddings = RagEmbedding::query()->count();

        $kbVersion = 'v1';
        $tenancyEnabled = $tenant->enabled();
        $tenantId = $tenant->current();

        $config = config('rag');
        $embeddingModel = (string) ($config['ai']['embedding_model'] ?? '');
        $chatModel = (string) ($config['ai']['chat_model'] ?? '');
        $hybrid = $config['retrieval']['hybrid'] ?? ['enabled' => false, 'semantic_weight' => 0.0, 'keyword_weight' => 0.0];

        $payload = [
            'documents' => $docs,
            'chunks' => $chunks,
            'embeddings' => $embeddings,
            'kbVersion' => $kbVersion,
            'tenancy' => $tenancyEnabled ? 'multi' : 'single',
            'tenantId' => $tenancyEnabled ? ($tenantId ?? null) : 'single',
            'models' => [
                'chat' => $chatModel,
                'embedding' => $embeddingModel,
            ],
            'hybrid' => [
                'enabled' => (bool) ($hybrid['enabled'] ?? false),
                'semantic_weight' => (float) ($hybrid['semantic_weight'] ?? 0.0),
                'keyword_weight' => (float) ($hybrid['keyword_weight'] ?? 0.0),
            ],
        ];

        if ($this->option('json')) {
            $this->output->writeln(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->twoColumnDetail('Documents', (string) $payload['documents']);
        $this->components->twoColumnDetail('Chunks', (string) $payload['chunks']);
        $this->components->twoColumnDetail('Embeddings', (string) $payload['embeddings']);
        $this->components->twoColumnDetail('KB Version', $payload['kbVersion']);
        $this->components->twoColumnDetail('Tenancy', $payload['tenancy']);
        $this->components->twoColumnDetail('Tenant ID', is_string($payload['tenantId']) ? $payload['tenantId'] : $payload['tenantId'] ?? 'null');
        $this->components->twoColumnDetail('Chat Model', $payload['models']['chat']);
        $this->components->twoColumnDetail('Embedding Model', $payload['models']['embedding']);
        $this->components->twoColumnDetail('Hybrid', $payload['hybrid']['enabled'] ? 'enabled' : 'disabled');
        $this->components->twoColumnDetail('Hybrid Weights', 'semantic='.$payload['hybrid']['semantic_weight'].' keyword='.$payload['hybrid']['keyword_weight']);

        return self::SUCCESS;
    }
}
