<?php

declare(strict_types=1);

namespace Akira\Rag\Tests\Helpers;

trait CreatesText
{
    protected function lorem(int $words): string
    {
        return implode(' ', array_map(fn (int $i): string => 'w'.$i, range(1, $words)));
    }
}
