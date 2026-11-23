@extends('services.layout')
@section('title', 'Dokumen belum tersedia')
@section('content')
<div class="surface-card p-8 min-[641px]:p-10 text-center space-y-4 max-w-xl mx-auto" role="alert">
    <div class="w-12 h-12 rounded-[16px] bg-[#FEF3C7] text-[#92400E] border border-[#F5D07A] mx-auto flex items-center justify-center">
        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">description</span>
    </div>
    <h1 class="type-h2 text-[#0B1020]">Dokumen belum tersedia</h1>
    <p class="text-sm text-[#4A5366] leading-relaxed">
        Dokumen untuk layanan ini belum tersedia atau tidak dapat ditemukan. Silakan kembali ke katalog layanan.
    </p>
    <div class="pt-1">
        <a class="btn-primary !min-h-[44px] px-6 text-sm inline-flex items-center justify-center gap-2" href="{{ route('services.index') }}">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            <span>Kembali ke katalog layanan</span>
        </a>
    </div>
</div>
@endsection
