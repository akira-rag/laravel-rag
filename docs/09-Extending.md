# Extending & Internals

This page documents internals, extension points, and how the package works end‑to‑end.

## Architecture

- Facade → Service → Manager
  - `Rag::...` calls the service bound as `akira.rag`.
  - `RagService` is a thin wrapper delegating to `RagManager`.
  - `RagManager` implements ingestion, chunking, retrieval, cache, and audit.
- Tenancy
  - `TenantContext` reads `config('rag.tenancy.*')`, resolves the tenant via `TenantResolver`, and exposes `enabled()`, `column()`, and `current()`.
  - `ScopesTenant` is a global scope that applies tenant filtering to all package models.

## Ingestion Flow

1. Validate payload (required fields, no `tenant_id`).
2. Compute idempotency hash: `sha256(source_type|source_ref|sha256(content))`.
3. If a document with the same hash exists, return it.
4. Create document with `tenant_id` (or null in single‑tenant).
5. Chunk content deterministically using `target_tokens` and `overlap_tokens`.
6. Create chunks and placeholder embeddings (deterministic JSON string for tests).

## Retrieval Flow

1. Validate question.
2. Build a cache key using tenant, question, and filters.
3. Retrieve chunks ordered by `position` and limited by `retrieval.top_k`.
4. Optionally store query + chunk results when `audit.enabled = true`.
5. Return a deterministic mock answer with chunk ids and scores.

## Replacing Models

You can swap models via `config/rag.php` → `models.*` as long as your schema is compatible (UUID PKs, `tenant_id`, `meta` arrays, etc.).

## Implementing a Tenant Resolver

```php
namespace App\Rag\Tenant;

use Akira\Rag\Tenant\TenantResolver;

final class JetstreamTenantResolver implements TenantResolver
{
    public function resolve(): ?string
    {
        return auth()->user()?->currentTeam?->id;
    }
}
```

Update `config/rag.php`:

```php
'tenancy' => [
    'enabled' => true,
    'resolver' => App\\Rag\\Tenant\\JetstreamTenantResolver::class,
];
```

## Swapping the Chunker

You may wrap or replace chunking by decorating `RagManager` or binding your own manager into the container, as long as the public API and determinism are preserved. Ensure tests are updated accordingly.

## PrismPHP Integration

`Akira\\Rag\\Support\\Prism::highlight()` is a placeholder that you can wire to PrismPHP in your application. The package keeps it deterministic and side‑effect free for tests.

## Data Transfer Objects

DTOs use Spatie Laravel Data (`AskPayload`, `IngestPayload`). In this package they are minimal; in your app, you can extend them or add transformers/validation as needed.

## Commands

`rag:install` publishes config/migrations, optionally runs migrations, can toggle tenancy, and includes a GitHub star prompt.

Previous: [08 Cache and Audit](08-Cache-and-Audit.md) | Next: [10 Recipes](10-Recipes.md)
