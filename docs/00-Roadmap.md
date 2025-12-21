# Roadmap

This document describes the **future direction** of `akira/laravel-rag`.

It is intentionally **non-binding**:

- no dates
- no versions
- no guarantees

The goal is to communicate intent and architectural direction, not timelines.

---

## Core RAG Capabilities

### Retrieval Improvements

- Advanced hybrid retrieval strategies
- Query reformulation and expansion
- Adaptive top-k selection based on question complexity
- Context window optimization

### Chunking & Preprocessing

- Semantic-aware chunk boundaries
- Language-aware chunking
- Structured document chunking (headings, articles, sections)
- Adaptive chunk sizes based on content type

### Embeddings

- Multiple embedding models per knowledge base
- Embedding versioning and comparison
- Background re-embedding strategies
- Cost-aware embedding policies

---

## Knowledge Base Management

### Versioning & Snapshots

- Knowledge base snapshots
- Diffing between knowledge versions
- Rollback to previous states
- Read-only historical queries

### Import Sources

- Web / HTML ingestion
- API-based ingestion
- Database-backed ingestion
- Incremental ingestion for large sources

### Consistency & Integrity

- Duplicate detection improvements
- Orphaned chunk and embedding detection
- Integrity checks during ingest and restore
- Safer restore previews

---

## Query & Answer Quality

### Answer Structure

- Structured answers (sections, bullet points)
- Optional machine-readable outputs
- Confidence calibration improvements
- Partial answers with uncertainty markers

### Citations

- Fine-grained citation mapping
- Citation confidence scoring
- Source reliability weighting
- Multiple citation formats

---

## Performance & Scalability

### Storage & Indexing

- Alternative vector indexing strategies
- Index rebuild automation
- Partitioning strategies
- Storage optimization for large datasets

### Caching

- Multi-layer caching strategies
- Pre-warming of frequent queries
- Cost-aware caching policies
- Cache invalidation improvements

---

## Observability & Operations

### Monitoring

- Extended metrics
- Latency breakdowns
- Throughput monitoring
- Cache efficiency metrics

### Diagnostics

- Extended health checks
- Environment compatibility checks
- Automated recovery suggestions
- Safer destructive operation flows

---

## Security & Compliance

- Fine-grained access control for commands
- Backup encryption improvements
- Key rotation strategies
- Redaction of sensitive content
- Safer multi-tenant isolation guarantees

---

## Developer Experience

### Extensibility

- Clear extension points
- Hooks and lifecycle events
- Custom ranking and scoring pipelines

### Tooling

- Better local debugging tools
- Improved test utilities
- Example-driven documentation

---

## Guiding Principles

This roadmap prioritizes:

- correctness over convenience
- explicit design over magic
- composability over monoliths
- production-readiness over demos

Features are added only when they fit naturally within these constraints.