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
<body class="stitch-liquid-body has-mobile-tabbar min-h-screen overflow-x-hidden bg-[#E9EEF5] font-sans text-[#0B1020] antialiased selection:bg-[#E6F0FC] selection:text-[#0B1020]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-full focus:bg-[#0066CC] focus:text-white focus:font-bold focus:shadow-lg"
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
            <div class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 space-y-6">
                <!-- Top Breadcrumb & Privacy Context Bar -->
                <div class="stitch-no-print flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap text-xs font-semibold text-[#4A5366]">
                        <a
                            href="{{ route('consultation.home') }}"
                            aria-label="Kembali ke Beranda Portal BHIMA"
                            class="press-scale w-11 h-11 rounded-full bg-white/85 border border-white shadow-sm flex items-center justify-center text-[#0B1020] hover:bg-white hover:text-[#0066CC] transition-colors shrink-0"
                        >
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_back</span>
                        </a>
                        <a class="min-h-[44px] inline-flex items-center hover:text-[#0066CC] transition-colors" href="{{ route('consultation.home') }}">Beranda</a>
                        <span class="material-symbols-outlined text-[16px] text-[#94A3B8]" aria-hidden="true">chevron_right</span>
                        <a class="min-h-[44px] inline-flex items-center hover:text-[#0066CC] transition-colors" href="{{ route('consultation.home') }}#layanan">Layanan Publik</a>
                        <span class="material-symbols-outlined text-[16px] text-[#94A3B8]" aria-hidden="true">chevron_right</span>
                        <span class="text-[#005EB8] font-bold">Formulir Pengaduan</span>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/90 border border-[#DCE4F0] text-xs font-bold text-[#0F766E] self-start min-[641px]:self-auto">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">verified_user</span>
                        <span>Opsi Anonim &amp; Pelindungan Pelapor Aktif</span>
                    </div>
                </div>

                <!-- Two-Column Asymmetric Layout (7 + 5 Columns on >= 1008px) -->
                <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 min-[1008px]:gap-8 items-start">
                    <!-- LEFT PRIMARY COLUMN: Header, Prefill Summary & 3-Step Form -->
                    <section class="min-[1008px]:col-span-7 flex flex-col gap-6" aria-label="Formulir Pengaduan Masyarakat">
                        <!-- Intro Header Card -->
                        <div class="surface-card p-6 min-[641px]:p-7 space-y-3">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1.5">
                                    <p class="text-xs uppercase tracking-[0.12em] font-bold text-[#005EB8]">
                                        Pengaduan Pelayanan Publik
                                    </p>
                                    <h1 class="type-h1 text-[#0B1020]">
                                        Formulir Pengaduan Masyarakat &amp; Maladministrasi
                                    </h1>
                                </div>
                                <div class="w-12 h-12 rounded-[16px] bg-[#E6F0FC] flex items-center justify-center text-[#005EB8] shrink-0">
                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">gavel</span>
                                </div>
                            </div>
                            <p class="text-sm text-[#4A5366] leading-relaxed">
                                Sampaikan laporan dugaan pungutan liar, pelayanan lambat, atau ketidakjelasan prosedur loket desa dan kelurahan. Setiap laporan tercatat resmi dan ditelaah oleh verifikator BHIMA.
                            </p>
                        </div>

                        <!-- Pre-filled Consultation Summary Card -->
                        @if ($prefill)
                            <div class="surface-card p-5 min-[641px]:p-6 space-y-3.5 relative overflow-hidden">
                                <span class="absolute inset-x-0 top-0 h-1 bg-[#0066CC]" aria-hidden="true"></span>
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-[14px] bg-[#0066CC] text-white flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">smart_toy</span>
                                        </div>
                                        <div>
                                            <h2 class="text-sm min-[641px]:text-base font-bold text-[#0B1020]">Hasil Konsultasi Asisten Hukum</h2>
                                            <p class="text-xs text-[#4A5366]">Data awal otomatis terisi dari sesi konsultasi Anda (dapat diedit)</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check_circle</span>
                                        <span>Siap Diteruskan</span>
                                    </span>
                                </div>

                                @if (!empty($prefill['chronology']))
                                    <div class="surface-nested p-4 space-y-1">
                                        <span class="text-xs uppercase tracking-wider font-bold text-[#0F766E] block">Ringkasan Kronologi Sesi:</span>
                                        <blockquote class="text-xs min-[641px]:text-sm text-[#0B1020] italic leading-relaxed">
                                            "{{ $prefill['chronology'] }}"
                                        </blockquote>
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#4A5366] block">Kajian Pasal &amp; Regulasi Terindikasi:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($prefill['citations'] as $c)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-[#DCE4F0] text-[#005EB8] text-xs font-semibold">
                                                <span class="material-symbols-outlined text-[15px] text-[#0066CC]" aria-hidden="true">menu_book</span>
                                                <span>{{ data_get($c, 'document_name', 'Rujukan') }} ({{ data_get($c, 'document_number', '-') }}) {{ data_get($c, 'article_label') }}</span>
                                            </span>
                                        @empty
                                            <span class="text-xs text-[#4A5366]">Belum ada rujukan pasal pada sesi ini.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="surface-card !rounded-[20px] p-4 min-[641px]:p-5 text-xs min-[641px]:text-sm text-[#4A5366] flex items-start gap-3">
                                <span class="material-symbols-outlined text-[#0066CC] text-[20px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                                <p class="leading-relaxed">
                                    Belum terhubung dengan hasil konsultasi sebelumnya. Anda dapat <a class="text-[#005EB8] font-bold underline" href="{{ route('consultation.index') }}">bertanya ke Asisten Hukum</a> terlebih dahulu atau langsung mengisi formulir di bawah ini.
                                </p>
                            </div>
                        @endif

                        <!-- Server Validation Errors -->
                        @if ($errors->any())
                            <div role="alert" class="rounded-[20px] bg-[#FEE2E2] border border-[#FCA5A5] text-[#991B1B] p-4 min-[641px]:p-5 space-y-1.5 text-xs min-[641px]:text-sm">
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
                        <div id="clientFormError" hidden role="alert" class="rounded-[20px] bg-[#FEE2E2] border border-[#FCA5A5] text-[#991B1B] p-4 text-xs min-[641px]:text-sm font-bold flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-[20px] shrink-0" aria-hidden="true">error</span>
                            <span id="clientFormErrorText"></span>
                        </div>

                        <!-- MAIN COMPLAINT FORM (Clean Frosted Content Surface + Clear Liquid Glass Segmented Step Bar) -->
                        <form
                            id="complaintForm"
                            method="POST"
                            action="{{ route('complaint.store') }}"
                            enctype="multipart/form-data"
                            novalidate
                            data-initial-step="{{ $initialStep }}"
                            class="surface-card p-5 min-[641px]:p-7 flex flex-col gap-6"
                        >
                            @csrf
                            <input type="hidden" name="session_uuid" value="{{ $prefill['session_uuid'] ?? '' }}">

                            <!-- Clear Liquid Glass Segmented 3-Step Control -->
                            <div class="liquid-glass p-1.5 rounded-[24px] min-[481px]:rounded-full grid grid-cols-1 min-[481px]:grid-cols-3 gap-1.5" role="tablist" aria-label="Tahapan Pengisian Formulir">
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
                                <div class="surface-nested p-4 flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-4">
                                    <div class="flex items-start min-[641px]:items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-white border border-[#DCE4F0] flex items-center justify-center shrink-0">
                                            <span id="anonIcon" class="material-symbols-outlined text-[20px] text-[#0066CC]" aria-hidden="true">badge</span>
                                        </div>
                                        <div>
                                            <span id="anonTitle" class="text-sm font-bold text-[#0B1020] block">Data Terbuka (Dengan Identitas Pelapor)</span>
                                            <span id="anonDesc" class="text-xs text-[#4A5366]">Pilih mode Anonim apabila Anda tidak ingin identitas diketahui pihak terlapor.</span>
                                        </div>
                                    </div>

                                    <!-- Segmented Privacy Mode Switch -->
                                    <div class="inline-flex p-1 bg-[#E4EAF3] rounded-full border border-[#CBD5E4] shrink-0 self-start min-[641px]:self-center">
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
                                        <label class="block text-xs font-bold text-[#0B1020]" for="reporterName">
                                            Nama Pelapor <span class="text-[#991B1B]">*</span>
                                        </label>
                                        <input
                                            class="glass-input w-full min-h-[48px] px-4 text-sm placeholder:text-[#4A5366]"
                                            id="reporterName"
                                            name="nama_pelapor"
                                            type="text"
                                            placeholder="Nama lengkap pelapor"
                                            value="{{ old('nama_pelapor') }}"
                                        >
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-[#0B1020]" for="reporterContact">
                                            Kontak (Opsional)
                                        </label>
                                        <input
                                            class="glass-input w-full min-h-[48px] px-4 text-sm placeholder:text-[#4A5366]"
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
                                        class="btn-primary w-full min-[481px]:w-auto px-6 text-sm inline-flex items-center justify-center gap-2"
                                    >
                                        <span>Lanjut: Rincian Masalah</span>
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 2: RINCIAN MALADMINISTRASI & KRONOLOGI -->
                            <div id="step-content-2" role="tabpanel" aria-labelledby="tab-step-2" class="step-section hidden flex-col gap-5">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#0B1020]" for="categorySelect">
                                        Kategori Pokok Pengaduan <span class="text-[#991B1B]">*</span>
                                    </label>
                                    <div class="relative">
                                        <select
                                            class="glass-input w-full min-h-[48px] px-4 pr-10 text-sm appearance-none"
                                            id="categorySelect"
                                            name="kategori"
                                            required
                                        >
                                            <option value="">Pilih kategori pengaduan</option>
                                            @foreach (($categories ?? ['Pungutan Liar', 'Dugaan Pungutan Liar', 'Pelayanan Lambat', 'Prosedur Tidak Jelas', 'Administrasi Kependudukan', 'Pelayanan Kesehatan', 'Infrastruktur', 'Ketenagakerjaan', 'Pertanahan', 'Maladministrasi Lainnya']) as $k)
                                                <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-[#4A5366]" aria-hidden="true">expand_more</span>
                                    </div>
                                    <p class="text-xs text-[#4A5366]">Pilih kategori pokok yang paling sesuai dengan kejadian Anda.</p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-[#0B1020]" for="reportedEntity">
                                        Pihak / Instansi Terlapor <span class="text-[#991B1B]">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#4A5366] text-[20px]" aria-hidden="true">domain</span>
                                        <input
                                            class="glass-input w-full min-h-[48px] pl-11 pr-4 text-sm placeholder:text-[#4A5366]"
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
                                        <label class="block text-xs font-bold text-[#0B1020]" for="incidentDate">
                                            Tanggal Kejadian <span class="text-[#991B1B]">*</span>
                                        </label>
                                        <input
                                            class="glass-input w-full min-h-[48px] px-4 text-sm"
                                            id="incidentDate"
                                            name="tanggal_kejadian"
                                            required
                                            type="date"
                                            value="{{ old('tanggal_kejadian') }}"
                                            max="{{ now()->toDateString() }}"
                                        >
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-[#0B1020]" for="incidentLoc">
                                            Lokasi Rinci Kejadian <span class="text-[#991B1B]">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#4A5366] text-[20px]" aria-hidden="true">location_on</span>
                                            <input
                                                class="glass-input w-full min-h-[48px] pl-11 pr-4 text-sm placeholder:text-[#4A5366]"
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
                                        <label class="block text-xs font-bold text-[#0B1020]" for="fullDescription">
                                            Uraian Lengkap Kejadian &amp; Fakta Lapangan <span class="text-[#991B1B]">*</span>
                                        </label>
                                        <span class="text-xs font-bold text-[#005EB8]" id="charCount">Karakter: 0 / 2000 (Min. 20)</span>
                                    </div>
                                    <textarea
                                        class="glass-input w-full p-4 text-sm placeholder:text-[#4A5366] leading-relaxed resize-y"
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
                                        class="btn-glass !min-h-[44px] px-5 text-xs inline-flex items-center justify-center gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                                        <span>Kembali ke Identitas</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-next-step="3"
                                        class="btn-primary px-6 text-sm inline-flex items-center justify-center gap-2"
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
                                        <label for="fileInput" class="block text-xs font-bold text-[#0B1020]">
                                            Unggah Berkas Bukti &amp; Dokumentasi (Opsional)
                                        </label>
                                        <span class="text-xs font-semibold text-[#4A5366]">Maks. 5 berkas @ 10 MB (PDF, JPG, PNG)</span>
                                    </div>

                                    <button
                                        type="button"
                                        id="uploadTriggerBtn"
                                        class="press-scale w-full p-6 rounded-[20px] bg-[#F4F7FC] hover:bg-[#E6F0FC]/60 border-2 border-dashed border-[#B7C5DB] hover:border-[#0066CC] transition-all text-center flex flex-col items-center justify-center gap-2"
                                    >
                                        <div class="w-12 h-12 rounded-full bg-white text-[#0066CC] border border-[#DCE4F0] flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[26px]" aria-hidden="true">cloud_upload</span>
                                        </div>
                                        <span class="text-sm font-bold text-[#0B1020]">Klik untuk memilih berkas bukti</span>
                                        <span class="text-xs text-[#4A5366]">Format didukung: PDF, JPG, PNG</span>
                                    </button>

                                    <input id="fileInput" name="lampiran[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                                    <ul id="fileList" class="space-y-2"></ul>
                                </div>

                                <div class="surface-nested p-4">
                                    <label class="flex items-start gap-3 cursor-pointer select-none" for="consentCheck">
                                        <input
                                            class="mt-0.5 w-5 h-5 rounded text-[#0066CC] focus:ring-[#0066CC] accent-[#0066CC] shrink-0"
                                            id="consentCheck"
                                            name="persetujuan"
                                            value="1"
                                            required
                                            type="checkbox"
                                            @checked(old('persetujuan'))
                                        >
                                        <span class="text-xs min-[641px]:text-sm text-[#0B1020] leading-relaxed">
                                            Saya menyatakan bahwa informasi yang disampaikan adalah benar dan dapat dipertanggungjawabkan untuk ditindaklanjuti demi perbaikan pelayanan publik.
                                        </span>
                                    </label>
                                </div>

                                <div class="pt-2 flex flex-col min-[641px]:flex-row items-stretch min-[641px]:items-center justify-between gap-3">
                                    <button
                                        type="button"
                                        data-prev-step="2"
                                        class="btn-glass !min-h-[44px] px-5 text-xs inline-flex items-center justify-center gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                                        <span>Kembali ke Rincian</span>
                                    </button>

                                    <button
                                        id="submitBtn"
                                        type="submit"
                                        class="btn-primary px-7 text-sm inline-flex items-center justify-center gap-2"
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
                            <div id="ticketSuccessCard" class="surface-card relative overflow-hidden border-2 border-[#0F766E]/45 p-6 min-[641px]:p-7 space-y-5">
                                <span class="absolute inset-x-0 top-0 h-1 bg-[#0F766E]" aria-hidden="true"></span>
                                <div class="text-center space-y-2.5">
                                    <div class="w-16 h-16 rounded-full bg-[#0F766E] text-white mx-auto flex items-center justify-center shadow-[0_8px_20px_rgba(15,118,110,0.28)]">
                                        <span class="material-symbols-outlined text-[36px]" aria-hidden="true">check_circle</span>
                                    </div>
                                    <span class="inline-block px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] text-xs uppercase tracking-wider font-extrabold">
                                        Berhasil Dikirim
                                    </span>
                                    <h2 class="text-xl font-extrabold text-[#0B1020]">Pengaduan Berhasil Dikirim!</h2>
                                    <p class="text-xs min-[641px]:text-sm text-[#4A5366] leading-relaxed">
                                        Laporan Anda telah tercatat dan akan diperiksa oleh petugas verifikator BHIMA.
                                    </p>
                                </div>

                                <div class="surface-nested p-4 space-y-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs uppercase tracking-wider text-[#4A5366] font-bold">Bukti Registrasi</span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#E6F0FC] text-[#005EB8] text-xs font-bold">Diterima</span>
                                    </div>

                                    <div>
                                        <span class="text-xs text-[#4A5366] font-medium block mb-1">Nomor Tiket Pengaduan:</span>
                                        <div class="flex items-center justify-between gap-2 bg-white p-3 rounded-[14px] border border-[#CBD5E4]">
                                            <span class="text-lg min-[380px]:text-xl text-[#005EB8] font-extrabold font-mono tracking-wider select-all" id="ticketNumber">{{ session('ticket') }}</span>
                                            <button
                                                type="button"
                                                id="copyBtn"
                                                class="btn-glass !min-h-[44px] px-3.5 text-xs inline-flex items-center gap-1.5 shrink-0"
                                            >
                                                <span class="material-symbols-outlined text-[16px] text-[#0066CC]" aria-hidden="true">content_copy</span>
                                                <span id="copyBtnLabel">Salin</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-xs text-[#4A5366] pt-1">
                                        <div class="flex justify-between gap-2 py-1 border-b border-[#DCE4F0]">
                                            <span>Waktu Pengiriman:</span>
                                            <span class="font-bold text-[#0B1020]">{{ session('submitted_at') }}</span>
                                        </div>
                                        <div class="flex justify-between gap-2 py-1">
                                            <span>Status Awal:</span>
                                            <span class="font-bold text-[#0F766E]">Diterima</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-[16px] bg-[#E6F0FC] border border-[#BFD7F5] flex items-start gap-2.5 text-xs text-[#0B1020]">
                                    <span class="material-symbols-outlined text-[#005EB8] text-[18px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                                    <p class="leading-relaxed">
                                        <strong>Instruksi Pelapor:</strong> Simpan nomor tiket ini untuk memantau status pengaduan melalui menu Lacak Tiket tanpa perlu membuat akun.
                                    </p>
                                </div>

                                <a
                                    href="{{ route('complaint.track', ['tiket' => session('ticket')]) }}"
                                    class="btn-primary w-full px-6 text-sm flex items-center justify-center gap-2"
                                >
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_search</span>
                                    <span>Lacak Status Tiket Sekarang</span>
                                </a>
                            </div>
                        @else
                            <div class="surface-card p-6 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-[16px] bg-[#E6F0FC] text-[#005EB8] flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">confirmation_number</span>
                                    </div>
                                    <h2 class="text-base font-bold text-[#0B1020]">Bukti Nomor Tiket Otomatis</h2>
                                </div>
                                <p class="text-sm text-[#4A5366] leading-relaxed">
                                    Nomor tiket Anda (<span class="font-mono font-bold text-[#005EB8]">BHM-YYYY-XXXX</span>) akan langsung diterbitkan di sini setelah formulir pengaduan selesai dikirim.
                                </p>
                                <a href="{{ route('complaint.track') }}" class="min-h-[44px] inline-flex items-center gap-1 text-xs font-bold text-[#005EB8] hover:underline">
                                    <span>Sudah punya tiket? Cek status di sini</span>
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                </a>
                            </div>
                        @endif

                        <!-- 3-Stage Post Submission Timeline -->
                        <div class="surface-card p-6 min-[641px]:p-7 space-y-5">
                            <div class="flex items-center gap-2 text-[#0B1020]">
                                <span class="material-symbols-outlined text-[#0066CC] text-[22px]" aria-hidden="true">timeline</span>
                                <h2 class="text-base min-[641px]:text-lg font-bold">Tahapan Penanganan Pasca Pengiriman</h2>
                            </div>

                            <ol class="space-y-4">
                                <li class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-[#0F766E] text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                                    <div class="space-y-0.5">
                                        <h3 class="text-sm font-bold text-[#0B1020]">Verifikasi Kelayakan Berkas</h3>
                                        <p class="text-xs text-[#4A5366] leading-relaxed">Pemeriksaan kelengkapan uraian kejadian, lampiran bukti, dan indikasi pelanggaran administratif.</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-[#E6F0FC] border border-[#BFD7F5] text-[#005EB8] text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                                    <div class="space-y-0.5">
                                        <h3 class="text-sm font-bold text-[#0B1020]">Klarifikasi ke Instansi Terlapor</h3>
                                        <p class="text-xs text-[#4A5366] leading-relaxed">Disposisi telaah ke unit kerja atau inspektorat terkait untuk permintaan klarifikasi resmi.</p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-full bg-[#E6F0FC] border border-[#BFD7F5] text-[#005EB8] text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                                    <div class="space-y-0.5">
                                        <h3 class="text-sm font-bold text-[#0B1020]">Rekomendasi Tindakan &amp; Penyelesaian</h3>
                                        <p class="text-xs text-[#4A5366] leading-relaxed">Penerbitan rekomendasi sanksi disiplin, perbaikan prosedur loket, dan pemulihan layanan warga.</p>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        @include('partials.liquid-footer')
        @include('partials.liquid-bottom-nav', ['activeNav' => 'formulir-pengaduan'])
    </div>

    <script>
        (function () {
            const form = document.getElementById('complaintForm');
            if (!form) return;

            const activeTabClass = 'press-scale min-h-[44px] px-3.5 py-2 rounded-full text-xs font-bold flex items-center justify-center min-[481px]:justify-start gap-2 transition-colors bg-[#0066CC] text-white shadow-[0_4px_12px_rgba(0,102,204,0.22)]';
            const inactiveTabClass = 'press-scale min-h-[44px] px-3.5 py-2 rounded-full text-xs font-bold flex items-center justify-center min-[481px]:justify-start gap-2 transition-colors text-[#333E52] hover:text-[#0B1020] hover:bg-white/70';

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
                            : 'w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 bg-[#E6F0FC] text-[#005EB8]';
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
                    ? 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors bg-[#0066CC] text-white shadow-sm'
                    : 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors text-[#333E52] hover:text-[#0B1020]';
                btnPublic.className = !isAnon
                    ? 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors bg-[#0066CC] text-white shadow-sm'
                    : 'min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold transition-colors text-[#333E52] hover:text-[#0B1020]';

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
                        anonIcon.className = 'material-symbols-outlined text-[20px] text-[#0066CC]';
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
                counter.className = len < 20 ? 'text-xs font-bold text-[#991B1B]' : 'text-xs font-bold text-[#0F766E]';
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
                uploadTriggerBtn.classList.add('border-[#0066CC]', 'bg-[#E6F0FC]');
            });
            uploadTriggerBtn?.addEventListener('dragleave', (event) => {
                event.preventDefault();
                uploadTriggerBtn.classList.remove('border-[#0066CC]', 'bg-[#E6F0FC]');
            });
            uploadTriggerBtn?.addEventListener('drop', (event) => {
                event.preventDefault();
                uploadTriggerBtn.classList.remove('border-[#0066CC]', 'bg-[#E6F0FC]');
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
                    li.className = 'p-3 rounded-[16px] bg-[#F4F7FC] border border-[#DCE4F0] flex items-center justify-between gap-3';

                    const info = document.createElement('div');
                    info.className = 'min-w-0';
                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'text-xs font-bold text-[#0B1020] block truncate';
                    nameSpan.textContent = f.name;
                    const sizeSpan = document.createElement('span');
                    sizeSpan.className = 'text-xs text-[#4A5366]';
                    sizeSpan.textContent = (f.size / 1048576).toFixed(2) + ' MB';
                    info.appendChild(nameSpan);
                    info.appendChild(sizeSpan);

                    const rmBtn = document.createElement('button');
                    rmBtn.type = 'button';
                    rmBtn.className = 'w-11 h-11 rounded-full bg-white hover:bg-[#FEE2E2] text-[#4A5366] hover:text-[#991B1B] border border-[#DCE4F0] flex items-center justify-center shrink-0 transition-colors';
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
