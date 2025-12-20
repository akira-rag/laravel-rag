# Installation

This guide walks you through installing and configuring Akira RAG for Laravel.

## Requirements

### System Requirements

- **PHP**: 8.4 or higher
- **Laravel**: 12.x
- **Composer**: 2.x

### Database Requirements

#### PostgreSQL (Recommended for Production)

- **Version**: PostgreSQL 14 or higher
- **Extension**: pgvector
- **Why PostgreSQL?**
  - Native vector support with pgvector
  - HNSW indexing for fast similarity search
  - Full-text search with `tsvector`
  - ACID compliance

#### SQLite (Testing Only)

- **Version**: SQLite 3.x
- **Use Case**: In-memory testing
- **Limitations**: No vector indexing, slower similarity search

⚠️ **Note**: MySQL is **not supported** as it lacks native vector search capabilities.

## Installation Steps

### Method 1: Quick Install (Recommended)

The interactive installer guides you through the entire setup:

```bash
# Install package
composer require akira/laravel-rag

# Run interactive installer
php artisan rag:install
```

The installer will prompt you for:

1. **Publish Configuration?** - Creates `config/rag.php`
2. **Publish Migrations?** - Creates migration files
3. **Run Migrations Now?** - Executes database migrations
4. **Enable Multi-tenant Mode?** - Configures tenant isolation
5. **Star the Repository?** - Opens GitHub to leave a star ⭐

**Example Session:**

```
 ┌ Akira RAG Installer ────────────────────────────────┐
 │                                                      │
 │ Publish configuration file?                         │
 │ ● Yes / ○ No                                        │
 │                                                      │
 │ ✓ Publishing config...                              │
 └──────────────────────────────────────────────────────┘
```

### Method 2: Non-Interactive Install

Perfect for CI/CD pipelines and automated deployments:

```bash
# Install with all options enabled
php artisan rag:install \
  --force \
  --with-tenancy \
  --run-migrate \
  --star
```

**Options:**
- `--force` - Skip all prompts
- `--with-tenancy` - Enable multi-tenant mode
- `--run-migrate` - Run migrations automatically
- `--star` - Open GitHub repository

### Method 3: Manual Install

For maximum control over each step:

```bash
# 1. Install package
composer require akira/laravel-rag

# 2. Publish configuration
php artisan vendor:publish --tag="laravel-rag-config"

# 3. Publish migrations
php artisan vendor:publish --tag="laravel-rag-migrations"

# 4. Run migrations
php artisan migrate
```

## Database Setup

### PostgreSQL Setup

#### 1. Install pgvector Extension

**Ubuntu/Debian:**
```bash
sudo apt-get install postgresql-14-pgvector
```

**macOS (Homebrew):**
```bash
brew install pgvector
```

**Docker:**
```dockerfile
FROM postgres:14
RUN apt-get update && apt-get install -y postgresql-14-pgvector
```

#### 2. Enable Extension

Connect to your database and run:

```sql
CREATE EXTENSION IF NOT EXISTS vector;
```

**Verify Installation:**
```sql
SELECT * FROM pg_extension WHERE extname = 'vector';
```

#### 3. Configure Laravel Database

Update your `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Optional: RAG-specific
RAG_EMBEDDING_MODEL=text-embedding-3-small
RAG_CHAT_MODEL=gpt-4
```

### SQLite Setup (Testing)

For testing environments:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

**Note**: SQLite support is limited to testing. Vector search will be slower and less accurate.

## Configuration

After installation, review and customize `config/rag.php`:

### AI Models

```php
'ai' => [
    'embedding_model' => env('RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
    'chat_model' => env('RAG_CHAT_MODEL', 'gpt-4'),
],
```

**Supported Embedding Models:**
- `text-embedding-3-small` (1536 dimensions, recommended)
- `text-embedding-3-large` (3072 dimensions)
- `text-embedding-ada-002` (1536 dimensions, legacy)

### Chunking Strategy

```php
'chunking' => [
    'target_tokens' => 800,      // ~600 words per chunk
    'overlap_tokens' => 120,     // ~90 words overlap
],
```

**Guidelines:**
- **Small chunks** (400-600 tokens): Better precision, more chunks
- **Large chunks** (1000-1500 tokens): More context, fewer chunks
- **Overlap**: Prevents context loss at chunk boundaries

### Retrieval Settings

```php
'retrieval' => [
    'top_k' => 5,  // Number of chunks to retrieve
    'hybrid' => [
        'enabled' => true,
        'semantic_weight' => 0.7,   // Vector similarity
        'keyword_weight' => 0.3,    // Full-text search
    ],
],
```

