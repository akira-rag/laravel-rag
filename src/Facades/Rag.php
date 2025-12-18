<?php

declare(strict_types=1);

namespace Akira\Rag\Facades;

use Akira\Rag\RagManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see RagManager
 */
final class Rag extends Facade
{
    protected static function getFacadeAccessor(): string
    {

        return 'akira.rag';
    }
}
