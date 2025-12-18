<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RagQuery extends Model
{
    use HasUuids;
    use ScopesTenant;

    protected $table = 'rag_queries';

    protected $guarded = [];

    /**
     * @return HasMany<RagQueryChunk, $this>
     */
    public function chunks(): HasMany
    {

        return $this->hasMany(RagQueryChunk::class, 'query_id');
    }

    protected function casts(): array
    {

        return [
            'meta' => 'array',
        ];
    }
}
