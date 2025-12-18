<?php

declare(strict_types=1);

namespace Akira\Rag\Data;

use Spatie\LaravelData\Data;

final class IngestPayload extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly string $source_type,
        public readonly string $source_ref,
        public readonly string $content,
        /** @var array<string,mixed> */
        public readonly array $meta = [],
    ) {}
}
