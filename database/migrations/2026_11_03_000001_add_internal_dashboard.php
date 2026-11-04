<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('verifikator');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('dashboard_records', function (Blueprint $table) {
            $table->id();
            $table->string('collection', 30)->index();
            $table->string('record_key', 80);
            $table->json('payload');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->unique(['collection', 'record_key']);
        });
        Schema::create('dashboard_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_activities');
        Schema::dropIfExists('dashboard_records');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'is_active']));
    }
};
