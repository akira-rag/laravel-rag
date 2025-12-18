<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RagDocument extends Model
{
    use HasUuids;
    use ScopesTenant;

    protected $table = 'rag_documents';

    protected $guarded = [];

    /**
     * @return HasMany<RagChunk, $this>
     */
    public function chunks(): HasMany
    {

        return $this->hasMany(RagChunk::class, 'document_id');
    }

    protected function casts(): array
    {

        return [
            'meta' => 'array',
        ];
    }
}
