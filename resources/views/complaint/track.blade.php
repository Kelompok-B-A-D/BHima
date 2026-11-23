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
<body class="stitch-liquid-body has-mobile-tabbar min-h-screen overflow-x-hidden bg-[#E9EEF5] font-sans text-[#0B1020] antialiased selection:bg-[#E6F0FC] selection:text-[#0B1020]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-full focus:bg-[#0066CC] focus:text-white focus:font-bold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <div class="w-full overflow-x-hidden">
        @include('partials.liquid-header', ['activeNav' => 'lacak-tiket'])

        <main id="portal-main-content" class="w-full pt-24 min-[641px]:pt-28 pb-10 min-[641px]:pb-14 bg-transparent relative min-h-[calc(100vh-280px)] stitch-print-full">
            <div class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 space-y-6 min-[641px]:space-y-8">
                <!-- Screen Header & Context -->
                <section class="space-y-2.5 max-w-3xl">
                    <p class="text-xs uppercase tracking-[0.12em] font-bold text-[#005EB8]">
                        Pelayanan Pengaduan Masyarakat Mandiri
                    </p>
                    <h1 class="type-h1 text-[#0B1020]">
                        Lacak Status Pengaduan
                    </h1>
                    <p class="text-sm min-[641px]:text-base text-[#4A5366] leading-relaxed">
                        Masukkan nomor tiket untuk memantau perkembangan verifikasi dan disposisi laporan pengaduan Anda secara langsung, tanpa perlu membuat akun.
                    </p>
                </section>

                <!-- FLOATING CLEAR LIQUID GLASS TICKET SEARCH PILL -->
                <section class="stitch-no-print" aria-label="Formulir Pencarian Nomor Tiket">
                    <form
                        id="trackTicketForm"
                        method="POST"
                        action="{{ route('complaint.track.submit') }}"
                        class="liquid-glass liquid-glass--strong p-3 min-[641px]:p-3.5 rounded-[28px] min-[641px]:rounded-full flex flex-col min-[641px]:flex-row items-stretch min-[641px]:items-center gap-2.5"
                    >
                        @csrf
                        <div class="glass-input min-[641px]:!border-0 min-[641px]:!bg-transparent min-[641px]:!shadow-none flex items-center gap-3 px-3.5 min-[641px]:pl-4 min-[641px]:pr-2 flex-1 min-w-0 py-2 min-[641px]:py-0">
                            <span class="material-symbols-outlined text-[#0066CC] text-[24px] shrink-0" aria-hidden="true">confirmation_number</span>
                            <div class="flex flex-col flex-1 min-w-0">
                                <label for="ticket-input" class="text-[11px] text-[#4A5366] uppercase tracking-wider font-bold leading-tight">
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
                                    class="w-full min-h-[38px] bg-transparent text-sm min-[641px]:text-base font-bold text-[#0B1020] placeholder:text-[#4A5366] focus:outline-none uppercase font-mono"
                                >
                            </div>
                            <button
                                type="button"
                                id="clearTicketBtn"
                                aria-label="Bersihkan nomor tiket"
                                title="Bersihkan kolom tiket"
                                class="press-scale w-11 h-11 rounded-full hover:bg-white/80 flex items-center justify-center text-[#4A5366] hover:text-[#0B1020] transition-colors shrink-0"
                            >
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                            </button>
                        </div>

                        <button
                            id="trackSubmitBtn"
                            type="submit"
                            class="btn-primary w-full min-[641px]:w-auto px-7 text-sm inline-flex items-center justify-center gap-2 shrink-0"
                        >
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_search</span>
                            <span id="trackSubmitLabel">Lacak Status</span>
                        </button>
                    </form>
                </section>

                <!-- Flash Success Message -->
                @if (session('completeness_submitted'))
                    <div role="status" class="rounded-[20px] bg-[#DCFCE7] border border-[#BBF7D0] text-[#166534] p-4 min-[641px]:p-5 flex items-start gap-3">
                        <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">check_circle</span>
                        <div class="space-y-1 text-xs min-[641px]:text-sm">
                            <strong class="font-bold block">Kelengkapan Berkas Diterima</strong>
                            <p>{{ session('completeness_submitted') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Server Validation Errors -->
                @if ($errors->any())
                    <div role="alert" class="rounded-[20px] bg-[#FEE2E2] border border-[#FCA5A5] text-[#991B1B] p-4 min-[641px]:p-5 flex items-start gap-3">
                        <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">error</span>
                        <div class="space-y-1 text-xs min-[641px]:text-sm">
                            @if ($errors->has('tiket'))
                                <strong class="font-bold block">Format Nomor Tiket Belum Sesuai</strong>
                                <p>{{ $errors->first('tiket') }} Pastikan format tiket diawali <span class="font-mono font-bold">BHM-</span> (contoh: <span class="font-mono font-bold">BHM-2026-0001</span>).</p>
                            @else
                                <strong class="font-bold block">Pengiriman Kelengkapan Belum Sesuai</strong>
                                <ul class="list-disc pl-5 space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
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
                                'Ditolak' => 'bg-[#FEE2E2] text-[#991B1B] border-[#FCA5A5]',
                                'Perlu Dilengkapi' => 'bg-[#FEF3C7] text-[#92400E] border-[#F5D07A]',
                                'Selesai' => 'bg-[#DCFCE7] text-[#166534] border-[#BBF7D0]',
                                'Diproses' => 'bg-[#E6F0FC] text-[#005EB8] border-[#BFD7F5]',
                                default => 'bg-[#E6F5F3] text-[#0F766E] border-[#BFE6DF]',
                            };
                        @endphp

                        <!-- Main Asymmetric Bento Result -->
                        <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 min-[1008px]:gap-7 items-start">
                            <!-- LEFT COLUMN: Status Card & Vertical Progression (7 cols) -->
                            <div class="min-[1008px]:col-span-7 flex flex-col space-y-6">
                                <!-- Status Overview Surface -->
                                <div class="surface-card p-6 min-[641px]:p-7 space-y-6">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <span class="text-xs text-[#4A5366] uppercase tracking-wider font-bold block">ID Berkas Resmi</span>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xl min-[641px]:text-2xl font-extrabold text-[#005EB8] font-mono tracking-wide" id="ticket-number-display">{{ $result['ticket'] }}</span>
                                                <span class="material-symbols-outlined text-[#0F766E] text-[20px]" title="Tiket Terdaftar" aria-hidden="true">verified</span>
                                                <button
                                                    type="button"
                                                    id="copy-ticket-btn"
                                                    aria-label="Salin nomor tiket"
                                                    class="stitch-no-print btn-glass !min-h-[44px] px-3.5 text-xs inline-flex items-center gap-1.5"
                                                >
                                                    <span class="material-symbols-outlined text-[16px] text-[#0066CC]" aria-hidden="true">content_copy</span>
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
                                    <dl class="surface-nested grid grid-cols-1 min-[481px]:grid-cols-3 gap-4 p-4">
                                        <div class="space-y-1">
                                            <dt class="text-[11px] text-[#4A5366] uppercase tracking-wider font-bold">Tanggal Masuk</dt>
                                            <dd class="text-sm text-[#0B1020] font-bold">{{ $result['date'] ?? '-' }}</dd>
                                        </div>
                                        <div class="space-y-1">
                                            <dt class="text-[11px] text-[#4A5366] uppercase tracking-wider font-bold">Klasifikasi Kasus</dt>
                                            <dd class="text-sm text-[#0B1020] font-bold">{{ $result['category'] ?? 'Pengaduan Publik' }}</dd>
                                        </div>
                                        <div class="space-y-1">
                                            <dt class="text-[11px] text-[#4A5366] uppercase tracking-wider font-bold">Saluran Pelacakan</dt>
                                            <dd class="text-sm text-[#0F766E] font-bold">Tanpa Akun (Publik)</dd>
                                        </div>
                                    </dl>
                                </div>

                                @if ($needsFix)
                                    <div class="rounded-[20px] bg-[#FEF3C7] border border-[#F5D07A] text-[#92400E] p-5 min-[641px]:p-6 space-y-4">
                                        <div class="flex items-start gap-3">
                                            <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">warning</span>
                                            <div class="text-xs min-[641px]:text-sm space-y-2 min-w-0 flex-1">
                                                <strong class="font-bold block text-[#78350F]">Perlu Dilengkapi</strong>
                                                <p class="leading-relaxed">
                                                    {{ $result['request_note'] ?: 'Petugas verifikator meminta kelengkapan berkas tambahan untuk menindaklanjuti laporan ini.' }}
                                                </p>
                                                @if (! empty($result['requested_documents']))
                                                    <div class="pt-1 space-y-1">
                                                        <span class="text-xs font-bold uppercase tracking-wider text-[#78350F] block">Dokumen yang Diminta</span>
                                                        <ul class="list-disc pl-5 space-y-1 text-[#78350F] font-medium">
                                                            @foreach ($result['requested_documents'] as $doc)
                                                                <li>{{ $doc }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <form
                                            method="POST"
                                            action="{{ route('complaint.completeness', $result['ticket']) }}"
                                            enctype="multipart/form-data"
                                            class="stitch-no-print rounded-[16px] bg-white border border-[#F5D07A] p-4 min-[641px]:p-5 space-y-4 text-[#0B1020]"
                                        >
                                            @csrf
                                            <div class="space-y-1.5">
                                                <label for="completeness-note" class="block text-xs font-bold uppercase tracking-wider text-[#333E52]">
                                                    Catatan Kelengkapan
                                                </label>
                                                <textarea
                                                    id="completeness-note"
                                                    name="catatan"
                                                    rows="3"
                                                    required
                                                    minlength="5"
                                                    maxlength="2000"
                                                    placeholder="Jelaskan berkas tambahan yang Anda lampirkan..."
                                                    class="w-full rounded-[14px] border border-[#CBD5E1] bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-[#0B1020] placeholder:text-[#4A5366] focus:border-[#0066CC] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0066CC]/20"
                                                >{{ old('catatan') }}</textarea>
                                            </div>

                                            <div class="space-y-1.5">
                                                <label for="completeness-files" class="block text-xs font-bold uppercase tracking-wider text-[#333E52]">
                                                    Unggah Berkas Tambahan (PDF, JPG, PNG; maks. 5 berkas, 10 MB per berkas)
                                                </label>
                                                <input
                                                    id="completeness-files"
                                                    type="file"
                                                    name="lampiran[]"
                                                    multiple
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    required
                                                    class="w-full min-h-[44px] rounded-[14px] border border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2 text-xs min-[641px]:text-sm text-[#0B1020] file:mr-3 file:rounded-full file:border-0 file:bg-[#E6F0FC] file:px-3.5 file:py-1.5 file:text-xs file:font-bold file:text-[#005EB8] hover:file:bg-[#D5E6FA]"
                                                >
                                            </div>

                                            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                                                <p class="text-xs text-[#4A5366]">
                                                    Berkas disimpan secara privat dan hanya dapat diperiksa petugas berwenang.
                                                </p>
                                                <button
                                                    type="submit"
                                                    class="btn-primary !min-h-[44px] px-6 text-xs min-[641px]:text-sm inline-flex items-center justify-center gap-2"
                                                >
                                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">upload_file</span>
                                                    <span>Kirim Kelengkapan</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                @if ($rejected)
                                    <div class="rounded-[20px] bg-[#FEE2E2] border border-[#FCA5A5] text-[#991B1B] p-5 space-y-3">
                                        <div class="flex items-start gap-3">
                                            <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">cancel</span>
                                            <div class="text-xs min-[641px]:text-sm space-y-2 min-w-0 flex-1">
                                                <strong class="font-bold block">Laporan Ditolak</strong>
                                                <p>Laporan berada di luar kewenangan layanan atau belum memenuhi syarat administrasi. Anda dapat mengajukan laporan baru dengan data yang sesuai.</p>
                                                @if (! empty($result['rejection_reason']))
                                                    <div class="rounded-[14px] bg-white border border-[#FCA5A5] p-3.5 text-[#7F1D1D] space-y-1">
                                                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#991B1B] block">Alasan Penolakan</span>
                                                        <p class="whitespace-pre-line leading-relaxed font-medium">{{ $result['rejection_reason'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('complaint.create') }}" class="press-scale min-h-[44px] px-5 rounded-full bg-[#991B1B] hover:bg-[#7F1D1D] text-white text-xs font-bold inline-flex items-center gap-2 transition-colors">
                                            <span>Ajukan Laporan Baru</span>
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_forward</span>
                                        </a>
                                    </div>
                                @else
                                    <!-- Vertical SOP Timeline Card -->
                                    <div class="surface-card p-6 min-[641px]:p-7 space-y-6">
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <div class="flex items-center gap-2.5">
                                                <span class="material-symbols-outlined text-[#0066CC] text-[24px]" aria-hidden="true">timeline</span>
                                                <h2 class="type-h3 text-[#0B1020]">Tahapan Penanganan</h2>
                                            </div>
                                            <span class="text-xs px-3 py-1 rounded-full bg-[#F4F7FC] text-[#4A5366] font-bold border border-[#DCE4F0]">
                                                Tahap {{ $idx + 1 }} dari {{ count($stages) }}
                                            </span>
                                        </div>

                                        <ol class="space-y-3.5">
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
                                                <li class="flex items-start gap-3.5 p-4 rounded-[16px] {{ $isCurrent ? 'bg-[#E6F0FC] border border-[#BFD7F5]' : ($isDone ? 'bg-[#F4F7FC] border border-[#DCE4F0]' : 'bg-white border border-[#DCE4F0]') }}">
                                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $isDone ? 'bg-[#0F766E] text-white' : ($isCurrent ? 'bg-[#0066CC] text-white' : 'bg-[#E9EEF5] text-[#4A5366]') }}">
                                                        @if ($isDone)
                                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">check</span>
                                                        @else
                                                            {{ $i + 1 }}
                                                        @endif
                                                    </span>
                                                    <div class="min-w-0 flex-1 space-y-0.5">
                                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                                            <span class="text-sm font-bold {{ $isCurrent ? 'text-[#005EB8]' : 'text-[#0B1020]' }}">{{ $stage }}</span>
                                                            @if ($isCurrent)
                                                                <span class="px-2.5 py-0.5 rounded-full bg-[#0066CC] text-white text-[10px] font-bold uppercase tracking-wider">Aktif</span>
                                                            @elseif ($isDone)
                                                                <span class="text-xs font-bold text-[#0F766E]">Selesai</span>
                                                            @endif
                                                        </div>
                                                        @if ($stageDesc !== '')
                                                            <p class="text-xs text-[#4A5366] leading-relaxed">{{ $stageDesc }}</p>
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
                                <div class="surface-card p-6 min-[641px]:p-7 space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-[16px] bg-[#E6F0FC] flex items-center justify-center text-[#005EB8] shrink-0">
                                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">corporate_fare</span>
                                        </div>
                                        <div>
                                            <span class="text-[11px] text-[#4A5366] uppercase tracking-wider font-bold block">Instansi Tujuan Disposisi</span>
                                            <h2 class="text-base min-[641px]:text-lg font-bold text-[#0B1020] leading-snug">
                                                {{ $result['agency'] ?: 'Dalam Antrean Verifikasi & Disposisi' }}
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="surface-nested p-4 flex items-start gap-2.5">
                                        <span class="material-symbols-outlined text-[#0066CC] text-[19px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                                        <p class="text-xs text-[#4A5366] leading-relaxed">
                                            {{ $result['agency'] ? 'Laporan telah diteruskan ke instansi di atas untuk pemeriksaan dan klarifikasi lebih lanjut.' : 'Setelah verifikator menyelesaikan pemeriksaan berkas awal, instansi tujuan disposisi akan ditampilkan di sini.' }}
                                        </p>
                                    </div>
                                    @if (! empty($result['dispositions']))
                                        <div class="space-y-2.5 pt-1">
                                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#4A5366]">Riwayat Disposisi</h3>
                                            <ul class="space-y-2">
                                                @foreach ($result['dispositions'] as $disposition)
                                                    <li class="surface-nested flex flex-col min-[481px]:flex-row min-[481px]:items-center justify-between gap-1 p-3">
                                                        <span class="text-xs min-[641px]:text-sm font-bold text-[#0B1020]">{{ $disposition['agency'] }}</span>
                                                        <span class="text-xs font-semibold text-[#4A5366]">{{ $disposition['date'] ?? '-' }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                @if (! empty($result['follow_up']['summary']))
                                    <!-- Hasil Tindak Lanjut -->
                                    <div class="surface-card p-6 min-[641px]:p-7 space-y-4">
                                        <div class="flex items-start justify-between gap-3 flex-wrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-11 h-11 rounded-[16px] bg-[#DCFCE7] flex items-center justify-center text-[#166534] shrink-0">
                                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">assignment_turned_in</span>
                                                </div>
                                                <div>
                                                    <span class="text-[11px] text-[#4A5366] uppercase tracking-wider font-bold block">Ringkasan Penanganan</span>
                                                    <h2 class="text-base min-[641px]:text-lg font-bold text-[#0B1020] leading-snug">Hasil Tindak Lanjut</h2>
                                                </div>
                                            </div>
                                            @if (! empty($result['follow_up']['updated_at']))
                                                <span class="text-xs font-semibold text-[#4A5366]">{{ $result['follow_up']['updated_at'] }}</span>
                                            @endif
                                        </div>
                                        <div class="surface-nested p-4">
                                            <p class="whitespace-pre-line text-xs min-[641px]:text-sm text-[#0B1020] leading-relaxed">{{ $result['follow_up']['summary'] }}</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Riwayat Penanganan Log -->
                                <div class="surface-card p-6 min-[641px]:p-7 space-y-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <h2 class="text-base min-[641px]:text-lg font-bold text-[#0B1020]">Riwayat Penanganan</h2>
                                        <span class="text-xs font-bold text-[#0F766E]">{{ count($result['history'] ?? []) }} Catatan</span>
                                    </div>
                                    @if (!empty($result['history']))
                                        <ul class="space-y-3">
                                            @foreach ($result['history'] as $h)
                                                <li class="surface-nested flex flex-col min-[481px]:flex-row min-[481px]:items-center justify-between gap-1.5 p-3.5">
                                                    <span class="text-xs min-[641px]:text-sm font-bold text-[#0B1020]">{{ $h['status'] }}</span>
                                                    <span class="text-xs font-semibold text-[#4A5366]">{{ $h['date'] ?? '-' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="surface-nested text-xs text-[#4A5366] italic p-3.5">
                                            Belum ada catatan perubahan status tambahan.
                                        </p>
                                    @endif
                                </div>

                                <!-- Bantuan & Cetak Status (Zero Gradient) -->
                                <div class="stitch-no-print surface-card relative overflow-hidden p-6 space-y-3">
                                    <span class="absolute inset-x-0 top-0 h-1 bg-[#0066CC]" aria-hidden="true"></span>
                                    <h3 class="text-base font-bold text-[#0B1020]">Butuh Penjelasan Dasar Hukum?</h3>
                                    <p class="text-xs text-[#4A5366] leading-relaxed">
                                        Konsultasikan peraturan JDIH dan SOP layanan terkait laporan Anda kepada Asisten Hukum Virtual BHIMA, atau cetak bukti status ini.
                                    </p>
                                    <div class="flex flex-col min-[481px]:flex-row gap-2.5 pt-1">
                                        <a href="{{ route('consultation.index') }}" class="btn-primary !min-h-[44px] flex-1 px-4 text-xs inline-flex items-center justify-center gap-1.5">
                                            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">smart_toy</span>
                                            <span>Tanya Asisten</span>
                                        </a>
                                        <button type="button" onclick="window.print()" class="btn-glass !min-h-[44px] px-4 text-xs inline-flex items-center justify-center gap-1.5">
                                            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">print</span>
                                            <span>Cetak</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Service Evaluation Banner -->
                        <div class="surface-card p-6 min-[641px]:p-7 flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-4">
                            <div class="space-y-1.5 max-w-2xl">
                                <div class="flex items-center gap-2 text-[#0B1020]">
                                    <span class="material-symbols-outlined text-[22px] text-[#0066CC]" aria-hidden="true">rate_review</span>
                                    <h2 class="text-base min-[641px]:text-lg font-bold">Evaluasi Kepuasan Pelayanan Publik</h2>
                                </div>
                                <p class="text-xs min-[641px]:text-sm text-[#4A5366] leading-relaxed">
                                    @if ($current === 'Selesai')
                                        Laporan telah selesai ditangani. Formulir evaluasi kepuasan akan tersedia di sini.
                                    @else
                                        <strong class="text-[#0B1020]">Evaluasi kepuasan</strong> dapat diisi setelah status penanganan laporan dinyatakan <strong class="text-[#0B1020]">Selesai</strong>.
                                    @endif
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ $current === 'Selesai' ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#F4F7FC] text-[#4A5366] border border-[#DCE4F0]' }} text-xs font-bold shrink-0 self-start min-[641px]:self-center">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">{{ $current === 'Selesai' ? 'check_circle' : 'lock' }}</span>
                                <span>{{ $current === 'Selesai' ? 'Penanganan Selesai' : 'Terbuka Setelah Selesai' }}</span>
                            </span>
                        </div>
                    @else
                        <!-- Ticket Not Found State -->
                        <div class="rounded-[28px] bg-[#FEF3C7] border border-[#F5D07A] p-6 min-[641px]:p-8 space-y-4">
                            <div class="flex items-start gap-3.5 text-[#92400E]">
                                <span class="material-symbols-outlined text-[28px] shrink-0" aria-hidden="true">search_off</span>
                                <div class="space-y-1.5">
                                    <h2 class="text-lg min-[641px]:text-xl font-bold text-[#0B1020]">Tiket Tidak Ditemukan</h2>
                                    <p class="text-xs min-[641px]:text-sm leading-relaxed text-[#78350F]">
                                        Tiket <strong class="font-mono text-[#0B1020]">{{ $searchedTicket ?: 'yang dimasukkan' }}</strong> tidak ditemukan di basis data pengaduan BHIMA. Periksa kembali penulisan huruf, tahun, dan angka pada kode tiket Anda.
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    onclick="document.getElementById('ticket-input')?.focus()"
                                    class="btn-glass !min-h-[44px] px-5 text-xs min-[641px]:text-sm inline-flex items-center gap-1.5"
                                >
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit</span>
                                    <span>Periksa Ulang Nomor Tiket</span>
                                </button>
                                <a
                                    href="{{ route('complaint.create') }}"
                                    class="btn-primary !min-h-[44px] px-5 text-xs min-[641px]:text-sm inline-flex items-center gap-1.5"
                                >
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                                    <span>Buat Pengaduan Baru</span>
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Initial Empty Guidance State (Asymmetric 5 + 7 cols) -->
                    <div class="grid grid-cols-1 min-[641px]:grid-cols-12 gap-6">
                        <div class="surface-card min-[641px]:col-span-5 p-6 min-[641px]:p-7 space-y-3">
                            <div class="w-11 h-11 rounded-[16px] bg-[#E6F0FC] text-[#005EB8] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">receipt_long</span>
                            </div>
                            <h2 class="type-h3 text-[#0B1020]">Cara Melihat Perkembangan Tiket</h2>
                            <p class="text-sm text-[#4A5366] leading-relaxed">
                                Masukkan nomor tiket yang Anda peroleh setelah mengirim laporan melalui Formulir Pengaduan, misalnya dengan pola <span class="font-mono font-bold text-[#005EB8]">BHM-2026-0001</span>.
                            </p>
                        </div>

                        <div class="surface-card min-[641px]:col-span-7 p-6 min-[641px]:p-7 flex flex-col justify-between gap-5">
                            <div class="space-y-3">
                                <div class="w-11 h-11 rounded-[16px] bg-[#E6F5F3] text-[#0F766E] flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">campaign</span>
                                </div>
                                <h2 class="type-h3 text-[#0B1020]">Belum Mengirim Pengaduan?</h2>
                                <p class="text-sm text-[#4A5366] leading-relaxed">
                                    Anda dapat berkonsultasi terlebih dahulu melalui Asisten Hukum atau langsung menyampaikan aduan layanan publik secara aman dan anonim.
                                </p>
                            </div>
                            <div class="flex flex-col min-[481px]:flex-row flex-wrap gap-3">
                                <a href="{{ route('complaint.create') }}" class="btn-primary px-6 text-sm inline-flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                                    <span>Buat Pengaduan</span>
                                </a>
                                <a href="{{ route('consultation.index') }}" class="btn-glass px-6 text-sm inline-flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">smart_toy</span>
                                    <span>Tanya Asisten</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </main>

        @include('partials.liquid-footer')
        @include('partials.liquid-bottom-nav', ['activeNav' => 'lacak-tiket'])
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
