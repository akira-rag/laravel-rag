<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // rag_documents
        Schema::create('rag_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('title');
            $table->string('source_type');
            $table->string('source_ref');
            $table->string('hash')->unique();
            $json = Schema::getConnection()->getDriverName() === 'pgsql' ? 'jsonb' : 'json';
            $table->{$json}('meta')->nullable();
            $table->timestamps();
        });

        // rag_chunks
        Schema::create('rag_chunks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('document_id');
            $table->unsignedInteger('position');
            $table->longText('content');
            // placeholder for non-PgSQL; replaced below for Postgres
            $table->text('content_tsvector')->nullable();
            $json = Schema::getConnection()->getDriverName() === 'pgsql' ? 'jsonb' : 'json';
            $table->{$json}('meta')->nullable();
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('rag_documents')->cascadeOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE rag_chunks DROP COLUMN content_tsvector");
            DB::statement("ALTER TABLE rag_chunks ADD COLUMN content_tsvector tsvector GENERATED ALWAYS AS (to_tsvector('simple', content)) STORED");
            DB::statement("CREATE INDEX rag_chunks_content_tsvector_idx ON rag_chunks USING GIN (content_tsvector)");
        }

        // rag_embeddings
        Schema::create('rag_embeddings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('chunk_id');
            // placeholder; replaced below for Postgres pgvector
            $table->text('embedding')->nullable();
            $json = Schema::getConnection()->getDriverName() === 'pgsql' ? 'jsonb' : 'json';
            $table->{$json}('meta')->nullable();
            $table->timestamps();

            $table->foreign('chunk_id')->references('id')->on('rag_chunks')->cascadeOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE rag_embeddings ALTER COLUMN embedding TYPE vector(1536)');
            DB::statement('CREATE INDEX rag_embeddings_embedding_hnsw ON rag_embeddings USING hnsw (embedding vector_l2_ops)');
        }

        // rag_queries
        Schema::create('rag_queries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->text('question');
            $json = Schema::getConnection()->getDriverName() === 'pgsql' ? 'jsonb' : 'json';
            $table->{$json}('meta')->nullable();
            $table->timestamps();
        });

        // rag_query_chunks
        Schema::create('rag_query_chunks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('query_id');
            $table->uuid('chunk_id');
            $table->float('score');
            $table->unsignedInteger('rank');
            $json = Schema::getConnection()->getDriverName() === 'pgsql' ? 'jsonb' : 'json';
            $table->{$json}('meta')->nullable();
            $table->timestamps();

            $table->foreign('query_id')->references('id')->on('rag_queries')->cascadeOnDelete();
            $table->foreign('chunk_id')->references('id')->on('rag_chunks')->cascadeOnDelete();
        });
    }
};

