# Cache and Audit

Cache
- Enable with `cache.enabled`.
- Tune `ttl_seconds` and `prefix` in `config/rag.php`.

Audit
- Enable with `audit.enabled`.
- Controls storage of queries (`rag_queries`) and results (`rag_query_chunks`).
- `store_prompts` controls whether prompts are persisted.

Cache key structure
- `rag:v1:ask:{sha1(json_encode([tenant, question, filters]))}` with configurable prefix.

Audit records
- `rag_queries`: stores the question and tenant context.
- `rag_query_chunks`: stores top‑k chunk references with score and rank.

Previous: [07 Asking](07-Asking.md) | Next: [10 Recipes](10-Recipes.md)
