@extends('services.layout')
@section('title', $record ? 'Edit Informasi' : 'Tambah Informasi')
@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">INFORMASI LAYANAN PUBLIK</p>
        <h1>{{ $record ? 'Edit' : 'Tambah' }} SOP &amp; panduan</h1>
        <p>Isi informasi resmi yang telah diverifikasi. Kolom bertanda * wajib diisi; kosongkan informasi opsional yang belum tersedia.</p>
    </div>
    <a class="button-link btn-glass" href="{{ route('services.manage') }}">Kembali ke daftar</a>
</div>

@php
    $data = $record?->payload ?? [];
@endphp
<form class="panel service-form" method="post" enctype="multipart/form-data" action="{{ $record ? route('services.update', $record->record_key) : route('services.store') }}" data-pending data-upload>
    @csrf
    @if ($record)
        @method('PUT')
    @endif

    <div class="form-grid">
        @foreach (['title' => ['Judul layanan / SOP', 200], 'service_category' => ['Kategori layanan', 100], 'agency' => ['Instansi penyelenggara', 200]] as $name => [$label, $max])
            <label>
                {{ $label }} *
                <input name="{{ $name }}" value="{{ old($name, $data[$name] ?? '') }}" required maxlength="{{ $max }}" @error($name) aria-invalid="true" @enderror>
                @error($name)
                    <span class="danger-text">{{ $message }}</span>
                @enderror
            </label>
        @endforeach

        @foreach ([
            'category' => ['Jenis konten', ['SOP' => 'SOP', 'Panduan' => 'Panduan', 'Pengumuman' => 'Pengumuman', 'Informasi' => 'Informasi']],
            'type' => ['Jenis layanan', ['daerah' => 'Daerah', 'umum' => 'Umum']],
            'status' => ['Status publikasi', ['Draf' => 'Draf', 'Terbit' => 'Terbit']],
        ] as $name => [$label, $choices])
            <label>
                {{ $label }} *
                <select required name="{{ $name }}">
                    @foreach ($choices as $value => $text)
                        <option value="{{ $value }}" @selected(old($name, $data[$name] ?? ($name === 'status' ? 'Draf' : '')) === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
    </div>

    @foreach (['body' => ['Deskripsi lengkap', 50000], 'requirements' => ['Persyaratan', 20000], 'procedure' => ['Prosedur / tahapan layanan', 20000]] as $name => [$label, $max])
        <label>
            {{ $label }} {{ $name === 'body' ? '*' : '' }}
            <textarea name="{{ $name }}" rows="5" maxlength="{{ $max }}" @required($name === 'body')>{{ old($name, $data[$name] ?? '') }}</textarea>
        </label>
    @endforeach

    <div class="form-grid">
        @foreach (['duration' => 'Estimasi waktu penyelesaian', 'cost' => 'Biaya resmi'] as $name => $label)
            <label>
                {{ $label }}
                <input name="{{ $name }}" maxlength="200" value="{{ old($name, $data[$name] ?? '') }}" placeholder="Kosongkan jika belum tersedia">
            </label>
        @endforeach
    </div>

    <label>
        {{ $record?->file_path ? 'Ganti dokumen panduan' : 'Dokumen panduan / persyaratan' }} (PDF, maksimal 10 MB)
        <input type="file" name="file" accept=".pdf,application/pdf">
        <span data-file-status role="status" class="muted" style="display:block;margin-top:6px">
            Tidak memilih file baru.
            @if ($record?->file_path)
                Dokumen saat ini: {{ $data['filename'] ?? 'panduan.pdf' }}. Tetap digunakan jika tidak diganti.
                <a class="text-link" href="{{ route('services.download', $record->record_key) }}">Unduh PDF saat ini</a>
            @endif
        </span>
    </label>

    <div class="form-actions">
        <a class="button-link btn-glass" href="{{ route('services.manage') }}">Batal</a>
        <button type="submit" class="primary">Simpan informasi</button>
    </div>
    <p data-submit-status role="status" class="muted"></p>
</form>
@endsection
