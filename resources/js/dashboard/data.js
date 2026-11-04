// Fixtures only. These records are never presented as operational data.
export const statuses = ['Baru', 'Menunggu Verifikasi', 'Menunggu Kelengkapan', 'Dalam Proses', 'Menunggu Disposisi', 'Selesai', 'Ditolak'];
export const categories = ['Administrasi Kependudukan', 'Pelayanan Kesehatan', 'Infrastruktur', 'Dugaan Pungutan Liar'];
export const fixtures = {
    complaints: Array.from({length: 32}, (_, i) => ({
        id: `BHM-2026-${String(i + 1).padStart(4, '0')}`, date: `2026-10-${String(28 - i % 27).padStart(2, '0')}`,
        category: categories[i % 4], reporter: i % 3 === 0 ? 'Anonim' : ['Siti Rahma', 'Budi Santoso', 'Dewi Lestari'][i % 3],
        summary: ['Permohonan KTP belum mendapat tindak lanjut', 'Kendala akses pelayanan BPJS', 'Perbaikan jalan lingkungan belum terlaksana', 'Permintaan biaya di luar ketentuan layanan'][i % 4],
        status: statuses[i % 7], priority: i % 5 === 0 ? 'Tinggi' : 'Normal', officer: i % 2 ? 'Petugas B' : 'Petugas A',
        chronology: 'Pelapor menyampaikan kendala pelayanan dan meminta pemeriksaan lebih lanjut. Kronologi ini merupakan contoh demonstrasi.',
        reported: 'Unit pelayanan contoh', location: 'Kantor pelayanan desa', incidentDate: '2026-10-20',
        attachments: [], ai: null, references: [], notes: [], history: [{date: '2026-10-28T08:00:00', text: 'Laporan contoh diterima'}],
    })),
    contents: [
        {id: 'C1', title: 'SOP pengurusan KTP elektronik', category: 'SOP', updated: '2026-10-28', status: 'Terbit', body: 'Contoh alur pelayanan administrasi kependudukan.'},
        {id: 'C2', title: 'Panduan pendaftaran BPJS', category: 'Panduan', updated: '2026-10-27', status: 'Draf', body: 'Contoh panduan persyaratan pendaftaran.'},
        {id: 'C3', title: 'Jadwal pelayanan masyarakat', category: 'Pengumuman', updated: '2026-10-26', status: 'Terbit', body: 'Contoh pengumuman jadwal pelayanan.'},
    ],
    documents: [{id: 'D1', title: 'Dokumen peraturan contoh', number: 'CONTOH/2026', year: '2026', category: 'Pelayanan Publik', summary: 'Metadata demonstrasi, bukan dokumen hukum resmi.', extraction: 'Menunggu', status: 'Menunggu', filename: 'Belum ada berkas'}],
    accounts: [{id: 'U1', name: 'Administrator Demo', email: 'admin@example.test', role: 'admin', status: 'Aktif', created: '2026-10-01'}, {id: 'U2', name: 'Petugas A', email: 'petugas@example.test', role: 'verifikator', status: 'Aktif', created: '2026-10-01'}],
    budgets: [{id: 'B1', title: 'Pelayanan administrasi', year: '2026', allocation: 150000000, realized: 98000000}, {id: 'B2', title: 'Infrastruktur desa', year: '2026', allocation: 420000000, realized: 280000000}],
    searches: [{term: 'Persyaratan KTP', count: 78}, {term: 'Pendaftaran BPJS', count: 56}, {term: 'SOP layanan desa', count: 43}, {term: 'Bantuan hukum', count: 31}],
    activities: [],
};
