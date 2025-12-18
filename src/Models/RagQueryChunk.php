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
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use ScopesTenant;

    protected $table = 'rag_query_chunks';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function ragQuery(): BelongsTo
    {
        return $this->belongsTo(RagQuery::class, 'query_id');
    }

    public function chunk(): BelongsTo
    {
        return $this->belongsTo(RagChunk::class, 'chunk_id');
    }
}
