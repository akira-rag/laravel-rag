<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class DatabaseException extends RuntimeException
{
    public static function transactionFailed(string $reason): self
    {
        return new self(
            "Database transaction failed: {$reason}"
        );
    }

    public static function queryFailed(string $modelClass, string $reason): self
    {
        return new self(
            "Failed to execute query on {$modelClass}: {$reason}"
        );
    }

    public static function connectionFailed(string $reason): self
    {
        return new self(
            "Database connection failed: {$reason}"
        );
    }
}
