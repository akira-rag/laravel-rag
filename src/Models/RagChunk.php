<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class RagChunk extends Model
{
    use HasUuids;
    use ScopesTenant;

    protected $table = 'rag_chunks';

    protected $guarded = [];

    public function casts(): array
    {

        return [
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<RagDocument, $this>
     */
    public function document(): BelongsTo
    {

        return $this->belongsTo(RagDocument::class, 'document_id');
    }

    /**
     * @return HasOne<RagEmbedding, $this>
     */
    public function embedding(): HasOne
    {

        return $this->hasOne(RagEmbedding::class, 'chunk_id');
    }
}
