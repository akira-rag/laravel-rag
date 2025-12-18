<?php

declare(strict_types=1);

use Akira\Rag\RagManager;

it('chunks deterministically', function (): void {
    $content = str_repeat('word ', 2000);
    $a = resolve(RagManager::class)->chunk($content);
    $b = resolve(RagManager::class)->chunk($content);
    expect($a)->toEqual($b)->and($a)->not()->toBe([]);
});
