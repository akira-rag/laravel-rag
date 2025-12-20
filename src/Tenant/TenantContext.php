<?php

declare(strict_types=1);

namespace Akira\Rag\Tenant;

use Akira\Rag\Exceptions\TenantResolverException;
use Illuminate\Contracts\Config\Repository as Config;

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
        $resolverClass = $this->config->string('rag.tenancy.resolver', NullTenantResolver::class);

        $resolverInstance = resolve($resolverClass);

        throw_unless(
            $resolverInstance instanceof TenantResolver,
            TenantResolverException::invalidResolverImplementation($resolverClass, TenantResolver::class)
        );

        return $resolverInstance;
    }

    public function current(): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        return $this->resolver()->resolve();
    }
}
