<?php

declare(strict_types=1);

namespace Akira\Rag\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array{document_id:string, chunks:int} ingest(array $payload)
 * @method static array{answer:string,chunks:array<int,array{id:string,score:float}>,query_id:string} ask(string $question, array $filters = [])
 */
final class Rag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'akira.rag';
    }
}
