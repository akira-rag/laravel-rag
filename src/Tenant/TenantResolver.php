<?php

declare(strict_types=1);

namespace Akira\Rag\Tenant;

interface TenantResolver
{
    public function resolve(): ?string;
}
