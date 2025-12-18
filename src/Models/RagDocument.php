<?php

declare(strict_types=1);

namespace Akira\Rag\Models;

use Akira\Rag\Database\Factories\RagDocumentFactory;
use Akira\Rag\Support\ScopesTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RagDocument extends Model
{
    /** @use HasFactory<RagDocumentFactory> */
    use HasFactory;

    use HasUuids;
    use ScopesTenant;

    /** @var list<string> */
    protected $fillable
        = [
            'id',
            'tenant_id',
            'title',
            'source_type',
            'source_ref',
            'hash',
            'meta',
        ];

    /**
     * @return HasMany<RagChunk, $this>
     */
    public function chunks(): HasMany
    {

        return $this->hasMany(RagChunk::class, 'document_id');
    }

    /**
     * @return RagDocumentFactory
     */
    protected static function newFactory(): Factory
    {

        return RagDocumentFactory::new();
    }

    protected function casts(): array
    {

        return [
            'meta' => 'array',
        ];
    }
}
