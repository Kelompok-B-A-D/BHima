@extends('services.layout')
@section('title', $record->payload['title'])
@section('content')
@php($data = $record->payload)
<a href="{{ route('services.index') }}">← Katalog layanan</a>
<article class="panel service-detail"><p class="eyebrow">{{ $data['service_category'] ?? $data['category'] ?? '' }}</p><h1>{{ $data['title'] }}</h1><p>Diperbarui {{ $record->updated_at->format('d/m/Y') }}</p>
<dl class="detail-grid"><div><dt>Instansi penyelenggara</dt><dd>{{ $data['agency'] ?? 'Informasi belum tersedia' }}</dd></div><div><dt>Jenis layanan</dt><dd>{{ ucfirst($data['type'] ?? 'Informasi belum tersedia') }}</dd></div><div><dt>Estimasi waktu</dt><dd>{{ $data['duration'] ?? 'Informasi belum tersedia' }}</dd></div><div><dt>Biaya resmi</dt><dd>{{ $data['cost'] ?? 'Informasi belum tersedia' }}</dd></div></dl>
@foreach(['body'=>'Deskripsi layanan','requirements'=>'Persyaratan','procedure'=>'Prosedur / tahapan'] as $name => $label)<h2>{{ $label }}</h2><p class="preserve">{{ $data[$name] ?? 'Informasi belum tersedia' }}</p>@endforeach
<h2>Dokumen panduan</h2>@if($record->file_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($record->file_path))<a class="primary button-link" href="{{ route('services.download', $record->record_key) }}">Unduh PDF · {{ $data['title'] }}</a>@else<p>Dokumen belum tersedia.</p>@endif
</article>
@endsection
