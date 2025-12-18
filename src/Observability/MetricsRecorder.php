<?php

declare(strict_types=1);

namespace Akira\Rag\Observability;

interface MetricsRecorder
{
    /**
     * @param  array<string,scalar|null>  $tags
     */
    public function increment(string $metric, int $by = 1, array $tags = []): void;

    /**
     * @param  array<string,scalar|null>  $tags
     */
    public function timing(string $metric, float $milliseconds, array $tags = []): void;
}
