<?php

namespace App\Services;

use App\Services\Analysis\MockProvider;

class CatalogFallbackService
{
    public function __construct(
        private readonly MockProvider $mockProvider,
        private readonly LegalRetrievalService $retrievalService,
    ) {}

    /**
     * Susun rekomendasi pasal dari chunk hasil retrieval PBI 1 (knowledge_chunks)
     * menggunakan ulang logika MockProvider agar skema dan guardrail PBI 2 tetap identik.
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
    public function buildFromChunks(string $chronology, array $chunks, string $reason = 'quota_exceeded'): array
    {
        $result = $this->mockProvider->analyze($chronology, $chunks);
        $catalogNote = 'Rekomendasi pasal di atas disusun langsung dari katalog JDIH terindeks tanpa panggilan AI eksternal.';
        $baseNote = trim((string) ($result['catatan'] ?? ''));
        $note = str_contains($baseNote, $catalogNote)
            ? $baseNote
            : trim($catalogNote.' '.$baseNote);

        return [
            'rujukan' => $result['rujukan'],
            'pasal_relevan' => $result['pasal_relevan'],
            'ancaman_sanksi' => $result['ancaman_sanksi'],
            'perlu_eskalasi' => true,
            'catatan' => $note,
        ];
    }

    /**
     * Ambil chunk langsung dari katalog JDIH (LegalRetrievalService) lalu susun hasil fallback.
     *
     * @return array{
     *     rujukan: list<array{dokumen: string, nomor_tahun: string, tautan: string}>,
     *     pasal_relevan: list<array{pasal: string, dokumen: string, penjelasan_awam: string, alasan_terkait_kronologi: string}>,
     *     ancaman_sanksi: list<array{pasal: string, jenis: string, bunyi_sanksi_ringkas: string, dokumen: string}>,
     *     perlu_eskalasi: bool,
     *     catatan: string
     * }
     */
    public function recommend(string $chronology, ?int $topK = null, string $reason = 'quota_exceeded'): array
    {
        $retrieved = $this->retrievalService->retrieve($chronology, $topK);

        return $this->buildFromChunks($chronology, $retrieved['chunks'], $reason);
    }
}
