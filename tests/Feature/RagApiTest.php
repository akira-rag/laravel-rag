<?php

declare(strict_types=1);

use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Facades\Rag;

it('ingests and is idempotent', function (): void {
    $payload = [
        'title' => 'Law',
        'source_type' => 'law',
        'source_ref' => 'law:2021:123',
        'content' => str_repeat('a ', 1200),
        'meta' => ['lang' => 'en'],
    ];

    $first = Rag::ingest($payload);
    $second = Rag::ingest($payload);

    expect($first['document_id'])->toBe($second['document_id']);
});

it('rejects tenant_id in payload', function (): void {
    Rag::ingest([
        'title' => 'A',
        'source_type' => 't',
        'source_ref' => 'r',
        'content' => 'x',
    ]);

    expect(fn () => Rag::ingest([
        'title' => 'A', 'source_type' => 't', 'source_ref' => 'r', 'content' => 'x', 'tenant_id' => 'bad',
    ]))->toThrow(InvalidPayload::class);
});

it('answers using retrieved chunks and caches', function (): void {
    $payload = [
        'title' => 'Law',
        'source_type' => 'law',
        'source_ref' => 'law:2021:xyz',
        'content' => 'hello alpha beta gamma',
    ];
    Rag::ingest($payload);

    $a = Rag::ask('What?', [], []);
    $b = Rag::ask('What?', [], []);
    expect($a)->toEqual($b)->and($a['chunks'])->not()->toBe([]);
});
