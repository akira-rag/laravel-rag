<?php

declare(strict_types=1);

namespace Akira\Rag\Support;

use Akira\Rag\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait ScopesTenant
{
    public static function bootScopesTenant(): void
    {
        static::addGlobalScope('akira.rag.tenant', function (Builder $query): void {
            /** @var TenantContext $ctx */
            $ctx = resolve(TenantContext::class);
            $column = $ctx->column();
            $tenant = $ctx->current();

            if ($ctx->enabled()) {
                $query->where($query->getModel()->getTable().'.'.$column, $tenant);
            } else {
                $query->whereNull($query->getModel()->getTable().'.'.$column);
            }
        });
    }
}
