<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->nullable()->constrained('dashboard_records')->nullOnDelete();
            $table->string('document_key', 80)->index();
            $table->unsignedInteger('chunk_index');
            $table->string('chunk_hash', 64)->index();
            $table->string('document_name', 255);
            $table->string('document_type', 100);
            $table->string('document_number', 100);
            $table->unsignedSmallInteger('document_year');
            $table->string('article_label', 120);
            $table->string('source_url', 500);
            $table->string('category', 20)->index(); // JDIH or SOP
            $table->string('topic', 100)->nullable()->index();
            $table->text('content');
            $table->text('keywords')->nullable();
            $table->json('embedding');
            $table->string('embedding_model', 80)->default('bhima-legal-embed-v1');
            $table->timestamps();

            $table->unique(['document_key', 'chunk_index']);
        });

        Schema::create('consultation_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_uuid', 64)->unique();
            $table->string('session_fingerprint', 64)->nullable()->index();
            $table->string('title', 200)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        Schema::create('consultation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_session_id')->constrained('consultation_sessions')->cascadeOnDelete();
            $table->string('sender', 20); // user | assistant
            $table->text('content');
            $table->string('rewritten_query', 500)->nullable();
            $table->json('citations')->nullable();
            $table->json('retrieval_meta')->nullable();
            $table->timestamps();

            $table->index(['consultation_session_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_messages');
        Schema::dropIfExists('consultation_sessions');
        Schema::dropIfExists('knowledge_chunks');
    }
};

