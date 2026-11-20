<!-- Portal Footer (Clean Frosted Content Surface, Zero Glass-on-Glass) -->
<footer class="stitch-no-print w-full pt-8 min-[641px]:pt-10 pb-8 px-4 min-[641px]:px-6 mt-8 min-[641px]:mt-12">
    <div class="surface-card max-w-[1200px] mx-auto p-6 min-[641px]:p-8 min-[1008px]:p-10">
        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-4 gap-6 min-[641px]:gap-8 pb-8 border-b border-[#DCE4F0]">
            <div class="min-[641px]:col-span-2 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#0066CC] flex items-center justify-center text-white shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">balance</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-base min-[380px]:text-lg min-[641px]:text-xl text-[#0B1020] font-extrabold tracking-tight block truncate">BHIMA</span>
                        <span class="text-[11px] text-[#4A5366] font-semibold uppercase tracking-wider block truncate">Bantuan Hukum dan Informasi Masyarakat</span>
                    </div>
                </div>
                <p class="text-xs min-[380px]:text-sm text-[#4A5366] max-w-md leading-relaxed">
                    Portal layanan advokasi, pengaduan publik, dan rujukan peraturan JDIH yang diselenggarakan secara terbuka, aman, dan akuntabel.
                </p>
                <div class="flex items-center gap-2 pt-1 text-[#333E52] text-xs font-semibold">
                    <span class="material-symbols-outlined text-[18px] text-[#0F766E] shrink-0" aria-hidden="true">schedule</span>
                    <span>Jam Operasional: Senin sampai Jumat (08.00 - 16.30 WIB)</span>
                </div>
            </div>

            <div class="space-y-2.5">
                <h3 class="text-xs min-[641px]:text-sm text-[#0B1020] font-bold block uppercase tracking-wider">Kanal Publik</h3>
                <div class="flex flex-col space-y-0.5 text-xs min-[641px]:text-sm font-medium">
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.home') }}">Beranda Portal</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.home') }}#layanan">Layanan &amp; SOP Gratis</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.home') }}#pengumuman">Pengumuman Regulasi</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.home') }}#anggaran">Transparansi Anggaran</a>
                </div>
            </div>

            <div class="space-y-2.5">
                <h3 class="text-xs min-[641px]:text-sm text-[#0B1020] font-bold block uppercase tracking-wider">Bantuan Terpadu</h3>
                <div class="flex flex-col space-y-0.5 text-xs min-[641px]:text-sm font-medium">
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.index') }}">Asisten Hukum Virtual</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('complaint.track') }}">Lacak Status Tiket</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('complaint.create') }}">Formulir Pengaduan</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}">Basis Data Peraturan JDIH</a>
                </div>
            </div>
        </div>

        <div class="pt-6 flex flex-col min-[641px]:flex-row items-start min-[641px]:items-center justify-between gap-4 text-xs text-[#4A5366] font-semibold">
            <p>© {{ date('Y') }} Portal Layanan Publik BHIMA. Seluruh hak cipta dilindungi undang-undang.</p>
            <div class="flex flex-wrap items-center gap-3 min-[641px]:gap-6">
                <span class="inline-flex items-center gap-1.5 text-[#333E52]">
                    <span class="material-symbols-outlined text-[16px] text-[#0F766E] shrink-0" aria-hidden="true">verified_user</span>
                    <span>Sesuai UU No. 14/2008 &amp; UU No. 27/2022</span>
                </span>
                <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-017']) }}">Kebijakan Privasi</a>
                <a class="min-h-[44px] inline-flex items-center text-[#333E52] hover:text-[#0066CC] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-003']) }}">Standar Operasional</a>
            </div>
        </div>
    </div>
</footer>
