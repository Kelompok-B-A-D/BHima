<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="web_standard" name="shell-type">
        <title>BHIMA - Lacak Tiket</title>
        <link href="https://fonts.googleapis.com" rel="preconnect">
        <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&amp;family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400&amp;display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-background font-body-md text-on-surface antialiased">
        <header class="fixed top-0 w-full z-50 bg-primary-container text-on-primary shadow-[0_1px_8px_rgba(0,0,0,0.06)]">
            <div class="h-20 max-w-7xl mx-auto px-margin flex items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-sm">
                    <div class="w-10 h-10 rounded-lg bg-surface-container-lowest/10 flex items-center justify-center text-primary-fixed">
                        <span class="material-symbols-outlined text-[26px]">balance</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-title-lg text-title-lg tracking-tight text-on-primary font-bold leading-none">BHIMA</span>
                        <span class="font-label-sm text-label-sm text-primary-fixed uppercase tracking-wider opacity-90">Bantuan Hukum &amp; Informasi</span>
                    </div>
                </div>
                <nav class="hidden xl:flex items-center gap-space-md" data-active-classes="text-primary-fixed font-title-md">
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="beranda" href="{{ route('consultation.home') }}">Beranda</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="layanan" href="{{ route('consultation.home') }}#layanan">Layanan</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="pengumuman" href="{{ route('consultation.home') }}#pengumuman">Pengumuman</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="anggaran" href="{{ route('consultation.home') }}#anggaran">Anggaran</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="peraturan" href="{{ route('consultation.home') }}#peraturan">Peraturan</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="asisten-hukum" href="{{ route('consultation.index') }}">Asisten Hukum</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="lacak-tiket" href="{{ route('complaint.track') }}">Lacak Tiket</a>
                </nav>
                <div class="flex items-center gap-space-md">
                    <a class="inline-flex items-center justify-center font-bold px-4 py-2 rounded-lg shadow-sm transition-all hover:opacity-90 text-[14px]" data-path="formulir-pengaduan" href="{{ route('complaint.create') }}" style="background-color: #F59E0B; color: #111827;">Lapor</a>
                    <a href="{{ route('login') }}" title="Portal Petugas BHIMA" aria-label="Portal Petugas BHIMA" class="w-9 h-9 rounded-full bg-primary hover:bg-secondary flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                    </a>
                </div>
            </div>
        </header>
        <main class="w-full pt-20 bg-background min-h-screen">
            <div class="max-w-3xl mx-auto px-margin py-space-xl space-y-space-lg">
                <div class="space-y-space-xs">
                    <span class="font-label-lg text-label-lg text-secondary uppercase tracking-widest font-bold">Pantau Laporan Anda</span>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface font-semibold tracking-tight">Lacak Status Pengaduan</h1>
                    <p class="font-body-lg text-body-lg text-on-surface-variant">Masukkan nomor tiket untuk melihat status dan riwayat penanganan laporan Anda, tanpa perlu membuat akun.</p>
                </div>

                <form method="POST" action="{{ route('complaint.track.submit') }}" class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col sm:flex-row gap-space-sm">
                    @csrf
                    <input type="text" name="tiket" value="{{ old('tiket', $autoTicket ?? request('tiket')) }}" placeholder="Contoh: BHM-2026-0001" required class="flex-1 h-11 px-space-md rounded-lg bg-surface text-on-surface shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <button type="submit" class="h-11 px-space-lg rounded-lg bg-primary hover:bg-primary-container text-on-primary font-bold">Lacak</button>
                </form>

                @if ($errors->any())
                    <div role="alert" class="bg-error-container text-on-error-container rounded-xl p-space-md">{{ $errors->first() }}</div>
                @endif

                @if (session('searched') || ($autoSearched ?? false))
                    @php
                        $result = session('searched') ? session('result') : ($autoResult ?? null);
                    @endphp
                    @if ($result)
                        @php
                            $stages = ['Diterima', 'Diperiksa', 'Diproses', 'Selesai'];
                            $current = $result['status'];
                            $rejected = $current === 'Ditolak';
                            $idx = $current === 'Perlu Dilengkapi' ? 1 : array_search($current, $stages);
                            $idx = $idx === false ? 0 : $idx;
                        @endphp

                        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm space-y-space-md">
                            <div class="flex flex-wrap items-center justify-between gap-space-sm">
                                <div>
                                    <div class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">Nomor Tiket</div>
                                    <div class="font-title-lg text-title-lg text-primary font-bold font-mono">{{ $result['ticket'] }}</div>
                                </div>
                                <span class="inline-flex px-3 py-1 rounded-full font-label-md text-label-md font-bold {{ $rejected ? 'bg-error-container text-on-error-container' : 'bg-secondary-container text-on-secondary-container' }}">{{ $current }}</span>
                            </div>
                            <dl class="grid grid-cols-2 gap-space-md font-body-md text-body-md">
                                <div><dt class="text-on-surface-variant">Kategori</dt><dd class="font-semibold">{{ $result['category'] ?? '-' }}</dd></div>
                                <div><dt class="text-on-surface-variant">Tanggal diterima</dt><dd class="font-semibold">{{ $result['date'] ?? '-' }}</dd></div>
                            </dl>
                            @if ($result['agency'])
                                <div class="bg-surface-container-low rounded-lg p-space-md">
                                    <div class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">Instansi tujuan disposisi</div>
                                    <div class="font-semibold">{{ $result['agency'] }}</div>
                                </div>
                            @endif
                        </div>

                        @if ($current === 'Perlu Dilengkapi')
                            <div class="bg-tertiary-fixed text-on-tertiary-fixed rounded-xl p-space-md">
                                <strong>Perlu dilengkapi.</strong> Petugas meminta kelengkapan berkas untuk laporan ini.
                            </div>
                        @endif

                        @if ($rejected)
                            <div class="bg-error-container text-on-error-container rounded-xl p-space-md">
                                <strong>Laporan ditolak.</strong> Laporan berada di luar kewenangan atau tidak memenuhi syarat. Anda dapat mengajukan laporan baru dengan data yang sesuai.
                            </div>
                        @else
                            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
                                <h2 class="font-title-md text-title-md font-bold mb-space-md">Tahapan penanganan</h2>
                                <ol class="space-y-space-md">
                                    @foreach ($stages as $i => $stage)
                                        <li class="flex items-center gap-space-sm">
                                            <span class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-label-md
                                                {{ $i < $idx ? 'bg-secondary text-on-secondary' : ($i === $idx ? 'bg-primary text-on-primary ring-4 ring-primary-fixed' : 'bg-surface-container-high text-outline') }}">{{ $i + 1 }}</span>
                                            <span class="{{ $i <= $idx ? 'font-semibold text-on-surface' : 'text-outline' }}">{{ $stage }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
                            <h2 class="font-title-md text-title-md font-bold mb-space-md">Riwayat penanganan</h2>
                            <ul class="space-y-space-sm">
                                @foreach ($result['history'] as $h)
                                    <li class="flex justify-between gap-space-md border-b border-outline-variant/20 pb-space-xs">
                                        <span class="font-semibold">{{ $h['status'] }}</span>
                                        <span class="text-on-surface-variant font-body-sm text-body-sm">{{ $h['date'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="bg-surface-container-low rounded-xl p-space-lg text-on-surface-variant">
                            @if ($current === 'Selesai')
                                Laporan telah selesai ditangani. Formulir evaluasi kepuasan akan tersedia di sini.
                            @else
                                <strong>Evaluasi kepuasan</strong> dapat diisi setelah laporan selesai.
                            @endif
                        </div>
                    @else
                        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm text-on-surface-variant">
                            Tiket tidak ditemukan. Periksa kembali nomor tiket Anda.
                        </div>
                    @endif
                @endif
            </div>
        </main>
        <footer class="w-full bg-surface-container-low text-on-surface-variant">
            <div class="max-w-7xl mx-auto px-margin py-space-xl grid grid-cols-1 md:grid-cols-4 gap-space-xl">
                <div class="space-y-space-sm md:col-span-2">
                    <div class="flex items-center gap-space-sm">
                        <div class="w-8 h-8 rounded bg-primary flex items-center justify-center text-on-primary">
                            <span class="material-symbols-outlined text-[20px]">balance</span>
                        </div>
                        <span class="font-title-lg text-title-lg text-on-surface font-bold">BHIMA</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-md">Sistem portal integrasi advokasi, pengaduan publik, dan transparansi informasi hukum masyarakat yang diselenggarakan secara adil, aman, dan akuntabel.</p>
                </div>
                <div>
                    <h4 class="font-title-md text-title-md text-on-surface mb-space-sm">Tautan Penting</h4>
                    <ul class="space-y-space-xs font-body-md text-body-md">
                        <li class="">
                            <a class="hover:text-on-surface transition-colors" data-path="layanan" href="{{ route('consultation.home') }}#layanan">Layanan Publik</a>
                        </li>
                        <li class="">
                            <a class="hover:text-on-surface transition-colors" data-path="peraturan" href="{{ route('consultation.home') }}#peraturan">Jaringan Dokumentasi Peraturan</a>
                        </li>
                        <li class="">
                            <a class="hover:text-on-surface transition-colors" data-path="lacak-tiket" href="{{ route('complaint.track') }}">Pelacakan Pengaduan</a>
                        </li>
                        <li class="">
                            <a class="hover:text-on-surface transition-colors" data-path="formulir-pengaduan" href="{{ route('complaint.create') }}">Formulir Lapor</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-title-md text-title-md text-on-surface mb-space-sm">Pusat Layanan</h4>
                    <p class="font-body-md text-body-md">
                        Gedung Pelayanan Terpadu<br>Email: pengaduan@bhima.go.id<br>Pelayanan Senin – Jumat: 08.00 – 16.00 WIB
                    </p>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-margin py-space-md border-t border-outline-variant/30 text-center font-label-md text-label-md text-on-surface-variant">© 2026 Portal Layanan Publik BHIMA. Hak Cipta Dilindungi Undang-Undang.</div>
        </footer>
    </body>
</html>
