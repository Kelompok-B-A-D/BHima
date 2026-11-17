<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shell-type" content="web_dashboard">
    <title>BHIMA - Konsultasi &amp; Asisten Hukum Virtual</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Newsreader:ital,opsz,wght@0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="stitch-consultation-body bg-[#F0FAF8] font-sans text-text-body antialiased selection:bg-primary-light selection:text-primary-dark">
    <div
        id="consultation-app"
        data-session-id="{{ $sessionUuid }}"
        data-max-length="{{ $maxChronologyLength }}"
        data-docs-count="{{ $knowledgeStats['documents'] }}"
        data-jdih-count="{{ $knowledgeStats['jdih'] }}"
        data-sop-count="{{ $knowledgeStats['sop'] }}"
        data-chunks-count="{{ $knowledgeStats['chunks'] }}"
    >
        <!-- Fixed Top Header -->
        <header class="stitch-no-print fixed top-0 left-0 right-0 z-50 bg-[#115E59] text-white shadow-md border-b border-[#0d4d49]">
            <div class="bg-[#0d4d49] text-white/90 h-8 px-3 sm:px-6 flex items-center">
                <div class="max-w-[1600px] w-full mx-auto flex items-center justify-between gap-3 text-[11px] sm:text-xs font-medium">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="flex flex-col w-4 h-2.5 overflow-hidden rounded-[1px] border border-white/40 shrink-0">
                            <div class="h-1/2 bg-red-600"></div>
                            <div class="h-1/2 bg-white"></div>
                        </div>
                        <span class="tracking-wide truncate">Portal Resmi Edukasi Hukum &amp; Keterbukaan Informasi Publik - Tingkat Desa / Kelurahan &amp; Kota</span>
                    </div>
                    <div class="hidden md:flex items-center gap-4 lg:gap-5 text-xs text-white/80 shrink-0">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-[#9cf2e8]">verified</span>
                            Pendampingan Warga Aktif
                        </span>
                        <span class="text-white/40">•</span>
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-[#9cf2e8]">headset_mic</span>
                            Hotline: 150-BHIMA
                        </span>
                    </div>
                </div>
            </div>
            <div class="h-16 sm:h-20 max-w-[1600px] mx-auto px-3 sm:px-6 flex items-center justify-between gap-2 sm:gap-4">
                <div class="flex items-center gap-2 sm:gap-3.5 min-w-0">
                    <button
                        type="button"
                        id="mobile-menu-btn"
                        aria-label="Buka Menu Navigasi"
                        aria-expanded="false"
                        aria-controls="consult-sidebar"
                        class="xl:hidden w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-colors shrink-0 cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[22px]" id="mobile-menu-icon">menu</span>
                    </button>
                    <a href="{{ route('consultation.home') }}" class="flex items-center gap-2.5 sm:gap-3.5 text-white no-underline min-w-0">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-[#9cf2e8] shadow-sm backdrop-blur-sm shrink-0">
                            <span class="material-symbols-outlined text-[22px] sm:text-[28px]">balance</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-headline text-lg sm:text-2xl font-bold text-white tracking-tight leading-tight truncate">BHIMA</span>
                            <span class="text-[10px] sm:text-xs text-[#CCECE6] font-medium tracking-wide truncate">Bantuan Hukum &amp; Informasi Masyarakat</span>
                        </div>
                    </a>
                </div>
                <nav class="hidden xl:flex items-center gap-1.5 text-sm font-semibold text-white/90" aria-label="Navigasi Utama Portal">
                    <a class="hover:text-white hover:bg-white/10 transition-colors px-3 py-2 rounded-lg" href="{{ route('consultation.home') }}">Beranda</a>
                    <a class="hover:text-white hover:bg-white/10 transition-colors px-3 py-2 rounded-lg" href="#quick-scenarios">Layanan</a>
                    <a class="hover:text-white hover:bg-white/10 transition-colors px-3 py-2 rounded-lg" href="#jdih-reference-panel">Peraturan JDIH</a>
                    <a aria-current="page" class="transition-all bg-[#0F766E] text-white font-bold rounded-lg px-3.5 py-2 shadow-sm border border-[#2dd4bf]/30" href="{{ route('consultation.index') }}">Asisten Hukum</a>
                    <a class="hover:text-white hover:bg-white/10 transition-colors px-3 py-2 rounded-lg" href="#posbakum-panel">Posbakum</a>
                    <a class="hover:text-white hover:bg-white/10 transition-colors px-3 py-2 rounded-lg" href="{{ route('login') }}">Portal Petugas</a>
                </nav>
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a class="flex items-center gap-1.5 bg-[#F59E0B] hover:bg-[#D97706] text-[#111827] font-bold text-xs sm:text-sm px-2.5 sm:px-4 py-2 rounded-lg shadow-sm hover:shadow transition-all active:scale-[0.98]" href="#chronology-input">
                        <span class="material-symbols-outlined text-[18px] sm:text-[20px] text-[#111827]">campaign</span>
                        <span class="hidden sm:inline">Lapor / Konsultasi</span>
                        <span class="sm:hidden">Konsultasi</span>
                    </a>
                    <a href="{{ route('login') }}" title="Portal Petugas BHIMA" class="w-9 h-9 rounded-full bg-white/15 border border-white/25 flex items-center justify-center text-white hover:bg-white/25 transition-colors shrink-0">
                        <span class="material-symbols-outlined text-[20px]">person</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Mobile & Tablet Backdrop Scrim -->
        <div
            id="mobile-nav-backdrop"
            hidden
            class="stitch-no-print xl:hidden fixed inset-0 top-[96px] sm:top-[112px] bg-[#0d233c]/60 backdrop-blur-[2px] z-30 transition-opacity"
        ></div>

        <!-- Left Sidebar (Slide-over Drawer on Mobile/Tablet, Fixed on Desktop xl+) -->
        <aside
            id="consult-sidebar"
            class="stitch-no-print fixed left-0 top-[96px] sm:top-[112px] bottom-0 w-72 max-w-[85vw] bg-[#115E59] border-r border-[#0d4d49] z-40 flex flex-col pt-4 sm:pt-5 pb-6 text-white overflow-y-auto -translate-x-full xl:translate-x-0 transition-transform duration-200 ease-in-out shadow-2xl xl:shadow-none"
        >
            <div class="px-4 mb-4">
                <div class="p-3.5 bg-[#0d4d49]/70 rounded-xl border border-white/10 shadow-inner">
                    <div class="text-sm font-bold text-white flex items-center justify-between gap-2">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-[#9cf2e8]">smart_toy</span>
                            Konsultasi Virtual BHIMA
                        </span>
                        <button
                            type="button"
                            id="mobile-nav-close"
                            aria-label="Tutup Menu"
                            class="xl:hidden w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                    <p class="text-xs text-[#CCECE6] mt-1.5 leading-relaxed">
                        Tanyakan hak hukum, sertifikasi tanah, ketenagakerjaan, layanan kependudukan, atau regulasi daerah secara langsung.
                    </p>
                </div>
            </div>
            <nav class="flex-1 px-3 space-y-1 text-sm font-semibold" aria-label="Menu Samping Konsultasi">
                <a aria-current="page" data-drawer-link class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all bg-white text-[#115E59] font-bold shadow-sm" href="#chat-messages">
                    <span class="material-symbols-outlined text-[20px] text-[#0F766E]">chat</span>
                    Sesi Konsultasi Aktif
                </a>
                <a data-drawer-link class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#CCECE6] hover:bg-white/10 hover:text-white transition-colors" href="#quick-scenarios">
                    <span class="material-symbols-outlined text-[20px]">menu_book</span>
                    Topik Panduan Hukum
                </a>
                <a data-drawer-link class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#CCECE6] hover:bg-white/10 hover:text-white transition-colors" href="#jdih-reference-panel">
                    <span class="material-symbols-outlined text-[20px]">history_edu</span>
                    Indeks Rujukan JDIH &amp; SOP
                </a>
                <a data-drawer-link class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#CCECE6] hover:bg-white/10 hover:text-white transition-colors" href="#posbakum-panel">
                    <span class="material-symbols-outlined text-[20px]">gavel</span>
                    Hubungi Advokat Posbakum
                </a>
                <div class="xl:hidden pt-3 mt-3 border-t border-white/10">
                    <span class="px-3.5 text-[11px] font-bold uppercase tracking-wider text-[#9cf2e8]/80 block mb-1">Akses Portal</span>
                    <a data-drawer-link class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#CCECE6] hover:bg-white/10 hover:text-white transition-colors" href="{{ route('login') }}">
                        <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                        Portal Petugas BHIMA
                    </a>
                </div>
            </nav>

            <!-- Guardrail Info Box in Sidebar -->
            <div class="px-4 mb-4 mt-4">
                <div class="p-3.5 bg-[#0d4d49]/50 rounded-xl border border-white/10 text-xs text-[#CCECE6] space-y-1.5">
                    <div class="font-bold text-[#9cf2e8] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">verified_user</span>
                        Prinsip Layanan RAG AI
                    </div>
                    <p class="leading-relaxed text-[11px] text-[#CCECE6]/90">
                        Jawaban disusun <strong>hanya</strong> dari <span id="kb-badge-text">{{ $knowledgeStats['documents'] ?: 100 }} Dokumen JDIH &amp; SOP</span> terindeks. Asisten tidak memberikan vonis perkara.
                    </p>
                </div>
            </div>

            <div class="px-5 pt-3 border-t border-white/10 text-xs text-[#CCECE6]">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#34D399] animate-pulse"></span>
                    <span class="font-medium">Sistem Terenkripsi &amp; Anonim</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="xl:pl-72 stitch-print-full">
            <main class="relative pt-[96px] sm:pt-[112px] bg-[#F0FAF8] min-h-screen stitch-print-full">
                <div class="flex flex-col w-full">
                    <div class="p-3 sm:p-6 md:p-8 max-w-[1440px] mx-auto w-full">
                        <!-- Top Action Banner / Status Strip -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3.5 sm:gap-4 mb-4 sm:mb-6 bg-white p-4 sm:p-5 rounded-2xl border border-[#CCECE6] shadow-[0_2px_8px_rgba(15,118,110,0.04)]">
                            <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-[#0F766E] flex items-center justify-center text-white shadow-sm shrink-0 mt-0.5 sm:mt-0">
                                    <span class="material-symbols-outlined text-[22px] sm:text-[26px]">gavel</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                                        <h1 class="font-headline text-lg sm:text-xl md:text-2xl font-bold text-text-dark tracking-tight">Asisten Hukum Virtual BHIMA</h1>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-[#DCFCE7] text-[#166534] text-[11px] sm:text-xs font-bold border border-[#BBF7D0]">
                                            <span class="w-2 h-2 rounded-full bg-[#16A34A] animate-pulse shrink-0"></span>
                                            <span>Aktif 24 Jam • Regulasi JDIH</span>
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-text-muted mt-1 leading-relaxed">
                                        Ruang Konsultasi Hukum Warga — Sistem verifikasi pasal &amp; panduan administratif warga otomatis berbasis indeks {{ $knowledgeStats['documents'] ?: 100 }} dokumen JDIH dan SOP layanan.
                                    </p>
                                </div>
                            </div>
                            <div class="stitch-no-print grid grid-cols-2 sm:flex items-center gap-2 sm:gap-2.5 w-full lg:w-auto shrink-0 pt-1 lg:pt-0">
                                <button
                                    type="button"
                                    id="reset-session-btn"
                                    class="flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 bg-[#E6F5F2] hover:bg-[#CCECE6] text-[#0F766E] font-semibold text-xs sm:text-sm rounded-xl transition-colors border border-[#CCECE6] cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[17px] sm:text-[18px]">restart_alt</span>
                                    <span>Topik Baru</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.print()"
                                    class="flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 bg-white hover:bg-[#F3F4F6] text-text-body font-semibold text-xs sm:text-sm rounded-xl transition-colors border border-[#E2E8F0] cursor-pointer"
                                >
                                    <span class="material-symbols-outlined text-[17px] sm:text-[18px]">print</span>
                                    <span>Cetak Riwayat</span>
                                </button>
                            </div>
                        </div>

                        <!-- Main 2-Column Responsive Layout -->
                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 sm:gap-6 items-start">
                            <!-- Left / Center Column: Chat & Legal Analysis Engine (8 Cols) -->
                            <section
                                class="xl:col-span-8 flex flex-col bg-white rounded-2xl border border-[#CCECE6] shadow-[0_4px_16px_rgba(15,118,110,0.05)] overflow-hidden min-w-0"
                                aria-label="Ruang Konsultasi Hukum Warga"
                            >
                                <!-- Chat Header Meta -->
                                <div class="px-3.5 sm:px-6 py-2.5 sm:py-3.5 bg-[#E6F5F2] border-b border-[#CCECE6] flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs font-semibold text-text-muted flex-wrap">
                                        <span class="material-symbols-outlined text-[#0F766E] text-[16px] sm:text-[18px]">security</span>
                                        <span>ID Sesi: <strong id="session-id-label" class="text-text-dark">#BHM-{{ strtoupper(substr($sessionUuid, 0, 8)) }}</strong></span>
                                        <span class="hidden md:inline text-[#9CA3AF]">•</span>
                                        <span class="hidden md:inline">Tingkat Kerahasiaan: Anonim &amp; Terenkripsi</span>
                                    </div>
                                    <span class="text-[#0F766E] text-[11px] sm:text-xs font-bold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px] sm:text-[16px] text-[#059669]">check_circle</span>
                                        <span>Indeks: <span id="stat-total-docs">{{ $knowledgeStats['documents'] ?: 100 }} Dokumen</span> (<span id="stat-total-chunks">{{ $knowledgeStats['chunks'] ?: 301 }} Chunk</span>)</span>
                                    </span>
                                </div>

                                <!-- Scrollable Conversation Feed -->
                                <div
                                    id="chat-messages"
                                    class="p-3.5 sm:p-6 md:p-8 space-y-5 sm:space-y-7 overflow-y-auto max-h-[62vh] sm:max-h-[680px] lg:max-h-[720px] bg-[#FAFDFD]"
                                    role="log"
                                    aria-live="polite"
                                    aria-label="Daftar pesan percakapan"
                                >
                                    <!-- Timestamp Marker -->
                                    <div class="flex items-center justify-center my-0.5 sm:my-1 welcome-bubble">
                                        <span class="px-3 py-1 bg-[#F3F4F6] border border-[#E5E7EB] rounded-full text-[11px] sm:text-xs font-medium text-text-muted text-center">
                                            Sesi Konsultasi Aktif • Bahasa Indonesia Awam &amp; Transparan
                                        </span>
                                    </div>

                                    <!-- Initial Welcome Assistant Message & Sample Preview -->
                                    <article class="chat-bubble welcome-bubble flex flex-col items-start gap-2 pr-1 sm:pr-8">
                                        <div class="flex items-center gap-1.5 sm:gap-2 mb-0.5 flex-wrap">
                                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-[#0F766E] text-white flex items-center justify-center shadow-sm shrink-0">
                                                <span class="material-symbols-outlined text-[15px] sm:text-[17px]">smart_toy</span>
                                            </div>
                                            <span class="text-xs sm:text-sm font-bold text-text-dark">Asisten Hukum Virtual BHIMA</span>
                                            <span class="px-2 sm:px-2.5 py-0.5 bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] rounded-full text-[10px] sm:text-xs font-bold">
                                                Penalaran Peraturan Terverifikasi
                                            </span>
                                        </div>
                                        <div class="inline-block w-fit bg-white text-text-body rounded-2xl rounded-tl-sm px-3.5 py-3 sm:px-4 sm:py-3.5 border border-[#E2E8F0] shadow-sm max-w-[95%] sm:max-w-xl space-y-2 text-xs sm:text-sm leading-relaxed">
                                            <p>
                                                Halo Bapak/Ibu Warga. Selamat datang di <strong>Ruang Konsultasi Hukum Warga BHIMA</strong>. Silakan ceritakan kronologi permasalahan hukum atau kendala layanan publik yang Anda alami pada kolom di bawah.
                                            </p>
                                            <p class="text-text-muted text-[11px] sm:text-xs">
                                                Sistem akan mencocokkan kronologi Anda dengan <strong>{{ $knowledgeStats['jdih'] ?: 80 }} Peraturan JDIH</strong> &amp; <strong>{{ $knowledgeStats['sop'] ?: 20 }} SOP Layanan</strong> beserta sumber rujukan yang dapat diklik.
                                            </p>
                                        </div>
                                    </article>
                                </div>

                                <!-- Loading Indicator -->
                                <div
                                    id="chat-loading"
                                    hidden
                                    role="status"
                                    aria-live="assertive"
                                    class="mx-3.5 sm:mx-6 my-2.5 sm:my-3 p-3.5 sm:p-4 bg-[#E6F5F2] border border-[#CCECE6] rounded-2xl flex items-center gap-3 text-[#0F766E]"
                                >
                                    <div class="w-5 h-5 rounded-full border-2 border-[#0F766E]/30 border-t-[#0F766E] animate-spin shrink-0"></div>
                                    <div class="text-xs sm:text-sm min-w-0">
                                        <strong class="block font-bold text-text-dark">Sedang mencocokkan pasal JDIH &amp; SOP layanan…</strong>
                                        <span class="text-text-muted text-[11px] sm:text-xs">Meringkas kronologi → menghitung kemiripan semantik &amp; kata kunci → memverifikasi sitasi pasal</span>
                                    </div>
                                </div>

                                <!-- Error Banner -->
                                <div
                                    id="chat-error"
                                    hidden
                                    role="alert"
                                    class="mx-3.5 sm:mx-6 my-2.5 sm:my-3 p-3.5 sm:p-4 bg-[#FEE2E2] border border-[#FECACA] rounded-xl text-xs sm:text-sm font-semibold text-[#991B1B]"
                                ></div>

                                <!-- Sticky Interaction Input Dock -->
                                <div class="stitch-no-print p-3 sm:p-4 md:p-5 bg-white border-t border-[#CCECE6] space-y-2.5 sm:space-y-3">
                                    <!-- Suggested Quick Topics Chips -->
                                    <div id="quick-scenarios" class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1.5 text-nowrap -mx-1 px-1">
                                        <span class="text-[11px] sm:text-xs font-bold text-text-muted uppercase shrink-0">Saran Cepat:</span>
                                        <button
                                            type="button"
                                            data-scenario="Tetangga menggeser patok batas tanah pekarangan keluarga kami secara sepihak sejauh satu meter padahal kami memegang sertifikat hak milik (SHM) yang sah. Bagaimana prosedur mediasi sengketa batas tanah ini?"
                                            class="quick-chip px-2.5 sm:px-3 py-1.5 bg-[#F0FAF8] hover:bg-[#CCECE6] text-[#0F766E] border border-[#CCECE6] rounded-full text-[11px] sm:text-xs font-semibold transition-colors flex items-center gap-1 cursor-pointer shrink-0"
                                        >
                                            <span class="material-symbols-outlined text-[14px] sm:text-[15px]">fence</span>
                                            Sengketa tanah batas pekarangan
                                        </button>
                                        <button
                                            type="button"
                                            data-scenario="Saya mengurus pembuatan KTP elektronik yang hilang di kantor pelayanan Disdukcapil, sudah membawa KK dan surat kehilangan kepolisian, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat selesai. Apa aturan hukumnya dan langkah yang harus saya lakukan?"
                                            class="quick-chip px-2.5 sm:px-3 py-1.5 bg-[#F0FAF8] hover:bg-[#CCECE6] text-[#0F766E] border border-[#CCECE6] rounded-full text-[11px] sm:text-xs font-semibold transition-colors flex items-center gap-1 cursor-pointer shrink-0"
                                        >
                                            <span class="material-symbols-outlined text-[14px] sm:text-[15px]">badge</span>
                                            Pungutan liar pengurusan KTP-el
                                        </button>
                                        <button
                                            type="button"
                                            data-scenario="Adik saya mengalami kecelakaan dan dibawa ke IGD rumah sakit terdekat dalam keadaan gawat darurat, namun pihak rumah sakit meminta uang muka deposit Rp5 juta sebelum tindakan medis dan menolak kartu BPJS Kesehatan. Bagaimana aturannya?"
                                            class="quick-chip px-2.5 sm:px-3 py-1.5 bg-[#F0FAF8] hover:bg-[#CCECE6] text-[#0F766E] border border-[#CCECE6] rounded-full text-[11px] sm:text-xs font-semibold transition-colors flex items-center gap-1 cursor-pointer shrink-0"
                                        >
                                            <span class="material-symbols-outlined text-[14px] sm:text-[15px]">medical_services</span>
                                            Penolakan pasien darurat IGD
                                        </button>
                                        <button
                                            type="button"
                                            data-scenario="Saya bekerja di perusahaan selama 3 tahun, bulan ini di-PHK secara sepihak tanpa perundingan, gaji bulan terakhir belum dibayar, dan ijazah asli saya masih ditahan perusahaan. Apa hak hukum saya?"
                                            class="quick-chip px-2.5 sm:px-3 py-1.5 bg-[#F0FAF8] hover:bg-[#CCECE6] text-[#0F766E] border border-[#CCECE6] rounded-full text-[11px] sm:text-xs font-semibold transition-colors flex items-center gap-1 cursor-pointer shrink-0"
                                        >
                                            <span class="material-symbols-outlined text-[14px] sm:text-[15px]">work</span>
                                            PHK sepihak &amp; ijazah ditahan
                                        </button>
                                        <button
                                            type="button"
                                            data-scenario="Jalan kabupaten di desa kami rusak berlubang parah dan belum diberi rambu peringatan sehingga warga sering terjatuh saat malam hari, serta drainase jalan tersumbat menyebabkan banjir. Ke mana dan bagaimana cara melaporkannya?"
                                            class="quick-chip px-2.5 sm:px-3 py-1.5 bg-[#F0FAF8] hover:bg-[#CCECE6] text-[#0F766E] border border-[#CCECE6] rounded-full text-[11px] sm:text-xs font-semibold transition-colors flex items-center gap-1 cursor-pointer shrink-0"
                                        >
                                            <span class="material-symbols-outlined text-[14px] sm:text-[15px]">add_road</span>
                                            Jalan rusak &amp; drainase banjir
                                        </button>
                                    </div>

                                    <!-- Input Bar -->
                                    <form
                                        id="consultation-form"
                                        class="flex flex-col sm:flex-row sm:items-end gap-2 bg-[#F0FAF8] border border-[#CCECE6] p-2.5 sm:p-3 rounded-2xl focus-within:border-[#0F766E] focus-within:ring-2 focus-within:ring-[#0F766E]/20 transition-all"
                                        novalidate
                                    >
                                        <div class="flex items-start sm:items-end gap-1.5 flex-1 w-full min-w-0">
                                            <button
                                                type="button"
                                                onclick="document.getElementById('chronology-input')?.focus()"
                                                class="hidden sm:inline-flex p-2 text-text-muted hover:text-[#0F766E] hover:bg-white rounded-xl transition-colors cursor-pointer shrink-0"
                                                title="Tulis Kronologi Perkara Warga"
                                            >
                                                <span class="material-symbols-outlined text-[22px]">edit_note</span>
                                            </button>
                                            <label for="chronology-input" class="sr-only">Kronologi Permasalahan Hukum Warga</label>
                                            <textarea
                                                id="chronology-input"
                                                name="chronology"
                                                rows="3"
                                                maxlength="{{ $maxChronologyLength }}"
                                                required
                                                class="w-full bg-transparent border-0 resize-none text-xs sm:text-sm text-text-dark placeholder:text-text-muted/70 focus:outline-none p-1 sm:p-1.5 max-h-40"
                                                placeholder="Ketik pertanyaan hukum, kronologi perkara warga, atau kendala pelayanan publik Anda di sini..."
                                            ></textarea>
                                        </div>
                                        <button
                                            type="submit"
                                            id="send-btn"
                                            class="w-full sm:w-auto flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2.5 bg-[#0F766E] hover:bg-[#115E59] disabled:opacity-50 text-white rounded-xl font-bold text-xs sm:text-sm transition-all shadow-sm shrink-0 active:scale-95 cursor-pointer"
                                        >
                                            <span>Kirim Pertanyaan</span>
                                            <span class="material-symbols-outlined text-[17px] sm:text-[18px]">send</span>
                                        </button>
                                    </form>
                                    <div class="flex flex-wrap items-center justify-between text-text-muted text-[11px] sm:text-xs px-1 gap-1.5">
                                        <span class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px] text-[#0F766E] shrink-0">lock</span>
                                            <span>Dilindungi UU Pelindungan Data Pribadi No. 27/2022</span>
                                        </span>
                                        <div class="flex items-center gap-2 sm:gap-3 ml-auto">
                                            <span id="char-counter" class="font-semibold text-[#0F766E]">0 / {{ $maxChronologyLength }} karakter</span>
                                            <span class="hidden md:inline">• Ctrl+Enter untuk kirim cepat</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Right Column: Statutory References & POSBAKUM Panel (4 Cols on Desktop, 2-Col Grid on Tablet) -->
                            <aside class="xl:col-span-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-1 gap-4 sm:gap-6 items-start" aria-label="Dokumen Rujukan JDIH dan Posbakum">
                                <!-- JDIH Document References Box -->
                                <div
                                    id="jdih-reference-panel"
                                    class="md:col-span-2 xl:col-span-1 bg-white rounded-2xl border border-[#CCECE6] shadow-[0_2px_12px_rgba(15,118,110,0.04)] p-4 sm:p-5 md:p-6 space-y-4"
                                >
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-[#E2E8F0]">
                                        <div>
                                            <div class="flex items-center gap-2 text-[#0F766E]">
                                                <span class="material-symbols-outlined text-[22px] sm:text-[24px]">auto_stories</span>
                                                <h2 class="font-headline text-lg sm:text-xl font-bold text-text-dark">Dokumen Rujukan JDIH</h2>
                                            </div>
                                            <p class="text-xs text-text-muted mt-1">
                                                Kompilasi berkas perundangan &amp; SOP valid terhubung dengan topik percakapan aktif.
                                            </p>
                                        </div>
                                        <span class="px-2 sm:px-2.5 py-1 bg-[#E0F2FE] text-[#0369A1] text-[10px] sm:text-xs font-bold rounded-md uppercase tracking-wider shrink-0">
                                            JDIH Terpadu
                                        </span>
                                    </div>

                                    <div id="jdih-sidebar-list" class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-1 gap-3.5 items-stretch">
                                        <!-- Document Item 1 -->
                                        <div class="p-3.5 bg-[#F0FAF8] rounded-xl border border-[#CCECE6] hover:border-[#0F766E] transition-all flex flex-col justify-between gap-2.5">
                                            <div class="flex items-start gap-3">
                                                <div class="w-9 h-9 rounded-lg bg-[#FEE2E2] text-[#991B1B] flex items-center justify-center shrink-0 mt-0.5 border border-[#FECACA]">
                                                    <span class="material-symbols-outlined text-[20px]">picture_as_pdf</span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-xs text-[#0F766E] font-bold block uppercase tracking-wider">UU No. 24 Tahun 2013</span>
                                                    <h3 class="text-sm text-text-dark font-semibold truncate" title="Undang-Undang Administrasi Kependudukan">
                                                        Administrasi Kependudukan (Adminduk)
                                                    </h3>
                                                    <div class="flex items-center gap-1.5 mt-1 text-text-muted text-[11px] sm:text-xs flex-wrap">
                                                        <span class="px-2 py-0.5 bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] rounded font-bold">Status: Berlaku</span>
                                                        <span>•</span>
                                                        <span>Pasal 79A &amp; 95B</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 pt-1">
                                                <a
                                                    href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="flex-1 py-1.5 px-2 rounded-lg bg-white border border-[#CCECE6] hover:bg-[#E6F5F2] text-[#0F766E] font-semibold text-xs flex items-center justify-center gap-1 transition-colors"
                                                >
                                                    <span class="material-symbols-outlined text-[16px]">visibility</span> Baca Pasal
                                                </a>
                                                <a
                                                    href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="py-1.5 px-3 rounded-lg bg-[#0F766E] hover:bg-[#115E59] text-white font-semibold text-xs flex items-center justify-center gap-1 shadow-sm transition-colors"
                                                >
                                                    <span class="material-symbols-outlined text-[16px]">open_in_new</span> Rujukan
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Document Item 2 -->
                                        <div class="p-3.5 bg-[#F0FAF8] rounded-xl border border-[#CCECE6] hover:border-[#0F766E] transition-all flex flex-col justify-between gap-2.5">
                                            <div class="flex items-start gap-3">
                                                <div class="w-9 h-9 rounded-lg bg-[#E0F2FE] text-[#0369A1] flex items-center justify-center shrink-0 mt-0.5 border border-[#BAE6FD]">
                                                    <span class="material-symbols-outlined text-[20px]">description</span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-xs text-[#0F766E] font-bold block uppercase tracking-wider">SOP Layanan Publik</span>
                                                    <h3 class="text-sm text-text-dark font-semibold truncate" title="SOP Pelaporan Pungutan Liar &amp; Pengaduan Layanan">
                                                        SOP Pelaporan Pungli &amp; Mediasi Warga
                                                    </h3>
                                                    <div class="flex items-center gap-1.5 mt-1 text-text-muted text-[11px] sm:text-xs flex-wrap">
                                                        <span>Inspektorat &amp; Ombudsman</span>
                                                        <span>•</span>
                                                        <span>SOP-002</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 pt-1">
                                                <a
                                                    href="{{ route('consultation.reference', ['key' => 'SOP-002']) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="w-full py-1.5 px-2 rounded-lg bg-white border border-[#CCECE6] hover:bg-[#E6F5F2] text-[#0F766E] font-semibold text-xs flex items-center justify-center gap-1 transition-colors"
                                                >
                                                    <span class="material-symbols-outlined text-[16px]">account_tree</span> Lihat Alur Prosedur Layanan
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Document Item 3 -->
                                        <div class="p-3.5 bg-[#F0FAF8] rounded-xl border border-[#CCECE6] hover:border-[#0F766E] transition-all flex flex-col justify-between gap-2.5">
                                            <div class="flex items-start gap-3">
                                                <div class="w-9 h-9 rounded-lg bg-[#F3F4F6] text-text-muted flex items-center justify-center shrink-0 mt-0.5 border border-[#E5E7EB]">
                                                    <span class="material-symbols-outlined text-[20px]">article</span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-xs text-text-muted font-bold block uppercase tracking-wider">PP No. 24 Tahun 1997</span>
                                                    <h3 class="text-sm text-text-dark font-semibold truncate" title="Peraturan Pemerintah tentang Pendaftaran Tanah">
                                                        Pendaftaran Tanah &amp; Mediasi Sengketa Batas
                                                    </h3>
                                                    <div class="flex items-center gap-1.5 mt-1 text-text-muted text-[11px] sm:text-xs flex-wrap">
                                                        <span>Pertanahan &amp; Agraria</span>
                                                        <span>•</span>
                                                        <span>JDIH-005</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 pt-1">
                                                <a
                                                    href="{{ route('consultation.reference', ['key' => 'JDIH-005']) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="w-full py-1.5 px-2 rounded-lg bg-white border border-[#CCECE6] hover:bg-[#E6F5F2] text-text-body font-semibold text-xs flex items-center justify-center gap-1 transition-colors"
                                                >
                                                    <span class="material-symbols-outlined text-[16px]">menu_book</span> Buka Detail Pasal
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <a
                                        class="block text-center py-2 text-[#0F766E] font-bold text-xs hover:text-[#115E59] hover:underline"
                                        href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Jelajahi Basis Data {{ $knowledgeStats['documents'] ?: 100 }} Dokumen JDIH &amp; SOP →
                                    </a>
                                </div>

                                <!-- POSBAKUM Legal Aid Desk Card -->
                                <div
                                    id="posbakum-panel"
                                    class="bg-white rounded-2xl border-2 border-[#0F766E] shadow-[0_4px_16px_rgba(15,118,110,0.06)] p-4 sm:p-5 md:p-6 flex flex-col justify-between gap-4 h-full"
                                >
                                    <div class="space-y-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-[#0F766E] text-white flex items-center justify-center shadow-sm shrink-0">
                                                <span class="material-symbols-outlined text-[20px]">gavel</span>
                                            </div>
                                            <div>
                                                <h2 class="font-headline text-base sm:text-lg font-bold text-text-dark">Pos Bantuan Hukum (Posbakum)</h2>
                                                <span class="text-xs text-[#0F766E] font-bold">Layanan Advokasi Pro Bono (Gratis)</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-text-muted leading-relaxed">
                                            Warga kurang mampu atau menghadapi perkara mendesak berhak memperoleh pendampingan pengacara secara gratis di pengadilan negeri setempat sesuai UU No. 16 Tahun 2011 tentang Bantuan Hukum.
                                        </p>
                                        <div class="space-y-2.5 pt-2 border-t border-[#E2E8F0]">
                                            <div class="flex items-start gap-2 text-text-body text-xs">
                                                <span class="material-symbols-outlined text-[#0F766E] text-[18px] shrink-0 mt-0.5">location_on</span>
                                                <span>Gedung Pengadilan Negeri Kelas 1A, Jl. Veteran No. 18</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-text-body text-xs">
                                                <span class="material-symbols-outlined text-[#0F766E] text-[18px] shrink-0">call</span>
                                                <span class="font-bold text-text-dark">Hotline Khusus: (021) 8849-0112</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-text-body text-xs">
                                                <span class="material-symbols-outlined text-[#4B5563] text-[18px] shrink-0">schedule</span>
                                                <span class="text-text-muted">Senin - Jumat: 08.30 - 15.00 WIB</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-1">
                                        <a
                                            class="w-full flex items-center justify-center gap-2 bg-[#0F766E] hover:bg-[#115E59] text-white py-2.5 px-3 sm:px-4 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition-all text-center"
                                            href="{{ route('consultation.reference', ['key' => 'JDIH-007']) }}"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <span class="material-symbols-outlined text-[18px] shrink-0">contact_page</span>
                                            <span>Panduan &amp; Syarat Bantuan Hukum Gratis</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Real-Time Metrics & Citizen Trust Card -->
                                <div class="bg-[#115E59] text-white rounded-2xl p-4 sm:p-5 md:p-6 flex flex-col justify-between gap-4 shadow-sm border border-[#0d4d49] h-full">
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-[#9cf2e8] uppercase tracking-wider font-bold">Transparansi Penanganan</span>
                                            <span class="material-symbols-outlined text-[20px] text-[#9cf2e8]">shield_with_heart</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 pt-1">
                                            <div class="bg-white/10 p-3 sm:p-3.5 rounded-xl border border-white/10">
                                                <span class="font-headline text-xl sm:text-2xl font-bold text-[#9cf2e8] block">{{ $knowledgeStats['documents'] ?: 100 }}</span>
                                                <span class="text-[11px] sm:text-xs text-[#CCECE6]">Dokumen JDIH &amp; SOP</span>
                                            </div>
                                            <div class="bg-white/10 p-3 sm:p-3.5 rounded-xl border border-white/10">
                                                <span class="font-headline text-xl sm:text-2xl font-bold text-[#9cf2e8] block">{{ $knowledgeStats['chunks'] ?: 301 }}</span>
                                                <span class="text-[11px] sm:text-xs text-[#CCECE6]">Indeks Pasal Terverifikasi</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-[#CCECE6] leading-relaxed pt-1">
                                        Dikelola bersama Bagian Hukum Sekretariat Daerah, Dinas Kominfo, &amp; Organisasi Bantuan Hukum Terakreditasi.
                                    </p>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
