<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shell-type" content="web_standard">
    <title>BHIMA - Formulir Pengaduan Masyarakat</title>
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

    @php
        $initialStep = 1;
        if ($errors->hasAny(['nama_pelapor', 'kontak'])) {
            $initialStep = 1;
        } elseif ($errors->hasAny(['persetujuan', 'lampiran', 'lampiran.*'])) {
            $initialStep = 3;
        } elseif ($errors->hasAny(['kategori', 'instansi_terlapor', 'tanggal_kejadian', 'lokasi', 'uraian']) || !empty($prefill)) {
            $initialStep = 2;
        }
    @endphp

    <div class="w-full overflow-x-hidden">
        @include('partials.liquid-header', ['activeNav' => 'formulir-pengaduan'])

        <main id="portal-main-content" class="w-full pt-24 min-[641px]:pt-28 pb-10 min-[641px]:pb-14 bg-transparent relative min-h-[calc(100vh-280px)] stitch-print-full">
            <div class="max-w-[1200px] mx-auto w-full px-3 min-[481px]:px-4 min-[641px]:px-6 space-y-6">
                <!-- Top Context & Breadcrumb Bar -->
                <div class="stitch-no-print flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 flex-wrap text-xs font-semibold text-[#414754]">
                        <a
                            href="{{ route('consultation.home') }}"
                            aria-label="Kembali ke Beranda Portal BHIMA"
                            class="w-11 h-11 rounded-full bg-white/90 border border-white shadow-sm flex items-center justify-center text-[#171C21] hover:bg-[#D6E3FF] hover:text-[#00468D] transition-colors shrink-0"
                        >
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_back</span>
                        </a>
                        <a class="min-h-[44px] inline-flex items-center hover:text-[#005AB3] transition-colors" href="{{ route('consultation.home') }}">Beranda</a>
                        <span class="material-symbols-outlined text-[16px] text-[#717786]" aria-hidden="true">chevron_right</span>
                        <a class="min-h-[44px] inline-flex items-center hover:text-[#005AB3] transition-colors" href="{{ route('consultation.home') }}#layanan">Layanan Publik</a>
                        <span class="material-symbols-outlined text-[16px] text-[#717786]" aria-hidden="true">chevron_right</span>
                        <span class="text-[#005AB3] font-bold">Formulir Pengaduan Masyarakat</span>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/90 border border-[#DEE3EA] text-xs font-bold text-[#0F766E] self-start min-[641px]:self-auto">
                        <span class="w-2 h-2 rounded-full bg-[#0F766E] shrink-0" aria-hidden="true"></span>
                        <span>Opsi Anonim &amp; Pelindungan Pelapor Aktif</span>
                    </div>
                </div>

                <!-- Two-Column Asymmetric Layout (7 + 5 Columns on >= 1008px) -->
                <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 min-[1008px]:gap-8 items-start">
                    <!-- LEFT PRIMARY COLUMN: Header, Prefill Summary & 3-Step Glass Form -->
                    <section class="min-[1008px]:col-span-7 flex flex-col gap-5 min-[641px]:gap-6" aria-label="Formulir Pengaduan Masyarakat">
                        <!-- Intro Header Card -->
                        <div class="rounded-[28px] p-5 min-[641px]:p-7 bg-white/92 border border-white shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] space-y-3">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1.5">
                                    <span class="inline-block px-3 py-1 rounded-full bg-[#D6E3FF] text-[#00468D] text-[11px] uppercase tracking-wider font-bold">
                                        Formulir Pengaduan Pelayanan Publik
                                    </span>
                                    <h1 class="text-xl min-[380px]:text-2xl min-[641px]:text-[30px] min-[641px]:leading-[1.18] text-[#171C21] font-extrabold tracking-tight">
                                        Formulir Pengaduan Masyarakat &amp; Maladministrasi
                                    </h1>
                                </div>
                                <div class="w-11 h-11 min-[641px]:w-12 min-[641px]:h-12 rounded-2xl bg-[#005AB3]/10 flex items-center justify-center text-[#005AB3] shrink-0">
                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">gavel</span>
                                </div>
                            </div>
                            <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                Sampaikan laporan dugaan pungutan liar, pelayanan lambat, atau ketidakjelasan prosedur loket desa/kelurahan. Setiap laporan tercatat resmi dan ditelaah oleh verifikator BHIMA.
                            </p>
                        </div>

                        <!-- Pre-filled Consultation Summary Card -->
                        @if ($prefill)
                            <div class="rounded-[28px] p-5 min-[641px]:p-6 bg-[#EFF4FB]/95 border border-white shadow-[0_12px_28px_rgba(20,30,80,0.06)] space-y-3.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-[#005AB3] text-white flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">smart_toy</span>
                                        </div>
                                        <div>
                                            <h2 class="text-sm min-[641px]:text-base font-bold text-[#171C21]">Hasil Konsultasi Asisten Hukum</h2>
                                            <p class="text-xs text-[#414754]">Data awal otomatis terisi dari sesi konsultasi Anda (dapat diedit)</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-[#9CF2E8] text-[#00504A] text-xs font-bold inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check_circle</span>
                                        <span>Siap Diteruskan</span>
                                    </span>
                                </div>

                                @if (!empty($prefill['chronology']))
                                    <div class="p-3.5 rounded-2xl bg-white border border-[#DEE3EA] space-y-1">
                                        <span class="text-[11px] uppercase tracking-wider font-bold text-[#0F766E] block">Ringkasan Kronologi Sesi:</span>
                                        <blockquote class="text-xs min-[641px]:text-sm text-[#171C21] italic leading-relaxed">
                                            "{{ $prefill['chronology'] }}"
                                        </blockquote>
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#414754] block">Kajian Pasal &amp; Regulasi yang Terindikasi:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($prefill['citations'] as $c)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-[#DEE3EA] text-[#00468D] text-xs font-semibold shadow-sm">
                                                <span class="material-symbols-outlined text-[15px] text-[#005AB3]" aria-hidden="true">menu_book</span>
                                                <span>{{ data_get($c, 'document_name', 'Rujukan') }} ({{ data_get($c, 'document_number', '-') }}) {{ data_get($c, 'article_label') }}</span>
                                            </span>
                                        @empty
                                            <span class="text-xs text-[#414754]">Belum ada rujukan pasal pada sesi ini.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl p-4 min-[641px]:p-5 bg-white/90 border border-[#DEE3EA] text-xs min-[641px]:text-sm text-[#414754] flex items-start gap-3">
                                <span class="material-symbols-outlined text-[#005AB3] text-[20px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                                <p class="leading-relaxed">
                                    Belum terhubung dengan hasil konsultasi sebelumnya. Anda dapat <a class="text-[#005AB3] font-bold underline" href="{{ route('consultation.index') }}">bertanya ke Asisten Hukum</a> terlebih dahulu atau langsung mengisi formulir di bawah ini.
                                </p>
                            </div>
                        @endif

                        <!-- Server Validation Errors -->
                        @if ($errors->any())
                            <div role="alert" class="rounded-2xl bg-[#FFDAD6] border border-[#BA1A1A]/30 text-[#93000A] p-4 min-[641px]:p-5 space-y-1.5 text-xs min-[641px]:text-sm">
                                <div class="flex items-center gap-2 font-bold">
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">error</span>
                                    <span>Mohon periksa kembali isian formulir Anda:</span>
                                </div>
                                <ul class="list-disc pl-5 space-y-0.5 font-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Client-side Validation Error Banner -->
                        <div id="clientFormError" hidden role="alert" class="rounded-2xl bg-[#FFDAD6] border border-[#BA1A1A]/30 text-[#93000A] p-4 text-xs min-[641px]:text-sm font-bold flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-[20px] shrink-0" aria-hidden="true">error</span>
                            <span id="clientFormErrorText"></span>
                        </div>

                        <!-- MAIN LIQUID GLASS FORM (Focal Blur Element #2) -->
                        <form
                            id="complaintForm"
                            method="POST"
                            action="{{ route('complaint.store') }}"
                            enctype="multipart/form-data"
                            novalidate
                            data-initial-step="{{ $initialStep }}"
                            class="rounded-[28px] p-5 min-[641px]:p-7 bg-white/85 backdrop-blur-2xl border border-white shadow-[0_20px_48px_rgba(20,30,80,0.08),inset_0_1px_1px_rgba(255,255,255,0.9)] flex flex-col gap-6"
                        >
                            @csrf
                            <input type="hidden" name="session_uuid" value="{{ $prefill['session_uuid'] ?? '' }}">

                            <!-- 3-Step Segmented Tabs Bar (Full width stacked on tiny phone, 3 cols on >=481px) -->
                            <div class="grid grid-cols-1 min-[481px]:grid-cols-3 gap-2 pb-4 border-b border-[#DEE3EA]" role="tablist" aria-label="Tahapan Pengisian Formulir">
                                <button
                                    type="button"
                                    id="tab-step-1"
                                    role="tab"
                                    aria-selected="{{ $initialStep === 1 ? 'true' : 'false' }}"
                                    aria-controls="step-content-1"
                                    class="min-h-[44px] px-3.5 py-2 rounded-full text-xs font-bold flex items-center justify-center min-[481px]:justify-start gap-2 transition-colors"
                                >
                                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0">1</span>
                                    <span>Identitas &amp; Privasi</span>
                                </button>
                                <button
                                    type="button"
                                    id="tab-step-2"
                                    role="tab"
                                    aria-selected="{{ $initialStep === 2 ? 'true' : 'false' }}"
                                    aria-controls="step-content-2"
                                    class="min-h-[44px] px-3.5 py-2 rounded-full text-xs font-bold flex items-center justify-center min-[481px]:justify-start gap-2 transition-colors"
                                >
                                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0">2</span>
                                    <span>Rincian Masalah</span>
                                </button>
                                <button
                                    type="button"
                                    id="tab-step-3"
                                    role="tab"
                                    aria-selected="{{ $initialStep === 3 ? 'true' : 'false' }}"
                                    aria-controls="step-content-3"
                                    class="min-h-[44px] px-3.5 py-2 rounded-full text-xs font-bold flex items-center justify-center min-[481px]:justify-start gap-2 transition-colors"
                                >
                                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0">3</span>
                                    <span>Bukti &amp; Kirim</span>
                                </button>
                            </div>

                            <!-- STEP 1: IDENTITAS & KERAHASIAAN -->
                            <div id="step-content-1" role="tabpanel" aria-labelledby="tab-step-1" class="step-section flex flex-col gap-5">
                                <div class="p-4 rounded-2xl bg-[#EFF4FB]/90 border border-[#DEE3EA] flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-4">
                                    <div class="flex items-start min-[641px]:items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0">
                                            <span id="anonIcon" class="material-symbols-outlined text-[20px] text-[#005AB3]" aria-hidden="true">badge</span>
                                        </div>
                                        <div>
                                            <span id="anonTitle" class="text-sm font-bold text-[#171C21] block">Data Terbuka (Dengan Identitas Pelapor)</span>
                                            <span id="anonDesc" class="text-xs text-[#414754]">Pilih mode Anonim apabila Anda tidak ingin identitas diketahui pihak terlapor.</span>
                                        </div>
                                    </div>

                                    <!-- Accessible Segmented Mode Switch -->
                                    <div class="inline-flex p-1 bg-[#E4E9F0] rounded-full border border-[#C0C6D6]/50 shrink-0 self-start min-[641px]:self-center">
                                        <button
                                            type="button"
                                            id="btnAnonMode"
                                            class="min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors"
                                        >
                                            Anonim
                                        </button>
                                        <button
                                            type="button"
                                            id="btnPublicMode"
                                            class="min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors"
                                        >
                                            Data Terbuka
                                        </button>
                                    </div>
                                    <input class="sr-only" id="anonToggle" name="is_anonim" value="1" type="checkbox" @checked(old('is_anonim'))>
                                </div>

                                <div id="identityFields" class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-[#171C21]" for="reporterName">
                                            Nama Pelapor <span class="text-[#93000A]">*</span>
                                        </label>
                                        <input
                                            class="w-full min-h-[46px] px-4 rounded-2xl bg-[#EFF4FB] border border-[#C0C6D6] text-sm text-[#171C21] placeholder:text-[#414754]/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#005AB3]"
                                            id="reporterName"
                                            name="nama_pelapor"
                                            type="text"
                                            placeholder="Nama lengkap pelapor"
                                            value="{{ old('nama_pelapor') }}"
                                        >
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-[#171C21]" for="reporterContact">
                                            Kontak (Opsional)
                                        </label>
                                        <input
                                            class="w-full min-h-[46px] px-4 rounded-2xl bg-[#EFF4FB] border border-[#C0C6D6] text-sm text-[#171C21] placeholder:text-[#414754]/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#005AB3]"
                                            id="reporterContact"
                                            name="kontak"
                                            type="text"
                                            placeholder="Nomor WhatsApp atau email"
                                            value="{{ old('kontak') }}"
                                        >
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button
                                        type="button"
                                        data-next-step="2"
                                        class="w-full min-[481px]:w-auto min-h-[46px] px-6 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white text-xs min-[641px]:text-sm font-bold flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <span>Lanjut: Rincian Masalah</span>
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 2: RINCIAN MALADMINISTRASI & KRONOLOGI -->
                            <div id="step-content-2" role="tabpanel" aria-labelledby="tab-step-2" class="step-section hidden flex-col gap-5">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#171C21]" for="categorySelect">
                                        Kategori Pokok Pengaduan <span class="text-[#93000A]">*</span>
                                    </label>
                                    <div class="relative">
                                        <select
                                            class="w-full min-h-[46px] px-4 pr-10 rounded-2xl bg-[#EFF4FB] border border-[#C0C6D6] text-sm text-[#171C21] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#005AB3] appearance-none"
                                            id="categorySelect"
                                            name="kategori"
                                            required
                                        >
                                            <option value="">Pilih kategori pengaduan</option>
                                            @foreach (($categories ?? ['Pungutan Liar', 'Dugaan Pungutan Liar', 'Pelayanan Lambat', 'Prosedur Tidak Jelas', 'Administrasi Kependudukan', 'Pelayanan Kesehatan', 'Infrastruktur', 'Ketenagakerjaan', 'Pertanahan', 'Maladministrasi Lainnya']) as $k)
                                                <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-[#414754]" aria-hidden="true">expand_more</span>
                                    </div>
                                    <p class="text-xs text-[#414754]">Pilih kategori pokok yang paling sesuai dengan kejadian Anda.</p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#171C21]" for="reportedEntity">
                                        Pihak / Instansi Terlapor <span class="text-[#93000A]">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#414754] text-[20px]" aria-hidden="true">domain</span>
                                        <input
                                            class="w-full min-h-[46px] pl-11 pr-4 rounded-2xl bg-[#EFF4FB] border border-[#C0C6D6] text-sm text-[#171C21] placeholder:text-[#414754]/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#005AB3]"
                                            id="reportedEntity"
                                            name="instansi_terlapor"
                                            required
                                            type="text"
                                            placeholder="Contoh: Loket Pelayanan Kependudukan Kecamatan"
                                            value="{{ old('instansi_terlapor') }}"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-[#171C21]" for="incidentDate">
                                            Tanggal Kejadian <span class="text-[#93000A]">*</span>
                                        </label>
                                        <input
                                            class="w-full min-h-[46px] px-4 rounded-2xl bg-[#EFF4FB] border border-[#C0C6D6] text-sm text-[#171C21] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#005AB3]"
                                            id="incidentDate"
                                            name="tanggal_kejadian"
                                            required
                                            type="date"
                                            value="{{ old('tanggal_kejadian') }}"
                                            max="{{ now()->toDateString() }}"
                                        >
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-[#171C21]" for="incidentLoc">
                                            Lokasi Rinci Kejadian <span class="text-[#93000A]">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#414754] text-[20px]" aria-hidden="true">location_on</span>
                                            <input
                                                class="w-full min-h-[46px] pl-11 pr-4 rounded-2xl bg-[#EFF4FB] border border-[#C0C6D6] text-sm text-[#171C21] placeholder:text-[#414754]/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#005AB3]"
                                                id="incidentLoc"
                                                name="lokasi"
                                                required
                                                type="text"
                                                placeholder="Nama kantor / wilayah kejadian"
                                                value="{{ old('lokasi') }}"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <label class="block text-xs font-bold text-[#171C21]" for="fullDescription">
                                            Uraian Lengkap Kejadian &amp; Fakta Lapangan <span class="text-[#93000A]">*</span>
                                        </label>
                                        <span class="text-[11px] font-bold text-[#005AB3]" id="charCount">Karakter: 0 / 2000 (Min. 20)</span>
                                    </div>
                                    <textarea
                                        class="w-full p-4 rounded-2xl bg-[#EFF4FB] border border-[#C0C6D6] text-xs min-[380px]:text-sm text-[#171C21] placeholder:text-[#414754]/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#005AB3] leading-relaxed resize-y"
                                        id="fullDescription"
                                        name="uraian"
                                        maxlength="2000"
                                        required
                                        rows="5"
                                        placeholder="Jelaskan kronologi waktu, tempat, tindakan petugas, atau hambatan layanan yang Anda alami (minimal 20 karakter)..."
                                    >{{ old('uraian', $prefill['chronology'] ?? '') }}</textarea>
                                </div>

                                <div class="flex flex-col min-[481px]:flex-row items-stretch min-[481px]:items-center justify-between gap-2.5 pt-2">
                                    <button
                                        type="button"
                                        data-prev-step="1"
                                        class="min-h-[44px] px-5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-[#171C21] text-xs font-bold flex items-center justify-center gap-1.5 border border-[#C0C6D6] transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                                        <span>Kembali ke Identitas</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-next-step="3"
                                        class="min-h-[46px] px-6 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white text-xs min-[641px]:text-sm font-bold flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <span>Lanjut: Bukti &amp; Kirim</span>
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 3: UNGGAH BUKTI & KIRIM -->
                            <div id="step-content-3" role="tabpanel" aria-labelledby="tab-step-3" class="step-section hidden flex-col gap-5">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <label for="fileInput" class="block text-xs font-bold text-[#171C21]">
                                            Unggah Berkas Bukti &amp; Dokumentasi (Opsional)
                                        </label>
                                        <span class="text-[11px] font-semibold text-[#414754]">Maks. 5 berkas @ 10 MB (PDF, JPG, PNG)</span>
                                    </div>

                                    <button
                                        type="button"
                                        id="uploadTriggerBtn"
                                        class="w-full p-6 rounded-2xl bg-[#EFF4FB]/70 hover:bg-[#EFF4FB] border-2 border-dashed border-[#C0C6D6] hover:border-[#005AB3] transition-all text-center flex flex-col items-center justify-center gap-2"
                                    >
                                        <div class="w-12 h-12 rounded-full bg-white text-[#005AB3] shadow-sm flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[26px]" aria-hidden="true">cloud_upload</span>
                                        </div>
                                        <span class="text-sm font-bold text-[#171C21]">Klik untuk memilih berkas bukti</span>
                                        <span class="text-xs text-[#414754]">Format didukung: PDF, JPG, PNG</span>
                                    </button>

                                    <input id="fileInput" name="lampiran[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                                    <ul id="fileList" class="space-y-2"></ul>
                                </div>

                                <div class="p-4 rounded-2xl bg-[#EFF4FB]/90 border border-[#DEE3EA]">
                                    <label class="flex items-start gap-3 cursor-pointer select-none" for="consentCheck">
                                        <input
                                            class="mt-0.5 w-5 h-5 rounded text-[#005AB3] focus:ring-[#005AB3] accent-[#005AB3] shrink-0"
                                            id="consentCheck"
                                            name="persetujuan"
                                            value="1"
                                            required
                                            type="checkbox"
                                            @checked(old('persetujuan'))
                                        >
                                        <span class="text-xs min-[641px]:text-sm text-[#171C21] leading-relaxed">
                                            Saya menyatakan bahwa informasi yang disampaikan adalah benar dan dapat dipertanggungjawabkan untuk ditindaklanjuti demi perbaikan pelayanan publik.
                                        </span>
                                    </label>
                                </div>

                                <div class="pt-2 flex flex-col min-[641px]:flex-row items-stretch min-[641px]:items-center justify-between gap-3">
                                    <button
                                        type="button"
                                        data-prev-step="2"
                                        class="min-h-[44px] px-5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-[#171C21] text-xs font-bold flex items-center justify-center gap-1.5 border border-[#C0C6D6] transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                                        <span>Kembali ke Rincian</span>
                                    </button>

                                    <button
                                        id="submitBtn"
                                        type="submit"
                                        class="min-h-[48px] px-7 rounded-full bg-[#F59E0B] hover:bg-[#D97706] active:bg-[#B45309] text-[#111827] text-xs min-[641px]:text-sm font-extrabold flex items-center justify-center gap-2 shadow-[0_6px_18px_rgba(245,158,11,0.30)] transition-all"
                                    >
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">send</span>
                                        <span id="submitBtnLabel">Kirim Pengaduan</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </section>

                    <!-- RIGHT COLUMN: Ticket Success Frame & Post-Submission Stages (5 Columns) -->
                    <aside class="min-[1008px]:col-span-5 flex flex-col gap-6 min-[1008px]:sticky min-[1008px]:top-24">
                        @if (session('ticket'))
                            <div id="ticketSuccessCard" class="rounded-[28px] bg-white/95 border-2 border-[#0F766E]/40 p-6 min-[641px]:p-7 shadow-[0_20px_48px_rgba(15,118,110,0.12)] space-y-5">
                                <div class="text-center space-y-2.5">
                                    <div class="w-16 h-16 rounded-full bg-[#0F766E] text-white mx-auto flex items-center justify-center shadow-[0_8px_20px_rgba(15,118,110,0.30)]">
                                        <span class="material-symbols-outlined text-[36px]" aria-hidden="true">check_circle</span>
                                    </div>
                                    <span class="inline-block px-3 py-1 rounded-full bg-[#9CF2E8] text-[#00504A] text-xs uppercase tracking-wider font-extrabold">
                                        Berhasil Dikirim
                                    </span>
                                    <h2 class="text-xl font-extrabold text-[#171C21]">Pengaduan Berhasil Dikirim!</h2>
                                    <p class="text-xs min-[641px]:text-sm text-[#414754] leading-relaxed">
                                        Laporan Anda telah tercatat dan akan diperiksa oleh petugas verifikator BHIMA.
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-[#EFF4FB] border border-[#DEE3EA] p-4 space-y-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] uppercase tracking-wider text-[#414754] font-bold">Bukti Registrasi</span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#D6E3FF] text-[#00468D] text-[11px] font-bold">Diterima</span>
                                    </div>

                                    <div>
                                        <span class="text-xs text-[#414754] font-medium block mb-1">Nomor Tiket Pengaduan:</span>
                                        <div class="flex items-center justify-between gap-2 bg-white p-3 rounded-xl border border-[#C0C6D6]/70">
                                            <span class="text-lg min-[380px]:text-xl text-[#005AB3] font-extrabold font-mono tracking-wider select-all" id="ticketNumber">{{ session('ticket') }}</span>
                                            <button
                                                type="button"
                                                id="copyBtn"
                                                class="min-h-[44px] px-3.5 rounded-full bg-[#EFF4FB] hover:bg-[#D6E3FF] text-[#005AB3] text-xs font-bold inline-flex items-center gap-1.5 transition-colors shrink-0"
                                            >
                                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">content_copy</span>
                                                <span id="copyBtnLabel">Salin</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-xs text-[#414754] pt-1">
                                        <div class="flex justify-between gap-2 py-1 border-b border-[#DEE3EA]">
                                            <span>Waktu Pengiriman:</span>
                                            <span class="font-bold text-[#171C21]">{{ session('submitted_at') }}</span>
                                        </div>
                                        <div class="flex justify-between gap-2 py-1">
                                            <span>Status Awal:</span>
                                            <span class="font-bold text-[#0F766E]">Diterima</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-2xl bg-[#D6E3FF]/45 border border-[#AEC6FF] flex items-start gap-2.5 text-xs text-[#171C21]">
                                    <span class="material-symbols-outlined text-[#005AB3] text-[18px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                                    <p class="leading-relaxed">
                                        <strong>Instruksi Pelapor:</strong> Simpan nomor tiket ini untuk memantau status pengaduan melalui menu Lacak Tiket tanpa perlu membuat akun.
                                    </p>
                                </div>

                                <a
                                    href="{{ route('complaint.track', ['tiket' => session('ticket')]) }}"
                                    class="w-full min-h-[48px] px-6 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white text-xs min-[641px]:text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition-colors"
                                >
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_search</span>
                                    <span>Lacak Status Tiket Sekarang</span>
                                </a>
                            </div>
                        @else
                            <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-6 shadow-[0_16px_36px_rgba(20,30,80,0.06)] space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-[#D6E3FF] text-[#005AB3] flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">confirmation_number</span>
                                    </div>
                                    <h2 class="text-base font-bold text-[#171C21]">Bukti Nomor Tiket Otomatis</h2>
                                </div>
                                <p class="text-xs min-[641px]:text-sm text-[#414754] leading-relaxed">
                                    Nomor tiket Anda (<span class="font-mono font-bold text-[#005AB3]">BHM-YYYY-XXXX</span>) akan langsung diterbitkan di sini setelah formulir pengaduan selesai dikirim.
                                </p>
                                <a href="{{ route('complaint.track') }}" class="min-h-[44px] inline-flex items-center gap-1.5 text-xs font-bold text-[#005AB3] hover:underline">
                                    <span>Sudah punya tiket? Cek status di sini</span>
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                </a>
                            </div>
                        @endif

                        <!-- 3-Stage Post Submission Timeline -->
                        <div class="rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-7 shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] space-y-5">
                            <div class="flex items-center gap-2 text-[#171C21]">
                                <span class="material-symbols-outlined text-[#005AB3] text-[22px]" aria-hidden="true">timeline</span>
                                <h2 class="text-base min-[641px]:text-lg font-bold">Tahapan Penanganan Pasca Pengiriman</h2>
                            </div>

                            <ol class="space-y-4">
                                <li class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-[#0F766E] text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                                    <div class="space-y-0.5">
                                        <h3 class="text-xs min-[641px]:text-sm font-bold text-[#171C21]">Verifikasi Kelayakan Berkas</h3>
                                        <p class="text-xs text-[#414754] leading-relaxed">Pemeriksaan kelengkapan uraian kejadian, lampiran bukti, dan indikasi pelanggaran administratif.</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-[#EFF4FB] border border-[#C0C6D6] text-[#005AB3] text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                                    <div class="space-y-0.5">
                                        <h3 class="text-xs min-[641px]:text-sm font-bold text-[#171C21]">Klarifikasi ke Instansi Terlapor</h3>
                                        <p class="text-xs text-[#414754] leading-relaxed">Disposisi telaah ke unit kerja atau inspektorat terkait untuk permintaan klarifikasi resmi.</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-[#EFF4FB] border border-[#C0C6D6] text-[#414754] text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                                    <div class="space-y-0.5">
                                        <h3 class="text-xs min-[641px]:text-sm font-bold text-[#171C21]">Rekomendasi Tindakan &amp; Penyelesaian</h3>
                                        <p class="text-xs text-[#414754] leading-relaxed">Penerbitan rekomendasi sanksi disiplin, perbaikan prosedur loket, dan pemulihan layanan warga.</p>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        @include('partials.liquid-footer')
    </div>

    <script>
        (function () {
            const form = document.getElementById('complaintForm');
            if (!form) return;

            const activeTabClass = 'min-h-[44px] px-3.5 py-2 rounded-full text-xs font-bold flex items-center justify-center min-[481px]:justify-start gap-2 transition-colors bg-[#005AB3] text-white shadow-sm';
            const inactiveTabClass = 'min-h-[44px] px-3.5 py-2 rounded-full text-xs font-bold flex items-center justify-center min-[481px]:justify-start gap-2 transition-colors bg-[#EFF4FB] text-[#414754] hover:text-[#171C21] hover:bg-[#E4E9F0] border border-[#DEE3EA]';

            let currentStep = Number(form.dataset.initialStep || 1);

            function switchStep(step) {
                currentStep = step;
                for (let i = 1; i <= 3; i++) {
                    const panel = document.getElementById('step-content-' + i);
                    const tab = document.getElementById('tab-step-' + i);
                    if (!panel || !tab) continue;

                    const active = i === step;
                    panel.classList.toggle('hidden', !active);
                    panel.classList.toggle('flex', active);
                    tab.className = active ? activeTabClass : inactiveTabClass;
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');

                    const numBadge = tab.querySelector('span:first-child');
                    if (numBadge) {
                        numBadge.className = active
                            ? 'w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 bg-white/25 text-white'
                            : 'w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 bg-[#D6E3FF] text-[#00468D]';
                    }
                }
            }

            [1, 2, 3].forEach((stepNum) => {
                document.getElementById('tab-step-' + stepNum)?.addEventListener('click', () => switchStep(stepNum));
            });
            form.querySelectorAll('[data-next-step]').forEach((btn) => {
                btn.addEventListener('click', () => switchStep(Number(btn.dataset.nextStep)));
            });
            form.querySelectorAll('[data-prev-step]').forEach((btn) => {
                btn.addEventListener('click', () => switchStep(Number(btn.dataset.prevStep)));
            });

            switchStep(currentStep);

            // Privacy Mode Toggle (Anonim vs Data Terbuka)
            const anonToggle = document.getElementById('anonToggle');
            const btnAnon = document.getElementById('btnAnonMode');
            const btnPublic = document.getElementById('btnPublicMode');
            const identityFields = document.getElementById('identityFields');
            const reporterName = document.getElementById('reporterName');
            const anonTitle = document.getElementById('anonTitle');
            const anonDesc = document.getElementById('anonDesc');
            const anonIcon = document.getElementById('anonIcon');

            function syncPrivacyMode(isAnon) {
                if (!anonToggle || !btnAnon || !btnPublic) return;
                anonToggle.checked = isAnon;
                btnAnon.className = isAnon
                    ? 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors bg-[#005AB3] text-white shadow-sm'
                    : 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors text-[#414754] hover:text-[#171C21]';
                btnPublic.className = !isAnon
                    ? 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors bg-[#005AB3] text-white shadow-sm'
                    : 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors text-[#414754] hover:text-[#171C21]';

                if (identityFields) {
                    identityFields.classList.toggle('hidden', isAnon);
                }
                if (reporterName) {
                    reporterName.required = !isAnon;
                }
                if (anonTitle && anonDesc && anonIcon) {
                    if (isAnon) {
                        anonTitle.textContent = 'Anonim Terlindungi';
                        anonDesc.textContent = 'Nama dan kontak Anda tidak dibagikan ke pihak terlapor.';
                        anonIcon.textContent = 'visibility_off';
                        anonIcon.className = 'material-symbols-outlined text-[20px] text-[#0F766E]';
                    } else {
                        anonTitle.textContent = 'Data Terbuka (Dengan Identitas Pelapor)';
                        anonDesc.textContent = 'Nama pelapor disertakan agar verifikator dapat menghubungi Anda.';
                        anonIcon.textContent = 'badge';
                        anonIcon.className = 'material-symbols-outlined text-[20px] text-[#005AB3]';
                    }
                }
            }

            btnAnon?.addEventListener('click', () => syncPrivacyMode(true));
            btnPublic?.addEventListener('click', () => syncPrivacyMode(false));
            syncPrivacyMode(Boolean(anonToggle?.checked));

            // Character Counter for Uraian
            const desc = document.getElementById('fullDescription');
            const counter = document.getElementById('charCount');
            function syncCount() {
                if (!desc || !counter) return;
                const len = desc.value.length;
                counter.textContent = `Karakter: ${len} / 2000 (Min. 20)`;
                counter.className = len < 20 ? 'text-[11px] font-bold text-[#93000A]' : 'text-[11px] font-bold text-[#0F766E]';
            }
            desc?.addEventListener('input', syncCount);
            syncCount();

            // Multi-file Upload with Safe Remove
            const fileInput = document.getElementById('fileInput');
            const fileListEl = document.getElementById('fileList');
            const uploadTriggerBtn = document.getElementById('uploadTriggerBtn');
            let attachedFiles = [];

            uploadTriggerBtn?.addEventListener('click', () => fileInput?.click());
            uploadTriggerBtn?.addEventListener('dragover', (event) => {
                event.preventDefault();
                uploadTriggerBtn.classList.add('border-[#005AB3]', 'bg-[#D6E3FF]/40');
            });
            uploadTriggerBtn?.addEventListener('dragleave', (event) => {
                event.preventDefault();
                uploadTriggerBtn.classList.remove('border-[#005AB3]', 'bg-[#D6E3FF]/40');
            });
            uploadTriggerBtn?.addEventListener('drop', (event) => {
                event.preventDefault();
                uploadTriggerBtn.classList.remove('border-[#005AB3]', 'bg-[#D6E3FF]/40');
                const dropped = Array.from(event.dataTransfer?.files || []).slice(0, 5);
                if (dropped.length > 0) {
                    attachedFiles = dropped;
                    syncFileList();
                }
            });

            function syncFileList() {
                if (!fileListEl || !fileInput) return;
                const dt = new DataTransfer();
                attachedFiles.forEach((f) => dt.items.add(f));
                fileInput.files = dt.files;

                fileListEl.innerHTML = '';
                attachedFiles.forEach((f, idx) => {
                    const li = document.createElement('li');
                    li.className = 'p-3 rounded-2xl bg-[#EFF4FB] border border-[#DEE3EA] flex items-center justify-between gap-3';

                    const info = document.createElement('div');
                    info.className = 'min-w-0';
                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'text-xs font-bold text-[#171C21] block truncate';
                    nameSpan.textContent = f.name;
                    const sizeSpan = document.createElement('span');
                    sizeSpan.className = 'text-[11px] text-[#414754]';
                    sizeSpan.textContent = (f.size / 1048576).toFixed(2) + ' MB';
                    info.appendChild(nameSpan);
                    info.appendChild(sizeSpan);

                    const rmBtn = document.createElement('button');
                    rmBtn.type = 'button';
                    rmBtn.className = 'w-11 h-11 rounded-full bg-white hover:bg-[#FFDAD6] text-[#414754] hover:text-[#93000A] border border-[#DEE3EA] flex items-center justify-center shrink-0 transition-colors';
                    rmBtn.setAttribute('aria-label', 'Hapus berkas ' + f.name);
                    rmBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>';
                    rmBtn.addEventListener('click', () => {
                        attachedFiles.splice(idx, 1);
                        syncFileList();
                    });

                    li.appendChild(info);
                    li.appendChild(rmBtn);
                    fileListEl.appendChild(li);
                });
            }

            fileInput?.addEventListener('change', () => {
                attachedFiles = Array.from(fileInput.files || []).slice(0, 5);
                syncFileList();
            });

            // Cross-Step Client Validation on Real POST Submit
            const clientErrBox = document.getElementById('clientFormError');
            const clientErrText = document.getElementById('clientFormErrorText');
            const submitBtn = document.getElementById('submitBtn');
            const submitLabel = document.getElementById('submitBtnLabel');

            function showClientError(message, step, focusEl) {
                if (clientErrBox && clientErrText) {
                    clientErrText.textContent = message;
                    clientErrBox.hidden = false;
                }
                switchStep(step);
                focusEl?.focus();
            }

            form.addEventListener('submit', (event) => {
                if (clientErrBox) clientErrBox.hidden = true;

                if (!anonToggle?.checked && (!reporterName?.value?.trim())) {
                    event.preventDefault();
                    showClientError('Isi Nama Pelapor pada Langkah 1 atau pilih mode Anonim.', 1, reporterName);
                    return;
                }

                const cat = document.getElementById('categorySelect');
                if (!cat?.value) {
                    event.preventDefault();
                    showClientError('Pilih Kategori Pokok Pengaduan pada Langkah 2.', 2, cat);
                    return;
                }

                const entity = document.getElementById('reportedEntity');
                if (!entity?.value?.trim()) {
                    event.preventDefault();
                    showClientError('Isi Pihak / Instansi Terlapor pada Langkah 2.', 2, entity);
                    return;
                }

                const dateEl = document.getElementById('incidentDate');
                if (!dateEl?.value) {
                    event.preventDefault();
                    showClientError('Pilih Tanggal Kejadian pada Langkah 2.', 2, dateEl);
                    return;
                }

                const locEl = document.getElementById('incidentLoc');
                if (!locEl?.value?.trim()) {
                    event.preventDefault();
                    showClientError('Isi Lokasi Kejadian pada Langkah 2.', 2, locEl);
                    return;
                }

                const uraianVal = desc?.value?.trim() || '';
                if (uraianVal.length < 20) {
                    event.preventDefault();
                    showClientError('Uraian lengkap kejadian pada Langkah 2 wajib minimal 20 karakter agar dapat ditelaah verifikator.', 2, desc);
                    return;
                }

                const consent = document.getElementById('consentCheck');
                if (!consent?.checked) {
                    event.preventDefault();
                    showClientError('Centang pernyataan kebenaran informasi sebelum mengirim pengaduan.', 3, consent);
                    return;
                }

                if (submitBtn && submitLabel) {
                    submitBtn.disabled = true;
                    submitLabel.textContent = 'Mengirim Pengaduan...';
                }
            });

            // Copy Ticket Button & Mobile Auto-Focus on Success Card
            const successCard = document.getElementById('ticketSuccessCard');
            if (successCard && window.innerWidth < 1008) {
                successCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            document.getElementById('copyBtn')?.addEventListener('click', () => {
                const ticketText = document.getElementById('ticketNumber')?.innerText?.trim();
                const copyLabel = document.getElementById('copyBtnLabel');
                if (!ticketText) return;
                navigator.clipboard?.writeText(ticketText).catch(() => {});
                if (copyLabel) {
                    copyLabel.textContent = 'Tersalin!';
                    setTimeout(() => {
                        copyLabel.textContent = 'Salin';
                    }, 2200);
                }
            });
        })();
    </script>
</body>
</html>
