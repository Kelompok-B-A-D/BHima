<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BHIMA · Lacak Tiket</title>
</head>
<body>
    <h1>Lacak Tiket</h1>

    @if ($errors->any())
        <p role="alert">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('complaint.track.submit') }}">
        @csrf
        <input type="text" name="tiket" value="{{ old('tiket') }}" placeholder="BHM-XXXXXXXXXX" required>
        <button type="submit">Lacak</button>
    </form>

    @if (session('searched'))
        @php($result = session('result'))
        @if ($result)
            <h2>{{ $result['ticket'] }}</h2>
            <p>Status: <strong>{{ $result['status'] }}</strong></p>
            <ol>
                @foreach ($result['history'] as $h)
                    <li>{{ $h['status'] }} ({{ $h['date'] }})</li>
                @endforeach
            </ol>
        @else
            <p>Tiket tidak ditemukan. Periksa kembali nomor tiket Anda.</p>
        @endif
    @endif
</body>
</html>