<?php

declare(strict_types=1);

use Akira\Rag\Tenant\NullTenantResolver;
use Akira\Rag\Tenant\TenantContext;

it('resolves tenant via configured resolver', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', NullTenantResolver::class);
    $ctx = resolve(TenantContext::class);
    expect($ctx->current())->toBeNull();
});
