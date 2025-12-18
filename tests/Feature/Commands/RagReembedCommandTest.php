<?php

declare(strict_types=1);

use Akira\Rag\Models\RagDocument;
use Tests\Helpers\FakeTenantResolver;
use function Pest\Laravel\artisan;

beforeEach(function (): void {
    config()->set('rag.tenancy.enabled', false);
    artisan('rag:ingest', [
        '--title' => 'ReembedDoc',
        '--source_type' => 'note',
        '--source_ref' => 're:1',
        '--text' => 'content x y z',
    ])->assertSuccessful();
});

it('reembeds with --all', function (): void {
    artisan('rag:reembed', ['--all' => true, '--sync' => true])->assertSuccessful();
});

it('reembeds with --document_id', function (): void {
    $id = Akira\Rag\Models\RagDocument::query()->first()->getKey();
    artisan('rag:reembed', ['--document_id' => $id, '--model' => 'text-embedding-3-small'])->assertSuccessful();
});

it('scopes reembed by tenant', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', FakeTenantResolver::class);

    // tenant A
    app()->bind(FakeTenantResolver::class, fn () => new FakeTenantResolver('tenant-a'));
    artisan('rag:ingest', [
        '--title' => 'A',
        '--source_type' => 'note',
        '--source_ref' => 're:a',
        '--text' => 'aaa',
    ])->assertSuccessful();
    $aId = RagDocument::query()->first()->getKey();

    // tenant B
    app()->bind(FakeTenantResolver::class, fn () => new FakeTenantResolver('tenant-b'));
    artisan('rag:ingest', [
        '--title' => 'B',
        '--source_type' => 'note',
        '--source_ref' => 're:b',
        '--text' => 'bbb',
    ])->assertSuccessful();

    // reembed only for current tenant (B)
    artisan('rag:reembed', ['--all' => true])->assertSuccessful();
    app()->bind(FakeTenantResolver::class, fn () => new FakeTenantResolver('tenant-a'));
    expect(RagDocument::query()->find($aId))->not()->toBeNull();
});
