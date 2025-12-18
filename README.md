## Akira RAG for Laravel

This package provides a complete Retrieval-Augmented Generation (RAG) system for Laravel (12+) using PostgreSQL + pgvector, PrismPHP, and Spatie Laravel Data.
### Installation

1. Require the package

```bash
composer require akira/laravel-rag
```

2. Preferred: use the installer

```bash
php artisan rag:install
```

Non-interactive (CI/scripts):

```bash
php artisan rag:install \
  --force \
  --with-tenancy \
  --run-migrate \
  --star
```

3. Alternative: manual setup

```bash
php artisan vendor:publish --tag="laravel-rag-config"
php artisan vendor:publish --tag="laravel-rag-migrations"
```

4. Run migrations

```bash
php artisan migrate
```

### Quick Start

```php
use Akira\\Rag\\Facades\\Rag;

Rag::ingest([
    'title' => 'Labor Law',
    'source_type' => 'law',
    'source_ref' => 'law:2021:123',
    'content' => $text,
    'meta' => ['lang' => 'en'],
]);

$answer = Rag::ask('What are the notice periods?');
```

### Documentation

- 01 Introduction: docs/01-Introduction.md
- 02 Installation: docs/02-Installation.md
- 03 Configuration: docs/03-Configuration.md
- 04 Database: docs/04-Database.md
- 05 Tenancy: docs/05-Tenancy.md
- 06 Ingestion: docs/06-Ingestion.md
- 07 Asking: docs/07-Asking.md
- 08 Cache and Audit: docs/08-Cache-and-Audit.md
- 10 Recipes: docs/10-Recipes.md

### CLI Commands

Commands support interactive prompts and non-interactive flags.

- Commands Guide: docs/03-Commands.md

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [kid](https://github.com/kidiatoliny)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
