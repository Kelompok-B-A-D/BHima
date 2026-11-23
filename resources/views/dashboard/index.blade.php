<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BHIMA · Dashboard Internal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-body">
    <div id="dashboard" data-role="{{ $role }}" data-demo="{{ $demo ? 'true' : 'false' }}"><p role="status">Memuat dashboard BHIMA…</p></div>
    <noscript>Aktifkan JavaScript untuk menggunakan dashboard interaktif.</noscript>
</body>
</html>
