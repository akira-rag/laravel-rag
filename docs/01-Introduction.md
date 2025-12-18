# Introduction

Akira RAG for Laravel is a production‑ready Retrieval‑Augmented Generation (RAG) package for Laravel 12+ with a strong focus on deterministic behavior, clean architecture, and explicit configuration.

It uses:
- PostgreSQL + pgvector for vector similarity and HNSW indexing
- PostgreSQL tsvector for keyword search
- PrismPHP for code highlighting integration points
- Spatie Laravel Data for typed DTOs

Core properties:
- Clean Architecture (Facade → Service → Manager)
- Single‑tenant by default, opt‑in multi‑tenant via resolver
- Deterministic chunking and ingestion idempotency
- Explicit configuration (`config/rag.php`) and no hidden globals
- Cache and audit logging built in

Public API (Facade):

```php
use Akira\\Rag\\Facades\\Rag;

Rag::ingest([
    'title' => 'Labor Law',
    'source_type' => 'law',
    'source_ref' => 'law:2021:123',
    'content' => $text,
    'meta' => ['lang' => 'en'],
]);

$answer = Rag::ask('What are the notice periods?');
```

Read next to install and configure the package.

Next: [02 Installation](02-Installation.md)
