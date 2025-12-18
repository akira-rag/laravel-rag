# Configuration

The `config/rag.php` file controls all aspects of the package.

- Tenancy
  - `tenancy.enabled`: enable multi‑tenant mode.
  - `tenancy.tenant_column`: tenant column name (nullable UUID in all tables).
  - `tenancy.resolver`: class implementing `Akira\\Rag\\Tenant\\TenantResolver`.
- Models
  - `models.*`: Eloquent models for document, chunk, embedding, and query.
- AI
  - `ai.*`: string identifiers for embedding/chat/rerank models (configure providers in your app).
- Chunking
  - `chunking.target_tokens`: desired tokens per chunk (approx. by words).
  - `chunking.overlap_tokens`: overlap between chunks.
- Retrieval
  - `retrieval.top_k`: number of chunks returned.
  - `retrieval.candidate_k`: intermediate candidate pool.
  - `retrieval.hybrid.*`: enable semantic+keyword blending; `ts_config` for FTS.
- Prompt
  - `prompt.system`, `prompt.language`: system guidance and language.
- Cache
  - `cache.enabled`, `ttl_seconds`, `prefix`: response caching behavior.
- Audit
  - `audit.enabled`, `store_prompts`: persist queries and optionally prompts.
- Queue
  - `queue.connection`, `queue.queue`: reserved for background jobs.

Example (multi‑tenant with Jetstream):

```php
// config/rag.php
'tenancy' => [
    'enabled' => true,
    'resolver' => App\\Rag\\Tenant\\JetstreamTenantResolver::class,
];

final class JetstreamTenantResolver implements \\Akira\\Rag\\Tenant\\TenantResolver
{
    public function resolve(): ?string
    {
        return auth()->user()?->currentTeam?->id;
    }
}
```

Advanced notes:
- `tenancy.tenant_column` defaults to `tenant_id` and must be present (nullable UUID) on all tables.
- `ai` keys are string identifiers only; you wire actual LLM providers in your app.
- `retrieval.hybrid.ts_config` controls PostgreSQL text search config (e.g., `simple`, `english`).
- `cache.enabled` and `audit.enabled` can be toggled at runtime for environments.
- `queue` is reserved for future background jobs; null defaults to app settings.

Previous: [03 Commands](03-Commands.md) | Next: [05 Database](05-Database.md)
