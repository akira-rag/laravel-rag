<?php

declare(strict_types=1);

namespace Akira\Rag;

use Akira\Rag\Exceptions\InvalidPayload;
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

        foreach (['title', 'source_type', 'source_ref', 'content'] as $key) {
            throw_if(! isset($payload[$key]) || ! is_string($payload[$key]) || $payload[$key] === '',
                InvalidPayload::class, 'Missing or invalid field: '.$key);
        }

        throw_if(array_key_exists('tenant_id', $payload), InvalidPayload::class, 'tenant_id is not allowed in payload');

        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];

        $hash = hash('sha256',
            $payload['source_type'].'|'.$payload['source_ref'].'|'.hash('sha256', (string) $payload['content']));

        $tenantId = $this->tenant->current();
        $tenantColumn = $this->tenant->column();

        $existing = RagDocument::query()
            ->where('hash', $hash)
            ->first();
        if ($existing) {
            return ['document_id' => (string) $existing->getKey(), 'chunks' => $existing->chunks()->count()];
        }

        $docId = (string) Str::uuid();

        $this->db->transaction(function () use ($payload, $meta, $hash, $tenantId, $tenantColumn, $docId): void {

            $document = RagDocument::query()->create([
                'id' => $docId,
                'title' => $payload['title'],
                'source_type' => $payload['source_type'],
                'source_ref' => $payload['source_ref'],
                'hash' => $hash,
                $tenantColumn => $tenantId,
                'meta' => $meta,
            ]);

            $chunks = $this->chunk($payload['content']);
            foreach ($chunks as $i => $content) {
                $chunkId = (string) Str::uuid();
                RagChunk::query()->create([
                    'id' => $chunkId,
                    'document_id' => $document->getKey(),
                    'position' => $i,
                    'content' => $content,
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

        return ['document_id' => $docId, 'chunks' => count($this->chunk($payload['content']))];
    }

    /**
     * @param  array<string,mixed>  $filters
     * @return array{answer:string,chunks:array<int,array{id:string,score:float}>,query_id:string}
     */
    public function ask(string $question, array $filters = []): array
    {

        throw_if($question === '', InvalidPayload::class, 'Question must be non-empty');

        $tenantId = $this->tenant->current();
        $tenantColumn = $this->tenant->column();

        $cacheEnabled = (bool) $this->config->get('rag.cache.enabled', true);
        $cachePrefix = (string) $this->config->get('rag.cache.prefix', 'rag:v1');
        $cacheKey = $cachePrefix.':ask:'.sha1(json_encode([
            't' => $tenantId,
            'q' => $question,
            'f' => $filters,
        ], JSON_THROW_ON_ERROR));

        if ($cacheEnabled) {
            $cached = $this->cache->store()->get($cacheKey);
            if (is_array($cached)
                && isset($cached['answer'], $cached['chunks'], $cached['query_id'])
                && is_string($cached['answer'])
                && is_array($cached['chunks'])
                && is_string($cached['query_id'])) {
                /** @var array{answer:string,chunks:array<int,array{id:string,score:float}>,query_id:string} */
                return $cached;
            }
        }

        $queryId = (string) Str::uuid();

        /** @var array<int,array{id:string,score:float}> $chunks */
        $chunks = RagChunk::query()
            ->when(isset($filters['document_id']), function (Builder $q) use ($filters): void {

                $q->where('document_id', $filters['document_id']);
            })
            ->orderBy('position')
            ->limit($this->config->integer('rag.retrieval.top_k', 5))
            ->get(['id', 'position'])
            ->map(fn (RagChunk $c): array => ['id' => (string) $c->id, 'score' => 1.0])
            ->values()
            ->all();

        $auditEnabled = (bool) $this->config->get('rag.audit.enabled', true);

        if ($auditEnabled) {
            $this->db->transaction(function () use ($queryId, $question, $tenantId, $tenantColumn, $chunks): void {

                RagQuery::query()->create([
                    'id' => $queryId,
                    'question' => $question,
                    $tenantColumn => $tenantId,
                    'meta' => [],
                ]);

                foreach ($chunks as $rank => $c) {
                    RagQueryChunk::query()->create([
                        'id' => (string) Str::uuid(),
                        'query_id' => $queryId,
                        'chunk_id' => $c['id'],
                        'score' => $c['score'],
                        'rank' => $rank,
                        $tenantColumn => $tenantId,
                        'meta' => [],
                    ]);
                }
            });
        }

        $result = [
            'answer' => 'Deterministic mock answer',
            'chunks' => $chunks,
            'query_id' => $queryId,
        ];

        if ($cacheEnabled) {
            $ttl = $this->config->integer('rag.cache.ttl_seconds', 1209600);
            $this->cache->store()->put($cacheKey, $result, $ttl);
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function chunk(string $content): array
    {

        $target = $this->config->integer('rag.chunking.target_tokens', 800);
        $overlap = $this->config->integer('rag.chunking.overlap_tokens', 120);

        $words = preg_split('/\s+/', mb_trim($content)) ?: [];
        if ($words === [] || (count($words) === 1 && $words[0] === '')) {
            return [];
        }

        $chunks = [];
        $i = 0;
        while ($i < count($words)) {
            $chunkWords = array_slice($words, $i, $target);
            $chunks[] = implode(' ', $chunkWords);
            if ($i + $target >= count($words)) {
                break;
            }
            $i += ($target - $overlap);
        }

        return $chunks;
    }
}
