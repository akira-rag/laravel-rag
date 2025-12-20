<?php

declare(strict_types=1);

use Akira\Rag\Exceptions\TenantResolverException;
use Akira\Rag\Tenant\TenantContext;

it('throws when resolver is invalid', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', stdClass::class);

    expect(fn () => resolve(TenantContext::class)->resolver())
        ->toThrow(TenantResolverException::class);
});

it('returns null current when disabled', function (): void {
    config()->set('rag.tenancy.enabled', false);
    $tenantContext = resolve(TenantContext::class);
    expect($tenantContext->current())->toBeNull();
});
