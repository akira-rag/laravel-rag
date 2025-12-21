# Akira RAG for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-rag.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-rag)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-rag.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-rag)

A production-ready Retrieval-Augmented Generation (RAG) system for Laravel 12+ with PostgreSQL + pgvector. Built with
clean architecture, type safety, and deterministic behavior.

## Features

- **Clean Architecture** - Facade → Service → Manager pattern
- **Type Safety** - Full type hints and custom exceptions
- **Multi-tenant Support** - Optional tenant isolation with custom resolvers
- **Performance** - HNSW indexing, caching, and optimized queries
- **Idempotent Ingestion** - Hash-based deduplication
- **Audit Logging** - Built-in query tracking
- **Deterministic Chunking** - Configurable token-based splitting
- ️ **CLI Tools** - Rich interactive commands for management

## Requirements

- **PHP:** 8.4+
- **Laravel:** 12+
- **Database:** PostgreSQL 14+ (with pgvector extension)
- **Optional:** SQLite for testing (in-memory)

## Installation

### Quick Install (Recommended)

```bash
composer require akira/laravel-rag
php artisan rag:install
```

The installer will guide you through:

- Publishing configuration files
- Publishing migrations
- Running migrations
- Enabling multi-tenant mode (optional)

### Non-Interactive Install

Perfect for CI/CD pipelines:

```bash
php artisan rag:install \
  --force \
  --with-tenancy \
  --run-migrate \
  --star
```

### Manual Install

```bash
composer require akira/laravel-rag
php artisan vendor:publish --tag="laravel-rag-config"
php artisan vendor:publish --tag="laravel-rag-migrations"
php artisan migrate
```

### PostgreSQL Setup

Ensure pgvector extension is available:

```sql
CREATE
EXTENSION IF NOT EXISTS vector;
```

## Quick Start

### Basic Usage

```php
use Akira\Rag\Facades\Rag;

// Ingest a document
$result = Rag::ingest([
    'title' => 'Labor Law 2024',
    'source_type' => 'law',
    'source_ref' => 'law:2024:001',
    'content' => 'Full text of the labor law...',
    'meta' => ['lang' => 'en', 'year' => 2024],
]);

// Returns: ['document_id' => '...', 'chunks' => 5]

// Ask a question
$answer = Rag::ask('What are the notice periods for termination?');

// Returns: [
//     'answer' => 'According to the law...',
//     'chunks' => [['id' => '...', 'score' => 0.95], ...],
//     'query_id' => '...'
// ]
```

### Advanced Usage

```php
use Akira\Rag\Facades\Rag;

// Filtered retrieval
$answer = Rag::ask(
    question: 'How many vacation days?',
    filters: ['document_id' => '123e4567-e89b-12d3-a456-426614174000']
);

// With custom metadata
Rag::ingest([
    'title' => 'Employee Handbook',
    'source_type' => 'handbook',
    'source_ref' => 'handbook:2024:v2',
    'content' => $content,
    'meta' => [
        'department' => 'HR',
        'version' => 2,
        'effective_date' => '2024-01-01',
        'tags' => ['benefits', 'policies']
    ],
]);
```

### Using Data Transfer Objects

```php
use Akira\Rag\Data\IngestPayload;
use Akira\Rag\Data\AskPayload;

// Type-safe ingestion
$payload = new IngestPayload(
    title: 'Safety Guidelines',
    source_type: 'manual',
    source_ref: 'safety:2024',
    content: $text,
    meta: ['category' => 'safety']
);

Rag::ingest($payload->toArray());

// Type-safe asking
$askPayload = new AskPayload(
    question: 'What are the fire safety procedures?',
    filters: ['category' => 'safety'],
    meta: ['user_id' => auth()->id()]
);

Rag::ask($askPayload->question, $askPayload->filters);
```

## CLI Commands

### Interactive Commands

All commands support rich interactive prompts:

```bash
# Install and configure
php artisan rag:install

# Ingest content
php artisan rag:ingest

# View statistics
php artisan rag:stats

# Export knowledge base
php artisan rag:export

# Import from backup
php artisan rag:restore backup.json.gz

# Create backups
php artisan rag:backup

# Re-embed documents
php artisan rag:reembed --all

# Import PDF files
php artisan rag:import:pdf documents/
```

### Non-Interactive Mode

Perfect for automation:

```bash
# Ingest with options
php artisan rag:ingest \
  --title="My Document" \
  --source_type=manual \
  --source_ref=doc:001 \
  --text="Content here..."

# Export with encryption
php artisan rag:export \
  --output=backup.json \
  --include-embeddings \
  --compress \
  --encrypt

# Stats as JSON
php artisan rag:stats --json
```

## Configuration

The `config/rag.php` file provides extensive configuration options:

