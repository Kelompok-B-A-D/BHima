<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Analysis\DeepSeekProvider;
use App\Services\LegalAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicComplaintController extends Controller
{
    private const CATEGORIES = [
        'Administrasi Kependudukan',
        'Pelayanan Kesehatan',
        'Infrastruktur',
        'Dugaan Pungutan Liar',
        'Ketenagakerjaan',
        'Pertanahan',
        'Perlindungan Konsumen & Digital',
        'Perlindungan Perempuan & Anak',
        'Keterbukaan Informasi & Anggaran',
        'Bantuan Hukum',
    ];

    public function __construct(
        private readonly LegalAssistantService $assistantService
    ) {}

    /**
     * Tampilkan formulir pengaduan warga dengan data awal terisi otomatis (prefill)
     * dari hasil konsultasi & analisis pasal yang masih dapat diedit warga.
     */
    public function create(Request $request): View
    {
        $sessionUuid = trim((string) $request->query('session_id', $request->session()->get('bhima_consultation_session_uuid', '')));
        $messageId = (int) $request->query('message_id', 0);

        $prefill = $this->resolvePrefillData($request, $sessionUuid, $messageId);

        return view('consultation.complaint-form', [
            'categories' => self::CATEGORIES,
            'prefill' => $prefill,
            'sessionUuid' => $sessionUuid,
            'messageId' => $prefill['message_id'],
        ]);
    }

    /**
     * Simpan pengaduan warga yang diekskalasi ke tabel dashboard_records (collection = 'complaints')
     * beserta relasinya ke consultation_sessions dan consultation_messages.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $rateKey = 'bhima-complaint:'.sha1((string) ($request->ip() ?? 'unknown'));
        if (RateLimiter::tooManyAttempts($rateKey, 15)) {
            $retryAfter = RateLimiter::availableIn($rateKey);
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Terlalu banyak pengiriman pengaduan. Silakan tunggu {$retryAfter} detik.",
                ], 429);
            }

            return back()->withErrors(['chronology' => "Terlalu banyak pengiriman pengaduan. Silakan tunggu {$retryAfter} detik."])->withInput();
        }
        RateLimiter::hit($rateKey, 60);

        $validated = $request->validate([
            'reporter' => ['nullable', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'summary' => ['required', 'string', 'min:5', 'max:200'],
            'chronology' => ['required', 'string', 'min:10', 'max:4000'],
            'reported' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'incidentDate' => ['nullable', 'date'],
            'ai' => ['nullable', 'string', 'max:5000'],
            'references_text' => ['nullable', 'string', 'max:5000'],
            'references' => ['nullable', 'array', 'max:20'],
            'references.*' => ['string', 'max:500'],
            'session_id' => ['nullable', 'string', 'max:64'],
            'message_id' => ['nullable', 'integer', 'min:1'],
        ], [
            'category.required' => 'Pilih kategori pengaduan terlebih dahulu.',
            'summary.required' => 'Ringkasan pengaduan wajib diisi.',
            'summary.min' => 'Ringkasan pengaduan minimal 5 karakter.',
            'chronology.required' => 'Kronologi kejadian wajib diisi.',
            'chronology.min' => 'Kronologi kejadian minimal 10 karakter.',
        ]);

        $sanitizedChronology = $this->assistantService->sanitizeChronology((string) $validated['chronology']);
        $sanitizedSummary = trim(strip_tags((string) $validated['summary']));
        $reporter = trim(strip_tags((string) ($validated['reporter'] ?? '')));
        if ($reporter === '') {
            $reporter = 'Anonim';
        }

        $reported = trim(strip_tags((string) ($validated['reported'] ?? '')));
        if ($reported === '') {
            $reported = 'Unit Pelayanan Terkait';
        }

        $location = trim(strip_tags((string) ($validated['location'] ?? '')));
        if ($location === '') {
            $location = 'Wilayah Layanan BHIMA';
        }

        $incidentDate = ! empty($validated['incidentDate'])
            ? (string) $validated['incidentDate']
            : now()->toDateString();

        $aiSummary = trim(strip_tags((string) ($validated['ai'] ?? '')));

        $references = [];
        if (! empty($validated['references']) && is_array($validated['references'])) {
            foreach ($validated['references'] as $ref) {
                $cleanRef = trim(strip_tags((string) $ref));
                if ($cleanRef !== '') {
                    $references[] = $cleanRef;
                }
            }
        } elseif (! empty($validated['references_text'])) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $validated['references_text']) ?: [];
            foreach ($lines as $line) {
                $cleanLine = trim(strip_tags($line), " \t\n\r\0\x0B-•");
                if ($cleanLine !== '') {
                    $references[] = $cleanLine;
                }
            }
        }

        // Cari relasi ke consultation_sessions & consultation_messages bila ada
        $sessionUuid = trim((string) ($validated['session_id'] ?? $request->session()->get('bhima_consultation_session_uuid', '')));
        $sessionDbId = null;
        $messageDbId = null;

        if ($sessionUuid !== '') {
            $sessionRow = DB::table('consultation_sessions')->where('session_uuid', $sessionUuid)->first();
            if ($sessionRow) {
                $sessionDbId = (int) $sessionRow->id;
            }
        }

        if (! empty($validated['message_id'])) {
            $msgRow = DB::table('consultation_messages')->where('id', (int) $validated['message_id'])->first();
            if ($msgRow) {
                $messageDbId = (int) $msgRow->id;
                $sessionDbId ??= (int) $msgRow->consultation_session_id;
            }
        } elseif ($sessionDbId !== null) {
            $latestAssistantMsg = DB::table('consultation_messages')
                ->where('consultation_session_id', $sessionDbId)
                ->where('sender', 'assistant')
                ->orderByDesc('id')
                ->first();
            if ($latestAssistantMsg) {
                $messageDbId = (int) $latestAssistantMsg->id;
            }
        }

        $assignee = User::query()
            ->where('role', 'verifikator')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        $ticketId = $this->generateTicketKey();
        $now = now();

        $payload = [
            'id' => $ticketId,
            'date' => $now->toDateString(),
            'category' => (string) $validated['category'],
            'reporter' => $reporter,
            'summary' => $sanitizedSummary,
            'status' => 'Baru',
            'priority' => $aiSummary !== '' ? 'Tinggi' : 'Normal',
            'officer' => $assignee?->name,
            'chronology' => DeepSeekProvider::maskPersonalIdentifiers($sanitizedChronology),
            'reported' => $reported,
            'location' => $location,
            'incidentDate' => $incidentDate,
            'attachments' => [],
            'ai' => $aiSummary !== '' ? $aiSummary : null,
            'references' => $references,
            'consultation_session_id' => $sessionDbId,
            'consultation_message_id' => $messageDbId,
            'notes' => [],
            'history' => [
                [
                    'date' => $now->toIso8601String(),
                    'text' => 'Laporan pengaduan diajukan warga melalui eskalasi Asisten Hukum Virtual BHIMA',
                ],
            ],
        ];

        $insertData = [
            'collection' => 'complaints',
            'record_key' => $ticketId,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'assigned_to' => $assignee?->id,
            'file_path' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('dashboard_records', 'consultation_session_id')) {
            $insertData['consultation_session_id'] = $sessionDbId;
        }
        if (Schema::hasColumn('dashboard_records', 'consultation_message_id')) {
            $insertData['consultation_message_id'] = $messageDbId;
        }

        DB::table('dashboard_records')->insert($insertData);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pengaduan berhasil diajukan dan tercatat dalam sistem BHIMA.',
                'ticket_id' => $ticketId,
                'complaint' => $payload,
                'consultation_session_id' => $sessionDbId,
                'consultation_message_id' => $messageDbId,
            ], 201);
        }

        return redirect()
            ->route('complaint.create', ['session_id' => $sessionUuid])
            ->with('complaint_submitted', [
                'ticket_id' => $ticketId,
                'summary' => $sanitizedSummary,
                'category' => $payload['category'],
                'date' => $payload['date'],
            ]);
    }

    /**
     * Ambil data prefill otomatis dari sesi/pesan konsultasi atau query string.
     *
     * @return array{
     *     reporter: string,
     *     category: string,
     *     summary: string,
     *     chronology: string,
     *     reported: string,
     *     location: string,
     *     incidentDate: string,
     *     ai: string,
     *     references_text: string,
     *     message_id: ?int
     * }
     */
    private function resolvePrefillData(Request $request, string $sessionUuid, int $messageId): array
    {
        $chronology = trim((string) $request->query('chronology', ''));
        $aiSummary = trim((string) $request->query('pasal_ringkas', $request->query('ai', '')));
        $referencesText = trim((string) $request->query('rujukan', $request->query('references_text', '')));
        $category = trim((string) $request->query('category', ''));
        $summary = trim((string) $request->query('summary', ''));
        $resolvedMessageId = $messageId > 0 ? $messageId : null;

        $assistantRow = null;
        $sessionRow = null;

        if ($sessionUuid !== '') {
            $sessionRow = DB::table('consultation_sessions')->where('session_uuid', $sessionUuid)->first();
        }

        if ($messageId > 0) {
            $assistantRow = DB::table('consultation_messages')
                ->where('id', $messageId)
                ->where('sender', 'assistant')
                ->first();
        } elseif ($sessionRow) {
            $assistantRow = DB::table('consultation_messages')
                ->where('consultation_session_id', $sessionRow->id)
                ->where('sender', 'assistant')
                ->orderByDesc('id')
                ->first();
        }

        if ($assistantRow) {
            $resolvedMessageId = (int) $assistantRow->id;

            // Cari pesan user tepat sebelum pesan asisten ini
            if ($chronology === '') {
                $userRow = DB::table('consultation_messages')
                    ->where('consultation_session_id', $assistantRow->consultation_session_id)
                    ->where('sender', 'user')
                    ->where('id', '<=', $assistantRow->id)
                    ->orderByDesc('id')
                    ->first();

                if ($userRow) {
                    $chronology = trim((string) $userRow->content);
                }
            }

            $analysis = isset($assistantRow->legal_analysis) && $assistantRow->legal_analysis
                ? (json_decode((string) $assistantRow->legal_analysis, true) ?: [])
                : [];
            $citations = json_decode((string) ($assistantRow->citations ?? '[]'), true) ?: [];

            if ($category === '' && ! empty($citations[0]['topic'])) {
                $candidateTopic = (string) $citations[0]['topic'];
                if (in_array($candidateTopic, self::CATEGORIES, true)) {
                    $category = $candidateTopic;
                }
            }

            if ($aiSummary === '' && ! empty($analysis)) {
                $aiLines = [];
                foreach (($analysis['pasal_relevan'] ?? []) as $p) {
                    $aiLines[] = sprintf(
                        '- %s (%s): %s',
                        $p['pasal'] ?? '',
                        $p['dokumen'] ?? '',
                        $p['penjelasan_awam'] ?? ''
                    );
                }
                foreach (($analysis['ancaman_sanksi'] ?? []) as $s) {
                    $aiLines[] = sprintf(
                        '- Estimasi Ancaman Sanksi %s [%s]: %s',
                        $s['pasal'] ?? '',
                        strtoupper((string) ($s['jenis'] ?? 'lainnya')),
                        $s['bunyi_sanksi_ringkas'] ?? ''
                    );
                }
                $aiSummary = implode("\n", $aiLines);
            }

            if ($referencesText === '' && ! empty($analysis['rujukan'])) {
                $refLines = [];
                foreach ($analysis['rujukan'] as $r) {
                    $refLines[] = sprintf(
                        '%s (%s) — %s',
                        $r['dokumen'] ?? '',
                        $r['nomor_tahun'] ?? '',
                        $r['tautan'] ?? ''
                    );
                }
                $referencesText = implode("\n", $refLines);
            } elseif ($referencesText === '' && ! empty($citations)) {
                $refLines = [];
                foreach ($citations as $c) {
                    $refLines[] = sprintf(
                        '%s (%s Tahun %s) - %s — %s',
                        $c['document_name'] ?? '',
                        $c['document_number'] ?? '',
                        $c['document_year'] ?? '',
                        $c['article_label'] ?? '',
                        $c['source_url'] ?? ''
                    );
                }
                $referencesText = implode("\n", array_unique($refLines));
            }
        }

        if ($category === '' || ! in_array($category, self::CATEGORIES, true)) {
            $category = self::CATEGORIES[0];
        }

        if ($summary === '' && $chronology !== '') {
            $summary = mb_strlen($chronology) > 110
                ? mb_substr($chronology, 0, 107).'...'
                : $chronology;
        }

        return [
            'reporter' => 'Anonim',
            'category' => $category,
            'summary' => $summary,
            'chronology' => $chronology,
            'reported' => 'Unit Pelayanan Terkait',
            'location' => '',
            'incidentDate' => now()->toDateString(),
            'ai' => $aiSummary,
            'references_text' => $referencesText,
            'message_id' => $resolvedMessageId,
        ];
    }

    private function generateTicketKey(): string
    {
        $year = now()->format('Y');
        $count = DB::table('dashboard_records')
            ->where('collection', 'complaints')
            ->count();

        for ($offset = 1; $offset <= 500; $offset++) {
            $candidate = sprintf('BHM-%s-%04d', $year, $count + $offset);
            $exists = DB::table('dashboard_records')
                ->where('collection', 'complaints')
                ->where('record_key', $candidate)
                ->exists();
            if (! $exists) {
                return $candidate;
            }
        }

        return 'BHM-'.$year.'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
}
