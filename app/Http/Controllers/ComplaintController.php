<?php

namespace App\Http\Controllers;

use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ComplaintController extends Controller
{
    private const CATEGORIES = [
        'Pungutan Liar',
        'Dugaan Pungutan Liar',
        'Pelayanan Lambat',
        'Prosedur Tidak Jelas',
        'Administrasi Kependudukan',
        'Pelayanan Kesehatan',
        'Infrastruktur',
        'Ketenagakerjaan',
        'Pertanahan',
        'Maladministrasi Lainnya',
    ];

    public function __construct(private readonly ComplaintService $complaints) {}

    public function create(Request $request)
    {
        $uuid = (string) ($request->query('session')
            ?: $request->query('session_id')
            ?: ($request->hasSession() ? $request->session()->get('bhima_consultation_session_uuid') : ''));
        $messageId = $request->filled('message_id') ? (int) $request->query('message_id') : null;

        $prefill = $this->complaints->prescreeningFor($uuid ?: null, $messageId);
        if (! $prefill && $request->filled('chronology')) {
            $prefill = [
                'session_uuid' => $uuid,
                'message_id' => $messageId,
                'chronology' => trim((string) $request->query('chronology')),
                'answer' => trim((string) $request->query('pasal_ringkas', '')),
                'citations' => [],
                'legal_analysis' => null,
            ];
        }

        return view('complaint.create', [
            'prefill' => $prefill,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        // Dukung pengiriman JSON / skema eskalasi PBI 2 maupun pengiriman form langsung dari halaman pengaduan
        if ($request->expectsJson() || ($request->filled('chronology') && ! $request->filled('uraian'))) {
            return app(PublicComplaintController::class)->store($request);
        }

        $data = $request->validate([
            'kategori' => ['required', Rule::in(self::CATEGORIES)],
            'instansi_terlapor' => 'required|string|max:200',
            'tanggal_kejadian' => 'required|date|before_or_equal:today',
            'lokasi' => 'required|string|max:200',
            'uraian' => 'required|string|min:20|max:2000',
            'is_anonim' => 'nullable|boolean',
            'nama_pelapor' => 'required_unless:is_anonim,1|nullable|string|max:120',
            'kontak' => 'nullable|string|max:120',
            'session_uuid' => ['nullable', 'regex:/^[A-Za-z0-9_-]{12,64}$/'],
            'persetujuan' => 'accepted',
            'lampiran' => 'nullable|array|max:5',
            'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'kategori.required' => 'Pilih kategori pengaduan.',
            'uraian.min' => 'Uraian minimal 20 karakter agar dapat ditelaah petugas.',
            'nama_pelapor.required_unless' => 'Isi nama pelapor atau pilih kirim sebagai anonim.',
            'persetujuan.accepted' => 'Centang pernyataan kebenaran informasi.',
            'lampiran.*.mimes' => 'Lampiran harus berformat PDF, JPG, atau PNG.',
            'lampiran.*.max' => 'Setiap lampiran maksimal 10 MB.',
        ]);

        $ticket = $this->complaints->create($data, $request->file('lampiran', []));

        return redirect()->route('complaint.create')->with([
            'ticket' => $ticket,
            'submitted_at' => now('Asia/Jakarta')->locale('id')->translatedFormat('d M Y • H:i').' WIB',
        ]);
    }

    public function trackForm(Request $request)
    {
        $ticketParam = strtoupper(trim((string) $request->query('tiket', '')));
        if ($ticketParam !== '' && preg_match('/^BHM-(\d{4}-\d{4,}|[A-Z0-9]{10})$/', $ticketParam)) {
            return view('complaint.track', [
                'autoSearched' => true,
                'autoTicket' => $ticketParam,
                'autoResult' => $this->complaints->trackByTicket($ticketParam),
            ]);
        }

        return view('complaint.track');
    }

    public function track(Request $request)
    {
        $request->merge(['tiket' => strtoupper(trim((string) $request->input('tiket')))]);
        $data = $request->validate(
            ['tiket' => ['required', 'regex:/^BHM-(\d{4}-\d{4,}|[A-Z0-9]{10})$/']],
            ['tiket.regex' => 'Format nomor tiket tidak valid.']
        );

        return back()->withInput()->with([
            'searched' => true,
            'result' => $this->complaints->trackByTicket($data['tiket']),
        ]);
    }

    public function submitCompleteness(Request $request, string $tiket)
    {
        $data = $request->validate([
            'catatan' => 'required|string|min:5|max:2000',
            'lampiran' => 'required|array|min:1|max:5',
            'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'catatan.required' => 'Catatan kelengkapan wajib diisi.',
            'catatan.min' => 'Catatan kelengkapan minimal 5 karakter.',
            'lampiran.required' => 'Unggah minimal satu berkas kelengkapan.',
            'lampiran.max' => 'Maksimal 5 berkas untuk sekali pengiriman.',
            'lampiran.*.mimes' => 'Berkas harus berformat PDF, JPG, atau PNG.',
            'lampiran.*.max' => 'Setiap berkas maksimal 10 MB.',
        ]);

        $note = trim(strip_tags($data['catatan']));
        if (mb_strlen($note) < 5) {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan kelengkapan minimal 5 karakter.',
            ]);
        }

        $this->complaints->submitCompleteness($tiket, $note, $request->file('lampiran', []));

        return redirect()
            ->route('complaint.track', ['tiket' => $tiket])
            ->with('completeness_submitted', 'Kelengkapan berkas berhasil dikirim. Laporan Anda kembali masuk tahap pemeriksaan verifikator.');
    }
}