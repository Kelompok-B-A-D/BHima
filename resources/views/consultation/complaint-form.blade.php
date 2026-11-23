<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Formulir Eskalasi Pengaduan Warga · BHIMA</title>
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
        @include('partials.liquid-header', ['activeNav' => 'formulir-pengaduan'])

        <main id="portal-main-content" class="w-full pt-24 min-[641px]:pt-28 pb-10 min-[641px]:pb-14 px-4 min-[641px]:px-6 max-w-[960px] mx-auto space-y-6 stitch-print-full">
            <div class="stitch-no-print flex flex-wrap items-center justify-between gap-3">
                <a
                    href="{{ route('consultation.index') }}"
                    class="btn-glass !min-h-[44px] px-4 text-xs min-[641px]:text-sm inline-flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">arrow_back</span>
                    <span>Kembali ke Ruang Konsultasi</span>
                </a>
                @if ($sessionUuid !== '')
                    <span class="px-3 py-1.5 rounded-full bg-white border border-[#DCE4F0] text-xs font-bold text-[#005EB8] font-mono">
                        Sesi: #BHM-{{ strtoupper(substr($sessionUuid, 0, 8)) }}
                    </span>
                @endif
            </div>

            @if (session('complaint_submitted'))
                @php
                    $submitted = session('complaint_submitted');
                @endphp
                <section role="status" class="rounded-[24px] bg-[#DCFCE7] border border-[#BBF7D0] text-[#166534] p-5 min-[641px]:p-6 space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-[24px] shrink-0 mt-0.5" aria-hidden="true">check_circle</span>
                        <div class="space-y-1">
                            <h2 class="text-lg font-bold text-[#166534]">
                                Pengaduan Berhasil Diajukan ({{ $submitted['ticket_id'] }})
                            </h2>
                            <p class="text-sm leading-relaxed">
                                Laporan Anda pada kategori <strong>{{ $submitted['category'] }}</strong> telah tercatat di basis data pengaduan BHIMA dan diteruskan ke antrean verifikator.
                            </p>
                        </div>
                    </div>
                    <div class="pt-1 flex flex-wrap gap-3">
                        <a
                            href="{{ route('complaint.track', ['tiket' => $submitted['ticket_id']]) }}"
                            class="btn-primary !min-h-[44px] px-5 text-xs min-[641px]:text-sm inline-flex items-center gap-1.5"
                        >
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">manage_search</span>
                            <span>Lacak Status Tiket</span>
                        </a>
                        <a
                            href="{{ route('consultation.index') }}"
                            class="btn-glass !min-h-[44px] px-5 text-xs min-[641px]:text-sm inline-flex items-center gap-1.5"
                        >
                            <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">chat</span>
                            <span>Kembali ke Ruang Konsultasi</span>
                        </a>
                    </div>
                </section>
            @endif

            <section class="surface-card hero-enter overflow-hidden">
                <div class="bg-[#1E3A8A] text-white px-5 min-[641px]:px-7 py-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[26px] text-[#F2D8B3] shrink-0" aria-hidden="true">campaign</span>
                        <div>
                            <h1 class="type-h2 text-white">Formulir Pengaduan Masyarakat</h1>
                            <p class="text-xs min-[641px]:text-sm text-[#E2ECFF] mt-0.5">
                                Data awal di bawah telah diisi otomatis dari hasil konsultasi dan analisis pasal. Anda dapat mengedit seluruh isian sebelum mengirim.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('complaint.store') }}" class="p-5 min-[641px]:p-7 space-y-5" id="complaint-escalation-form">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ old('session_id', $sessionUuid) }}">
                    @if (! empty($messageId))
                        <input type="hidden" name="message_id" value="{{ old('message_id', $messageId) }}">
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="p-4 bg-[#FEE2E2] border border-[#FCA5A5] rounded-[16px] text-xs min-[641px]:text-sm text-[#991B1B] font-semibold space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>• {{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="reporter" class="block text-xs font-bold text-[#0B1020]">
                                Nama Pelapor (Opsional / Anonim)
                            </label>
                            <input
                                type="text"
                                id="reporter"
                                name="reporter"
                                maxlength="150"
                                value="{{ old('reporter', $prefill['reporter']) }}"
                                class="glass-input w-full min-h-[48px] px-4 text-sm placeholder:text-[#4A5366]"
                                placeholder="Anonim"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="category" class="block text-xs font-bold text-[#0B1020]">
                                Kategori Pengaduan <span class="text-[#991B1B]">*</span>
                            </label>
                            <select
                                id="category"
                                name="category"
                                required
                                class="glass-input w-full min-h-[48px] px-4 text-sm"
                            >
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" @selected(old('category', $prefill['category']) === $cat)>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="summary" class="block text-xs font-bold text-[#0B1020]">
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
                            class="glass-input w-full min-h-[48px] px-4 text-sm placeholder:text-[#4A5366]"
                            placeholder="Tuliskan judul singkat permasalahan yang diadukan..."
                        >
                    </div>

                    <div class="grid grid-cols-1 min-[641px]:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label for="reported" class="block text-xs font-bold text-[#0B1020]">
                                Pihak / Instansi Terlapor
                            </label>
                            <input
                                type="text"
                                id="reported"
                                name="reported"
                                maxlength="200"
                                value="{{ old('reported', $prefill['reported']) }}"
                                class="glass-input w-full min-h-[48px] px-4 text-sm placeholder:text-[#4A5366]"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="location" class="block text-xs font-bold text-[#0B1020]">
                                Lokasi Kejadian
                            </label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                maxlength="200"
                                value="{{ old('location', $prefill['location']) }}"
                                class="glass-input w-full min-h-[48px] px-4 text-sm placeholder:text-[#4A5366]"
                                placeholder="Contoh: Kantor Pelayanan Desa / Kecamatan"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="incidentDate" class="block text-xs font-bold text-[#0B1020]">
                                Tanggal Kejadian
                            </label>
                            <input
                                type="date"
                                id="incidentDate"
                                name="incidentDate"
                                value="{{ old('incidentDate', $prefill['incidentDate']) }}"
                                class="glass-input w-full min-h-[48px] px-4 text-sm"
                            >
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="chronology" class="block text-xs font-bold text-[#0B1020]">
                            Kronologi Kejadian Warga (Dapat Diedit) <span class="text-[#991B1B]">*</span>
                        </label>
                        <textarea
                            id="chronology"
                            name="chronology"
                            rows="5"
                            required
                            minlength="10"
                            maxlength="4000"
                            class="glass-input w-full p-4 text-sm placeholder:text-[#4A5366] leading-relaxed"
                            placeholder="Uraikan kronologi kejadian secara lengkap..."
                        >{{ old('chronology', $prefill['chronology']) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-4 pt-1">
                        <div class="space-y-1.5">
                            <label for="ai" class="block text-xs font-bold text-[#0F766E]">
                                Ringkasan Pasal Terkait &amp; Estimasi Sanksi (Hasil Analisis AI)
                            </label>
                            <textarea
                                id="ai"
                                name="ai"
                                rows="4"
                                maxlength="5000"
                                class="glass-input w-full p-3.5 text-xs leading-relaxed"
                                placeholder="Ringkasan pasal berpotensi terkait dari hasil analisis..."
                            >{{ old('ai', $prefill['ai']) }}</textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label for="references_text" class="block text-xs font-bold text-[#0F766E]">
                                Rujukan Dokumen JDIH &amp; SOP (1 Dokumen per Baris)
                            </label>
                            <textarea
                                id="references_text"
                                name="references_text"
                                rows="4"
                                maxlength="5000"
                                class="glass-input w-full p-3.5 text-xs leading-relaxed"
                                placeholder="Daftar dokumen rujukan JDIH &amp; SOP..."
                            >{{ old('references_text', $prefill['references_text']) }}</textarea>
                        </div>
                    </div>

                    <div class="p-4 bg-[#FEF3C7] border border-[#F5D07A] rounded-[16px] text-xs text-[#78350F] flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#92400E] shrink-0 mt-0.5" aria-hidden="true">info</span>
                        <span class="leading-relaxed">
                            <strong>Informasi Umum:</strong> Hasil analisis pasal dan estimasi ancaman sanksi yang terlampir merupakan informasi awal berdasarkan bunyi pasal (bukan prediksi putusan atau nasihat hukum resmi) untuk membantu verifikator menindaklanjuti laporan Anda.
                        </span>
                    </div>

                    <div class="pt-3 border-t border-[#DCE4F0] flex flex-col min-[481px]:flex-row items-stretch min-[481px]:items-center justify-end gap-3">
                        <a
                            href="{{ route('consultation.index') }}"
                            class="btn-glass px-5 text-sm inline-flex items-center justify-center"
                        >
                            Batal &amp; Kembali
                        </a>
                        <button
                            type="submit"
                            id="submit-complaint-btn"
                            class="btn-primary px-6 text-sm inline-flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">send</span>
                            <span>Kirim Pengaduan Resmi</span>
                        </button>
                    </div>
                </form>
            </section>
        </main>

        @include('partials.liquid-footer')
        @include('partials.liquid-bottom-nav', ['activeNav' => 'formulir-pengaduan'])

        <x-confirm-dialog
            id="confirm-escalation-complaint-dialog"
            title="Konfirmasi Pengiriman Pengaduan"
            description="Pastikan kronologi dan ringkasan pasal di atas sudah sesuai sebelum diteruskan ke antrean verifikator BHIMA."
            confirm-label="Ya, Kirim Pengaduan"
            cancel-label="Periksa Kembali"
            icon="send"
            tone="primary"
        />
    </div>

    <script>
        (function () {
            const form = document.getElementById('complaint-escalation-form');
            const dialog = document.getElementById('confirm-escalation-complaint-dialog');
            if (!form || !dialog) return;

            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmedSubmit === '1') return;
                if (!form.reportValidity()) return;
                if (typeof dialog.showModal === 'function') {
                    event.preventDefault();
                    dialog.showModal();
                }
            });

            dialog.addEventListener('bhima:dialog-result', (event) => {
                if (!event.detail?.confirmed) return;
                form.dataset.confirmedSubmit = '1';
                form.submit();
            });
        })();
    </script>
</body>
</html>
