<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RagQueryChunk extends Model
{
    use HasUuids;
    use ScopesTenant;

    protected $table = 'rag_query_chunks';

    protected $guarded = [];

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
}
