<?php

declare(strict_types=1);

namespace Akira\Rag\Tenant;

final class NullTenantResolver implements TenantResolver
{
    public function resolve(): ?string
    {
        return null;
    }
}
