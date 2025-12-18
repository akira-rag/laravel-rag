<?php

declare(strict_types=1);

use Akira\Rag\Tenant\TenantContext;

it('throws when resolver is invalid', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', stdClass::class);

    expect(fn () => resolve(TenantContext::class)->resolver())
        ->toThrow(InvalidArgumentException::class);
});

it('returns null current when disabled', function (): void {
    config()->set('rag.tenancy.enabled', false);
    $ctx = resolve(TenantContext::class);
    expect($ctx->current())->toBeNull();
});
