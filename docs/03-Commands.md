# CLI Commands

This guide documents all Artisan commands provided by Akira RAG. All commands support rich interactive prompts (Laravel Prompts) and can be run non-interactively with flags for automation.

## Command Overview

| Command | Purpose |
|---------|---------|
| `rag:install` | Interactive installer |
| `rag:ingest` | Add documents to knowledge base |
| `rag:reembed` | Rebuild embeddings |
| `rag:stats` | View statistics |
| `rag:export` | Export knowledge base |
| `rag:backup` | Create encrypted backups |
| `rag:restore` | Restore from backup |
| `rag:import:pdf` | Import PDF documents |

## rag:install

Interactive installer that guides you through package setup.

### Signature

```bash
php artisan rag:install
    [--force]              # Skip all prompts
    [--with-tenancy]       # Enable multi-tenant mode
    [--run-migrate]        # Run migrations automatically
    [--star]               # Open GitHub to star repo
```

### Interactive Mode

```bash
php artisan rag:install
```

**Prompts:**
1. Publish configuration file?
2. Publish migrations?
3. Run database migrations now?
4. Enable multi-tenant mode?
5. Open GitHub repository to leave a star?

### Non-Interactive Mode

```bash
# Full automated setup
php artisan rag:install --force --with-tenancy --run-migrate

# CI/CD pipeline
php artisan rag:install --force --run-migrate
```

### What It Does

1. **Publishes Config** - Creates `config/rag.php`
2. **Publishes Migrations** - Creates migration files
3. **Runs Migrations** - Executes database setup
4. **Enables Tenancy** - Updates config for multi-tenant
5. **Opens GitHub** - Browser launch to star repo

---

## rag:ingest

Ingest content into the RAG knowledge base.

### Signature

```bash
php artisan rag:ingest
    [--title=]              # Document title
    [--source_type=]        # Type: law, manual, faq, etc.
    [--source_ref=]         # External reference
    [--text=]               # Raw text content
    [--file=]               # Path to .txt or .md file
    [--meta=*]              # Metadata key=value pairs
    [--no-embed]            # Skip embedding generation
    [--sync]                # Run embedding synchronously
```

### Interactive Mode

```bash
php artisan rag:ingest
```

**Step-by-step prompts:**
1. Enter document title
2. Select source type (law, pdf, web, faq, ticket, note)
3. Enter source reference (optional)
4. Choose ingestion source (paste text or file)
5. Add metadata key-value pairs (optional)

### Non-Interactive Examples

**From text:**
```bash
php artisan rag:ingest \
  --title="Employee Handbook" \
  --source_type=handbook \
  --source_ref=handbook:2024 \
  --text="Full handbook content here..." \
  --meta="department=HR" \
  --meta="year=2024"
```

**From file:**
```bash
php artisan rag:ingest \
  --title="Labor Law 2024" \
  --source_type=law \
  --source_ref=law:2024:001 \
  --file=storage/docs/labor-law.txt \
  --meta="lang=en" \
  --meta="jurisdiction=federal"
```

**With sync embedding:**
```bash
php artisan rag:ingest \
  --title="Quick Note" \
  --source_type=note \
  --text="Important information..." \
  --sync
```

**Skip embedding:**
```bash
php artisan rag:ingest \
  --title="Draft Document" \
  --source_type=manual \
  --file=draft.txt \
  --no-embed
```

### Output

```
Document ID        3fa85f64-5717-4562-b3fc-2c963f66afa6
Chunks             7
Embedding          dispatched
```

### Common Errors

**File not found:**
```
✗ File not found: storage/docs/missing.txt
```
**Solution:** Check file path and permissions

**Unsupported extension:**
```
✗ Unsupported file extension. Only .txt and .md are supported.
```
**Solution:** Use `.txt` or `.md` files only

**Missing required fields:**
```
✗ Provide --document_id or use --all
```
**Solution:** Run interactively or provide all required flags

---

## rag:reembed

