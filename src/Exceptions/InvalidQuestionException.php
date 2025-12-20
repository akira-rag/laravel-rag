<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use InvalidArgumentException;

final class InvalidQuestionException extends InvalidArgumentException
{
    public static function emptyQuestion(): self
    {
        return new self(
            'Question cannot be empty. Please provide a valid question string.'
        );
    }

    public static function questionTooLong(int $maxLength, int $actualLength): self
    {
        return new self(
            "Question is too long. Maximum length is {$maxLength} characters, but {$actualLength} were provided."
        );
    }
}
