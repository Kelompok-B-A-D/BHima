import { fixtures } from './data';
const key = 'bhima-dashboard-demo-v1';
export function createDemoService(storage = window.sessionStorage) {
    let data;
    try { data = JSON.parse(storage.getItem(key) || 'null') || structuredClone(fixtures); }
    catch { throw new Error('Penyimpanan sesi demo tidak dapat dibaca. Aktifkan penyimpanan browser.'); }
    for (const name of Object.keys(fixtures)) if (!Array.isArray(data[name])) throw new Error('Data demo rusak. Hapus data sesi demo melalui pengaturan browser.');
    return {
        read: () => structuredClone(data),
        save(collection, record, role) {
            if (role !== 'admin' && collection !== 'complaints') throw new Error('Aksi di luar izin peran demo.');
            if (!Object.hasOwn(data, collection) || collection === 'activities' || collection === 'searches') throw new Error('Koleksi tidak dapat diubah.');
            const next = structuredClone(data);
            const i = next[collection].findIndex(x => x.id === record.id);
            if (i < 0) next[collection].unshift(record); else next[collection][i] = record;
            next.activities.unshift({date: new Date().toISOString(), text: `${role}: memperbarui ${collection} ${record.id}`});
            storage.setItem(key, JSON.stringify(next)); data = next;
        },
        remove(collection, id, role) {
            if (role !== 'admin' || !['contents', 'documents', 'budgets'].includes(collection)) throw new Error('Tidak diizinkan.');
            const next = structuredClone(data); next[collection] = next[collection].filter(x => x.id !== id);
            next.activities.unshift({date: new Date().toISOString(), text: `${role}: menghapus ${collection} ${id}`});
            storage.setItem(key, JSON.stringify(next)); data = next;
        },
    };
}
// Production must supply a server-authorized service with assignment checks and durable audit logs.
// No fictional API endpoints are defined here.

export function createApiService() {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    let data;
    async function request(url, options = {}) {
        const response = await fetch(url, {credentials: 'same-origin', ...options, headers: {Accept: 'application/json', 'X-CSRF-TOKEN': token, ...options.headers}});
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || `Requête gagal (${response.status}).`);
        return result;
    }
    return {
        async load() { data = await request('/internal/data'); },
        read() { return structuredClone(data); },
        async save(collection, record) {
            const form = new FormData();
            Object.entries(record).forEach(([key, value]) => { if (value !== null && value !== undefined && !Array.isArray(value)) form.append(key, value); });
            await request(`/internal/data/${collection}`, {method: 'POST', body: form});
            await this.load();
        },
        async remove(collection, id) { await request(`/internal/data/${collection}/${encodeURIComponent(id)}`, {method: 'DELETE'}); await this.load(); },
    };
}
