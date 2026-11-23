<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $document['title'] }} ({{ $document['number'] }}) · Rujukan JDIH &amp; SOP BHIMA</title>
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

    <div class="w-full overflow-x-hidden">
        @include('partials.liquid-header', ['activeNav' => 'asisten-hukum'])

        <main id="portal-main-content" class="w-full pt-24 min-[641px]:pt-28 pb-10 min-[641px]:pb-14 px-4 min-[641px]:px-6 max-w-[1120px] mx-auto space-y-6 stitch-print-full">
            <!-- Top Navigation Context Bar -->
            <div class="stitch-no-print flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 flex-wrap text-xs font-semibold text-[#4A5366]">
                    <a
                        href="{{ route('consultation.index') }}"
                        class="btn-glass !min-h-[44px] px-4 text-xs inline-flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">arrow_back</span>
                        <span>Kembali ke Ruang Konsultasi</span>
                    </a>
                    <span class="hidden min-[641px]:inline text-[#5A657A]">/</span>
                    <span class="hidden min-[641px]:inline text-[#0B1020] font-bold">Dokumen Rujukan {{ $document['key'] }}</span>
                </div>

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-glass !min-h-[44px] px-4 text-xs inline-flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-[18px] text-[#333E52]" aria-hidden="true">print</span>
                    <span>Cetak Naskah</span>
                </button>
            </div>

            <!-- Document Metadata Overview Surface -->
            <section class="surface-card hero-enter p-6 min-[641px]:p-8 space-y-5">
                @php
                    $docStatus = strtolower((string) ($document['status_berlaku'] ?? 'berlaku'));
                    $statusBadgeClass = match ($docStatus) {
                        'dicabut' => 'bg-[#FEE2E2] text-[#991B1B] border-[#FCA5A5]',
                        'diubah' => 'bg-[#FEF3C7] text-[#92400E] border-[#F5D07A]',
                        default => 'bg-[#DCFCE7] text-[#166534] border-[#BBF7D0]',
                    };
                    $statusLabel = match ($docStatus) {
                        'dicabut' => 'Dicabut (Arsip)',
                        'diubah' => 'Berlaku (Diubah)',
                        default => 'Berlaku',
                    };
                @endphp

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wider {{ $statusBadgeClass }}">
                            Status: {{ $statusLabel }} · {{ $document['category'] }}
                        </span>
                        @if(!empty($document['source_page_url']))
                            <a
                                href="{{ $document['source_page_url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E6F0FC] text-[#005EB8] border border-[#BFD7F5] text-xs font-bold hover:bg-[#D5E6FA] transition-colors"
                            >
                                <span class="material-symbols-outlined text-[15px]" aria-hidden="true">open_in_new</span>
                                <span>Sumber Resmi JDIH</span>
                            </a>
                        @endif
                    </div>
                    <span class="text-xs font-bold text-[#0F766E] bg-[#E6F5F3] border border-[#BFE6DF] px-3 py-1 rounded-full font-mono">
                        Kode: {{ $document['key'] }}
                    </span>
                </div>

                @if($docStatus === 'dicabut')
                    <div role="alert" class="p-4 rounded-[16px] bg-[#FEE2E2] border border-[#FCA5A5] text-[#991B1B] text-xs min-[641px]:text-sm font-semibold leading-relaxed">
                        Peraturan ini berstatus <strong>dicabut</strong>{{ !empty($document['dicabut_oleh']) ? ' oleh '.$document['dicabut_oleh'] : '' }} dan disimpan hanya sebagai arsip (tidak digunakan sebagai rujukan utama jawaban asisten).
                    </div>
                @elseif($docStatus === 'diubah' && !empty($document['diubah_oleh']))
                    <div class="p-4 rounded-[16px] bg-[#FEF3C7] border border-[#F5D07A] text-[#92400E] text-xs min-[641px]:text-sm font-semibold leading-relaxed">
                        Catatan perubahan: sebagian ketentuan peraturan ini <strong>diubah oleh {{ $document['diubah_oleh'] }}</strong>. Pastikan mengecek naskah konsolidasi terbaru pada tautan sumber resmi.
                    </div>
                @endif

                <h1 class="type-h1 text-[#0B1020]">
                    {{ $document['title'] }}
                </h1>

                <dl class="surface-nested grid grid-cols-1 min-[481px]:grid-cols-2 min-[1008px]:grid-cols-4 gap-4 p-4 min-[641px]:p-5 text-xs">
                    <div>
                        <dt class="text-[#4A5366] font-semibold">Kode Dokumen</dt>
                        <dd class="font-bold text-[#0B1020] text-sm mt-1 font-mono">{{ $document['key'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#4A5366] font-semibold">Jenis &amp; Nomor</dt>
                        <dd class="font-bold text-[#0B1020] text-sm mt-1">{{ $document['type'] }} · {{ $document['number'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#4A5366] font-semibold">Tahun Penetapan</dt>
                        <dd class="font-bold text-[#0B1020] text-sm mt-1">{{ $document['year'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#4A5366] font-semibold">Bidang / Kategori</dt>
                        <dd class="font-bold text-[#0F766E] text-sm mt-1">{{ $document['topic'] }}</dd>
                    </div>
                </dl>

                @if(!empty($document['summary']))
                    <div class="space-y-1.5 pt-1">
                        <h2 class="text-xs font-bold text-[#005EB8] uppercase tracking-wider">Ringkasan Dokumen</h2>
                        <p class="prose-measure text-sm min-[641px]:text-base text-[#333E52] leading-relaxed whitespace-pre-wrap">{{ $document['summary'] }}</p>
                    </div>
                @endif
            </section>

            <!-- Indexed Articles & Chunks Section -->
            <section class="surface-card hero-enter hero-enter-1 overflow-hidden">
                <div class="bg-[#1E3A8A] text-white px-5 min-[641px]:px-7 py-4 min-[641px]:py-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[24px] text-[#F2D8B3] shrink-0" aria-hidden="true">menu_book</span>
                        <div>
                            <h2 class="text-base min-[641px]:text-lg font-bold text-white">Daftar Potongan Pasal &amp; Bagian Terindeks</h2>
                            <p class="text-xs text-[#E2ECFF]">{{ $chunks->count() }} bagian tersedia pada indeks pencarian RAG BHIMA</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 min-[641px]:p-7 space-y-4">
                    @forelse($chunks as $chunk)
                        @php
                            $anchor = \Illuminate\Support\Str::slug($chunk->article_label);
                        @endphp
                        <article
                            id="{{ $anchor }}"
                            class="reference-chunk-card reveal-on-scroll surface-nested p-4 min-[641px]:p-5 space-y-3 scroll-mt-28 transition-all"
                        >
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <strong class="text-sm font-bold text-[#005EB8] flex items-center gap-1.5 flex-wrap">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">gavel</span>
                                    <span>{{ $chunk->article_label }}</span>
                                    @if(!empty($chunk->is_ketentuan_pidana))
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#FEF3C7] text-[#92400E] border border-[#F5D07A] text-xs font-bold uppercase">
                                            Ketentuan Sanksi ({{ strtoupper((string) ($chunk->sanksi_jenis ?? 'LAINNYA')) }})
                                        </span>
                                    @endif
                                    @if(!empty($chunk->rujuk_pasal))
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#E6F0FC] text-[#005EB8] text-xs font-bold">
                                            Rujuk: {{ $chunk->rujuk_pasal }}
                                        </span>
                                    @endif
                                </strong>
                                <span class="px-2.5 py-0.5 bg-white border border-[#DCE4F0] text-[#4A5366] rounded-full text-xs font-bold">
                                    {{ $chunk->category }} · Bagian #{{ $chunk->chunk_index + 1 }}{{ !empty($chunk->pdf_page) ? ' · Hal. '.$chunk->pdf_page : '' }}
                                </span>
                            </div>
                            <div class="bg-white p-4 rounded-[14px] border border-[#DCE4F0]">
                                <p class="prose-measure text-sm min-[641px]:text-base text-[#0B1020] leading-relaxed whitespace-pre-wrap">{{ $chunk->content }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="p-8 text-center text-[#4A5366] text-sm">
                            Belum ada potongan pasal terindeks untuk dokumen ini.
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        @include('partials.liquid-footer')
        @include('partials.liquid-bottom-nav', ['activeNav' => 'asisten-hukum'])
    </div>
</body>
</html>
