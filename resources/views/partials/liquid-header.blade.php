@php
    $activeNav = $activeNav ?? 'beranda';
    $navInactiveClass = 'min-h-[44px] px-3 min-[1140px]:px-4 py-2 rounded-full text-xs font-semibold text-[#333E52] hover:text-[#0B1020] hover:bg-white/65 transition-colors whitespace-nowrap inline-flex items-center';
    $navActiveClass = 'min-h-[44px] px-3.5 min-[1140px]:px-4 py-2 rounded-full transition-colors bg-[#0066CC] text-white text-xs font-bold shadow-[0_4px_14px_rgba(0,102,204,0.24)] whitespace-nowrap inline-flex items-center';
    $drawerInactiveClass = 'min-h-[44px] px-4 py-2.5 rounded-full text-[#0B1020] hover:bg-white/75 font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5 transition-colors';
    $drawerActiveClass = 'min-h-[44px] px-4 py-2.5 rounded-full bg-[#0066CC] text-white font-bold text-xs min-[380px]:text-sm flex items-center gap-2.5 shadow-sm';
    $jdihHref = $activeNav === 'asisten-hukum' ? '#jdih-reference-panel' : route('consultation.home').'#peraturan';
@endphp

<!-- Floating Clear Liquid Glass Navigation Bar (16px from top, pill shape, zero gradients) -->
<header class="bhima-header-root stitch-no-print fixed top-3 min-[641px]:top-4 left-0 right-0 z-50 flex flex-col items-center px-3 min-[481px]:px-4 min-[641px]:px-6 pointer-events-none">
    <div class="bhima-header-pill liquid-glass h-16 w-full max-w-[1200px] pointer-events-auto rounded-full px-2.5 min-[380px]:px-3.5 min-[641px]:px-5 min-[1008px]:px-6 flex items-center justify-between gap-2 min-[1008px]:gap-3">
        <div class="flex items-center gap-2 min-[641px]:gap-3 min-w-0">
            <button
                type="button"
                id="beranda-mobile-menu-btn"
                aria-label="Buka Menu Navigasi"
                aria-expanded="false"
                aria-controls="beranda-mobile-drawer"
                class="min-[1008px]:hidden press-scale min-w-[44px] min-h-[44px] px-2.5 min-[420px]:px-3.5 rounded-full bg-white/80 hover:bg-white border border-[#75849B] flex items-center justify-center gap-1.5 text-[#0B1020] text-xs font-bold transition-colors shrink-0"
            >
                <span class="material-symbols-outlined text-[20px] text-[#0066CC]" id="beranda-mobile-menu-icon" aria-hidden="true">menu</span>
                <span class="hidden min-[420px]:inline">Menu</span>
            </button>

            <a class="min-h-[44px] flex items-center gap-2.5 shrink-0 group no-underline min-w-0" href="{{ route('consultation.home') }}">
                <div class="w-9 h-9 min-[380px]:w-10 min-[380px]:h-10 rounded-full bg-[#0066CC] flex items-center justify-center text-white shadow-[0_4px_12px_rgba(0,102,204,0.24)] shrink-0">
                    <span class="material-symbols-outlined text-[18px] min-[380px]:text-[20px]" aria-hidden="true">balance</span>
                </div>
                <div class="hidden min-[350px]:flex flex-col text-left min-w-0">
                    <span class="text-sm min-[481px]:text-base min-[641px]:text-lg tracking-tight leading-none text-[#0B1020] font-extrabold truncate">BHIMA</span>
                    <span class="hidden min-[740px]:block min-[1008px]:hidden min-[1220px]:block text-xs text-[#4A5366] font-semibold tracking-tight truncate mt-0.5">Bantuan Hukum &amp; Informasi</span>
                    <span class="hidden min-[641px]:block min-[740px]:hidden min-[1008px]:block min-[1220px]:hidden text-xs text-[#4A5366] font-semibold tracking-tight truncate mt-0.5">Portal Hukum Publik</span>
                </div>
            </a>
        </div>

        <!-- Primary 4-Link Inline Navigation (Laptop/Desktop >= 1008px) -->
        <nav class="hidden min-[1008px]:flex items-center gap-2 bg-white/40 px-2 py-1 rounded-full border border-white/70 shrink-0" aria-label="Navigasi Utama Portal">
            <a
                @if($activeNav === 'beranda') aria-current="page" @endif
                class="{{ $activeNav === 'beranda' ? $navActiveClass : $navInactiveClass }}"
                href="{{ route('consultation.home') }}"
            >Beranda</a>
            <a
                @if($activeNav === 'layanan') aria-current="page" @endif
                class="{{ $activeNav === 'layanan' ? $navActiveClass : $navInactiveClass }}"
                href="{{ route('services.index') }}"
            >Layanan &amp; SOP</a>
            <a
                @if($activeNav === 'asisten-hukum') aria-current="page" @endif
                class="{{ $activeNav === 'asisten-hukum' ? $navActiveClass : $navInactiveClass }}"
                href="{{ route('consultation.index') }}"
            >Asisten Hukum</a>
            <a
                @if($activeNav === 'lacak-tiket') aria-current="page" @endif
                class="{{ $activeNav === 'lacak-tiket' ? $navActiveClass : $navInactiveClass }}"
                href="{{ route('complaint.track') }}"
            >Lacak Tiket</a>
        </nav>

        <div class="flex items-center gap-2 shrink-0">
            <a
                href="{{ route('consultation.home') }}#pencarian-regulasi"
                aria-label="Cari Layanan atau Regulasi JDIH"
                title="Cari Layanan atau Regulasi"
                class="hidden min-[481px]:flex press-scale w-11 h-11 rounded-full bg-white/80 hover:bg-white items-center justify-center text-[#0B1020] border border-[#75849B] transition-colors"
            >
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span>
            </a>
            <a
                href="{{ route('login') }}"
                aria-label="Masuk Portal Petugas BHIMA"
                title="Portal Petugas BHIMA"
                class="press-scale w-11 h-11 rounded-full bg-white/80 hover:bg-white flex items-center justify-center text-[#0B1020] border border-[#75849B] transition-colors"
            >
                <span class="material-symbols-outlined text-[19px]" aria-hidden="true">person</span>
            </a>
            <a
                href="{{ route('complaint.create') }}"
                @if($activeNav === 'formulir-pengaduan') aria-current="page" @endif
                class="btn-primary !min-h-[44px] px-3.5 min-[380px]:px-4 min-[641px]:px-5 text-xs min-[641px]:text-sm inline-flex items-center justify-center gap-1.5 {{ $activeNav === 'formulir-pengaduan' ? 'ring-2 ring-[#F2D8B3] ring-offset-2' : '' }}"
            >
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                <span>Lapor</span>
            </a>
        </div>
    </div>

    <!-- Responsive Mobile & Tablet Floating Glass Drawer -->
    <nav
        id="beranda-mobile-drawer"
        hidden
        aria-label="Navigasi Perangkat Seluler dan Tablet"
        class="bhima-drawer liquid-glass liquid-glass--strong min-[1008px]:hidden w-full max-w-[1200px] mt-2 pointer-events-auto rounded-[28px] px-3.5 min-[641px]:px-5 py-4"
    >
        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-2">
            <a data-beranda-drawer-link class="{{ $activeNav === 'beranda' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('consultation.home') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'beranda' ? 'text-white' : 'text-[#0066CC]' }}" aria-hidden="true">home</span>
                <span>Beranda</span>
            </a>
            <a data-beranda-drawer-link class="{{ $activeNav === 'layanan' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('services.index') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'layanan' ? 'text-white' : 'text-[#0066CC]' }}" aria-hidden="true">verified_user</span>
                <span>Layanan &amp; SOP</span>
            </a>
            <a data-beranda-drawer-link class="{{ $activeNav === 'asisten-hukum' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('consultation.index') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'asisten-hukum' ? 'text-white' : 'text-[#0066CC]' }}" aria-hidden="true">smart_toy</span>
                <span>Asisten Hukum Virtual</span>
            </a>
            <a data-beranda-drawer-link class="{{ $activeNav === 'lacak-tiket' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('complaint.track') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'lacak-tiket' ? 'text-white' : 'text-[#0066CC]' }}" aria-hidden="true">track_changes</span>
                <span>Lacak Tiket</span>
            </a>
            <a data-beranda-drawer-link class="{{ $activeNav === 'formulir-pengaduan' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('complaint.create') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'formulir-pengaduan' ? 'text-white' : 'text-[#0F766E]' }}" aria-hidden="true">campaign</span>
                <span>Buat Pengaduan</span>
            </a>
            <a data-beranda-drawer-link class="{{ $drawerInactiveClass }}" href="{{ $jdihHref }}">
                <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">menu_book</span>
                <span>Peraturan JDIH</span>
            </a>
            <a data-beranda-drawer-link class="{{ $drawerInactiveClass }}" href="{{ route('consultation.home') }}#pencarian-regulasi">
                <span class="material-symbols-outlined text-[18px] text-[#0066CC]" aria-hidden="true">search</span>
                <span>Cari Dokumen JDIH</span>
            </a>
            <a data-beranda-drawer-link class="{{ $drawerInactiveClass }}" href="{{ route('login') }}">
                <span class="material-symbols-outlined text-[18px] text-[#4A5366]" aria-hidden="true">admin_panel_settings</span>
                <span>Portal Petugas</span>
            </a>
        </div>
    </nav>
</header>
