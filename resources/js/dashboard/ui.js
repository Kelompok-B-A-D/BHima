export const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
export const currency = value => new Intl.NumberFormat('id-ID', {style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(value) || 0);
export const date = value => value ? new Date(value).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'}) : 'Belum tersedia';
export const icons = {
 dashboard:'▦', pengaduan:'▤', konten:'▧', jdih:'▥', 'basis-ai':'✧', anggaran:'◈', analitik:'▥', pencarian:'⌕', akun:'♙', peran:'◇', aktivitas:'◷', pengaturan:'⚙',
};
export function badge(status) {
 const type = ['Selesai','Terbit','Aktif','Terindeks'].includes(status) ? 'success' : ['Ditolak','Gagal','Nonaktif','Tinggi'].includes(status) ? 'danger' : ['Baru','Dalam Proses'].includes(status) ? 'info' : 'warning';
 return `<span class="badge ${type}"><span aria-hidden="true">●</span> ${escape(status)}</span>`;
}
export function empty(message = 'Belum ada data', description = 'Data akan tampil di sini setelah tersedia.') { return `<div class="empty"><span aria-hidden="true">▤</span><h3>${escape(message)}</h3><p>${escape(description)}</p></div>`; }
export function stat(label, value, icon, sub = 'Sesuai data yang tersedia') { return `<article class="stat"><div class="stat-top"><span>${escape(label)}</span><span class="stat-icon" aria-hidden="true">${icon}</span></div><strong>${value}</strong><small>${escape(sub)}</small></article>`; }
export function bars(title, items, {money = false} = {}) {
 const max = Math.max(1,...items.map(i=>i.value));
 return `<section class="panel chart"><div class="panel-heading"><h2>${escape(title)}</h2><span class="muted">${money ? 'Rupiah' : 'Jumlah'}</span></div>${items.length ? `<div class="bar-chart">${items.map(i=>`<div class="bar-row"><div><span>${escape(i.label)}</span><strong>${money ? currency(i.value) : i.value}</strong></div><meter min="0" max="${max}" value="${i.value}" title="${escape(i.label)}: ${money ? currency(i.value) : i.value}">${i.value}</meter></div>`).join('')}</div>` : empty('Belum ada data grafik')}<p class="chart-legend"><i></i> ${money ? 'Realisasi anggaran' : 'Jumlah tercatat'} · Arahkan kursor atau baca nilai pada label</p></section>`;
}
export function trend(records) {
 const months = Array.from({length:6},(_,i)=>({label:['Mei','Jun','Jul','Agu','Sep','Okt'][i], key:`2026-${String(i+5).padStart(2,'0')}`}));
 // Caller uses the timeline derived from actual record dates, with no invented historical totals.
 const keys = [...new Set(records.map(r=>r.date?.slice(0,7)).filter(Boolean))].sort();
 const points = keys.map(key=>({label:new Date(`${key}-01`).toLocaleDateString('id-ID',{month:'short',year:'2-digit'}), value:records.filter(r=>r.date?.startsWith(key)).length}));
 if (!points.length) return `<section class="panel chart"><h2>Tren pengaduan</h2>${empty('Belum ada pengaduan')}</section>`;
 const max = Math.max(...points.map(p=>p.value),1);
 const dots = points.map((p,i)=>({ ...p, x: points.length === 1 ? 300 : 40+i*520/(points.length-1), y:145-p.value/max*110}));
 return `<section class="panel chart trend"><div class="panel-heading"><div><h2>Tren pengaduan</h2><p class="muted">Jumlah laporan per bulan</p></div><span class="badge info">${records.length} laporan</span></div><svg viewBox="0 0 600 200" role="img" aria-label="Grafik tren pengaduan per bulan"><defs><linearGradient id="area" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#2b67b1" stop-opacity=".22"/><stop offset="100%" stop-color="#2b67b1" stop-opacity="0"/></linearGradient></defs>${[35,90,145].map(y=>`<line x1="30" y1="${y}" x2="575" y2="${y}" stroke="#e8edf3" stroke-dasharray="4 4"/>`).join('')}<path d="M${dots[0].x},155 L${dots.map(p=>`${p.x},${p.y}`).join(' L')} L${dots.at(-1).x},155 Z" fill="url(#area)"/><polyline points="${dots.map(p=>`${p.x},${p.y}`).join(' ')}" fill="none" stroke="#2b67b1" stroke-width="3"/>${dots.map(p=>`<g tabindex="0"><title>${escape(p.label)}: ${p.value} pengaduan</title><circle cx="${p.x}" cy="${p.y}" r="5" fill="#2b67b1" stroke="white" stroke-width="2"/><text x="${p.x}" y="${p.y-12}" text-anchor="middle">${p.value}</text><text x="${p.x}" y="184" text-anchor="middle">${escape(p.label)}</text></g>`).join('')}</svg><p class="chart-legend"><i></i> Pengaduan masuk · Nilai bulanan ditampilkan pada setiap titik</p></section>`;
}
