<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Akira\Rag\Tenant\TenantResolver;

final class FakeTenantResolver implements TenantResolver
{
    public function __construct(private readonly ?string $id)
    {
    }

    public function resolve(): ?string
    {
        return $this->id;
    }
}

