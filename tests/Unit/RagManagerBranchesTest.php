<?php

declare(strict_types=1);

use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Facades\Rag;
use Akira\Rag\RagManager;

it('throws on missing ingest fields', function (): void {
    expect(fn () => resolve(RagManager::class)->ingest([
        'source_type' => 's', 'source_ref' => 'r', 'content' => 'c',
    ]))->toThrow(InvalidPayload::class);
});

it('returns empty chunks for empty content', function (): void {
    expect(resolve(RagManager::class)->chunk(''))->toBe([]);
});

it('applies retrieval filters by document_id', function (): void {
    $ing = Rag::ingest([
        'title' => 'F', 'source_type' => 's', 'source_ref' => 'r:filter', 'content' => 'one two three four five',
    ]);

    $res = resolve(RagManager::class)->ask('filter-q', ['document_id' => $ing['document_id']]);
    expect($res['chunks'])->not()->toBe([]);
});
