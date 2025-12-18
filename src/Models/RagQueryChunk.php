<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Database\Factories\RagQueryChunkFactory;
use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RagQueryChunk extends Model
{
    /** @use HasFactory<RagQueryChunkFactory> */
    use HasFactory;

    use HasUuids;
    use ScopesTenant;

    /** @var list<string> */
    protected $fillable
        = [
            'id',
            'tenant_id',
            'query_id',
            'chunk_id',
            'score',
            'rank',
            'meta',
        ];

    public function casts(): array
    {

        return [
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<RagQuery, $this>
     */
    public function ragQuery(): BelongsTo
    {

        return $this->belongsTo(RagQuery::class, 'query_id');
    }

    /**
     * @return BelongsTo<RagChunk, $this>
     */
    public function chunk(): BelongsTo
    {

        return $this->belongsTo(RagChunk::class, 'chunk_id');
    }

    /**
     * @return RagQueryChunkFactory
     */
    protected static function newFactory(): Factory
    {

        return RagQueryChunkFactory::new();
    }
}
