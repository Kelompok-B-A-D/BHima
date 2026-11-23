<!doctype html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Informasi Layanan') · BHIMA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/services.js'])
</head>
@php
    $internal = request()->routeIs('services.manage', 'services.create', 'services.edit', 'services.store', 'services.update');
    $navIcons = [
        'dashboard' => '▦',
        'pengaduan' => '▤',
        'konten' => '▧',
        'anggaran' => '◈',
        'jdih' => '▥',
        'basis-ai' => '◎',
        'analitik' => '▥',
        'pencarian' => '⌕',
        'akun' => '♙',
        'peran' => '◇',
        'aktivitas' => '◷',
        'pengaturan' => '⚙',
    ];
@endphp
@if ($internal)
<body class="dashboard-body service-admin">
    <div class="shell">
        <button type="button" class="scrim" aria-label="Tutup navigasi" data-menu-close></button>
        <aside class="sidebar" aria-label="Navigasi utama">
            <a class="brand" href="/admin/dashboard">
                <span class="brand-mark">B</span>
                <span>BHIMA<small>Bantuan Hukum &amp; Informasi Masyarakat</small></span>
            </a>
            <div class="workspace">
                <span class="workspace-icon" aria-hidden="true">▦</span>
                <div>Portal Internal<small>Administrator</small></div>
                <span class="live-dot" aria-hidden="true"></span>
            </div>
            <nav>
                @foreach ([
                    'UTAMA' => ['dashboard' => 'Dashboard', 'pengaduan' => 'Pengaduan'],
                    'LAYANAN PUBLIK' => ['konten' => 'Transparansi Layanan', 'anggaran' => 'Transparansi Anggaran'],
                    'PUSAT HUKUM' => ['jdih' => 'Dokumen JDIH', 'basis-ai' => 'Basis Pengetahuan AI'],
                    'WAWASAN' => ['analitik' => 'Statistik Pengaduan', 'pencarian' => 'Tren Pencarian'],
                    'ADMINISTRASI' => ['akun' => 'Kelola Akun', 'peran' => 'Kelola Peran', 'aktivitas' => 'Log Aktivitas', 'pengaturan' => 'Pengaturan'],
                ] as $group => $items)
                    <p class="nav-label">{{ $group }}</p>
                    @foreach ($items as $page => $title)
                        <a href="/admin/{{ $page }}" @if ($page === 'konten') aria-current="page" @endif>
                            <span class="nav-icon" aria-hidden="true">{{ $navIcons[$page] ?? '▤' }}</span>
                            <span>{{ $title }}</span>
                            @if ($page === 'konten')
                                <span class="nav-indicator" aria-hidden="true"></span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </nav>
            <div class="sidebar-footer">
                <span class="shield" aria-hidden="true">◇</span>
                <div>Layanan publik terpercaya<small>Transparan. Responsif. Terintegrasi.</small></div>
            </div>
        </aside>

        <div class="workspace-main">
            <header class="topbar">
                <button type="button" class="icon-button mobile-toggle" data-menu-toggle aria-label="Buka navigasi" aria-expanded="false">☰</button>
                <div class="breadcrumb">Portal Internal <span aria-hidden="true">/</span> <strong>SOP &amp; panduan</strong></div>
                <div class="topbar-right">
                    <a class="button-link btn-glass" style="min-height:44px;padding:8px 16px;font-size:12px" href="{{ route('services.index') }}">Katalog publik</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Keluar</button>
                    </form>
                </div>
            </header>

            <main id="main-content">
                @if (session('success'))
                    <div class="notice success" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="notice error" role="alert">
                        <strong>Periksa kembali isian Anda.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>

            <footer>
                <span>© {{ date('Y') }} BHIMA</span>
                <span>Bantuan Hukum dan Informasi Masyarakat</span>
            </footer>
        </div>
    </div>

    <x-confirm-dialog
        id="confirm-delete-service-dialog"
        title="Hapus Informasi Layanan?"
        description="Informasi layanan ini akan dihapus dari pengelolaan internal dan katalog publik. Pastikan data tidak lagi diperlukan."
        confirm-label="Ya, Hapus Data"
        cancel-label="Batal"
        icon="delete_forever"
        tone="danger"
    />
</body>
@else
<body class="stitch-liquid-body has-mobile-tabbar min-h-screen overflow-x-hidden bg-[#E9EEF5] font-sans text-[#0B1020] antialiased selection:bg-[#E6F0FC] selection:text-[#0B1020]">
    <a
        href="#portal-main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:px-4 focus:py-2.5 focus:rounded-full focus:bg-[#0066CC] focus:text-white focus:font-bold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <div class="w-full overflow-x-hidden">
        @include('partials.liquid-header', ['activeNav' => 'layanan'])

        <main id="portal-main-content" class="w-full pt-24 min-[641px]:pt-28 pb-10 min-[641px]:pb-14 bg-transparent relative min-h-[calc(100vh-280px)] stitch-print-full">
            <div class="max-w-[1200px] mx-auto w-full px-4 min-[641px]:px-6 space-y-6 min-[641px]:space-y-8">
                @if (session('success'))
                    <div role="status" class="rounded-[20px] bg-[#DCFCE7] border border-[#BBF7D0] text-[#166534] p-4 min-[641px]:p-5 flex items-start gap-3">
                        <span class="material-symbols-outlined text-[22px] shrink-0 mt-0.5" aria-hidden="true">check_circle</span>
                        <p class="text-sm font-semibold">{{ session('success') }}</p>
                    </div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="rounded-[20px] bg-[#FEE2E2] border border-[#FCA5A5] text-[#991B1B] p-4 min-[641px]:p-5 space-y-1.5 text-sm">
                        <strong class="font-bold block">Periksa kembali isian Anda.</strong>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>

        @include('partials.liquid-footer')
        @include('partials.liquid-bottom-nav', ['activeNav' => 'layanan'])
    </div>
</body>
@endif
</html>
