<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Database\Factories\RagQueryFactory;
use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RagQuery extends Model
{
    /** @use HasFactory<RagQueryFactory> */
    use HasFactory;

    use HasUuids;
    use ScopesTenant;

    /** @var list<string> */
    protected $fillable
        = [
            'id',
            'tenant_id',
            'question',
            'meta',
        ];

    /**
     * @return HasMany<RagQueryChunk, $this>
     */
    public function chunks(): HasMany
    {

        return $this->hasMany(RagQueryChunk::class, 'query_id');
    }

    /**
     * @return RagQueryFactory
     */
    protected static function newFactory(): Factory
    {

        return RagQueryFactory::new();
    }

    protected function casts(): array
    {

        return [
            'meta' => 'array',
        ];
    }
}
