<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Informasi Layanan') · BHIMA</title>
    @vite(['resources/css/app.css', 'resources/js/services.js'])
</head>
@php($internal = request()->routeIs('services.manage', 'services.create', 'services.edit', 'services.store', 'services.update'))
<body class="dashboard-body {{ $internal ? 'service-admin' : '' }}">
@if($internal)
<div class="shell">
<button class="scrim" aria-label="Tutup navigasi" data-menu-close></button>
<aside class="sidebar" aria-label="Navigasi utama">
    <a class="brand" href="/admin/dashboard"><span class="brand-mark">B</span><span>BHIMA<small>Bantuan Hukum & Informasi Masyarakat</small></span></a>
    <div class="workspace"><span class="workspace-icon">▦</span><div>Portal Internal<small>Administrator</small></div><span class="live-dot"></span></div>
    <nav>
    @foreach(['UTAMA'=>['dashboard'=>'Dashboard','pengaduan'=>'Pengaduan'], 'LAYANAN PUBLIK'=>['konten'=>'Transparansi Layanan','anggaran'=>'Transparansi Anggaran'], 'PUSAT HUKUM'=>['jdih'=>'Dokumen JDIH','basis-ai'=>'Basis Pengetahuan AI'], 'WAWASAN'=>['analitik'=>'Statistik Pengaduan','pencarian'=>'Tren Pencarian'], 'ADMINISTRASI'=>['akun'=>'Kelola Akun','peran'=>'Kelola Peran','aktivitas'=>'Log Aktivitas','pengaturan'=>'Pengaturan']] as $group => $items)
        <p class="nav-label">{{ $group }}</p>
        @foreach($items as $page => $title)<a href="/admin/{{ $page }}" @if($page === 'konten') aria-current="page" @endif>{{ $title }}</a>@endforeach
    @endforeach
    </nav>
</aside>
<div class="workspace-main">
<header class="topbar"><button class="icon-button mobile-toggle" data-menu-toggle aria-label="Buka navigasi" aria-expanded="false">☰</button><div class="breadcrumb">Portal Internal <span>/</span> <strong>SOP & panduan</strong></div><a href="/layanan">Katalog publik</a><form method="post" action="/logout">@csrf<button>Keluar</button></form></header>
@else
<header class="service-header"><a class="brand" href="/layanan"><span class="brand-mark">B</span> BHIMA</a><nav aria-label="Navigasi layanan"><a href="/layanan">Katalog layanan</a> @if(auth()->user()?->role === 'admin')<a href="/admin/dashboard">Dashboard</a><a href="/admin/konten">Kelola SOP & panduan</a>@else<a href="/login">Masuk petugas</a>@endif</nav></header>
@endif
<main class="service-main">
    @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="notice error" role="alert"><strong>Periksa kembali isian Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer>BHIMA · Bantuan Hukum dan Informasi Masyarakat</footer>
@if($internal)</div></div>@endif
</body>
</html>
