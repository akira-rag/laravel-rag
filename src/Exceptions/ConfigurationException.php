<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use RuntimeException;

final class ConfigurationException extends RuntimeException
{
    public static function missingConfiguration(string $configKey): self
    {
        return new self(
            "Missing required configuration: '{$configKey}'. Please check your configuration file."
        );
    }

    public static function invalidConfiguration(string $configKey, string $reason): self
    {
        return new self(
            "Invalid configuration for '{$configKey}': {$reason}"
        );
    }

    public static function modelNotConfigured(string $modelType): self
    {
        return new self(
            "No {$modelType} model configured. Please set a valid model in your configuration."
        );
    }
}
