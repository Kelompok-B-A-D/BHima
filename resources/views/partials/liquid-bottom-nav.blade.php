@php
    $activeNav = $activeNav ?? 'beranda';
    $tabs = [
        [
            'key' => 'beranda',
            'label' => 'Beranda',
            'icon' => 'home',
            'href' => route('consultation.home'),
        ],
        [
            'key' => 'layanan',
            'label' => 'Layanan',
            'icon' => 'verified_user',
            'href' => route('services.index'),
        ],
        [
            'key' => 'asisten-hukum',
            'label' => 'Asisten',
            'icon' => 'smart_toy',
            'href' => route('consultation.index'),
        ],
        [
            'key' => 'lacak-tiket',
            'label' => 'Lacak',
            'icon' => 'track_changes',
            'href' => route('complaint.track'),
        ],
        [
            'key' => 'formulir-pengaduan',
            'label' => 'Lapor',
            'icon' => 'campaign',
            'href' => route('complaint.create'),
        ],
    ];
@endphp

<!-- Mobile & Tablet Floating Clear Liquid Glass Bottom Tab Bar (DESIGN (1).md Section 9) -->
<nav
    aria-label="Navigasi Cepat Bawah"
    class="bhima-bottom-nav stitch-no-print min-[1008px]:hidden fixed left-3 right-3 z-40 pointer-events-none flex justify-center"
    style="bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));"
>
    <div class="liquid-glass w-full max-w-[500px] pointer-events-auto rounded-full p-2 grid grid-cols-5 gap-1.5">
        @foreach ($tabs as $tab)
            @php
                $isActive = $activeNav === $tab['key'];
            @endphp
            <a
                href="{{ $tab['href'] }}"
                @if($isActive) aria-current="page" @endif
                class="press-scale min-h-[48px] rounded-full px-1.5 py-1 flex flex-col items-center justify-center gap-0.5 text-xs leading-none transition-colors {{ $isActive ? 'bg-[#0066CC] text-white font-bold shadow-[0_4px_12px_rgba(0,102,204,0.24)]' : 'text-[#0B1020] hover:bg-white/75 font-semibold' }}"
            >
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">{{ $tab['icon'] }}</span>
                <span class="truncate max-w-full">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
