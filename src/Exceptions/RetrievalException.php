<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class RetrievalException extends RuntimeException
{
    public static function noResultsFound(string $query): self
    {
        return new self(
            "No results found for query: '{$query}'"
        );
    }

    public static function retrievalFailed(string $reason): self
    {
        return new self(
            "Failed to retrieve chunks: {$reason}"
        );
    }

    public static function invalidTopK(int $topK): self
    {
        return new self(
            "Invalid top_k value: {$topK}. The value must be a positive integer."
        );
    }

    public static function invalidFilters(string $reason): self
    {
        return new self(
            "Invalid retrieval filters: {$reason}"
        );
    }
}
