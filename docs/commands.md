# CLI Commands (All-in-one)

This single guide documents all Artisan commands shipped by the package. Every command supports interactive prompts (Laravel Prompts) and non‑interactive flags. All commands are tenant‑aware and never accept a `tenant_id`; tenancy is resolved internally by TenantContext.

Contents
- Install: rag:install
- Ingest: rag:ingest
- Re-embed: rag:reembed
- Stats: rag:stats
- Doctor: rag:doctor
- Export: rag:export
- Backup: rag:backup
- Restore: rag:restore
- Import PDF: rag:import:pdf

---

## rag:install (brief)

Purpose
- Interactive installer that publishes config/migrations, optionally runs migrations, can enable tenancy, and suggests starring the repo.

Signature
```
rag:install
  [--force]
  [--with-tenancy]
  [--run-migrate]
  [--star]
```

Notes
- Uses Laravel Prompts; safe to run multiple times.
- See README for quick usage.

---

## rag:ingest

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

Interactive
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

Multi-tenant vs Single-tenant
- Single‑tenant: `tenant_id` is null.
- Multi‑tenant: resolved via your `TenantResolver`.

Safety & Idempotency
- Idempotent by content hash; safe to re‑run with identical content.

Common errors and fixes
- File not found → check the path and permissions.
- Unsupported extension → use `.txt` or `.md`.
- Missing required fields → run interactively or provide flags.

---

## rag:reembed

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

Interactive
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

Notes
- Tenant‑scoped by global scope.
- Single‑tenant processes `tenant_id = NULL` rows.

Safety & Idempotency
- Idempotent; re‑running will not duplicate embeddings.

Common errors and fixes
- Missing target → supply `--document_id` or `--all`, or run interactively.

---

## rag:stats

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

---

## rag:doctor

Purpose
- Runs environment and configuration checks. Reports issues and suggested fixes.

Signature
```
rag:doctor
```

Examples
```
php artisan rag:doctor
php artisan rag:doctor --quiet
php artisan rag:doctor | tee doctor.log
php artisan rag:doctor && echo OK
php artisan rag:doctor || echo FAIL
php artisan rag:doctor --ansi
```

Expected output
```
Checks             ok=12 warn=1 fail=0
PgVector           present
TSVector           present
Migrations         up-to-date
...
```

---

## rag:export

Purpose
- Export the RAG knowledge base for the current tenant. Read‑only, deterministic, streams large datasets.

Signature
```
rag:export
    {--format=json}
    {--output=}
    {--include-embeddings}
    {--include-audit}
    {--compress}
    {--encrypt : Encrypt export using application key}
```

Options
- `--format`: Only `json` supported.
- `--output`: Target file path (prompted if missing).
- `--include-embeddings`: Include embeddings array.
- `--include-audit`: Include queries.
- `--compress`: Gzip the output.
- `--encrypt`: Encrypt with `APP_KEY` using Laravel Crypt.

Interactive
- Prompts for output path when missing.

Examples
```
php artisan rag:export --output=storage/app/rag/exports/single/export.json
php artisan rag:export --output=storage/app/rag/exports/single/export.json --compress
php artisan rag:export --output=storage/app/rag/exports/team-1/export.json --encrypt
php artisan rag:export --include-embeddings --include-audit --output=export.json
php artisan rag:export --output=export.json --compress --encrypt
php artisan rag:export --format=json --output=export.json
```

Expected output
```
Format             json+gz
Encrypted          yes
Embeddings         included
Audit              included
Output             storage/app/rag/exports/single/export.json.gz
```

Encryption notes
- Encrypted exports use Laravel Crypt with `APP_KEY`.
- Metadata header (version, tenant, timestamp) is included in the JSON payload before encryption/compression.
- Decryption failures (wrong key/corruption) produce a clear error during restore.

---

## rag:backup

Purpose
- Create encrypted, compressed backups. Wraps `rag:export`, enables compression and encryption by default, prunes old backups per tenant.

Signature
```
rag:backup
    {--output=}
    {--retain=7}
    {--no-encryption : Disable encryption explicitly}
```

Interactive
- Suggests default path when `--output` not provided.

Examples
```
php artisan rag:backup
php artisan rag:backup --retain=30
php artisan rag:backup --output=storage/app/rag/backups/team-1/backup.json
php artisan rag:backup --no-encryption
php artisan rag:backup && ls storage/app/rag/backups
php artisan rag:backup --retain=5 --output=storage/app/rag/backups/single/backup.json
```

Expected output
```
Output             storage/app/rag/backups/single/backup.json.gz
Encryption         enabled
Retention          7
```

---

## rag:restore

Purpose
- Restore a RAG knowledge base from a previous backup. Tenant‑scoped, transactional behavior (upserts), with dry‑run validation.

Signature
```
rag:restore
    {path : Path to backup archive}
    {--force : Skip confirmation prompt}
    {--dry-run : Validate backup without restoring}
    {--decrypt : Explicitly decrypt backup before restore}
```

Interactive
- Confirmation required unless `--force`.

Examples
```
php artisan rag:restore storage/app/rag/backups/single/backup-2025-01-15.zip
php artisan rag:restore storage/app/rag/backups/team-123/backup.zip --dry-run
php artisan rag:restore backup.zip --force
php artisan rag:restore storage/app/rag/backups/single/backup.json.gz --decrypt --dry-run
php artisan rag:restore storage/app/rag/backups/single/backup.json.gz --decrypt --force
php artisan rag:restore storage/app/rag/backups/team-1/backup.json.gz --decrypt
```

Expected output
```
Documents          4
Chunks             19
Embeddings         19
Queries            6
```

Safety & Idempotency
- Upserts preserve existing data where possible.
- Dry‑run validates archive and schema compatibility before restore.

Common errors and fixes
- Invalid archive → re‑export or verify file integrity.
- Decrypt failed → check `APP_KEY` and encryption flag.

---

## rag:import:pdf

Purpose
- Import PDF documents into the RAG knowledge base. Accepts a file or directory. PHP‑native parsing, no shell exec.

Signature
```
rag:import:pdf
    {path}
    {--title=}
    {--source_type=pdf}
    {--source_ref=}
    {--lang=en}
    {--sync}
    {--dry-run}
```

Interactive
- Confirms before importing multiple files.

Examples
```
php artisan rag:import:pdf storage/docs/policies.pdf --title="Policies 2025"
php artisan rag:import:pdf storage/docs/book.pdf --source_ref=docs:book
php artisan rag:import:pdf storage/docs --dry-run
php artisan rag:import:pdf storage/docs --sync
php artisan rag:import:pdf storage/docs/handbook.pdf --lang=en
php artisan rag:import:pdf storage/docs/contracts.pdf --title="Contracts" --sync
```

Expected output
```
PDF                storage/docs/policies.pdf
Bytes              120834
Extracted (approx chars) 10923
```

---

## Observability (Logging & Metrics)

Logging
- Structured logs on channel `rag` for: ingest, embed, query, cache hit/miss, export/backup/restore, doctor. Context includes tenant id.

Metrics
- Implemented via `Akira\\Rag\\Observability\\MetricsRecorder` with default `LogMetricsRecorder`.
- Metrics: `rag.ingest.count`, `rag.embed.count`, `rag.query.count`, `rag.cache.hit`, `rag.cache.miss`, `rag.export.count`, `rag.backup.count`, `rag.restore.count`, `rag.query.duration_ms`.

Encryption
- Uses Laravel Crypt (`APP_KEY`). Encrypted backups/exports include metadata header (version, tenant, timestamp). Decrypt failures emit clear errors.
