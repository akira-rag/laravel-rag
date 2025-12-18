<?php

declare(strict_types=1);

namespace Rag\Rag\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rag\Rag\Rag
 */
final class Rag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Rag\Rag\Rag::class;
    }
}
