# Introduction

Akira RAG for Laravel is a production-ready Retrieval-Augmented Generation (RAG) package designed for Laravel 12+
applications. It combines the power of vector similarity search with traditional keyword search to provide accurate,
context-aware responses.

## What is RAG?

Retrieval-Augmented Generation (RAG) is an AI pattern that enhances large language models by retrieving relevant
information from a knowledge base before generating responses. Instead of relying solely on the model's training data,
RAG:

1. **Retrieves** relevant documents from your knowledge base
2. **Augments** the query with retrieved context
3. **Generates** accurate, grounded responses

## Key Features

### Clean Architecture

- **Layered Design**: Facade → Service → Manager pattern
- **Dependency Injection**: All dependencies are injected, no globals
- **Single Responsibility**: Each class has one clear purpose
- **Testable**: Easy to mock and test in isolation

```php
// Clean facade API
use Akira\Rag\Facades\Rag;

Rag::ingest([...]);  // Stores documents
Rag::ask('...');     // Retrieves & generates
```

### Type Safety

- **Full PHP 8.4 Types**: All methods use type hints
- **Custom Exceptions**: Specific exception classes for every error scenario
- **Data Transfer Objects**: Spatie Laravel Data for validated payloads
- **Static Analysis**: PHPStan level max compatibility

```php
use Akira\Rag\Data\IngestPayload;
use Akira\Rag\Exceptions\InvalidPayload;

try {
    $payload = new IngestPayload(
        title: 'Document',
        source_type: 'manual',
        source_ref: 'doc:001',
        content: $text
    );
} catch (InvalidPayload $exception) {
    // Handle validation errors
}
```

### Multi-Tenant Support

- **Single-tenant by default**: Zero configuration needed
- **Opt-in multi-tenancy**: Enable when you need it
- **Custom Resolvers**: Implement your own tenant resolution logic
- **Automatic Scoping**: All queries are tenant-scoped automatically

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

### Performance Optimized

- **HNSW Indexing**: Fast approximate nearest neighbor search
- **Query Caching**: Configurable result caching (default 14 days)
- **Hybrid Search**: Combines semantic and keyword search
- **Batch Operations**: Efficient bulk ingestion

### Deterministic & Idempotent

- **Consistent Chunking**: Same content → same chunks
- **Hash-based Deduplication**: Automatic duplicate detection
- **Reproducible Results**: Predictable behavior for testing
- **Audit Trail**: Track all queries and results

```php
// Same payload = same result
$result1 = Rag::ingest(['title' => 'Doc', 'content' => $text, ...]);
$result2 = Rag::ingest(['title' => 'Doc', 'content' => $text, ...]);

// Returns existing document
assert($result1['document_id'] === $result2['document_id']);
```

## Technology Stack

### Database: PostgreSQL + pgvector

- **Vector Storage**: Native vector type with efficient storage
- **HNSW Index**: Fast approximate nearest neighbor search
- **Full-Text Search**: Built-in `tsvector` for keyword matching
- **ACID Compliance**: Reliable transactions

```sql
-- Automatic migration setup
CREATE
EXTENSION IF NOT EXISTS vector;

CREATE INDEX ON rag_embeddings
    USING hnsw (embedding vector_cosine_ops);
```

### Framework Integration

- **Laravel 12+**: Modern Laravel features
- **Queue Support**: Async embedding generation
- **Cache Integration**: Uses Laravel's cache system
- **Event System**: Hooks for custom logic

### Dependencies

- **PrismPHP**: AI/LLM integration layer
- **Spatie Laravel Data**: Type-safe DTOs
- **pgvector**: PostgreSQL extension for vectors

## Core Concepts

### Documents

Documents are the base unit of knowledge:

```php
[
    'id' => 'uuid',                    // Auto-generated
    'title' => 'Labor Law 2024',       // Required
    'source_type' => 'law',            // Required (categorization)
    'source_ref' => 'law:2024:001',    // Required (external reference)
    'hash' => 'sha256...',             // Auto-generated (idempotency)
    'meta' => ['lang' => 'en'],        // Optional metadata
    'created_at' => '...',             // Auto-generated
]
```

### Chunks

Large documents are split into manageable chunks:

```php
[
    'id' => 'uuid',                    // Auto-generated
    'document_id' => 'uuid',           // Parent document
    'position' => 0,                   // Order in document
    'content' => 'Text content...',    // Actual text
    'meta' => [],                      // Optional metadata
]
```

**Chunking Strategy**:

- Token-based splitting (configurable)
- Overlap between chunks (prevents context loss)
- Deterministic results

### Embeddings

Numeric representations of text for semantic search:

```php
[
    'id' => 'uuid',
    'chunk_id' => 'uuid',
    'embedding' => [0.1, -0.5, ...],  // 1536-dim vector (OpenAI)
    'meta' => [],
]
```

### Queries

All user questions are logged for audit:

```php
[
    'id' => 'uuid',
    'question' => 'What are the notice periods?',
    'chunks' => [...],                 // Retrieved chunks with scores
    'meta' => [],
    'created_at' => '...',
]
```

## Public API

The package exposes a clean Facade API:

```php
use Akira\Rag\Facades\Rag;

// Ingest: Store documents in knowledge base
$result = Rag::ingest([
    'title' => 'Labor Law',
    'source_type' => 'law',
    'source_ref' => 'law:2021:123',
    'content' => $fullLawText,
    'meta' => ['lang' => 'en', 'year' => 2021],
]);
// Returns: ['document_id' => '...', 'chunks' => 5]

// Ask: Query the knowledge base
$answer = Rag::ask(
    question: 'What are the notice periods?',
    filters: ['source_type' => 'law']  // Optional filtering
);
// Returns: [
//     'answer' => 'According to the law...',
//     'chunks' => [['id' => '...', 'score' => 0.95], ...],
//     'query_id' => '...'
// ]
```

## Configuration Philosophy

Akira RAG follows an **explicit configuration** approach:

- **No Hidden Defaults**: All behavior is configurable
- **Environment Variables**: Support for `.env` configuration
- **Type-Safe Config**: Configuration is validated at runtime
- **Documentation**: Every config key is documented

```php
// config/rag.php
return [
    'ai' => [
        'embedding_model' => env('RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chat_model' => env('RAG_CHAT_MODEL', 'gpt-4'),
    ],
    'chunking' => [
        'target_tokens' => 800,      // ~600 words
        'overlap_tokens' => 120,     // ~90 words overlap
    ],
    'retrieval' => [
        'top_k' => 5,                // Return top 5 chunks
    ],
];
```

## Use Cases

### Legal Document Search

Store laws, regulations, and case law. Query with natural language.

### Customer Support

Build a knowledge base from support articles, FAQs, and documentation.

### Internal Documentation

Make company policies, procedures, and handbooks searchable.

### Research Assistant

Index research papers, articles, and notes for quick retrieval.

### Compliance

Track regulatory documents and quickly find relevant sections.

## Next Steps

Ready to get started? Follow the installation guide:

**Next**: [02 Installation](02-Installation.md)

## Additional Resources

- [Configuration Guide](04-Configuration.md) - Detailed config options
- [Ingestion Guide](07-Ingestion.md) - How to add documents
- [Asking Guide](08-Asking.md) - How to query documents
- [Multi-tenancy](06-Tenancy.md) - Set up tenant isolation
- [Recipes](10-Recipes.md) - Common patterns and examples
