<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shell-type" content="web_standard">
    <title>BHIMA - Bantuan Hukum dan Informasi Masyarakat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Newsreader:ital,opsz,wght@0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="stitch-beranda-body bg-[#F0FAF8] font-sans text-[#1F2937] antialiased selection:bg-[#CCECE6] selection:text-[#115E59]">
    <a
        href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-xl focus:bg-[#F59E0B] focus:text-[#111827] focus:font-bold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <div id="beranda-app">
        <!-- Fixed Header (Teal & Amber Accent) -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-[#115E59] shadow-md border-b border-[#0F766E] text-white">
            <div class="bg-[#0b4844] text-[#CCFBF1] py-1.5 px-3 sm:px-6 lg:px-8 border-b border-teal-800/40">
                <div class="max-w-[1280px] mx-auto flex items-center justify-between gap-3 text-[11px] sm:text-xs font-bold">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="flex flex-col w-4 h-2.5 overflow-hidden rounded-[1px] border border-white/40 shrink-0" aria-hidden="true">
                            <div class="h-1/2 bg-red-600"></div>
                            <div class="h-1/2 bg-white"></div>
                        </div>
                        <span class="tracking-wide truncate">Portal Resmi Edukasi Hukum &amp; Keterbukaan Informasi Publik - Tingkat Desa / Kelurahan &amp; Kota</span>
                    </div>
                    <div class="hidden md:flex items-center gap-4 lg:gap-6 shrink-0">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">verified</span>
                            Standar Layanan Informasi Publik RI
                        </span>
                        <span class="text-teal-300" aria-hidden="true">•</span>
                        <a href="tel:15024462" class="flex items-center gap-1 text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">headset_mic</span>
                            Bebas Pulsa: 150-BHIMA
                        </a>
                    </div>
                </div>
            </div>

            <div class="h-16 sm:h-20 max-w-[1280px] mx-auto px-3 sm:px-6 lg:px-8 flex items-center justify-between gap-2 sm:gap-4">
                <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                    <button
                        type="button"
                        id="beranda-mobile-menu-btn"
                        aria-label="Buka Menu Navigasi"
                        aria-expanded="false"
                        aria-controls="beranda-mobile-drawer"
                        class="xl:hidden min-h-[44px] px-3 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/25 border border-white/20 flex items-center justify-center gap-1.5 text-white text-xs font-bold transition-colors shrink-0"
                    >
                        <span class="material-symbols-outlined text-[20px]" id="beranda-mobile-menu-icon" aria-hidden="true">menu</span>
                        <span>Menu</span>
                    </button>

                    <a href="{{ route('consultation.home') }}" class="flex items-center gap-2.5 sm:gap-3.5 text-white no-underline min-w-0">
                        <img
                            src="/images/bhima-logo.svg"
                            alt="Logo BHIMA"
                            width="44"
                            height="44"
                            fetchpriority="high"
                            class="h-9 w-9 sm:h-11 sm:w-11 object-contain bg-white/10 p-1 rounded-lg shrink-0"
                        >
                        <div class="flex flex-col min-w-0">
                            <span class="font-headline text-lg sm:text-[22px] font-semibold text-white tracking-tight leading-tight truncate">BHIMA</span>
                            <span class="text-[11px] sm:text-xs text-[#CCFBF1] font-normal tracking-wide truncate">Bantuan Hukum &amp; Informasi Masyarakat</span>
                        </div>
                    </a>
                </div>

                <nav class="hidden xl:flex items-center gap-1.5 text-sm font-semibold" aria-label="Navigasi Utama Portal">
                    <a aria-current="page" class="transition-colors bg-white/20 text-white font-bold rounded-lg px-3.5 py-2.5 border border-white/20 shadow-sm min-h-[44px] inline-flex items-center" href="#beranda">Beranda</a>
                    <a class="text-[#CCFBF1] hover:text-white hover:bg-white/10 active:bg-white/15 rounded-lg transition-colors py-2.5 px-3 min-h-[44px] inline-flex items-center" href="#layanan">Layanan</a>
                    <a class="text-[#CCFBF1] hover:text-white hover:bg-white/10 active:bg-white/15 rounded-lg transition-colors py-2.5 px-3 min-h-[44px] inline-flex items-center" href="#pengumuman">Pengumuman</a>
                    <a class="text-[#CCFBF1] hover:text-white hover:bg-white/10 active:bg-white/15 rounded-lg transition-colors py-2.5 px-3 min-h-[44px] inline-flex items-center" href="#anggaran">Anggaran</a>
                    <a class="text-[#CCFBF1] hover:text-white hover:bg-white/10 active:bg-white/15 rounded-lg transition-colors py-2.5 px-3 min-h-[44px] inline-flex items-center" href="#peraturan">Peraturan</a>
                    <a class="text-[#CCFBF1] hover:text-white hover:bg-white/10 active:bg-white/15 rounded-lg transition-colors py-2.5 px-3 min-h-[44px] inline-flex items-center" href="{{ route('consultation.index') }}">Asisten Hukum</a>
                    <a class="text-[#CCFBF1] hover:text-white hover:bg-white/10 active:bg-white/15 rounded-lg transition-colors py-2.5 px-3 min-h-[44px] inline-flex items-center" href="{{ route('complaint.track') }}">Lacak Tiket</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a
                        href="{{ route('complaint.create') }}"
                        class="min-h-[44px] flex items-center gap-1.5 bg-[#F59E0B] hover:bg-[#d97706] active:bg-[#b45309] text-[#111827] font-bold text-xs sm:text-sm px-3.5 sm:px-5 py-2.5 rounded-lg shadow-md transition-all"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                        <span>Lapor</span>
                    </a>
                    <a
                        href="{{ route('login') }}"
                        aria-label="Masuk Portal Petugas BHIMA"
                        title="Portal Petugas BHIMA"
                        class="w-11 h-11 rounded-full bg-[#0b4844] hover:bg-[#0F766E] active:bg-[#0d4d49] border border-teal-300/40 flex items-center justify-center text-white transition-colors shrink-0"
                    >
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
                    </a>
                </div>
            </div>

            <!-- Responsive Mobile & Tablet Drawer Navigation -->
            <nav
                id="beranda-mobile-drawer"
                hidden
                aria-label="Navigasi Perangkat Seluler"
                class="xl:hidden bg-[#0b4844] border-t border-teal-800/60 px-4 py-4 shadow-xl"
            >
                <div class="max-w-[1280px] mx-auto grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white/15 text-white font-bold text-sm flex items-center gap-2.5" href="#beranda">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">home</span>
                        <span>Beranda</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl text-[#CCFBF1] hover:bg-white/10 hover:text-white font-semibold text-sm flex items-center gap-2.5" href="#layanan">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">verified_user</span>
                        <span>Layanan Publik</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl text-[#CCFBF1] hover:bg-white/10 hover:text-white font-semibold text-sm flex items-center gap-2.5" href="#pengumuman">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">feed</span>
                        <span>Pengumuman</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl text-[#CCFBF1] hover:bg-white/10 hover:text-white font-semibold text-sm flex items-center gap-2.5" href="#anggaran">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">account_balance</span>
                        <span>Transparansi Anggaran</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl text-[#CCFBF1] hover:bg-white/10 hover:text-white font-semibold text-sm flex items-center gap-2.5" href="#peraturan">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">menu_book</span>
                        <span>Peraturan JDIH</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl text-[#CCFBF1] hover:bg-white/10 hover:text-white font-semibold text-sm flex items-center gap-2.5" href="{{ route('consultation.index') }}">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">smart_toy</span>
                        <span>Asisten Hukum Virtual</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl text-[#CCFBF1] hover:bg-white/10 hover:text-white font-semibold text-sm flex items-center gap-2.5" href="{{ route('complaint.track') }}">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">track_changes</span>
                        <span>Lacak Tiket Pengaduan</span>
                    </a>
                    <a data-beranda-drawer-link class="min-h-[44px] px-3.5 py-2.5 rounded-xl text-[#CCFBF1] hover:bg-white/10 hover:text-white font-semibold text-sm flex items-center gap-2.5" href="{{ route('login') }}">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">badge</span>
                        <span>Portal Petugas</span>
                    </a>
                </div>
            </nav>
        </header>

        <main id="main-content" class="w-full pt-[96px] sm:pt-[112px] bg-[#F0FAF8] min-h-[calc(100vh-280px)]">
            <div class="flex flex-col w-full">
                <!-- Top Trust & Status Ribbon -->
                <section class="w-full bg-[#E6F4F1] border-b border-[#CCECE6] py-2.5 px-3 sm:px-6 lg:px-8" aria-label="Status Layanan Portal">
                    <div class="max-w-[1280px] mx-auto flex flex-wrap items-center justify-between gap-2 text-xs font-bold text-[#374151]">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] font-bold border border-[#bbf7d0]">
                                <span class="w-2 h-2 rounded-full bg-[#166534]" aria-hidden="true"></span>
                                Loket Digital Aktif
                            </span>
                            <span class="text-[#1F2937] font-semibold">Akses Terbuka 24 Jam • Seluruh Wilayah Kelurahan / Desa</span>
                        </div>
                        <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
                            <span class="flex items-center gap-1 text-[#0F766E] font-semibold">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">security</span>
                                Perlindungan Data Warga Dijamin UU PDP
                            </span>
                            <span class="hidden md:inline text-[#0F766E]/40" aria-hidden="true">|</span>
                            <span class="hidden md:flex items-center gap-1 text-[#374151] font-medium">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">library_books</span>
                                Basis Data Aktif: {{ $portalMetrics['documents'] }} Dokumen ({{ $portalMetrics['chunks'] }} Pasal &amp; SOP)
                            </span>
                        </div>
                    </div>
                </section>

                <!-- 1. Hero Section -->
                <section id="beranda" class="relative w-full bg-[#F0FAF8] overflow-hidden py-7 sm:py-10 lg:py-12 scroll-mt-28">
                    <div class="relative max-w-[1280px] mx-auto px-3 sm:px-6 lg:px-8">
                        <!-- Title & Subtitle Editorial Block -->
                        <div class="max-w-3xl mb-6 sm:mb-8">
                            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#0F766E] mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">balance</span>
                                <span>Inisiatif Keadilan Terbuka &amp; Layanan Publik Terpadu</span>
                            </p>
                            <h1 class="font-headline text-2xl sm:text-4xl lg:text-[2.5rem] lg:leading-[3rem] font-bold text-[#0F766E] tracking-tight mb-3 sm:mb-4">
                                Akses Keadilan, Regulasi, dan Layanan Publik Tanpa Hambatan
                            </h1>
                            <p class="text-sm sm:text-base lg:text-lg text-[#374151] leading-relaxed">
                                Platform transparansi hukum, <strong>Ruang Konsultasi Hukum Warga</strong>, dan pengaduan layanan desa serta kota yang mudah, akuntabel, dan tanpa perlu mendaftar akun.
                            </p>
                        </div>

                        <!-- Prominent Search Bar with Drop Shadow Elevation -->
                        <div id="pencarian-regulasi" class="w-full bg-white p-3.5 sm:p-5 rounded-2xl shadow-md border border-[#CCECE6] mb-6 sm:mb-8 scroll-mt-32">
                            <form id="bhimaSearchForm" action="{{ route('consultation.search') }}" method="GET" class="flex flex-col md:flex-row items-stretch gap-2.5 sm:gap-3">
                                <div class="flex-1 flex flex-col">
                                    <label for="bhimaSearchInput" class="text-xs font-bold text-[#0F766E] mb-1.5">
                                        Cari Layanan Publik, SOP Kelurahan/Desa, atau Peraturan JDIH
                                    </label>
                                    <div class="relative flex-1 flex items-center">
                                        <span class="material-symbols-outlined absolute left-3.5 text-[#0F766E] text-[24px] pointer-events-none" aria-hidden="true">search</span>
                                        <input
                                            id="bhimaSearchInput"
                                            name="q"
                                            type="search"
                                            placeholder="Ketik kata kunci, misalnya: KTP-el gratis, sengketa batas tanah, BPJS PBI..."
                                            class="w-full min-h-[48px] pl-11 pr-4 py-2.5 bg-[#F0FAF8] rounded-xl text-sm sm:text-base text-[#1F2937] placeholder-[#4B5563] focus:bg-white border border-[#CCECE6]"
                                        >
                                    </div>
                                </div>
                                <div class="flex items-end">
                                    <button
                                        id="bhimaSearchSubmitBtn"
                                        type="submit"
                                        class="w-full md:w-auto min-h-[48px] px-6 sm:px-7 py-2.5 bg-[#0F766E] hover:bg-[#115E59] active:bg-[#0d4d49] text-white font-bold text-sm sm:text-base rounded-xl shadow-sm flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <span>Telusuri Data</span>
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span>
                                    </button>
                                </div>
                            </form>

                            <!-- Popular Tags Filter Bar -->
                            <div class="mt-3.5 pt-3 border-t border-[#CCECE6]/60 flex flex-wrap items-center gap-2 text-[#374151]">
                                <span class="text-xs uppercase tracking-wider text-[#0F766E] font-bold mr-1">Pencarian Populer:</span>
                                <button
                                    type="button"
                                    data-search-tag="Bantuan Hukum Gratis Posbakum"
                                    class="min-h-[44px] px-3.5 py-1.5 rounded-xl bg-[#F0FAF8] text-[#0F766E] hover:bg-[#0F766E] hover:text-white active:bg-[#115E59] border border-[#CCECE6] text-xs font-bold transition-colors inline-flex items-center gap-1.5"
                                >
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">gavel</span>
                                    <span>Bantuan Hukum Gratis</span>
                                </button>
                                <button
                                    type="button"
                                    data-search-tag="Bantuan Sosial & PBI Jaminan Kesehatan"
                                    class="min-h-[44px] px-3.5 py-1.5 rounded-xl bg-[#F0FAF8] text-[#0F766E] hover:bg-[#0F766E] hover:text-white active:bg-[#115E59] border border-[#CCECE6] text-xs font-bold transition-colors inline-flex items-center gap-1.5"
                                >
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">volunteer_activism</span>
                                    <span>Bansos Desa</span>
                                </button>
                                <button
                                    type="button"
                                    data-search-tag="Sengketa Batas Bidang Tanah & Sertifikat SHM"
                                    class="min-h-[44px] px-3.5 py-1.5 rounded-xl bg-[#F0FAF8] text-[#0F766E] hover:bg-[#0F766E] hover:text-white active:bg-[#115E59] border border-[#CCECE6] text-xs font-bold transition-colors inline-flex items-center gap-1.5"
                                >
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">landscape</span>
                                    <span>Sengketa Tanah</span>
                                </button>
                                <button
                                    type="button"
                                    data-search-tag="SOP Penerbitan KTP Elektronik Gratis"
                                    class="min-h-[44px] px-3.5 py-1.5 rounded-xl bg-[#F0FAF8] text-[#0F766E] hover:bg-[#0F766E] hover:text-white active:bg-[#115E59] border border-[#CCECE6] text-xs font-bold transition-colors inline-flex items-center gap-1.5"
                                >
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">badge</span>
                                    <span>SOP KTP</span>
                                </button>
                                <button
                                    type="button"
                                    data-search-tag="Ketenagakerjaan & Usaha warga"
                                    class="min-h-[44px] px-3.5 py-1.5 rounded-xl bg-[#F0FAF8] text-[#0F766E] hover:bg-[#0F766E] hover:text-white active:bg-[#115E59] border border-[#CCECE6] text-xs font-bold transition-colors inline-flex items-center gap-1.5"
                                >
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">storefront</span>
                                    <span>Surat Keterangan Usaha</span>
                                </button>
                            </div>

                            <!-- Live Search Results Region (Empty / Loading / Error / Data States) -->
                            <div id="beranda-search-panel" class="mt-4 pt-3.5 border-t border-[#CCECE6]/80" aria-live="polite">
                                <p id="beranda-search-idle" class="text-xs text-[#4B5563]">
                                    Menampilkan akses cepat ke <strong>{{ $portalMetrics['documents'] }} dokumen JDIH &amp; SOP</strong>. Ketik kata kunci di atas atau pilih topik populer untuk menelusuri isi pasal.
                                </p>
                                <div id="beranda-search-loading" hidden class="p-4 rounded-xl bg-[#F0FAF8] border border-[#CCECE6] text-xs sm:text-sm text-[#0F766E] font-semibold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">hourglass_top</span>
                                    <span>Sedang menelusuri basis data regulasi JDIH dan SOP Layanan...</span>
                                </div>
                                <div id="beranda-search-error" hidden role="alert" class="p-4 rounded-xl bg-[#FEE2E2] border border-[#FECACA] text-xs sm:text-sm text-[#991B1B] font-semibold"></div>
                                <div id="beranda-search-empty" hidden class="p-4 rounded-xl bg-[#FEF3C7] border border-[#FDE68A] text-xs sm:text-sm text-[#92400E] space-y-2">
                                    <p class="font-bold">Tidak ditemukan dokumen yang cocok dengan kata kunci tersebut.</p>
                                    <p>Gunakan kata kunci yang lebih umum (seperti <em>KTP</em>, <em>tanah</em>, <em>BPJS</em>, <em>upah</em>) atau konsultasikan langsung permasalahan Anda kepada Asisten Hukum Virtual.</p>
                                    <a href="{{ route('consultation.index') }}" class="inline-flex items-center gap-1.5 font-bold text-[#0F766E] underline">
                                        <span>Buka Ruang Konsultasi Hukum Warga</span>
                                    </a>
                                </div>
                                <div id="beranda-search-results" hidden class="space-y-3">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <span id="beranda-search-summary" class="text-xs font-bold text-[#0F766E] uppercase tracking-wider"></span>
                                        <button
                                            type="button"
                                            id="beranda-search-close"
                                            class="min-h-[44px] px-3 py-1.5 rounded-lg text-xs font-bold text-[#374151] hover:bg-[#F3F4F6] border border-[#E5E7EB]"
                                        >
                                            Tutup Hasil Pencarian
                                        </button>
                                    </div>
                                    <div id="beranda-search-list" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Primary Shortcut Cards (With Hierarchy Differentiation: Primary Consultation Card Highlighted) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            <!-- Shortcut 1 (Primary Flagship Card): Tanya Asisten Hukum AI -->
                            <div class="relative bg-white p-5 sm:p-6 rounded-2xl shadow-md flex flex-col justify-between overflow-hidden border-2 border-[#0F766E]">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-[#0F766E] text-white flex items-center justify-center shadow-sm shrink-0">
                                            <span class="material-symbols-outlined text-[26px]" aria-hidden="true">smart_toy</span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold border border-[#bbf7d0]">
                                            Siaga 24 Jam
                                        </span>
                                    </div>
                                    <h2 class="font-headline text-xl font-semibold text-[#0F766E] mb-1.5">
                                        Tanya Asisten Hukum
                                    </h2>
                                    <p class="text-sm text-[#374151] mb-4 leading-relaxed">
                                        Konsultasi awal dan telaah aturan hukum 24 jam dengan ringkasan pasal cepat berbasis <strong>{{ $knowledgeStats['jdih'] ?: 80 }} Peraturan JDIH &amp; {{ $knowledgeStats['sop'] ?: 20 }} SOP Layanan</strong> terverifikasi.
                                    </p>
                                </div>
                                <div>
                                    <div class="p-3 bg-[#F0FAF8] rounded-xl mb-4 flex items-center gap-2 text-[#374151] text-xs font-semibold border border-[#CCECE6]">
                                        <span class="material-symbols-outlined text-[#0F766E] text-[18px]" aria-hidden="true">verified_user</span>
                                        <span>Rujukan Kitab UU &amp; Perda Terkini</span>
                                    </div>
                                    <a
                                        href="{{ route('consultation.index') }}"
                                        class="w-full min-h-[48px] py-3 px-4 rounded-xl bg-[#0F766E] hover:bg-[#115E59] active:bg-[#0d4d49] text-white font-bold text-sm flex items-center justify-between transition-colors shadow-sm"
                                    >
                                        <span>Mulai Tanya AI</span>
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Shortcut 2: Buat Pengaduan -->
                            <div class="relative bg-white p-5 sm:p-6 rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden border border-[#CCECE6]">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-[#115E59] text-white flex items-center justify-center shadow-sm shrink-0">
                                            <span class="material-symbols-outlined text-[26px]" aria-hidden="true">campaign</span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-[#FEE2E2] text-[#991B1B] text-xs font-bold border border-[#fecaca]">
                                            Saluran Rahasia
                                        </span>
                                    </div>
                                    <h2 class="font-headline text-xl font-semibold text-[#0F766E] mb-1.5">
                                        Buat Pengaduan
                                    </h2>
                                    <p class="text-sm text-[#374151] mb-4 leading-relaxed">
                                        Laporkan kendala pelayanan publik, pungutan liar, atau masalah ketertiban lingkungan dengan jaminan perlindungan identitas pelapor.
                                    </p>
                                </div>
                                <div>
                                    <div class="p-3 bg-[#F0FAF8] rounded-xl mb-4 flex items-center gap-2 text-[#374151] text-xs font-semibold border border-[#CCECE6]">
                                        <span class="material-symbols-outlined text-[#0F766E] text-[18px]" aria-hidden="true">lock</span>
                                        <span>Identitas Pelapor Dilindungi</span>
                                    </div>
                                    <a
                                        href="{{ route('complaint.create') }}"
                                        class="w-full min-h-[48px] py-3 px-4 rounded-xl bg-[#F59E0B] hover:bg-[#d97706] active:bg-[#b45309] text-[#111827] font-bold text-sm flex items-center justify-between transition-colors shadow-sm"
                                    >
                                        <span>Isi Pengaduan</span>
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Shortcut 3: Lacak Tiket -->
                            <div class="relative bg-white p-5 sm:p-6 rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden border border-[#CCECE6] sm:col-span-2 lg:col-span-1">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-[#115E59] text-white flex items-center justify-center shadow-sm shrink-0">
                                            <span class="material-symbols-outlined text-[26px]" aria-hidden="true">track_changes</span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-[#E0F2FE] text-[#075985] text-xs font-bold border border-[#bae6fd]">
                                            Pelacakan Langsung
                                        </span>
                                    </div>
                                    <h2 class="font-headline text-xl font-semibold text-[#0F766E] mb-1.5">
                                        Lacak Tiket
                                    </h2>
                                    <p class="text-sm text-[#374151] mb-4 leading-relaxed">
                                        Periksa status tindak lanjut laporan Anda cukup dengan memasukkan nomor tiket tanpa perlu masuk akun.
                                    </p>
                                </div>
                                <div>
                                    <form id="beranda-ticket-form" action="{{ route('complaint.track') }}" method="GET" class="space-y-2">
                                        <label for="ticketCodeInput" class="block text-xs font-bold text-[#0F766E]">
                                            Nomor Tiket Pengaduan
                                        </label>
                                        <div class="flex items-stretch gap-2">
                                            <input
                                                id="ticketCodeInput"
                                                name="tiket"
                                                type="text"
                                                placeholder="Contoh: BHM-2026-0001"
                                                class="flex-1 min-h-[44px] px-3 py-2 bg-[#F0FAF8] rounded-xl text-xs sm:text-sm text-[#1F2937] placeholder-[#4B5563] focus:bg-white border border-[#CCECE6]"
                                            >
                                            <button
                                                id="ticketTrackBtn"
                                                type="submit"
                                                aria-label="Periksa Status Tiket"
                                                class="min-h-[44px] min-w-[44px] px-4 py-2 bg-[#0F766E] hover:bg-[#115E59] active:bg-[#0d4d49] text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1 shadow-sm"
                                            >
                                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
                                                <span>Cek</span>
                                            </button>
                                        </div>
                                        <div id="ticketStatusMsg" role="status" aria-live="polite" class="text-xs min-h-[1.25rem] text-[#374151]"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 2. Pengumuman Terbaru Section -->
                <section id="pengumuman" class="w-full bg-[#EBF7F5] py-8 sm:py-10 lg:py-12 border-y border-[#CCECE6] scroll-mt-24">
                    <div class="max-w-[1280px] mx-auto px-3 sm:px-6 lg:px-8">
                        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 sm:mb-8">
                            <div class="max-w-2xl">
                                <div class="flex items-center gap-2 text-[#0F766E] mb-1.5">
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">feed</span>
                                    <span class="text-xs sm:text-sm uppercase tracking-wider font-bold">Keterbukaan Informasi Publik (KIP)</span>
                                </div>
                                <h2 class="font-headline text-2xl sm:text-3xl font-semibold text-[#0F766E] tracking-tight">
                                    Pengumuman Terbaru &amp; Berita Regulasi
                                </h2>
                                <p class="text-sm sm:text-base text-[#374151] mt-1">
                                    Akses langsung dokumen regulasi daerah/nasional, transparansi anggaran publik, dan panduan bantuan hukum warga.
                                </p>
                            </div>
                            <a
                                href="#pencarian-regulasi"
                                data-show-all-docs="1"
                                class="min-h-[44px] inline-flex items-center gap-1.5 text-sm text-[#0F766E] hover:text-[#115E59] font-bold transition-colors"
                            >
                                <span>Lihat Seluruh Dokumen Terindeks</span>
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            <!-- Card 1: Transparansi Anggaran -->
                            <article id="anggaran" class="bg-white rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden border border-[#CCECE6] scroll-mt-28">
                                <div class="p-5 sm:p-6 flex flex-col gap-2.5">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-[#374151] flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">account_balance</span>
                                            UU No. 14 Tahun 2008
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#E0F2FE] text-[#075985] text-xs font-bold border border-[#bae6fd]">
                                            Transparansi Anggaran
                                        </span>
                                    </div>
                                    <h3 class="font-headline text-lg sm:text-xl font-semibold text-[#1F2937] leading-snug">
                                        Publikasi Realisasi Dana Desa &amp; Hak Warga Memperoleh Informasi Anggaran
                                    </h3>
                                    <p class="text-sm text-[#374151] leading-relaxed">
                                        Ringkasan hak warga memantau laporan pertanggungjawaban penyerapan anggaran pembangunan fasilitas umum serta mekanisme permohonan informasi ke PPID.
                                    </p>
                                </div>
                                <div class="px-5 sm:px-6 py-3.5 bg-[#F0FAF8] border-t border-[#CCECE6] flex items-center justify-between gap-2">
                                    <span class="text-xs text-[#374151] font-semibold">Kode: JDIH-025 &amp; SOP-009</span>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'JDIH-025']) }}"
                                        class="min-h-[44px] inline-flex items-center gap-1 text-xs text-[#0F766E] hover:text-[#115E59] font-bold"
                                    >
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">menu_book</span>
                                        <span>Baca Pasal KIP</span>
                                    </a>
                                </div>
                            </article>

                            <!-- Card 2: Penyuluhan Hukum -->
                            <article class="bg-white rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden border border-[#CCECE6]">
                                <div class="p-5 sm:p-6 flex flex-col gap-2.5">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-[#374151] flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">gavel</span>
                                            UU No. 16 Tahun 2011
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold border border-[#bbf7d0]">
                                            Penyuluhan Hukum
                                        </span>
                                    </div>
                                    <h3 class="font-headline text-lg sm:text-xl font-semibold text-[#1F2937] leading-snug">
                                        Layanan Pos Bantuan Hukum (Posbakum) Gratis Bagi Warga Kurang Mampu
                                    </h3>
                                    <p class="text-sm text-[#374151] leading-relaxed">
                                        Panduan pendampingan hukum litigasi maupun non-litigasi bersama paralegal dan advokat terakreditasi dengan melampirkan KTP dan SKTM resmi kelurahan/desa.
                                    </p>
                                </div>
                                <div class="px-5 sm:px-6 py-3.5 bg-[#F0FAF8] border-t border-[#CCECE6] flex items-center justify-between gap-2">
                                    <span class="text-xs text-[#374151] font-semibold">Kode: SOP-010 (Posbakum)</span>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-010']) }}"
                                        class="min-h-[44px] inline-flex items-center gap-1 text-xs text-[#0F766E] hover:text-[#115E59] font-bold"
                                    >
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">event_available</span>
                                        <span>Lihat Syarat &amp; Alur</span>
                                    </a>
                                </div>
                            </article>

                            <!-- Card 3: Regulasi Baru -->
                            <article id="peraturan" class="bg-white rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden border border-[#CCECE6] sm:col-span-2 lg:col-span-1 scroll-mt-28">
                                <div class="p-5 sm:p-6 flex flex-col gap-2.5">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-[#374151] flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">policy</span>
                                            UU No. 25 Tahun 2009
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#FEF3C7] text-[#92400E] text-xs font-bold border border-[#fde68a]">
                                            Regulasi Pelayanan
                                        </span>
                                    </div>
                                    <h3 class="font-headline text-lg sm:text-xl font-semibold text-[#1F2937] leading-snug">
                                        Standar Pelayanan Publik Daerah &amp; Pencegahan Pungutan Liar
                                    </h3>
                                    <p class="text-sm text-[#374151] leading-relaxed">
                                        Kewajiban penyelenggara layanan mengumumkan persyaratan, jangka waktu penyelesaian, dan tarif resmi yang transparan serta bebas pungutan di luar ketentuan.
                                    </p>
                                </div>
                                <div class="px-5 sm:px-6 py-3.5 bg-[#F0FAF8] border-t border-[#CCECE6] flex items-center justify-between gap-2">
                                    <span class="text-xs text-[#374151] font-semibold">Kode: JDIH-003 &amp; JDIH-004</span>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'JDIH-003']) }}"
                                        class="min-h-[44px] inline-flex items-center gap-1 text-xs text-[#0F766E] hover:text-[#115E59] font-bold"
                                    >
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">article</span>
                                        <span>Baca Ringkasan Pasal</span>
                                    </a>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <!-- 3. Layanan Populer Section -->
                <section id="layanan" class="w-full bg-[#F0FAF8] py-8 sm:py-10 lg:py-12 scroll-mt-24">
                    <div class="max-w-[1280px] mx-auto px-3 sm:px-6 lg:px-8">
                        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 sm:mb-8">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#166534] mb-1.5">
                                    Panduan Prosedur Operasional Standar (SOP)
                                </p>
                                <h2 class="font-headline text-2xl sm:text-3xl font-semibold text-[#0F766E] tracking-tight">
                                    Standar Layanan Publik Cepat &amp; Transparan
                                </h2>
                                <p class="text-sm sm:text-base text-[#374151] mt-1">
                                    Informasi resmi mengenai syarat berkas, ketentuan biaya resmi tanpa perantara, serta estimasi waktu penyelesaian.
                                </p>
                            </div>
                            <!-- Operational Status Indicator Pill -->
                            <div class="flex items-center gap-3 px-4 py-2.5 bg-white rounded-2xl shadow-sm border border-[#CCECE6]">
                                <div class="w-3 h-3 rounded-full bg-[#166534] shrink-0" aria-hidden="true"></div>
                                <div>
                                    <div class="text-xs text-[#0F766E] font-bold">Loket Pelayanan Terpadu Kelurahan / Desa</div>
                                    <div class="text-xs text-[#374151]">Buka Hari Kerja: 08.00 - 15.30 WIB</div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            <!-- Service 1: Lalu Lintas & Pelaporan Kerusakan Jalan -->
                            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm flex flex-col justify-between border border-[#CCECE6]">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-[#E6F7F4] flex items-center justify-center text-[#0F766E] shrink-0">
                                            <span class="material-symbols-outlined text-[28px]" aria-hidden="true">drive_eta</span>
                                        </div>
                                        <span class="text-xs px-2.5 py-1 rounded-full bg-[#F1F5F9] text-[#334155] font-bold border border-[#cbd5e1]">
                                            Lalu Lintas &amp; Jalan
                                        </span>
                                    </div>
                                    <h3 class="font-headline text-lg sm:text-xl font-semibold text-[#1F2937] mb-1.5">
                                        Pelaporan Jalan Rusak &amp; Keselamatan Lalu Lintas
                                    </h3>
                                    <p class="text-sm text-[#374151] mb-4 leading-relaxed">
                                        Prosedur pelaporan kerusakan jalan kabupaten/kota, pemasangan tanda peringatan darurat, dan tanggung jawab penyelenggara jalan sesuai UU No. 22 Tahun 2009.
                                    </p>
                                    <dl class="space-y-2 bg-[#F0FAF8] p-3.5 rounded-xl mb-5 border border-[#CCECE6] text-xs">
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">schedule</span>
                                                Tindak Lanjut Darurat:
                                            </dt>
                                            <dd class="font-bold text-[#1F2937]">Maksimal 1 x 24 Jam</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">payments</span>
                                                Biaya Pelaporan:
                                            </dt>
                                            <dd class="font-bold text-[#166534]">Gratis Rp 0</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">description</span>
                                                Data Pendukung:
                                            </dt>
                                            <dd class="font-bold text-[#1F2937]">Lokasi Jalan &amp; Foto Titik Rusak</dd>
                                        </div>
                                    </dl>
                                </div>
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'SOP-004']) }}"
                                    class="w-full min-h-[44px] py-2.5 px-4 rounded-xl bg-[#0F766E] hover:bg-[#115E59] active:bg-[#0d4d49] text-white font-bold text-sm flex items-center justify-center gap-2 transition-colors shadow-sm"
                                >
                                    <span>Lihat Panduan &amp; Syarat</span>
                                </a>
                            </div>

                            <!-- Service 2: BPJS Kesehatan -->
                            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm flex flex-col justify-between border border-[#CCECE6]">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-[#DCFCE7] flex items-center justify-center text-[#166534] shrink-0">
                                            <span class="material-symbols-outlined text-[28px]" aria-hidden="true">health_and_safety</span>
                                        </div>
                                        <span class="text-xs px-2.5 py-1 rounded-full bg-[#DCFCE7] text-[#166534] font-bold border border-[#bbf7d0]">
                                            Jaminan Kesehatan
                                        </span>
                                    </div>
                                    <h3 class="font-headline text-lg sm:text-xl font-semibold text-[#1F2937] mb-1.5">
                                        Pendaftaran &amp; Bantuan Iuran BPJS
                                    </h3>
                                    <p class="text-sm text-[#374151] mb-4 leading-relaxed">
                                        Panduan pengajuan dan reaktivasi segmen Penerima Bantuan Iuran (PBI JKN) melalui verifikasi kelurahan/desa dan Dinas Sosial.
                                    </p>
                                    <dl class="space-y-2 bg-[#F0FAF8] p-3.5 rounded-xl mb-5 border border-[#CCECE6] text-xs">
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">schedule</span>
                                                Reaktivasi Darurat:
                                            </dt>
                                            <dd class="font-bold text-[#1F2937]">1 x 24 Jam (Kondisi Rawat)</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">payments</span>
                                                Iuran PBI:
                                            </dt>
                                            <dd class="font-bold text-[#166534]">Ditanggung Negara (Rp 0)</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">description</span>
                                                Berkas Wajib:
                                            </dt>
                                            <dd class="font-bold text-[#1F2937]">KTP, KK, SKTM / DTKS</dd>
                                        </div>
                                    </dl>
                                </div>
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'SOP-002']) }}"
                                    class="w-full min-h-[44px] py-2.5 px-4 rounded-xl bg-[#0F766E] hover:bg-[#115E59] active:bg-[#0d4d49] text-white font-bold text-sm flex items-center justify-center gap-2 transition-colors shadow-sm"
                                >
                                    <span>Cek Alur Layanan</span>
                                </a>
                            </div>

                            <!-- Service 3: KTP-el & Adminduk -->
                            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm flex flex-col justify-between border border-[#CCECE6] sm:col-span-2 lg:col-span-1">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-[#E0F2FE] flex items-center justify-center text-[#075985] shrink-0">
                                            <span class="material-symbols-outlined text-[28px]" aria-hidden="true">badge</span>
                                        </div>
                                        <span class="text-xs px-2.5 py-1 rounded-full bg-[#E0F2FE] text-[#075985] font-bold border border-[#bae6fd]">
                                            Kependudukan Dukcapil
                                        </span>
                                    </div>
                                    <h3 class="font-headline text-lg sm:text-xl font-semibold text-[#1F2937] mb-1.5">
                                        Perekaman KTP-el &amp; Data Kependudukan
                                    </h3>
                                    <p class="text-sm text-[#374151] mb-4 leading-relaxed">
                                        Penerbitan KTP-el baru pemula (usia 17 tahun), penggantian KTP rusak/hilang, dan pemutakhiran elemen data Kartu Keluarga sesuai UU No. 24 Tahun 2013.
                                    </p>
                                    <dl class="space-y-2 bg-[#F0FAF8] p-3.5 rounded-xl mb-5 border border-[#CCECE6] text-xs">
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">schedule</span>
                                                Estimasi Waktu:
                                            </dt>
                                            <dd class="font-bold text-[#1F2937]">1 - 3 Hari Kerja</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">payments</span>
                                                Biaya Pelayanan:
                                            </dt>
                                            <dd class="font-bold text-[#166534]">Gratis Rp 0 (Bebas Pungli)</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <dt class="text-[#374151] flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px] text-[#0F766E]" aria-hidden="true">description</span>
                                                Berkas Wajib:
                                            </dt>
                                            <dd class="font-bold text-[#1F2937]">Fotokopi KK, Surat Kehilangan</dd>
                                        </div>
                                    </dl>
                                </div>
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'SOP-001']) }}"
                                    class="w-full min-h-[44px] py-2.5 px-4 rounded-xl bg-[#0F766E] hover:bg-[#115E59] active:bg-[#0d4d49] text-white font-bold text-sm flex items-center justify-center gap-2 transition-colors shadow-sm"
                                >
                                    <span>Pelajari SOP</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. Indeks Kebutuhan Hukum Warga (Real Database Metrics & Topic Coverage) -->
                <section id="indeks-topik" class="w-full bg-[#EBF7F5] py-8 sm:py-10 lg:py-12 border-y border-[#CCECE6] scroll-mt-24">
                    <div class="max-w-[1280px] mx-auto px-3 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-center">
                            <!-- Left Column: Context & Real Database Metrics -->
                            <div class="lg:col-span-5 flex flex-col gap-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] w-fit text-xs font-bold uppercase border border-[#bbf7d0]">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">bar_chart</span>
                                    <span>Indeks Basis Pengetahuan Hukum</span>
                                </div>
                                <h2 class="font-headline text-2xl sm:text-3xl font-semibold text-[#0F766E] tracking-tight">
                                    Cakupan Regulasi &amp; Topik Layanan Warga Terindeks
                                </h2>
                                <p class="text-sm sm:text-base text-[#374151] leading-relaxed">
                                    Data di samping merangkum distribusi pasal peraturan JDIH dan prosedur SOP layanan yang terindeks secara nyata pada basis data BHIMA. Klik salah satu topik untuk langsung berkonsultasi.
                                </p>
                                <!-- Real Database Stat Boxes -->
                                <div class="grid grid-cols-2 gap-3 sm:gap-4 mt-1">
                                    <div class="p-4 rounded-2xl bg-white shadow-sm border border-[#CCECE6]">
                                        <span class="font-headline text-2xl sm:text-3xl text-[#0F766E] font-bold block">{{ $portalMetrics['documents'] }}</span>
                                        <span class="text-xs sm:text-sm text-[#374151] font-medium">Dokumen JDIH &amp; SOP Terindeks</span>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-white shadow-sm border border-[#CCECE6]">
                                        <span class="font-headline text-2xl sm:text-3xl text-[#166534] font-bold block">{{ $portalMetrics['chunks'] }}</span>
                                        <span class="text-xs sm:text-sm text-[#374151] font-medium">Potongan Pasal &amp; Prosedur Aktif</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Interactive Topic Coverage Bars -->
                            <div class="lg:col-span-7 bg-white p-5 sm:p-7 rounded-2xl shadow-sm border border-[#CCECE6]">
                                <div class="flex items-center justify-between gap-2 pb-3 mb-4 bg-[#F0FAF8] px-4 py-2.5 rounded-xl border border-[#CCECE6]">
                                    <span class="text-xs sm:text-sm text-[#0F766E] font-bold">Berapa cakupan pasal terindeks per topik layanan warga?</span>
                                    <span class="text-xs text-[#374151] font-semibold shrink-0">Bagian Terindeks</span>
                                </div>
                                <div class="space-y-4">
                                    @foreach ($topicDistribution as $item)
                                        <a
                                            href="{{ route('consultation.index', ['q' => $item['query']]) }}"
                                            class="block group p-2.5 -mx-2.5 rounded-xl hover:bg-[#F0FAF8] transition-colors"
                                        >
                                            <div class="flex justify-between items-center gap-2 mb-1.5">
                                                <span class="text-xs sm:text-sm font-bold text-[#1F2937] group-hover:text-[#0F766E] transition-colors flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[#0F766E] text-[18px]" aria-hidden="true">{{ $item['icon'] }}</span>
                                                    <span>{{ $item['label'] }}</span>
                                                </span>
                                                <span class="text-xs sm:text-sm text-[#0F766E] font-bold shrink-0">{{ $item['count'] }} bagian</span>
                                            </div>
                                            <div class="w-full bg-[#E6F4F1] h-3.5 rounded-full overflow-hidden border border-[#CCECE6]/60">
                                                <div
                                                    class="bg-[#0F766E] h-full rounded-full"
                                                    style="width: {{ $item['percent'] }}%"
                                                ></div>
                                            </div>
                                            <div class="flex justify-between items-center gap-2 mt-1 text-[#374151] text-xs">
                                                <span>{{ $item['reference'] }}</span>
                                                <span class="text-[#0F766E] font-bold group-hover:underline">Konsultasikan topik ini</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. Civic Emergency & Rapid Contact Callout -->
                <section id="bantuan-darurat" class="w-full bg-[#F0FAF8] py-8 sm:py-10 lg:py-12">
                    <div class="max-w-[1280px] mx-auto px-3 sm:px-6 lg:px-8">
                        <div class="bg-[#115E59] text-white p-6 sm:p-8 lg:p-10 rounded-2xl shadow-lg border border-[#0F766E]">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                                <div class="md:col-span-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-xs font-bold text-[#CCFBF1] uppercase tracking-wider mb-3 border border-white/20">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">support_agent</span>
                                        <span>Bantuan Langsung Warga Bebas Biaya</span>
                                    </span>
                                    <h2 class="font-headline text-2xl sm:text-3xl font-semibold text-white mb-2">
                                        Butuh Pendampingan Darurat atau Mediasi Desa?
                                    </h2>
                                    <p class="text-sm sm:text-base text-[#CCFBF1] max-w-xl leading-relaxed">
                                        Hubungi petugas siaga kami melalui saluran bebas pulsa nasional atau ajukan konsultasi dan pengaduan resmi melalui portal ini tanpa dipungut biaya apa pun (Gratis).
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row md:flex-col gap-3 justify-end">
                                    <a
                                        href="tel:15024462"
                                        class="min-h-[48px] px-5 py-3 rounded-xl bg-white text-[#0F766E] hover:bg-teal-50 active:bg-teal-100 text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[20px] text-[#0F766E]" aria-hidden="true">call</span>
                                        <span>Bebas Pulsa: 150-BHIMA</span>
                                    </a>
                                    <a
                                        href="{{ route('consultation.index') }}"
                                        class="min-h-[48px] px-5 py-3 rounded-xl bg-[#0F766E] hover:bg-[#0b4844] active:bg-[#093835] text-white text-sm font-bold flex items-center justify-center gap-2 transition-colors border border-teal-300/40"
                                    >
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chat</span>
                                        <span>Buka Ruang Konsultasi</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full bg-[#115E59] text-white border-t border-teal-800 pt-10 pb-8">
            <div class="max-w-[1280px] mx-auto px-3 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 pb-8 border-b border-teal-800/80">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2.5">
                            <img
                                src="/images/bhima-logo.svg"
                                alt=""
                                width="32"
                                height="32"
                                loading="lazy"
                                class="h-8 w-8 object-contain bg-white/10 p-1 rounded-md"
                            >
                            <span class="font-headline text-xl font-semibold text-white">BHIMA</span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#CCFBF1] leading-relaxed">
                            Sistem keterbukaan informasi publik dan layanan pendampingan hukum warga berbasis regulasi JDIH dan SOP Layanan Terpadu.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2">
                        <h3 class="text-xs sm:text-sm text-white font-bold uppercase tracking-wider mb-1">Jejaring Nasional</h3>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="https://data.go.id" target="_blank" rel="noopener noreferrer">Portal Satu Data Indonesia</a>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="https://jdih.bphn.go.id" target="_blank" rel="noopener noreferrer">JDIH Nasional (BPHN)</a>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="https://sippn.menpan.go.id" target="_blank" rel="noopener noreferrer">SIPPN KemenPANRB</a>
                    </div>

                    <div class="flex flex-col gap-2">
                        <h3 class="text-xs sm:text-sm text-white font-bold uppercase tracking-wider mb-1">Akses Bantuan Warga</h3>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="{{ route('consultation.index') }}">Ruang Konsultasi Hukum Warga</a>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="{{ route('complaint.create') }}">Formulir Pengaduan Masyarakat</a>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="{{ route('complaint.track') }}">Pelacakan Status Tiket</a>
                    </div>

                    <div class="flex flex-col gap-2">
                        <h3 class="text-xs sm:text-sm text-white font-bold uppercase tracking-wider mb-1">Keterbukaan Publik</h3>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-003']) }}">Standar Layanan Informasi (UU 25/2009)</a>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-025']) }}">Keterbukaan Informasi Publik (UU 14/2008)</a>
                        <a class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-[#CCFBF1] hover:text-white underline-offset-2 hover:underline transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-019']) }}">Pelindungan Data Pribadi (UU 27/2022)</a>
                    </div>
                </div>

                <div class="pt-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-[#CCFBF1] text-xs">
                    <p>© {{ date('Y') }} Portal BHIMA. Seluruh Hak Cipta Dilindungi Undang-Undang Keterbukaan Informasi Publik Republik Indonesia.</p>
                    <div class="flex items-center gap-3">
                        <span class="font-bold bg-[#0b4844] text-[#CCFBF1] border border-teal-700/60 px-2.5 py-1 rounded">
                            Sesuai UU No. 14/2008 &amp; UU No. 27/2022
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
