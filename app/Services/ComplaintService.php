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

    // Buat nomor tiket acak yang unik

    public function newTicket(): string
    {
        do {
            $ticket = 'BHM-'.strtoupper(Str::random(10));
        } while (DB::table('dashboard_records')
            ->where('collection', 'complaints')
            ->where('record_key', $ticket)
            ->exists());

        return $ticket;
    }
}