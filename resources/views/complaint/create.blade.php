<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="web_standard" name="shell-type">
        <title>BHIMA - Portal Bantuan Hukum dan Informasi Masyarakat</title>
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
            <div class="flex flex-col w-full">
                <!-- Top Ambient Glow & Pattern Layer -->
                <div class="relative w-full overflow-hidden bg-surface-container-low py-space-xl">
                    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[840px] h-[340px] bg-gradient-to-b from-primary-fixed-dim/25 via-secondary-container/20 to-transparent blur-3xl pointer-events-none rounded-full"></div>
                    <div class="max-w-7xl mx-auto px-margin relative z-10">
                        <!-- Breadcrumbs & Badges -->
                        <div class="flex flex-wrap items-center justify-between gap-space-sm mb-space-md">
                            <div class="flex items-center gap-space-xs font-label-md text-label-md text-on-surface-variant">
                                <a class="hover:text-primary transition-colors" data-path="beranda" href="{{ route('consultation.home') }}">Beranda</a>
                                <span class="material-symbols-outlined text-[16px] text-outline">chevron_right</span>
                                <a class="hover:text-primary transition-colors" data-path="layanan" href="#">Layanan Publik</a>
                                <span class="material-symbols-outlined text-[16px] text-outline">chevron_right</span>
                                <span class="text-primary font-semibold">Formulir Pengaduan Warga</span>
                            </div>
                        </div>
                        <!-- Section Title & Statutory Legal Assurance (UU PDP 27/2022) -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-end">
                            <div class="space-y-space-xs w-full lg:col-span-12">
                                <span class="font-label-lg text-label-lg text-secondary uppercase tracking-widest font-bold">Layanan Pengaduan Pelayanan Publik</span>
                                <h1 class="font-display text-display text-on-surface font-semibold tracking-tight">Formulir Pengaduan Pelayanan Publik &amp; Maladministrasi</h1>
                                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl leading-relaxed">Sampaikan laporan dugaan pungutan liar, pelayanan lambat, atau ketidakjelasan prosedur loket di kantor desa dan kelurahan. Setiap pengaduan ditelaah langsung oleh petugas verifikasi daerah secara transparan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Main Interaction Area (Split Flow: Active Form & Live Success State Frame) -->
                <div class="max-w-7xl mx-auto px-margin py-space-xl w-full">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl">
                        <!-- LEFT/PRIMARY: Active Citizen Complaint Form (7 Cols) -->
                        <div class="lg:col-span-7 space-y-space-lg">
                            <!-- STEP INDICATOR BAR -->
                            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
                                <div class="flex items-center gap-space-sm">
                                    <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-label-md text-label-md font-bold">1</div>
                                    <div>
                                        <div class="font-title-md text-title-md text-on-surface leading-tight">Pengisian Data &amp; Kronologi</div>
                                        <div class="font-label-sm text-label-sm text-on-surface-variant">Tahap 1 dari 2: Isi Laporan</div>
                                    </div>
                                </div>
                                <div class="hidden sm:flex items-center gap-space-xs text-secondary font-label-md text-label-md">
                                    <span class="material-symbols-outlined text-[18px]">verified</span>
                                    <span class="">Formulir Pengaduan Warga 2026</span>
                                </div>
                            </div>
                            <!-- Section Box: Pre-filled Virtual Legal Assistant Kronologi & Detected Articles -->
                            @if ($prefill)
                            <!-- Hasil konsultasi Asisten Hukum (pre-fill) -->
                            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm space-y-space-md">
                                <div class="flex items-start justify-between gap-space-sm">
                                    <div class="flex items-center gap-space-sm">
                                        <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[22px]">info</span>
                                        </div>
                                        <div>
                                            <h2 class="font-title-lg text-title-lg text-on-surface font-bold leading-tight">Hasil Konsultasi Asisten Hukum</h2>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant">Diambil dari sesi konsultasi terakhir Anda</p>
                                        </div>
                                    </div>
                                    <span class="px-space-sm py-1 rounded-full bg-secondary-container/40 text-on-secondary-container font-label-sm text-label-sm font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Siap Diteruskan
                                    </span>
                                </div>
                                <div class="bg-surface-container-low p-space-md rounded-lg space-y-space-xs">
                                    <div class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-bold">Ringkasan Kasus:</div>
                                    <blockquote class="font-body-md text-body-md text-on-surface italic leading-relaxed">“{{ $prefill['chronology'] }}”</blockquote>
                                </div>
                                <div class="space-y-space-xs pt-1">
                                    <label class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold">Kajian Pasal &amp; Regulasi yang Terindikasi:</label>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($prefill['citations'] as $c)
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container text-on-surface text-label-sm font-label-sm shadow-sm">
                                                <span class="material-symbols-outlined text-[16px] text-primary">gavel</span>
                                                <span>{{ data_get($c, 'document_name', 'Rujukan') }} {{ data_get($c, 'article_label') }}</span>
                                            </div>
                                        @empty
                                            <span class="font-body-sm text-body-sm text-on-surface-variant">Belum ada rujukan pasal pada sesi ini.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm text-on-surface-variant">
                                Belum ada hasil konsultasi. Anda dapat <a class="text-primary font-bold underline" href="{{ route('consultation.index') }}">bertanya ke Asisten Hukum</a> dulu, atau langsung mengisi formulir.
                            </div>
                            @endif
                            <!-- FORM INTERACTIVE FIELDS -->
                            @if ($errors->any())
                                <div role="alert" class="bg-error-container text-on-error-container rounded-xl p-space-md">
                                    <ul class="list-disc pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm space-y-space-lg" id="complaintForm" method="POST" action="{{ route('complaint.store') }}" enctype="multipart/form-data">
                            @csrf
                                <input type="hidden" name="session_uuid" value="{{ $prefill['session_uuid'] ?? '' }}">
                                <!-- Field 1: Category Dropdown -->
                                <div class="space-y-space-xs">
                                    <label class="block font-label-md text-label-md text-primary font-bold" for="categorySelect">
                                        Kategori Pokok Pengaduan <span class="text-error">*</span>
                                    </label>
                                    <div class="relative">
                                        <select class="w-full h-11 px-space-md pr-10 rounded-lg bg-surface text-on-surface font-body-md text-body-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary appearance-none cursor-pointer" id="categorySelect" name="kategori" required="">
                                            <option value="">Pilih kategori</option>
                                            @foreach (['Pungutan Liar', 'Pelayanan Lambat', 'Prosedur Tidak Jelas', 'Maladministrasi Lainnya'] as $k)
                                                <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline">expand_more</span>
                                    </div>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant">Pilih kategori yang paling sesuai dengan masalah Anda.</p>
                                </div>
                                <!-- Field 2: Instansi Terlapor -->
                                <div class="space-y-space-xs">
                                    <label class="block font-label-md text-label-md text-primary font-bold" for="reportedEntity">
                                        Pihak / Instansi Terlapor <span class="text-error">*</span>
                                    </label>
                                    <div class="relative">
                                        <input class="w-full h-11 px-space-md rounded-lg bg-surface text-on-surface font-body-md text-body-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary pl-10" id="reportedEntity" name="instansi_terlapor" required="" type="text" value="{{ old('instansi_terlapor') }}">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">domain</span>
                                    </div>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Sebutkan nama unit layanan atau petugas yang dilaporkan.</span>
                                </div>
                                <!-- Field 3: 2-Column Incident Date and Exact Location -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                                    <!-- Date -->
                                    <div class="space-y-space-xs">
                                        <label class="block font-label-md text-label-md text-primary font-bold" for="incidentDate">
                                            Tanggal Kejadian <span class="text-error">*</span>
                                        </label>
                                        <div class="relative">
                                            <input class="w-full h-11 px-space-md rounded-lg bg-surface text-on-surface font-body-md text-body-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary" id="incidentDate" name="tanggal_kejadian" required="" type="date" value="{{ old('tanggal_kejadian') }}" max="{{ now()->toDateString() }}">
                                        </div>
                                    </div>
                                    <!-- Location -->
                                    <div class="space-y-space-xs">
                                        <label class="block font-label-md text-label-md text-primary font-bold" for="incidentLoc">
                                            Lokasi / Alamat Rinci Kejadian <span class="text-error">*</span>
                                        </label>
                                        <div class="relative">
                                            <input class="w-full h-11 px-space-md rounded-lg bg-surface text-on-surface font-body-md text-body-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary pl-10" id="incidentLoc" name="lokasi" required="" type="text" value="{{ old('lokasi') }}">
                                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">location_on</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Field 4: Textarea Kronologi Lengkap -->
                                <div class="space-y-space-xs">
                                    <div class="flex items-center justify-between">
                                        <label class="block font-label-md text-label-md text-primary font-bold" for="fullDescription">
                                            Uraian Lengkap Kejadian &amp;Fakta Lapangan <span class="text-error">*</span>
                                        </label>
                                        <span class="font-label-sm text-label-sm text-outline font-medium " id="charCount">Karakter: 0 / 2000</span>
                                    </div>
                                    <textarea class="w-full p-space-md rounded-lg bg-surface text-on-surface font-body-md text-body-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary leading-relaxed resize-y" id="fullDescription" name="uraian" maxlength="2000" required="" rows="5">{{ old('uraian', $prefill['chronology'] ?? '') }}</textarea>
                                </div>
                                <!-- Field 5: Interactive Anonymous Toggle Switch -->
                                <div class="bg-surface-container-low p-space-md rounded-lg flex items-start sm:items-center justify-between gap-space-md">
                                    <div class="flex items-start gap-space-sm">
                                        <div class="p-2 rounded-lg bg-surface-container-lowest text-primary shadow-sm mt-0.5 sm:mt-0">
                                            <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                                        </div>
                                        <div>
                                            <span class="font-title-md text-title-md text-on-surface font-bold block">Kirim Sebagai Pengaduan Anonim</span>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant max-w-lg">(Nama dan kontak Anda tidak akan dibagikan kepada pihak terlapor).</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input class="sr-only peer" id="anonToggle" name="is_anonim" value="1" type="checkbox" @checked(old('is_anonim'))>
                                        <div class="w-12 h-7 bg-surface-container-high peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-surface-container-lowest after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary shadow-sm"></div>
                                    </label>
                                </div>
                                <div id="identityFields" class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                                    <div class="space-y-space-xs">
                                        <label class="block font-label-md text-label-md text-primary font-bold" for="reporterName">Nama Pelapor</label>
                                        <input class="w-full h-11 px-space-md rounded-lg bg-surface text-on-surface shadow-sm focus:outline-none focus:ring-2 focus:ring-primary" id="reporterName" name="nama_pelapor" type="text" value="{{ old('nama_pelapor') }}">
                                    </div>
                                    <div class="space-y-space-xs">
                                        <label class="block font-label-md text-label-md text-primary font-bold" for="reporterContact">Kontak (opsional)</label>
                                        <input class="w-full h-11 px-space-md rounded-lg bg-surface text-on-surface shadow-sm focus:outline-none focus:ring-2 focus:ring-primary" id="reporterContact" name="kontak" type="text" value="{{ old('kontak') }}">
                                    </div>
                                </div>
                                <!-- Field 6: Upload -->
                                <div class="space-y-space-sm">
                                    <div class="flex items-center justify-between">
                                        <label class="block font-label-md text-label-md text-primary font-bold">Unggah Berkas Bukti &amp; Dokumentasi</label>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">Maksimal 10 MB (PDF, JPG, PNG)</span>
                                    </div>
                                    <div class="p-space-lg rounded-xl bg-surface-container-low/60 hover:bg-surface-container-low transition-all text-center cursor-pointer group flex flex-col items-center justify-center" onclick="document.getElementById('fileInput').click()">
                                        <div class="w-12 h-12 rounded-full bg-surface-container-lowest text-primary shadow-sm flex items-center justify-center mb-space-xs group-hover:scale-105 transition-transform">
                                            <span class="material-symbols-outlined text-[28px]">cloud_upload</span>
                                        </div>
                                        <p class="font-title-md text-title-md text-on-surface font-semibold">Klik untuk memilih berkas bukti</p>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">PDF, JPG, atau PNG, maksimal 10 MB per berkas</p>
                                    </div>
                                    <input id="fileInput" name="lampiran[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                                    <ul id="fileList" class="list-disc pl-5 text-body-sm text-on-surface-variant"></ul>
                                </div>
                                <!-- Field 7: Integrity Consent Checkbox -->
                                <div class="pt-space-xs">
                                    <label class="flex items-start gap-space-sm cursor-pointer select-none">
                                        <input class="mt-1 w-5 h-5 rounded text-primary focus:ring-primary accent-primary cursor-pointer" id="consentCheck" name="persetujuan" value="1" required="" type="checkbox">
                                        <span class="font-body-md text-body-md text-on-surface">Saya menyatakan bahwa informasi yang disampaikan adalah benar dan dapat dipertanggungjawabkan untuk ditindaklanjuti demi perbaikan pelayanan publik.</span>
                                    </label>
                                </div>
                                <!-- Field 8: Action Submission Button -->
                                <div class="pt-space-sm flex flex-col sm:flex-row items-center gap-space-md justify-between">
                                    <div class="flex items-center gap-space-xs text-outline font-body-sm text-body-sm">
                                        <span class="material-symbols-outlined text-[18px]">find_replace</span>
                                        <span class="">Laporan akan diperiksa oleh petugas verifikator</span>
                                    </div>
                                    <button class="w-full sm:w-auto inline-flex items-center justify-center gap-space-sm bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg px-space-xl py-3 rounded-lg shadow-md transition-all active:scale-[0.99] font-bold" type="submit">
                                        <span class="">Kirim Pengaduan</span>
                                        <span class="material-symbols-outlined text-[20px]">send</span>
                                    </button>
                                </div>
                            </form>
                            <!-- Civic Assistance Helpline Footnote -->
                        </div>
                        <!-- RIGHT: SUCCESS STATE FRAME (Dedicated Elegant Preview Frame) (5 Cols) -->
                        <div class="lg:col-span-5 space-y-space-lg">
                            @if (session('ticket'))
                            <div class="bg-surface-container-lowest rounded-2xl shadow-xl overflow-hidden relative">
                                <div class="h-3 bg-gradient-to-r from-primary via-secondary to-primary-fixed"></div>
                                <div class="p-space-xl space-y-space-lg">
                                    <div class="text-center space-y-space-xs">
                                        <div class="w-20 h-20 rounded-full bg-secondary-container/50 text-secondary mx-auto flex items-center justify-center mb-space-sm shadow-inner relative">
                                            <span class="material-symbols-outlined text-[44px]">check_circle</span>
                                            <span class="absolute inset-0 rounded-full bg-secondary/10 animate-ping"></span>
                                        </div>
                                        <span class="inline-block px-3 py-1 rounded-full bg-secondary-fixed/50 text-on-secondary-fixed font-label-sm text-label-sm uppercase tracking-wider font-extrabold">Berhasil Dikirim</span>
                                        <h3 class="font-headline-lg text-headline-lg text-on-surface font-semibold tracking-tight">Pengaduan Berhasil Dikirim!</h3>
                                        <p class="font-body-md text-body-md text-on-surface-variant max-w-sm mx-auto leading-relaxed">Laporan Anda telah tercatat dan akan diperiksa oleh petugas verifikator.</p>
                                    </div>
                                    <div class="bg-surface-container-low rounded-xl p-space-lg space-y-space-md relative overflow-hidden">
                                        <div class="flex items-center justify-between">
                                            <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">Bukti Registrasi</span>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm font-bold shadow-sm">
                                                <span class="w-2 h-2 rounded-full bg-tertiary-container animate-pulse"></span>Diterima
                                            </span>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="font-label-sm text-label-sm text-on-surface-variant font-medium">Nomor Tiket Pengaduan:</div>
                                            <div class="flex items-center justify-between bg-surface-container-lowest p-space-sm px-space-md rounded-lg shadow-sm">
                                                <span class="font-title-lg text-title-lg text-primary font-bold tracking-wider font-mono text-[20px]" id="ticketNumber">{{ session('ticket') }}</span>
                                                <button type="button" class="inline-flex items-center gap-1.5 px-space-md py-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container text-primary font-label-md text-label-md font-bold transition-all active:scale-95" id="copyBtn" onclick="copyTicketCode()">
                                                    <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                                    <span id="copyBtnLabel">Salin</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-space-xs text-on-surface-variant font-body-sm text-body-sm bg-surface-container-lowest/60 p-space-sm rounded-lg">
                                            <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">info</span>
                                            <p><strong>Instruksi Pelapor:</strong> Simpan nomor tiket ini untuk melacak perkembangan pengaduan Anda pada menu Lacak Tiket tanpa perlu membuat akun.</p>
                                        </div>
                                        <div class="pt-space-xs space-y-1.5 font-label-sm text-label-sm text-on-surface-variant">
                                            <div class="flex justify-between py-1 border-b border-outline-variant/20">
                                                <span>Waktu Pengiriman:</span>
                                                <span class="font-semibold text-on-surface">{{ session('submitted_at') }}</span>
                                            </div>
                                            <div class="flex justify-between py-1">
                                                <span>Status Awal:</span>
                                                <span class="font-semibold text-secondary">Diterima</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-space-sm">
                                        <a class="w-full inline-flex items-center justify-center gap-space-sm bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg py-3 px-space-lg rounded-lg shadow-sm transition-all font-bold" href="{{ route('complaint.track') }}">
                                            <span class="material-symbols-outlined text-[20px]">search_check</span>
                                            <span>Lacak Status Tiket Sekarang</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm text-on-surface-variant">
                                Nomor tiket Anda akan muncul di sini setelah laporan berhasil dikirim.
                            </div>
                            @endif
                            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm space-y-space-md">
                                <h4 class="font-title-md text-title-md text-on-surface font-bold flex items-center gap-space-xs">
                                    <span class="material-symbols-outlined text-primary text-[20px]">timeline</span>Tahapan Penanganan Pasca Pengiriman
                                </h4>
                                <div class="space-y-space-md relative pl-6 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-surface-container-high">
                                    <div class="relative">
                                        <div class="absolute -left-6 top-1 w-3 h-3 rounded-full bg-secondary ring-4 ring-secondary-container"></div>
                                        <div class="font-label-md text-label-md text-on-surface font-bold">1. Verifikasi Kelayakan Berkas</div>
                                        <div class="font-body-sm text-body-sm text-on-surface-variant">Pemeriksaan kelengkapan dokumen dan indikasi pelanggaran administratif.</div>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute -left-6 top-1 w-3 h-3 rounded-full bg-outline-variant"></div>
                                        <div class="font-label-md text-label-md text-on-surface-variant font-semibold">2. Klarifikasi ke Instansi Terlapor</div>
                                        <div class="font-body-sm text-body-sm text-outline">Penyampaian telaah ke pimpinan unit kerja kelurahan untuk klarifikasi resmi.</div>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute -left-6 top-1 w-3 h-3 rounded-full bg-outline-variant"></div>
                                        <div class="font-label-md text-label-md text-on-surface-variant font-semibold">3. Rekomendasi Tindakan &amp; Penyelesaian</div>
                                        <div class="font-body-sm text-body-sm text-outline">Penerbitan rekomendasi sanksi, perbaikan prosedur loket, dan penyelesaian laporan.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Inline Client Interaction Logic -->
                <script>
                    function copyTicketCode() {
                        const ticketText = document.getElementById('ticketNumber').innerText;
                        const copyLabel = document.getElementById('copyBtnLabel');

                        navigator.clipboard.writeText(ticketText).then( () => {
                            copyLabel.innerText = "Tersalin!";
                            setTimeout( () => {
                                copyLabel.innerText = "Salin";
                            }
                            , 2200);
                        }
                        ).catch( () => {
                            copyLabel.innerText = "Tersalin!";
                            setTimeout( () => {
                                copyLabel.innerText = "Salin";
                            }
                            , 2200);
                        }
                        );
                    }

                    const anon = document.getElementById('anonToggle');
                    const identity = document.getElementById('identityFields');
                    function syncIdentity() { identity.classList.toggle('hidden', anon.checked); }
                    anon.addEventListener('change', syncIdentity);
                    syncIdentity();

                    const desc = document.getElementById('fullDescription');
                    const counter = document.getElementById('charCount');
                    function syncCount() { counter.textContent = 'Karakter: ' + desc.value.length + ' / 2000'; }
                    desc.addEventListener('input', syncCount);
                    syncCount();

                    const fileInput = document.getElementById('fileInput');
                    const fileList = document.getElementById('fileList');
                    fileInput.addEventListener('change', () => {
                        fileList.innerHTML = '';
                        Array.from(fileInput.files).forEach(f => {
                            const li = document.createElement('li');
                            li.textContent = f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB)';
                            fileList.appendChild(li);
                        });
                    });
                </script>
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
