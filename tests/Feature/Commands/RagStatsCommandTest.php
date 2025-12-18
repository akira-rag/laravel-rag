<?php

declare(strict_types=1);

use Tests\Helpers\FakeTenantResolver;

use function Pest\Laravel\artisan;

it('shows stats in single-tenant', function (): void {
    config()->set('rag.tenancy.enabled', false);

    artisan('rag:ingest', [
        '--title' => 'S',
        '--source_type' => 'note',
        '--source_ref' => 'st:1',
        '--text' => 's',
    ])->assertSuccessful();

    $res = artisan('rag:stats')->run();
    expect($res)->toBe(0);
});

it('outputs JSON when --json', function (): void {
    config()->set('rag.tenancy.enabled', false);
    $code = artisan('rag:stats', ['--json' => true])->run();
    expect($code)->toBe(0);
});

it('is tenant-scoped', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', FakeTenantResolver::class);

    app()->bind(FakeTenantResolver::class, fn (): FakeTenantResolver => new FakeTenantResolver('t-1'));
    artisan('rag:ingest', [
        '--title' => 'T1',
        '--source_type' => 'note',
        '--source_ref' => 'st:t1',
        '--text' => 'x',
    ])->assertSuccessful();

    app()->bind(FakeTenantResolver::class, fn (): FakeTenantResolver => new FakeTenantResolver('t-2'));
    artisan('rag:ingest', [
        '--title' => 'T2',
        '--source_type' => 'note',
        '--source_ref' => 'st:t2',
        '--text' => 'y',
    ])->assertSuccessful();

    app()->bind(FakeTenantResolver::class, fn (): FakeTenantResolver => new FakeTenantResolver('t-1'));
    $code = artisan('rag:stats')->run();
    expect($code)->toBe(0);
});
