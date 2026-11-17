<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Formulir Eskalasi Pengaduan Warga · BHIMA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Newsreader:ital,opsz,wght@0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="stitch-consultation-body bg-[#F0FAF8] font-sans text-text-body antialiased selection:bg-primary-light selection:text-primary-dark">
    <header class="fixed top-0 left-0 right-0 z-50 bg-[#115E59] text-white shadow-md border-b border-[#0d4d49]">
        <div class="bg-[#0d4d49] text-white/90 h-8 px-3 sm:px-6 flex items-center">
            <div class="max-w-[1280px] w-full mx-auto flex items-center justify-between gap-3 text-[11px] sm:text-xs font-medium">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <div class="flex flex-col w-4 h-2.5 overflow-hidden rounded-[1px] border border-white/40 shrink-0">
                        <div class="h-1/2 bg-red-600"></div>
                        <div class="h-1/2 bg-white"></div>
                    </div>
                    <span class="tracking-wide truncate">Portal Resmi Edukasi Hukum &amp; Eskalasi Pengaduan Masyarakat BHIMA</span>
                </div>
                <span class="hidden sm:flex items-center gap-1.5 text-xs text-white/80 shrink-0">
                    <span class="material-symbols-outlined text-[15px] text-[#9cf2e8]">verified_user</span>
                    Terintegrasi dengan Verifikator Pengaduan
                </span>
            </div>
        </div>
        <div class="h-16 sm:h-20 max-w-[1280px] mx-auto px-3 sm:px-6 flex items-center justify-between gap-2 sm:gap-4">
            <a href="{{ route('consultation.home') }}" class="flex items-center gap-2.5 sm:gap-4 text-white no-underline min-w-0">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-[#9cf2e8] shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[28px]">balance</span>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="font-headline text-lg sm:text-2xl font-bold text-white tracking-tight leading-tight truncate">BHIMA</span>
                    <span class="text-[10px] sm:text-xs text-[#CCECE6] font-medium tracking-wide truncate">Formulir Pengaduan Masyarakat</span>
                </div>
            </a>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a
                    href="{{ route('consultation.index') }}"
                    class="flex items-center gap-1.5 bg-[#F59E0B] hover:bg-[#D97706] text-[#111827] font-bold text-xs sm:text-sm px-3 sm:px-4 py-2 rounded-lg shadow-sm transition-all"
                >
                    <span class="material-symbols-outlined text-[17px] sm:text-[18px] text-[#111827]">arrow_back</span>
                    <span class="hidden sm:inline">Kembali ke Ruang Konsultasi</span>
                    <span class="sm:hidden">Konsultasi</span>
                </a>
            </div>
        </div>
    </header>

    <main class="pt-[108px] sm:pt-[132px] pb-12 sm:pb-16 px-3 sm:px-6 max-w-[960px] mx-auto space-y-5 sm:space-y-6">
        @if (session('complaint_submitted'))
            @php($submitted = session('complaint_submitted'))
            <section role="status" class="bg-[#DCFCE7] border-2 border-[#16A34A] rounded-2xl p-4 sm:p-6 space-y-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[26px] text-[#166534] shrink-0">check_circle</span>
                    <div class="space-y-1">
                        <h2 class="font-headline text-lg sm:text-xl font-bold text-[#166534]">
                            Pengaduan Berhasil Diajukan ({{ $submitted['ticket_id'] }})
                        </h2>
                        <p class="text-xs sm:text-sm text-[#166534]/90">
                            Laporan Anda pada kategori <strong>{{ $submitted['category'] }}</strong> telah tercatat di basis data pengaduan BHIMA dan diteruskan ke antrean verifikator.
                        </p>
                    </div>
                </div>
                <div class="pt-1 flex flex-wrap gap-2.5">
                    <a
                        href="{{ route('consultation.index') }}"
                        class="inline-flex items-center gap-1.5 bg-[#0F766E] hover:bg-[#115E59] text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl shadow-sm transition-all"
                    >
                        <span class="material-symbols-outlined text-[17px]">chat</span>
                        Kembali ke Ruang Konsultasi
                    </a>
                </div>
            </section>
        @endif

        <section class="bg-white rounded-2xl border border-[#CCECE6] shadow-[0_4px_16px_rgba(15,118,110,0.05)] overflow-hidden">
            <div class="bg-[#0F766E] text-white px-4 sm:px-6 py-4 sm:py-5 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[26px] text-[#9cf2e8] shrink-0">campaign</span>
                    <div>
                        <h1 class="font-headline text-lg sm:text-2xl font-bold text-white">Formulir Pengaduan Masyarakat</h1>
                        <p class="text-xs text-[#CCECE6] mt-0.5">
                            Data awal di bawah telah diisi otomatis dari hasil konsultasi dan analisis pasal. Anda dapat mengedit seluruh isian sebelum mengirim.
                        </p>
                    </div>
                </div>
                @if ($sessionUuid !== '')
                    <span class="px-2.5 py-1 rounded-lg bg-[#115E59] border border-[#2dd4bf]/30 text-[11px] font-bold text-[#9cf2e8]">
                        Sesi: #BHM-{{ strtoupper(substr($sessionUuid, 0, 8)) }}
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('complaint.store') }}" class="p-4 sm:p-6 md:p-8 space-y-5" id="complaint-escalation-form">
                @csrf
                <input type="hidden" name="session_id" value="{{ old('session_id', $sessionUuid) }}">
                @if (!empty($messageId))
                    <input type="hidden" name="message_id" value="{{ old('message_id', $messageId) }}">
                @endif

                @if ($errors->any())
                    <div role="alert" class="p-3.5 bg-[#FEE2E2] border border-[#FECACA] rounded-xl text-xs sm:text-sm text-[#991B1B] font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reporter" class="block text-xs sm:text-sm font-bold text-text-dark mb-1.5">
                            Nama Pelapor (Opsional / Anonim)
                        </label>
                        <input
                            type="text"
                            id="reporter"
                            name="reporter"
                            maxlength="150"
                            value="{{ old('reporter', $prefill['reporter']) }}"
                            class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] px-3.5 py-2.5 text-xs sm:text-sm text-text-dark focus:border-[#0F766E] focus:bg-white focus:outline-none"
                            placeholder="Anonim"
                        >
                    </div>

                    <div>
                        <label for="category" class="block text-xs sm:text-sm font-bold text-text-dark mb-1.5">
                            Kategori Pengaduan <span class="text-[#991B1B]">*</span>
                        </label>
                        <select
                            id="category"
                            name="category"
                            required
                            class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] px-3.5 py-2.5 text-xs sm:text-sm text-text-dark focus:border-[#0F766E] focus:bg-white focus:outline-none"
                        >
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" @selected(old('category', $prefill['category']) === $cat)>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="summary" class="block text-xs sm:text-sm font-bold text-text-dark mb-1.5">
                        Ringkasan Pokok Pengaduan <span class="text-[#991B1B]">*</span>
                    </label>
                    <input
                        type="text"
                        id="summary"
                        name="summary"
                        required
                        minlength="5"
                        maxlength="200"
                        value="{{ old('summary', $prefill['summary']) }}"
                        class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] px-3.5 py-2.5 text-xs sm:text-sm text-text-dark focus:border-[#0F766E] focus:bg-white focus:outline-none"
                        placeholder="Tuliskan judul singkat permasalahan yang diadukan..."
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="reported" class="block text-xs sm:text-sm font-bold text-text-dark mb-1.5">
                            Pihak / Instansi Terlapor
                        </label>
                        <input
                            type="text"
                            id="reported"
                            name="reported"
                            maxlength="200"
                            value="{{ old('reported', $prefill['reported']) }}"
                            class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] px-3.5 py-2.5 text-xs sm:text-sm text-text-dark focus:border-[#0F766E] focus:bg-white focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="location" class="block text-xs sm:text-sm font-bold text-text-dark mb-1.5">
                            Lokasi Kejadian
                        </label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            maxlength="200"
                            value="{{ old('location', $prefill['location']) }}"
                            class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] px-3.5 py-2.5 text-xs sm:text-sm text-text-dark focus:border-[#0F766E] focus:bg-white focus:outline-none"
                            placeholder="Contoh: Kantor Pelayanan Desa / Kecamatan"
                        >
                    </div>

                    <div>
                        <label for="incidentDate" class="block text-xs sm:text-sm font-bold text-text-dark mb-1.5">
                            Tanggal Kejadian
                        </label>
                        <input
                            type="date"
                            id="incidentDate"
                            name="incidentDate"
                            value="{{ old('incidentDate', $prefill['incidentDate']) }}"
                            class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] px-3.5 py-2.5 text-xs sm:text-sm text-text-dark focus:border-[#0F766E] focus:bg-white focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <label for="chronology" class="block text-xs sm:text-sm font-bold text-text-dark mb-1.5">
                        Kronologi Kejadian Warga (Dapat Diedit) <span class="text-[#991B1B]">*</span>
                    </label>
                    <textarea
                        id="chronology"
                        name="chronology"
                        rows="5"
                        required
                        minlength="10"
                        maxlength="4000"
                        class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] p-3.5 text-xs sm:text-sm text-text-dark focus:border-[#0F766E] focus:bg-white focus:outline-none leading-relaxed"
                        placeholder="Uraikan kronologi kejadian secara lengkap..."
                    >{{ old('chronology', $prefill['chronology']) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label for="ai" class="block text-xs sm:text-sm font-bold text-[#0F766E] mb-1.5">
                            Ringkasan Pasal Terkait &amp; Estimasi Sanksi (Hasil Analisis AI)
                        </label>
                        <textarea
                            id="ai"
                            name="ai"
                            rows="4"
                            maxlength="5000"
                            class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] p-3 text-xs text-text-body focus:border-[#0F766E] focus:bg-white focus:outline-none leading-relaxed"
                            placeholder="Ringkasan pasal berpotensi terkait dari hasil analisis..."
                        >{{ old('ai', $prefill['ai']) }}</textarea>
                    </div>

                    <div>
                        <label for="references_text" class="block text-xs sm:text-sm font-bold text-[#0F766E] mb-1.5">
                            Rujukan Dokumen JDIH &amp; SOP (1 Dokumen per Baris)
                        </label>
                        <textarea
                            id="references_text"
                            name="references_text"
                            rows="4"
                            maxlength="5000"
                            class="w-full rounded-xl border border-[#CCECE6] bg-[#F0FAF8] p-3 text-xs text-text-body focus:border-[#0F766E] focus:bg-white focus:outline-none leading-relaxed"
                            placeholder="Daftar dokumen rujukan JDIH &amp; SOP..."
                        >{{ old('references_text', $prefill['references_text']) }}</textarea>
                    </div>
                </div>

                <div class="p-3 bg-[#FEF3C7] border border-[#FDE68A] rounded-xl text-xs text-[#92400E] flex items-start gap-2">
                    <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5">info</span>
                    <span>
                        <strong>Informasi Umum:</strong> Hasil analisis pasal dan estimasi ancaman sanksi yang terlampir merupakan informasi awal berdasarkan bunyi pasal (bukan prediksi putusan atau nasihat hukum resmi) untuk membantu verifikator menindaklanjuti laporan Anda.
                    </span>
                </div>

                <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <a
                        href="{{ route('consultation.index') }}"
                        class="px-4 py-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-[#F3F4F6] text-text-body font-semibold text-xs sm:text-sm text-center transition-colors"
                    >
                        Batal &amp; Kembali
                    </a>
                    <button
                        type="submit"
                        id="submit-complaint-btn"
                        class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#F59E0B] hover:bg-[#D97706] text-[#111827] font-bold text-xs sm:text-sm shadow-sm transition-all cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">send</span>
                        <span>Kirim Pengaduan Resmi</span>
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
