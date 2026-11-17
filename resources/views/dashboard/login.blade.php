<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Masuk Petugas · BHIMA</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="login-body"><main class="login-card"><div class="brand-mark">B</div><p class="eyebrow">BHIMA · PORTAL INTERNAL</p><h1>Selamat datang kembali</h1><p>Masuk untuk mengelola layanan, dokumen JDIH, dan pengaduan masyarakat.</p>
@if($errors->any())<div role="alert" class="notice error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login.submit') }}">@csrf<label>Email kedinasan<input type="email" name="email" value="{{ old('email') }}" placeholder="admin@bhima.go.id" required autocomplete="username" autofocus></label><label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label><button class="primary" type="submit">Masuk ke dashboard →</button></form>
@if(app()->environment('local', 'testing'))
<div class="notice" style="margin-bottom:14px">
    <strong>Akses Cepat Mode Lokal / Demo:</strong><br>
    Akun Admin: <code>admin@bhima.go.id</code> / <code>password12345</code><br>
    Akun Verifikator: <code>verifikator@bhima.go.id</code> / <code>password12345</code>
    <div class="actions" style="margin-top:8px;flex-wrap:wrap">
        <a href="/demo/admin/dashboard" class="button-link" style="border:1px solid var(--line);padding:6px 10px;background:white;font-size:11px">Buka Demo Admin →</a>
        <a href="/demo/verifikator/dashboard" class="button-link" style="border:1px solid var(--line);padding:6px 10px;background:white;font-size:11px">Buka Demo Verifikator →</a>
    </div>
</div>
@endif
<p class="muted">Warga dapat menggunakan layanan publik tanpa akun.</p><a href="/">Kembali ke halaman publik</a></main></body></html>
