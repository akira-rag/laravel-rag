<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Database\Factories\RagEmbeddingFactory;
use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RagEmbedding extends Model
{
    /** @use HasFactory<RagEmbeddingFactory> */
    use HasFactory;

    use HasUuids;
    use ScopesTenant;

    /** @var list<string> */
    protected $fillable
        = [
            'id',
            'tenant_id',
            'chunk_id',
            'embedding',
            'meta',
        ];

    public function casts(): array
    {

        return [
            'embedding' => 'array',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<RagChunk, $this>
     */
    public function chunk(): BelongsTo
    {

        return $this->belongsTo(RagChunk::class, 'chunk_id');
    }

    /**
     * @return RagEmbeddingFactory
     */
    protected static function newFactory(): Factory
    {

        return RagEmbeddingFactory::new();
    }
}
