<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shell-type" content="web_standard">
    <title>BHIMA - Bantuan Hukum &amp; Informasi Masyarakat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="stitch-beranda-body has-mobile-tabbar min-h-screen overflow-x-hidden bg-[#E9EEF5] font-sans text-[#0B1020] antialiased selection:bg-[#E6F0FC] selection:text-[#0B1020]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-full focus:bg-[#0066CC] focus:text-white focus:font-bold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <div id="beranda-app" class="w-full overflow-x-hidden">
        @include('partials.liquid-header', ['activeNav' => 'beranda'])

        <main id="portal-main-content" class="w-full pt-20 bg-transparent relative min-h-[calc(100vh-280px)]">
            <div class="flex flex-col w-full">
                <!-- 1. EDGE-TO-EDGE OCEAN-AT-DUSK HERO (Solid #1E3A8A, Zero Gradient) -->
                <section id="beranda" class="hero-dark-scene relative w-full -mt-20 pt-28 min-[641px]:pt-32 pb-10 min-[641px]:pb-14 min-[1008px]:pb-16 px-4 min-[641px]:px-6 overflow-hidden scroll-mt-24 bg-[#1E3A8A] text-white">
                    <div class="max-w-[1200px] mx-auto w-full flex flex-col pt-2 min-[1008px]:pt-6">
                        <!-- Asymmetric Hero Grid: Left Headline + Right Clear Glass UI Preview -->
                        <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 min-[1008px]:gap-8 items-end mb-8 min-[1008px]:mb-10">
                            <div class="hero-enter min-[1008px]:col-span-7 space-y-4">
                                <p class="text-xs uppercase tracking-[0.14em] text-[#F2D8B3] font-bold">
                                    Kanal Advokasi &amp; Transparansi Publik
                                </p>
                                <h1 class="type-display text-white">
                                    Layanan Publik Terbuka, Transparan, dan Adil untuk Warga.
                                </h1>
                                <p class="text-sm min-[641px]:text-base min-[1008px]:text-lg text-[#E2ECFF] max-w-2xl leading-relaxed">
                                    Portal rujukan hukum JDIH, <strong class="text-white font-bold">Ruang Konsultasi Hukum Warga</strong>, standar operasional prosedur bebas biaya, dan pelaporan maladministrasi.
                                </p>
                                <div class="flex flex-col min-[481px]:flex-row items-stretch min-[481px]:items-center gap-3 pt-1">
                                    <a
                                        href="{{ route('consultation.index') }}"
                                        class="press-scale min-h-[48px] px-6 rounded-full bg-white hover:bg-[#F4F7FC] text-[#0052A3] text-sm font-bold inline-flex items-center justify-center gap-2 shadow-[0_8px_24px_rgba(6,10,20,0.22)] transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">smart_toy</span>
                                        <span>Tanya Asisten Hukum</span>
                                    </a>
                                    <a
                                        href="{{ route('complaint.create') }}"
                                        class="btn-glass-dark px-6 text-sm inline-flex items-center justify-center gap-2"
                                    >
                                        <span class="material-symbols-outlined text-[19px]" aria-hidden="true">campaign</span>
                                        <span>Buat Pengaduan Anonim</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Clear Liquid Glass Preview Panel over Deep Ink Blue -->
                            <div class="hero-enter hero-enter-1 min-[1008px]:col-span-5 flex flex-col justify-end">
                                <div class="liquid-glass--clear p-5 min-[641px]:p-6 rounded-[28px] text-white space-y-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs uppercase tracking-wider font-bold text-[#F2D8B3]">Basis Pengetahuan Terindeks</span>
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white">
                                            <span class="w-2 h-2 rounded-full bg-[#FF7A59] shrink-0" aria-hidden="true"></span>
                                            <span>Aktif 24 Jam</span>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-[16px] bg-white/12 border border-white/25 p-3.5">
                                            <span class="text-2xl min-[641px]:text-3xl font-extrabold text-white block leading-none tabular-nums">{{ $portalMetrics['documents'] }}</span>
                                            <span class="text-xs text-[#E2ECFF] font-semibold mt-1.5 block">Dokumen JDIH &amp; SOP</span>
                                        </div>
                                        <div class="rounded-[16px] bg-white/12 border border-white/25 p-3.5">
                                            <span class="text-2xl min-[641px]:text-3xl font-extrabold text-white block leading-none tabular-nums">{{ $portalMetrics['chunks'] }}</span>
                                            <span class="text-xs text-[#E2ECFF] font-semibold mt-1.5 block">Bagian Pasal Aktif</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-[#E2ECFF] leading-relaxed">
                                        Setiap jawaban asisten merujuk langsung pada naskah regulasi JDIH dan SOP layanan yang tersimpan di basis data BHIMA.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- FLOATING CLEAR LIQUID GLASS SEARCH DOCK -->
                        <div id="pencarian-regulasi" class="hero-enter hero-enter-2 liquid-glass liquid-glass--strong w-full p-3.5 min-[641px]:p-4 rounded-[28px] scroll-mt-28 text-[#0B1020]">
                            <form id="bhimaSearchForm" action="{{ route('consultation.search') }}" method="GET" class="flex flex-col min-[641px]:flex-row items-stretch min-[641px]:items-center gap-2.5">
                                <div class="flex-1 flex flex-col min-w-0">
                                    <label for="bhimaSearchInput" class="sr-only">
                                        Cari layanan, SOP perizinan, anggaran, atau regulasi
                                    </label>
                                    <div class="glass-input flex items-center gap-2.5 min-[380px]:gap-3 px-3.5 py-1.5 flex-1">
                                        <span class="material-symbols-outlined text-[#0066CC] text-[22px] shrink-0" aria-hidden="true">search</span>
                                        <input
                                            id="bhimaSearchInput"
                                            name="q"
                                            type="search"
                                            placeholder="Cari layanan, SOP perizinan, anggaran, atau regulasi..."
                                            class="w-full min-h-[44px] bg-transparent text-sm min-[641px]:text-base text-[#0B1020] placeholder:text-[#4A5366] focus:outline-none min-w-0"
                                        >
                                    </div>
                                </div>
                                <div class="flex items-center justify-end shrink-0">
                                    <button
                                        id="bhimaSearchSubmitBtn"
                                        type="submit"
                                        class="btn-primary w-full min-[641px]:w-auto px-6 text-sm inline-flex items-center justify-center gap-2"
                                    >
                                        <span>Telusuri</span>
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
                                    </button>
                                </div>
                            </form>

                            <!-- Quick Access Filter Chips -->
                            <div class="pt-3 px-1 pb-1">
                                <span class="block min-[481px]:inline text-xs text-[#4A5366] uppercase tracking-wider font-bold mb-2 min-[481px]:mb-0 min-[481px]:mr-2">Akses Cepat:</span>
                                <div class="grid grid-cols-1 min-[360px]:grid-cols-2 min-[641px]:inline-flex min-[641px]:flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        data-search-tag="Perekaman KTP Elektronik Gratis"
                                        class="press-scale min-h-[44px] px-3.5 py-1.5 rounded-full bg-white/85 hover:bg-white text-xs font-semibold text-[#0B1020] border border-[#75849B] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px] text-[#0066CC] shrink-0" aria-hidden="true">badge</span>
                                        <span class="truncate">Perekaman KTP</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-search-tag="Bebas Pungli & Pelaporan Saber Pungli"
                                        class="press-scale min-h-[44px] px-3.5 py-1.5 rounded-full bg-white/85 hover:bg-white text-xs font-semibold text-[#0B1020] border border-[#75849B] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px] text-[#0F766E] shrink-0" aria-hidden="true">gpp_good</span>
                                        <span class="truncate">Bebas Pungli</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-search-tag="Transparansi Dana & Keterbukaan Informasi"
                                        class="press-scale min-h-[44px] px-3.5 py-1.5 rounded-full bg-white/85 hover:bg-white text-xs font-semibold text-[#0B1020] border border-[#75849B] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px] text-[#1E3A8A] shrink-0" aria-hidden="true">account_balance</span>
                                        <span class="truncate">Transparansi Dana</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-search-tag="SOP Izin Usaha Mikro & Ketenagakerjaan"
                                        class="press-scale min-h-[44px] px-3.5 py-1.5 rounded-full bg-white/85 hover:bg-white text-xs font-semibold text-[#0B1020] border border-[#75849B] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px] text-[#0F766E] shrink-0" aria-hidden="true">storefront</span>
                                        <span class="truncate">SOP Izin Usaha Mikro</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Search Results Region (Empty / Loading / Error / Data States) -->
                            <div id="beranda-search-panel" class="mt-3 pt-3 border-t border-[#DCE4F0]" aria-live="polite">
                                <p id="beranda-search-idle" class="text-xs text-[#4A5366] px-1">
                                    Menelusuri <strong class="text-[#0B1020]">{{ $portalMetrics['documents'] }} dokumen JDIH &amp; SOP</strong>. Ketik kata kunci atau pilih Akses Cepat di atas untuk membuka pasal terkait.
                                </p>
                                <div id="beranda-search-loading" hidden class="p-4 rounded-[16px] bg-[#E6F0FC] border border-[#C6DCF7] text-xs min-[641px]:text-sm text-[#005EB8] font-bold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">hourglass_top</span>
                                    <span>Sedang menelusuri basis data regulasi JDIH dan SOP Layanan...</span>
                                </div>
                                <div id="beranda-search-error" hidden role="alert" class="p-4 rounded-[16px] bg-[#FEE2E2] border border-[#FCA5A5] text-xs min-[641px]:text-sm text-[#991B1B] font-bold"></div>
                                <div id="beranda-search-empty" hidden class="p-4 rounded-[16px] bg-[#FEF3C7] border border-[#F5D07A] text-xs min-[641px]:text-sm text-[#92400E] space-y-2">
                                    <p class="font-bold">Tidak ditemukan dokumen yang cocok dengan kata kunci tersebut.</p>
                                    <p>Gunakan kata kunci yang lebih umum (seperti <em>KTP</em>, <em>tanah</em>, <em>BPJS</em>, <em>upah</em>) atau konsultasikan langsung permasalahan Anda kepada Asisten Hukum Virtual.</p>
                                    <a href="{{ route('consultation.index') }}" class="inline-flex items-center gap-1.5 font-bold text-[#005EB8] underline">
                                        <span>Buka Ruang Konsultasi Hukum Warga</span>
                                    </a>
                                </div>
                                <div id="beranda-search-results" hidden class="space-y-3">
                                    <div class="flex items-center justify-between gap-2 flex-wrap px-1">
                                        <span id="beranda-search-summary" class="text-xs font-bold text-[#005EB8] uppercase tracking-wider"></span>
                                        <button
                                            type="button"
                                            id="beranda-search-close"
                                            class="press-scale min-h-[44px] px-4 py-1.5 rounded-full text-xs font-bold text-[#0B1020] bg-white hover:bg-[#F4F7FC] border border-[#CBD5E4]"
                                        >
                                            Tutup Hasil Pencarian
                                        </button>
                                    </div>
                                    <div id="beranda-search-list" class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 2. WIDE PILL STATS BAR (DESIGN (1).md Section 8: 3 Real Numbers, Thin Dividers) -->
                <section class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 pt-8 min-[641px]:pt-10 pb-6" aria-label="Ringkasan Cakupan Data BHIMA">
                    <div class="surface-card reveal-on-scroll !rounded-[28px] min-[641px]:!rounded-full px-6 py-5 min-[641px]:px-8 min-[641px]:py-5 grid grid-cols-1 min-[641px]:grid-cols-3 divide-y min-[641px]:divide-y-0 min-[641px]:divide-x divide-[#DCE4F0]">
                        <div class="pb-3.5 min-[641px]:pb-0 min-[641px]:pr-6 flex items-baseline justify-between min-[641px]:flex-col min-[641px]:items-center gap-1">
                            <span class="text-2xl min-[641px]:text-3xl font-extrabold text-[#0B1020] tabular-nums">{{ $knowledgeStats['jdih'] }}</span>
                            <span class="text-xs font-semibold text-[#4A5366]">Peraturan JDIH Terverifikasi</span>
                        </div>
                        <div class="py-3.5 min-[641px]:py-0 min-[641px]:px-6 flex items-baseline justify-between min-[641px]:flex-col min-[641px]:items-center gap-1">
                            <span class="text-2xl min-[641px]:text-3xl font-extrabold text-[#0F766E] tabular-nums">{{ $knowledgeStats['sop'] }}</span>
                            <span class="text-xs font-semibold text-[#4A5366]">SOP Layanan Bebas Biaya</span>
                        </div>
                        <div class="pt-3.5 min-[641px]:pt-0 min-[641px]:pl-6 flex items-baseline justify-between min-[641px]:flex-col min-[641px]:items-center gap-1">
                            <span class="text-2xl min-[641px]:text-3xl font-extrabold text-[#005EB8] tabular-nums">{{ $portalMetrics['chunks'] }}</span>
                            <span class="text-xs font-semibold text-[#4A5366]">Potongan Pasal Siap Telusur</span>
                        </div>
                    </div>
                </section>

                <!-- 3. ASYMMETRIC BENTO SHORTCUT CARDS (5 + 7 + 12 cols, Clean Content Surfaces) -->
                <section class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 py-8 min-[641px]:py-10" aria-label="Pintasan Layanan Utama">
                    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-12 gap-5 min-[1008px]:gap-6">
                        <!-- Card 1: Tanya Asisten Hukum (5 cols) -->
                        <div class="surface-card card-interactive reveal-on-scroll min-[1008px]:col-span-5 p-6 min-[1008px]:p-7 flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-[16px] bg-[#E6F0FC] flex items-center justify-center text-[#005EB8] mb-5">
                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">gavel</span>
                                </div>
                                <span class="text-xs text-[#005EB8] uppercase font-bold tracking-wider">Modul Edukasi Advokasi</span>
                                <h2 class="type-h3 text-[#0B1020] mt-1.5 mb-2">Tanya Asisten Hukum</h2>
                                <p class="text-sm text-[#4A5366] leading-relaxed">
                                    Konsultasikan kronologi permasalahan hukum dan SOP layanan berdasarkan <strong class="text-[#0B1020]">{{ $knowledgeStats['jdih'] }} Peraturan JDIH &amp; {{ $knowledgeStats['sop'] }} SOP Layanan</strong> resmi.
                                </p>
                            </div>
                            <div class="pt-6">
                                <a
                                    href="{{ route('consultation.index') }}"
                                    class="btn-glass w-full px-5 text-sm inline-flex items-center justify-between"
                                >
                                    <span>Mulai Konsultasi</span>
                                    <span class="material-symbols-outlined text-[20px] text-[#0066CC]" aria-hidden="true">smart_toy</span>
                                </a>
                            </div>
                        </div>

                        <!-- Card 2: Buat Pengaduan (7 cols - Primary Action Card) -->
                        <div class="surface-card card-interactive reveal-on-scroll min-[1008px]:col-span-7 p-6 min-[1008px]:p-7 flex flex-col justify-between relative overflow-hidden">
                            <span class="absolute inset-x-0 top-0 h-1 bg-[#0F766E]" aria-hidden="true"></span>
                            <div>
                                <div class="flex items-center justify-between gap-2 flex-wrap mb-5">
                                    <div class="w-12 h-12 rounded-[16px] bg-[#0F766E] flex items-center justify-center text-white shrink-0">
                                        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">report_problem</span>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-[#FEF3C7] text-[#92400E] border border-[#F5D07A] text-xs font-bold">Privasi Terjaga</span>
                                </div>
                                <span class="text-xs text-[#0F766E] uppercase font-bold tracking-wider">Kanal Langsung Warga</span>
                                <h2 class="type-h3 text-[#0B1020] mt-1.5 mb-2">Buat Pengaduan Maladministrasi</h2>
                                <p class="text-sm text-[#4A5366] leading-relaxed">
                                    Laporkan pungutan liar, penolakan layanan, atau ketidakjelasan loket secara anonim dan aman. Setiap laporan langsung diperiksa tim verifikator daerah.
                                </p>
                            </div>
                            <div class="pt-6">
                                <a
                                    href="{{ route('complaint.create') }}"
                                    class="btn-primary w-full px-6 text-sm inline-flex items-center justify-between"
                                >
                                    <span>Kirim Aduan Anonim</span>
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                                </a>
                            </div>
                        </div>

                        <!-- Card 3: Lacak Status Tiket (12 cols) -->
                        <div class="surface-card reveal-on-scroll min-[641px]:col-span-2 min-[1008px]:col-span-12 p-6 min-[1008px]:p-7 flex flex-col min-[1008px]:flex-row min-[1008px]:items-center justify-between gap-6">
                            <div class="min-[1008px]:max-w-xl">
                                <div class="w-12 h-12 rounded-[16px] bg-[#E6F5F3] flex items-center justify-center text-[#0F766E] mb-4">
                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">track_changes</span>
                                </div>
                                <span class="text-xs text-[#1E3A8A] uppercase font-bold tracking-wider">Transparansi Penanganan</span>
                                <h2 class="type-h3 text-[#0B1020] mt-1 mb-2">Lacak Status Tiket Pengaduan</h2>
                                <p class="text-sm text-[#4A5366] leading-relaxed">
                                    Pantau perkembangan verifikasi dan disposisi laporan Anda secara langsung tanpa membuat akun cukup dengan kode tiket registrasi.
                                </p>
                            </div>
                            <div class="w-full min-[1008px]:w-[440px] shrink-0">
                                <form id="beranda-ticket-form" action="{{ route('complaint.track') }}" method="GET" class="space-y-2">
                                    <label for="ticketCodeInput" class="block text-xs font-bold text-[#0B1020]">
                                        Nomor Tiket Pengaduan
                                    </label>
                                    <div class="flex flex-col min-[380px]:flex-row items-stretch gap-2">
                                        <input
                                            id="ticketCodeInput"
                                            name="tiket"
                                            type="text"
                                            placeholder="Contoh: BHM-2026-0001"
                                            class="glass-input flex-1 min-w-0 min-h-[48px] px-4 py-2 text-sm text-[#0B1020] placeholder:text-[#4A5366]"
                                        >
                                        <button
                                            id="ticketTrackBtn"
                                            type="submit"
                                            aria-label="Cek Perkembangan Tiket"
                                            class="btn-primary px-5 text-sm inline-flex items-center justify-center gap-1.5 shrink-0"
                                        >
                                            <span>Cek</span>
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">manage_search</span>
                                        </button>
                                    </div>
                                    <div id="ticketStatusMsg" role="status" aria-live="polite" class="text-xs min-h-[1.25rem] text-[#4A5366]"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. PENGUMUMAN RESMI & RUJUKAN UTAMA (Asymmetric Bento 7x2 + 5 + 5) -->
                <section id="pengumuman" class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 py-8 min-[641px]:py-10 scroll-mt-24">
                    <div class="flex flex-col min-[641px]:flex-row min-[641px]:items-end justify-between gap-3 mb-6">
                        <div>
                            <span class="text-xs text-[#005EB8] uppercase font-bold tracking-wider">Pembaruan Regulasi &amp; Agenda</span>
                            <h2 class="type-h2 text-[#0B1020] mt-1">Rujukan Regulasi &amp; Pengumuman Resmi</h2>
                        </div>
                        <a
                            href="#pencarian-regulasi"
                            data-show-all-docs="1"
                            class="min-h-[44px] inline-flex items-center gap-1 text-[#005EB8] hover:text-[#004285] text-sm font-bold transition-colors self-start min-[641px]:self-auto"
                        >
                            <span>Arsip Regulasi Selengkapnya</span>
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-12 gap-5 min-[1008px]:gap-6">
                        <!-- Pengumuman 1 (Featured 7 cols x 2 rows) -->
                        <article id="peraturan" class="surface-card card-interactive reveal-on-scroll min-[1008px]:col-span-7 min-[1008px]:row-span-2 p-6 min-[1008px]:p-8 flex flex-col justify-between scroll-mt-28 relative overflow-hidden">
                            <span class="absolute inset-x-0 top-0 h-1 bg-[#0F766E]" aria-hidden="true"></span>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="px-3 py-1 rounded-full bg-[#E6F0FC] text-[#005EB8] text-xs font-bold">Kependudukan</span>
                                    <span class="text-xs text-[#4A5366] font-semibold">UU No. 24/2013</span>
                                </div>
                                <h3 class="type-h2 text-[#0B1020]">
                                    Layanan Kependudukan Bebas Biaya di Seluruh Wilayah
                                </h3>
                                <p class="text-sm min-[641px]:text-base text-[#4A5366] leading-relaxed">
                                    Pasal 79A dan Pasal 95B menegaskan bahwa pengurusan dan penerbitan seluruh dokumen kependudukan (KTP-el, KK, Akta Kelahiran, Akta Kematian) tidak dipungut biaya serta melarang segala bentuk pungutan tambahan oleh petugas.
                                </p>
                                <div class="surface-nested p-4 text-xs text-[#333E52] leading-relaxed">
                                    <strong class="text-[#0B1020] block mb-1">Dasar Sanksi Pidana (Pasal 95B):</strong>
                                    Setiap pejabat dan petugas pada desa/kelurahan, kecamatan, UPT, dan instansi pelaksana yang memerintahkan atau memfasilitasi pungutan biaya dokumen kependudukan diancam pidana penjara atau denda sesuai undang-undang.
                                </div>
                            </div>
                            <div class="pt-6">
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                    class="btn-glass w-full px-5 text-sm inline-flex items-center justify-between"
                                >
                                    <span>Baca Dokumen Resmi</span>
                                    <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">arrow_outward</span>
                                </a>
                            </div>
                        </article>

                        <!-- Pengumuman 2 (5 cols) -->
                        <article class="surface-card card-interactive reveal-on-scroll min-[1008px]:col-span-5 p-6 flex flex-col justify-between relative overflow-hidden">
                            <span class="absolute inset-x-0 top-0 h-1 bg-[#F2D8B3]" aria-hidden="true"></span>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">Posbakum &amp; Layanan</span>
                                    <span class="text-xs text-[#4A5366] font-semibold">UU No. 16/2011</span>
                                </div>
                                <h3 class="type-h3 text-[#0B1020]">
                                    Pendampingan Hukum Gratis Bagi Warga Kurang Mampu
                                </h3>
                                <p class="text-sm text-[#4A5366] leading-relaxed">
                                    Konsultasi hukum, penyusunan dokumen, dan pendampingan advokat atau paralegal terakreditasi bebas biaya dengan melampirkan KTP dan SKTM resmi.
                                </p>
                            </div>
                            <div class="pt-5">
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'SOP-006']) }}"
                                    class="min-h-[44px] inline-flex items-center justify-between w-full text-[#0F766E] hover:text-[#0B1020] text-sm font-bold transition-colors"
                                >
                                    <span>Lihat Prosedur Posbakum</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_outward</span>
                                </a>
                            </div>
                        </article>

                        <!-- Pengumuman 3 (5 cols) -->
                        <article class="surface-card card-interactive reveal-on-scroll min-[1008px]:col-span-5 p-6 flex flex-col justify-between relative overflow-hidden">
                            <span class="absolute inset-x-0 top-0 h-1 bg-[#1E3A8A]" aria-hidden="true"></span>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="px-3 py-1 rounded-full bg-[#E6F0FC] text-[#005EB8] text-xs font-bold">Keterbukaan Fiskal</span>
                                    <span class="text-xs text-[#4A5366] font-semibold">UU No. 14/2008</span>
                                </div>
                                <h3 class="type-h3 text-[#0B1020]">
                                    Keterbukaan Realisasi Anggaran &amp; Hak Informasi Publik
                                </h3>
                                <p class="text-sm text-[#4A5366] leading-relaxed">
                                    Laporan pertanggungjawaban alokasi anggaran layanan dan pembangunan publik wajib diumumkan secara berkala serta dapat diakses warga melalui PPID.
                                </p>
                            </div>
                            <div class="pt-5">
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'JDIH-020']) }}"
                                    class="min-h-[44px] inline-flex items-center justify-between w-full text-[#005EB8] hover:text-[#004285] text-sm font-bold transition-colors"
                                >
                                    <span>Buka Rincian Regulasi KIP</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_outward</span>
                                </a>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- 5. TWO-COLUMN BENTO: LAYANAN ADMIN POPULER (RP 0) & CAKUPAN TOPIK ADUAN -->
                <section id="layanan" class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 py-8 min-[641px]:py-10 scroll-mt-24">
                    <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 min-[1008px]:gap-8 items-start">
                        <!-- Left Column: Layanan Administrasi Populer (7 cols) -->
                        <div class="min-[1008px]:col-span-7 space-y-4">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-2">
                                <div>
                                    <span class="text-xs text-[#0F766E] uppercase font-bold tracking-wider">Tanpa Biaya Retribusi</span>
                                    <h2 class="type-h2 text-[#0B1020] mt-0.5">Layanan Administrasi Populer</h2>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] text-xs font-bold">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">verified</span>
                                    <span>Tarif Resmi: Rp 0</span>
                                </span>
                            </div>

                            <div class="space-y-3.5">
                                <!-- Item 1: Perekaman KTP-el -->
                                <div class="surface-card card-interactive reveal-on-scroll !rounded-[24px] p-5 flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-4">
                                    <div class="flex items-start min-[641px]:items-center gap-3.5 min-w-0">
                                        <div class="w-12 h-12 rounded-[16px] bg-[#E6F0FC] flex items-center justify-center text-[#005EB8] shrink-0">
                                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">badge</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-base text-[#0B1020] font-bold">Perekaman KTP-el Baru &amp; Penggantian</h3>
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">Gratis</span>
                                            </div>
                                            <p class="text-xs min-[641px]:text-sm text-[#4A5366] mt-0.5">SOP-001: Maksimal 1 sampai 3 hari kerja setelah verifikasi berkas tuntas.</p>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-001']) }}"
                                        class="btn-glass !min-h-[44px] w-full min-[641px]:w-auto px-4 text-xs shrink-0 inline-flex items-center justify-center gap-1"
                                    >
                                        <span>Baca SOP</span>
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                    </a>
                                </div>

                                <!-- Item 2: Keluhan IGD & BPJS Kesehatan (SOP-004) -->
                                <div class="surface-card card-interactive reveal-on-scroll !rounded-[24px] p-5 flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-4">
                                    <div class="flex items-start min-[641px]:items-center gap-3.5 min-w-0">
                                        <div class="w-12 h-12 rounded-[16px] bg-[#E6F5F3] flex items-center justify-center text-[#0F766E] shrink-0">
                                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">medical_services</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-base text-[#0B1020] font-bold">Penanganan Keluhan IGD &amp; Kepesertaan BPJS</h3>
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">Gratis</span>
                                            </div>
                                            <p class="text-xs min-[641px]:text-sm text-[#4A5366] mt-0.5">SOP-004: Pelayanan gawat darurat wajib didahulukan tanpa uang muka deposit.</p>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-004']) }}"
                                        class="btn-glass !min-h-[44px] w-full min-[641px]:w-auto px-4 text-xs shrink-0 inline-flex items-center justify-center gap-1"
                                    >
                                        <span>Baca SOP</span>
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                    </a>
                                </div>

                                <!-- Item 3: Penanganan Pengaduan Pungutan Liar (SOP-003) -->
                                <div class="surface-card card-interactive reveal-on-scroll !rounded-[24px] p-5 flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-4">
                                    <div class="flex items-start min-[641px]:items-center gap-3.5 min-w-0">
                                        <div class="w-12 h-12 rounded-[16px] bg-[#E6F0FC] flex items-center justify-center text-[#1E3A8A] shrink-0">
                                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">verified_user</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-base text-[#0B1020] font-bold">Penanganan Aduan Pungutan Liar &amp; Mediasi</h3>
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">Gratis</span>
                                            </div>
                                            <p class="text-xs min-[641px]:text-sm text-[#4A5366] mt-0.5">SOP-003: Verifikasi laporan maksimal 2 x 24 jam dengan pelindungan pelapor.</p>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-003']) }}"
                                        class="btn-glass !min-h-[44px] w-full min-[641px]:w-auto px-4 text-xs shrink-0 inline-flex items-center justify-center gap-1"
                                    >
                                        <span>Baca SOP</span>
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Warning Callout on Illegal Levies -->
                            <div class="p-4 rounded-[20px] bg-[#FEE2E2] border border-[#FCA5A5] text-[#0B1020] flex items-start gap-3 mt-4">
                                <span class="material-symbols-outlined text-[#991B1B] text-[20px] shrink-0 mt-0.5" aria-hidden="true">notification_important</span>
                                <p class="text-xs leading-relaxed text-[#333E52]">
                                    <strong class="font-bold text-[#991B1B]">Ketegasan Layanan:</strong> Petugas yang meminta imbalan uang rokok, biaya map, atau percepatan jalur pribadi dapat dilaporkan langsung melalui formulir pengaduan dengan proteksi identitas pelapor.
                                </p>
                            </div>
                        </div>

                        <!-- Right Column: Cakupan Topik Regulasi & Aduan (5 cols) -->
                        <div id="indeks-topik" class="surface-card min-[1008px]:col-span-5 p-6 min-[1008px]:p-7">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-2">
                                <span class="text-xs text-[#005EB8] uppercase font-bold tracking-wider">Indeks Basis Pengetahuan</span>
                                <span class="text-xs text-[#4A5366] font-semibold">{{ $portalMetrics['documents'] }} Dokumen Aktif</span>
                            </div>
                            <h2 class="type-h3 text-[#0B1020] mb-1.5">Cakupan Pasal per Topik Aduan</h2>
                            <p class="text-xs min-[641px]:text-sm text-[#4A5366] mb-6 leading-relaxed">
                                Distribusi potongan pasal JDIH dan SOP layanan di basis data BHIMA. Pilih topik untuk berkonsultasi.
                            </p>

                            <div class="space-y-3.5">
                                @php
                                    $barColors = ['bg-[#1E3A8A]', 'bg-[#0066CC]', 'bg-[#0F766E]', 'bg-[#2B509E]', 'bg-[#4A5366]'];
                                    $textColors = ['text-[#1E3A8A]', 'text-[#005EB8]', 'text-[#0F766E]', 'text-[#1E3A8A]', 'text-[#333E52]'];
                                @endphp
                                @foreach ($topicDistribution as $idx => $item)
                                    @php
                                        $barColor = $barColors[$idx % count($barColors)];
                                        $textColor = $textColors[$idx % count($textColors)];
                                    @endphp
                                    <a
                                        href="{{ route('consultation.index', ['q' => $item['query']]) }}"
                                        class="block space-y-1.5 p-2.5 -mx-2.5 rounded-[16px] hover:bg-[#E9EEF5]/70 transition-colors group"
                                    >
                                        <div class="flex justify-between items-baseline gap-2 text-xs min-[641px]:text-sm">
                                            <span class="font-bold text-[#0B1020] group-hover:text-[#005EB8] transition-colors">{{ $item['label'] }}</span>
                                            <span class="font-mono {{ $textColor }} font-bold shrink-0">{{ $item['count'] }} bagian</span>
                                        </div>
                                        <div class="w-full h-2.5 rounded-full bg-[#E2E9F3] overflow-hidden p-0.5">
                                            <div class="topic-bar-fill h-full rounded-full {{ $barColor }}" style="width: {{ $item['percent'] }}%"></div>
                                        </div>
                                        <div class="flex justify-between items-center gap-2 text-[#4A5366] text-xs">
                                            <span class="truncate">{{ $item['reference'] }}</span>
                                            <span class="text-[#005EB8] font-bold shrink-0 group-hover:underline">Konsultasikan</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <div class="surface-nested mt-6 p-4 flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#0F766E] text-[20px]" aria-hidden="true">policy</span>
                                    <span class="text-xs min-[641px]:text-sm text-[#0B1020] font-bold">Total Potongan Pasal &amp; SOP</span>
                                </div>
                                <span class="text-base font-extrabold text-[#005EB8] tabular-nums">{{ $portalMetrics['chunks'] }} Bagian</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 6. PARTICIPATORY BUDGET TRANSPARENCY BANNER (Solid #1E3A8A, Zero Gradient) -->
                <section id="anggaran" class="hero-dark-scene max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 py-8 min-[641px]:py-10 scroll-mt-24">
                    <div class="reveal-on-scroll rounded-[28px] bg-[#1E3A8A] text-white p-6 min-[641px]:p-8 min-[1008px]:p-10 shadow-[0_18px_44px_rgba(20,30,80,0.18)] flex flex-col min-[1008px]:flex-row items-start min-[1008px]:items-center justify-between gap-6">
                        <div class="space-y-2.5 max-w-xl">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#F2D8B3] block">Peran Serta Warga</span>
                            <h2 class="type-h2 text-white">Kawal Anggaran Kelurahan di Wilayah Anda</h2>
                            <p class="text-sm min-[641px]:text-base text-[#E2ECFF] leading-relaxed">
                                Setiap warga berhak memeriksa rincian pagu anggaran pembangunan drainase, perbaikan jalan lingkungan, dan bantuan sosial sesuai UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik.
                            </p>
                        </div>
                        <div class="shrink-0 w-full min-[641px]:w-auto flex flex-col min-[641px]:flex-row gap-3">
                            <a
                                href="{{ route('consultation.reference', ['key' => 'SOP-011']) }}"
                                class="press-scale min-h-[48px] px-6 rounded-full bg-white text-[#0052A3] hover:bg-[#F4F7FC] text-sm font-bold inline-flex items-center justify-center gap-2 shadow-lg transition-colors"
                            >
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_balance_wallet</span>
                                <span>Buka Panduan KIP &amp; Anggaran</span>
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @include('partials.liquid-footer')
        @include('partials.liquid-bottom-nav', ['activeNav' => 'beranda'])
    </div>
</body>
</html>
