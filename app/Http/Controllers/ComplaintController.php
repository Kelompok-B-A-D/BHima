<?php

namespace App\Http\Controllers;

use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    // TODO: samakan dengan daftar `categories` di resources/js/data.js
    private const CATEGORIES = ['Pungutan Liar', 'Pelayanan Lambat', 'Prosedur Tidak Jelas', 'Maladministrasi Lainnya'];

    public function __construct(private readonly ComplaintService $complaints) {}

    public function create(Request $request)
    {
        $uuid = $request->query('session')
            ?: $request->session()->get('bhima_consultation_session_uuid');

        return view('complaint.create', [
            'prefill' => $this->complaints->prescreeningFor($uuid),
        ]);
    }

    public function store(Request $request)
    {
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

    public function trackForm()
    {
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
}