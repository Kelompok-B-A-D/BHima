@extends('services.layout')
@section('title', 'Dokumen belum tersedia')
@section('content')
<div class="panel empty" role="alert"><h1>Dokumen belum tersedia</h1><p>Dokumen untuk layanan ini belum tersedia atau tidak dapat ditemukan. Silakan kembali ke katalog.</p><a href="{{ route('services.index') }}">Kembali ke katalog layanan</a></div>
@endsection
