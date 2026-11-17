<?php

namespace App\Contracts;

interface AnalysisProvider
{
    /**
     * Analisis kronologi warga berdasarkan potongan pasal/SOP hasil retrieval
     * dan kembalikan struktur analisis hukum sesuai skema tetap.
     *
     * @param  list<array<string, mixed>>  $chunks
     * @return array{
     *     rujukan: list<array{dokumen: string, nomor_tahun: string, tautan: string}>,
     *     pasal_relevan: list<array{pasal: string, dokumen: string, penjelasan_awam: string, alasan_terkait_kronologi: string}>,
     *     ancaman_sanksi: list<array{pasal: string, jenis: string, bunyi_sanksi_ringkas: string, dokumen: string}>,
     *     perlu_eskalasi: bool,
     *     catatan: string
     * }
     */
    public function analyze(string $chronology, array $chunks): array;

    /**
     * Nama pengenal provider ('mock' atau 'deepseek').
     */
    public function name(): string;
}
