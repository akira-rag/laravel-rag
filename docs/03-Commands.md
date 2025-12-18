# 09 — CLI Commands

This single guide documents all Artisan commands shipped by the package. Each command supports interactive prompts (Laravel Prompts) and non‑interactive flags. All commands are tenant‑aware and never accept a `tenant_id`; tenancy is resolved internally by TenantContext.

Contents
- [09.1 — rag:ingest](#091--ragingest)
- [09.2 — rag:reembed](#092--ragreembed)
- [09.3 — rag:stats](#093--ragstats)

Previous: [02 Installation](02-Installation.md) | Next: [04 Configuration](04-Configuration.md)

---

## 09.1 — rag:ingest

Purpose
- Ingest content into the RAG knowledge base, creating a document with deterministic chunks and placeholder embeddings. Idempotent by content hash.

Signature
```
rag:ingest
    {--title=}
    {--source_type=}
    {--source_ref=}
    {--text=}
    {--file=}
    {--meta=*}
    {--no-embed : Store document and chunks but skip embedding job dispatch}
    {--sync : Run embedding synchronously (no queue), if supported}
```

Options
- `--title`: Document title (required).
- `--source_type`: One of law, pdf, web, faq, ticket, note (required).
- `--source_ref`: Optional source reference.
- `--text`: Raw content text.
- `--file`: Path to `.txt` or `.md` file.
- `--meta`: Repeatable key=value pairs (e.g. `--meta=lang=en --meta=country=CV`).
- `--no-embed`: Skip embedding dispatch.
- `--sync`: Run embedding synchronously (if supported in your app).

Rules
- Provide either `--text` or `--file`.
- `.txt` and `.md` are supported for `--file`.
- Never pass `tenant_id` (tenant is resolved internally).

Interactive flow
- Prompts for title, source type (select), and optional source reference.
- Choose ingestion source: “Paste text” or “Read from file”.
- If file, prompts for a `.txt`/`.md` path and validates existence.
- Meta pairs: repeat key/value prompts until blank.

Examples
```
php artisan rag:ingest \
  --title="Labor Law 2021" \
  --source_type=law \
  --source_ref=law:2021:123 \
  --file=storage/docs/labor-law.txt \
  --meta=lang=en \
  --meta=country=CV

php artisan rag:ingest \
  --title="FAQ Contracts" \
  --source_type=faq \
  --text="Contracts must be signed by..." \
  --meta=lang=en

php artisan rag:ingest --sync \
  --title="Internal Note" \
  --source_type=note \
  --text="This is internal guidance..."

php artisan rag:ingest \
  --title="Web Capture" \
  --source_type=web \
  --text="Policy: Access is restricted" \
  --meta=lang=en \
  --meta=category=policy

php artisan rag:ingest \
  --title="Onboarding" \
  --source_type=faq \
  --file=docs/onboarding.md

php artisan rag:ingest \
  --title="Support Tickets" \
  --source_type=ticket \
  --text="Ticket response guidelines"
```

Expected output
```
Document ID        3fa85f64-5717-4562-b3fc-2c963f66afa6
Chunks             7
Embedding          dispatched | synced | skipped
```

Common errors and fixes
- File not found → check the path and permissions.
- Unsupported extension → use `.txt` or `.md`.
- Missing required fields → run interactively or provide flags.

Tenancy notes
- Single‑tenant: `tenant_id` is null.
- Multi‑tenant: tenant is resolved via your `TenantResolver`.

Queue notes
- `--sync` performs embedding inline if supported in your app.
- `--no-embed` stores document/chunks without enqueuing embedding.

Safety
- Idempotent by content hash; safe to re‑run with identical content.

Next: [09.2 — rag:reembed](#092--ragreembed)

---

## 09.2 — rag:reembed

Purpose
- Rebuild embeddings for existing documents. Safe to run multiple times.

Signature
```
rag:reembed
    {--document_id=}
    {--all}
    {--sync : Run embedding synchronously (no queue), if supported}
    {--model= : Override embedding model for this run}
```

Options
- `--document_id`: Reembed a single document by UUID.
- `--all`: Reembed all documents for the current tenant.
- `--sync`: Rebuild synchronously if supported in your app.
- `--model`: Override the embedding model just for this run.

Rules
- Provide either `--document_id` or `--all`.
- Never pass `tenant_id`.

Interactive flow
- If neither option is provided, prompts whether to process “All documents” or “Single document”. If single, prompts for the UUID.

Examples
```
php artisan rag:reembed --all
php artisan rag:reembed --document_id=3fa85f64-5717-4562-b3fc-2c963f66afa6
php artisan rag:reembed --all --model=text-embedding-3-small
php artisan rag:reembed --document_id=3fa85f64-5717-4562-b3fc-2c963f66afa6 --sync
php artisan rag:reembed --all --sync
php artisan rag:reembed --document_id=... --model=text-embedding-3-small
```

Expected output
```
Documents affected   12
Embeddings recreated 0
Model override       text-embedding-3-small
Mode                 queue | sync
```

Common errors and fixes
- Missing target → supply `--document_id` or `--all`, or run interactively.

Tenancy notes
- Scoped by current tenant via the global scope.
- Single‑tenant mode processes only records with `tenant_id = NULL`.

Safety
- Idempotent; re‑running will not duplicate embeddings.

Previous: [09.1 — rag:ingest](#091--ragingest) | Next: [09.3 — rag:stats](#093--ragstats)

---

## 09.3 — rag:stats

Purpose
- Show tenant‑scoped statistics about the knowledge base.

Signature
```
rag:stats
    {--json : Output as JSON}
```

Options
- `--json`: Output only the JSON payload.

Output fields
- Documents count
- Chunks count
- Embeddings count
- Knowledge base version
- Tenancy mode and current tenant id
- Configured models (chat, embedding)
- Hybrid retrieval (enabled and weights)

Examples
```
php artisan rag:stats
php artisan rag:stats --json
```

Expected output
```
Documents          4
Chunks             19
Embeddings         19
KB Version         v1
Tenancy            single | multi
Tenant ID          single | <uuid>
Chat Model         gpt-4.1-mini
Embedding Model    text-embedding-3-small
Hybrid             enabled
Hybrid Weights     semantic=0.75 keyword=0.25
```

Tenancy notes
- Single‑tenant shows `single` as tenant id.
- Multi‑tenant shows current resolved tenant id.

Previous: [09.2 — rag:reembed](#092--ragreembed)
