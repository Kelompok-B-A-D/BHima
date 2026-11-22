<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shell-type" content="web_standard">
    <title>BHIMA - Asisten Hukum Virtual</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="stitch-consultation-body has-mobile-tabbar min-h-screen overflow-x-hidden bg-[#E9EEF5] font-sans text-[#0B1020] antialiased selection:bg-[#E6F0FC] selection:text-[#0B1020]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-full focus:bg-[#0066CC] focus:text-white focus:font-bold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <div
        id="consultation-app"
        class="w-full overflow-x-hidden"
        data-session-id="{{ $sessionUuid }}"
        data-max-length="{{ $maxChronologyLength }}"
        data-docs-count="{{ $knowledgeStats['documents'] }}"
        data-jdih-count="{{ $knowledgeStats['jdih'] }}"
        data-sop-count="{{ $knowledgeStats['sop'] }}"
        data-chunks-count="{{ $knowledgeStats['chunks'] }}"
    >
        @include('partials.liquid-header', ['activeNav' => 'asisten-hukum'])

        <main id="portal-main-content" class="w-full pt-24 min-[641px]:pt-28 pb-10 min-[641px]:pb-14 bg-transparent relative min-h-[calc(100vh-280px)] stitch-print-full">
            <div class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 space-y-6">
                <!-- Context Header Bar -->
                <div class="surface-card flex flex-col min-[1008px]:flex-row min-[1008px]:items-center justify-between gap-4 p-5 min-[641px]:p-6">
                    <div class="flex items-start min-[641px]:items-center gap-3.5 min-w-0">
                        <div class="w-12 h-12 rounded-[16px] bg-[#0066CC] flex items-center justify-center text-white shadow-[0_6px_16px_rgba(0,102,204,0.22)] shrink-0">
                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">gavel</span>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h1 class="type-h2 text-[#0B1020]">Asisten Hukum Virtual BHIMA</h1>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] text-xs font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#0F766E] shrink-0" aria-hidden="true"></span>
                                    <span>Aktif · Regulasi JDIH</span>
                                </span>
                            </div>
                            <p class="text-xs min-[641px]:text-sm text-[#4A5366] leading-relaxed">
                                <strong class="text-[#0B1020]">Ruang Konsultasi Hukum Warga:</strong> Penelusuran pasal dan panduan prosedur otomatis berbasis {{ $knowledgeStats['documents'] }} dokumen JDIH dan SOP layanan resmi.
                            </p>
                        </div>
                    </div>

                    <div class="stitch-no-print grid grid-cols-2 min-[641px]:flex items-center gap-2.5 w-full min-[1008px]:w-auto shrink-0">
                        <button
                            type="button"
                            id="reset-session-btn"
                            class="btn-glass !min-h-[44px] flex items-center justify-center gap-1.5 px-4 py-2 text-xs min-[641px]:text-sm"
                        >
                            <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">restart_alt</span>
                            <span>Topik Baru</span>
                        </button>
                        <button
                            type="button"
                            onclick="window.print()"
                            class="btn-glass !min-h-[44px] flex items-center justify-center gap-1.5 px-4 py-2 text-xs min-[641px]:text-sm"
                        >
                            <span class="material-symbols-outlined text-[18px] text-[#333E52]" aria-hidden="true">print</span>
                            <span>Cetak Riwayat</span>
                        </button>
                    </div>
                </div>

                <!-- Main Asymmetric Workspace Grid (8 + 4 Cols on Desktop) -->
                <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-6 items-start">
                    <!-- LEFT PANEL: Consultation Chat Workspace (8 Cols) -->
                    <section
                        class="surface-card min-[1008px]:col-span-8 flex flex-col overflow-hidden min-w-0"
                        aria-label="Ruang Konsultasi Hukum Warga"
                    >
                        <!-- Chat Header Meta Bar -->
                        <div class="px-5 min-[641px]:px-6 py-4 bg-[#F4F7FC] border-b border-[#DCE4F0] flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-[14px] bg-[#0066CC] flex items-center justify-center text-white shrink-0">
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">smart_toy</span>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-sm font-bold text-[#0B1020] block truncate">Asisten Informasi Regulasi &amp; SOP</span>
                                    <span class="text-xs text-[#4A5366] font-semibold flex items-center gap-1.5 truncate">
                                        <span>Sesi: <strong id="session-id-label" class="text-[#005EB8] font-mono">#BHM-{{ strtoupper(substr($sessionUuid, 0, 8)) }}</strong></span>
                                        <span class="hidden min-[481px]:inline text-[#94A3B8]">·</span>
                                        <span class="hidden min-[481px]:inline">Anonim &amp; Terlindungi</span>
                                    </span>
                                </div>
                            </div>

                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-[#DCE4F0] text-[#0F766E] text-xs font-bold shrink-0">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">check_circle</span>
                                <span><span id="stat-total-docs">{{ $knowledgeStats['documents'] }} Dokumen</span> (<span id="stat-total-chunks">{{ $knowledgeStats['chunks'] }} Bagian</span>)</span>
                            </div>
                        </div>

                        <!-- Scrollable Conversation Thread (Flat Crisp Surface, Zero Gradient) -->
                        <div
                            id="chat-messages"
                            class="p-4 min-[641px]:p-6 space-y-5 min-[641px]:space-y-6 overflow-y-auto max-h-[60vh] min-[641px]:max-h-[640px] min-[1008px]:max-h-[680px] bg-[#F8FAFD]"
                            role="log"
                            aria-live="polite"
                            aria-label="Daftar pesan percakapan"
                        >
                            <!-- Timeline Divider -->
                            <div class="flex items-center justify-center gap-3 my-1 welcome-bubble">
                                <div class="h-px bg-[#DCE4F0] flex-1"></div>
                                <span class="px-3.5 py-1 bg-white border border-[#DCE4F0] rounded-full text-xs font-semibold text-[#4A5366] text-center">
                                    Sesi Konsultasi Aktif · Bahasa Indonesia Awam
                                </span>
                                <div class="h-px bg-[#DCE4F0] flex-1"></div>
                            </div>

                            <!-- Initial Welcome Assistant Message -->
                            <article class="chat-bubble welcome-bubble flex flex-col items-start gap-2 pr-1 min-[641px]:pr-8">
                                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                    <div class="w-7 h-7 rounded-[10px] bg-[#0066CC] text-white flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">smart_toy</span>
                                    </div>
                                    <span class="text-xs min-[641px]:text-sm font-bold text-[#0B1020]">Asisten Hukum Virtual BHIMA</span>
                                    <span class="px-2.5 py-0.5 bg-[#E6F0FC] text-[#005EB8] rounded-full text-xs font-bold">
                                        Penalaran Peraturan Terverifikasi
                                    </span>
                                </div>
                                <div class="inline-block w-fit bg-white text-[#0B1020] rounded-[20px] rounded-tl-sm px-4 py-3.5 border border-[#DCE4F0] shadow-[0_4px_14px_rgba(20,30,80,0.04)] max-w-[96%] min-[641px]:max-w-xl space-y-2 text-sm leading-relaxed">
                                    <p>
                                        Halo Bapak/Ibu Warga. Selamat datang di <strong>Ruang Konsultasi Hukum Warga BHIMA</strong>. Ceritakan kronologi permasalahan hukum atau kendala layanan publik yang Anda alami pada kolom di bawah.
                                    </p>
                                    <p class="text-[#4A5366] text-xs">
                                        Sistem mencocokkan kronologi Anda secara langsung terhadap <strong class="text-[#0B1020]">{{ $knowledgeStats['jdih'] }} Peraturan JDIH</strong> dan <strong class="text-[#0B1020]">{{ $knowledgeStats['sop'] }} SOP Layanan</strong> tanpa memberikan putusan vonis.
                                    </p>
                                </div>
                            </article>
                        </div>

                        <!-- Loading State Banner -->
                        <div
                            id="chat-loading"
                            hidden
                            role="status"
                            aria-live="assertive"
                            class="mx-4 min-[641px]:mx-6 my-2.5 p-4 bg-[#E6F0FC] border border-[#BFD7F5] rounded-[16px] flex items-center gap-3 text-[#005EB8]"
                        >
                            <div class="w-5 h-5 rounded-full border-2 border-[#0066CC]/30 border-t-[#0066CC] animate-spin shrink-0"></div>
                            <div class="text-xs min-[641px]:text-sm min-w-0">
                                <strong class="block font-bold text-[#0B1020]">Sedang mencocokkan pasal JDIH &amp; SOP layanan...</strong>
                                <span class="text-[#4A5366] text-xs">Meringkas kronologi, mencari kemiripan pasal, lalu memverifikasi sitasi resmi</span>
                            </div>
                        </div>

                        <!-- Error Banner -->
                        <div
                            id="chat-error"
                            hidden
                            role="alert"
                            class="mx-4 min-[641px]:mx-6 my-2.5 p-4 bg-[#FEE2E2] border border-[#FCA5A5] rounded-[16px] text-xs min-[641px]:text-sm font-bold text-[#991B1B]"
                        ></div>

                        <!-- CLEAR LIQUID GLASS INPUT DOCK (Control Layer, Zero Gradient) -->
                        <div class="stitch-no-print liquid-glass !border-x-0 !border-b-0 !shadow-none p-4 min-[641px]:p-5 space-y-3">
                            <!-- Suggested Quick Topics Pills -->
                            <div id="quick-scenarios" class="flex items-center gap-2 overflow-x-auto pb-1.5 text-nowrap -mx-1 px-1">
                                <span class="text-xs font-bold text-[#4A5366] uppercase tracking-wider shrink-0 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#0066CC]" aria-hidden="true">bolt</span>
                                    <span>Topik Cepat:</span>
                                </span>
                                <button
                                    type="button"
                                    data-scenario="Tetangga menggeser patok batas tanah pekarangan keluarga kami secara sepihak sejauh satu meter padahal kami memegang sertifikat hak milik (SHM) yang sah. Bagaimana prosedur mediasi sengketa batas tanah ini?"
                                    class="quick-chip press-scale min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#E6F0FC] text-[#0B1020] hover:text-[#005EB8] border border-[#CBD5E4] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#0066CC]" aria-hidden="true">fence</span>
                                    <span>Sengketa batas tanah</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Saya mengurus pembuatan KTP elektronik yang hilang di kantor pelayanan Disdukcapil, sudah membawa KK dan surat kehilangan kepolisian, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat selesai. Apa aturan hukumnya dan langkah yang harus saya lakukan?"
                                    class="quick-chip press-scale min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#E6F0FC] text-[#0B1020] hover:text-[#005EB8] border border-[#CBD5E4] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#0F766E]" aria-hidden="true">badge</span>
                                    <span>Pungli pengurusan KTP-el</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Adik saya mengalami kecelakaan dan dibawa ke IGD rumah sakit terdekat dalam keadaan gawat darurat, namun pihak rumah sakit meminta uang muka deposit Rp5 juta sebelum tindakan medis dan menolak kartu BPJS Kesehatan. Bagaimana aturannya?"
                                    class="quick-chip press-scale min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#E6F0FC] text-[#0B1020] hover:text-[#005EB8] border border-[#CBD5E4] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#0066CC]" aria-hidden="true">medical_services</span>
                                    <span>Penolakan pasien darurat IGD</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Saya bekerja di perusahaan selama 3 tahun, bulan ini di-PHK secara sepihak tanpa perundingan, gaji bulan terakhir belum dibayar, dan ijazah asli saya masih ditahan perusahaan. Apa hak hukum saya?"
                                    class="quick-chip press-scale min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#E6F0FC] text-[#0B1020] hover:text-[#005EB8] border border-[#CBD5E4] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#1E3A8A]" aria-hidden="true">work</span>
                                    <span>PHK sepihak &amp; ijazah ditahan</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Jalan kabupaten di desa kami rusak berlubang parah dan belum diberi rambu peringatan sehingga warga sering terjatuh saat malam hari, serta drainase jalan tersumbat menyebabkan banjir. Ke mana dan bagaimana cara melaporkannya?"
                                    class="quick-chip press-scale min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#E6F0FC] text-[#0B1020] hover:text-[#005EB8] border border-[#CBD5E4] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#0F766E]" aria-hidden="true">add_road</span>
                                    <span>Jalan rusak &amp; drainase banjir</span>
                                </button>
                            </div>

                            <!-- Input Bar -->
                            <form
                                id="consultation-form"
                                class="glass-input flex flex-col min-[641px]:flex-row min-[641px]:items-end gap-2.5 p-2.5 min-[641px]:p-3 !rounded-[22px]"
                                novalidate
                            >
                                <div class="flex items-start min-[641px]:items-end gap-2 flex-1 w-full min-w-0">
                                    <button
                                        type="button"
                                        onclick="document.getElementById('chronology-input')?.focus()"
                                        aria-label="Fokus ke kolom kronologi"
                                        class="hidden min-[641px]:inline-flex w-11 h-11 items-center justify-center text-[#4A5366] hover:text-[#0066CC] hover:bg-[#E6F0FC] rounded-full transition-colors shrink-0"
                                        title="Tulis Kronologi Perkara Warga"
                                    >
                                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">edit_note</span>
                                    </button>
                                    <label for="chronology-input" class="sr-only">Kronologi Permasalahan Hukum Warga</label>
                                    <textarea
                                        id="chronology-input"
                                        name="chronology"
                                        rows="3"
                                        maxlength="{{ $maxChronologyLength }}"
                                        required
                                        class="w-full bg-transparent border-0 resize-none text-sm min-[641px]:text-base text-[#0B1020] placeholder:text-[#4A5366] focus:outline-none p-1.5 max-h-40 leading-relaxed"
                                        placeholder="Tulis kronologi permasalahan hukum, kendala layanan publik, atau pungutan liar yang Anda alami..."
                                    ></textarea>
                                </div>
                                <button
                                    type="submit"
                                    id="send-btn"
                                    class="btn-primary w-full min-[641px]:w-auto px-6 py-2.5 text-sm inline-flex items-center justify-center gap-2 disabled:opacity-50 shrink-0"
                                >
                                    <span>Kirim Pertanyaan</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">send</span>
                                </button>
                            </form>

                            <div class="flex flex-wrap items-center justify-between text-[#4A5366] text-xs px-1 gap-2 font-medium">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[15px] text-[#0F766E] shrink-0" aria-hidden="true">lock</span>
                                    <span>Dilindungi UU Pelindungan Data Pribadi No. 27/2022</span>
                                </span>
                                <div class="flex items-center gap-2.5 ml-auto">
                                    <span id="char-counter" class="font-bold text-[#005EB8]">0 / {{ $maxChronologyLength }} karakter</span>
                                    <span class="hidden min-[1008px]:inline">· Ctrl+Enter kirim cepat</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- RIGHT PANEL: JDIH References, POSBAKUM & Index Coverage (4 Cols on Desktop) -->
                    <aside class="min-[1008px]:col-span-4 grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-1 gap-5 items-start" aria-label="Dokumen Rujukan JDIH dan Posbakum">
                        <!-- JDIH Document References Box -->
                        <div
                            id="jdih-reference-panel"
                            class="surface-card min-[641px]:col-span-2 min-[1008px]:col-span-1 p-5 min-[641px]:p-6 space-y-4 scroll-mt-24"
                        >
                            <div class="flex items-start justify-between gap-2 pb-3 border-b border-[#DCE4F0]">
                                <div>
                                    <div class="flex items-center gap-2 text-[#005EB8]">
                                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">auto_stories</span>
                                        <h2 class="type-h3 text-[#0B1020]">Dokumen Rujukan JDIH</h2>
                                    </div>
                                    <p class="text-xs text-[#4A5366] mt-1">
                                        Regulasi dan SOP terindeks yang terhubung dengan percakapan aktif.
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 bg-[#E6F0FC] text-[#005EB8] text-[11px] font-bold rounded-full uppercase tracking-wider shrink-0">
                                    JDIH Terpadu
                                </span>
                            </div>

                            <div id="jdih-sidebar-list" class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-1 gap-3.5 items-stretch">
                                <!-- Document Item 1: Featured Clear Card (Zero Gradient) -->
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                    target="_blank"
                                    rel="noopener"
                                    aria-label="Baca Pasal UU No. 24 Tahun 2013 tentang Administrasi Kependudukan"
                                    class="press-scale group relative min-[641px]:col-span-2 min-[1008px]:col-span-1 flex flex-col overflow-hidden rounded-[20px] bg-white border border-[#DCE4F0] shadow-[0_6px_18px_rgba(20,30,80,0.05)] hover:border-[#0066CC]/50 transition-colors"
                                >
                                    <span class="h-1 w-full bg-[#0066CC]" aria-hidden="true"></span>
                                    <div class="flex flex-1 flex-col gap-3 p-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E6F0FC] px-2.5 py-1 text-[11px] font-bold text-[#005EB8]">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#FF7A59]" aria-hidden="true"></span>
                                                <span>UU No. 24 Tahun 2013</span>
                                            </span>
                                            <span class="material-symbols-outlined text-[20px] text-[#4A5366]" aria-hidden="true">menu_book</span>
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-base font-bold leading-snug text-[#0B1020]">Administrasi Kependudukan</h3>
                                            <p class="text-xs leading-relaxed text-[#4A5366]">Pasal 79A dan 95B menegaskan pengurusan dokumen kependudukan tidak dipungut biaya.</p>
                                        </div>
                                        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-1">
                                            <span class="inline-flex min-h-[40px] items-center gap-1.5 rounded-full bg-[#0066CC] px-4 text-xs font-bold text-white transition-colors group-hover:bg-[#0052A3]">
                                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">visibility</span>
                                                <span>Baca Pasal</span>
                                            </span>
                                            <span class="text-xs font-bold text-[#0F766E]">Pasal 79A &amp; 95B</span>
                                        </div>
                                    </div>
                                </a>

                                <!-- Document Item 2: SOP-003 Pungutan Liar -->
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'SOP-003']) }}"
                                    target="_blank"
                                    rel="noopener"
                                    aria-label="Lihat Prosedur SOP Penanganan Pengaduan Dugaan Pungutan Liar"
                                    class="press-scale group relative flex flex-col overflow-hidden rounded-[20px] bg-white border border-[#DCE4F0] shadow-[0_6px_18px_rgba(20,30,80,0.05)] hover:border-[#0F766E]/60 transition-colors"
                                >
                                    <span class="h-1 w-full bg-[#0F766E]" aria-hidden="true"></span>
                                    <div class="flex flex-1 flex-col gap-3 p-4">
                                        <div class="flex items-start gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[14px] bg-[#E6F5F3] text-[#0F766E]">
                                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_tree</span>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <span class="block text-[11px] font-bold uppercase tracking-wider text-[#0F766E]">SOP Layanan Publik</span>
                                                <h3 class="mt-0.5 text-sm font-bold leading-snug text-[#0B1020]">SOP Pengaduan Pungli &amp; Mediasi</h3>
                                                <p class="mt-1 text-xs font-semibold text-[#4A5366]">Inspektorat Daerah · SOP-003</p>
                                            </div>
                                        </div>
                                        <span class="mt-auto inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-full border border-[#CBD5E4] bg-[#F4F7FC] px-4 text-xs font-bold text-[#0B1020] transition-colors group-hover:bg-[#E6F0FC] group-hover:text-[#005EB8]">
                                            <span>Lihat Prosedur</span>
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">open_in_new</span>
                                        </span>
                                    </div>
                                </a>

                                <!-- Document Item 3: JDIH-015 Pendaftaran Tanah -->
                                <a
                                    href="{{ route('consultation.reference', ['key' => 'JDIH-015']) }}"
                                    target="_blank"
                                    rel="noopener"
                                    aria-label="Buka Detail Pasal PP No. 24 Tahun 1997 tentang Pendaftaran Tanah"
                                    class="press-scale group relative flex flex-col overflow-hidden rounded-[20px] bg-white border border-[#DCE4F0] shadow-[0_6px_18px_rgba(20,30,80,0.05)] hover:border-[#1E3A8A]/60 transition-colors"
                                >
                                    <span class="h-1 w-full bg-[#1E3A8A]" aria-hidden="true"></span>
                                    <div class="p-4 flex flex-1 flex-col gap-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#1E3A8A]">PP No. 24 Tahun 1997</span>
                                            <span class="material-symbols-outlined text-[18px] text-[#1E3A8A]" aria-hidden="true">fence</span>
                                        </div>
                                        <h3 class="text-base font-bold leading-snug text-[#0B1020]">Pendaftaran Tanah &amp; Batas</h3>
                                        <p class="text-xs font-semibold text-[#4A5366]">Pertanahan · JDIH-015</p>
                                        <div class="mt-auto pt-3 border-t border-[#DCE4F0] flex items-center justify-between gap-2">
                                            <span class="text-xs font-bold text-[#005EB8]">Buka Detail Pasal</span>
                                            <span class="material-symbols-outlined text-[18px] text-[#005EB8]" aria-hidden="true">open_in_new</span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <a
                                class="min-h-[44px] flex items-center justify-center gap-1.5 text-[#005EB8] font-bold text-xs hover:underline"
                                href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                <span>Jelajahi Indeks {{ $knowledgeStats['documents'] }} Dokumen JDIH &amp; SOP</span>
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">north_east</span>
                            </a>
                        </div>

                        <!-- POSBAKUM Legal Aid Desk Card (Zero Gradient) -->
                        <div
                            id="posbakum-panel"
                            class="surface-card relative overflow-hidden p-5 min-[641px]:p-6 flex flex-col justify-between gap-4 h-full scroll-mt-24"
                        >
                            <span class="absolute inset-x-0 top-0 h-1 bg-[#0F766E]" aria-hidden="true"></span>
                            <div class="space-y-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-[14px] bg-[#0F766E] text-white flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">gavel</span>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-[#0B1020]">Pos Bantuan Hukum (Posbakum)</h2>
                                        <span class="text-xs text-[#0F766E] font-bold">Layanan Advokasi Pro Bono (Gratis)</span>
                                    </div>
                                </div>
                                <p class="text-xs text-[#4A5366] leading-relaxed">
                                    Warga kurang mampu berhak memperoleh pendampingan hukum gratis di pengadilan negeri sesuai UU No. 16 Tahun 2011 tentang Bantuan Hukum.
                                </p>
                                <div class="space-y-2 pt-2 border-t border-[#DCE4F0] text-xs">
                                    <div class="flex items-start gap-2 text-[#0B1020]">
                                        <span class="material-symbols-outlined text-[#0F766E] text-[18px] shrink-0 mt-0.5" aria-hidden="true">location_on</span>
                                        <span>Gedung Pengadilan Negeri Kelas 1A, Jl. Veteran No. 18</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[#0B1020]">
                                        <span class="material-symbols-outlined text-[#0F766E] text-[18px] shrink-0" aria-hidden="true">call</span>
                                        <span class="font-bold">Hotline Posbakum: (021) 8849-0112</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[#4A5366]">
                                        <span class="material-symbols-outlined text-[#4A5366] text-[18px] shrink-0" aria-hidden="true">schedule</span>
                                        <span>Senin sampai Jumat: 08.30 - 15.00 WIB</span>
                                    </div>
                                </div>
                            </div>
                            <a
                                class="press-scale w-full min-h-[48px] flex items-center justify-center gap-2 bg-[#0F766E] hover:bg-[#0B5B55] text-white py-2.5 px-4 rounded-full font-bold text-xs min-[641px]:text-sm transition-colors text-center"
                                href="{{ route('consultation.reference', ['key' => 'JDIH-010']) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                <span class="material-symbols-outlined text-[18px] shrink-0" aria-hidden="true">contact_page</span>
                                <span>Syarat Bantuan Hukum Gratis</span>
                            </a>
                        </div>

                        <!-- Real-Time Index Coverage Card (Solid Ink Blue #1E3A8A) -->
                        <div class="hero-dark-scene rounded-[28px] bg-[#1E3A8A] text-white p-5 min-[641px]:p-6 flex flex-col justify-between gap-4 shadow-[0_14px_34px_rgba(20,30,80,0.16)] h-full">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-[#F2D8B3] uppercase tracking-wider font-bold">Integritas Indeks Regulasi</span>
                                    <span class="material-symbols-outlined text-[20px] text-[#F2D8B3]" aria-hidden="true">shield_with_heart</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-white/12 p-3.5 rounded-[16px] border border-white/20">
                                        <span class="text-xl min-[641px]:text-2xl font-extrabold text-white block tabular-nums">{{ $knowledgeStats['documents'] }}</span>
                                        <span class="text-xs text-[#E2ECFF] font-semibold">Dokumen JDIH &amp; SOP</span>
                                    </div>
                                    <div class="bg-white/12 p-3.5 rounded-[16px] border border-white/20">
                                        <span class="text-xl min-[641px]:text-2xl font-extrabold text-white block tabular-nums">{{ $knowledgeStats['chunks'] }}</span>
                                        <span class="text-xs text-[#E2ECFF] font-semibold">Bagian Pasal Terindeks</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-[#E2ECFF] leading-relaxed">
                                Kueri dicocokkan langsung pada basis data terindeks BHIMA tanpa mengarang pasal di luar sumber resmi.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        @include('partials.liquid-footer')
        @include('partials.liquid-bottom-nav', ['activeNav' => 'asisten-hukum'])
    </div>
</body>
</html>
