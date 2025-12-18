# Installation

Requirements:
- PHP 8.4+
- Laravel 12
- Database:
  - PostgreSQL 14+ (recommended for production)
  - SQLite in‑memory supported for tests
  - MySQL is not supported for vector search

1) Install the package

```bash
composer require akira/laravel-rag
```

2) Preferred: use the installer

```bash
php artisan rag:install
```

3) Alternative: manual setup

```bash
php artisan vendor:publish --tag="laravel-rag-config"
php artisan vendor:publish --tag="laravel-rag-migrations"
```

4) Run migrations

```bash
php artisan migrate
```

5) Review configuration

Open `config/rag.php` and adjust as needed.

PostgreSQL setup tips
- Ensure the `vector` extension is available (pgvector). On your DB:

```sql
CREATE EXTENSION IF NOT EXISTS vector;
```

- The migration will create HNSW indexes and a generated `tsvector` column when running on PostgreSQL. For SQLite, compatible text/json columns are used to keep tests fast and deterministic.

Previous: [01 Introduction](01-Introduction.md) | Next: [03 Configuration](03-Configuration.md)
