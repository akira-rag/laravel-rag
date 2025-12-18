<?php

declare(strict_types=1);

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Akira\Rag\Tenant\NullTenantResolver;

return [

    /*
    |--------------------------------------------------------------------------
    | Tenancy
    |--------------------------------------------------------------------------
    |
    | Configure single-tenant or multi-tenant behavior. When disabled, the
    | tenant column remains null and no resolver is used. When enabled, the
    | resolver returns the current tenant identifier.
    */
    'tenancy' => [
        'enabled' => false,
        'tenant_column' => 'tenant_id',
        'resolver' => NullTenantResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The Eloquent models used by the package. You may swap these with your
    | own models as long as the schema stays compatible.
    */
    'models' => [
        'document' => RagDocument::class,
        'chunk' => RagChunk::class,
        'embedding' => RagEmbedding::class,
        'query' => RagQuery::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Models
    |--------------------------------------------------------------------------
    |
    | Default identifiers for embedding, chat, and reranking. These are names
    | only; configure your actual provider separately.
    */
    'ai' => [
        'embedding_model' => 'text-embedding-3-small',
        'chat_model' => 'gpt-4.1-mini',
        'rerank_model' => 'gpt-4.1-mini',
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunking
    |--------------------------------------------------------------------------
    |
    | Token-based chunking settings. Overlap preserves context between adjacent
    | chunks during retrieval.
    */
    'chunking' => [
        'target_tokens' => 800,
        'overlap_tokens' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retrieval
    |--------------------------------------------------------------------------
    |
    | top_k controls final chunks returned; candidate_k controls intermediate
    | candidates. Hybrid retrieval blends semantic and keyword scoring. The
    | ts_config controls PostgreSQL text search configuration.
    */
    'retrieval' => [
        'top_k' => 5,
        'candidate_k' => 20,
        'hybrid' => [
            'enabled' => true,
            'semantic_weight' => 0.75,
            'keyword_weight' => 0.25,
            'ts_config' => 'simple',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt
    |--------------------------------------------------------------------------
    |
    | Default system prompt and language used when constructing chat requests.
    */
    'prompt' => [
        'system' => 'You MUST answer only using the provided context. If the answer is not present, say so clearly.',
        'language' => 'en',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Enables response caching for Rag::ask. TTL is in seconds; prefix namespaces
    | cache entries.
    */
    'cache' => [
        'enabled' => true,
        'ttl_seconds' => 1209600,
        'prefix' => 'rag:v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    |
    | When enabled, queries and retrieved chunks are stored. Use store_prompts
    | to persist prompts when appropriate.
    */
    'audit' => [
        'enabled' => true,
        'store_prompts' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Connection and queue name for background work. Null values fall back to
    | your application defaults.
    */
    'queue' => [
        'connection' => null,
        'queue' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Observability
    |--------------------------------------------------------------------------
    |
    | Configure structured logging and metrics collection for RAG operations.
    | Logs are written to the configured channel. Metrics are recorded via the
    | provided recorder implementation.
    */
    'observability' => [
        'logging' => [
            'enabled' => true,
            'channel' => 'rag',
        ],
        'metrics' => [
            'enabled' => true,
            'recorder' => Akira\Rag\Observability\LogMetricsRecorder::class,
        ],
    ],
];
