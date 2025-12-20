<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use InvalidArgumentException;

final class InvalidPayload extends InvalidArgumentException
{
    public static function missingField(string $fieldName): self
    {
        return new self(
            "Missing or invalid required field: '{$fieldName}'. The field must be present and contain a valid value."
        );
    }

    public static function tenantIdNotAllowed(): self
    {
        return new self(
            'The tenant_id field is not allowed in the payload. Tenant ID is automatically managed by the system.'
        );
    }

    public static function invalidFieldType(string $fieldName, string $expectedType, string $actualType): self
    {
        return new self(
            "Invalid type for field '{$fieldName}'. Expected {$expectedType}, but got {$actualType}."
        );
    }

    public static function emptyContent(): self
    {
        return new self(
            'Content cannot be empty. Please provide valid content to ingest.'
        );
    }

    public static function invalidMetadata(string $reason): self
    {
        return new self(
            "Invalid metadata: {$reason}"
        );
    }
}