Rebuild embeddings for existing documents.

### Signature

```bash
php artisan rag:reembed
    [--document_id=]        # Reembed single document
    [--all]                 # Reembed all documents
    [--sync]                # Run synchronously
    [--model=]              # Override embedding model
```

### Interactive Mode

```bash
php artisan rag:reembed
```

**Prompts:**
1. Rebuild embeddings for: All documents / Single document
2. If single: Enter Document ID (UUID)

### Examples

**Reembed all documents:**
```bash
php artisan rag:reembed --all
```

**Reembed single document:**
```bash
php artisan rag:reembed --document_id=3fa85f64-5717-4562-b3fc-2c963f66afa6
```

**With model override:**
```bash
php artisan rag:reembed --all --model=text-embedding-3-large
```

**Synchronous processing:**
```bash
php artisan rag:reembed --all --sync
```

### Output

```
Documents affected   12
Embeddings recreated 0
Model override       text-embedding-3-small
Mode                 queue
```

### Use Cases

- **Model Migration** - Switch to newer embedding model
- **Fix Corrupted Embeddings** - Regenerate after data issues
- **Performance Testing** - Compare different models
- **Development** - Test embedding pipeline

---

## rag:stats

Display statistics about the RAG knowledge base.

### Signature

```bash
php artisan rag:stats
    [--json]                # Output as JSON
```

### Examples

**Human-readable output:**
```bash
php artisan rag:stats
```

**JSON output:**
```bash
php artisan rag:stats --json
```

**Pipe to file:**
```bash
php artisan rag:stats --json > stats.json
```

**Use in scripts:**
```bash
DOCS=$(php artisan rag:stats --json | jq -r '.documents')
echo "Total documents: $DOCS"
```

### Output

**Standard:**
```
Documents       42
Chunks          187
Embeddings      187
KB Version      v1
Tenancy         multi
Tenant ID       team-123
Chat Model      gpt-4
Embedding Model text-embedding-3-small
Hybrid          enabled
Hybrid Weights  semantic=0.7 keyword=0.3
```

**JSON:**
```json
{
  "documents": 42,
  "chunks": 187,
  "embeddings": 187,
  "kbVersion": "v1",
  "tenancy": "multi",
  "tenantId": "team-123",
  "models": {
    "chat": "gpt-4",
    "embedding": "text-embedding-3-small"
  },
  "hybrid": {
    "enabled": true,
    "semantic_weight": 0.7,
    "keyword_weight": 0.3
  }
}
```

---

## rag:export

Export the RAG knowledge base to a file.

### Signature

```bash
php artisan rag:export
    [--format=json]         # Export format
    [--output=]             # Output file path
    [--include-embeddings]  # Include embedding vectors
    [--include-audit]       # Include query history
    [--compress]            # Gzip compression
    [--encrypt]             # Encrypt with APP_KEY
```

### Interactive Mode

```bash
php artisan rag:export
```

**Prompts:**
- Output path (suggests default based on tenant)

### Examples

**Basic export:**
```bash
php artisan rag:export --output=backup.json
```

**Full export with everything:**
```bash
php artisan rag:export \
  --output=full-backup.json \
  --include-embeddings \
  --include-audit \
  --compress \
  --encrypt
```

**Compressed only:**
```bash
php artisan rag:export \
  --output=backup.json \
  --compress
```

**Encrypted only:**
```bash
php artisan rag:export \
  --output=backup.json \
  --encrypt
```

**For specific tenant:**
```bash
# Exports are automatically tenant-scoped
php artisan rag:export --output=storage/exports/team-1/backup.json
```

### Output

```
Format             json+gz
Encrypted          yes
Embeddings         included
Audit              omitted
Output             storage/app/rag/exports/team-123/export-20241220-160000.json.gz
```

### Export Structure

```json
{
  "meta": {
    "version": "v1",
    "tenant": "team-123",
    "timestamp": "2024-12-20T16:00:00Z"
  },
  "documents": [...],
  "chunks": [...],
  "embeddings": [...],
  "queries": [...]
}
```

