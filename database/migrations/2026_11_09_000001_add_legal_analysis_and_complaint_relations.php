<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_messages', 'legal_analysis')) {
                $table->json('legal_analysis')->nullable()->after('retrieval_meta');
            }
        });

        Schema::table('dashboard_records', function (Blueprint $table) {
            if (! Schema::hasColumn('dashboard_records', 'consultation_session_id')) {
                $table->foreignId('consultation_session_id')
                    ->nullable()
                    ->after('assigned_to')
                    ->constrained('consultation_sessions')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('dashboard_records', 'consultation_message_id')) {
                $table->foreignId('consultation_message_id')
                    ->nullable()
                    ->after('consultation_session_id')
                    ->constrained('consultation_messages')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_records', function (Blueprint $table) {
            if (Schema::hasColumn('dashboard_records', 'consultation_message_id')) {
                $table->dropConstrainedForeignId('consultation_message_id');
            }
            if (Schema::hasColumn('dashboard_records', 'consultation_session_id')) {
                $table->dropConstrainedForeignId('consultation_session_id');
            }
        });

        Schema::table('consultation_messages', function (Blueprint $table) {
            if (Schema::hasColumn('consultation_messages', 'legal_analysis')) {
                $table->dropColumn('legal_analysis');
            }
        });
    }
};
