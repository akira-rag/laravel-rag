<?php

declare(strict_types=1);

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Tests\Helpers\FakeTenantResolver;

use function Pest\Laravel\artisan;

it('ingests using --text', function (): void {
    config()->set('rag.tenancy.enabled', false);

    artisan('rag:ingest', [
        '--title' => 'CLI Text',
        '--source_type' => 'faq',
        '--source_ref' => 'cli:text',
        '--text' => 'alpha beta gamma',
        '--meta' => ['lang=en', 'k=v'],
        '--sync' => true,
    ])->assertSuccessful();
    expect(RagDocument::query()->count())->toBe(1);
    expect(RagChunk::query()->count())->toBeGreaterThan(0);
});

it('ingests using --file .txt and parses meta', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $path = storage_path('app/test-ingest.txt');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, str_repeat('x ', 100));

    artisan('rag:ingest', [
        '--title' => 'CLI File',
        '--source_type' => 'note',
        '--source_ref' => 'cli:file',
        '--file' => $path,
        '--meta' => ['lang=en', 'country=CV'],
        '--no-embed' => true,
    ])->assertSuccessful();

    $doc = RagDocument::query()->first();
    expect($doc)->not()->toBeNull();
    expect($doc->tenant_id)->toBeNull();
});

it('fails when file does not exist', function (): void {
    config()->set('rag.tenancy.enabled', false);
    $missing = storage_path('app/does-not-exist.md');
    $code = artisan('rag:ingest', [
        '--title' => 'Missing',
        '--source_type' => 'note',
        '--file' => $missing,
    ])->run();
    expect($code)->toBe(2);
});

it('fails on unsupported file extension', function (): void {
    config()->set('rag.tenancy.enabled', false);
    $pdf = storage_path('app/unsupported.pdf');
    @mkdir(dirname($pdf), 0777, true);
    file_put_contents($pdf, 'dummy');
    $code = artisan('rag:ingest', [
        '--title' => 'Ext',
        '--source_type' => 'note',
        '--file' => $pdf,
    ])->run();
    expect($code)->toBe(2);
});

it('is idempotent for same content', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $payload = [
        '--title' => 'Same',
        '--source_type' => 'note',
        '--source_ref' => 'same:1',
        '--text' => str_repeat('d ', 50),
    ];

    artisan('rag:ingest', $payload)->assertSuccessful();
    $d1 = RagDocument::query()->first();
    $c1 = RagChunk::query()->count();

    artisan('rag:ingest', $payload)->assertSuccessful();
    $d2 = RagDocument::query()->first();
    $c2 = RagChunk::query()->count();

    expect($d1->getKey())->toBe($d2->getKey());
    expect($c1)->toBe($c2);
});

it('stores NULL tenant_id in single-tenant', function (): void {
    config()->set('rag.tenancy.enabled', false);

    artisan('rag:ingest', [
        '--title' => 'Single',
        '--source_type' => 'note',
        '--source_ref' => 'single:1',
        '--text' => 's t',
    ])->assertSuccessful();

    $doc = RagDocument::query()->first();
    expect($doc->tenant_id)->toBeNull();
});

it('isolates data in multi-tenant', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', FakeTenantResolver::class);

    // tenant A
    app()->bind(FakeTenantResolver::class, fn (): FakeTenantResolver => new FakeTenantResolver('tenant-a'));
    artisan('rag:ingest', [
        '--title' => 'A',
        '--source_type' => 'note',
        '--source_ref' => 'mt:a',
        '--text' => 'aaa',
    ])->assertSuccessful();
    $countA = RagDocument::query()->count();
    expect($countA)->toBe(1);

    // tenant B
    app()->bind(FakeTenantResolver::class, fn (): FakeTenantResolver => new FakeTenantResolver('tenant-b'));
    artisan('rag:ingest', [
        '--title' => 'B',
        '--source_type' => 'note',
        '--source_ref' => 'mt:b',
        '--text' => 'bbb',
    ])->assertSuccessful();

    // back to tenant A should still only see A
    app()->bind(FakeTenantResolver::class, fn (): FakeTenantResolver => new FakeTenantResolver('tenant-a'));
    expect(RagDocument::query()->count())->toBe(1);
});
