# Asking

Asking retrieves relevant chunks and returns a deterministic (mock) answer for reproducibility.

Public API:

```php
$answer = Rag::ask('What does the document say about X?');
```

With filters:

```php
$answer = Rag::ask(
    'Question',
    filters: ['document_id' => $documentId],
);
```

Return payload:
- `answer` (string)
- `chunks` (list of `{id, score}`)
- `query_id` (UUID)

Cache:
- Controlled by `cache.enabled`, `ttl_seconds` and `prefix`.

Audit:
- When `audit.enabled = true`, the query and retrieved chunks are persisted.

Filtering
- Supported filter: `document_id` (limits candidate chunks to a document).
- Extend retrieval in your app by post‑filtering RagManager results if needed.

Caching
- Cache key includes tenant id (or null in single‑tenant), question, and filters.
- Configure via `cache.enabled`, `ttl_seconds`, and `prefix`.

Previous: [06 Ingestion](06-Ingestion.md) | Next: [08 Cache and Audit](08-Cache-and-Audit.md)
