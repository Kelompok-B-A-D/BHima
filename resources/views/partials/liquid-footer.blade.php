<!-- LIQUID GLASS FOOTER -->
<footer class="stitch-no-print w-full pt-8 min-[641px]:pt-10 pb-8 px-3 min-[481px]:px-4 min-[641px]:px-6 mt-8 min-[641px]:mt-12">
    <div class="max-w-[1200px] mx-auto rounded-[28px] bg-white/90 border border-white shadow-[0_16px_36px_rgba(20,30,80,0.06),inset_0_1px_1px_rgba(255,255,255,0.85)] p-5 min-[641px]:p-8 min-[1008px]:p-10">
        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1008px]:grid-cols-4 gap-6 min-[641px]:gap-8 pb-8 border-b border-[#DEE3EA]">
            <div class="min-[641px]:col-span-2 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#005AB3] flex items-center justify-center text-white shrink-0">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">balance</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-base min-[380px]:text-lg min-[641px]:text-xl text-[#171C21] font-extrabold tracking-tight block truncate">BHIMA</span>
                        <span class="text-[10px] min-[380px]:text-[11px] text-[#414754] font-semibold uppercase tracking-wider block truncate">Bantuan Hukum dan Informasi Masyarakat</span>
                    </div>
                </div>
                <p class="text-xs min-[380px]:text-sm text-[#414754] max-w-md leading-relaxed">
                    Sistem portal integrasi advokasi, pengaduan publik, dan transparansi informasi hukum masyarakat yang diselenggarakan secara adil, aman, dan akuntabel.
                </p>
                <div class="flex items-center gap-2 pt-1 text-[#414754] text-xs font-semibold">
                    <span class="material-symbols-outlined text-[18px] text-[#0F766E] shrink-0" aria-hidden="true">schedule</span>
                    <span>Jam Operasional: Senin - Jumat (08.00 - 16.30 WIB)</span>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-xs min-[641px]:text-sm text-[#171C21] font-bold block uppercase tracking-wider">Kanal Publik</h3>
                <div class="flex flex-col space-y-1 text-xs min-[641px]:text-sm font-medium">
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('consultation.home') }}">Beranda Portal</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('consultation.home') }}#layanan">Layanan &amp; SOP Gratis</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('consultation.home') }}#pengumuman">Pengumuman Regulasi</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('consultation.home') }}#anggaran">Transparansi Anggaran</a>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-xs min-[641px]:text-sm text-[#171C21] font-bold block uppercase tracking-wider">Bantuan Terpadu</h3>
                <div class="flex flex-col space-y-1 text-xs min-[641px]:text-sm font-medium">
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('consultation.index') }}">Asisten Hukum Virtual</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('complaint.track') }}">Lacak Status Tiket</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('complaint.create') }}">Formulir Pengaduan</a>
                    <a class="min-h-[44px] inline-flex items-center text-[#414754] hover:text-[#005AB3] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-001']) }}">Basis Data Peraturan JDIH</a>
                </div>
            </div>
        </div>

        <div class="pt-6 flex flex-col min-[641px]:flex-row items-start min-[641px]:items-center justify-between gap-4 text-xs text-[#414754] font-semibold">
            <p>© {{ date('Y') }} Portal Layanan Publik BHIMA. Seluruh hak cipta dilindungi undang-undang.</p>
            <div class="flex flex-wrap items-center gap-3 min-[641px]:gap-6">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#0F766E] shrink-0" aria-hidden="true"></span>
                    <span>Sesuai UU No. 14/2008 &amp; UU No. 27/2022</span>
                </span>
                <a class="min-h-[44px] inline-flex items-center hover:text-[#005AB3] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-019']) }}">Kebijakan Privasi</a>
                <a class="min-h-[44px] inline-flex items-center hover:text-[#005AB3] transition-colors" href="{{ route('consultation.reference', ['key' => 'JDIH-003']) }}">Standar Operasional</a>
            </div>
        </div>
    </div>
</footer>