---

## rag:backup

Create encrypted, compressed backups with automatic pruning.

### Signature

```bash
php artisan rag:backup
    [--output=]             # Output file path
    [--retain=7]            # Keep last N backups
    [--no-encryption]       # Disable encryption
```

### Examples

**Default backup:**
```bash
php artisan rag:backup
```

**Custom retention:**
```bash
php artisan rag:backup --retain=30
```

**Without encryption:**
```bash
php artisan rag:backup --no-encryption
```

**Custom output:**
```bash
php artisan rag:backup --output=storage/backups/custom-backup.json
```

**Daily backups in cron:**
```bash
# Add to crontab
0 2 * * * cd /path/to/app && php artisan rag:backup --retain=7
```

### Output

```
Output             storage/app/rag/backups/team-123/backup-20241220-020000.json.gz
Encryption         enabled
Retention          7
```

### Backup Naming

Format: `backup-{date}-{time}.json.gz`

Example: `backup-20241220-160000.json.gz`

### Pruning Strategy

- Keeps most recent `N` backups (default: 7)
- Deletes older backups automatically
- Per-tenant backup directories
- Sorted by modification time

---

## rag:restore

Restore a knowledge base from a backup file.

### Signature

```bash
php artisan rag:restore {path}
    [--force]               # Skip confirmation
    [--dry-run]             # Validate only
    [--decrypt]             # Decrypt backup
```

### Interactive Mode

```bash
php artisan rag:restore storage/backups/backup.json.gz
```

**Prompts:**
- Confirmation: "Proceed with restore?"

### Examples

**Dry run (validation):**
```bash
php artisan rag:restore backup.json.gz --dry-run
```

**Force restore:**
```bash
php artisan rag:restore backup.json.gz --force
```

**Decrypt and restore:**
```bash
php artisan rag:restore backup.json.gz --decrypt --force
```

**Validate encrypted backup:**
```bash
php artisan rag:restore backup.json.gz --decrypt --dry-run
```

### Dry Run Output

```
Documents          42
Chunks             187
Embeddings         187
Queries            156
```

### Restore Output

```
Restored documents   42
Restored chunks      187
Restored embeddings  187
Restored queries     156
```

### Restore Strategy

- **Upserts** - Updates existing, inserts new
- **Preserves** - Existing data not in backup is kept
- **Tenant-scoped** - Only affects current tenant
- **Transactional** - Rollback on error (if supported by DB)

### Common Errors

**Invalid backup:**
```
✗ Invalid backup content.
```
**Solution:** Verify file integrity, re-export if needed

**Decryption failed:**
```
✗ Failed to decrypt backup. Invalid key or corrupted archive.
```
**Solution:** Check APP_KEY matches original encryption key

**Backup not found:**
```
✗ Backup not found: storage/backups/missing.json.gz
```
**Solution:** Verify file path exists

---

## rag:import:pdf

Import PDF documents into the knowledge base.

### Signature

```bash
php artisan rag:import:pdf {path}
    [--title=]              # Document title
    [--source_type=pdf]     # Source type
    [--source_ref=]         # Source reference
    [--lang=en]             # Language code
    [--sync]                # Process synchronously
    [--dry-run]             # Preview only
```

### Examples

**Single PDF:**
```bash
php artisan rag:import:pdf storage/docs/manual.pdf \
  --title="User Manual"
```

**Directory of PDFs:**
```bash
php artisan rag:import:pdf storage/docs/ \
  --source_type=manual \
  --lang=en
```

**Dry run:**
```bash
php artisan rag:import:pdf storage/docs/*.pdf --dry-run
```

**With source reference:**
```bash
php artisan rag:import:pdf contract.pdf \
  --title="Service Contract" \
  --source_ref="contract:2024:001"
```

**Synchronous processing:**
```bash
php artisan rag:import:pdf report.pdf --sync
```

