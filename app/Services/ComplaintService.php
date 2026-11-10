<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    // Ambil hasil pre-screening terakhir dari sesi konsultasi chatbot.

    public function prescreeningFor(?string $uuid): ?array
    {
        if (! $uuid || ! preg_match('/^[A-Za-z0-9_-]{12,64}$/', $uuid)) {
            return null;
        }

        $session = DB::table('consultation_sessions')->where('session_uuid', $uuid)->first();
        if (! $session) {
            return null;
        }

        $base = DB::table('consultation_messages')->where('consultation_session_id', $session->id);
        $assistant = (clone $base)->where('sender', 'assistant')->latest('id')->first();
        $user = (clone $base)->where('sender', 'user')->latest('id')->first();

        if (! $assistant) {
            return null;
        }

        return [
            'session_uuid' => $uuid,
            'chronology' => $user?->content,
            'answer' => $assistant->content,
            'citations' => json_decode($assistant->citations ?? '[]', true) ?: [],
        ];
    }

    // Nomor tiket berurutan: BHM-tahun-nomor (contoh BHM-2026-0001).

    public function newTicket(): string
    {
        $year = now('Asia/Jakarta')->year;
        $prefix = "BHM-{$year}-";

        $last = DB::table('dashboard_records')
            ->where('collection', 'complaints')
            ->where('record_key', 'like', $prefix.'%')
            ->pluck('record_key')
            ->map(fn ($key) => (int) Str::after($key, $prefix))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    // Simpan pengaduan warga ke dashboard_records (collection 'complaints').

    public function create(array $input, array $files = []): string
    {
        $anon = (bool) ($input['is_anonim'] ?? false);
        $prefill = $this->prescreeningFor($input['session_uuid'] ?? null);

        return DB::transaction(function () use ($input, $files, $anon, $prefill) {
            // Samakan dengan kunci yang dipakai dashboard supaya dua pengaduan tidak mendapat nomor sama
            DB::table('users')->orderBy('id')->lockForUpdate()->get(['id']);
            $ticket = $this->newTicket();
            $now = now();

            $attachments = [];
            foreach ($files as $file) {
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store('complaints/'.$ticket, 'local'),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }

            $references = collect($prefill['citations'] ?? [])
                ->map(fn ($c) => trim(data_get($c, 'document_name', 'Rujukan').' '.data_get($c, 'article_label', '')))
                ->filter()->values()->all();

            $payload = [
                'id' => $ticket,
                'date' => $now->toDateString(),
                'category' => $input['kategori'],
                'reporter' => $anon ? 'Anonim' : $input['nama_pelapor'],
                'contact' => $anon ? null : ($input['kontak'] ?? null),
                'summary' => Str::limit($input['uraian'], 140),
                'status' => 'Baru',
                'priority' => 'Sedang',
                'officer' => null,
                'reported' => $input['instansi_terlapor'],
                'location' => $input['lokasi'],
                'incidentDate' => $input['tanggal_kejadian'],
                'chronology' => $input['uraian'],
                'agency' => null,
                'ai' => $prefill['answer'] ?? null,
                'references' => $references,
                'attachments' => $attachments,
                'notes' => [],
                'history' => [['date' => $now->toIso8601String(), 'text' => 'received · Baru']],
            ];

            DB::table('dashboard_records')->insert([
                'collection' => 'complaints',
                'record_key' => $ticket,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'assigned_to' => null,
                'file_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $ticket;
        });
    }

    // Cari pengaduan berdasarkan nomor tiket

    public function trackByTicket(string $ticket): ?array
    {
        $record = DB::table('dashboard_records')
            ->where('collection', 'complaints')
            ->where('record_key', $ticket)
            ->first();

        if (! $record) {
            return null;
        }

        $p = json_decode($record->payload, true) ?: [];
        $map = [
            'Baru' => 'Diterima',
            'Menunggu Verifikasi' => 'Diperiksa',
            'Menunggu Disposisi' => 'Diperiksa',
            'Menunggu Kelengkapan' => 'Perlu Dilengkapi',
            'Dalam Proses' => 'Diproses',
            'Selesai' => 'Selesai',
            'Ditolak' => 'Ditolak',
        ];
        $label = fn ($s) => $map[$s] ?? $s;

        $history = collect($p['history'] ?? [])->map(fn ($h) => [
            'date' => $h['date'] ?? null,
            'status' => $label(trim(Str::afterLast($h['text'] ?? '', '·'))),
        ])->values()->all();

        return [
            'ticket' => $ticket,
            'status' => $label($p['status'] ?? 'Baru'),
            'category' => $p['category'] ?? null,
            'date' => $p['date'] ?? null,
            'history' => $history,
        ];
    }
}