<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LegalIngestionService
{
    public function __construct(
        private readonly EmbeddingService $embeddingService
    ) {}

    /**
     * Proses seluruh dokumen JDIH dan SOP Layanan yang ada di dashboard_records.
     * Bersifat idempotent (dijalankan berulang kali tidak menggandakan data).
     *
     * @return array{documents_processed: int, jdih_count: int, sop_count: int, chunks_indexed: int}
     */
    public function ingestAll(): array
    {
        $records = DB::table('dashboard_records')
            ->whereIn('collection', ['documents', 'contents'])
            ->orderBy('id')
            ->get();

        $documentsProcessed = 0;
        $jdihCount = 0;
        $sopCount = 0;
        $chunksIndexed = 0;
        $activeDocumentKeys = [];

        DB::transaction(function () use ($records, &$documentsProcessed, &$jdihCount, &$sopCount, &$chunksIndexed, &$activeDocumentKeys) {
            foreach ($records as $record) {
                $payload = json_decode((string) $record->payload, true) ?: [];

                if ($record->collection === 'contents' && ($payload['category'] ?? '') !== 'SOP') {
                    continue;
                }

                $count = $this->ingestRecord($record, $payload);
                if ($count > 0) {
                    $documentsProcessed++;
                    $chunksIndexed += $count;
                    $activeDocumentKeys[] = $record->record_key;
                    if ($record->collection === 'documents') {
                        $jdihCount++;
                    } else {
                        $sopCount++;
                    }
                }
            }

            if (! empty($activeDocumentKeys)) {
                DB::table('knowledge_chunks')
                    ->whereNotIn('document_key', $activeDocumentKeys)
                    ->delete();
            }
        });

        return [
            'documents_processed' => $documentsProcessed,
            'jdih_count' => $jdihCount,
            'sop_count' => $sopCount,
            'chunks_indexed' => $chunksIndexed,
        ];
    }

    /**
     * Proses satu record dokumen JDIH atau SOP menjadi potongan chunk dan simpan secara idempotent.
     *
     * @param  array<string, mixed>  $payload
     */
    public function ingestRecord(object $record, array $payload): int
    {
        $isJdih = $record->collection === 'documents';
        $category = $isJdih ? 'JDIH' : 'SOP';
        $docKey = (string) ($record->record_key ?: ($payload['id'] ?? 'DOC-'.$record->id));
        $docName = trim((string) ($payload['title'] ?? 'Dokumen '.$docKey));
        $docNumber = trim((string) ($payload['number'] ?? ($isJdih ? $docKey : 'SOP/'.$docKey)));
        $docYear = (int) ($payload['year'] ?? (isset($payload['updated']) ? substr((string) $payload['updated'], 0, 4) : date('Y')));
        if ($docYear < 1900 || $docYear > 2100) {
            $docYear = (int) date('Y');
        }
        $docType = trim((string) ($payload['type'] ?? $this->inferDocumentType($docName, $docNumber, $category)));
        $topic = trim((string) ($payload['topic'] ?? $payload['category'] ?? 'Umum'));
        $baseUrl = trim((string) ($payload['source_url'] ?? '/rujukan/'.rawurlencode($docKey)));

        $rawText = $this->extractText($record, $payload);
        if (trim($rawText) === '') {
            return 0;
        }

        $chunks = $this->chunkDocumentText($rawText, $docName, $docNumber, (string) ($payload['summary'] ?? ''));
        if (empty($chunks)) {
            return 0;
        }

        $now = now();
        $embeddingModel = (string) config('rag.embedding.model', 'bhima-legal-embed-v1');

        $chunkRows = [];
        foreach ($chunks as $index => $chunk) {
            $articleLabel = $chunk['article_label'];
            $anchor = Str::slug($articleLabel);
            $sourceUrl = str_contains($baseUrl, '#') ? $baseUrl : $baseUrl.'#'.$anchor;

            $contextForEmbedding = implode(' ', array_filter([
                $docName,
                $docNumber,
                $topic,
                $articleLabel,
                $chunk['content'],
            ]));

            $tokens = $this->embeddingService->tokenize($contextForEmbedding);
            $keywords = implode(' ', array_unique($tokens));
            $embedding = $this->embeddingService->embed($contextForEmbedding);
            $chunkHash = hash('sha256', implode('|', [$docKey, $index, $articleLabel, $chunk['content']]));

            $chunkRows[] = [
                'document_key' => $docKey,
                'chunk_index' => $index,
                'record_id' => $record->id,
                'chunk_hash' => $chunkHash,
                'document_name' => $docName,
                'document_type' => $docType,
                'document_number' => $docNumber,
                'document_year' => $docYear,
                'article_label' => $articleLabel,
                'source_url' => $sourceUrl,
                'category' => $category,
                'topic' => $topic,
                'content' => $chunk['content'],
                'keywords' => $keywords,
                'embedding' => json_encode($embedding),
                'embedding_model' => $embeddingModel,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        DB::table('knowledge_chunks')->upsert(
            $chunkRows,
            ['document_key', 'chunk_index'],
            [
                'record_id',
                'chunk_hash',
                'document_name',
                'document_type',
                'document_number',
                'document_year',
                'article_label',
                'source_url',
                'category',
                'topic',
                'content',
                'keywords',
                'embedding',
                'embedding_model',
                'updated_at',
            ]
        );

        // Hapus sisa chunk lama apabila jumlah chunk berkurang saat re-ingestion
        DB::table('knowledge_chunks')
            ->where('document_key', $docKey)
            ->where('chunk_index', '>=', count($chunks))
            ->delete();

        // Perbarui status ekstraksi dan indeksasi pada dokumen JDIH
        if ($isJdih && (($payload['status'] ?? '') !== 'Terindeks' || ($payload['extraction'] ?? '') !== 'Berhasil')) {
            $payload['extraction'] = 'Berhasil';
            $payload['status'] = 'Terindeks';
            DB::table('dashboard_records')
                ->where('id', $record->id)
                ->update([
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'updated_at' => $now,
                ]);
        }

        return count($chunks);
    }

    /**
     * Ekstrak teks dari berkas PDF (jika ada) serta field body/summary pada payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function extractText(object $record, array $payload): string
    {
        $parts = [];

        if (! empty($record->file_path) && Storage::disk('local')->exists($record->file_path)) {
            $pdfBytes = (string) Storage::disk('local')->get($record->file_path);
            $extractedPdf = $this->extractPdfText($pdfBytes);
            if ($extractedPdf !== '') {
                $parts[] = $extractedPdf;
            }
        }

        if (! empty($payload['body']) && is_string($payload['body'])) {
            $parts[] = trim($payload['body']);
        }

        if (empty($parts) && ! empty($payload['summary']) && is_string($payload['summary'])) {
            $parts[] = trim($payload['summary']);
        }

        return trim(implode("\n\n", $parts));
    }

    /**
     * Pecah teks dokumen hukum per Pasal/Ayat.
     * Fallback: pecah per paragraf dengan overlap bila struktur Pasal tidak ditemukan.
     *
     * @return list<array{article_label: string, content: string}>
     */
    public function chunkDocumentText(string $text, string $docName = '', string $docNumber = '', string $summary = ''): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));
        if ($normalized === '') {
            return [];
        }

        // Pola pemecahan berbasis Pasal / Ayat
        $pattern = '/(?=^\s*(?:Pasal\s+\d+[A-Z]?(?:\s+Ayat\s*\(\d+\))?(?:[^\n]*)?)\s*$)/imu';
        $sections = preg_split($pattern, $normalized, -1, PREG_SPLIT_NO_EMPTY);

        if (is_array($sections) && count($sections) >= 1 && preg_match('/^\s*Pasal\s+\d+/iu', $sections[0])) {
            $chunks = [];
            foreach ($sections as $section) {
                $lines = array_values(array_filter(array_map('trim', explode("\n", trim($section))), fn ($l) => $l !== ''));
                if (empty($lines)) {
                    continue;
                }

                $header = $lines[0];
                $body = trim(implode(' ', array_slice($lines, 1)));
                if ($body === '') {
                    $body = $header;
                }

                if (preg_match('/^(Pasal\s+\d+[A-Z]?(?:\s+(?:Ayat\s*\(\d+\)|huruf\s+[a-z]))?)/iu', $header, $m)) {
                    $articleLabel = trim($m[1]);
                    $remainderHeader = trim(mb_substr($header, mb_strlen($m[0])));
                    if ($remainderHeader !== '' && ! str_starts_with($body, $remainderHeader)) {
                        $body = $remainderHeader.': '.$body;
                    }
                } else {
                    $articleLabel = mb_substr($header, 0, 80);
                }

                // Jika di dalam satu Pasal masih terdapat beberapa Ayat (1), (2) yang panjang, pecah per Ayat
                $subAyat = preg_split('/(?=\bAyat\s*\(\d+\)|\n\(\d+\)\s+)/iu', $body, -1, PREG_SPLIT_NO_EMPTY);
                if (is_array($subAyat) && count($subAyat) > 1) {
                    foreach ($subAyat as $idx => $ayatText) {
                        $ayatClean = trim($ayatText);
                        if ($ayatClean === '') {
                            continue;
                        }
                        $ayatLabel = $articleLabel;
                        if (preg_match('/^(?:Ayat\s*)?\((\d+)\)/iu', $ayatClean, $am)) {
                            if (! stripos($articleLabel, 'Ayat')) {
                                $ayatLabel = $articleLabel.' Ayat ('.$am[1].')';
                            }
                        } elseif ($idx > 0) {
                            $ayatLabel = $articleLabel.' Bagian '.($idx + 1);
                        }
                        $chunks[] = [
                            'article_label' => $ayatLabel,
                            'content' => $ayatClean,
                        ];
                    }
                } else {
                    $chunks[] = [
                        'article_label' => $articleLabel,
                        'content' => $body,
                    ];
                }
            }

            if (! empty($chunks)) {
                return $chunks;
            }
        }

        // Fallback: pecah per paragraf dengan overlap
        return $this->chunkByParagraphWithOverlap($normalized, $summary);
    }

    /**
     * Fallback chunking per paragraf dengan overlap.
     *
     * @return list<array{article_label: string, content: string}>
     */
    public function chunkByParagraphWithOverlap(string $text, string $summary = ''): array
    {
        $maxChars = max(200, (int) config('rag.retrieval.chunk_max_chars', 600));
        $overlapChars = max(40, min(250, (int) config('rag.retrieval.chunk_overlap_chars', 120)));

        $paragraphs = preg_split('/\n\s*\n/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [$text];
        $chunks = [];
        $buffer = '';
        $index = 1;

        foreach ($paragraphs as $para) {
            $para = trim(preg_replace('/\s+/u', ' ', $para) ?? $para);
            if ($para === '') {
                continue;
            }

            if ($buffer !== '' && (mb_strlen($buffer) + 1 + mb_strlen($para)) > $maxChars) {
                $chunks[] = [
                    'article_label' => 'Paragraf '.$index,
                    'content' => $buffer,
                ];
                $index++;
                $overlapText = mb_substr($buffer, max(0, mb_strlen($buffer) - $overlapChars));
                $buffer = trim($overlapText.' '.$para);
            } else {
                $buffer = $buffer === '' ? $para : $buffer.' '.$para;
            }
        }

        if ($buffer !== '') {
            // Jika seluruh teks masih terlalu panjang dalam 1 paragraf, pecah dengan sliding window
            if (mb_strlen($buffer) > $maxChars && empty($chunks)) {
                $step = max(100, $maxChars - $overlapChars);
                $len = mb_strlen($buffer);
                for ($offset = 0; $offset < $len; $offset += $step) {
                    $slice = trim(mb_substr($buffer, $offset, $maxChars));
                    if ($slice === '') {
                        continue;
                    }
                    $chunks[] = [
                        'article_label' => 'Paragraf '.$index,
                        'content' => $slice,
                    ];
                    $index++;
                    if ($offset + $maxChars >= $len) {
                        break;
                    }
                }
            } else {
                $chunks[] = [
                    'article_label' => 'Paragraf '.$index,
                    'content' => $buffer,
                ];
            }
        }

        return $chunks;
    }

    /**
     * Ekstraksi teks ringan dari berkas PDF tanpa dependensi eksternal berat.
     */
    private function extractPdfText(string $pdfBytes): string
    {
        $extracted = [];

        // Dekompresi stream FlateDecode jika tersedia
        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $pdfBytes, $streamMatches)) {
            foreach ($streamMatches[1] as $rawStream) {
                $decoded = @gzuncompress($rawStream);
                if ($decoded === false) {
                    $decoded = @gzinflate($rawStream);
                }
                $target = is_string($decoded) ? $decoded : $rawStream;
                if (preg_match_all('/\(([^()\\\\]*(?:\\\\.[^()\\\\]*)*)\)\s*Tj/s', $target, $tjMatches)) {
                    foreach ($tjMatches[1] as $str) {
                        $extracted[] = stripcslashes($str);
                    }
                }
            }
        }

        if (empty($extracted) && preg_match_all('/\(([^()\\\\]*(?:\\\\.[^()\\\\]*)*)\)\s*Tj/s', $pdfBytes, $directMatches)) {
            foreach ($directMatches[1] as $str) {
                $extracted[] = stripcslashes($str);
            }
        }

        $text = trim(implode(' ', $extracted));
        $text = preg_replace('/[^\P{C}\n\t]+/u', ' ', $text) ?? '';

        return trim($text);
    }

    private function inferDocumentType(string $name, string $number, string $category): string
    {
        if ($category === 'SOP' || str_starts_with(strtoupper($number), 'SOP')) {
            return 'SOP Layanan';
        }
        $combined = mb_strtolower($name.' '.$number);
        if (str_contains($combined, 'undang-undang') || str_starts_with($combined, 'uu ')) {
            return 'Undang-Undang';
        }
        if (str_contains($combined, 'peraturan pemerintah') || str_contains($combined, 'pp no')) {
            return 'Peraturan Pemerintah';
        }
        if (str_contains($combined, 'peraturan presiden') || str_contains($combined, 'perpres')) {
            return 'Peraturan Presiden';
        }
        if (str_contains($combined, 'peraturan daerah') || str_contains($combined, 'perda')) {
            return 'Peraturan Daerah';
        }
        if (str_contains($combined, 'peraturan bupati') || str_contains($combined, 'perbup')) {
            return 'Peraturan Bupati';
        }
        if (str_contains($combined, 'peraturan menteri') || str_contains($combined, 'permen')) {
            return 'Peraturan Menteri';
        }

        return 'Peraturan Perundang-undangan';
    }
}

