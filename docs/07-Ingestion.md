# Ingestion

Ingestion is the process of adding documents to your RAG knowledge base. This guide covers how to store documents, how chunking works, and best practices for data organization.

## Basic Ingestion

### Using the Facade

The simplest way to ingest documents:

```php
use Akira\Rag\Facades\Rag;

$result = Rag::ingest([
    'title' => 'Employee Handbook 2024',
    'source_type' => 'handbook',
    'source_ref' => 'handbook:2024:v1',
    'content' => $documentText,
    'meta' => ['department' => 'HR', 'year' => 2024],
]);

// Returns:
// [
//     'document_id' => '123e4567-e89b-12d3-a456-426614174000',
//     'chunks' => 8
// ]
```

### Using Data Transfer Objects

For type-safe ingestion with validation:

```php
use Akira\Rag\Data\IngestPayload;
use Akira\Rag\Facades\Rag;

$payload = new IngestPayload(
    title: 'Safety Guidelines',
    source_type: 'manual',
    source_ref: 'safety:2024',
    content: $content,
    meta: ['category' => 'safety', 'version' => 1]
);

$result = Rag::ingest($payload->toArray());
```

## Required Fields

All ingestion requests must include these fields:

### `title` (string, required)

Human-readable document title used for identification and display.

```php
'title' => 'Q4 2024 Financial Report'
```

**Guidelines:**
- Keep descriptive but concise
- Include relevant identifiers (dates, versions)
- Use consistent naming conventions

### `source_type` (string, required)

Category or type of document for filtering and organization.

```php
'source_type' => 'financial_report'
```

**Common Types:**
- `law` - Legal documents, regulations
- `policy` - Company policies
- `manual` - User manuals, guides
- `faq` - Frequently asked questions
- `article` - Blog posts, articles
- `ticket` - Support tickets
- `note` - Internal notes
- `handbook` - Employee handbooks

### `source_ref` (string, required)

External reference or unique identifier for the source document.

```php
'source_ref' => 'fin:2024:q4:001'
```

**Best Practices:**
- Use a consistent format (e.g., `type:year:identifier`)
- Include version numbers when applicable
- Make it unique across your system
- Use it to link back to source systems

### `content` (string, required)

The actual text content to be indexed and searched.

```php
'content' => file_get_contents('report.txt')
```

**Guidelines:**
- Plain text format (HTML/Markdown will be searched as-is)
- Remove excessive whitespace
- Ensure UTF-8 encoding
- Minimum length: 1 character (but meaningful content recommended)

### `meta` (array, optional)

Additional structured metadata as key-value pairs.

```php
'meta' => [
    'lang' => 'en',
    'department' => 'Finance',
    'author' => 'Jane Doe',
    'created_at' => '2024-01-15',
    'tags' => ['quarterly', 'financial', 'report'],
    'classification' => 'internal',
]
```

**Uses:**
- Filtering during retrieval
- Display in UI
- Audit trail
- Custom business logic

## Validation Rules

### Automatic Validation

The package validates all fields automatically:

```php
use Akira\Rag\Exceptions\InvalidPayload;

try {
    Rag::ingest([
        'title' => '',  // Empty title
        'source_type' => 'manual',
        'source_ref' => 'doc:001',
        'content' => 'Content here',
    ]);
} catch (InvalidPayload $exception) {
    // Exception message: "Missing or invalid required field: 'title'"
    echo $exception->getMessage();
}
```

### Forbidden Fields

The `tenant_id` field is automatically managed and **cannot** be provided:

```php
try {
    Rag::ingest([
        'title' => 'Document',
        'tenant_id' => '123',  // ❌ Not allowed!
        // ...
    ]);
} catch (InvalidPayload $exception) {
    // "The tenant_id field is not allowed in the payload"
}
```

## Idempotency

Akira RAG prevents duplicate documents using content-based hashing.

### How It Works

A hash is generated from:
```
SHA256(source_type | source_ref | SHA256(content))
```

### Duplicate Detection

```php
// First ingestion
$result1 = Rag::ingest([
    'title' => 'Document',
    'source_type' => 'manual',
    'source_ref' => 'doc:001',
    'content' => 'Same content',
]);
// Creates new document: document_id = 'abc123', chunks = 1

// Second ingestion (same content)
$result2 = Rag::ingest([
    'title' => 'Document',  // Title can differ
    'source_type' => 'manual',
    'source_ref' => 'doc:001',
    'content' => 'Same content',
]);
// Returns existing: document_id = 'abc123', chunks = 1

assert($result1['document_id'] === $result2['document_id']);
```

## Chunking Strategy

Large documents are automatically split into smaller chunks for better retrieval accuracy.

### Configuration

In `config/rag.php`:

```php
'chunking' => [
    'target_tokens' => 800,      // Target chunk size
    'overlap_tokens' => 120,     // Overlap between chunks
],
```

### Choosing Chunk Size

| Chunk Size | Best For | Trade-offs |
|------------|----------|------------|
| 400-600 tokens | Technical docs, precise answers | More chunks, less context |
| 800-1000 tokens (default) | General content, balanced | Good balance |
| 1200-1500 tokens | Long-form content, narratives | Fewer chunks, more context |

## CLI Ingestion

### Interactive Mode

```bash
php artisan rag:ingest
```

### Non-Interactive Mode

```bash
php artisan rag:ingest \
  --title="Employee Handbook" \
  --source_type=handbook \
  --source_ref=handbook:2024 \
  --file=handbook.txt \
  --meta="department=HR" \
  --meta="year=2024"
```

**Previous**: [06 Tenancy](06-Tenancy.md) | **Next**: [08 Asking](08-Asking.md)
