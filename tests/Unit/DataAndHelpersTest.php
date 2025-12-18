<?php

declare(strict_types=1);

use Akira\Rag\Data\AskPayload;
use Akira\Rag\Data\IngestPayload;
use Akira\Rag\Support\Prism;

it('constructs data DTOs', function (): void {
    $ingest = new IngestPayload('T', 's', 'r', 'content', ['a' => 1]);
    expect($ingest->title)->toBe('T')
        ->and($ingest->source_type)->toBe('s')
        ->and($ingest->source_ref)->toBe('r')
        ->and($ingest->content)->toBe('content')
        ->and($ingest->meta['a'])->toBe(1);

    $ask = new AskPayload('Q', ['x' => 'y'], ['m' => true]);
    expect($ask->question)->toBe('Q')
        ->and($ask->filters['x'])->toBe('y')
        ->and($ask->meta['m'])->toBeTrue();
});

it('prism helper and global helper work', function (): void {
    expect(Prism::highlight('<?php echo 1;'))
        ->toBe('<?php echo 1;');

    expect(function_exists('rag'))->toBeTrue();
    expect(rag())->toBeInstanceOf(Akira\Rag\RagService::class);
});
