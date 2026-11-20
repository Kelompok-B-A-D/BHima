<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_chunks', function (Blueprint $table) {
            if (! Schema::hasColumn('knowledge_chunks', 'pasal_nomor')) {
                $table->string('pasal_nomor', 40)->nullable()->index()->after('article_label');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'ayat')) {
                $table->string('ayat', 20)->nullable()->after('pasal_nomor');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'is_ketentuan_pidana')) {
                $table->boolean('is_ketentuan_pidana')->default(false)->index()->after('ayat');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'sanksi_jenis')) {
                $table->string('sanksi_jenis', 30)->nullable()->after('is_ketentuan_pidana');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'sanksi_teks')) {
                $table->text('sanksi_teks')->nullable()->after('sanksi_jenis');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'rujuk_pasal')) {
                $table->string('rujuk_pasal', 120)->nullable()->index()->after('sanksi_teks');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'status_berlaku')) {
                $table->string('status_berlaku', 20)->default('berlaku')->index()->after('rujuk_pasal');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'diubah_oleh')) {
                $table->string('diubah_oleh', 255)->nullable()->after('status_berlaku');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'dicabut_oleh')) {
                $table->string('dicabut_oleh', 255)->nullable()->after('diubah_oleh');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'source_page_url')) {
                $table->string('source_page_url', 500)->nullable()->after('source_url');
            }
            if (! Schema::hasColumn('knowledge_chunks', 'pdf_page')) {
                $table->unsignedSmallInteger('pdf_page')->nullable()->after('source_page_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_chunks', function (Blueprint $table) {
            $columns = [
                'pasal_nomor',
                'ayat',
                'is_ketentuan_pidana',
                'sanksi_jenis',
                'sanksi_teks',
                'rujuk_pasal',
                'status_berlaku',
                'diubah_oleh',
                'dicabut_oleh',
                'source_page_url',
                'pdf_page',
            ];

            $existing = array_values(array_filter(
                $columns,
                fn (string $col) => Schema::hasColumn('knowledge_chunks', $col)
            ));

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
