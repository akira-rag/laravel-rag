# Recipes

Ingest + Quick Ask

```php
$doc = Rag::ingest([
    'title' => 'Labor Law',
    'source_type' => 'law',
    'source_ref' => 'law:2021:123',
    'content' => $text,
]);

$answer = Rag::ask('What are the notice periods?');
```

Multi‑tenant with Jetstream

```php
// config/rag.php
'tenancy' => [
    'enabled' => true,
    'resolver' => App\\Rag\\Tenant\\JetstreamTenantResolver::class,
];

final class JetstreamTenantResolver implements \\Akira\\Rag\\Tenant\\TenantResolver
{
    public function resolve(): ?string
    {
        return auth()->user()?->currentTeam?->id;
    }
}

$result = Rag::ask('What does the law say about contracts?');
```

Filter by document

```php
$doc = Rag::ingest([
    'title' => 'Employee Handbook',
    'source_type' => 'policy',
    'source_ref' => 'handbook:v2',
    'content' => $text,
]);

$filtered = Rag::ask('What is the PTO policy?', ['document_id' => $doc['document_id']]);
```

Previous: [08 Cache and Audit](08-Cache-and-Audit.md) 
