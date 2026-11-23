<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('llm_daily_usage', function (Blueprint $table) {
            $table->id();
            $table->date('usage_date')->unique();
            $table->unsignedBigInteger('tokens_used')->default(0);
            $table->unsignedInteger('requests_used')->default(0);
            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->timestamp('circuit_open_until')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('llm_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->date('usage_date')->index();
            $table->string('source', 32);
            $table->string('provider', 64);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->string('status', 32);
            $table->string('error_code', 64)->nullable();
            $table->string('session_uuid', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['usage_date', 'status']);
            $table->index(['usage_date', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_usage_logs');
        Schema::dropIfExists('llm_daily_usage');
    }
};
