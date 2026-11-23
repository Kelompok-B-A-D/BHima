@extends('services.layout')
@section('title', $record->payload['title'])
@section('content')
@php
    $data = $record->payload;
    $hasPdf = $record->file_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($record->file_path);
@endphp

<div class="stitch-no-print flex flex-wrap items-center justify-between gap-3">
    <a
        href="{{ route('services.index') }}"
        class="btn-glass !min-h-[44px] px-4 text-xs min-[641px]:text-sm inline-flex items-center gap-1.5"
    >
        <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">arrow_back</span>
        <span>Katalog Layanan</span>
    </a>

    <button
        type="button"
        onclick="window.print()"
        class="btn-glass !min-h-[44px] px-4 text-xs min-[641px]:text-sm inline-flex items-center gap-1.5"
    >
        <span class="material-symbols-outlined text-[18px] text-[#333E52]" aria-hidden="true">print</span>
        <span>Cetak Panduan</span>
    </button>
</div>

<article class="surface-card hero-enter p-6 min-[641px]:p-8 space-y-6">
    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1 rounded-full bg-[#E6F0FC] text-[#005EB8] text-xs font-bold">
                {{ $data['service_category'] ?? $data['category'] ?? 'Informasi Layanan' }}
            </span>
            @if (! empty($data['type']))
                <span class="px-3 py-1 rounded-full bg-[#E6F5F3] text-[#0F766E] text-xs font-bold">
                    Layanan {{ ucfirst($data['type']) }}
                </span>
            @endif
            <span class="text-xs font-semibold text-[#4A5366]">
                Diperbarui {{ $record->updated_at->format('d/m/Y') }}
            </span>
        </div>

        <h1 class="type-h1 text-[#0B1020] break-words">{{ $data['title'] }}</h1>
    </div>

    <dl class="surface-nested grid grid-cols-1 min-[481px]:grid-cols-2 min-[1008px]:grid-cols-4 gap-4 p-4 min-[641px]:p-5 text-xs">
        <div>
            <dt class="text-[#4A5366] font-semibold">Instansi penyelenggara</dt>
            <dd class="text-sm font-bold text-[#0B1020] mt-1">{{ $data['agency'] ?? 'Informasi belum tersedia' }}</dd>
        </div>
        <div>
            <dt class="text-[#4A5366] font-semibold">Jenis layanan</dt>
            <dd class="text-sm font-bold text-[#0B1020] mt-1">{{ ucfirst($data['type'] ?? 'Informasi belum tersedia') }}</dd>
        </div>
        <div>
            <dt class="text-[#4A5366] font-semibold">Estimasi waktu</dt>
            <dd class="text-sm font-bold text-[#0B1020] mt-1">{{ $data['duration'] ?? 'Informasi belum tersedia' }}</dd>
        </div>
        <div>
            <dt class="text-[#4A5366] font-semibold">Biaya resmi</dt>
            <dd class="text-sm font-bold text-[#0F766E] mt-1">{{ $data['cost'] ?? 'Informasi belum tersedia' }}</dd>
        </div>
    </dl>

    <div class="space-y-6">
        @foreach (['body' => 'Deskripsi layanan', 'requirements' => 'Persyaratan', 'procedure' => 'Prosedur / tahapan'] as $name => $label)
            <section class="space-y-2">
                <h2 class="type-h3 text-[#0B1020]">{{ $label }}</h2>
                <div class="surface-nested p-4 min-[641px]:p-5">
                    <p class="prose-measure whitespace-pre-wrap text-sm min-[641px]:text-base text-[#333E52] leading-relaxed">{{ $data[$name] ?? 'Informasi belum tersedia' }}</p>
                </div>
            </section>
        @endforeach
    </div>

    <section class="pt-4 border-t border-[#DCE4F0] space-y-3">
        <h2 class="type-h3 text-[#0B1020]">Dokumen panduan</h2>
        @if ($hasPdf)
            <a
                class="btn-primary px-6 text-sm inline-flex items-center justify-center gap-2"
                href="{{ route('services.download', $record->record_key) }}"
            >
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">download</span>
                <span>Unduh PDF · {{ $data['title'] }}</span>
            </a>
        @else
            <p class="text-sm text-[#4A5366]">Dokumen belum tersedia.</p>
        @endif
    </section>
</article>
@endsection
