<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class ChunkingException extends RuntimeException
{
    public static function invalidTokenCount(int $targetTokens, int $overlapTokens): self
    {
        return new self(
            "Invalid chunking configuration: target_tokens ({$targetTokens}) must be greater than overlap_tokens ({$overlapTokens})."
        );
    }

    public static function chunkingFailed(string $reason): self
    {
        return new self(
            "Failed to chunk content: {$reason}"
        );
    }

    public static function emptyContentProvided(): self
    {
        return new self(
            'Cannot chunk empty content. Please provide valid content.'
        );
    }

    public static function invalidChunkSize(int $chunkSize, int $minSize, int $maxSize): self
    {
        return new self(
            "Invalid chunk size: {$chunkSize}. Chunk size must be between {$minSize} and {$maxSize}."
        );
    }
}
