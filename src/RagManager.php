<?php

declare(strict_types=1);

namespace Akira\Rag;

use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Exceptions\InvalidQuestionException;
use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Akira\Rag\Models\RagQueryChunk;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final readonly class RagManager
{
    public function __construct(
        private Config $config,
        private CacheFactory $cache,
        private ConnectionInterface $db,
        private TenantContext $tenant,
    ) {}

    /**
     * @param  array<string,mixed>  $payload
     * @return array{document_id:string, chunks:int}
     *
     * @throws InvalidPayload
     */
    public function ingest(array $payload): array
    {

        foreach (['title', 'source_type', 'source_ref', 'content'] as $requiredField) {
            throw_if(! isset($payload[$requiredField]) || ! is_string($payload[$requiredField]) || $payload[$requiredField] === '',
                InvalidPayload::missingField($requiredField));
        }

        throw_if(array_key_exists('tenant_id', $payload), InvalidPayload::tenantIdNotAllowed());

        $metadata = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];

        assert(is_string($payload['source_type']));
        assert(is_string($payload['source_ref']));
        assert(is_string($payload['content']));

        $contentHash = hash('sha256',
            $payload['source_type'].'|'.$payload['source_ref'].'|'.hash('sha256', $payload['content']));

        $tenantId = $this->tenant->current();
        $tenantColumn = $this->tenant->column();

        $existingDocument = RagDocument::query()
            ->where('hash', $contentHash)
            ->first();
        if ($existingDocument) {
            $existingDocumentKey = $existingDocument->getKey();
            assert(is_string($existingDocumentKey) || is_int($existingDocumentKey));

            return ['document_id' => (string) $existingDocumentKey, 'chunks' => $existingDocument->chunks()->count()];
        }

        $documentId = (string) Str::uuid();

        $this->db->transaction(function () use ($payload, $metadata, $contentHash, $tenantId, $tenantColumn, $documentId): void {

            $document = RagDocument::query()->create([
                'id' => $documentId,
                'title' => $payload['title'],
                'source_type' => $payload['source_type'],
                'source_ref' => $payload['source_ref'],
                'hash' => $contentHash,
                $tenantColumn => $tenantId,
                'meta' => $metadata,
            ]);

            $contentChunks = $this->chunk($payload['content']);
            foreach ($contentChunks as $chunkPosition => $chunkContent) {
                $chunkId = (string) Str::uuid();
                RagChunk::query()->create([
                    'id' => $chunkId,
                    'document_id' => $document->getKey(),
                    'position' => $chunkPosition,
                    'content' => $chunkContent,
                    $tenantColumn => $tenantId,
                    'meta' => [],
                ]);

                RagEmbedding::query()->create([
                    'id' => (string) Str::uuid(),
                    'chunk_id' => $chunkId,
                    'embedding' => json_encode([0.0]),
                    $tenantColumn => $tenantId,
                    'meta' => [],
                ]);
            }
        });

        return ['document_id' => $documentId, 'chunks' => count($this->chunk($payload['content']))];
    }

    /**
     * @param  array<string,mixed>  $filters
     * @return array{answer:string,chunks:array<int,array{id:string,score:float}>,query_id:string}
     */
    public function ask(string $question, array $filters = []): array
    {

        throw_if($question === '', InvalidQuestionException::emptyQuestion());

        $tenantId = $this->tenant->current();
        $tenantColumn = $this->tenant->column();

        $cacheEnabled = $this->config->boolean('rag.cache.enabled', true);
        $cachePrefix = $this->config->string('rag.cache.prefix', 'rag:v1');
        $cacheKey = $cachePrefix.':ask:'.sha1(json_encode([
            't' => $tenantId,
            'q' => $question,
            'f' => $filters,
        ], JSON_THROW_ON_ERROR));

        if ($cacheEnabled) {
            $cachedResult = $this->cache->store()->get($cacheKey);
            if (is_array($cachedResult)
                && isset($cachedResult['answer'], $cachedResult['chunks'], $cachedResult['query_id'])
                && is_string($cachedResult['answer'])
                && is_array($cachedResult['chunks'])
                && is_string($cachedResult['query_id'])
            ) {
                /** @var array{answer:string,chunks:array<int,array{id:string,score:float}>,query_id:string} $cachedResult */
                return $cachedResult;
            }
        }

        $queryId = (string) Str::uuid();

        /** @var array<int,array{id:string,score:float}> $retrievedChunks */
        $retrievedChunks = RagChunk::query()
            ->when(isset($filters['document_id']), function (Builder $query) use ($filters): void {

                $query->where('document_id', $filters['document_id']);
            })
            ->orderBy('position')
            ->limit($this->config->integer('rag.retrieval.top_k', 5))
            ->get(['id', 'position'])
            ->map(fn (RagChunk $chunk): array => ['id' => (string) $chunk->id, 'score' => 1.0])
            ->values()
            ->all();

        $auditEnabled = (bool) $this->config->get('rag.audit.enabled', true);

        if ($auditEnabled) {
            $this->db->transaction(function () use ($queryId, $question, $tenantId, $tenantColumn, $retrievedChunks): void {

                RagQuery::query()->create([
                    'id' => $queryId,
                    'question' => $question,
                    $tenantColumn => $tenantId,
                    'meta' => [],
                ]);

                foreach ($retrievedChunks as $chunkRank => $chunkData) {
                    RagQueryChunk::query()->create([
                        'id' => (string) Str::uuid(),
                        'query_id' => $queryId,
                        'chunk_id' => $chunkData['id'],
                        'score' => $chunkData['score'],
                        'rank' => $chunkRank,
                        $tenantColumn => $tenantId,
                        'meta' => [],
                    ]);
                }
            });
        }

        /** @var array{answer:string,chunks:array<int,array{id:string,score:float}>,query_id:string} $result */
        $result = [
            'answer' => 'Deterministic mock answer',
            'chunks' => $retrievedChunks,
            'query_id' => $queryId,
        ];

        if ($cacheEnabled) {
            $cacheTtlSeconds = $this->config->integer('rag.cache.ttl_seconds', 1209600);
            $this->cache->store()->put($cacheKey, $result, $cacheTtlSeconds);
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function chunk(string $content): array
    {

        $targetTokens = $this->config->integer('rag.chunking.target_tokens', 800);
        $overlapTokens = $this->config->integer('rag.chunking.overlap_tokens', 120);

        $words = preg_split('/\s+/', mb_trim($content)) ?: [];
        if ($words === [] || (count($words) === 1 && $words[0] === '')) {
            return [];
        }

        $resultChunks = [];
        $currentIndex = 0;
        while ($currentIndex < count($words)) {
            $chunkWords = array_slice($words, $currentIndex, $targetTokens);
            $resultChunks[] = implode(' ', $chunkWords);
            if ($currentIndex + $targetTokens >= count($words)) {
                break;
            }
            $currentIndex += ($targetTokens - $overlapTokens);
        }

        return $resultChunks;
    }
}
