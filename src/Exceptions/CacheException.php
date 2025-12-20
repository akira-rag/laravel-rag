<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class CacheException extends RuntimeException
{
    public static function cacheStoreFailed(string $key, string $reason): self
    {
        return new self(
            "Failed to store cache for key '{$key}': {$reason}"
        );
    }

    public static function cacheRetrievalFailed(string $key, string $reason): self
    {
        return new self(
            "Failed to retrieve cache for key '{$key}': {$reason}"
        );
    }

    public static function invalidCacheConfiguration(string $reason): self
    {
        return new self(
            "Invalid cache configuration: {$reason}"
        );
    }
}
