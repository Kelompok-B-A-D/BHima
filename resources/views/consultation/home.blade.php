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
<body class="stitch-beranda-body min-h-screen overflow-x-hidden bg-gradient-to-b from-[#EBF2FA] via-[#F3F7FC] to-[#F6F9FF] font-sans text-[#171C21] antialiased selection:bg-[#D6E3FF] selection:text-[#001B3E]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-xl focus:bg-[#F59E0B] focus:text-[#111827] focus:font-bold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <div id="beranda-app" class="w-full overflow-x-hidden">
        <!-- FLOATING LIQUID GLASS HEADER -->
        <!-- Responsive tiers: Handphone (320px-480px), Tablet (641px-1007px), Laptop/Desktop (>=1008px) -->
        <header class="fixed top-2.5 min-[641px]:top-4 left-0 right-0 z-50 flex flex-col items-center px-2.5 min-[481px]:px-4 min-[641px]:px-6 pointer-events-none">
            <div class="h-16 w-full max-w-[1200px] pointer-events-auto rounded-full bg-white/80 backdrop-blur-2xl border border-white/80 shadow-[0_16px_36px_rgba(20,30,80,0.08),0_2px_6px_rgba(0,0,0,0.03),inset_0_1px_1px_rgba(255,255,255,0.8)] px-2.5 min-[380px]:px-3.5 min-[641px]:px-5 min-[1008px]:px-6 flex items-center justify-between gap-1.5 min-[380px]:gap-2 min-[1008px]:gap-4">
                <div class="flex items-center gap-1.5 min-[380px]:gap-2.5 min-[641px]:gap-3 min-w-0">
                    <button
                        type="button"
                        id="beranda-mobile-menu-btn"
                        aria-label="Buka Menu Navigasi"
                        aria-expanded="false"
                        aria-controls="beranda-mobile-drawer"
                        class="min-[1008px]:hidden min-w-[44px] min-h-[44px] px-2.5 min-[420px]:px-3.5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] active:bg-[#DEE3EA] border border-[#C0C6D6]/60 flex items-center justify-center gap-1.5 text-[#171C21] text-xs font-bold transition-colors shrink-0"
                    >
                        <span class="material-symbols-outlined text-[20px] text-[#005AB3]" id="beranda-mobile-menu-icon" aria-hidden="true">menu</span>
                        <span class="hidden min-[420px]:inline">Menu</span>
                    </button>

                    <a class="flex items-center gap-2 min-[641px]:gap-3 shrink-0 group no-underline min-w-0" href="{{ route('consultation.home') }}">
                        <div class="w-9 h-9 min-[380px]:w-10 min-[380px]:h-10 rounded-full bg-[#005AB3] flex items-center justify-center text-white shadow-[0_4px_12px_rgba(0,90,179,0.25)] shrink-0">
                            <span class="material-symbols-outlined text-[18px] min-[380px]:text-[20px]" aria-hidden="true">balance</span>
                        </div>
                        <div class="hidden min-[350px]:flex flex-col text-left min-w-0">
                            <span class="text-sm min-[481px]:text-base min-[641px]:text-lg tracking-tight leading-none text-[#171C21] font-extrabold truncate">BHIMA</span>
                            <span class="hidden min-[740px]:block min-[1008px]:hidden min-[1180px]:block text-[10px] text-[#414754] font-semibold uppercase tracking-wider truncate mt-0.5">Bantuan Hukum &amp; Informasi Masyarakat</span>
                            <span class="hidden min-[641px]:block min-[740px]:hidden min-[1008px]:block min-[1180px]:hidden text-[10px] text-[#414754] font-semibold uppercase tracking-wider truncate mt-0.5">Portal Hukum Publik</span>
                        </div>
                    </a>
                </div>

                <!-- Inline Navigation Pill for Laptop/Desktop (>= 1008px) -->
                <nav class="hidden min-[1008px]:flex items-center gap-1 bg-[#EFF4FB]/90 px-2 py-1.5 rounded-full border border-[#DEE3EA] shrink-0" aria-label="Navigasi Utama Portal">
                    <a aria-current="page" class="px-3 min-[1140px]:px-4 py-2 rounded-full transition-colors bg-[#005AB3] text-white text-xs font-bold shadow-[0_2px_8px_rgba(0,90,179,0.2)]" href="#beranda">Beranda</a>
                    <a class="px-2.5 min-[1140px]:px-3.5 py-2 rounded-full text-xs font-semibold text-[#414754] hover:text-[#171C21] hover:bg-[#E4E9F0] transition-colors" href="#layanan">Layanan &amp; SOP</a>
                    <a class="px-2.5 min-[1140px]:px-3.5 py-2 rounded-full text-xs font-semibold text-[#414754] hover:text-[#171C21] hover:bg-[#E4E9F0] transition-colors" href="#pengumuman">Pengumuman</a>
                    <a class="px-2.5 min-[1140px]:px-3.5 py-2 rounded-full text-xs font-semibold text-[#414754] hover:text-[#171C21] hover:bg-[#E4E9F0] transition-colors" href="#anggaran">Anggaran</a>
                    <a class="px-2.5 min-[1140px]:px-3.5 py-2 rounded-full text-xs font-semibold text-[#414754] hover:text-[#171C21] hover:bg-[#E4E9F0] transition-colors" href="#peraturan">Peraturan JDIH</a>
                </nav>

                <div class="flex items-center gap-1.5 min-[641px]:gap-2.5 shrink-0">
                    <a
                        href="{{ route('complaint.create') }}"
                        class="min-h-[44px] px-3 min-[380px]:px-4 min-[641px]:px-5 rounded-full bg-[#F59E0B] hover:bg-[#D97706] active:bg-[#B45309] text-[#111827] text-xs min-[641px]:text-sm flex items-center justify-center gap-1.5 font-bold shadow-[0_4px_16px_rgba(245,158,11,0.25)] transition-all"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                        <span>Lapor</span>
                    </a>
                    <a
                        href="#pencarian-regulasi"
                        aria-label="Fokus ke Kolom Pencarian"
                        title="Cari Layanan atau Regulasi"
                        class="hidden min-[481px]:flex w-11 h-11 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] items-center justify-center text-[#414754] hover:text-[#171C21] border border-[#DEE3EA] transition-colors"
                    >
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span>
                    </a>
                    <a
                        href="{{ route('login') }}"
                        aria-label="Masuk Portal Petugas BHIMA"
                        title="Portal Petugas BHIMA"
                        class="w-11 h-11 rounded-full bg-[#005AB3] hover:bg-[#00468D] flex items-center justify-center text-white shadow-sm transition-colors"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person</span>
                    </a>
                </div>
            </div>

            <!-- Responsive Handphone (320px-480px) & Tablet (641px-1007px) Floating Glass Drawer -->
            <nav
                id="beranda-mobile-drawer"
                hidden
                aria-label="Navigasi Perangkat Seluler dan Tablet"
                class="min-[1008px]:hidden w-full max-w-[1200px] mt-2 pointer-events-auto rounded-3xl bg-white/95 border border-[#DEE3EA] px-3.5 min-[641px]:px-5 py-4 shadow-[0_20px_48px_rgba(15,35,90,0.14)]"
            >
                <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-2">
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl bg-[#005AB3] text-white font-bold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="#beranda">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">home</span>
                        <span>Beranda</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="#layanan">
                        <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">verified_user</span>
                        <span>Layanan &amp; SOP</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="#pengumuman">
                        <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">feed</span>
                        <span>Pengumuman</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="#anggaran">
                        <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">account_balance_wallet</span>
                        <span>Transparansi Anggaran</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="#peraturan">
                        <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">menu_book</span>
                        <span>Peraturan JDIH</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="{{ route('consultation.index') }}">
                        <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">smart_toy</span>
                        <span>Asisten Hukum Virtual</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="{{ route('complaint.track') }}">
                        <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">track_changes</span>
                        <span>Lacak Tiket</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5" href="{{ route('complaint.create') }}">
                        <span class="material-symbols-outlined text-[18px] text-[#0F766E]" aria-hidden="true">campaign</span>
                        <span>Buat Pengaduan</span>
                    </a>
                </div>
            </nav>
        </header>

        <main id="portal-main-content" class="w-full pt-20 bg-transparent relative min-h-[calc(100vh-280px)]">
            <div class="flex flex-col w-full">
                <!-- 1. ATMOSPHERIC FULL-BLEED HERO SECTION -->
                <section id="beranda" class="relative w-full -mt-20 pt-28 min-[641px]:pt-32 pb-10 min-[641px]:pb-14 min-[1008px]:pb-16 px-3 min-[481px]:px-4 min-[641px]:px-6 overflow-hidden scroll-mt-24">
                    <!-- Atmospheric liquid glass caustic backdrop -->
                    <div class="absolute inset-0 pointer-events-none -z-10 bg-gradient-to-b from-[#E3EFFB] via-[#EBF3FB] to-[#F6F9FF]" aria-hidden="true"></div>

                    <div class="max-w-[1200px] mx-auto w-full flex flex-col pt-2 min-[1008px]:pt-6">
                        <!-- Main Asymmetric Headline & Status Card -->
                        <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-5 min-[641px]:gap-6 min-[1008px]:gap-8 items-end mb-6 min-[641px]:mb-8 min-[1008px]:mb-10">
                            <div class="min-[1008px]:col-span-8 space-y-3 min-[641px]:space-y-4">
                                <p class="inline-flex items-center gap-2 px-3 min-[380px]:px-3.5 py-1.5 rounded-full bg-white/90 border border-[#DEE3EA] shadow-[0_2px_8px_rgba(20,30,80,0.06)] text-[10px] min-[380px]:text-xs uppercase tracking-wider text-[#0F766E] font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#0F766E] shrink-0" aria-hidden="true"></span>
                                    <span>Kanal Advokasi &amp; Transparansi Publik 2026</span>
                                </p>
                                <h1 class="text-[26px] leading-[1.15] min-[380px]:text-3xl min-[481px]:text-4xl min-[641px]:text-[42px] min-[641px]:leading-[1.12] min-[1008px]:text-[48px] min-[1200px]:text-[54px] min-[1008px]:leading-[1.08] text-[#171C21] font-extrabold tracking-tight">
                                    Layanan Publik Terbuka, Transparan, dan Adil untuk Warga.
                                </h1>
                                <p class="text-xs min-[380px]:text-sm min-[641px]:text-base min-[1008px]:text-lg text-[#414754] max-w-2xl leading-relaxed">
                                    Portal informasi hukum, <strong>Ruang Konsultasi Hukum Warga</strong>, standar operasional prosedur bebas biaya, dan pengaduan maladministrasi tingkat kelurahan dan kota.
                                </p>
                            </div>

                            <div class="min-[1008px]:col-span-4 flex flex-col justify-end">
                                <div class="p-4 min-[641px]:p-5 rounded-2xl bg-white/90 border border-white shadow-[0_12px_30px_rgba(20,30,80,0.06)] flex flex-col min-[641px]:flex-row min-[1008px]:flex-col min-[641px]:items-center min-[1008px]:items-stretch justify-between gap-2">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between gap-3 text-xs">
                                            <span class="uppercase tracking-wider font-bold text-[#005AB3]">Status Basis Pengetahuan</span>
                                            <span class="text-[#0F766E] font-bold min-[641px]:hidden min-[1008px]:inline">Aktif 24 Jam</span>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-2xl min-[641px]:text-3xl font-extrabold text-[#171C21]">{{ $portalMetrics['documents'] }}</span>
                                            <span class="text-xs min-[380px]:text-sm text-[#414754] font-semibold">Dokumen JDIH &amp; SOP</span>
                                        </div>
                                    </div>
                                    <div class="min-[641px]:text-right min-[1008px]:text-left">
                                        <span class="hidden min-[641px]:inline-block min-[1008px]:hidden text-xs text-[#0F766E] font-bold mb-1">Aktif 24 Jam</span>
                                        <p class="text-xs text-[#414754]">
                                            Cakupan rujukan terverifikasi: <strong class="font-bold text-[#171C21]">{{ $portalMetrics['chunks'] }} potongan pasal &amp; prosedur</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FLOATING GLASS SEARCH BAR -->
                        <div id="pencarian-regulasi" class="w-full p-3 min-[641px]:p-4 rounded-3xl bg-white/85 backdrop-blur-2xl border border-white shadow-[0_20px_48px_rgba(15,35,90,0.10)] transition-all scroll-mt-28">
                            <form id="bhimaSearchForm" action="{{ route('consultation.search') }}" method="GET" class="flex flex-col min-[641px]:flex-row items-stretch min-[641px]:items-center gap-2.5">
                                <div class="flex-1 flex flex-col min-w-0">
                                    <label for="bhimaSearchInput" class="sr-only">
                                        Cari layanan, SOP perizinan, anggaran, atau regulasi
                                    </label>
                                    <div class="flex items-center gap-2.5 min-[380px]:gap-3 px-3 min-[380px]:px-3.5 py-2 flex-1 bg-[#EFF4FB]/70 rounded-2xl border border-[#DEE3EA]">
                                        <span class="material-symbols-outlined text-[#005AB3] text-[22px] min-[380px]:text-[24px] shrink-0" aria-hidden="true">search</span>
                                        <input
                                            id="bhimaSearchInput"
                                            name="q"
                                            type="search"
                                            placeholder="Cari layanan, SOP perizinan, anggaran, atau regulasi..."
                                            class="w-full min-h-[44px] bg-transparent text-xs min-[380px]:text-sm min-[641px]:text-base text-[#171C21] placeholder:text-[#414754] focus:outline-none min-w-0"
                                        >
                                    </div>
                                </div>
                                <div class="flex items-center justify-end shrink-0">
                                    <button
                                        id="bhimaSearchSubmitBtn"
                                        type="submit"
                                        class="w-full min-[641px]:w-auto min-h-[48px] px-5 min-[641px]:px-6 rounded-full bg-[#005AB3] hover:bg-[#00468D] active:bg-[#003366] text-white text-xs min-[380px]:text-sm flex items-center justify-center gap-2 font-bold shadow-[0_4px_16px_rgba(0,90,179,0.28)] transition-all"
                                    >
                                        <span>Telusuri</span>
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                    </button>
                                </div>
                            </form>

                            <!-- Filter chips underneath input inside glass track -->
                            <div class="pt-3 px-1 pb-1">
                                <span class="block min-[481px]:inline text-[11px] min-[380px]:text-xs text-[#414754] uppercase tracking-wider font-bold mb-2 min-[481px]:mb-0 min-[481px]:mr-1.5">Akses Cepat:</span>
                                <div class="grid grid-cols-1 min-[350px]:grid-cols-2 min-[641px]:inline-flex min-[641px]:flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        data-search-tag="Perekaman KTP Elektronik Gratis"
                                        class="min-h-[44px] px-3 min-[380px]:px-3.5 py-1.5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-xs font-semibold text-[#171C21] border border-[#DEE3EA] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="w-2 h-2 rounded-full bg-[#005AB3] shrink-0" aria-hidden="true"></span>
                                        <span class="truncate">Perekaman KTP</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-search-tag="Bebas Pungli & Pelaporan Saber Pungli"
                                        class="min-h-[44px] px-3 min-[380px]:px-3.5 py-1.5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-xs font-semibold text-[#171C21] border border-[#DEE3EA] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="w-2 h-2 rounded-full bg-[#0F766E] shrink-0" aria-hidden="true"></span>
                                        <span class="truncate">Bebas Pungli</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-search-tag="Transparansi Dana & Keterbukaan Informasi"
                                        class="min-h-[44px] px-3 min-[380px]:px-3.5 py-1.5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-xs font-semibold text-[#171C21] border border-[#DEE3EA] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="w-2 h-2 rounded-full bg-[#3E57A8] shrink-0" aria-hidden="true"></span>
                                        <span class="truncate">Transparansi Dana</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-search-tag="SOP Izin Usaha Mikro & Ketenagakerjaan"
                                        class="min-h-[44px] px-3 min-[380px]:px-3.5 py-1.5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-xs font-semibold text-[#171C21] border border-[#DEE3EA] transition-colors inline-flex items-center justify-center min-[641px]:justify-start gap-1.5"
                                    >
                                        <span class="w-2 h-2 rounded-full bg-[#006A63] shrink-0" aria-hidden="true"></span>
                                        <span class="truncate">SOP Izin Usaha Mikro</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Search Results Region (Empty / Loading / Error / Data States) -->
                            <div id="beranda-search-panel" class="mt-3 pt-3 border-t border-[#DEE3EA]" aria-live="polite">
                                <p id="beranda-search-idle" class="text-xs text-[#414754] px-1">
                                    Menampilkan akses pencarian ke <strong>{{ $portalMetrics['documents'] }} dokumen JDIH &amp; SOP</strong>. Ketik kata kunci atau klik chip Akses Cepat di atas untuk melihat pasal terkait.
                                </p>
                                <div id="beranda-search-loading" hidden class="p-4 rounded-2xl bg-[#EFF4FB] border border-[#DEE3EA] text-xs min-[641px]:text-sm text-[#005AB3] font-bold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">hourglass_top</span>
                                    <span>Sedang menelusuri basis data regulasi JDIH dan SOP Layanan...</span>
                                </div>
                                <div id="beranda-search-error" hidden role="alert" class="p-4 rounded-2xl bg-[#FFDAD6] border border-[#BA1A1A]/30 text-xs min-[641px]:text-sm text-[#93000A] font-bold"></div>
                                <div id="beranda-search-empty" hidden class="p-4 rounded-2xl bg-[#FEF3C7] border border-[#FDE68A] text-xs min-[641px]:text-sm text-[#92400E] space-y-2">
                                    <p class="font-bold">Tidak ditemukan dokumen yang cocok dengan kata kunci tersebut.</p>
                                    <p>Gunakan kata kunci yang lebih umum (seperti <em>KTP</em>, <em>tanah</em>, <em>BPJS</em>, <em>upah</em>) atau konsultasikan langsung permasalahan Anda kepada Asisten Hukum Virtual.</p>
                                    <a href="{{ route('consultation.index') }}" class="inline-flex items-center gap-1.5 font-bold text-[#005AB3] underline">
                                        <span>Buka Ruang Konsultasi Hukum Warga</span>
                                    </a>
                                </div>
                                <div id="beranda-search-results" hidden class="space-y-3">
                                    <div class="flex items-center justify-between gap-2 flex-wrap px-1">
                                        <span id="beranda-search-summary" class="text-xs font-bold text-[#005AB3] uppercase tracking-wider"></span>
                                        <button
                                            type="button"
                                            id="beranda-search-close"
                                            class="min-h-[44px] px-3.5 py-1.5 rounded-xl text-xs font-bold text-[#171C21] hover:bg-[#EFF4FB] border border-[#C0C6D6]"
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

                <!-- 2. THREE FLOATING SHORTCUT GLASS CARDS -->
                <!-- Handphone (320-640px): 1 col | Tablet (641-1007px): 2 cols | Laptop/Desktop (>=1008px): 3 cols -->
                <section class="max-w-[1200px] mx-auto w-full px-3 min-[481px]:px-4 min-[641px]:px-6 -mt-2 pb-10 min-[641px]:pb-14" aria-label="Pintasan Layanan Utama">
                    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-3 gap-4 min-[641px]:gap-5 min-[1008px]:gap-6">
                        <!-- Card 1: Tanya Asisten Hukum -->
                        <div class="rounded-[28px] bg-white/90 border border-white p-5 min-[641px]:p-6 min-[1008px]:p-7 flex flex-col justify-between shadow-[0_16px_36px_rgba(20,30,80,0.06)] hover:shadow-[0_20px_42px_rgba(20,30,80,0.10)] transition-all group">
                            <div>
                                <div class="w-12 h-12 rounded-2xl bg-[#D6E3FF] flex items-center justify-center text-[#005AB3] mb-4 min-[641px]:mb-5 shadow-sm">
                                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">gavel</span>
                                </div>
                                <span class="text-xs text-[#005AB3] uppercase font-bold tracking-wider">Modul Edukasi Advokasi</span>
                                <h2 class="text-lg min-[380px]:text-xl min-[641px]:text-[22px] text-[#171C21] font-bold mt-1 mb-2">Tanya Asisten Hukum</h2>
                                <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                    Konsultasi otomatis indikasi pasal maladministrasi dan SOP layanan pemerintahan sesuai <strong>{{ $knowledgeStats['jdih'] ?: 80 }} Peraturan JDIH &amp; {{ $knowledgeStats['sop'] ?: 20 }} SOP Layanan</strong> terkini.
                                </p>
                            </div>
                            <div class="pt-5 min-[641px]:pt-7">
                                <a
                                    href="{{ route('consultation.index') }}"
                                    class="min-h-[48px] w-full px-5 rounded-full bg-[#EFF4FB] hover:bg-[#D6E3FF] text-[#005AB3] text-xs min-[380px]:text-sm font-bold flex items-center justify-between border border-[#C0C6D6]/60 shadow-sm transition-colors"
                                >
                                    <span>Mulai Konsultasi</span>
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">smart_toy</span>
                                </a>
                            </div>
                        </div>

                        <!-- Card 2: Buat Pengaduan (Tinted Teal/Amber Focal Card) -->
                        <div class="rounded-[28px] bg-gradient-to-br from-[#0F766E]/10 via-white/95 to-[#F59E0B]/10 border-2 border-[#0F766E]/30 p-5 min-[641px]:p-6 min-[1008px]:p-7 flex flex-col justify-between shadow-[0_18px_40px_rgba(15,118,110,0.10)] hover:shadow-[0_22px_48px_rgba(15,118,110,0.14)] transition-all relative overflow-hidden group">
                            <div>
                                <div class="flex items-center justify-between gap-2 flex-wrap mb-4 min-[641px]:mb-5">
                                    <div class="w-12 h-12 rounded-2xl bg-[#0F766E] flex items-center justify-center text-white shadow-md shrink-0">
                                        <span class="material-symbols-outlined text-[26px]" aria-hidden="true">report_problem</span>
                                    </div>
                                    <span class="px-2.5 min-[380px]:px-3 py-1 rounded-full bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A] text-[10px] min-[380px]:text-xs font-bold tracking-wider uppercase">Privasi Terjaga</span>
                                </div>
                                <span class="text-xs text-[#0F766E] uppercase font-bold tracking-wider">Kanal Langsung Warga</span>
                                <h2 class="text-lg min-[380px]:text-xl min-[641px]:text-[22px] text-[#171C21] font-bold mt-1 mb-2">Buat Pengaduan</h2>
                                <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                    Lapor pungutan liar atau ketidakjelasan loket secara anonim dan aman. Setiap laporan ditelaah langsung oleh tim verifikator daerah.
                                </p>
                            </div>
                            <div class="pt-5 min-[641px]:pt-7 relative z-10">
                                <a
                                    href="{{ route('complaint.create') }}"
                                    class="min-h-[48px] w-full px-5 rounded-full bg-[#0F766E] hover:bg-[#0C635D] text-white text-xs min-[380px]:text-sm font-bold flex items-center justify-between shadow-[0_4px_14px_rgba(15,118,110,0.30)] transition-colors"
                                >
                                    <span>Kirim Aduan Anonim</span>
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                                </a>
                            </div>
                        </div>

                        <!-- Card 3: Lacak Status Tiket -->
                        <div class="rounded-[28px] bg-white/90 border border-white p-5 min-[641px]:p-6 min-[1008px]:p-7 flex flex-col justify-between shadow-[0_16px_36px_rgba(20,30,80,0.06)] hover:shadow-[0_20px_42px_rgba(20,30,80,0.10)] transition-all group min-[641px]:col-span-2 min-[1008px]:col-span-1">
                            <div>
                                <div class="w-12 h-12 rounded-2xl bg-[#9CF2E8] flex items-center justify-center text-[#006A63] mb-4 min-[641px]:mb-5 shadow-sm">
                                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">track_changes</span>
                                </div>
                                <span class="text-xs text-[#3E57A8] uppercase font-bold tracking-wider">Transparansi Penanganan</span>
                                <h2 class="text-lg min-[380px]:text-xl min-[641px]:text-[22px] text-[#171C21] font-bold mt-1 mb-2">Lacak Status Tiket</h2>
                                <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed mb-4">
                                    Pantau tindak lanjut laporan pengaduan Anda secara transparan tanpa perlu akun menggunakan kode tiket registrasi.
                                </p>
                            </div>
                            <div>
                                <form id="beranda-ticket-form" action="{{ route('complaint.track') }}" method="GET" class="space-y-2">
                                    <label for="ticketCodeInput" class="block text-xs font-bold text-[#005AB3]">
                                        Nomor Tiket Pengaduan
                                    </label>
                                    <div class="flex flex-col min-[360px]:flex-row items-stretch gap-2">
                                        <input
                                            id="ticketCodeInput"
                                            name="tiket"
                                            type="text"
                                            placeholder="Contoh: BHM-2026-0001"
                                            class="flex-1 min-w-0 min-h-[44px] px-3.5 py-2 bg-[#EFF4FB] rounded-full text-xs min-[641px]:text-sm text-[#171C21] placeholder-[#414754] focus:bg-white border border-[#C0C6D6]"
                                        >
                                        <button
                                            id="ticketTrackBtn"
                                            type="submit"
                                            aria-label="Cek Perkembangan Tiket"
                                            class="min-h-[44px] px-4 py-2 rounded-full bg-[#171C21] hover:bg-[#005AB3] text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm transition-colors shrink-0"
                                        >
                                            <span>Cek</span>
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">manage_search</span>
                                        </button>
                                    </div>
                                    <div id="ticketStatusMsg" role="status" aria-live="polite" class="text-xs min-h-[1.25rem] text-[#414754]"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. PENGUMUMAN RESMI TERBARU (2026) -->
                <section id="pengumuman" class="max-w-[1200px] mx-auto w-full px-3 min-[481px]:px-4 min-[641px]:px-6 py-6 min-[641px]:py-8 scroll-mt-24">
                    <div class="flex flex-col min-[641px]:flex-row min-[641px]:items-end justify-between gap-3 min-[641px]:gap-4 mb-5 min-[641px]:mb-6">
                        <div>
                            <span class="text-xs text-[#005AB3] uppercase font-bold tracking-wider">Pembaruan Regulasi &amp; Agenda</span>
                            <h2 class="text-xl min-[380px]:text-2xl min-[641px]:text-[28px] text-[#171C21] font-bold mt-1">Pengumuman Resmi Terbaru (2026)</h2>
                        </div>
                        <a
                            href="#pencarian-regulasi"
                            data-show-all-docs="1"
                            class="min-h-[44px] inline-flex items-center gap-1.5 text-[#005AB3] hover:text-[#00468D] text-xs min-[641px]:text-sm font-bold transition-colors self-start min-[641px]:self-auto"
                        >
                            <span>Arsip Regulasi Selengkapnya</span>
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-3 gap-4 min-[641px]:gap-5 min-[1008px]:gap-6">
                        <!-- Pengumuman 1 -->
                        <article id="peraturan" class="rounded-[28px] bg-white/90 border border-white p-5 min-[641px]:p-6 shadow-[0_12px_32px_rgba(20,30,80,0.05)] hover:shadow-[0_16px_36px_rgba(20,30,80,0.08)] flex flex-col justify-between transition-all scroll-mt-28">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="px-3 py-1 rounded-full bg-[#D6E3FF] text-[#00468D] text-xs font-bold">Kependudukan</span>
                                    <span class="text-xs text-[#414754] font-semibold">UU No. 24/2013</span>
                                </div>
                                <h3 class="text-base min-[380px]:text-lg text-[#171C21] font-bold leading-snug">
                                    Sosialisasi Layanan Kependudukan Bebas Biaya di Seluruh Wilayah
                                </h3>
                                <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                    Pasal 79A dan Pasal 95B menegaskan pengurusan dan penerbitan dokumen kependudukan tidak dipungut biaya serta melarang segala bentuk pungutan tambahan.
                                </p>
                            </div>
                            <div class="pt-5 min-[641px]:pt-6">
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                    class="min-h-[44px] inline-flex items-center justify-between w-full text-[#005AB3] hover:text-[#00468D] text-xs min-[641px]:text-sm font-bold"
                                >
                                    <span>Baca Dokumen Resmi</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_outward</span>
                                </a>
                            </div>
                        </article>

                        <!-- Pengumuman 2 -->
                        <article class="rounded-[28px] bg-white/90 border border-white p-5 min-[641px]:p-6 shadow-[0_12px_32px_rgba(20,30,80,0.05)] hover:shadow-[0_16px_36px_rgba(20,30,80,0.08)] flex flex-col justify-between transition-all">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="px-3 py-1 rounded-full bg-[#9CF2E8] text-[#00504A] text-xs font-bold">Posbakum &amp; Layanan</span>
                                    <span class="text-xs text-[#414754] font-semibold">UU No. 16/2011</span>
                                </div>
                                <h3 class="text-base min-[380px]:text-lg text-[#171C21] font-bold leading-snug">
                                    Panduan Pendampingan Hukum Gratis Bagi Warga Kurang Mampu
                                </h3>
                                <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                    Layanan konsultasi hukum, penyusunan dokumen, dan pendampingan advokat/paralegal terakreditasi bebas biaya dengan melampirkan KTP dan SKTM resmi.
                                </p>
                            </div>
                            <div class="pt-5 min-[641px]:pt-6">
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'SOP-010']) }}"
                                    class="min-h-[44px] inline-flex items-center justify-between w-full text-[#006A63] hover:text-[#00504A] text-xs min-[641px]:text-sm font-bold"
                                >
                                    <span>Lihat Prosedur Posbakum</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_outward</span>
                                </a>
                            </div>
                        </article>

                        <!-- Pengumuman 3 -->
                        <article class="rounded-[28px] bg-white/90 border border-white p-5 min-[641px]:p-6 shadow-[0_12px_32px_rgba(20,30,80,0.05)] hover:shadow-[0_16px_36px_rgba(20,30,80,0.08)] flex flex-col justify-between transition-all min-[641px]:col-span-2 min-[1008px]:col-span-1">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="px-3 py-1 rounded-full bg-[#DCE1FF] text-[#264191] text-xs font-bold">Keterbukaan Fiskal</span>
                                    <span class="text-xs text-[#414754] font-semibold">UU No. 14/2008</span>
                                </div>
                                <h3 class="text-base min-[380px]:text-lg text-[#171C21] font-bold leading-snug">
                                    Keterbukaan Realisasi Anggaran &amp; Hak Permohonan Informasi Publik
                                </h3>
                                <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                    Laporan pertanggungjawaban alokasi anggaran layanan dan pembangunan publik wajib diumumkan secara berkala serta dapat diakses warga melalui PPID.
                                </p>
                            </div>
                            <div class="pt-5 min-[641px]:pt-6">
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'JDIH-025']) }}"
                                    class="min-h-[44px] inline-flex items-center justify-between w-full text-[#264191] hover:text-[#00164E] text-xs min-[641px]:text-sm font-bold"
                                >
                                    <span>Buka Rincian Regulasi KIP</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_outward</span>
                                </a>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- 4. TWO-COLUMN BENTO: LAYANAN ADMIN POPULER (RP 0) & CAKUPAN TOPIK ADUAN -->
                <section id="layanan" class="max-w-[1200px] mx-auto w-full px-3 min-[481px]:px-4 min-[641px]:px-6 py-6 min-[641px]:py-8 scroll-mt-24">
                    <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 min-[1008px]:gap-8 items-start">
                        <!-- Left Column: Layanan Administrasi Populer (Biaya Rp 0) - 7 Cols on Laptop/Desktop (>=1008px) -->
                        <div class="min-[1008px]:col-span-7 space-y-4">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-2">
                                <div>
                                    <span class="text-xs text-[#0F766E] uppercase font-bold tracking-wider">Tanpa Biaya Retribusi</span>
                                    <h2 class="text-xl min-[380px]:text-2xl min-[641px]:text-[28px] text-[#171C21] font-bold mt-0.5">Layanan Administrasi Populer</h2>
                                </div>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] text-xs font-bold">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">verified</span>
                                    <span>Tarif Resmi: Rp 0</span>
                                </span>
                            </div>

                            <div class="space-y-3.5">
                                <!-- Item 1: Perekaman KTP-el -->
                                <div class="rounded-2xl bg-white/90 border border-white p-4 min-[641px]:p-5 shadow-[0_8px_24px_rgba(20,30,80,0.04)] flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-3.5 min-[641px]:gap-4 hover:bg-white transition-all">
                                    <div class="flex items-start min-[641px]:items-center gap-3 min-[380px]:gap-3.5 min-w-0">
                                        <div class="w-11 h-11 min-[380px]:w-12 min-[380px]:h-12 rounded-xl bg-[#EFF4FB] flex items-center justify-center text-[#005AB3] shrink-0">
                                            <span class="material-symbols-outlined text-[22px] min-[380px]:text-[24px]" aria-hidden="true">badge</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-sm min-[380px]:text-base min-[641px]:text-lg text-[#171C21] font-bold">Perekaman KTP-el Baru &amp; Penggantian</h3>
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">Gratis</span>
                                            </div>
                                            <p class="text-xs min-[641px]:text-sm text-[#414754] mt-0.5">SOP-001: Maksimal 1 - 3 hari kerja setelah verifikasi berkas tuntas.</p>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-001']) }}"
                                        class="w-full min-[641px]:w-auto min-h-[44px] px-4 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-[#171C21] text-xs font-bold shrink-0 inline-flex items-center justify-center gap-1 border border-[#DEE3EA] transition-colors"
                                    >
                                        <span>SOP</span>
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                    </a>
                                </div>

                                <!-- Item 2: Pendaftaran & Reaktivasi PBI JKN -->
                                <div class="rounded-2xl bg-white/90 border border-white p-4 min-[641px]:p-5 shadow-[0_8px_24px_rgba(20,30,80,0.04)] flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-3.5 min-[641px]:gap-4 hover:bg-white transition-all">
                                    <div class="flex items-start min-[641px]:items-center gap-3 min-[380px]:gap-3.5 min-w-0">
                                        <div class="w-11 h-11 min-[380px]:w-12 min-[380px]:h-12 rounded-xl bg-[#EFF4FB] flex items-center justify-center text-[#006A63] shrink-0">
                                            <span class="material-symbols-outlined text-[22px] min-[380px]:text-[24px]" aria-hidden="true">family_restroom</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-sm min-[380px]:text-base min-[641px]:text-lg text-[#171C21] font-bold">Pendaftaran &amp; Reaktivasi PBI Jaminan Kesehatan</h3>
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">Gratis</span>
                                            </div>
                                            <p class="text-xs min-[641px]:text-sm text-[#414754] mt-0.5">SOP-002: Pengusulan peserta PBI melalui kelurahan/desa dan Dinas Sosial.</p>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-002']) }}"
                                        class="w-full min-[641px]:w-auto min-h-[44px] px-4 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-[#171C21] text-xs font-bold shrink-0 inline-flex items-center justify-center gap-1 border border-[#DEE3EA] transition-colors"
                                    >
                                        <span>SOP</span>
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                    </a>
                                </div>

                                <!-- Item 3: Penanganan Pengaduan Pungutan Liar -->
                                <div class="rounded-2xl bg-white/90 border border-white p-4 min-[641px]:p-5 shadow-[0_8px_24px_rgba(20,30,80,0.04)] flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-3.5 min-[641px]:gap-4 hover:bg-white transition-all">
                                    <div class="flex items-start min-[641px]:items-center gap-3 min-[380px]:gap-3.5 min-w-0">
                                        <div class="w-11 h-11 min-[380px]:w-12 min-[380px]:h-12 rounded-xl bg-[#EFF4FB] flex items-center justify-center text-[#264191] shrink-0">
                                            <span class="material-symbols-outlined text-[22px] min-[380px]:text-[24px]" aria-hidden="true">verified_user</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-sm min-[380px]:text-base min-[641px]:text-lg text-[#171C21] font-bold">Penanganan Aduan Pungutan Liar &amp; Mediasi</h3>
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">Gratis</span>
                                            </div>
                                            <p class="text-xs min-[641px]:text-sm text-[#414754] mt-0.5">SOP-003: Verifikasi laporan maksimal 2 x 24 jam dengan perlindungan pelapor.</p>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-003']) }}"
                                        class="w-full min-[641px]:w-auto min-h-[44px] px-4 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] text-[#171C21] text-xs font-bold shrink-0 inline-flex items-center justify-center gap-1 border border-[#DEE3EA] transition-colors"
                                    >
                                        <span>SOP</span>
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Warning Callout on Retribution -->
                            <div class="p-4 rounded-2xl bg-[#FFDAD6]/60 border border-[#BA1A1A]/25 text-[#171C21] flex items-start gap-3 mt-4">
                                <span class="material-symbols-outlined text-[#93000A] text-[20px] shrink-0 mt-0.5" aria-hidden="true">notification_important</span>
                                <p class="text-xs leading-relaxed">
                                    <strong class="font-bold text-[#93000A]">Ketegasan Layanan:</strong> Petugas yang meminta imbalan uang rokok, biaya map, atau percepatan jalur pribadi dapat dilaporkan langsung melalui modul pengaduan dengan proteksi identitas pelapor.
                                </p>
                            </div>
                        </div>

                        <!-- Right Column: Cakupan Topik Regulasi & Aduan (Real Database Counts) - 5 Cols on Laptop/Desktop (>=1008px) -->
                        <div id="indeks-topik" class="min-[1008px]:col-span-5 rounded-[28px] bg-white/90 border border-white p-5 min-[641px]:p-6 min-[1008px]:p-7 shadow-[0_16px_36px_rgba(20,30,80,0.06)]">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-2">
                                <span class="text-xs text-[#005AB3] uppercase font-bold tracking-wider">Indeks Basis Pengetahuan</span>
                                <span class="text-xs text-[#414754] font-semibold">{{ $portalMetrics['documents'] }} Dokumen Aktif</span>
                            </div>
                            <h2 class="text-lg min-[380px]:text-xl min-[641px]:text-[22px] text-[#171C21] font-bold mb-1">Berapa Cakupan Pasal per Topik Aduan?</h2>
                            <p class="text-xs min-[380px]:text-sm text-[#414754] mb-5 min-[641px]:mb-6">
                                Distribusi potongan pasal JDIH dan SOP layanan yang tersedia di basis data BHIMA. Klik topik untuk berkonsultasi.
                            </p>

                            <div class="space-y-4">
                                @php
                                    $barColors = ['bg-[#DC2626]', 'bg-[#3E57A8]', 'bg-[#0F766E]', 'bg-[#005AB3]', 'bg-[#525866]'];
                                    $textColors = ['text-[#93000A]', 'text-[#264191]', 'text-[#0F766E]', 'text-[#005AB3]', 'text-[#414754]'];
                                @endphp
                                @foreach ($topicDistribution as $idx => $item)
                                    @php
                                        $barColor = $barColors[$idx % count($barColors)];
                                        $textColor = $textColors[$idx % count($textColors)];        
                                    @endphp
                                    <a
                                        href="{{ route('consultation.index', ['q' => $item['query']]) }}"
                                        class="block space-y-1.5 p-2 -mx-2 rounded-xl hover:bg-[#EFF4FB] transition-colors group"
                                    >
                                        <div class="flex justify-between items-baseline gap-2 text-xs min-[641px]:text-sm">
                                            <span class="font-bold text-[#171C21] group-hover:text-[#005AB3] transition-colors">{{ $item['label'] }}</span>
                                            <span class="font-mono {{ $textColor }} font-bold shrink-0">{{ $item['count'] }} bagian</span>
                                        </div>
                                        <div class="w-full h-3 rounded-full bg-[#E4E9F0] overflow-hidden p-0.5">
                                            <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $item['percent'] }}%"></div>
                                        </div>
                                        <div class="flex justify-between items-center gap-2 text-[#414754] text-[11px]">
                                            <span class="truncate">{{ $item['reference'] }}</span>
                                            <span class="text-[#0F766E] font-bold shrink-0 group-hover:underline">Konsultasikan</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <!-- Inspection Metric Box -->
                            <div class="mt-6 pt-4 bg-[#EFF4FB] rounded-2xl p-3.5 min-[380px]:p-4 flex items-center justify-between gap-2 flex-wrap border border-[#DEE3EA]">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#0F766E] text-[20px] min-[380px]:text-[22px]" aria-hidden="true">policy</span>
                                    <span class="text-xs min-[641px]:text-sm text-[#171C21] font-bold">Total Potongan Pasal &amp; SOP</span>
                                </div>
                                <span class="text-sm min-[380px]:text-base min-[641px]:text-lg font-extrabold text-[#005AB3]">{{ $portalMetrics['chunks'] }} Bagian</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. BOTTOM PARTICIPATORY TRANSPARENCY BANNER -->
                <section id="anggaran" class="max-w-[1200px] mx-auto w-full px-3 min-[481px]:px-4 min-[641px]:px-6 py-6 scroll-mt-24">
                    <div class="rounded-3xl bg-[#005AB3] text-white p-5 min-[641px]:p-8 min-[1008px]:p-10 shadow-[0_20px_48px_rgba(0,90,179,0.22)] relative overflow-hidden flex flex-col min-[1008px]:flex-row items-start min-[1008px]:items-center justify-between gap-5 min-[641px]:gap-6">
                        <div class="space-y-2.5 max-w-xl relative z-10">
                            <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider">Peran Serta Warga</span>
                            <h2 class="text-xl min-[380px]:text-2xl min-[641px]:text-[28px] font-bold tracking-tight leading-snug">Kawal Anggaran Kelurahan di Wilayah Anda</h2>
                            <p class="text-xs min-[380px]:text-sm min-[641px]:text-base text-white/90 leading-relaxed">
                                Setiap warga berhak memeriksa rincian pagu anggaran pembangunan drainase, perbaikan jalan lingkungan, dan bantuan sosial sesuai UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik.
                            </p>
                        </div>
                        <div class="shrink-0 relative z-10 w-full min-[641px]:w-auto flex flex-col min-[641px]:flex-row gap-3">
                            <a
                                href="{{ route('consultation.reference', ['key' => 'SOP-009']) }}"
                                class="min-h-[48px] px-5 min-[641px]:px-6 rounded-full bg-white text-[#005AB3] hover:bg-[#EFF4FB] text-xs min-[380px]:text-sm font-bold flex items-center justify-center gap-2 shadow-lg transition-colors"
                            >
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_balance_wallet</span>
                                <span>Buka Panduan KIP &amp; Anggaran</span>
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- LIQUID GLASS FOOTER -->
        <footer class="w-full pt-8 min-[641px]:pt-10 pb-8 px-3 min-[481px]:px-4 min-[641px]:px-6 mt-6 min-[641px]:mt-10">
            <div class="max-w-[1200px] mx-auto rounded-3xl bg-white/85 border border-white shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.8)] p-5 min-[641px]:p-8 min-[1008px]:p-12">
                <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-4 gap-6 min-[641px]:gap-8 pb-8 border-b border-[#DEE3EA]">
                    <div class="min-[641px]:col-span-2 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#005AB3] flex items-center justify-center text-white shrink-0">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">balance</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-base min-[380px]:text-lg min-[641px]:text-xl text-[#171C21] font-extrabold tracking-tight block truncate">BHIMA</span>
                                <span class="text-[10px] min-[380px]:text-[11px] text-[#414754] font-semibold uppercase tracking-wider block truncate">Bantuan Hukum dan Informasi Masyarakat</span>
                            </div>
                        </div>
                        <p class="text-xs min-[380px]:text-sm text-[#414754] max-w-md leading-relaxed">
                            Sistem tata kelola informasi peradilan dan pendampingan advokasi masyarakat berbasis transparansi publik dan asistensi hukum terintegrasi.
                        </p>
                        <div class="flex items-center gap-2 pt-1 text-[#414754] text-xs font-semibold">
                            <span class="material-symbols-outlined text-[18px] text-[#0F766E] shrink-0" aria-hidden="true">schedule</span>
                            <span>Jam Operasional 2026: Senin - Jumat (08.00 - 16.30 WIB)</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-xs min-[641px]:text-sm text-[#171C21] font-bold block uppercase tracking-wider">Kanal Publik</h3>
                        <div class="flex flex-col space-y-1 text-xs min-[641px]:text-sm font-medium">
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="#beranda">Beranda Portal</a>
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="#layanan">Layanan Advokasi &amp; SOP</a>
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="#pengumuman">Warta &amp; Pengumuman</a>
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="#anggaran">Transparansi Anggaran</a>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-xs min-[641px]:text-sm text-[#171C21] font-bold block uppercase tracking-wider">Bantuan Terpadu</h3>
                        <div class="flex flex-col space-y-1 text-xs min-[641px]:text-sm font-medium">
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="#peraturan">Basis Data Peraturan</a>
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('consultation.index') }}">Konsultasi Asisten Hukum</a>
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('complaint.track') }}">Pelacakan Status Berkas</a>
                            <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('complaint.create') }}">Formulir Pengaduan Cepat</a>
                        </div>
                    </div>
                </div>

                <div class="pt-6 flex flex-col min-[641px]:flex-row items-start min-[641px]:items-center justify-between gap-4 text-xs text-[#414754] font-semibold">
                    <p>© {{ date('Y') }} Portal Layanan Publik BHIMA. Seluruh hak cipta dilindungi undang-undang.</p>
                    <div class="flex flex-wrap items-center gap-3 min-[641px]:gap-6">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#0F766E] shrink-0" aria-hidden="true"></span>
                            <span>Sesuai UU No. 14/2008 &amp; UU No. 27/2022</span>
                        </span>
                        <a class="min-h-[44px] inline-flex items-center hover:text-[#005AB3] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-019']) }}">Kebijakan Privasi</a>
                        <a class="min-h-[44px] inline-flex items-center hover:text-[#005AB3] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-003']) }}">Standar Operasional</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