### Caching

```php
'cache' => [
    'enabled' => true,
    'prefix' => 'rag:v1',
    'ttl_seconds' => 1209600,  // 14 days
],
```

### Audit Logging

```php
'audit' => [
    'enabled' => true,
],
```

**When enabled**, all queries are logged to the `rag_queries` table.

### Multi-Tenancy

```php
'tenancy' => [
    'enabled' => false,
    'resolver' => \Akira\Rag\Tenant\NullTenantResolver::class,
    'tenant_column' => 'tenant_id',
],
```

See [Multi-tenancy Guide](06-Tenancy.md) for detailed setup.

## Verification

### Test Basic Functionality

Create a test script to verify installation:

```php
// routes/console.php or a test file

use Akira\Rag\Facades\Rag;

// Ingest a test document
$result = Rag::ingest([
    'title' => 'Test Document',
    'source_type' => 'test',
    'source_ref' => 'test:001',
    'content' => 'This is a test document for Akira RAG installation.',
    'meta' => ['environment' => 'testing'],
]);

dump('Ingestion result:', $result);

// Query the document
$answer = Rag::ask('What is this test about?');

dump('Query result:', $answer);
```

### Check Database Tables

Verify all tables were created:

```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
  AND table_name LIKE 'rag_%';
```

Expected tables:
- `rag_documents`
- `rag_chunks`
- `rag_embeddings`
- `rag_queries`
- `rag_query_chunks`

### View Statistics

```bash
php artisan rag:stats
```

Expected output:
```
Documents       0
Chunks          0
Embeddings      0
KB Version      v1
Tenancy         single
```

## Troubleshooting

### pgvector Extension Not Found

**Error:** `extension "vector" does not exist`

**Solution:**
```bash
# Install pgvector
sudo apt-get install postgresql-14-pgvector

# Enable in database
psql -d your_database -c "CREATE EXTENSION vector;"
```

### Permission Denied

**Error:** `permission denied to create extension "vector"`

**Solution:**
```sql
-- Connect as superuser
psql -d your_database -U postgres
CREATE EXTENSION vector;
```

### Migration Fails

**Error:** Various migration errors

**Solution:**
```bash
# Rollback and retry
php artisan migrate:rollback --step=1
php artisan migrate
```

### Config Not Found

**Error:** `Configuration file [rag] not found`

**Solution:**
```bash
# Re-publish config
php artisan vendor:publish --tag="laravel-rag-config" --force
```

## Environment Variables

Add these to your `.env` file for easy configuration:

```env
# AI Models
RAG_EMBEDDING_MODEL=text-embedding-3-small
RAG_CHAT_MODEL=gpt-4

# Chunking
RAG_CHUNK_TARGET_TOKENS=800
RAG_CHUNK_OVERLAP_TOKENS=120

# Retrieval
RAG_RETRIEVAL_TOP_K=5

# Cache
RAG_CACHE_ENABLED=true
RAG_CACHE_TTL_SECONDS=1209600

# Audit
RAG_AUDIT_ENABLED=true

# Tenancy
RAG_TENANCY_ENABLED=false
```

Then reference in `config/rag.php`:

```php
'chunking' => [
    'target_tokens' => env('RAG_CHUNK_TARGET_TOKENS', 800),
    'overlap_tokens' => env('RAG_CHUNK_OVERLAP_TOKENS', 120),
],
```

## Performance Optimization

### Database Indexes

The migrations automatically create optimized indexes:

```sql
-- HNSW index for fast vector search
CREATE INDEX rag_embeddings_hnsw_idx 
ON rag_embeddings 
USING hnsw (embedding vector_cosine_ops);

-- Full-text search index
CREATE INDEX rag_chunks_tsvector_idx 
ON rag_chunks 
USING gin (to_tsvector('english', content));

-- Foreign key indexes
CREATE INDEX rag_chunks_document_id_idx 
ON rag_chunks (document_id);
```

### Connection Pooling

For high-traffic applications, consider using PgBouncer:

```env
DB_HOST=pgbouncer_host
DB_PORT=6432
```

### Cache Configuration

Use Redis for better cache performance:

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Next Steps

Now that installation is complete:

1. **Configure AI Models** - Set up your embedding and chat models
2. **Ingest Documents** - Start adding content to your knowledge base
3. **Test Queries** - Verify retrieval is working correctly

**Previous**: [01 Introduction](01-Introduction.md) | **Next**: [03 Commands](03-Commands.md)
