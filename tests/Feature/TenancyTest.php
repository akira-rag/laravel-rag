<?php

declare(strict_types=1);

use Akira\Rag\Facades\Rag;
use Akira\Rag\Tenant\TenantResolver;

it('single-tenant stores null tenant_id', function (): void {
    config()->set('rag.tenancy.enabled', false);
    $res = Rag::ingest([
        'title' => 'Doc',
        'source_type' => 'x',
        'source_ref' => 'y',
        'content' => 'hello world',
    ]);

    $doc = Akira\Rag\Models\RagDocument::query()->find($res['document_id']);
    expect($doc->tenant_id)->toBeNull();
});

it('multi-tenant isolates by resolver', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', new class implements TenantResolver
    {
        public function resolve(): string
        {
            return 'tenant-a';
        }
    }::class);

    Rag::ingest([
        'title' => 'Doc A',
        'source_type' => 'x',
        'source_ref' => 'a',
        'content' => 'alpha',
    ]);

    config()->set('rag.tenancy.resolver', new class implements TenantResolver
    {
        public function resolve(): string
        {
            return 'tenant-b';
        }
    }::class);

    Rag::ingest([
        'title' => 'Doc B',
        'source_type' => 'x',
        'source_ref' => 'b',
        'content' => 'beta',
    ]);

    expect(Akira\Rag\Models\RagDocument::query()->count())->toBe(1);
});
