<?php

declare(strict_types=1);

use Akira\Rag\Database\Factories\RagChunkFactory;
use Akira\Rag\Database\Factories\RagDocumentFactory;
use Akira\Rag\Database\Factories\RagEmbeddingFactory;
use Akira\Rag\Database\Factories\RagQueryChunkFactory;
use Akira\Rag\Database\Factories\RagQueryFactory;
use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Akira\Rag\Models\RagEmbedding;
use Akira\Rag\Models\RagQuery;
use Akira\Rag\Models\RagQueryChunk;

it('covers RagDocument factory and casts', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $factory = RagDocument::factory();
    expect($factory)->toBeInstanceOf(RagDocumentFactory::class);

    $doc = RagDocument::factory()->create(['meta' => ['key' => 'value']]);
    expect($doc->meta)->toBeArray()
        ->and($doc->meta)->toHaveKey('key')
        ->and($doc->meta['key'])->toBe('value')
        ->and($doc->getCasts())->toHaveKey('meta')
        ->and($doc->getCasts()['meta'])->toBe('array');
});

it('covers RagChunk factory and casts', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $factory = RagChunk::factory();
    expect($factory)->toBeInstanceOf(RagChunkFactory::class);

    $chunk = RagChunk::factory()->create(['meta' => ['key' => 'value']]);
    expect($chunk->meta)->toBeArray()
        ->and($chunk->meta)->toHaveKey('key')
        ->and($chunk->meta['key'])->toBe('value')
        ->and($chunk->getCasts())->toHaveKey('meta')
        ->and($chunk->getCasts()['meta'])->toBe('array');
});

it('covers RagEmbedding factory and casts', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $factory = RagEmbedding::factory();
    expect($factory)->toBeInstanceOf(RagEmbeddingFactory::class);

    $embedding = RagEmbedding::factory()->create(['meta' => ['key' => 'value']]);
    expect($embedding->meta)->toBeArray()
        ->and($embedding->meta)->toHaveKey('key')
        ->and($embedding->meta['key'])->toBe('value')
        ->and($embedding->getCasts())->toHaveKey('meta')
        ->and($embedding->getCasts()['meta'])->toBe('array')
        ->and($embedding->getCasts())->toHaveKey('embedding')
        ->and($embedding->getCasts()['embedding'])->toBe('array');
});

it('covers RagQuery factory and casts', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $factory = RagQuery::factory();
    expect($factory)->toBeInstanceOf(RagQueryFactory::class);

    $query = RagQuery::factory()->create(['meta' => ['key' => 'value']]);
    expect($query->meta)->toBeArray()
        ->and($query->meta)->toHaveKey('key')
        ->and($query->meta['key'])->toBe('value')
        ->and($query->getCasts())->toHaveKey('meta')
        ->and($query->getCasts()['meta'])->toBe('array');
});

it('covers RagQueryChunk factory and casts', function (): void {
    config()->set('rag.tenancy.enabled', false);

    $factory = RagQueryChunk::factory();
    expect($factory)->toBeInstanceOf(RagQueryChunkFactory::class);

    $queryChunk = RagQueryChunk::factory()->create(['meta' => ['key' => 'value']]);
    expect($queryChunk->meta)->toBeArray()
        ->and($queryChunk->meta)->toHaveKey('key')
        ->and($queryChunk->meta['key'])->toBe('value')
        ->and($queryChunk->getCasts())->toHaveKey('meta')
        ->and($queryChunk->getCasts()['meta'])->toBe('array');
});
