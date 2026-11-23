@extends('services.layout')
@section('title', $manage ? 'Kelola SOP & Panduan' : 'Katalog Layanan')
@section('content')
@if ($manage)
    <div class="page-heading">
        <div>
            <p class="eyebrow">INFORMASI LAYANAN PUBLIK</p>
            <h1>Kelola SOP &amp; panduan</h1>
            <p>Prosedur layanan daerah dan umum, persyaratan, serta dokumen panduan resmi.</p>
        </div>
        <a class="primary button-link" href="{{ route('services.create') }}">Tambah informasi</a>
    </div>

    <form class="panel service-filters" method="get" data-pending>
        <label>
            Kata kunci
            <input type="search" name="q" maxlength="200" value="{{ $filters['q'] ?? '' }}" placeholder="Judul, deskripsi, kategori, instansi">
        </label>
        @foreach (['category' => 'Kategori layanan', 'agency' => 'Instansi'] as $name => $label)
            <label>
                {{ $label }}
                <select name="{{ $name }}">
                    <option value="">Semua {{ strtolower($label) }}</option>
                    @foreach ($options[$name] as $option)
                        <option @selected(($filters[$name] ?? '') === $option) value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
        <label>
            Jenis layanan
            <select name="type">
                <option value="">Semua jenis</option>
                <option value="daerah" @selected(($filters['type'] ?? '') === 'daerah')>Daerah</option>
                <option value="umum" @selected(($filters['type'] ?? '') === 'umum')>Umum</option>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="">Semua status</option>
                @foreach (['Draf', 'Terbit'] as $status)
                    <option @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <div class="service-filter-actions">
            <button type="submit" class="primary">Cari / terapkan filter</button>
            <a class="button-link btn-glass" href="{{ route('services.manage') }}">Hapus pencarian &amp; filter</a>
        </div>
    </form>

    <p class="muted service-results-summary" role="status">
        <strong>{{ $records->total() }} informasi ditemukan.</strong>
        @foreach (collect($filters)->filter() as $name => $value)
            <span class="badge info">{{ ['q' => 'Pencarian', 'category' => 'Kategori', 'agency' => 'Instansi', 'type' => 'Jenis', 'status' => 'Status'][$name] }}: {{ $value }}</span>
        @endforeach
    </p>

    <section class="panel table-panel">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        @foreach (['Judul', 'Kategori', 'Instansi', 'Jenis', 'Diperbarui', 'Status', 'Aksi'] as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $data = $record->payload;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $data['title'] }}</strong>
                                <small>{{ $data['category'] ?? '' }}</small>
                            </td>
                            <td>{{ $data['service_category'] ?? 'Belum tersedia' }}</td>
                            <td>{{ $data['agency'] ?? 'Belum tersedia' }}</td>
                            <td>{{ ucfirst($data['type'] ?? 'Belum tersedia') }}</td>
                            <td>{{ $record->updated_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ ($data['status'] ?? '') === 'Terbit' ? 'success' : 'warning' }}">
                                    <span aria-hidden="true">●</span> {{ $data['status'] }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="text-link" href="{{ route('services.edit', $record->record_key) }}">Edit</a>
                                    <form
                                        method="post"
                                        action="{{ route('services.destroy', $record->record_key) }}"
                                        data-confirm="Hapus informasi &quot;{{ $data['title'] }}&quot; dari pengelolaan dan katalog publik?"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-button danger-text">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">Belum ada informasi yang cocok. Tambahkan informasi atau sesuaikan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($records->hasPages())
        <div class="service-pagination">
            {{ $records->links() }}
        </div>
    @endif
@else
    <!-- Public Screen Header -->
    <section class="hero-enter space-y-2.5 max-w-3xl">
        <p class="text-xs uppercase tracking-[0.12em] font-bold text-[#005EB8]">
            Informasi Layanan Publik
        </p>
        <h1 class="type-h1 text-[#0B1020]">
            Temukan Informasi Layanan &amp; SOP
        </h1>
        <p class="prose-measure text-sm min-[641px]:text-base text-[#4A5366] leading-relaxed">
            Telusuri prosedur layanan daerah dan umum, persyaratan administrasi, estimasi waktu, biaya resmi, serta dokumen panduan yang telah diverifikasi.
        </p>
    </section>

    <!-- Clear Liquid Glass Filter Dock -->
    <section class="hero-enter hero-enter-1" aria-label="Filter Katalog Layanan">
        <form class="liquid-glass liquid-glass--strong rounded-[28px] p-5 min-[641px]:p-6 space-y-4" method="get" data-pending>
            <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label for="service-q" class="block text-xs font-bold text-[#0B1020]">Kata Kunci</label>
                    <input
                        id="service-q"
                        type="search"
                        name="q"
                        maxlength="200"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Judul, deskripsi, kategori, instansi"
                        class="glass-input w-full min-h-[44px] px-3.5 text-sm placeholder:text-[#4A5366]"
                    >
                </div>

                @foreach (['category' => 'Kategori Layanan', 'agency' => 'Instansi'] as $name => $label)
                    <div class="space-y-1.5">
                        <label for="service-{{ $name }}" class="block text-xs font-bold text-[#0B1020]">{{ $label }}</label>
                        <select
                            id="service-{{ $name }}"
                            name="{{ $name }}"
                            class="glass-input w-full min-h-[44px] px-3.5 text-sm"
                        >
                            <option value="">Semua {{ strtolower($label) }}</option>
                            @foreach ($options[$name] as $option)
                                <option @selected(($filters[$name] ?? '') === $option) value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                <div class="space-y-1.5">
                    <label for="service-type" class="block text-xs font-bold text-[#0B1020]">Jenis Layanan</label>
                    <select
                        id="service-type"
                        name="type"
                        class="glass-input w-full min-h-[44px] px-3.5 text-sm"
                    >
                        <option value="">Semua jenis</option>
                        <option value="daerah" @selected(($filters['type'] ?? '') === 'daerah')>Daerah</option>
                        <option value="umum" @selected(($filters['type'] ?? '') === 'umum')>Umum</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col min-[481px]:flex-row items-stretch min-[481px]:items-center justify-between gap-3 pt-1">
                <p class="text-xs min-[641px]:text-sm text-[#4A5366] font-semibold flex flex-wrap items-center gap-2" role="status">
                    <span class="text-[#0B1020] font-bold">{{ $records->total() }} informasi ditemukan.</span>
                    @foreach (collect($filters)->filter() as $name => $value)
                        <span class="px-3 py-1 rounded-full bg-[#E6F0FC] text-[#005EB8] border border-[#BFD7F5] text-xs font-bold">
                            {{ ['q' => 'Pencarian', 'category' => 'Kategori', 'agency' => 'Instansi', 'type' => 'Jenis', 'status' => 'Status'][$name] }}: {{ $value }}
                        </span>
                    @endforeach
                </p>

                <div class="flex flex-col min-[481px]:flex-row items-stretch min-[481px]:items-center gap-2.5 shrink-0">
                    <a
                        href="{{ route('services.index') }}"
                        class="btn-glass !min-h-[44px] px-5 text-xs min-[641px]:text-sm inline-flex items-center justify-center gap-1.5"
                    >
                        <span>Hapus Pencarian &amp; Filter</span>
                    </a>
                    <button
                        type="submit"
                        class="btn-primary !min-h-[44px] px-6 text-xs min-[641px]:text-sm inline-flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
                        <span>Cari / Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Service Catalog Cards -->
    <section aria-label="Daftar Informasi Layanan">
        @if ($records->count() > 0)
            <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-3 gap-5 min-[1008px]:gap-6">
                @foreach ($records as $record)
                    @php
                        $data = $record->payload;
                        $hasPdf = $record->file_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($record->file_path);
                    @endphp
                    <article class="surface-card card-interactive p-6 flex flex-col justify-between gap-5 min-w-0">
                        <div class="space-y-3 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="px-3 py-1 rounded-full bg-[#E6F0FC] text-[#005EB8] text-xs font-bold">
                                    {{ $data['service_category'] ?? $data['category'] ?? 'Layanan' }}
                                </span>
                                @if (! empty($data['type']))
                                    <span class="px-2.5 py-1 rounded-full bg-[#E6F5F3] text-[#0F766E] text-xs font-bold">
                                        {{ ucfirst($data['type']) }}
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <h2 class="type-h3 text-[#0B1020] break-words">{{ $data['title'] }}</h2>
                                <p class="text-xs font-bold text-[#0F766E]">{{ $data['agency'] ?? 'Instansi belum tersedia' }}</p>
                            </div>

                            <p class="text-sm text-[#4A5366] leading-relaxed break-words">
                                {{ \Illuminate\Support\Str::limit($data['body'], 200) }}
                            </p>

                            <dl class="surface-nested p-4 space-y-2.5 text-xs">
                                <div>
                                    <dt class="text-[#4A5366] font-semibold">Estimasi waktu</dt>
                                    <dd class="text-[#0B1020] font-bold mt-0.5">{{ $data['duration'] ?? 'Informasi belum tersedia' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#4A5366] font-semibold">Biaya resmi</dt>
                                    <dd class="text-[#0B1020] font-bold mt-0.5">{{ $data['cost'] ?? 'Informasi belum tersedia' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#4A5366] font-semibold">Persyaratan utama</dt>
                                    <dd class="text-[#333E52] font-medium mt-0.5 leading-relaxed">{{ \Illuminate\Support\Str::limit($data['requirements'] ?? 'Informasi belum tersedia', 160) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5 pt-2 border-t border-[#DCE4F0]">
                            <a
                                class="btn-primary !min-h-[44px] flex-1 px-4 text-xs min-[641px]:text-sm inline-flex items-center justify-center gap-1.5"
                                href="{{ route('services.show', $record->record_key) }}"
                            >
                                <span>Lihat Detail</span>
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_forward</span>
                            </a>
                            @if ($hasPdf)
                                <a
                                    class="btn-glass !min-h-[44px] px-4 text-xs min-[641px]:text-sm inline-flex items-center justify-center gap-1.5"
                                    href="{{ route('services.download', $record->record_key) }}"
                                >
                                    <span class="material-symbols-outlined text-[16px] text-[#0066CC]" aria-hidden="true">download</span>
                                    <span>Unduh PDF</span>
                                </a>
                            @else
                                <span class="text-xs font-semibold text-[#4A5366]">Dokumen belum tersedia</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="surface-card p-8 min-[641px]:p-10 text-center space-y-3">
                <div class="w-12 h-12 rounded-[16px] bg-[#E6F0FC] text-[#005EB8] mx-auto flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">search_off</span>
                </div>
                <h2 class="type-h3 text-[#0B1020]">Tidak ada informasi yang cocok</h2>
                <p class="text-sm text-[#4A5366] max-w-md mx-auto leading-relaxed">
                    Sesuaikan kata kunci atau hapus filter pencarian untuk melihat seluruh daftar layanan dan SOP yang tersedia.
                </p>
                <div class="pt-2">
                    <a href="{{ route('services.index') }}" class="btn-glass !min-h-[44px] px-5 text-xs min-[641px]:text-sm inline-flex items-center justify-center gap-1.5">
                        <span>Reset Pencarian &amp; Filter</span>
                    </a>
                </div>
            </div>
        @endif
    </section>

    @if ($records->hasPages())
        <div class="surface-card p-4 min-[641px]:p-5">
            {{ $records->links() }}
        </div>
    @endif
@endif
@endsection