### Dry Run Output

```
PDF                storage/docs/manual.pdf
Bytes              120834
Extracted (approx chars) 10923
```

### Processing Output

```
Mode               queue
```

### PDF Extraction

The command uses a **naive PHP-native** text extraction:
- Searches for PDF text operators (Tj/TJ)
- No external dependencies
- Fast but basic
- Deterministic results

**For production**, consider:
- [Spatie PDF to Text](https://github.com/spatie/pdf-to-text) (uses pdftotext)
- [PDF Parser](https://github.com/smalot/pdfparser)
- Cloud services (AWS Textract, Google Cloud Vision)

### Limitations

- Basic text extraction only
- No OCR for scanned documents
- May miss complex layouts
- No image/table extraction

---

## Global Options

All commands support standard Laravel options:

```bash
# Quiet mode (suppress output)
php artisan rag:stats --quiet

# Verbose mode (detailed output)
php artisan rag:ingest --verbose

# No interaction (assume defaults)
php artisan rag:install --no-interaction

# ANSI colors
php artisan rag:stats --ansi

# No ANSI colors
php artisan rag:stats --no-ansi
```

## Multi-Tenancy

All commands are **automatically tenant-scoped** when multi-tenancy is enabled.

### How It Works

1. **TenantResolver** - Resolves current tenant ID
2. **Automatic Scoping** - All queries filter by tenant
3. **No Manual tenant_id** - Never provide tenant_id manually

### Example

```php
// In your TenantResolver
public function resolve(): ?string
{
    return auth()->user()?->organization_id;
}
```

```bash
# Command runs in context of authenticated user's tenant
php artisan rag:stats
```

## Automation & Scripting

### Cron Jobs

```bash
# Daily backups
0 2 * * * cd /var/www/app && php artisan rag:backup --retain=7

# Weekly stats
0 0 * * 0 cd /var/www/app && php artisan rag:stats --json > /var/log/rag/stats.json

# Monthly re-embedding
0 3 1 * * cd /var/www/app && php artisan rag:reembed --all
```

### Bash Scripts

```bash
#!/bin/bash
# backup-all-tenants.sh

TENANTS=("team-1" "team-2" "team-3")

for tenant in "${TENANTS[@]}"; do
  echo "Backing up $tenant..."
  php artisan rag:backup \
    --output="storage/backups/$tenant/backup.json" \
    --retain=14
done
```

### Laravel Scheduler

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Daily backups
    $schedule->command('rag:backup --retain=7')
        ->daily()
        ->at('02:00');
    
    // Weekly stats logging
    $schedule->command('rag:stats --json')
        ->weekly()
        ->appendOutputTo(storage_path('logs/rag-stats.log'));
}
```

## Error Handling

### Exit Codes

- `0` - Success
- `1` - General error (INVALID)
- `2` - Failure (FAILURE)

### Example Script

```bash
#!/bin/bash

if php artisan rag:backup --retain=7; then
  echo "✓ Backup successful"
  # Send success notification
else
  echo "✗ Backup failed"
  # Send failure alert
  exit 1
fi
```

## Best Practices

### 1. Use --force in Automation

```bash
# CI/CD pipelines
php artisan rag:install --force --run-migrate
```

### 2. Validate with --dry-run

```bash
# Test before actual restore
php artisan rag:restore backup.json.gz --dry-run
```

### 3. Log Output

```bash
# Capture logs for audit
php artisan rag:ingest \
  --title="Doc" \
  --file=doc.txt 2>&1 | tee -a /var/log/rag/ingest.log
```

### 4. Use JSON for Parsing

```bash
# Extract specific stats
DOCS=$(php artisan rag:stats --json | jq -r '.documents')
```

### 5. Set Timeouts

```bash
# For large operations
timeout 3600 php artisan rag:reembed --all
```

**Previous**: [02 Installation](02-Installation.md) | **Next**: [04 Configuration](04-Configuration.md)
