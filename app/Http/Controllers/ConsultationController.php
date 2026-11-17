<?php

namespace App\Http\Controllers;

use App\Services\Analysis\DeepSeekProvider;
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
}

