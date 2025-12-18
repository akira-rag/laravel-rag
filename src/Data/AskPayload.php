<?php

declare(strict_types=1);

namespace Akira\Rag\Data;

use Spatie\LaravelData\Data;

final class AskPayload extends Data
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $question,
        public readonly array $filters = [],
        public readonly array $meta = [],
    ) {}
}
