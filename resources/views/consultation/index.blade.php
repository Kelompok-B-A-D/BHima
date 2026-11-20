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
<body class="stitch-consultation-body min-h-screen overflow-x-hidden bg-gradient-to-b from-[#E3EFFB] via-[#E9EEF5] to-[#F6F9FF] font-sans text-[#171C21] antialiased selection:bg-[#D6E3FF] selection:text-[#001B3E]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-xl focus:bg-[#F59E0B] focus:text-[#111827] focus:font-bold focus:shadow-lg"
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
            <div class="max-w-[1200px] mx-auto w-full px-3 min-[481px]:px-4 min-[641px]:px-6 space-y-5 min-[641px]:space-y-6">
                <!-- Status & Context Header Strip -->
                <div class="flex flex-col min-[1008px]:flex-row min-[1008px]:items-center justify-between gap-4 rounded-[28px] bg-white/90 border border-white p-4 min-[641px]:p-6 shadow-[0_12px_32px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)]">
                    <div class="flex items-start min-[641px]:items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 min-[641px]:w-12 min-[641px]:h-12 rounded-2xl bg-[#005AB3] flex items-center justify-center text-white shadow-[0_6px_16px_rgba(0,90,179,0.22)] shrink-0">
                            <span class="material-symbols-outlined text-[24px] min-[641px]:text-[26px]" aria-hidden="true">gavel</span>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-xl min-[380px]:text-2xl min-[641px]:text-[28px] font-extrabold text-[#171C21] tracking-tight leading-tight">Asisten Hukum Virtual BHIMA</h1>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#9CF2E8] text-[#00504A] text-[11px] min-[641px]:text-xs font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#0F766E] shrink-0" aria-hidden="true"></span>
                                    <span>Aktif • Regulasi JDIH</span>
                                </span>
                            </div>
                            <p class="text-xs min-[380px]:text-sm text-[#414754] leading-relaxed">
                                <strong>Ruang Konsultasi Hukum Warga:</strong> Verifikasi indikasi pasal &amp; panduan administratif otomatis berbasis {{ $knowledgeStats['documents'] ?: 100 }} dokumen JDIH dan SOP layanan resmi.
                            </p>
                        </div>
                    </div>

                    <div class="stitch-no-print grid grid-cols-2 min-[641px]:flex items-center gap-2.5 w-full min-[1008px]:w-auto shrink-0">
                        <button
                            type="button"
                            id="reset-session-btn"
                            class="min-h-[44px] flex items-center justify-center gap-1.5 px-4 py-2 bg-[#EFF4FB] hover:bg-[#D6E3FF] text-[#005AB3] font-bold text-xs min-[641px]:text-sm rounded-full border border-[#C0C6D6]/70 transition-colors"
                        >
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">restart_alt</span>
                            <span>Topik Baru</span>
                        </button>
                        <button
                            type="button"
                            onclick="window.print()"
                            class="min-h-[44px] flex items-center justify-center gap-1.5 px-4 py-2 bg-white hover:bg-[#EFF4FB] text-[#171C21] font-bold text-xs min-[641px]:text-sm rounded-full border border-[#C0C6D6]/70 transition-colors"
                        >
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">print</span>
                            <span>Cetak Riwayat</span>
                        </button>
                    </div>
                </div>

                <!-- Main Asymmetric Grid Layout -->
                <!-- Handphone (320-640px): 1 col | Tablet (641-1007px): 1 col + 2-col refs | Laptop/Desktop (>=1008px): 8+4 cols -->
                <div class="grid grid-cols-1 min-[1008px]:grid-cols-12 gap-5 min-[641px]:gap-6 items-start">
                    <!-- LEFT PANEL: Liquid Glass Chat Consultation Workspace (8 Cols) -->
                    <section
                        class="min-[1008px]:col-span-8 flex flex-col rounded-[28px] bg-white/92 border border-white shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] overflow-hidden min-w-0"
                        aria-label="Ruang Konsultasi Hukum Warga"
                    >
                        <!-- Chat Header Meta Bar -->
                        <div class="px-4 min-[641px]:px-6 py-3.5 bg-[#EFF4FB]/90 border-b border-[#DEE3EA] flex flex-wrap items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-2xl bg-[#005AB3] flex items-center justify-center text-white shadow-sm shrink-0">
                                    <span class="material-symbols-outlined text-[19px]" aria-hidden="true">smart_toy</span>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs min-[380px]:text-sm font-bold text-[#171C21] block truncate">Asisten Informasi Regulasi &amp; SOP</span>
                                    <span class="text-[11px] text-[#414754] font-semibold flex items-center gap-1.5 truncate">
                                        <span>Sesi: <strong id="session-id-label" class="text-[#005AB3] font-mono">#BHM-{{ strtoupper(substr($sessionUuid, 0, 8)) }}</strong></span>
                                        <span class="hidden min-[481px]:inline text-[#C0C6D6]">•</span>
                                        <span class="hidden min-[481px]:inline">Anonim &amp; Terlindungi</span>
                                    </span>
                                </div>
                            </div>

                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-[#DEE3EA] text-[#0F766E] text-[11px] min-[641px]:text-xs font-bold shrink-0">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">check_circle</span>
                                <span><span id="stat-total-docs">{{ $knowledgeStats['documents'] }} Dokumen</span> (<span id="stat-total-chunks">{{ $knowledgeStats['chunks'] }} Chunk</span>)</span>
                            </div>
                        </div>

                        <!-- Scrollable Conversation Thread -->
                        <div
                            id="chat-messages"
                            class="p-3.5 min-[481px]:p-5 min-[641px]:p-6 space-y-5 min-[641px]:space-y-6 overflow-y-auto max-h-[60vh] min-[641px]:max-h-[640px] min-[1008px]:max-h-[680px] bg-gradient-to-b from-[#F6F9FF]/80 to-white/60"
                            role="log"
                            aria-live="polite"
                            aria-label="Daftar pesan percakapan"
                        >
                            <!-- Timeline Divider -->
                            <div class="flex items-center justify-center gap-3 my-1 welcome-bubble">
                                <div class="h-px bg-[#DEE3EA] flex-1"></div>
                                <span class="px-3 py-1 bg-[#EFF4FB] border border-[#DEE3EA] rounded-full text-[11px] font-semibold text-[#414754] text-center">
                                    Sesi Konsultasi Aktif • Bahasa Indonesia Awam
                                </span>
                                <div class="h-px bg-[#DEE3EA] flex-1"></div>
                            </div>

                            <!-- Initial Welcome Assistant Message -->
                            <article class="chat-bubble welcome-bubble flex flex-col items-start gap-2 pr-1 min-[641px]:pr-8">
                                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                    <div class="w-7 h-7 rounded-xl bg-[#005AB3] text-white flex items-center justify-center shadow-sm shrink-0">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">smart_toy</span>
                                    </div>
                                    <span class="text-xs min-[641px]:text-sm font-bold text-[#171C21]">Asisten Hukum Virtual BHIMA</span>
                                    <span class="px-2.5 py-0.5 bg-[#D6E3FF] text-[#00468D] rounded-full text-[10px] min-[641px]:text-xs font-bold">
                                        Penalaran Peraturan Terverifikasi
                                    </span>
                                </div>
                                <div class="inline-block w-fit bg-white text-[#171C21] rounded-2xl rounded-tl-sm px-4 py-3.5 border border-[#DEE3EA] shadow-[0_6px_18px_rgba(20,30,80,0.04)] max-w-[96%] min-[641px]:max-w-xl space-y-2 text-xs min-[380px]:text-sm leading-relaxed">
                                    <p>
                                        Halo Bapak/Ibu Warga. Selamat datang di <strong>Ruang Konsultasi Hukum Warga BHIMA</strong>. Ceritakan kronologi permasalahan hukum atau kendala layanan publik yang Anda alami pada kolom di bawah.
                                    </p>
                                    <p class="text-[#414754] text-[11px] min-[641px]:text-xs">
                                        Sistem mencocokkan kronologi Anda secara langsung terhadap <strong>{{ $knowledgeStats['jdih'] ?: 80 }} Peraturan JDIH</strong> dan <strong>{{ $knowledgeStats['sop'] ?: 20 }} SOP Layanan</strong> tanpa memberikan putusan vonis.
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
                            class="mx-3.5 min-[641px]:mx-6 my-2.5 p-3.5 min-[641px]:p-4 bg-[#D6E3FF]/50 border border-[#005AB3]/25 rounded-2xl flex items-center gap-3 text-[#00468D]"
                        >
                            <div class="w-5 h-5 rounded-full border-2 border-[#005AB3]/30 border-t-[#005AB3] animate-spin shrink-0"></div>
                            <div class="text-xs min-[641px]:text-sm min-w-0">
                                <strong class="block font-bold text-[#171C21]">Sedang mencocokkan pasal JDIH &amp; SOP layanan...</strong>
                                <span class="text-[#414754] text-[11px] min-[641px]:text-xs">Meringkas kronologi, mencari kemiripan pasal, lalu memverifikasi sitasi resmi</span>
                            </div>
                        </div>

                        <!-- Error Banner -->
                        <div
                            id="chat-error"
                            hidden
                            role="alert"
                            class="mx-3.5 min-[641px]:mx-6 my-2.5 p-3.5 min-[641px]:p-4 bg-[#FFDAD6] border border-[#BA1A1A]/30 rounded-2xl text-xs min-[641px]:text-sm font-bold text-[#93000A]"
                        ></div>

                        <!-- FLOATING LIQUID GLASS INPUT DOCK (Focal Blur Element #2) -->
                        <div class="stitch-no-print p-3.5 min-[481px]:p-4 min-[641px]:p-5 bg-[#EFF4FB]/75 backdrop-blur-2xl border-t border-white space-y-3">
                            <!-- Suggested Quick Topics Pills -->
                            <div id="quick-scenarios" class="flex items-center gap-2 overflow-x-auto pb-1.5 text-nowrap -mx-1 px-1">
                                <span class="text-[11px] font-bold text-[#414754] uppercase tracking-wider shrink-0 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#005AB3]" aria-hidden="true">bolt</span>
                                    <span>Topik Cepat:</span>
                                </span>
                                <button
                                    type="button"
                                    data-scenario="Tetangga menggeser patok batas tanah pekarangan keluarga kami secara sepihak sejauh satu meter padahal kami memegang sertifikat hak milik (SHM) yang sah. Bagaimana prosedur mediasi sengketa batas tanah ini?"
                                    class="quick-chip min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#D6E3FF] text-[#171C21] hover:text-[#00468D] border border-[#DEE3EA] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#005AB3]" aria-hidden="true">fence</span>
                                    <span>Sengketa batas tanah</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Saya mengurus pembuatan KTP elektronik yang hilang di kantor pelayanan Disdukcapil, sudah membawa KK dan surat kehilangan kepolisian, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat selesai. Apa aturan hukumnya dan langkah yang harus saya lakukan?"
                                    class="quick-chip min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#D6E3FF] text-[#171C21] hover:text-[#00468D] border border-[#DEE3EA] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#0F766E]" aria-hidden="true">badge</span>
                                    <span>Pungli pengurusan KTP-el</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Adik saya mengalami kecelakaan dan dibawa ke IGD rumah sakit terdekat dalam keadaan gawat darurat, namun pihak rumah sakit meminta uang muka deposit Rp5 juta sebelum tindakan medis dan menolak kartu BPJS Kesehatan. Bagaimana aturannya?"
                                    class="quick-chip min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#D6E3FF] text-[#171C21] hover:text-[#00468D] border border-[#DEE3EA] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#005AB3]" aria-hidden="true">medical_services</span>
                                    <span>Penolakan pasien darurat IGD</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Saya bekerja di perusahaan selama 3 tahun, bulan ini di-PHK secara sepihak tanpa perundingan, gaji bulan terakhir belum dibayar, dan ijazah asli saya masih ditahan perusahaan. Apa hak hukum saya?"
                                    class="quick-chip min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#D6E3FF] text-[#171C21] hover:text-[#00468D] border border-[#DEE3EA] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#264191]" aria-hidden="true">work</span>
                                    <span>PHK sepihak &amp; ijazah ditahan</span>
                                </button>
                                <button
                                    type="button"
                                    data-scenario="Jalan kabupaten di desa kami rusak berlubang parah dan belum diberi rambu peringatan sehingga warga sering terjatuh saat malam hari, serta drainase jalan tersumbat menyebabkan banjir. Ke mana dan bagaimana cara melaporkannya?"
                                    class="quick-chip min-h-[44px] px-3.5 py-2 bg-white hover:bg-[#D6E3FF] text-[#171C21] hover:text-[#00468D] border border-[#DEE3EA] rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-1.5 shrink-0"
                                >
                                    <span class="material-symbols-outlined text-[15px] text-[#0F766E]" aria-hidden="true">add_road</span>
                                    <span>Jalan rusak &amp; drainase banjir</span>
                                </button>
                            </div>

                            <!-- Input Bar -->
                            <form
                                id="consultation-form"
                                class="flex flex-col min-[641px]:flex-row min-[641px]:items-end gap-2.5 bg-white border border-[#C0C6D6]/80 p-2.5 min-[641px]:p-3 rounded-2xl min-[641px]:rounded-3xl shadow-[0_8px_24px_rgba(20,30,80,0.06)] focus-within:border-[#005AB3] focus-within:ring-2 focus-within:ring-[#005AB3]/20 transition-all"
                                novalidate
                            >
                                <div class="flex items-start min-[641px]:items-end gap-2 flex-1 w-full min-w-0">
                                    <button
                                        type="button"
                                        onclick="document.getElementById('chronology-input')?.focus()"
                                        aria-label="Fokus ke kolom kronologi"
                                        class="hidden min-[641px]:inline-flex w-11 h-11 items-center justify-center text-[#414754] hover:text-[#005AB3] hover:bg-[#EFF4FB] rounded-full transition-colors shrink-0"
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
                                        class="w-full bg-transparent border-0 resize-none text-xs min-[380px]:text-sm min-[641px]:text-base text-[#171C21] placeholder:text-[#414754]/80 focus:outline-none p-1.5 max-h-40 leading-relaxed"
                                        placeholder="Tulis kronologi permasalahan hukum, kendala layanan publik, atau pungutan liar yang Anda alami..."
                                    ></textarea>
                                </div>
                                <button
                                    type="submit"
                                    id="send-btn"
                                    class="w-full min-[641px]:w-auto min-h-[48px] flex items-center justify-center gap-2 px-5 min-[641px]:px-6 py-2.5 bg-[#005AB3] hover:bg-[#00468D] active:bg-[#003366] disabled:opacity-50 text-white rounded-full font-bold text-xs min-[380px]:text-sm transition-all shadow-[0_4px_14px_rgba(0,90,179,0.28)] shrink-0"
                                >
                                    <span>Kirim Pertanyaan</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">send</span>
                                </button>
                            </form>

                            <div class="flex flex-wrap items-center justify-between text-[#414754] text-[11px] min-[641px]:text-xs px-1 gap-2 font-medium">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[15px] text-[#0F766E] shrink-0" aria-hidden="true">lock</span>
                                    <span>Dilindungi UU Pelindungan Data Pribadi No. 27/2022</span>
                                </span>
                                <div class="flex items-center gap-2.5 ml-auto">
                                    <span id="char-counter" class="font-bold text-[#005AB3]">0 / {{ $maxChronologyLength }} karakter</span>
                                    <span class="hidden min-[1008px]:inline">• Ctrl+Enter kirim cepat</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- RIGHT PANEL: JDIH References, POSBAKUM & Knowledge Metrics (4 Cols on >=1008px, 2 Cols on Tablet) -->
                    <aside class="min-[1008px]:col-span-4 grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-1 gap-4 min-[641px]:gap-5 items-start" aria-label="Dokumen Rujukan JDIH dan Posbakum">
                        <!-- JDIH Document References Box -->
                        <div
                            id="jdih-reference-panel"
                            class="min-[641px]:col-span-2 min-[1008px]:col-span-1 rounded-[28px] bg-white/92 border border-white p-5 min-[641px]:p-6 shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] space-y-4 scroll-mt-24"
                        >
                            <div class="flex items-start justify-between gap-2 pb-3 border-b border-[#DEE3EA]">
                                <div>
                                    <div class="flex items-center gap-2 text-[#005AB3]">
                                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">auto_stories</span>
                                        <h2 class="text-base min-[380px]:text-lg min-[641px]:text-xl font-bold text-[#171C21]">Dokumen Rujukan JDIH</h2>
                                    </div>
                                    <p class="text-xs text-[#414754] mt-1">
                                        Regulasi dan SOP terindeks yang terhubung dengan topik percakapan aktif.
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 bg-[#D6E3FF] text-[#00468D] text-[10px] min-[641px]:text-xs font-bold rounded-full uppercase tracking-wider shrink-0">
                                    JDIH Terpadu
                                </span>
                            </div>

                            <div id="jdih-sidebar-list" class="grid grid-cols-1 min-[641px]:grid-cols-3 min-[1008px]:grid-cols-1 gap-3.5 items-stretch">
                                <!-- Document Item 1 -->
                                <div class="p-4 bg-[#EFF4FB]/90 rounded-2xl border border-[#DEE3EA] hover:border-[#005AB3] transition-all flex flex-col justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-[#D6E3FF] text-[#00468D] flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[19px]" aria-hidden="true">picture_as_pdf</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[11px] text-[#005AB3] font-bold block uppercase tracking-wider">UU No. 24 Tahun 2013</span>
                                            <h3 class="text-sm text-[#171C21] font-bold truncate" title="Undang-Undang Administrasi Kependudukan">
                                                Administrasi Kependudukan
                                            </h3>
                                            <div class="flex items-center gap-1.5 mt-1 text-[#414754] text-[11px] flex-wrap">
                                                <span class="px-2 py-0.5 bg-[#9CF2E8] text-[#00504A] rounded-md font-bold">Berlaku</span>
                                                <span>Pasal 79A &amp; 95B</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="flex-1 min-h-[44px] px-3 rounded-full bg-white border border-[#C0C6D6] hover:bg-[#D6E3FF] text-[#005AB3] font-bold text-xs flex items-center justify-center gap-1.5 transition-colors"
                                        >
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">visibility</span>
                                            <span>Baca Pasal</span>
                                        </a>
                                        <a
                                            href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="min-h-[44px] px-3.5 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white font-bold text-xs flex items-center justify-center gap-1 transition-colors"
                                        >
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">open_in_new</span>
                                            <span>Buka</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Document Item 2 -->
                                <div class="p-4 bg-[#EFF4FB]/90 rounded-2xl border border-[#DEE3EA] hover:border-[#005AB3] transition-all flex flex-col justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-[#9CF2E8] text-[#00504A] flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[19px]" aria-hidden="true">description</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[11px] text-[#0F766E] font-bold block uppercase tracking-wider">SOP Layanan Publik</span>
                                            <h3 class="text-sm text-[#171C21] font-bold truncate" title="SOP Pelaporan Pungutan Liar & Pengaduan Layanan">
                                                SOP Pelaporan Pungli &amp; Mediasi
                                            </h3>
                                            <div class="flex items-center gap-1.5 mt-1 text-[#414754] text-[11px] flex-wrap">
                                                <span>Inspektorat Daerah</span>
                                                <span>•</span>
                                                <span>SOP-002</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'SOP-002']) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="w-full min-h-[44px] px-3 rounded-full bg-white border border-[#C0C6D6] hover:bg-[#D6E3FF] text-[#005AB3] font-bold text-xs flex items-center justify-center gap-1.5 transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">account_tree</span>
                                        <span>Lihat Alur Prosedur</span>
                                    </a>
                                </div>

                                <!-- Document Item 3 -->
                                <div class="p-4 bg-[#EFF4FB]/90 rounded-2xl border border-[#DEE3EA] hover:border-[#005AB3] transition-all flex flex-col justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-[#DCE1FF] text-[#264191] flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[19px]" aria-hidden="true">article</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[11px] text-[#264191] font-bold block uppercase tracking-wider">PP No. 24 Tahun 1997</span>
                                            <h3 class="text-sm text-[#171C21] font-bold truncate" title="Peraturan Pemerintah tentang Pendaftaran Tanah">
                                                Pendaftaran Tanah &amp; Batas
                                            </h3>
                                            <div class="flex items-center gap-1.5 mt-1 text-[#414754] text-[11px] flex-wrap">
                                                <span>Pertanahan</span>
                                                <span>•</span>
                                                <span>JDIH-005</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('consultation.reference', ['key' => 'JDIH-005']) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="w-full min-h-[44px] px-3 rounded-full bg-white border border-[#C0C6D6] hover:bg-[#D6E3FF] text-[#171C21] font-bold text-xs flex items-center justify-center gap-1.5 transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">menu_book</span>
                                        <span>Buka Detail Pasal</span>
                                    </a>
                                </div>
                            </div>

                            <a
                                class="min-h-[44px] flex items-center justify-center gap-1.5 text-[#005AB3] font-bold text-xs hover:underline"
                                href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                <span>Jelajahi Indeks {{ $knowledgeStats['documents'] }} Dokumen JDIH &amp; SOP</span>
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">north_east</span>
                            </a>
                        </div>

                        <!-- POSBAKUM Legal Aid Desk Card -->
                        <div
                            id="posbakum-panel"
                            class="rounded-[28px] bg-gradient-to-br from-[#0F766E]/8 via-white/95 to-[#EFF4FB] border border-[#0F766E]/30 shadow-[0_12px_30px_rgba(15,118,110,0.08)] p-5 min-[641px]:p-6 flex flex-col justify-between gap-4 h-full scroll-mt-24"
                        >
                            <div class="space-y-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-[#0F766E] text-white flex items-center justify-center shadow-sm shrink-0">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">gavel</span>
                                    </div>
                                    <div>
                                        <h2 class="text-base min-[641px]:text-lg font-bold text-[#171C21]">Pos Bantuan Hukum (Posbakum)</h2>
                                        <span class="text-xs text-[#0F766E] font-bold">Layanan Advokasi Pro Bono (Gratis)</span>
                                    </div>
                                </div>
                                <p class="text-xs text-[#414754] leading-relaxed">
                                    Warga kurang mampu berhak memperoleh pendampingan hukum gratis di pengadilan negeri sesuai UU No. 16 Tahun 2011 tentang Bantuan Hukum.
                                </p>
                                <div class="space-y-2 pt-2 border-t border-[#DEE3EA] text-xs">
                                    <div class="flex items-start gap-2 text-[#171C21]">
                                        <span class="material-symbols-outlined text-[#0F766E] text-[18px] shrink-0 mt-0.5" aria-hidden="true">location_on</span>
                                        <span>Gedung Pengadilan Negeri Kelas 1A, Jl. Veteran No. 18</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[#171C21]">
                                        <span class="material-symbols-outlined text-[#0F766E] text-[18px] shrink-0" aria-hidden="true">call</span>
                                        <span class="font-bold">Hotline Posbakum: (021) 8849-0112</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[#414754]">
                                        <span class="material-symbols-outlined text-[#414754] text-[18px] shrink-0" aria-hidden="true">schedule</span>
                                        <span>Senin - Jumat: 08.30 - 15.00 WIB</span>
                                    </div>
                                </div>
                            </div>
                            <a
                                class="w-full min-h-[44px] flex items-center justify-center gap-2 bg-[#0F766E] hover:bg-[#0C635D] text-white py-2.5 px-4 rounded-full font-bold text-xs min-[641px]:text-sm shadow-sm transition-colors text-center"
                                href="{{ route('consultation.reference', ['key' => 'JDIH-010']) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                <span class="material-symbols-outlined text-[18px] shrink-0" aria-hidden="true">contact_page</span>
                                <span>Syarat Bantuan Hukum Gratis</span>
                            </a>
                        </div>

                        <!-- Real-Time Index Coverage Card -->
                        <div class="rounded-[28px] bg-[#005AB3] text-white p-5 min-[641px]:p-6 flex flex-col justify-between gap-4 shadow-[0_16px_36px_rgba(0,90,179,0.20)] h-full">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-[#D6E3FF] uppercase tracking-wider font-bold">Integritas Indeks Regulasi</span>
                                    <span class="material-symbols-outlined text-[20px] text-[#9CF2E8]" aria-hidden="true">shield_with_heart</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-white/12 p-3.5 rounded-2xl border border-white/15">
                                        <span class="text-xl min-[641px]:text-2xl font-extrabold text-white block">{{ $knowledgeStats['documents'] }}</span>
                                        <span class="text-[11px] text-[#D6E3FF] font-semibold">Dokumen JDIH &amp; SOP</span>
                                    </div>
                                    <div class="bg-white/12 p-3.5 rounded-2xl border border-white/15">
                                        <span class="text-xl min-[641px]:text-2xl font-extrabold text-white block">{{ $knowledgeStats['chunks'] }}</span>
                                        <span class="text-[11px] text-[#D6E3FF] font-semibold">Bagian Pasal Terindeks</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-white/90 leading-relaxed">
                                Kueri dicocokkan langsung pada basis data terindeks BHIMA tanpa mengarang pasal di luar sumber resmi.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        @include('partials.liquid-footer')
    </div>
</body>
</html>
