<?php

declare(strict_types=1);

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Akira\Rag\Models\RagQueryChunk;

it('resolves model relations', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $doc = RagDocument::query()->create([
        'title' => 'T',
        'source_type' => 's',
        'source_ref' => 'r',
        'hash' => 'h1',
        'meta' => ['a' => 1],
    ]);

    $chunk = RagChunk::query()->create([
        'document_id' => $doc->getKey(),
        'position' => 0,
        'content' => 'content',
        'meta' => [],
    ]);

    $embed = RagEmbedding::query()->create([
        'chunk_id' => $chunk->getKey(),
        'embedding' => json_encode([0.0]),
        'meta' => [],
    ]);

    $query = RagQuery::query()->create([
        'question' => 'q',
        'meta' => [],
    ]);

    $qc = RagQueryChunk::query()->create([
        'query_id' => $query->getKey(),
        'chunk_id' => $chunk->getKey(),
        'score' => 1.0,
        'rank' => 0,
        'meta' => [],
    ]);

    expect($doc->chunks()->count())->toBe(1)
        ->and($chunk->document->getKey())->toBe($doc->getKey())
        ->and($chunk->embedding->chunk->getKey())->toBe($chunk->getKey())
        ->and($query->chunks()->first()->chunk->getKey())->toBe($chunk->getKey())
        ->and($qc->ragQuery->getKey())->toBe($query->getKey());
});
