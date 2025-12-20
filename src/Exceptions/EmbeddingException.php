<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class EmbeddingException extends RuntimeException
{
    public static function generationFailed(string $reason): self
    {
        return new self(
            "Failed to generate embedding: {$reason}"
        );
    }

    public static function invalidDimensions(int $expected, int $actual): self
    {
        return new self(
            "Invalid embedding dimensions. Expected {$expected}, but got {$actual}."
        );
    }

    public static function modelNotConfigured(): self
    {
        return new self(
            'Embedding model is not configured. Please set a valid model in the configuration.'
        );
    }
}
