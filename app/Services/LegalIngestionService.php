<?php

namespace App\Services;

use App\Services\Analysis\MockProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LegalIngestionService
{
    private readonly JdihManifestService $manifestService;

    public function __construct(
        private readonly EmbeddingService $embeddingService,
        ?JdihManifestService $manifestService = null
    ) {
        $this->manifestService = $manifestService ?? app(JdihManifestService::class);
    }

    /**
     * Proses seluruh dokumen JDIH dan SOP Layanan yang ada di dashboard_records serta berkas PDF resmi
     * yang terdaftar pada jdih_manifest.csv. Bersifat idempotent.
     *
     * @return array{documents_processed: int, jdih_count: int, sop_count: int, chunks_indexed: int}
     */
    public function ingestAll(?string $manifestPath = null, ?string $pdfDir = null): array
    {
        $manifestEntries = $this->manifestService->loadAll($manifestPath);

        // Sinkronkan dokumen dari manifest yang berkas PDF resminya sudah tersedia di storage/app/private/jdih/
        $this->syncManifestDocumentsWithLocalPdfs($manifestEntries, $pdfDir);

        $records = DB::table('dashboard_records')
            ->whereIn('collection', ['documents', 'contents'])
            ->orderBy('id')
            ->get();

        $documentsProcessed = 0;
        $jdihCount = 0;
        $sopCount = 0;
        $chunksIndexed = 0;
        $activeDocumentKeys = [];

        DB::transaction(function () use ($records, $manifestEntries, $pdfDir, &$documentsProcessed, &$jdihCount, &$sopCount, &$chunksIndexed, &$activeDocumentKeys) {
            foreach ($records as $record) {
                $payload = json_decode((string) $record->payload, true) ?: [];

                if ($record->collection === 'contents' && ($payload['category'] ?? '') !== 'SOP') {
                    continue;
                }

                // Tolak dokumen templat karangan lama (JDIH-026..080 "Seri N") jika tidak memiliki berkas PDF resmi
                if ($this->isLegacySyntheticTemplate((string) $record->record_key, $payload, $pdfDir)) {
                    DB::table('knowledge_chunks')->where('document_key', $record->record_key)->delete();
                    DB::table('dashboard_records')->where('id', $record->id)->delete();

                    continue;
                }

                $manifest = $manifestEntries[strtoupper((string) $record->record_key)] ?? null;
                $count = $this->ingestRecord($record, $payload, $manifest, $pdfDir);
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
     * Proses ulang satu dokumen JDIH yang dikelola dashboard. Dipakai oleh
     * perintah admin dan endpoint internal agar unggahan baru tidak perlu
     * menunggu re-ingestion seluruh korpus.
     *
     * @return array{status: 'indexed'|'failed'|'not_found', document_key: string, chunks_indexed: int, message: string}
     */
    public function processDocument(string $documentKey, ?string $manifestPath = null, ?string $pdfDir = null): array
    {
        $documentKey = trim($documentKey);
        if ($documentKey === '' || ! preg_match('/^[A-Za-z0-9_-]{1,80}$/', $documentKey)) {
            throw new \InvalidArgumentException('Kunci dokumen tidak valid.');
        }

        $manifestKey = strtoupper($documentKey);
        $manifestEntries = $this->manifestService->loadAll($manifestPath);
        $this->syncManifestDocumentsWithLocalPdfs($manifestEntries, $pdfDir, $manifestKey);

        $record = DB::table('dashboard_records')
            ->where('collection', 'documents')
            ->where('record_key', $documentKey)
            ->first();

        if (! $record) {
            return [
                'status' => 'not_found',
                'document_key' => $documentKey,
                'chunks_indexed' => 0,
                'message' => 'Dokumen JDIH tidak ditemukan.',
            ];
        }

        try {
            $chunksIndexed = DB::transaction(function () use ($documentKey, $manifestKey, $manifestEntries, $pdfDir): int {
                $lockedRecord = DB::table('dashboard_records')
                    ->where('collection', 'documents')
                    ->where('record_key', $documentKey)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedRecord) {
                    throw new \RuntimeException('Dokumen tidak tersedia untuk diproses.');
                }

                $payload = json_decode((string) $lockedRecord->payload, true) ?: [];
                $payload['status'] = 'Memproses';
                $payload['extraction'] = 'Memproses';
                unset($payload['processing_error']);

                DB::table('dashboard_records')
                    ->where('id', $lockedRecord->id)
                    ->update([
                        'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);

                $manifest = $manifestEntries[$manifestKey] ?? null;
                $count = $this->ingestRecord($lockedRecord, $payload, $manifest, $pdfDir);
                if ($count === 0) {
                    throw new \RuntimeException('Tidak ada teks yang dapat diekstrak dari dokumen.');
                }

                return $count;
            }, 3);

            return [
                'status' => 'indexed',
                'document_key' => $documentKey,
                'chunks_indexed' => $chunksIndexed,
                'message' => "Dokumen berhasil diekstrak dan {$chunksIndexed} potongan terindeks.",
            ];
        } catch (\Throwable $e) {
            Log::warning('Pemrosesan dokumen JDIH gagal.', [
                'document_key' => $documentKey,
                'error' => $e->getMessage(),
            ]);

            $this->markProcessingFailed($documentKey);

            return [
                'status' => 'failed',
                'document_key' => $documentKey,
                'chunks_indexed' => 0,
                'message' => 'Dokumen belum dapat diproses. Periksa PDF, metadata, dan coba kembali.',
            ];
        }
    }

    /**
     * Proses satu record dokumen JDIH atau SOP menjadi potongan chunk terstruktur beserta metadata sanksi dan status.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>|null  $manifest
     */
    public function ingestRecord(object $record, array $payload, ?array $manifest = null, ?string $pdfDir = null): int
    {
        $isJdih = $record->collection === 'documents';
        $category = $isJdih ? 'JDIH' : 'SOP';
        $docKey = (string) ($record->record_key ?: ($payload['id'] ?? 'DOC-'.$record->id));
        $manifest ??= $this->manifestService->find($docKey);

        $docName = trim((string) ($payload['title'] ?? ($manifest['judul'] ?? 'Dokumen '.$docKey)));
        $docNumber = trim((string) ($payload['number'] ?? ($manifest ? $this->manifestService->formatDocumentNumber($manifest) : ($isJdih ? $docKey : 'SOP/'.$docKey))));
        $docYear = (int) ($payload['year'] ?? ($manifest['tahun'] ?? (isset($payload['updated']) ? substr((string) $payload['updated'], 0, 4) : date('Y'))));
        if ($docYear < 1900 || $docYear > 2100) {
            $docYear = (int) date('Y');
        }
        $rawType = trim((string) ($payload['type'] ?? ''));
        $docType = ($rawType !== '' && ! in_array(strtolower($rawType), ['daerah', 'umum'], true))
            ? $rawType
            : $this->inferDocumentType($docName, $docNumber, $category);
        $topic = trim((string) ($payload['topic'] ?? $payload['category'] ?? ($manifest['kategori'] ?? 'Umum')));
        $baseUrl = trim((string) ($payload['source_url'] ?? '/rujukan/'.rawurlencode($docKey)));

        $statusBerlaku = strtolower(trim((string) ($payload['status_berlaku'] ?? ($manifest['status'] ?? 'berlaku'))));
        if (! in_array($statusBerlaku, ['berlaku', 'diubah', 'dicabut'], true)) {
            $statusBerlaku = 'berlaku';
        }
        $diubahOleh = trim((string) ($payload['diubah_oleh'] ?? ($manifest['diubah_oleh'] ?? '')));
        $dicabutOleh = trim((string) ($payload['dicabut_oleh'] ?? ($manifest['dicabut_oleh'] ?? '')));
        $sourcePageUrl = trim((string) ($payload['source_page_url'] ?? ($manifest['url_halaman'] ?? '')));

        $rawText = $this->extractText($record, $payload, $docKey, $pdfDir);
        if (trim($rawText) === '') {
            return 0;
        }

        $chunks = $this->chunkDocumentText($rawText, $docName, $docNumber, (string) ($payload['summary'] ?? ''));
        if (empty($chunks)) {
            return 0;
        }

        $now = now();
        $embeddingModel = (string) config('rag.embedding.model', 'bhima-legal-embed-v1');
        $hasVerificationColumns = Schema::hasColumn('knowledge_chunks', 'pasal_nomor');

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

            $row = [
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

            if ($hasVerificationColumns) {
                $legalMeta = $this->extractChunkLegalMetadata($articleLabel, $chunk['content']);
                $row['pasal_nomor'] = $legalMeta['pasal_nomor'];
                $row['ayat'] = $legalMeta['ayat'];
                $row['is_ketentuan_pidana'] = $legalMeta['is_ketentuan_pidana'];
                $row['sanksi_jenis'] = $legalMeta['sanksi_jenis'];
                $row['sanksi_teks'] = $legalMeta['sanksi_teks'];
                $row['rujuk_pasal'] = $legalMeta['rujuk_pasal'];
                $row['status_berlaku'] = $statusBerlaku;
                $row['diubah_oleh'] = $diubahOleh !== '' ? $diubahOleh : null;
                $row['dicabut_oleh'] = $dicabutOleh !== '' ? $dicabutOleh : null;
                $row['source_page_url'] = $sourcePageUrl !== '' ? $sourcePageUrl : null;
                $row['pdf_page'] = $chunk['pdf_page'] ?? null;
            }

            $chunkRows[] = $row;
        }

        $updateColumns = [
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
        ];

        if ($hasVerificationColumns) {
            $updateColumns = array_merge($updateColumns, [
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
            ]);
        }

        DB::table('knowledge_chunks')->upsert(
            $chunkRows,
            ['document_key', 'chunk_index'],
            $updateColumns
        );

        // Hapus sisa chunk lama apabila jumlah chunk berkurang saat re-ingestion
        DB::table('knowledge_chunks')
            ->where('document_key', $docKey)
            ->where('chunk_index', '>=', count($chunks))
            ->delete();

        // Perbarui status ekstraksi dan indeksasi pada dokumen JDIH
        $targetStatus = $statusBerlaku === 'dicabut' ? 'Dicabut (Arsip)' : 'Terindeks';
        if ($isJdih && (($payload['status'] ?? '') !== $targetStatus || ($payload['extraction'] ?? '') !== 'Berhasil' || isset($payload['processing_error']))) {
            $payload['extraction'] = 'Berhasil';
            $payload['status'] = $targetStatus;
            $payload['indexed_at'] = $now->toIso8601String();
            unset($payload['processing_error']);
            $payload['status_berlaku'] = $statusBerlaku;
            if ($sourcePageUrl !== '') {
                $payload['source_page_url'] = $sourcePageUrl;
            }
            if ($diubahOleh !== '') {
                $payload['diubah_oleh'] = $diubahOleh;
            }
            if ($dicabutOleh !== '') {
                $payload['dicabut_oleh'] = $dicabutOleh;
            }
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
     * Ekstrak teks dari berkas PDF resmi (prioritas: storage/app/private/jdih/{doc_key}.pdf,
     * lalu file_path upload admin, lalu field body/summary).
     *
     * @param  array<string, mixed>  $payload
     */
    public function extractText(object $record, array $payload, ?string $docKey = null, ?string $pdfDir = null): string
    {
        $parts = [];
        $resolvedKey = $docKey ?? (string) ($record->record_key ?? ($payload['id'] ?? ''));

        // 1. Prioritaskan berkas PDF resmi di storage/app/private/jdih/
        if ($resolvedKey !== '') {
            $officialPdfPath = $this->manifestService->findLocalPdfPath($resolvedKey, $pdfDir);
            if ($officialPdfPath !== null && is_readable($officialPdfPath)) {
                $pdfBytes = (string) file_get_contents($officialPdfPath);
                $extractedPdf = $this->extractPdfText($pdfBytes);
                if ($extractedPdf !== '') {
                    return $extractedPdf;
                }
            }
        }

        // 2. Berkas PDF yang diunggah lewat dashboard admin
        if (! empty($record->file_path) && Storage::disk('local')->exists($record->file_path)) {
            $pdfBytes = (string) Storage::disk('local')->get($record->file_path);
            $extractedPdf = $this->extractPdfText($pdfBytes);
            if ($extractedPdf !== '') {
                $parts[] = $extractedPdf;
            }
        }

        // 3. Teks terstruktur pada payload
        if (! empty($payload['body']) && is_string($payload['body'])) {
            $parts[] = trim($payload['body']);
        }

        if (empty($parts) && ! empty($payload['summary']) && is_string($payload['summary'])) {
            $parts[] = trim($payload['summary']);
        }

        return trim(implode("\n\n", $parts));
    }

    /**
     * Ekstrak metadata hukum terstruktur per potongan pasal (Bagian 6 panduan JDIH BHIMA):
     * nomor pasal, ayat, penanda ketentuan pidana/sanksi, bunyi sanksi persis, dan rujuk pasal norma.
     *
     * @return array{
     *     pasal_nomor: ?string,
     *     ayat: ?string,
     *     is_ketentuan_pidana: bool,
     *     sanksi_jenis: ?string,
     *     sanksi_teks: ?string,
     *     rujuk_pasal: ?string
     * }
     */
    public function extractChunkLegalMetadata(string $articleLabel, string $content): array
    {
        $pasalNomor = null;
        if (preg_match('/\bPasal\s+(\d+[A-Z]?)\b/iu', $articleLabel, $pm)) {
            $pasalNomor = strtoupper(trim($pm[1]));
        }

        $ayat = null;
        if (preg_match('/\bAyat\s*\((\d+)\)/iu', $articleLabel, $am)) {
            $ayat = trim($am[1]);
        } elseif (preg_match('/^\s*\((\d+)\)\s+/u', $content, $am)) {
            $ayat = trim($am[1]);
        }

        $sanction = MockProvider::extractSanctionFromChunkContent($content, '');
        $hasSanction = $sanction['bunyi_sanksi_ringkas'] !== '';

        // Tangkap pula pola denda eksplisit yang belum tercakup regex standar
        if (! $hasSanction && preg_match('/((?:pidana\s+kurungan|denda\s+paling\s+(?:banyak|sedikit))(?:[^.]|\.(?=\d))+)/iu', $content, $dm)) {
            $hasSanction = true;
            $sanction = [
                'jenis' => str_contains(mb_strtolower($dm[1], 'UTF-8'), 'kurungan') ? 'pidana' : 'denda',
                'bunyi_sanksi_ringkas' => trim($dm[1], ' .;,'),
            ];
        }

        $referenced = [];
        $refPatterns = [
            '/sebagaimana\s+dimaksud\s+(?:dalam|pada)\s+(Pasal\s+\d+[A-Z]?(?:\s+ayat\s*\(\d+\))?)/iu',
            '/(?:melanggar|ketentuan)\s+(Pasal\s+\d+[A-Z]?(?:\s+ayat\s*\(\d+\))?)/iu',
        ];
        foreach ($refPatterns as $pattern) {
            if (preg_match_all($pattern, $content, $rm)) {
                foreach ($rm[1] as $rawRef) {
                    $cleanRef = trim(preg_replace('/\s+/u', ' ', $rawRef) ?? $rawRef);
                    if (preg_match('/^Pasal\s+(\d+[A-Z]?)/iu', $cleanRef, $rn)) {
                        if ($pasalNomor !== null && strcasecmp($rn[1], $pasalNomor) === 0) {
                            continue;
                        }
                    }
                    $referenced[mb_strtolower($cleanRef, 'UTF-8')] = $cleanRef;
                }
            }
        }

        $rujukPasal = ! empty($referenced)
            ? mb_substr(implode('; ', array_values($referenced)), 0, 120)
            : null;

        return [
            'pasal_nomor' => $pasalNomor,
            'ayat' => $ayat,
            'is_ketentuan_pidana' => $hasSanction,
            'sanksi_jenis' => $hasSanction ? $sanction['jenis'] : null,
            'sanksi_teks' => $hasSanction ? $sanction['bunyi_sanksi_ringkas'] : null,
            'rujuk_pasal' => $rujukPasal,
        ];
    }

    /**
     * Pecah teks dokumen hukum per Pasal/Ayat sambil melacak nomor halaman PDF bila tersedia.
     * Fallback: pecah per paragraf dengan overlap bila struktur Pasal tidak ditemukan.
     *
     * @return list<array{article_label: string, content: string, pdf_page?: ?int}>
     */
    public function chunkDocumentText(string $text, string $docName = '', string $docNumber = '', string $summary = ''): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));
        if ($normalized === '') {
            return [];
        }

        $hasPageMarkers = str_contains($normalized, '[[PDF_PAGE:');
        $currentPage = $hasPageMarkers ? 1 : null;
        $rawLines = explode("\n", $normalized);

        $sections = [];
        $currentSection = null;

        foreach ($rawLines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\[\[PDF_PAGE:(\d+)\]\]$/', $line, $pm)) {
                $currentPage = (int) $pm[1];

                continue;
            }

            // Deteksi baris judul Pasal (mis. "Pasal 79A", "Pasal 24 Ayat (1)", "Pasal 1 Ayat (1) Persyaratan...")
            if (mb_strlen($line) <= 120 && preg_match('/^(Pasal\s+\d+[A-Z]?(?:\s+(?:Ayat\s*\(\d+\)|huruf\s+[a-z]))?)(?:\s+(.*))?$/iu', $line, $hm)) {
                $remainder = trim((string) ($hm[2] ?? ''));
                // Pastikan baris panjang yang berisi kalimat pasal tidak keliru dianggap judul
                $looksLikeHeader = $remainder === '' || (mb_strlen($remainder) <= 85 && ! str_ends_with($remainder, '.'));

                if ($looksLikeHeader) {
                    if ($currentSection !== null) {
                        $sections[] = $currentSection;
                    }
                    $currentSection = [
                        'article_label' => trim($hm[1]),
                        'remainder_header' => $remainder,
                        'body_lines' => [],
                        'pdf_page' => $currentPage,
                    ];

                    continue;
                }
            }

            if ($currentSection !== null) {
                $currentSection['body_lines'][] = $line;
            }
        }

        if ($currentSection !== null) {
            $sections[] = $currentSection;
        }

        if (! empty($sections)) {
            $chunks = [];
            foreach ($sections as $section) {
                $articleLabel = $section['article_label'];
                $bodyWithNewlines = trim(implode("\n", $section['body_lines']));
                $remainderHeader = $section['remainder_header'];

                if ($bodyWithNewlines === '') {
                    $bodyWithNewlines = $remainderHeader !== '' ? $remainderHeader : $articleLabel;
                } elseif ($remainderHeader !== '' && ! str_starts_with($bodyWithNewlines, $remainderHeader)) {
                    $bodyWithNewlines = $remainderHeader.': '.$bodyWithNewlines;
                }

                // Pecah per Ayat HANYA jika penanda "(1) " / "Ayat (1)" berada di awal baris (bukan di tengah kalimat rujukan)
                $subAyat = preg_split('/\n(?=(?:Ayat\s*)?\(\d+\)\s+)/iu', $bodyWithNewlines, -1, PREG_SPLIT_NO_EMPTY);
                if (is_array($subAyat) && count($subAyat) > 1) {
                    foreach ($subAyat as $idx => $ayatText) {
                        $ayatClean = trim(preg_replace('/\s+/u', ' ', $ayatText) ?? $ayatText);
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
                            'pdf_page' => $section['pdf_page'],
                        ];
                    }
                } else {
                    $bodyFlat = trim(preg_replace('/\s+/u', ' ', $bodyWithNewlines) ?? $bodyWithNewlines);
                    $chunks[] = [
                        'article_label' => $articleLabel,
                        'content' => $bodyFlat,
                        'pdf_page' => $section['pdf_page'],
                    ];
                }
            }

            if (! empty($chunks)) {
                return $chunks;
            }
        }

        // Fallback: pecah per paragraf dengan overlap
        $cleanNormalized = trim((string) preg_replace('/\[\[PDF_PAGE:\d+\]\]\s*/u', '', $normalized));

        return $this->chunkByParagraphWithOverlap($cleanNormalized, $summary);
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
     * Sinkronkan dokumen pada jdih_manifest.csv yang berkas PDF resminya sudah tersedia di storage/app/private/jdih/
     * ke dashboard_records agar langsung terindeks secara otomatis.
     *
     * @param  array<string, array<string, mixed>>  $manifestEntries
     */
    private function syncManifestDocumentsWithLocalPdfs(array $manifestEntries, ?string $pdfDir = null, ?string $onlyDocumentKey = null): void
    {
        if (empty($manifestEntries)) {
            return;
        }

        $now = now();
        $today = $now->toDateString();

        foreach ($manifestEntries as $docKey => $entry) {
            if ($onlyDocumentKey !== null && strcasecmp($docKey, $onlyDocumentKey) !== 0) {
                continue;
            }

            $pdfPath = $this->manifestService->findLocalPdfPath($docKey, $pdfDir);
            if ($pdfPath === null) {
                continue;
            }

            $existing = DB::table('dashboard_records')
                ->where('collection', 'documents')
                ->where('record_key', $docKey)
                ->first();

            $existingPayload = $existing ? (json_decode((string) $existing->payload, true) ?: []) : [];
            $formattedNumber = $this->manifestService->formatDocumentNumber($entry);

            $payload = array_merge($existingPayload, [
                'id' => $docKey,
                'title' => (string) ($entry['judul'] ?? $docKey),
                'type' => $this->inferDocumentType((string) ($entry['judul'] ?? ''), $formattedNumber, 'JDIH'),
                'number' => $formattedNumber,
                'year' => (int) ($entry['tahun'] ?? (int) date('Y')),
                'category' => (string) ($entry['kategori'] ?? 'Umum'),
                'source_url' => '/rujukan/'.$docKey,
                'source_page_url' => (string) ($entry['url_halaman'] ?? ''),
                'status_berlaku' => (string) ($entry['status'] ?? 'berlaku'),
                'diubah_oleh' => ($entry['diubah_oleh'] ?? '') !== '' ? (string) $entry['diubah_oleh'] : null,
                'dicabut_oleh' => ($entry['dicabut_oleh'] ?? '') !== '' ? (string) $entry['dicabut_oleh'] : null,
                'filename' => basename($pdfPath),
                'extraction' => 'Menunggu',
                'status' => ($entry['status'] ?? 'berlaku') === 'dicabut' ? 'Dicabut (Arsip)' : 'Menunggu',
                'updated' => $today,
            ]);

            DB::table('dashboard_records')->upsert(
                [[
                    'collection' => 'documents',
                    'record_key' => $docKey,
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'file_path' => $existing?->file_path,
                    'created_at' => $existing?->created_at ?? $now,
                    'updated_at' => $now,
                ]],
                ['collection', 'record_key'],
                ['payload', 'updated_at']
            );
        }
    }

    private function markProcessingFailed(string $documentKey): void
    {
        $record = DB::table('dashboard_records')
            ->where('collection', 'documents')
            ->where('record_key', $documentKey)
            ->first();

        if (! $record) {
            return;
        }

        $payload = json_decode((string) $record->payload, true) ?: [];
        $payload['status'] = 'Gagal';
        $payload['extraction'] = 'Gagal';
        $payload['processing_error'] = 'Teks PDF tidak dapat diekstrak. Periksa berkas atau gunakan PDF berbasis teks.';

        DB::table('dashboard_records')
            ->where('id', $record->id)
            ->update([
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
    }

    /**
     * Deteksi dokumen templat karangan lama (JDIH-026..080 bertajuk "... Seri N") yang tidak memiliki PDF resmi.
     *
     * @param  array<string, mixed>  $payload
     */
    private function isLegacySyntheticTemplate(string $docKey, array $payload, ?string $pdfDir = null): bool
    {
        if (! preg_match('/^JDIH-(\d+)$/i', $docKey, $m)) {
            return false;
        }

        $num = (int) $m[1];
        if ($num < 26 || $num > 80) {
            return false;
        }

        $title = (string) ($payload['title'] ?? '');
        if (! preg_match('/\bSeri\s+\d+$/i', $title)) {
            return false;
        }

        return $this->manifestService->findLocalPdfPath($docKey, $pdfDir) === null;
    }

    /**
     * Ekstraksi teks ringan dari berkas PDF per halaman dengan penanda [[PDF_PAGE:N]]
     * tanpa dependensi eksternal berat (mendukung operator Tj dan TJ).
     */
    private function extractPdfText(string $pdfBytes): string
    {
        $pages = [];
        $pageNum = 0;

        // Dekompresi stream FlateDecode jika tersedia
        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $pdfBytes, $streamMatches)) {
            foreach ($streamMatches[1] as $rawStream) {
                $decoded = @gzuncompress($rawStream);
                if ($decoded === false) {
                    $decoded = @gzinflate($rawStream);
                }
                $target = is_string($decoded) ? $decoded : $rawStream;
                $lines = $this->extractPdfTextOperators($target);
                if (! empty($lines)) {
                    $pageNum++;
                    $pages[] = "[[PDF_PAGE:{$pageNum}]]\n".implode("\n", $lines);
                }
            }
        }

        if (empty($pages)) {
            $lines = $this->extractPdfTextOperators($pdfBytes);
            if (! empty($lines)) {
                $pages[] = "[[PDF_PAGE:1]]\n".implode("\n", $lines);
            }
        }

        $text = trim(implode("\n\n", $pages));
        $text = preg_replace('/[^\P{C}\n\t]+/u', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * @return list<string>
     */
    private function extractPdfTextOperators(string $stream): array
    {
        $lines = [];
        $balancedParen1 = '(?<p1>\((?:[^()\\\\]|\\\\.|(?&p1))*\))';
        $balancedParen2 = '(?<p2>\((?:[^()\\\\]|\\\\.|(?&p2))*\))';

        // Tangkap operator (text) Tj serta array [(text) -20 (text2)] TJ dengan dukungan tanda kurung bersarang seperti Ayat (1)
        if (preg_match_all('/'.$balancedParen1.'\s*Tj|\[((?:[^\[\]]|'.$balancedParen2.')*)\]\s*TJ/s', $stream, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (! empty($match['p1'])) {
                    $raw = substr($match['p1'], 1, -1);
                    $piece = trim(stripcslashes($raw));
                    if ($piece !== '') {
                        $lines[] = $piece;
                    }
                } elseif (! empty($match[2])) {
                    if (preg_match_all('/'.$balancedParen1.'/s', $match[2], $inner)) {
                        $parts = array_map(
                            fn (string $tok) => stripcslashes(substr($tok, 1, -1)),
                            $inner['p1']
                        );
                        $joined = trim(implode('', $parts));
                        if ($joined !== '') {
                            $lines[] = $joined;
                        }
                    }
                }
            }
        }

        return $lines;
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
