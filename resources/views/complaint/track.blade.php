<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shell-type" content="web_standard">
    <title>BHIMA - Lacak Status Pengaduan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="stitch-liquid-body min-h-screen overflow-x-hidden bg-gradient-to-b from-[#E3EFFB] via-[#E9EEF5] to-[#F6F9FF] font-sans text-[#171C21] antialiased selection:bg-[#D6E3FF] selection:text-[#001B3E]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-xl focus:bg-[#F59E0B] focus:text-[#111827] focus:font-bold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <div class="w-full overflow-x-hidden">
        @include('partials.liquid-header', ['activeNav' => 'lacak-tiket'])

        <main id="portal-main-content" class="w-full pt-24 min-[641px]:pt-28 pb-10 min-[641px]:pb-14 bg-transparent relative min-h-[calc(100vh-280px)] stitch-print-full">
            <div class="max-w-[1200px] mx-auto w-full px-3 min-[481px]:px-4 min-[641px]:px-6 space-y-6 min-[641px]:space-y-8">
                <!-- Screen Header & Context -->
                <section class="space-y-3 max-w-3xl">
                    <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/90 border border-[#DEE3EA] text-xs uppercase tracking-wider font-bold text-[#005AB3]">
                        <span class="w-2 h-2 rounded-full bg-[#005AB3] shrink-0" aria-hidden="true"></span>
                        <span>Pelayanan Pengaduan Masyarakat Mandiri</span>
                    </p>
                    <h1 class="text-[26px] leading-[1.15] min-[380px]:text-3xl min-[641px]:text-[40px] min-[1008px]:text-[46px] font-extrabold tracking-tight text-[#171C21]">
                        Lacak Status Pengaduan
                    </h1>
                    <p class="text-xs min-[380px]:text-sm min-[641px]:text-base text-[#414754] leading-relaxed">
                        Masukkan nomor tiket untuk memantau perkembangan verifikasi dan disposisi laporan pengaduan Anda secara langsung, tanpa perlu membuat akun.
                    </p>
                </section>

                <!-- FLOATING LIQUID GLASS TICKET SEARCH PILL (Focal Blur Element #2) -->
                <section class="stitch-no-print" aria-label="Formulir Pencarian Nomor Tiket">
                    <form
                        id="trackTicketForm"
                        method="POST"
                        action="{{ route('complaint.track.submit') }}"
                        class="p-3 min-[641px]:p-3.5 rounded-3xl min-[641px]:rounded-full bg-white/85 backdrop-blur-2xl border border-white shadow-[0_20px_48px_rgba(20,30,80,0.08),inset_0_1px_1px_rgba(255,255,255,0.85)] flex flex-col min-[641px]:flex-row items-stretch min-[641px]:items-center gap-2.5"
                    >
                        @csrf
                        <div class="flex items-center gap-3 px-3.5 min-[641px]:pl-4 min-[641px]:pr-2 flex-1 min-w-0 bg-[#EFF4FB]/80 min-[641px]:bg-transparent rounded-2xl py-2 min-[641px]:py-0">
                            <span class="material-symbols-outlined text-[#005AB3] text-[24px] shrink-0" aria-hidden="true">confirmation_number</span>
                            <div class="flex flex-col flex-1 min-w-0">
                                <label for="ticket-input" class="text-[11px] text-[#414754] uppercase tracking-wider font-bold leading-tight">
                                    Nomor Resi / Tiket Aduan
                                </label>
                                <input
                                    id="ticket-input"
                                    name="tiket"
                                    type="text"
                                    value="{{ old('tiket', $autoTicket ?? request('tiket')) }}"
                                    placeholder="BHM-2026-0001"
                                    required
                                    autocomplete="off"
                                    class="w-full min-h-[38px] bg-transparent text-sm min-[641px]:text-base font-bold text-[#171C21] placeholder:text-[#414754]/60 focus:outline-none uppercase font-mono"
                                >
                            </div>
                            <button
                                type="button"
                                id="clearTicketBtn"
                                aria-label="Bersihkan nomor tiket"
                                title="Bersihkan kolom tiket"
                                class="w-11 h-11 rounded-full hover:bg-[#E4E9F0] flex items-center justify-center text-[#414754] hover:text-[#171C21] transition-colors shrink-0"
                            >
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                            </button>
                        </div>

                        <button
                            id="trackSubmitBtn"
                            type="submit"
                            class="w-full min-[641px]:w-auto min-h-[48px] px-7 rounded-full bg-[#005AB3] hover:bg-[#00468D] active:bg-[#003366] text-white text-xs min-[380px]:text-sm font-bold flex items-center justify-center gap-2 shadow-[0_8px_20px_rgba(0,90,179,0.28)] transition-all shrink-0"
                        >
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_search</span>
                            <span id="trackSubmitLabel">Lacak Status</span>
                        </button>
                    </form>
                </section>

                <!-- Server Validation Errors -->
                @if ($errors->any())
                    <div role="alert" class="rounded-2xl bg-[#FFDAD6] border border-[#BA1A1A]/30 text-[#93000A] p-4 min-[641px]:p-5 flex items-start gap-3">
                        <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">error</span>
                        <div class="space-y-1 text-xs min-[641px]:text-sm">
                            <strong class="font-bold block">Format Nomor Tiket Belum Sesuai</strong>
                            <p>{{ $errors->first() }} Pastikan format tiket diawali <span class="font-mono font-bold">BHM-</span> (contoh: <span class="font-mono font-bold">BHM-2026-0001</span>).</p>
                        </div>
                    </div>
                @endif

                @php
                    $wasSearched = session('searched') || ($autoSearched ?? false);
                    $result = session('searched') ? session('result') : ($autoResult ?? null);
                    $searchedTicket = strtoupper(trim((string) old('tiket', $autoTicket ?? request('tiket'))));
                @endphp

                @if ($wasSearched)
                    @if ($result)
                        @php
                            $current = $result['status'];
                            $rejected = $current === 'Ditolak';
                            $needsFix = $current === 'Perlu Dilengkapi';
                            $stages = $needsFix
                                ? ['Diterima', 'Diperiksa', 'Perlu Dilengkapi', 'Diproses', 'Selesai']
                                : ['Diterima', 'Diperiksa', 'Diproses', 'Selesai'];
                            $idx = array_search($current, $stages);
                            $idx = $idx === false ? 0 : $idx;

                            $statusBadgeClass = match ($current) {
                                'Ditolak' => 'bg-[#FFDAD6] text-[#93000A] border-[#BA1A1A]/30',
                                'Perlu Dilengkapi' => 'bg-[#FEF3C7] text-[#78350F] border-[#FDE68A]',
                                'Selesai' => 'bg-[#DCFCE7] text-[#166534] border-[#BBF7D0]',
                                'Diproses' => 'bg-[#D6E3FF] text-[#00468D] border-[#AEC6FF]',
                                default => 'bg-[#9CF2E8] text-[#00504A] border-[#7FD6CD]',
                            };
                        @endphp

                        <!-- Main Asymmetric Bento Result -->
                        <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 min-[1008px]:gap-7 items-start">
                            <!-- LEFT COLUMN: Status Card & Vertical Progression (7 cols) -->
                            <div class="min-[1008px]:col-span-7 flex flex-col space-y-6">
                                <!-- Status Overview Lens -->
                                <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] space-y-6">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <span class="text-xs text-[#414754] uppercase tracking-wider font-bold block">ID Berkas Resmi</span>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-lg min-[380px]:text-xl min-[641px]:text-2xl font-extrabold text-[#005AB3] font-mono tracking-wide" id="ticket-number-display">{{ $result['ticket'] }}</span>
                                                <span class="material-symbols-outlined text-[#0F766E] text-[20px]" title="Tiket Terdaftar" aria-hidden="true">verified</span>
                                                <button
                                                    type="button"
                                                    id="copy-ticket-btn"
                                                    aria-label="Salin nomor tiket"
                                                    class="stitch-no-print min-h-[44px] px-3 rounded-full bg-[#EFF4FB] hover:bg-[#D6E3FF] text-[#005AB3] text-xs font-bold inline-flex items-center gap-1.5 border border-[#DEE3EA] transition-colors"
                                                >
                                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">content_copy</span>
                                                    <span id="copy-feedback">Salin</span>
                                                </button>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full border text-xs min-[641px]:text-sm font-bold {{ $statusBadgeClass }}">
                                            <span class="w-2 h-2 rounded-full bg-current" aria-hidden="true"></span>
                                            <span>{{ $current }}</span>
                                        </span>
                                    </div>

                                    <!-- 3-Column Metadata Ribbon -->
                                    <dl class="grid grid-cols-1 min-[481px]:grid-cols-3 gap-4 p-4 rounded-2xl bg-[#EFF4FB]/90 border border-[#DEE3EA]">
                                        <div class="space-y-1">
                                            <dt class="text-[11px] text-[#414754] uppercase tracking-wider font-bold">Tanggal Masuk</dt>
                                            <dd class="text-sm text-[#171C21] font-bold">{{ $result['date'] ?? '-' }}</dd>
                                        </div>
                                        <div class="space-y-1">
                                            <dt class="text-[11px] text-[#414754] uppercase tracking-wider font-bold">Klasifikasi Kasus</dt>
                                            <dd class="text-sm text-[#171C21] font-bold">{{ $result['category'] ?? 'Pengaduan Publik' }}</dd>
                                        </div>
                                        <div class="space-y-1">
                                            <dt class="text-[11px] text-[#414754] uppercase tracking-wider font-bold">Saluran Pelacakan</dt>
                                            <dd class="text-sm text-[#0F766E] font-bold">Tanpa Akun (Publik)</dd>
                                        </div>
                                    </dl>
                                </div>

                                @if ($needsFix)
                                    <div class="rounded-2xl bg-[#FEF3C7] border border-[#FDE68A] text-[#78350F] p-5 flex items-start gap-3">
                                        <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">warning</span>
                                        <div class="text-xs min-[641px]:text-sm space-y-1">
                                            <strong class="font-bold block">Perlu Dilengkapi</strong>
                                            <p>Petugas verifikator meminta kelengkapan berkas tambahan untuk menindaklanjuti laporan ini.</p>
                                        </div>
                                    </div>
                                @endif

                                @if ($rejected)
                                    <div class="rounded-2xl bg-[#FFDAD6] border border-[#BA1A1A]/30 text-[#93000A] p-5 space-y-3">
                                        <div class="flex items-start gap-3">
                                            <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">cancel</span>
                                            <div class="text-xs min-[641px]:text-sm space-y-1">
                                                <strong class="font-bold block">Laporan Ditolak</strong>
                                                <p>Laporan berada di luar kewenangan layanan atau belum memenuhi syarat administrasi. Anda dapat mengajukan laporan baru dengan data yang sesuai.</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('complaint.create') }}" class="min-h-[44px] px-5 rounded-full bg-[#93000A] hover:bg-[#690005] text-white text-xs font-bold inline-flex items-center gap-2 transition-colors">
                                            <span>Ajukan Laporan Baru</span>
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_forward</span>
                                        </a>
                                    </div>
                                @else
                                    <!-- Vertical SOP Timeline Card -->
                                    <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] space-y-6">
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <div class="flex items-center gap-2.5">
                                                <span class="material-symbols-outlined text-[#005AB3] text-[24px]" aria-hidden="true">timeline</span>
                                                <h2 class="text-lg min-[641px]:text-xl font-bold text-[#171C21]">Tahapan Penanganan</h2>
                                            </div>
                                            <span class="text-xs px-3 py-1 rounded-full bg-[#EFF4FB] text-[#414754] font-bold border border-[#DEE3EA]">
                                                Tahap {{ $idx + 1 }} dari {{ count($stages) }}
                                            </span>
                                        </div>

                                        <ol class="space-y-4">
                                            @foreach ($stages as $i => $stage)
                                                @php
                                                    $isDone = $i < $idx;
                                                    $isCurrent = $i === $idx;
                                                    $stageDesc = match ($stage) {
                                                        'Diterima' => 'Laporan resmi tercatat di sistem BHIMA dan menunggu pemeriksaan awal.',
                                                        'Diperiksa' => 'Petugas verifikator memeriksa kelengkapan kronologi dan kewenangan.',
                                                        'Perlu Dilengkapi' => 'Berkas memerlukan data atau kronologi tambahan sebelum diproses.',
                                                        'Diproses' => 'Laporan telah didisposisikan dan sedang ditindaklanjuti unit terkait.',
                                                        'Selesai' => 'Penanganan laporan telah selesai dan tercatat final.',
                                                        default => '',
                                                    };
                                                @endphp
                                                <li class="flex items-start gap-3.5 p-3.5 rounded-2xl {{ $isCurrent ? 'bg-[#D6E3FF]/45 border border-[#AEC6FF]' : ($isDone ? 'bg-[#EFF4FB]/70 border border-[#DEE3EA]' : 'bg-white border border-[#DEE3EA]/70 opacity-75') }}">
                                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $isDone ? 'bg-[#0F766E] text-white' : ($isCurrent ? 'bg-[#005AB3] text-white shadow-sm' : 'bg-[#E4E9F0] text-[#414754]') }}">
                                                        @if ($isDone)
                                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">check</span>
                                                        @else
                                                            {{ $i + 1 }}
                                                        @endif
                                                    </span>
                                                    <div class="min-w-0 flex-1 space-y-0.5">
                                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                                            <span class="text-sm font-bold {{ $isCurrent ? 'text-[#00468D]' : 'text-[#171C21]' }}">{{ $stage }}</span>
                                                            @if ($isCurrent)
                                                                <span class="px-2.5 py-0.5 rounded-full bg-[#005AB3] text-white text-[10px] font-bold uppercase tracking-wider">Aktif</span>
                                                            @elseif ($isDone)
                                                                <span class="text-[11px] font-bold text-[#0F766E]">Selesai</span>
                                                            @endif
                                                        </div>
                                                        @if ($stageDesc !== '')
                                                            <p class="text-xs text-[#414754] leading-relaxed">{{ $stageDesc }}</p>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endif
                            </div>

                            <!-- RIGHT COLUMN: Unit Disposisi & Riwayat Penanganan (5 cols) -->
                            <div class="min-[1008px]:col-span-5 flex flex-col space-y-6">
                                <!-- Instansi Pengampu Disposisi -->
                                <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-2xl bg-[#D6E3FF] flex items-center justify-center text-[#005AB3] shrink-0">
                                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">corporate_fare</span>
                                        </div>
                                        <div>
                                            <span class="text-[11px] text-[#414754] uppercase tracking-wider font-bold block">Instansi Tujuan Disposisi</span>
                                            <h2 class="text-base min-[641px]:text-lg font-bold text-[#171C21] leading-snug">
                                                {{ $result['agency'] ?: 'Dalam Antrean Verifikasi & Disposisi' }}
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-[#EFF4FB]/90 border border-[#DEE3EA] flex items-start gap-2.5">
                                        <span class="material-symbols-outlined text-[#005AB3] text-[19px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                                        <p class="text-xs text-[#414754] leading-relaxed">
                                            {{ $result['agency'] ? 'Laporan telah diteruskan ke instansi di atas untuk pemeriksaan dan klarifikasi lebih lanjut.' : 'Setelah verifikator menyelesaikan pemeriksaan berkas awal, instansi tujuan disposisi akan ditampilkan di sini.' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Riwayat Penanganan Log -->
                                <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] space-y-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <h2 class="text-base min-[641px]:text-lg font-bold text-[#171C21]">Riwayat Penanganan</h2>
                                        <span class="text-xs font-bold text-[#0F766E]">{{ count($result['history'] ?? []) }} Catatan</span>
                                    </div>
                                    @if (!empty($result['history']))
                                        <ul class="space-y-3">
                                            @foreach ($result['history'] as $h)
                                                <li class="flex flex-col min-[481px]:flex-row min-[481px]:items-center justify-between gap-1.5 p-3.5 rounded-2xl bg-[#EFF4FB]/80 border border-[#DEE3EA]">
                                                    <span class="text-xs min-[641px]:text-sm font-bold text-[#171C21]">{{ $h['status'] }}</span>
                                                    <span class="text-[11px] min-[641px]:text-xs font-semibold text-[#414754]">{{ $h['date'] ?? '-' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-xs text-[#414754] italic p-3 rounded-2xl bg-[#EFF4FB]/80 border border-[#DEE3EA]">
                                            Belum ada catatan perubahan status tambahan.
                                        </p>
                                    @endif
                                </div>

                                <!-- Bantuan & Cetak Status -->
                                <div class="stitch-no-print rounded-[28px] bg-gradient-to-br from-[#005AB3]/8 via-white/95 to-[#EFF4FB] border border-[#005AB3]/20 p-5 min-[641px]:p-6 space-y-3">
                                    <h3 class="text-sm min-[641px]:text-base font-bold text-[#005AB3]">Butuh Penjelasan Dasar Hukum?</h3>
                                    <p class="text-xs text-[#414754] leading-relaxed">
                                        Konsultasikan peraturan JDIH dan SOP layanan terkait laporan Anda kepada Asisten Hukum Virtual BHIMA, atau cetak bukti status ini.
                                    </p>
                                    <div class="flex flex-col min-[481px]:flex-row gap-2.5 pt-1">
                                        <a href="{{ route('consultation.index') }}" class="flex-1 min-h-[44px] px-4 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                                            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">smart_toy</span>
                                            <span>Tanya Asisten</span>
                                        </a>
                                        <button type="button" onclick="window.print()" class="min-h-[44px] px-4 rounded-full bg-white hover:bg-[#EFF4FB] text-[#171C21] border border-[#C0C6D6] text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                                            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">print</span>
                                            <span>Cetak</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Service Evaluation Banner -->
                        <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_12px_32px_rgba(20,30,80,0.06)] flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-2xl">
                                <div class="flex items-center gap-2 text-[#171C21]">
                                    <span class="material-symbols-outlined text-[22px] text-[#005AB3]" aria-hidden="true">rate_review</span>
                                    <h2 class="text-base min-[641px]:text-lg font-bold">Evaluasi Kepuasan Pelayanan Publik</h2>
                                </div>
                                <p class="text-xs min-[641px]:text-sm text-[#414754] leading-relaxed">
                                    @if ($current === 'Selesai')
                                        Laporan telah selesai ditangani. Formulir evaluasi kepuasan akan tersedia di sini.
                                    @else
                                        <strong>Evaluasi kepuasan</strong> dapat diisi setelah status penanganan laporan dinyatakan <strong>Selesai</strong>.
                                    @endif
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ $current === 'Selesai' ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#EFF4FB] text-[#414754] border border-[#DEE3EA]' }} text-xs font-bold shrink-0 self-start min-[641px]:self-center">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">{{ $current === 'Selesai' ? 'check_circle' : 'lock' }}</span>
                                <span>{{ $current === 'Selesai' ? 'Penanganan Selesai' : 'Terbuka Setelah Selesai' }}</span>
                            </span>
                        </div>
                    @else
                        <!-- Ticket Not Found State -->
                        <div class="rounded-[28px] bg-[#FEF3C7] border border-[#FDE68A] p-6 min-[641px]:p-8 space-y-4">
                            <div class="flex items-start gap-3.5 text-[#78350F]">
                                <span class="material-symbols-outlined text-[28px] shrink-0" aria-hidden="true">search_off</span>
                                <div class="space-y-1.5">
                                    <h2 class="text-lg min-[641px]:text-xl font-bold">Tiket Tidak Ditemukan</h2>
                                    <p class="text-xs min-[641px]:text-sm leading-relaxed">
                                        Tiket <strong class="font-mono">{{ $searchedTicket ?: 'yang dimasukkan' }}</strong> tidak ditemukan di basis data pengaduan BHIMA. Periksa kembali penulisan huruf, tahun, dan angka pada kode tiket Anda.
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    onclick="document.getElementById('ticket-input')?.focus()"
                                    class="min-h-[44px] px-5 rounded-full bg-white text-[#78350F] border border-[#F59E0B] font-bold text-xs min-[641px]:text-sm inline-flex items-center gap-1.5 hover:bg-[#FFFBEB] transition-colors"
                                >
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit</span>
                                    <span>Periksa Ulang Nomor Tiket</span>
                                </button>
                                <a
                                    href="{{ route('complaint.create') }}"
                                    class="min-h-[44px] px-5 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white font-bold text-xs min-[641px]:text-sm inline-flex items-center gap-1.5 transition-colors"
                                >
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                                    <span>Buat Pengaduan Baru</span>
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Initial Empty Guidance State -->
                    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-5 min-[641px]:gap-6">
                        <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_12px_32px_rgba(20,30,80,0.06)] space-y-3">
                            <div class="w-11 h-11 rounded-2xl bg-[#D6E3FF] text-[#005AB3] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">receipt_long</span>
                            </div>
                            <h2 class="text-lg font-bold text-[#171C21]">Cara Melihat Perkembangan Tiket</h2>
                            <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                Masukkan nomor tiket yang Anda peroleh setelah mengirim laporan melalui Formulir Pengaduan, misalnya dengan pola <span class="font-mono font-bold text-[#005AB3]">BHM-2026-0001</span>.
                            </p>
                        </div>

                        <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_12px_32px_rgba(20,30,80,0.06)] flex flex-col justify-between gap-4">
                            <div class="space-y-3">
                                <div class="w-11 h-11 rounded-2xl bg-[#9CF2E8] text-[#00504A] flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">campaign</span>
                                </div>
                                <h2 class="text-lg font-bold text-[#171C21]">Belum Mengirim Pengaduan?</h2>
                                <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                    Anda dapat berkonsultasi terlebih dahulu melalui Asisten Hukum atau langsung menyampaikan aduan layanan publik secara aman dan anonim.
                                </p>
                            </div>
                            <div class="flex flex-col min-[768px]:flex-row flex-wrap gap-2.5">
                                <a href="{{ route('complaint.create') }}" class="min-h-[44px] px-4 min-[768px]:px-5 rounded-full bg-[#F59E0B] hover:bg-[#D97706] text-[#111827] text-xs font-bold inline-flex items-center justify-center gap-1.5 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                                    <span>Buat Pengaduan</span>
                                </a>
                                <a href="{{ route('consultation.index') }}" class="min-h-[44px] px-4 min-[768px]:px-5 rounded-full bg-[#EFF4FB] hover:bg-[#D6E3FF] text-[#005AB3] border border-[#DEE3EA] text-xs font-bold inline-flex items-center justify-center gap-1.5 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">smart_toy</span>
                                    <span>Tanya Asisten</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </main>

        @include('partials.liquid-footer')
    </div>

    <script>
        document.getElementById('clearTicketBtn')?.addEventListener('click', () => {
            const input = document.getElementById('ticket-input');
            if (input) {
                input.value = '';
                input.focus();
            }
        });

        document.getElementById('trackTicketForm')?.addEventListener('submit', () => {
            const btn = document.getElementById('trackSubmitBtn');
            const label = document.getElementById('trackSubmitLabel');
            if (btn && label) {
                label.textContent = 'Memeriksa...';
            }
        });

        document.getElementById('copy-ticket-btn')?.addEventListener('click', () => {
            const code = document.getElementById('ticket-number-display')?.textContent?.trim();
            const feedback = document.getElementById('copy-feedback');
            if (!code) return;
            navigator.clipboard?.writeText(code).catch(() => {});
            if (feedback) {
                feedback.textContent = 'Tersalin!';
                setTimeout(() => {
                    feedback.textContent = 'Salin';
                }, 2000);
            }
        });
    </script>
</body>
</html>
