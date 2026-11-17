@php
    $activeNav = $activeNav ?? 'beranda';
    $navInactiveClass = 'px-2.5 min-[1140px]:px-3.5 py-2 rounded-full text-xs font-semibold text-[#414754] hover:text-[#171C21] hover:bg-[#E4E9F0] transition-colors whitespace-nowrap';
    $navActiveClass = 'px-3 min-[1140px]:px-4 py-2 rounded-full transition-colors bg-[#005AB3] text-white text-xs font-bold shadow-[0_2px_8px_rgba(0,90,179,0.2)] whitespace-nowrap';
    $drawerInactiveClass = 'min-h-[44px] px-4 py-2.5 rounded-2xl text-[#171C21] hover:bg-[#EFF4FB] font-semibold text-xs min-[380px]:text-sm flex items-center gap-2.5 transition-colors';
    $drawerActiveClass = 'min-h-[44px] px-4 py-2.5 rounded-2xl bg-[#005AB3] text-white font-bold text-xs min-[380px]:text-sm flex items-center gap-2.5 shadow-sm';
    $jdihHref = $activeNav === 'asisten-hukum' ? '#jdih-reference-panel' : route('consultation.home').'#peraturan';
@endphp

<!-- FLOATING LIQUID GLASS HEADER -->
<!-- Responsive tiers: Handphone (320px-480px), Tablet (641px-1007px), Laptop/Desktop (>=1008px) -->
<header class="stitch-no-print fixed top-2.5 min-[641px]:top-4 left-0 right-0 z-50 flex flex-col items-center px-2.5 min-[481px]:px-4 min-[641px]:px-6 pointer-events-none">
    <div class="h-16 w-full max-w-[1200px] pointer-events-auto rounded-full bg-white/80 backdrop-blur-2xl border border-white/85 shadow-[0_16px_36px_rgba(20,30,80,0.08),0_2px_6px_rgba(0,0,0,0.03),inset_0_1px_1px_rgba(255,255,255,0.85)] px-2.5 min-[380px]:px-3.5 min-[641px]:px-5 min-[1008px]:px-6 flex items-center justify-between gap-1.5 min-[380px]:gap-2 min-[1008px]:gap-3">
        <div class="flex items-center gap-1.5 min-[380px]:gap-2.5 min-[641px]:gap-3 min-w-0">
            <button
                type="button"
                id="beranda-mobile-menu-btn"
                aria-label="Buka Menu Navigasi"
                aria-expanded="false"
                aria-controls="beranda-mobile-drawer"
                class="min-[1008px]:hidden min-w-[44px] min-h-[44px] px-2.5 min-[420px]:px-3.5 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] active:bg-[#DEE3EA] border border-[#C0C6D6]/60 flex items-center justify-center gap-1.5 text-[#171C21] text-xs font-bold transition-colors shrink-0"
            >
                <span class="material-symbols-outlined text-[20px] text-[#005AB3]" id="beranda-mobile-menu-icon" aria-hidden="true">menu</span>
                <span class="hidden min-[420px]:inline">Menu</span>
            </button>

            <a class="min-h-[44px] flex items-center gap-2 min-[641px]:gap-3 shrink-0 group no-underline min-w-0" href="{{ route('consultation.home') }}">
                <div class="w-9 h-9 min-[380px]:w-10 min-[380px]:h-10 rounded-full bg-[#005AB3] flex items-center justify-center text-white shadow-[0_4px_12px_rgba(0,90,179,0.25)] shrink-0">
                    <span class="material-symbols-outlined text-[18px] min-[380px]:text-[20px]" aria-hidden="true">balance</span>
                </div>
                <div class="hidden min-[350px]:flex flex-col text-left min-w-0">
                    <span class="text-sm min-[481px]:text-base min-[641px]:text-lg tracking-tight leading-none text-[#171C21] font-extrabold truncate">BHIMA</span>
                    <span class="hidden min-[740px]:block min-[1008px]:hidden min-[1220px]:block text-[10px] text-[#414754] font-semibold uppercase tracking-wider truncate mt-0.5">Bantuan Hukum &amp; Informasi</span>
                    <span class="hidden min-[641px]:block min-[740px]:hidden min-[1008px]:block min-[1220px]:hidden text-[10px] text-[#414754] font-semibold uppercase tracking-wider truncate mt-0.5">Portal Hukum Publik</span>
                </div>
            </a>
        </div>

        <!-- Inline Navigation Pill for Laptop/Desktop (>= 1008px) -->
        <nav class="hidden min-[1008px]:flex items-center gap-1 bg-[#EFF4FB]/90 px-2 py-1.5 rounded-full border border-[#DEE3EA] shrink-0" aria-label="Navigasi Utama Portal">
            <a
                @if($activeNav === 'beranda') aria-current="page" @endif
                class="{{ $activeNav === 'beranda' ? $navActiveClass : $navInactiveClass }}"
                href="{{ route('consultation.home') }}"
            >Beranda</a>
            <a
                class="{{ $navInactiveClass }}"
                href="{{ route('consultation.home') }}#layanan"
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
            <a
                class="{{ $navInactiveClass }}"
                href="{{ $jdihHref }}"
            >Peraturan JDIH</a>
        </nav>

        <div class="flex items-center gap-1.5 min-[641px]:gap-2.5 shrink-0">
            <a
                href="{{ route('complaint.create') }}"
                @if($activeNav === 'formulir-pengaduan') aria-current="page" @endif
                class="min-h-[44px] px-3 min-[380px]:px-4 min-[641px]:px-5 rounded-full bg-[#F59E0B] hover:bg-[#D97706] active:bg-[#B45309] text-[#111827] text-xs min-[641px]:text-sm flex items-center justify-center gap-1.5 font-bold shadow-[0_4px_16px_rgba(245,158,11,0.25)] transition-all {{ $activeNav === 'formulir-pengaduan' ? 'ring-2 ring-[#005AB3] ring-offset-2' : '' }}"
            >
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                <span>Lapor</span>
            </a>
            <a
                href="{{ route('consultation.home') }}#pencarian-regulasi"
                aria-label="Cari Layanan atau Regulasi JDIH"
                title="Cari Layanan atau Regulasi"
                class="hidden min-[481px]:flex w-11 h-11 rounded-full bg-[#EFF4FB] hover:bg-[#E4E9F0] items-center justify-center text-[#414754] hover:text-[#171C21] border border-[#DEE3EA] transition-colors"
            >
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span>
            </a>
            <a
                href="{{ route('login') }}"
                aria-label="Masuk Portal Petugas BHIMA"
                title="Portal Petugas BHIMA"
                class="w-11 h-11 rounded-full bg-[#005AB3] hover:bg-[#00468D] flex items-center justify-center text-white shadow-sm transition-colors"
            >
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person</span>
            </a>
        </div>
    </div>

    <!-- Responsive Handphone (320px-480px) & Tablet (641px-1007px) Floating Glass Drawer -->
    <nav
        id="beranda-mobile-drawer"
        hidden
        aria-label="Navigasi Perangkat Seluler dan Tablet"
        class="min-[1008px]:hidden w-full max-w-[1200px] mt-2 pointer-events-auto rounded-3xl bg-white/95 border border-[#DEE3EA] px-3.5 min-[641px]:px-5 py-4 shadow-[0_20px_48px_rgba(15,35,90,0.14)]"
    >
        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-2">
            <a data-beranda-drawer-link class="{{ $activeNav === 'beranda' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('consultation.home') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'beranda' ? 'text-white' : 'text-[#005AB3]' }}" aria-hidden="true">home</span>
                <span>Beranda</span>
            </a>
            <a data-beranda-drawer-link class="{{ $drawerInactiveClass }}" href="{{ route('consultation.home') }}#layanan">
                <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">verified_user</span>
                <span>Layanan &amp; SOP</span>
            </a>
            <a data-beranda-drawer-link class="{{ $activeNav === 'asisten-hukum' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('consultation.index') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'asisten-hukum' ? 'text-white' : 'text-[#005AB3]' }}" aria-hidden="true">smart_toy</span>
                <span>Asisten Hukum Virtual</span>
            </a>
            <a data-beranda-drawer-link class="{{ $activeNav === 'lacak-tiket' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('complaint.track') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'lacak-tiket' ? 'text-white' : 'text-[#005AB3]' }}" aria-hidden="true">track_changes</span>
                <span>Lacak Tiket</span>
            </a>
            <a data-beranda-drawer-link class="{{ $activeNav === 'formulir-pengaduan' ? $drawerActiveClass : $drawerInactiveClass }}" href="{{ route('complaint.create') }}">
                <span class="material-symbols-outlined text-[18px] {{ $activeNav === 'formulir-pengaduan' ? 'text-white' : 'text-[#0F766E]' }}" aria-hidden="true">campaign</span>
                <span>Buat Pengaduan</span>
            </a>
            <a data-beranda-drawer-link class="{{ $drawerInactiveClass }}" href="{{ $jdihHref }}">
                <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">menu_book</span>
                <span>Peraturan JDIH</span>
            </a>
            <a data-beranda-drawer-link class="{{ $drawerInactiveClass }}" href="{{ route('consultation.home') }}#pencarian-regulasi">
                <span class="material-symbols-outlined text-[18px] text-[#005AB3]" aria-hidden="true">search</span>
                <span>Cari Dokumen JDIH</span>
            </a>
            <a data-beranda-drawer-link class="{{ $drawerInactiveClass }}" href="{{ route('login') }}">
                <span class="material-symbols-outlined text-[18px] text-[#414754]" aria-hidden="true">admin_panel_settings</span>
                <span>Portal Petugas</span>
            </a>
        </div>
    </nav>
</header>
