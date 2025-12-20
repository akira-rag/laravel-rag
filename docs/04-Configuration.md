# Configuration

This guide provides a complete reference for all configuration options in `config/rag.php`.

## Configuration File Location

After installation, the configuration file is located at:
```
config/rag.php
```

Publish it with:
```bash
php artisan vendor:publish --tag="laravel-rag-config"
```

## Complete Configuration Reference

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Models Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the AI models used for embeddings and chat generation.
    | These settings integrate with PrismPHP.
    |
    */
    'ai' => [
        'embedding_model' => env('RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chat_model' => env('RAG_CHAT_MODEL', 'gpt-4'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunking Strategy
    |--------------------------------------------------------------------------
    |
    | Configure how documents are split into chunks for processing.
    |
    */
    'chunking' => [
        'target_tokens' => env('RAG_CHUNK_TARGET_TOKENS', 800),
        'overlap_tokens' => env('RAG_CHUNK_OVERLAP_TOKENS', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retrieval Settings
    |--------------------------------------------------------------------------
    |
    | Configure document retrieval behavior and hybrid search.
    |
    */
    'retrieval' => [
        'top_k' => env('RAG_RETRIEVAL_TOP_K', 5),
        
        'hybrid' => [
            'enabled' => env('RAG_HYBRID_ENABLED', true),
            'semantic_weight' => env('RAG_HYBRID_SEMANTIC_WEIGHT', 0.7),
            'keyword_weight' => env('RAG_HYBRID_KEYWORD_WEIGHT', 0.3),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure result caching for faster repeated queries.
    |
    */
    'cache' => [
        'enabled' => env('RAG_CACHE_ENABLED', true),
        'prefix' => env('RAG_CACHE_PREFIX', 'rag:v1'),
        'ttl_seconds' => env('RAG_CACHE_TTL_SECONDS', 1209600), // 14 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Enable/disable query audit logging.
    |
    */
    'audit' => [
        'enabled' => env('RAG_AUDIT_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Configuration
    |--------------------------------------------------------------------------
    |
    | Configure tenant isolation and resolution.
    |
    */
    'tenancy' => [
        'enabled' => env('RAG_TENANCY_ENABLED', false),
        'resolver' => \Akira\Rag\Tenant\NullTenantResolver::class,
        'tenant_column' => 'tenant_id',
    ],
];
```

## AI Models

### Embedding Model

**Purpose:** Converts text into vector representations for semantic search.

**Options:**
- `text-embedding-3-small` (1536 dimensions) - **Recommended**
  - Best balance of performance and cost
  - Good for most use cases
- `text-embedding-3-large` (3072 dimensions)
  - Higher accuracy
  - More expensive
  - Larger storage requirements
- `text-embedding-ada-002` (1536 dimensions)
  - Legacy model
  - Still supported but older

**Environment Variable:**
```env
RAG_EMBEDDING_MODEL=text-embedding-3-small
```

**Example:**
```php
'ai' => [
    'embedding_model' => env('RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
],
```

### Chat Model

**Purpose:** Generates responses from retrieved context.

**Options:**
- `gpt-4` - Highest quality responses
- `gpt-4-turbo` - Faster, cost-effective
- `gpt-3.5-turbo` - Budget option

**Environment Variable:**
```env
RAG_CHAT_MODEL=gpt-4
```

**Example:**
```php
'ai' => [
    'chat_model' => env('RAG_CHAT_MODEL', 'gpt-4'),
],
```

## Chunking Strategy

### Target Tokens

**Purpose:** Target size for each chunk (in tokens).

**Default:** 800 tokens (~600 words)

**Guidelines:**

| Tokens | Words | Best For |
|--------|-------|----------|
| 400-600 | 300-450 | Technical docs, precise retrieval |
| 800-1000 | 600-750 | General content (recommended) |
| 1200-1500 | 900-1125 | Long-form, narrative content |

**Environment Variable:**
```env
RAG_CHUNK_TARGET_TOKENS=800
```

**Trade-offs:**
- **Smaller chunks:**
  - ✅ More precise retrieval
  - ✅ Less noise
  - ❌ May lose context
  - ❌ More chunks to process

- **Larger chunks:**
  - ✅ More context preserved
  - ✅ Fewer chunks
  - ❌ Less precise retrieval
  - ❌ May include irrelevant info

### Overlap Tokens

**Purpose:** Number of tokens that overlap between consecutive chunks.

**Default:** 120 tokens (~90 words)

**Guidelines:**
- **No overlap (0):** Risk losing context at boundaries
- **Small overlap (50-80):** Minimal redundancy
- **Medium overlap (100-150):** **Recommended** - Good balance
- **Large overlap (200+):** Maximum context preservation

**Environment Variable:**
```env
RAG_CHUNK_OVERLAP_TOKENS=120
```

**Example:**
```
Chunk 1: [The quick brown ... 800 tokens]
          ↓ 120 token overlap
Chunk 2:         [fox jumps ... 800 tokens]
```

## Retrieval Settings

### Top K

**Purpose:** Number of most relevant chunks to retrieve per query.

**Default:** 5

**Guidelines:**

| Value | Use Case |
|-------|----------|
| 3-5 | Quick answers, focused responses |
| 5-10 | Balanced (recommended) |
| 10-20 | Comprehensive coverage |
| 20+ | Research, exhaustive search |

**Environment Variable:**
```env
RAG_RETRIEVAL_TOP_K=5
```

**Trade-offs:**
- **Lower K:**
  - ✅ Faster queries
  - ✅ More focused
  - ❌ May miss relevant info

- **Higher K:**
  - ✅ More comprehensive
  - ✅ Less chance of missing info
  - ❌ Slower queries
  - ❌ More noise

### Hybrid Search

**Purpose:** Combines semantic (vector) and keyword (full-text) search.

#### Enabled

**Default:** `true`

**Environment Variable:**
```env
RAG_HYBRID_ENABLED=true
```

**When to Enable:**
- Need exact keyword matching
- Technical terminology
- Proper nouns, codes, IDs
- Multilingual content

**When to Disable:**
- Pure semantic search needed
- Performance optimization
- Simpler use cases

#### Semantic Weight

**Purpose:** Weight given to vector similarity scores.

**Default:** 0.7 (70%)

**Range:** 0.0 to 1.0

**Environment Variable:**
```env
RAG_HYBRID_SEMANTIC_WEIGHT=0.7
```

**Guidelines:**
- **0.8-1.0:** Prioritize meaning/context
- **0.6-0.8:** Balanced (recommended)
- **0.3-0.6:** More keyword-focused
- **0.0-0.3:** Primarily keyword search

#### Keyword Weight

**Purpose:** Weight given to full-text search scores.

**Default:** 0.3 (30%)

**Range:** 0.0 to 1.0

**Environment Variable:**
```env
RAG_HYBRID_KEYWORD_WEIGHT=0.3
```

**Note:** `semantic_weight + keyword_weight` should equal 1.0

**Examples:**

```php
// Semantic-heavy (70/30)
'hybrid' => [
    'semantic_weight' => 0.7,
    'keyword_weight' => 0.3,
],

// Balanced (50/50)
'hybrid' => [
    'semantic_weight' => 0.5,
    'keyword_weight' => 0.5,
],

// Keyword-heavy (30/70)
'hybrid' => [
    'semantic_weight' => 0.3,
    'keyword_weight' => 0.7,
],
```

## Cache Configuration

### Enabled

**Purpose:** Enable/disable query result caching.

**Default:** `true`

**Environment Variable:**
```env
RAG_CACHE_ENABLED=true
```

**Benefits:**
- ✅ Faster repeated queries
- ✅ Reduced API costs
- ✅ Better user experience

**Considerations:**
- ❌ Stale results if data changes
- ❌ Memory/storage usage

### Prefix

**Purpose:** Namespace for cache keys.

**Default:** `rag:v1`

**Environment Variable:**
```env
RAG_CACHE_PREFIX=rag:v1
```

**Usage:**
- Change when upgrading to invalidate old cache
- Separate environments (dev/staging/prod)
- Multiple RAG instances

**Examples:**
```env
RAG_CACHE_PREFIX=rag:v1
RAG_CACHE_PREFIX=rag:dev:v1
RAG_CACHE_PREFIX=rag:team-123:v1
```

### TTL (Time To Live)

**Purpose:** How long cached results remain valid (in seconds).

**Default:** 1209600 (14 days)

**Environment Variable:**
```env
RAG_CACHE_TTL_SECONDS=1209600
```

**Common Values:**

| Duration | Seconds | Use Case |
|----------|---------|----------|
| 1 hour | 3600 | Frequently changing data |
| 1 day | 86400 | Daily updates |
| 1 week | 604800 | Weekly updates |
| 2 weeks | 1209600 | Default, stable content |
| 30 days | 2592000 | Rarely changing data |

## Audit Logging

### Enabled

**Purpose:** Log all queries to the database for audit trail.

**Default:** `true`

**Environment Variable:**
```env
RAG_AUDIT_ENABLED=true
```

**What Gets Logged:**
- Question asked
- Retrieved chunks (with scores and ranks)
- Timestamp
- Tenant ID (if multi-tenant)
- Metadata

**Database Table:** `rag_queries` and `rag_query_chunks`

**Benefits:**
- ✅ Query analytics
- ✅ Debug retrieval issues
- ✅ Compliance/audit requirements
- ✅ Usage patterns analysis

**Considerations:**
- ❌ Database growth
- ❌ Minor performance impact

**Cleanup Strategy:**
```php
// Delete old audit logs
use Akira\Rag\Models\RagQuery;

RagQuery::where('created_at', '<', now()->subMonths(6))
    ->delete();
```

## Multi-Tenancy

### Enabled

**Purpose:** Enable tenant isolation for multi-tenant applications.

**Default:** `false` (single-tenant)

**Environment Variable:**
```env
RAG_TENANCY_ENABLED=false
```

**When to Enable:**
- SaaS applications
- Multiple organizations
- Isolated data requirements

### Resolver

**Purpose:** Class that resolves the current tenant ID.

**Default:** `\Akira\Rag\Tenant\NullTenantResolver::class`

**Custom Implementation:**
```php
namespace App\Rag;

use Akira\Rag\Tenant\TenantResolver;

class MyTenantResolver implements TenantResolver
{
    public function resolve(): ?string
    {
        return auth()->user()?->organization_id;
    }
}
```

**Configuration:**
```php
'tenancy' => [
    'enabled' => true,
    'resolver' => \App\Rag\MyTenantResolver::class,
],
```

See [Multi-tenancy Guide](06-Tenancy.md) for details.

### Tenant Column

**Purpose:** Name of the tenant ID column in database tables.

**Default:** `tenant_id`

**Configuration:**
```php
'tenancy' => [
    'tenant_column' => 'tenant_id',
],
```

**Custom Column:**
```php
'tenancy' => [
    'tenant_column' => 'organization_id',
],
```

## Environment Variables

Complete `.env` example:

```env
# AI Models
RAG_EMBEDDING_MODEL=text-embedding-3-small
RAG_CHAT_MODEL=gpt-4

# Chunking
RAG_CHUNK_TARGET_TOKENS=800
RAG_CHUNK_OVERLAP_TOKENS=120

# Retrieval
RAG_RETRIEVAL_TOP_K=5
RAG_HYBRID_ENABLED=true
RAG_HYBRID_SEMANTIC_WEIGHT=0.7
RAG_HYBRID_KEYWORD_WEIGHT=0.3

# Cache
RAG_CACHE_ENABLED=true
RAG_CACHE_PREFIX=rag:v1
RAG_CACHE_TTL_SECONDS=1209600

# Audit
RAG_AUDIT_ENABLED=true

# Tenancy
RAG_TENANCY_ENABLED=false
```

## Configuration Scenarios

### Scenario 1: High-Precision Technical Documentation

```php
'ai' => [
    'embedding_model' => 'text-embedding-3-large',
],
'chunking' => [
    'target_tokens' => 600,      // Smaller chunks
    'overlap_tokens' => 100,     // Less overlap
],
'retrieval' => [
    'top_k' => 3,                // Few, precise results
    'hybrid' => [
        'enabled' => true,
        'semantic_weight' => 0.5, // Balanced
        'keyword_weight' => 0.5,
    ],
],
```

### Scenario 2: General Knowledge Base

```php
'ai' => [
    'embedding_model' => 'text-embedding-3-small',
],
'chunking' => [
    'target_tokens' => 800,      // Default
    'overlap_tokens' => 120,
],
'retrieval' => [
    'top_k' => 5,
    'hybrid' => [
        'enabled' => true,
        'semantic_weight' => 0.7, // Semantic-focused
        'keyword_weight' => 0.3,
    ],
],
```

### Scenario 3: Legal Documents (Keyword-Heavy)

```php
'chunking' => [
    'target_tokens' => 1000,     // Larger chunks for context
    'overlap_tokens' => 150,
],
'retrieval' => [
    'top_k' => 10,               // More results
    'hybrid' => [
        'enabled' => true,
        'semantic_weight' => 0.3, // Keyword-heavy
        'keyword_weight' => 0.7,
    ],
],
```

### Scenario 4: Real-time Support (Performance)

```php
'ai' => [
    'embedding_model' => 'text-embedding-3-small',
    'chat_model' => 'gpt-3.5-turbo',
],
'retrieval' => [
    'top_k' => 3,                // Fewer chunks
],
'cache' => [
    'enabled' => true,
    'ttl_seconds' => 3600,       // 1 hour cache
],
```

## Validation

The package validates configuration at runtime:

```php
use Akira\Rag\Exceptions\ConfigurationException;

try {
    // Invalid config will throw exception
} catch (ConfigurationException $e) {
    echo $e->getMessage();
}
```

**Previous**: [03 Commands](03-Commands.md) | **Next**: [05 Database](05-Database.md)
