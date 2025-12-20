<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class ImportException extends RuntimeException
{
    public static function importFailed(string $reason): self
    {
        return new self(
            "Failed to import knowledge base: {$reason}"
        );
    }

    public static function fileNotFound(string $filePath): self
    {
        return new self(
            "Import file not found: '{$filePath}'"
        );
    }

    public static function invalidFileFormat(string $filePath, string $expectedFormat): self
    {
        return new self(
            "Invalid file format for '{$filePath}'. Expected {$expectedFormat} format."
        );
    }

    public static function decryptionFailed(string $reason): self
    {
        return new self(
            "Failed to decrypt import file: {$reason}"
        );
    }

    public static function decompressionFailed(string $reason): self
    {
        return new self(
            "Failed to decompress import file: {$reason}"
        );
    }

    public static function invalidBackupContent(string $reason): self
    {
        return new self(
            "Invalid backup content: {$reason}"
        );
    }
}
