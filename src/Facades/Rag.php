<?php

declare(strict_types=1);

namespace Akira\Rag\Facades;

use Illuminate\Support\Facades\Facade;

final class Rag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'akira.rag';
    }
}
