# Ingestion

Ingestion stores documents and creates deterministic chunks + embeddings.

Public API (Facade):

```php
Rag::ingest([
    'title' => 'Example',
    'source_type' => 'manual',
    'source_ref' => 'doc:123',
    'content' => $text,
    'meta' => ['lang' => 'en'],
]);
```

Rules:
- `tenant_id` is not accepted in payload (throws exception)
- Idempotency via hash (source_type + source_ref + content)

Chunking:
- `target_tokens` and `overlap_tokens` in `config/rag.php`

Determinism and idempotency
- The document’s idempotency hash is computed as `sha256(source_type|source_ref|sha256(content))`.
- Re‑ingesting the same payload returns the existing document id and chunk count.

Validation rules
- Required: `title`, `source_type`, `source_ref`, `content` (non‑empty strings).
- Forbidden: `tenant_id` — tenancy is resolved internally.

Metadata
- `meta` accepts a JSON‑serializable array for additional attributes.

Previous: [06 Tenancy](06-Tenancy.md) | Next: [08 Asking](08-Asking.md)
