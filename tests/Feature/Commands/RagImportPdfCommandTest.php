<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('imports a single PDF file', function (): void {
    $pdf = storage_path('app/docs/one.pdf');
    @mkdir(dirname($pdf), 0777, true);
    // Minimal fake PDF structure containing (text) Tj operators
    file_put_contents($pdf, "%PDF-1.4\n1 0 obj\n<<>>\nstream\n(Hello) Tj\nendstream\nendobj\n%%EOF");

    artisan('rag:import:pdf', [
        'path' => $pdf,
        '--title' => 'PDF One',
        '--source_type' => 'pdf',
        '--source_ref' => 'one',
        '--sync' => true,
    ])->assertSuccessful();
});

it('imports all PDFs in a directory', function (): void {
    $dir = storage_path('app/docs/dir');
    @mkdir($dir, 0777, true);
    file_put_contents($dir.'/a.pdf', "%PDF-1.4\n(Alpha) Tj");
    file_put_contents($dir.'/b.pdf', "%PDF-1.4\n(Beta) Tj");

    artisan('rag:import:pdf', [
        'path' => $dir,
        '--sync' => true,
    ])->assertSuccessful();
});

it('dry-run shows summary without persisting', function (): void {
    $pdf = storage_path('app/docs/dry.pdf');
    @mkdir(dirname($pdf), 0777, true);
    file_put_contents($pdf, "%PDF-1.4\n(Dry) Tj");

    artisan('rag:import:pdf', [
        'path' => $pdf,
        '--dry-run' => true,
    ])->assertSuccessful();
});

it('returns invalid when directory has no PDFs', function (): void {
    $dir = storage_path('app/docs/empty');
    @mkdir($dir, 0777, true);
    $code = artisan('rag:import:pdf', [
        'path' => $dir,
    ])->run();
    expect($code)->toBe(2);
});
