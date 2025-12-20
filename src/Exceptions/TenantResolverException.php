<?php

declare(strict_types=1);

namespace Akira\Rag\Exceptions;

use InvalidArgumentException;

final class TenantResolverException extends InvalidArgumentException
{
    public static function invalidResolverImplementation(string $resolverClass, string $expectedInterface): self
    {
        return new self(
            "Tenant resolver must implement {$expectedInterface}, but {$resolverClass} was provided."
        );
    }

    public static function resolverNotConfigured(): self
    {
        return new self(
            'No tenant resolver has been configured. Please set a valid TenantResolver in the service provider.'
        );
    }
}
