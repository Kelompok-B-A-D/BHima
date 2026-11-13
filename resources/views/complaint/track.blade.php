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
        <style>
            @layer base {
                html,body {
                    margin: 0;
                    padding: 0;
                }

                body {
                    overscroll-behavior: none;
                }

                main>:first-child {
                    margin-top: 0!important;
                }

                main>:last-child {
                    margin-bottom: 0!important;
                }
            }

            ::-webkit-scrollbar {
                display: none;
            }
        </style>
        <script src="https://cdn.tailwindcss.com"></script>
        <script id="tailwind-config">
            tailwind.config = {
                darkMode: "class",
                theme: {
                    extend: {
                        "colors": {
                            "on-secondary": "#ffffff",
                            "on-tertiary-fixed-variant": "#653e00",
                            "on-tertiary-fixed": "#2a1700",
                            "inverse-primary": "#8fd3cc",
                            "secondary-fixed": "#9cf2e8",
                            "inverse-surface": "#27313f",
                            "outline-variant": "#bec9c7",
                            "on-error-container": "#93000a",
                            "surface": "#f8f9ff",
                            "error-container": "#ffdad6",
                            "tertiary-fixed": "#ffddb8",
                            "surface-tint": "#216963",
                            "primary-fixed": "#abefe8",
                            "primary": "#004541",
                            "on-tertiary-container": "#ffbb66",
                            "secondary-container": "#99efe5",
                            "secondary-fixed-dim": "#80d5cb",
                            "outline": "#6f7977",
                            "surface-container-lowest": "#ffffff",
                            "surface-variant": "#d9e3f6",
                            "on-surface": "#121c2a",
                            "on-primary-fixed-variant": "#00504b",
                            "on-secondary-fixed-variant": "#00504a",
                            "surface-bright": "#f8f9ff",
                            "surface-container-high": "#dee9fc",
                            "secondary": "#006a63",
                            "on-secondary-fixed": "#00201d",
                            "on-error": "#ffffff",
                            "primary-fixed-dim": "#8fd3cc",
                            "on-tertiary": "#ffffff",
                            "on-surface-variant": "#3f4947",
                            "tertiary-container": "#774a00",
                            "tertiary-fixed-dim": "#ffb95f",
                            "surface-container-low": "#eff4ff",
                            "tertiary": "#583500",
                            "surface-container-highest": "#d9e3f6",
                            "on-secondary-container": "#006f67",
                            "on-primary-container": "#91d5ce",
                            "surface-dim": "#d0dbed",
                            "surface-container": "#e6eeff",
                            "error": "#ba1a1a",
                            "background": "#f8f9ff",
                            "on-primary-fixed": "#00201e",
                            "on-primary": "#ffffff",
                            "inverse-on-surface": "#eaf1ff",
                            "primary-container": "#115e59",
                            "on-background": "#121c2a"
                        },
                        "borderRadius": {
                            "DEFAULT": "0.25rem",
                            "lg": "0.5rem",
                            "xl": "0.75rem",
                            "full": "9999px"
                        },
                        "spacing": {
                            "space-lg": "1.5rem",
                            "space-xs": "0.25rem",
                            "margin-mobile": "1rem",
                            "gutter": "1.5rem",
                            "space-xl": "2.5rem",
                            "space-md": "1rem",
                            "gutter-sm": "1rem",
                            "space-sm": "0.5rem",
                            "margin": "2rem"
                        },
                        "fontFamily": {
                            "headline-lg": ["Newsreader"],
                            "label-md": ["Manrope"],
                            "title-md": ["Manrope"],
                            "body-lg": ["Manrope"],
                            "headline-lg-mobile": ["Newsreader"],
                            "display": ["Newsreader"],
                            "headline-sm": ["Newsreader"],
                            "label-lg": ["Manrope"],
                            "title-lg": ["Manrope"],
                            "label-sm": ["Manrope"],
                            "headline-md": ["Newsreader"],
                            "body-sm": ["Manrope"],
                            "body-md": ["Manrope"]
                        },
                        "fontSize": {
                            "headline-lg": ["36px", {
                                "lineHeight": "44px",
                                "letterSpacing": "-0.015em",
                                "fontWeight": "600"
                            }],
                            "label-md": ["12px", {
                                "lineHeight": "16px",
                                "letterSpacing": "0.02em",
                                "fontWeight": "600"
                            }],
                            "title-md": ["16px", {
                                "lineHeight": "24px",
                                "fontWeight": "600"
                            }],
                            "body-lg": ["16px", {
                                "lineHeight": "26px",
                                "fontWeight": "400"
                            }],
                            "headline-lg-mobile": ["28px", {
                                "lineHeight": "36px",
                                "letterSpacing": "-0.01em",
                                "fontWeight": "600"
                            }],
                            "display": ["48px", {
                                "lineHeight": "56px",
                                "letterSpacing": "-0.02em",
                                "fontWeight": "600"
                            }],
                            "headline-sm": ["20px", {
                                "lineHeight": "28px",
                                "fontWeight": "500"
                            }],
                            "label-lg": ["14px", {
                                "lineHeight": "20px",
                                "letterSpacing": "0.01em",
                                "fontWeight": "600"
                            }],
                            "title-lg": ["18px", {
                                "lineHeight": "26px",
                                "fontWeight": "700"
                            }],
                            "label-sm": ["11px", {
                                "lineHeight": "14px",
                                "letterSpacing": "0.04em",
                                "fontWeight": "700"
                            }],
                            "headline-md": ["26px", {
                                "lineHeight": "34px",
                                "letterSpacing": "-0.01em",
                                "fontWeight": "500"
                            }],
                            "body-sm": ["12px", {
                                "lineHeight": "18px",
                                "fontWeight": "400"
                            }],
                            "body-md": ["14px", {
                                "lineHeight": "22px",
                                "fontWeight": "400"
                            }]
                        }
                    }
                }
            };
        </script>
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
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="layanan" href="#">Layanan</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="pengumuman" href="#">Pengumuman</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="anggaran" href="#">Anggaran</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="peraturan" href="#">Peraturan</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="asisten-hukum" href="{{ route('consultation.index') }}">Asisten Hukum</a>
                    <a class="font-label-lg text-label-lg text-inverse-on-surface/80 hover:text-on-primary transition-colors py-space-xs" data-path="lacak-tiket" href="{{ route('complaint.track') }}">Lacak Tiket</a>
                </nav>
                <div class="flex items-center gap-space-md">
                    <a class="inline-flex items-center justify-center font-bold px-4 py-2 rounded-lg shadow-sm transition-all hover:opacity-90 text-[14px]" data-path="formulir-pengaduan" href="{{ route('complaint.create') }}" style="background-color: #F59E0B; color: #111827;">Lapor</a>
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                    </div>
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
                    <input type="text" name="tiket" value="{{ old('tiket') }}" placeholder="Contoh: BHM-2026-0001" required class="flex-1 h-11 px-space-md rounded-lg bg-surface text-on-surface shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <button type="submit" class="h-11 px-space-lg rounded-lg bg-primary hover:bg-primary-container text-on-primary font-bold">Lacak</button>
                </form>

                @if ($errors->any())
                    <div role="alert" class="bg-error-container text-on-error-container rounded-xl p-space-md">{{ $errors->first() }}</div>
                @endif

                @if (session('searched'))
                    @php
                        $result = session('result');
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
                            <a class="hover:text-on-surface transition-colors" data-path="layanan" href="#">Layanan Publik</a>
                        </li>
                        <li class="">
                            <a class="hover:text-on-surface transition-colors" data-path="peraturan" href="#">Jaringan Dokumentasi Peraturan</a>
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
