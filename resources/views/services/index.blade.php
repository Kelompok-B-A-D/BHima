@extends('services.layout')
@section('title', $manage ? 'Kelola SOP & Panduan' : 'Katalog Layanan')
@section('content')
<div class="page-heading"><div><p class="eyebrow">INFORMASI LAYANAN PUBLIK</p><h1>{{ $manage ? 'Kelola SOP & panduan' : 'Temukan informasi layanan' }}</h1><p>Prosedur layanan daerah dan umum, persyaratan, serta dokumen panduan.</p></div>@if($manage)<a class="primary button-link" href="{{ route('services.create') }}">Tambah informasi</a>@endif</div>
<form class="panel service-filters" method="get" data-pending>
    <label>Kata kunci<input type="search" name="q" maxlength="200" value="{{ $filters['q'] ?? '' }}" placeholder="Judul, deskripsi, kategori, instansi"></label>
    @foreach(['category' => 'Kategori layanan', 'agency' => 'Instansi'] as $name => $label)
    <label>{{ $label }}<select name="{{ $name }}"><option value="">Semua {{ strtolower($label) }}</option>@foreach($options[$name] as $option)<option @selected(($filters[$name] ?? '') === $option) value="{{ $option }}">{{ $option }}</option>@endforeach</select></label>
    @endforeach
    <label>Jenis layanan<select name="type"><option value="">Semua jenis</option><option value="daerah" @selected(($filters['type'] ?? '') === 'daerah')>Daerah</option><option value="umum" @selected(($filters['type'] ?? '') === 'umum')>Umum</option></select></label>
    @if($manage)<label>Status<select name="status"><option value="">Semua status</option>@foreach(['Draf','Terbit'] as $status)<option @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>@endforeach</select></label>@endif
    <button class="primary">Cari / terapkan filter</button><a href="{{ $manage ? route('services.manage') : route('services.index') }}">Hapus pencarian & filter</a>
</form>
<p role="status">{{ $records->total() }} informasi ditemukan. @foreach(collect($filters)->filter() as $name => $value)<span class="badge info">{{ ['q'=>'Pencarian','category'=>'Kategori','agency'=>'Instansi','type'=>'Jenis','status'=>'Status'][$name] }}: {{ $value }}</span> @endforeach</p>
@if($manage)
<section class="panel table-scroll"><table><thead><tr>@foreach(['Judul','Kategori','Instansi','Jenis','Diperbarui','Status','Aksi'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead><tbody>
@forelse($records as $record)
@php($data = $record->payload)
<tr><td><strong>{{ $data['title'] }}</strong><small>{{ $data['category'] ?? '' }}</small></td><td>{{ $data['service_category'] ?? 'Belum tersedia' }}</td><td>{{ $data['agency'] ?? 'Belum tersedia' }}</td><td>{{ ucfirst($data['type'] ?? 'Belum tersedia') }}</td><td>{{ $record->updated_at->format('d/m/Y') }}</td><td>{{ $data['status'] }}</td><td><div class="actions"><a href="{{ route('services.edit', $record->record_key) }}">Edit</a><form method="post" action="{{ route('services.destroy', $record->record_key) }}" data-confirm="Hapus informasi ini dari pengelolaan dan katalog publik?">@csrf @method('DELETE')<button class="danger-text">Hapus</button></form></div></td></tr>
@empty<tr><td colspan="7" class="empty">Belum ada informasi yang cocok. Tambahkan informasi atau sesuaikan filter.</td></tr>@endforelse
</tbody></table></section>
@else
<div class="service-cards">
@forelse($records as $record)
@php($data = $record->payload)
<article class="panel"><p class="eyebrow">{{ $data['service_category'] ?? $data['category'] ?? 'Layanan' }} · {{ ucfirst($data['type'] ?? '') }}</p><h2>{{ $data['title'] }}</h2><p>{{ $data['agency'] ?? 'Instansi belum tersedia' }}</p><p>{{ \Illuminate\Support\Str::limit($data['body'], 200) }}</p><dl><dt>Estimasi waktu</dt><dd>{{ $data['duration'] ?? 'Informasi belum tersedia' }}</dd><dt>Biaya resmi</dt><dd>{{ $data['cost'] ?? 'Informasi belum tersedia' }}</dd><dt>Persyaratan utama</dt><dd>{{ \Illuminate\Support\Str::limit($data['requirements'] ?? 'Informasi belum tersedia', 160) }}</dd></dl><div class="actions"><a class="primary button-link" href="{{ route('services.show', $record->record_key) }}">Lihat detail</a>@if($record->file_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($record->file_path))<a href="{{ route('services.download', $record->record_key) }}">Unduh PDF</a>@else<small>Dokumen belum tersedia</small>@endif</div></article>
@empty<div class="panel empty"><h2>Tidak ada informasi yang cocok</h2><p>Sesuaikan kata kunci atau hapus filter pencarian.</p></div>@endforelse
</div>
@endif
{{ $records->links() }}
@endsection
