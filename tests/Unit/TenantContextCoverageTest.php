<?php

declare(strict_types=1);

use Akira\Rag\Tenant\TenantContext;

it('returns column and enabled flags', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.tenant_column', 'tenant_id');
    $ctx = resolve(TenantContext::class);
    expect($ctx->enabled())->toBeTrue()->and($ctx->column())->toBe('tenant_id');
});
