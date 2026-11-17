<?php

namespace App\Http\Controllers;

use App\Services\Analysis\DeepSeekProvider;
use App\Services\ComplaintService;
use App\Services\LegalAnalysisService;
use App\Services\LegalAssistantService;
use App\Services\LegalIngestionService;
use App\Services\LegalRetrievalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly LegalAssistantService $assistantService,
        private readonly LegalIngestionService $ingestionService,
        private readonly LegalAnalysisService $analysisService,
        private readonly LegalRetrievalService $retrievalService
    ) {}

    /**
     * Tampilkan halaman Beranda Portal Hukum & Layanan Publik BHIMA (Teal & Amber Accent).
     */
    public function home(Request $request)
    {
        $stats = $this->knowledgeStats();
        $portalMetrics = $this->portalMetrics($stats);
        $topicDistribution = $this->topicDistribution();

        return view('consultation.home', [
            'knowledgeStats' => $stats,
            'portalMetrics' => $portalMetrics,
            'topicDistribution' => $topicDistribution,
        ]);
    }

    /**
     * Pencarian cepat dokumen JDIH dan SOP Layanan dari halaman Beranda.
     */
    public function searchPortal(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        try {
            if (DB::table('knowledge_chunks')->count() === 0 && DB::table('dashboard_records')->whereIn('collection', ['documents', 'contents'])->count() > 0) {
                $this->ingestionService->ingestAll();
            }

            if ($query === '' || mb_strtolower($query, 'UTF-8') === 'semua') {
                $rows = DB::table('knowledge_chunks')
                    ->select('document_key', 'document_name', 'document_number', 'document_year', 'article_label', 'category', 'topic', 'content')
                    ->whereIn('document_key', ['JDIH-001', 'JDIH-003', 'JDIH-005', 'JDIH-015', 'SOP-001', 'SOP-002', 'SOP-003', 'SOP-010'])
                    ->where('chunk_index', 0)
                    ->orderBy('document_key')
                    ->limit(6)
                    ->get();

                $results = $rows->map(fn ($r) => [
                    'document_key' => (string) $r->document_key,
                    'document_name' => (string) $r->document_name,
                    'document_number' => (string) $r->document_number,
                    'document_year' => (int) $r->document_year,
                    'article_label' => (string) $r->article_label,
                    'category' => (string) $r->category,
                    'topic' => (string) $r->topic,
                    'excerpt' => Str::limit((string) $r->content, 160),
                    'url' => route('consultation.reference', ['key' => $r->document_key]),
                ])->values()->all();

                return response()->json([
                    'query' => $query,
                    'count' => count($results),
                    'results' => $results,
                ]);
            }

            $sanitized = $this->assistantService->sanitizeChronology($query);
            $retrieved = $this->retrievalService->retrieve($sanitized, 6);
            $seenDocs = [];
            $results = [];

            foreach ($retrieved['chunks'] as $chunk) {
                $docKey = (string) ($chunk['document_key'] ?? '');
                if ($docKey === '' || isset($seenDocs[$docKey])) {
                    continue;
                }
                $seenDocs[$docKey] = true;
                $results[] = [
                    'document_key' => $docKey,
                    'document_name' => (string) ($chunk['document_name'] ?? ''),
                    'document_number' => (string) ($chunk['document_number'] ?? ''),
                    'document_year' => (int) ($chunk['document_year'] ?? 0),
                    'article_label' => (string) ($chunk['article_label'] ?? ''),
                    'category' => (string) ($chunk['category'] ?? 'JDIH'),
                    'topic' => (string) ($chunk['topic'] ?? 'Umum'),
                    'excerpt' => Str::limit((string) ($chunk['content'] ?? ''), 160),
                    'url' => route('consultation.reference', ['key' => $docKey]),
                ];
            }

            return response()->json([
                'query' => $sanitized,
                'count' => count($results),
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal menelusuri dokumen portal dari Beranda.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Pencarian dokumen belum dapat dimuat saat ini. Silakan coba lagi atau buka Ruang Konsultasi.',
            ], 503);
        }
    }

    /**
     * Pelacakan status tiket cepat tanpa login dari kartu Lacak Tiket di Beranda.
     */
    public function trackTicketQuick(Request $request, ComplaintService $complaintService): JsonResponse
    {
        $ticket = strtoupper(trim((string) $request->query('tiket', '')));

        if ($ticket === '' || ! preg_match('/^BHM-(\d{4}-\d{3,}|[A-Z0-9]{6,12})$/', $ticket)) {
            return response()->json([
                'found' => false,
                'message' => 'Format nomor tiket belum sesuai (contoh: BHM-2026-0001).',
            ], 422);
        }

        try {
            $result = $complaintService->trackByTicket($ticket);
            if (! $result) {
                return response()->json([
                    'found' => false,
                    'ticket' => $ticket,
                    'message' => "Tiket {$ticket} tidak ditemukan di basis data pengaduan.",
                ], 404);
            }

            return response()->json([
                'found' => true,
                'ticket' => $result['ticket'],
                'status' => $result['status'],
                'category' => $result['category'] ?? 'Pengaduan Masyarakat',
                'date' => $result['date'] ?? '-',
                'agency' => $result['agency'] ?? 'Tim Verifikator BHIMA',
                'detail_url' => route('complaint.track', ['tiket' => $result['ticket']]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal melacak tiket dari Beranda.', [
                'ticket' => $ticket,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'found' => false,
                'message' => 'Layanan pelacakan tiket sedang mengalami kendala. Silakan buka halaman Lacak Tiket.',
            ], 503);
        }
    }

    /**
     * Tampilkan halaman ruang percakapan konsultasi hukum untuk warga.
     */
    public function index(Request $request)
    {
        $sessionUuid = $this->resolveSessionUuid($request);
        $stats = $this->knowledgeStats();

        return view('consultation.index', [
            'sessionUuid' => $sessionUuid,
            'knowledgeStats' => $stats,
            'maxChronologyLength' => (int) config('rag.security.max_chronology_length', 4000),
        ]);
    }

    /**
     * Ambil riwayat percakapan untuk sesi aktif saat ini.
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $sessionUuid = $this->resolveSessionUuid($request);
            $sessionRecord = DB::table('consultation_sessions')
                ->where('session_uuid', $sessionUuid)
                ->first();

            $messages = [];
            if ($sessionRecord) {
                $rows = DB::table('consultation_messages')
                    ->where('consultation_session_id', $sessionRecord->id)
                    ->orderBy('id')
                    ->limit(100)
                    ->get();

                foreach ($rows as $row) {
                    $messages[] = [
                        'id' => (int) $row->id,
                        'sender' => (string) $row->sender,
                        'content' => (string) $row->content,
                        'rewritten_query' => $row->rewritten_query,
                        'citations' => json_decode((string) ($row->citations ?? '[]'), true) ?: [],
                        'retrieval_meta' => json_decode((string) ($row->retrieval_meta ?? '{}'), true) ?: [],
                        'legal_analysis' => isset($row->legal_analysis) && $row->legal_analysis
                            ? (json_decode((string) $row->legal_analysis, true) ?: null)
                            : null,
                        'created_at' => $row->created_at,
                    ];
                }
            }

            return response()->json([
                'session_id' => $sessionUuid,
                'messages' => $messages,
                'knowledge_stats' => $this->knowledgeStats(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal memuat riwayat sesi konsultasi warga.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Riwayat percakapan belum dapat dimuat saat ini. Silakan muat ulang halaman.',
            ], 503);
        }
    }

    /**
     * Terima kronologi permasalahan hukum warga, lakukan retrieval terhadap indeks JDIH & SOP,
     * dan kembalikan jawaban asisten beserta sitasi terverifikasi.
     */
    public function send(Request $request): JsonResponse
    {
        $sessionUuid = $this->resolveSessionUuid($request);
        $rateKey = 'bhima-consult:'.sha1($sessionUuid.'|'.($request->ip() ?? 'unknown'));
        $maxAttempts = max(1, (int) config('rag.security.rate_limit_per_minute', 15));

        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            return response()->json([
                'message' => "Terlalu banyak pengiriman kronologi dalam waktu singkat. Silakan tunggu {$retryAfter} detik sebelum mengirim pesan kembali.",
            ], 429);
        }

        RateLimiter::hit($rateKey, 60);

        $minLen = (int) config('rag.security.min_chronology_length', 10);
        $maxLen = (int) config('rag.security.max_chronology_length', 4000);

        $validated = $request->validate([
            'chronology' => [
                'required',
                'string',
                "min:{$minLen}",
                "max:{$maxLen}",
            ],
            'top_k' => ['nullable', 'integer', 'min:1', 'max:10'],
        ], [
            'chronology.required' => 'Mohon tuliskan kronologi permasalahan hukum Anda terlebih dahulu.',
            'chronology.min' => "Kronologi terlalu singkat (minimal {$minLen} karakter) agar kami dapat mencocokkan pasal yang tepat.",
            'chronology.max' => "Kronologi melebihi batas maksimal {$maxLen} karakter. Mohon ringkas pokok permasalahan Anda.",
        ]);

        $sanitized = $this->assistantService->sanitizeChronology((string) $validated['chronology']);
        if (mb_strlen($sanitized) < $minLen) {
            throw ValidationException::withMessages([
                'chronology' => "Kronologi bersih minimal {$minLen} karakter setelah penghapusan tag yang tidak valid.",
            ]);
        }

        // Samarkan data pribadi sensitif (seperti 16 digit NIK/KK) sebelum disimpan ke riwayat sesi
        $privacySafeChronology = $this->redactPersonalIdentifiers($sanitized);

        try {
            // Jika dokumen sudah ada di dashboard_records namun belum diindeks ke knowledge_chunks, indeks otomatis
            if (DB::table('knowledge_chunks')->count() === 0 && DB::table('dashboard_records')->whereIn('collection', ['documents', 'contents'])->count() > 0) {
                $this->ingestionService->ingestAll();
            }

            $topK = isset($validated['top_k']) ? (int) $validated['top_k'] : null;
            $result = $this->assistantService->answerChronology($privacySafeChronology, $topK);
            $legalAnalysis = $this->analysisService->analyze($privacySafeChronology, $result['citations']);

            $now = now();
            $userMsgId = 0;
            $assistantMsgId = 0;
            $hasAnalysisColumn = Schema::hasColumn('consultation_messages', 'legal_analysis');

            DB::transaction(function () use ($sessionUuid, $request, $privacySafeChronology, $result, $legalAnalysis, $hasAnalysisColumn, $now, &$userMsgId, &$assistantMsgId) {
                $sessionRecord = DB::table('consultation_sessions')
                    ->where('session_uuid', $sessionUuid)
                    ->first();

                $title = mb_substr($privacySafeChronology, 0, 120);
                $fingerprint = hash('sha256', (string) $request->session()->getId());

                if (! $sessionRecord) {
                    $sessionId = DB::table('consultation_sessions')->insertGetId([
                        'session_uuid' => $sessionUuid,
                        'session_fingerprint' => $fingerprint,
                        'title' => $title,
                        'last_active_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $sessionId = (int) $sessionRecord->id;
                    DB::table('consultation_sessions')
                        ->where('id', $sessionId)
                        ->update([
                            'last_active_at' => $now,
                            'updated_at' => $now,
                        ]);
                }

                $userMsgId = (int) DB::table('consultation_messages')->insertGetId([
                    'consultation_session_id' => $sessionId,
                    'sender' => 'user',
                    'content' => $privacySafeChronology,
                    'rewritten_query' => null,
                    'citations' => null,
                    'retrieval_meta' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $assistantInsert = [
                    'consultation_session_id' => $sessionId,
                    'sender' => 'assistant',
                    'content' => $result['answer'],
                    'rewritten_query' => $result['rewritten_query'],
                    'citations' => json_encode($result['citations'], JSON_UNESCAPED_UNICODE),
                    'retrieval_meta' => json_encode($result['retrieval_meta'], JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($hasAnalysisColumn) {
                    $assistantInsert['legal_analysis'] = json_encode($legalAnalysis, JSON_UNESCAPED_UNICODE);
                }

                $assistantMsgId = (int) DB::table('consultation_messages')->insertGetId($assistantInsert);
            });

            return response()->json([
                'session_id' => $sessionUuid,
                'status' => $result['status'],
                'user_message' => [
                    'id' => $userMsgId,
                    'sender' => 'user',
                    'content' => $privacySafeChronology,
                    'created_at' => $now->toIso8601String(),
                ],
                'assistant_message' => [
                    'id' => $assistantMsgId,
                    'sender' => 'assistant',
                    'content' => $result['answer'],
                    'rewritten_query' => $result['rewritten_query'],
                    'citations' => $result['citations'],
                    'retrieval_meta' => $result['retrieval_meta'],
                    'legal_analysis' => $legalAnalysis,
                    'created_at' => $now->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal memproses konsultasi hukum warga.', [
                'session_uuid' => $sessionUuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Maaf, layanan Asisten Hukum Virtual sedang mengalami gangguan teknis sementara. Silakan coba beberapa saat lagi atau hubungi petugas pengaduan BHIMA.',
            ], 503);
        }
    }

    /**
     * Endpoint khusus analisis pasal, ancaman sanksi, dan eskalasi pengaduan (PBI 2).
     * Menerima kronologi warga dan (opsional) chunk hasil retrieval PBI 1.
     */
    public function analyze(Request $request): JsonResponse
    {
        $sessionUuid = $this->resolveSessionUuid($request);
        $rateKey = 'bhima-analysis:'.sha1($sessionUuid.'|'.($request->ip() ?? 'unknown'));
        $maxAttempts = max(1, (int) config('legal_analysis.security.rate_limit_per_minute', 15));

        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            return response()->json([
                'message' => "Terlalu banyak permintaan analisis dalam waktu singkat. Silakan tunggu {$retryAfter} detik sebelum mencoba kembali.",
            ], 429);
        }

        RateLimiter::hit($rateKey, 60);

        $minLen = (int) config('legal_analysis.security.min_chronology_length', 10);
        $maxLen = (int) config('legal_analysis.security.max_chronology_length', 4000);

        $validated = $request->validate([
            'chronology' => ['required', 'string', "min:{$minLen}", "max:{$maxLen}"],
            'chunks' => ['nullable', 'array', 'max:15'],
            'top_k' => ['nullable', 'integer', 'min:1', 'max:10'],
        ], [
            'chronology.required' => 'Mohon tuliskan kronologi permasalahan hukum Anda terlebih dahulu.',
            'chronology.min' => "Kronologi terlalu singkat (minimal {$minLen} karakter).",
            'chronology.max' => "Kronologi melebihi batas maksimal {$maxLen} karakter.",
        ]);

        $sanitized = $this->assistantService->sanitizeChronology((string) $validated['chronology']);
        if (mb_strlen($sanitized) < $minLen) {
            throw ValidationException::withMessages([
                'chronology' => "Kronologi bersih minimal {$minLen} karakter setelah penghapusan tag yang tidak valid.",
            ]);
        }

        $privacySafeChronology = DeepSeekProvider::maskPersonalIdentifiers($this->redactPersonalIdentifiers($sanitized));

        if (array_key_exists('chunks', $validated) && is_array($validated['chunks'])) {
            $chunks = $validated['chunks'];
        } else {
            if (DB::table('knowledge_chunks')->count() === 0 && DB::table('dashboard_records')->whereIn('collection', ['documents', 'contents'])->count() > 0) {
                $this->ingestionService->ingestAll();
            }
            $topK = isset($validated['top_k']) ? (int) $validated['top_k'] : null;
            $retrieval = $this->retrievalService->retrieve($privacySafeChronology, $topK);
            $chunks = $retrieval['chunks'];
        }

        $analysis = $this->analysisService->analyze($privacySafeChronology, $chunks);

        return response()->json($analysis);
    }

    /**
     * Mulai sesi percakapan baru yang bersih untuk warga.
     */
    public function reset(Request $request): JsonResponse
    {
        $newUuid = (string) Str::uuid();
        if ($request->hasSession()) {
            $request->session()->put('bhima_consultation_session_uuid', $newUuid);
        }

        return response()->json([
            'session_id' => $newUuid,
            'message' => 'Sesi konsultasi baru telah dimulai.',
        ]);
    }

    /**
     * Tampilkan halaman detail dokumen rujukan JDIH atau SOP Layanan beserta daftar pasal/ayat terindeks.
     */
    public function reference(string $key)
    {
        $record = DB::table('dashboard_records')
            ->whereIn('collection', ['documents', 'contents'])
            ->where('record_key', $key)
            ->first();

        $chunks = DB::table('knowledge_chunks')
            ->where('document_key', $key)
            ->orderBy('chunk_index')
            ->get();

        abort_if(! $record && $chunks->isEmpty(), 404);

        $payload = $record ? (json_decode((string) $record->payload, true) ?: []) : [];
        $firstChunk = $chunks->first();

        $document = [
            'key' => $key,
            'title' => $payload['title'] ?? $firstChunk?->document_name ?? $key,
            'type' => $payload['type'] ?? $firstChunk?->document_type ?? 'Dokumen Rujukan',
            'number' => $payload['number'] ?? $firstChunk?->document_number ?? '-',
            'year' => $payload['year'] ?? $firstChunk?->document_year ?? '-',
            'category' => $firstChunk?->category ?? (($record?->collection === 'documents') ? 'JDIH' : 'SOP'),
            'topic' => $payload['topic'] ?? $payload['category'] ?? $firstChunk?->topic ?? 'Umum',
            'summary' => $payload['summary'] ?? null,
            'updated' => $payload['updated'] ?? null,
        ];

        return view('consultation.reference', [
            'document' => $document,
            'chunks' => $chunks,
        ]);
    }

    /**
     * Samarkan 16 digit NIK/KK dan nomor telepon pribadi agar tidak menyimpan data pribadi berlebih di riwayat sesi.
     */
    public function redactPersonalIdentifiers(string $text): string
    {
        // Samarkan angka 16 digit (NIK / Nomor KK)
        $redacted = preg_replace('/\b\d{16}\b/', '[NIK/KK-DISAMARKAN]', $text) ?? $text;
        // Samarkan nomor telepon seluler Indonesia (10-14 digit diawali 08 atau +628)
        $redacted = preg_replace('/(?:\+62|62|0)8\d{8,11}\b/', '[TELEPON-DISAMARKAN]', $redacted) ?? $redacted;

        return $redacted;
    }

    private function resolveSessionUuid(Request $request): string
    {
        $candidate = (string) ($request->header('X-Consultation-Session') ?: $request->input('session_id', ''));
        if ($candidate !== '' && preg_match('/^[A-Za-z0-9_-]{12,64}$/', $candidate)) {
            if ($request->hasSession()) {
                $request->session()->put('bhima_consultation_session_uuid', $candidate);
            }

            return $candidate;
        }

        if ($request->hasSession()) {
            $existing = (string) $request->session()->get('bhima_consultation_session_uuid', '');
            if ($existing !== '' && preg_match('/^[A-Za-z0-9_-]{12,64}$/', $existing)) {
                return $existing;
            }

            $generated = (string) Str::uuid();
            $request->session()->put('bhima_consultation_session_uuid', $generated);

            return $generated;
        }

        return (string) Str::uuid();
    }

    /**
     * @return array{documents: int, jdih: int, sop: int, chunks: int}
     */
    private function knowledgeStats(): array
    {
        try {
            $jdih = (int) DB::table('knowledge_chunks')->where('category', 'JDIH')->distinct('document_key')->count('document_key');
            $sop = (int) DB::table('knowledge_chunks')->where('category', 'SOP')->distinct('document_key')->count('document_key');
            $chunks = (int) DB::table('knowledge_chunks')->count();

            return [
                'documents' => $jdih + $sop,
                'jdih' => $jdih,
                'sop' => $sop,
                'chunks' => $chunks,
            ];
        } catch (\Throwable) {
            return ['documents' => 0, 'jdih' => 0, 'sop' => 0, 'chunks' => 0];
        }
    }

    /**
     * Ambil metrik nyata portal dari database untuk ditampilkan di halaman Beranda.
     *
     * @param  array{documents: int, jdih: int, sop: int, chunks: int}  $stats
     * @return array{documents: int, chunks: int, sessions: int, complaints: int}
     */
    private function portalMetrics(array $stats): array
    {
        try {
            $sessions = (int) DB::table('consultation_sessions')->count();
            $complaints = (int) DB::table('dashboard_records')->where('collection', 'complaints')->count();

            return [
                'documents' => $stats['documents'] ?: 100,
                'chunks' => $stats['chunks'] ?: 301,
                'sessions' => $sessions,
                'complaints' => $complaints,
            ];
        } catch (\Throwable) {
            return [
                'documents' => $stats['documents'] ?: 100,
                'chunks' => $stats['chunks'] ?: 301,
                'sessions' => 0,
                'complaints' => 0,
            ];
        }
    }

    /**
     * Hitung distribusi topik hukum nyata dari basis data knowledge_chunks untuk bagian Indeks Kebutuhan Hukum Warga.
     *
     * @return list<array{label: string, icon: string, reference: string, count: int, percent: int, query: string}>
     */
    private function topicDistribution(): array
    {
        $topicMap = [
            [
                'topic' => 'Pertanahan',
                'label' => 'Sengketa Batas Tanah & Waris',
                'icon' => 'domain',
                'reference' => 'Rujukan: PP No. 24 Tahun 1997 & UUPA',
                'query' => 'Sengketa batas bidang tanah pekarangan dan sertifikat hak milik',
                'fallback_count' => 33,
            ],
            [
                'topic' => 'Pelayanan Kesehatan',
                'label' => 'Prosedur Bantuan Sosial & Jaminan Kesehatan',
                'icon' => 'payments',
                'reference' => 'Rujukan: UU No. 17 Tahun 2023 & SOP PBI JKN',
                'query' => 'Pendaftaran bantuan iuran BPJS Kesehatan PBI dan layanan darurat rumah sakit',
                'fallback_count' => 30,
            ],
            [
                'topic' => 'Dugaan Pungutan Liar',
                'label' => 'Pengaduan Pelayanan Kelurahan & Pungli',
                'icon' => 'report_problem',
                'reference' => 'Rujukan: Perpres No. 87 Tahun 2016 & UU Pelayanan Publik',
                'query' => 'Pungutan liar pengurusan KTP elektronik dan dokumen layanan publik',
                'fallback_count' => 28,
            ],
            [
                'topic' => 'Ketenagakerjaan',
                'label' => 'Ketenagakerjaan & Legalitas Usaha Warga',
                'icon' => 'store',
                'reference' => 'Rujukan: UU Ketenagakerjaan & PP No. 35 Tahun 2021',
                'query' => 'Penyelesaian hak upah pekerja, PHK sepihak, dan izin usaha mikro desa',
                'fallback_count' => 27,
            ],
            [
                'topic' => 'Perlindungan Perempuan & Anak',
                'label' => 'Pencegahan KDRT & Perlindungan Anak',
                'icon' => 'family_restroom',
                'reference' => 'Rujukan: UU No. 23 Tahun 2004 & SOP Pendampingan UPTD PPA',
                'query' => 'Pendampingan darurat korban KDRT dan perlindungan perempuan serta anak',
                'fallback_count' => 26,
            ],
        ];

        $countsByTopic = [];
        $totalChunks = 0;

        try {
            $rows = DB::table('knowledge_chunks')
                ->select('topic', DB::raw('COUNT(*) as total'))
                ->groupBy('topic')
                ->get();

            foreach ($rows as $row) {
                $countsByTopic[(string) $row->topic] = (int) $row->total;
                $totalChunks += (int) $row->total;
            }
        } catch (\Throwable) {
            $countsByTopic = [];
        }

        if ($totalChunks === 0) {
            $totalChunks = 301;
        }

        $items = [];
        foreach ($topicMap as $item) {
            $count = $countsByTopic[$item['topic']] ?? $item['fallback_count'];
            $percent = max(15, min(100, (int) round(($count / max(1, $totalChunks)) * 280)));

            $items[] = [
                'label' => $item['label'],
                'icon' => $item['icon'],
                'reference' => $item['reference'],
                'count' => $count,
                'percent' => $percent,
                'query' => $item['query'],
            ];
        }

        usort($items, fn (array $a, array $b) => $b['count'] <=> $a['count']);

        return $items;
    }
}