```php
return [
    // AI Models
    'ai' => [
        'embedding_model' => env('RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chat_model' => env('RAG_CHAT_MODEL', 'gpt-4'),
    ],

    // Chunking Strategy
    'chunking' => [
        'target_tokens' => 800,
        'overlap_tokens' => 120,
    ],

    // Retrieval Settings
    'retrieval' => [
        'top_k' => 5,
        'hybrid' => [
            'enabled' => true,
            'semantic_weight' => 0.7,
            'keyword_weight' => 0.3,
        ],
    ],

    // Caching
    'cache' => [
        'enabled' => true,
        'prefix' => 'rag:v1',
        'ttl_seconds' => 1209600, // 14 days
    ],

    // Audit Logging
    'audit' => [
        'enabled' => true,
    ],

    // Multi-tenancy
    'tenancy' => [
        'enabled' => false,
        'resolver' => \Akira\Rag\Tenant\NullTenantResolver::class,
        'tenant_column' => 'tenant_id',
    ],
];
```

## Multi-Tenancy

### Setup

1. Enable tenancy in config:

```php
'tenancy' => [
    'enabled' => true,
    'resolver' => \App\Rag\MyTenantResolver::class,
    'tenant_column' => 'tenant_id',
],
```

2. Create a tenant resolver:

```php
namespace App\Rag;

use Akira\Rag\Tenant\TenantResolver;

class MyTenantResolver implements TenantResolver
{
    public function resolve(): ?string
    {
        // Return current tenant ID
        return auth()->user()?->tenant_id;
    }
}
```

### Usage

Once configured, all operations are automatically scoped to the current tenant:

```php
// Ingestion is automatically scoped
Rag::ingest([...]);

// Retrieval is automatically scoped
$answer = Rag::ask('...');

// Stats are tenant-specific
$stats = Rag::stats();
```

## Exception Handling

The package provides specific exceptions for different scenarios:

```php
use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Exceptions\InvalidQuestionException;
use Akira\Rag\Exceptions\DocumentNotFoundException;

try {
    Rag::ingest([
        'title' => 'Test',
        // Missing required fields
    ]);
} catch (InvalidPayload $e) {
    // Handle validation errors
    // $e->getMessage() returns descriptive error
}

try {
    Rag::ask('');
} catch (InvalidQuestionException $e) {
    // Handle empty question
}
```

### Available Exceptions

- **Validation**: `InvalidPayload`, `InvalidQuestionException`
- **Resources**: `DocumentNotFoundException`, `ChunkNotFoundException`
- **Processing**: `ChunkingException`, `EmbeddingException`, `RetrievalException`
- **Import/Export**: `ExportException`, `ImportException`
- **Infrastructure**: `CacheException`, `DatabaseException`, `ConfigurationException`
- **Tenancy**: `TenantResolverException`

See [Exception Documentation](src/Exceptions/README.md) for complete details.

## Testing

```bash
composer test
```

### Writing Tests

```php
use Akira\Rag\Facades\Rag;

it('can ingest and retrieve documents', function () {
    $result = Rag::ingest([
        'title' => 'Test Document',
        'source_type' => 'test',
        'source_ref' => 'test:001',
        'content' => 'This is a test document about Laravel RAG.',
        'meta' => ['category' => 'test'],
    ]);

    expect($result)
        ->toHaveKeys(['document_id', 'chunks'])
        ->and($result['chunks'])->toBeGreaterThan(0);

    $answer = Rag::ask('What is this test about?');

    expect($answer)
        ->toHaveKeys(['answer', 'chunks', 'query_id'])
        ->and($answer['chunks'])->not->toBeEmpty();
});
```

## Architecture

```
┌─────────────────────────────────────────┐
│           Facade (Rag)                  │
│    Public API Entry Point               │
└─────────────┬───────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│         RagService                      │
│    Business Logic Layer                 │
└─────────────┬───────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│         RagManager                      │
│   Core Operations & DB Access           │
│   - Ingestion & Chunking                │
│   - Retrieval & Caching                 │
│   - Tenant Scoping                      │
└─────────────┬───────────────────────────┘
              │
        ┌─────┴─────┬──────────────┐
        ▼           ▼              ▼
   ┌────────┐  ┌────────┐    ┌──────────┐
   │ Models │  │ Cache  │    │ Database │
   └────────┘  └────────┘    └──────────┘
```

## Documentation

For detailed documentation, visit:

- [Full Documentation](docs/01-Introduction.md)
- [Configuration Guide](docs/04-Configuration.md)
- [Multi-tenancy Setup](docs/06-Tenancy.md)
- [Ingestion Guide](docs/07-Ingestion.md)
- [Asking Queries](docs/08-Asking.md)
- [Recipes & Examples](docs/10-Recipes.md)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security vulnerabilities, please review [our security policy](../../security/policy).

## Credits

- [kid](https://github.com/kidiatoliny)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
