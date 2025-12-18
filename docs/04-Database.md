# Database

Tables created by the single migration:

- rag_documents (UUID pk, tenant_id, title, source, hash, meta json/jsonb)
- rag_chunks (UUID pk, document_id, position, content, content_tsvector, meta)
- rag_embeddings (UUID pk, chunk_id, embedding vector(1536), meta)
- rag_queries (UUID pk, question, meta)
- rag_query_chunks (UUID pk, query_id, chunk_id, score, rank, meta)

PostgreSQL specifics:
- `rag_chunks.content_tsvector` is generated and GIN‑indexed
- `rag_embeddings.embedding` uses pgvector with HNSW index

SQLite (dev/test):
- json/jsonb columns are mapped to json
- vector and tsvector are represented by compatible text columns

Embedding dimensions
- Default pgvector dimension is 1536. You can alter it by editing the migration statements under PostgreSQL blocks if you need to match a different embedding model.

Full‑text search
- `content_tsvector` is generated from `content` using the configured `ts_config` (default `simple`). You can adjust it in queries or by changing the generated expression.

Previous: [03 Configuration](03-Configuration.md) | Next: [05 Tenancy](05-Tenancy.md)
