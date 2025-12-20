<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class DocumentNotFoundException extends RuntimeException
{
    public static function withId(string $documentId): self
    {
        return new self(
            "Document with ID '{$documentId}' was not found in the knowledge base."
        );
    }

    public static function withHash(string $hash): self
    {
        return new self(
            "Document with hash '{$hash}' was not found in the knowledge base."
        );
    }
}
