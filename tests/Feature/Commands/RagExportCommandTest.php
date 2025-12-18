<?php

declare(strict_types=1);

use Tests\Helpers\FakeTenantResolver;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    config()->set('rag.tenancy.enabled', false);
    artisan('rag:ingest', [
        '--title' => 'Exportable',
        '--source_type' => 'note',
        '--source_ref' => 'exp:1',
        '--text' => 'export text',
    ])->assertSuccessful();
});

it('exports to a file', function (): void {
    $out = storage_path('app/rag/exports/single/test-export.json');
    @mkdir(dirname($out), 0777, true);

    artisan('rag:export', [
        '--output' => $out,
        '--format' => 'json',
        '--include-embeddings' => true,
        '--include-audit' => true,
    ])->assertSuccessful();

    expect(file_exists($out))->toBeTrue();
    $data = json_decode(file_get_contents($out), true);
    expect($data['documents'])->not()->toBeEmpty();
});

it('supports compression and encryption', function (): void {
    $out = storage_path('app/rag/exports/single/enc.json');
    @mkdir(dirname($out), 0777, true);

    artisan('rag:export', [
        '--output' => $out,
        '--compress' => true,
        '--encrypt' => true,
    ])->assertSuccessful();

    expect(file_exists($out.'.gz'))->toBeTrue();
});

it('exports without embeddings and audit explicitly', function (): void {
    $out = storage_path('app/rag/exports/single/only-core.json');
    @mkdir(dirname($out), 0777, true);

    artisan('rag:export', [
        '--output' => $out,
        '--include-embeddings' => false,
        '--include-audit' => false,
    ])->assertSuccessful();

    $data = json_decode(file_get_contents($out), true);
    expect($data['embeddings'])->toBeArray()->toBeEmpty()
        ->and($data['queries'])->toBeArray()->toBeEmpty();
});

it('is tenant-scoped in multi-tenant', function (): void {
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', FakeTenantResolver::class);

    // tenant T1
    app()->bind(FakeTenantResolver::class, fn (): FakeTenantResolver => new FakeTenantResolver('T1'));
    artisan('rag:ingest', [
        '--title' => 'T1 D',
        '--source_type' => 'note',
        '--source_ref' => 't1:1',
        '--text' => 't1',
    ])->assertSuccessful();

    // export for T1
    $out1 = storage_path('app/rag/exports/T1/t1.json');
    @mkdir(dirname($out1), 0777, true);
    artisan('rag:export', ['--output' => $out1])->assertSuccessful();
    expect(file_exists($out1))->toBeTrue();
});

it('returns invalid for unsupported format', function (): void {
    $out = storage_path('app/rag/exports/single/invalid.csv');
    @mkdir(dirname($out), 0777, true);
    artisan('rag:export', [
        '--output' => $out,
        '--format' => 'csv',
    ])->assertExitCode(2);
});
