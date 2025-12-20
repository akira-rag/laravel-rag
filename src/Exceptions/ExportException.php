<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class ExportException extends RuntimeException
{
    public static function exportFailed(string $reason): self
    {
        return new self(
            "Failed to export knowledge base: {$reason}"
        );
    }

    public static function invalidExportFormat(string $format): self
    {
        return new self(
            "Invalid export format: '{$format}'. Only 'json' format is currently supported."
        );
    }

    public static function encryptionFailed(string $reason): self
    {
        return new self(
            "Failed to encrypt export: {$reason}"
        );
    }

    public static function compressionFailed(string $reason): self
    {
        return new self(
            "Failed to compress export: {$reason}"
        );
    }

    public static function fileWriteFailed(string $filePath, string $reason): self
    {
        return new self(
            "Failed to write export file to '{$filePath}': {$reason}"
        );
    }
}
