<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document['title'] }} ({{ $document['number'] }}) · Rujukan JDIH &amp; SOP BHIMA</title>
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
            <div class="max-w-[1440px] w-full mx-auto flex items-center justify-between gap-3 text-[11px] sm:text-xs font-medium">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <div class="flex flex-col w-4 h-2.5 overflow-hidden rounded-[1px] border border-white/40 shrink-0">
                        <div class="h-1/2 bg-red-600"></div>
                        <div class="h-1/2 bg-white"></div>
                    </div>
                    <span class="tracking-wide truncate">Portal Resmi Edukasi Hukum &amp; Keterbukaan Informasi Publik - JDIH &amp; SOP Terpadu</span>
                </div>
                <span class="hidden sm:flex items-center gap-1.5 text-xs text-white/80 shrink-0">
                    <span class="material-symbols-outlined text-[15px] text-[#9cf2e8]">verified</span>
                    Dokumen Rujukan Terverifikasi
                </span>
            </div>
        </div>
        <div class="h-16 sm:h-20 max-w-[1440px] mx-auto px-3 sm:px-6 flex items-center justify-between gap-2 sm:gap-4">
            <a href="{{ route('consultation.home') }}" class="flex items-center gap-2.5 sm:gap-4 text-white no-underline min-w-0">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-[#9cf2e8] shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[28px]">balance</span>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="font-headline text-lg sm:text-2xl font-bold text-white tracking-tight leading-tight truncate">BHIMA</span>
                    <span class="text-[10px] sm:text-xs text-[#CCECE6] font-medium tracking-wide truncate">Bantuan Hukum &amp; Informasi Masyarakat</span>
                </div>
            </a>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a
                    href="{{ route('consultation.index') }}"
                    class="flex items-center gap-1.5 bg-[#F59E0B] hover:bg-[#D97706] text-[#111827] font-bold text-xs sm:text-sm px-3 sm:px-4 py-2 rounded-lg shadow-sm transition-all"
                >
                    <span class="material-symbols-outlined text-[17px] sm:text-[18px] text-[#111827]">arrow_back</span>
                    <span class="hidden sm:inline">Kembali ke Ruang Konsultasi</span>
                    <span class="sm:hidden">Kembali</span>
                </a>
            </div>
        </div>
    </header>

    <main class="pt-[108px] sm:pt-[132px] pb-12 sm:pb-16 px-3 sm:px-6 max-w-[1120px] mx-auto space-y-5 sm:space-y-6">
        <section class="bg-white rounded-2xl border border-[#CCECE6] shadow-[0_4px_16px_rgba(15,118,110,0.05)] p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3">
                <span class="px-2.5 sm:px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] text-[11px] sm:text-xs font-bold uppercase tracking-wider">
                    Sumber Rujukan Terverifikasi • {{ $document['category'] }}
                </span>
                <span class="text-[11px] sm:text-xs font-bold text-[#0F766E] bg-[#E6F5F2] border border-[#CCECE6] px-2.5 sm:px-3 py-1 rounded-lg">
                    Kode: {{ $document['key'] }}
                </span>
            </div>

            <h1 class="font-headline text-xl sm:text-2xl md:text-3xl font-bold text-text-dark tracking-tight">
                {{ $document['title'] }}
            </h1>

            <dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 p-3.5 sm:p-4 bg-[#F0FAF8] rounded-xl border border-[#CCECE6] text-xs">
                <div>
                    <dt class="text-text-muted font-medium">Kode Dokumen</dt>
                    <dd class="font-bold text-text-dark text-sm mt-0.5 sm:mt-1">{{ $document['key'] }}</dd>
                </div>
                <div>
                    <dt class="text-text-muted font-medium">Jenis &amp; Nomor</dt>
                    <dd class="font-bold text-text-dark text-sm mt-0.5 sm:mt-1">{{ $document['type'] }} • {{ $document['number'] }}</dd>
                </div>
                <div>
                    <dt class="text-text-muted font-medium">Tahun Penetapan</dt>
                    <dd class="font-bold text-text-dark text-sm mt-0.5 sm:mt-1">{{ $document['year'] }}</dd>
                </div>
                <div>
                    <dt class="text-text-muted font-medium">Bidang / Kategori</dt>
                    <dd class="font-bold text-[#0F766E] text-sm mt-0.5 sm:mt-1">{{ $document['topic'] }}</dd>
                </div>
            </dl>

            @if(!empty($document['summary']))
                <div class="space-y-1.5 pt-1 sm:pt-2">
                    <h2 class="text-xs sm:text-sm font-bold text-[#0F766E] uppercase tracking-wider">Ringkasan Dokumen</h2>
                    <p class="text-xs sm:text-sm text-text-body leading-relaxed whitespace-pre-wrap">{{ $document['summary'] }}</p>
                </div>
            @endif
        </section>

        <section class="bg-white rounded-2xl border border-[#CCECE6] shadow-[0_4px_16px_rgba(15,118,110,0.05)] overflow-hidden">
            <div class="bg-[#0F766E] text-white px-4 sm:px-6 py-3.5 sm:py-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px] text-[#9cf2e8] shrink-0">menu_book</span>
                    <div>
                        <h2 class="font-headline text-base sm:text-lg md:text-xl font-bold text-white">Daftar Potongan Pasal / Bagian Terindeks</h2>
                        <p class="text-[11px] sm:text-xs text-[#CCECE6]">{{ $chunks->count() }} bagian tersedia pada indeks pencarian RAG BHIMA</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6 md:p-8 space-y-3.5 sm:space-y-4">
                @forelse($chunks as $chunk)
                    @php
                        $anchor = \Illuminate\Support\Str::slug($chunk->article_label);
                    @endphp
                    <article
                        id="{{ $anchor }}"
                        class="reference-chunk-card p-3.5 sm:p-5 bg-[#F0FAF8] rounded-xl border border-[#CCECE6] space-y-2 scroll-mt-28 sm:scroll-mt-32 transition-all"
                    >
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <strong class="text-xs sm:text-sm font-bold text-[#0F766E] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[17px] sm:text-[18px]">gavel</span>
                                {{ $chunk->article_label }}
                            </strong>
                            <span class="px-2 sm:px-2.5 py-0.5 bg-white border border-[#CCECE6] text-[#0F766E] rounded-md text-[11px] sm:text-xs font-bold">
                                {{ $chunk->category }} • Bagian #{{ $chunk->chunk_index + 1 }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-text-body leading-relaxed whitespace-pre-wrap bg-white p-3 sm:p-4 rounded-lg border border-[#E2E8F0]">{{ $chunk->content }}</p>
                    </article>
                @empty
                    <div class="p-8 text-center text-text-muted text-sm">
                        Belum ada potongan pasal terindeks untuk dokumen ini.
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
