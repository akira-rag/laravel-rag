<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class ChunkNotFoundException extends RuntimeException
{
    public static function withId(string $chunkId): self
    {
        return new self(
            "Chunk with ID '{$chunkId}' was not found in the knowledge base."
        );
    }

    public static function forDocument(string $documentId): self
    {
        return new self(
            "No chunks found for document with ID '{$documentId}'."
        );
    }
}
