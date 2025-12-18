<?php

declare(strict_types=1);

use Akira\Rag\Facades\Rag;

if (! function_exists('rag')) {
    function rag(): mixed
    {
        return Rag::getFacadeRoot();
    }
}
