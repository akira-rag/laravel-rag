<?php

declare(strict_types=1);

namespace Akira\Rag\Tenant;

use Illuminate\Contracts\Config\Repository as Config;
use InvalidArgumentException;

final readonly class TenantContext
{
    public function __construct(private Config $config) {}

    public function enabled(): bool
    {
        return $this->config->boolean('rag.tenancy.enabled', false);
    }

    public function column(): string
    {
        return $this->config->string('rag.tenancy.tenant_column', 'tenant_id');
    }

    public function resolver(): TenantResolver
    {
        $class =  $this->config->string('rag.tenancy.resolver', NullTenantResolver::class);

        $resolver = resolve($class);

        throw_unless($resolver instanceof TenantResolver, InvalidArgumentException::class, 'Tenant resolver must implement '.TenantResolver::class);

        return $resolver;
    }

    public function current(): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        return $this->resolver()->resolve();
    }
}
