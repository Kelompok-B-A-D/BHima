import { escape as e } from '../dashboard/ui';

initLiquidHeaderDrawer();

const root = document.getElementById('consultation-app');
if (root) {
    initConsultationApp(root);
}

const berandaRoot = document.getElementById('beranda-app');
if (berandaRoot) {
    initBerandaApp(berandaRoot);
}

function initLiquidHeaderDrawer() {
    const mobileMenuBtn = document.getElementById('beranda-mobile-menu-btn');
    const mobileMenuIcon = document.getElementById('beranda-mobile-menu-icon');
    const mobileDrawer = document.getElementById('beranda-mobile-drawer');
    if (!mobileMenuBtn || !mobileDrawer) return;

    function setDrawerOpen(isOpen) {
        mobileDrawer.hidden = !isOpen;
        mobileMenuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (mobileMenuIcon) {
            mobileMenuIcon.textContent = isOpen ? 'close' : 'menu';
        }
    }

    mobileMenuBtn.addEventListener('click', () => {
        setDrawerOpen(mobileDrawer.hidden);
    });

    mobileDrawer.querySelectorAll('[data-beranda-drawer-link]').forEach((link) => {
        link.addEventListener('click', () => setDrawerOpen(false));
    });

    document.addEventListener('click', (event) => {
        if (mobileDrawer.hidden) return;
        const target = event.target;
        if (!mobileDrawer.contains(target) && !mobileMenuBtn.contains(target)) {
            setDrawerOpen(false);
        }
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !mobileDrawer.hidden) {
            setDrawerOpen(false);
            mobileMenuBtn.focus();
        }
    });
}

function initConsultationApp(container) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const maxLen = Number(container.dataset.maxLength || 4000);
    let sessionId = container.dataset.sessionId || window.sessionStorage.getItem('bhima_consult_session') || '';
    if (sessionId) {
        window.sessionStorage.setItem('bhima_consult_session', sessionId);
    }

    const messagesEl = container.querySelector('#chat-messages');
    const loadingEl = container.querySelector('#chat-loading');
    const errorEl = container.querySelector('#chat-error');
    const formEl = container.querySelector('#consultation-form');
    const inputEl = container.querySelector('#chronology-input');
    const sendBtn = container.querySelector('#send-btn');
    const charCounter = container.querySelector('#char-counter');
    const resetBtn = container.querySelector('#reset-session-btn');
    const sessionIdLabel = container.querySelector('#session-id-label');
    const jdihSidebarList = container.querySelector('#jdih-sidebar-list');
    const defaultSidebarHtml = jdihSidebarList ? jdihSidebarList.innerHTML : '';

    let busy = false;

    function updateSessionBadge(id) {
        if (sessionIdLabel && id) {
            sessionIdLabel.textContent = `#BHM-${String(id).slice(0, 8).toUpperCase()}`;
        }
    }

    function updateCounter() {
        const len = inputEl.value.length;
        charCounter.textContent = `${len} / ${maxLen} karakter`;
    }

    function showError(message) {
        if (!message) {
            errorEl.hidden = true;
            errorEl.textContent = '';
            return;
        }
        errorEl.textContent = message;
        errorEl.hidden = false;
    }

    function formatTime(isoString) {
        if (!isoString) return 'Baru saja';
        try {
            return new Date(isoString).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch {
            return 'Baru saja';
        }
    }

    function formatAssistantBody(rawText) {
        const lines = String(rawText || '').replace(/\s+\u2014\s+/g, ' - ').split('\n');
        return lines
            .map((line) => {
                const trimmed = line.trim();
                if (!trimmed) return '';
                if (/^\([a-d]\)\s+/i.test(trimmed)) {
                    return `<h4 class="stitch-answer-section-title">${e(trimmed)}</h4>`;
                }
                if (trimmed.startsWith('Penting:')) {
                    return `<div class="bg-[#FEF3C7] border border-[#FDE68A] p-3.5 rounded-xl text-xs text-[#92400E] font-medium my-2">${e(trimmed)}</div>`;
                }
                if (trimmed.startsWith('Penjelasan di atas bersifat informasi') || trimmed.startsWith('Catatan:')) {
                    return `<p class="stitch-answer-disclaimer">${e(trimmed)}</p>`;
                }
                if (/^[-•]\s+/.test(trimmed) || /^\d+\.\s+/.test(trimmed)) {
                    return `<p class="stitch-answer-list-item">${e(trimmed)}</p>`;
                }
                return `<p>${e(trimmed)}</p>`;
            })
            .filter(Boolean)
            .join('');
    }

    function updateRightSidebarReferences(citations = []) {
        if (!jdihSidebarList || !Array.isArray(citations) || citations.length === 0) {
            return;
        }

        const uniqueDocs = [];
        const seenKeys = new Set();
        for (const c of citations) {
            const key = c.document_key || c.document_name;
            if (!seenKeys.has(key)) {
                seenKeys.add(key);
                uniqueDocs.push(c);
            }
            if (uniqueDocs.length >= 3) break;
        }

        jdihSidebarList.innerHTML = uniqueDocs
            .map((c) => {
                const isSop = c.category === 'SOP';
                const iconBoxClass = isSop
                    ? 'bg-[#9CF2E8] text-[#00504A]'
                    : 'bg-[#D6E3FF] text-[#00468D]';
                const iconName = isSop ? 'description' : 'picture_as_pdf';
                const scorePct = (Number(c.score || 0) * 100).toFixed(1);
                const officialUrl = c.source_page_url || c.source_url;
                const openLabel = c.source_page_url ? 'Sumber Resmi' : 'Buka';
                const statusBadge =
                    c.status_berlaku === 'diubah' && c.diubah_oleh
                        ? `<span class="mt-1 inline-block px-2 py-0.5 bg-[#FEF3C7] text-[#78350F] rounded-md text-[10px] font-bold">Diubah: ${e(c.diubah_oleh)}</span>`
                        : '';

                return `
                    <div class="p-4 bg-[#EFF4FB]/90 rounded-2xl border border-[#DEE3EA] hover:border-[#005AB3] transition-all flex flex-col justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl ${iconBoxClass} flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[19px]" aria-hidden="true">${iconName}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] text-[#005AB3] font-bold block uppercase tracking-wider">
                                    ${e(c.document_number || c.category || 'JDIH')} (${e(c.document_year || '-')})
                                </span>
                                <h3 class="text-sm text-[#171C21] font-bold truncate" title="${e(c.document_name)}">
                                    ${e(c.document_name)}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-1 text-[#414754] text-[11px] flex-wrap">
                                    <span class="px-2 py-0.5 bg-[#9CF2E8] text-[#00504A] rounded-md font-bold">
                                        ${e(c.article_label || 'Pasal Terkait')}
                                    </span>
                                    <span>•</span>
                                    <span>Relevansi ${scorePct}%</span>
                                </div>
                                ${statusBadge}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                href="${e(c.source_url)}"
                                target="_blank"
                                rel="noopener"
                                class="flex-1 min-h-[44px] px-3 rounded-full bg-white border border-[#C0C6D6] hover:bg-[#D6E3FF] text-[#005AB3] font-bold text-xs flex items-center justify-center gap-1.5 transition-colors"
                            >
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">visibility</span>
                                <span>Baca Pasal</span>
                            </a>
                            <a
                                href="${e(officialUrl)}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="min-h-[44px] px-3.5 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white font-bold text-xs flex items-center justify-center gap-1 transition-colors"
                            >
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">open_in_new</span>
                                <span>${e(openLabel)}</span>
                            </a>
                        </div>
                    </div>
                `;
            })
            .join('');
    }

    function renderSourcesSection(citations = [], retrievalMeta = {}) {
        const count = Array.isArray(citations) ? citations.length : 0;
        const isOutOfScope = retrievalMeta?.status === 'out_of_scope' || count === 0;

        if (isOutOfScope) {
            return `
                <details class="w-fit max-w-[96%] min-[641px]:max-w-xl bg-white rounded-2xl border border-[#FDE68A] shadow-sm overflow-hidden mt-1.5">
                    <summary class="min-h-[44px] bg-[#FEF3C7] text-[#78350F] px-4 py-2.5 flex flex-wrap items-center justify-between gap-2.5 cursor-pointer select-none">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-[#92400E] shrink-0" aria-hidden="true">warning</span>
                            <span class="text-xs font-bold text-[#78350F]">
                                Sumber Rujukan (0 Pasal di Atas Ambang Kemiripan)
                            </span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-white text-[#78350F] border border-[#FDE68A] text-[11px] font-bold">
                            Di Luar Cakupan
                        </span>
                    </summary>
                    <div class="p-4 bg-white text-xs text-[#414754] leading-relaxed">
                        <p>
                            Sistem tidak menemukan potongan pasal JDIH atau SOP layanan yang melewati ambang kemiripan minimum untuk kronologi ini. Sesuai standar keamanan BHIMA, asisten tidak mengarang pasal atau substansi hukum.
                        </p>
                    </div>
                </details>
            `;
        }

        const topScorePct = (Math.max(...citations.map((c) => Number(c.score || 0))) * 100).toFixed(1);
        const primaryUrl = citations[0]?.source_url || '#jdih-reference-panel';

        const itemsHtml = citations
            .map((c) => {
                const scorePct = (Number(c.score || 0) * 100).toFixed(1);
                const isSop = c.category === 'SOP';
                const iconName = isSop ? 'description' : 'account_balance';
                const badgeBg = isSop ? 'bg-[#9CF2E8] text-[#00504A]' : 'bg-[#D6E3FF] text-[#00468D]';
                const badgeLabel = isSop ? 'SOP Layanan' : 'Dokumen JDIH';

                return `
                    <div class="p-3.5 bg-[#EFF4FB]/90 rounded-2xl border border-[#DEE3EA] space-y-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <span class="text-xs min-[641px]:text-sm font-bold text-[#005AB3] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[#005AB3] text-[18px] shrink-0" aria-hidden="true">${iconName}</span>
                                <span>${e(c.document_number || c.document_key)} • Tahun ${e(c.document_year || '-')}</span>
                            </span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2.5 py-0.5 ${badgeBg} rounded-full text-[11px] font-bold">${badgeLabel}</span>
                                <span class="px-2.5 py-0.5 bg-white border border-[#DEE3EA] text-[#0F766E] rounded-full text-[11px] font-bold">
                                    Relevansi ${scorePct}%
                                </span>
                            </div>
                        </div>
                        <p class="text-xs min-[641px]:text-sm text-[#171C21] font-bold">
                            ${e(c.document_name)}
                        </p>
                        <div class="bg-white p-3 rounded-xl border border-[#DEE3EA] space-y-1.5">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-[11px] text-[#0F766E] uppercase font-bold tracking-wide">
                                    Kutipan ${e(c.article_label)}:
                                </span>
                                <a
                                    href="${e(c.source_url)}"
                                    target="_blank"
                                    rel="noopener"
                                    class="min-h-[44px] px-2.5 text-xs font-bold text-[#005AB3] hover:text-[#00468D] hover:underline inline-flex items-center gap-1"
                                >
                                    <span>Buka Sumber (${e(c.document_key)})</span>
                                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">open_in_new</span>
                                </a>
                            </div>
                            <p class="text-xs text-[#171C21] italic leading-relaxed">"${e(String(c.excerpt || '').trim())}"</p>
                        </div>
                    </div>
                `;
            })
            .join('');

        return `
            <details class="w-full max-w-2xl bg-white rounded-[24px] border border-[#DEE3EA] shadow-[0_8px_24px_rgba(20,30,80,0.05)] overflow-hidden mt-1.5">
                <summary class="min-h-[48px] bg-[#005AB3] text-white px-4 py-3 flex flex-wrap items-center justify-between gap-3 cursor-pointer select-none">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="material-symbols-outlined text-[21px] text-[#D6E3FF] shrink-0" aria-hidden="true">balance</span>
                        <div class="min-w-0">
                            <h3 class="text-xs min-[641px]:text-sm font-bold tracking-tight text-white">
                                Sumber Rujukan &amp; Analisis Pasal (${count} Dokumen)
                            </h3>
                            <p class="text-[11px] text-[#D6E3FF]">
                                Klik untuk membuka kutipan pasal JDIH &amp; SOP Layanan
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-auto">
                        <span class="px-2.5 py-0.5 rounded-full bg-white/15 text-white border border-white/25 text-[11px] font-bold tracking-wide">
                            Relevansi ${topScorePct}%
                        </span>
                        <span class="material-symbols-outlined text-[18px] text-white transition-transform summary-chevron" aria-hidden="true">expand_more</span>
                    </div>
                </summary>
                <div class="p-4 min-[641px]:p-5 space-y-3.5 bg-white">
                    ${itemsHtml}

                    <div class="p-3 bg-[#EFF4FB] border border-[#DEE3EA] rounded-2xl flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[#414754] text-[18px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                        <p class="text-xs text-[#414754] leading-relaxed">
                            <strong class="text-[#171C21] font-bold">Pemberitahuan Resmi:</strong>
                            Hasil analisis diproses langsung dari basis data regulasi JDIH dan SOP layanan publik. Informasi ini bersifat edukasi hukum awal dan bukan nasihat hukum resmi.
                        </p>
                    </div>

                    <div class="pt-1 flex flex-col min-[641px]:flex-row min-[641px]:flex-wrap gap-2 items-stretch min-[641px]:items-center">
                        <a
                            class="flex-1 min-h-[44px] flex items-center justify-center gap-2 bg-[#005AB3] hover:bg-[#00468D] text-white py-2.5 px-4 rounded-full font-bold text-xs min-[641px]:text-sm shadow-sm transition-colors text-center"
                            href="${e(primaryUrl)}"
                            target="_blank"
                            rel="noopener"
                        >
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">menu_book</span>
                            <span>Lihat Dokumen Rujukan Lengkap</span>
                        </a>
                        <div class="flex items-center gap-2">
                            <a
                                class="flex-1 min-[641px]:flex-initial min-h-[44px] flex items-center justify-center gap-1.5 bg-[#EFF4FB] hover:bg-[#D6E3FF] text-[#005AB3] border border-[#C0C6D6] py-2.5 px-4 rounded-full font-bold text-xs min-[641px]:text-sm transition-colors"
                                href="#posbakum-panel"
                            >
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">support_agent</span>
                                <span>Posbakum</span>
                            </a>
                            <button
                                type="button"
                                data-copy-answer="1"
                                aria-label="Salin Ringkasan Jawaban"
                                class="w-11 h-11 rounded-full bg-white border border-[#C0C6D6] text-[#414754] hover:text-[#005AB3] hover:bg-[#EFF4FB] transition-colors flex items-center justify-center shrink-0"
                                title="Salin Ringkasan Jawaban"
                            >
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
            </details>
        `;
    }

    function getBubbleSizingClasses(text) {
        const clean = String(text || '').trim();
        const len = clean.length;
        const lineCount = clean.split('\n').filter((l) => l.trim().length > 0).length;

        if (len <= 70 && lineCount <= 1) {
            return 'inline-block w-fit max-w-[85%] sm:max-w-md px-3.5 py-2';
        }
        if (len <= 200 && lineCount <= 3) {
            return 'inline-block w-fit max-w-[90%] sm:max-w-lg px-3.5 py-2.5 sm:px-4 sm:py-3';
        }
        return 'inline-block w-fit max-w-[95%] sm:max-w-2xl p-3.5 sm:p-4 md:p-5';
    }

    let lastUserChronology = '';

    function renderLegalAnalysisCard(analysis, msg) {
        if (!analysis || typeof analysis !== 'object') {
            return '';
        }

        const rujukan = Array.isArray(analysis.rujukan) ? analysis.rujukan : [];
        const pasalRelevan = Array.isArray(analysis.pasal_relevan) ? analysis.pasal_relevan : [];
        const ancamanSanksi = Array.isArray(analysis.ancaman_sanksi) ? analysis.ancaman_sanksi : [];
        const perluEskalasi = Boolean(analysis.perlu_eskalasi);
        const catatan = String(analysis.catatan || '').trim();
        const sanctionLabel = String(
            analysis.meta?.sanction_label || 'berdasarkan bunyi pasal, bukan prediksi putusan'
        );
        const disclaimer = String(
            analysis.meta?.disclaimer ||
                'Informasi umum untuk edukasi awal warga, bukan nasihat hukum resmi maupun putusan pengadilan.'
        );
        const isError = analysis.meta?.status === 'error';

        const rujukanHtml = rujukan.length
            ? `<div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-2.5">
                ${rujukan
                    .map(
                        (r) => `
                        <div class="min-h-[44px] p-3 rounded-2xl bg-[#EFF4FB] border border-[#DEE3EA] flex flex-col justify-between gap-2">
                            <a
                                href="${e(r.tautan || '#jdih-reference-panel')}"
                                target="_blank"
                                rel="noopener"
                                class="flex items-start justify-between gap-2 no-underline group"
                            >
                                <div class="min-w-0">
                                    <span class="text-[11px] font-bold text-[#005AB3] block uppercase tracking-wide">${e(r.nomor_tahun || '-')}</span>
                                    <span class="text-xs font-bold text-[#171C21] block truncate group-hover:text-[#00468D]" title="${e(r.dokumen || '')}">${e(r.dokumen || '')}</span>
                                    ${
                                        r.status_berlaku === 'diubah' && r.diubah_oleh
                                            ? `<span class="mt-1 inline-block px-2 py-0.5 rounded-md bg-[#FEF3C7] text-[#78350F] text-[10px] font-bold">Diubah oleh: ${e(r.diubah_oleh)}</span>`
                                            : ''
                                    }
                                </div>
                                <span class="material-symbols-outlined text-[16px] text-[#005AB3] shrink-0 mt-0.5" aria-hidden="true">open_in_new</span>
                            </a>
                            ${
                                r.tautan_resmi
                                    ? `<a href="${e(r.tautan_resmi)}" target="_blank" rel="noopener noreferrer" class="text-[11px] font-bold text-[#0F766E] hover:underline inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[13px]" aria-hidden="true">verified</span>
                                        <span>Cek Sumber Resmi JDIH</span>
                                       </a>`
                                    : ''
                            }
                        </div>
                    `
                    )
                    .join('')}
               </div>`
            : `<p class="text-xs text-[#414754] italic bg-[#EFF4FB] p-3 rounded-2xl border border-[#DEE3EA]">Belum ada rujukan undang-undang yang cocok di atas ambang kemiripan.</p>`;

        const pasalHtml = pasalRelevan.length
            ? `<div class="space-y-2.5">
                ${pasalRelevan
                    .map(
                        (p) => `
                        <div class="p-3.5 rounded-2xl bg-[#EFF4FB] border border-[#DEE3EA] space-y-1.5">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-xs font-bold text-[#005AB3] flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">gavel</span>
                                    ${e(p.pasal || '')}
                                </span>
                                <span class="text-[11px] font-semibold text-[#414754]">${e(p.dokumen || '')}</span>
                            </div>
                            <p class="text-xs text-[#171C21] leading-relaxed">${e(p.penjelasan_awam || '')}</p>
                            <p class="text-[11px] text-[#414754] leading-relaxed border-t border-[#DEE3EA] pt-1.5">
                                <strong class="text-[#0F766E]">Keterkaitan Kronologi:</strong> ${e(p.alasan_terkait_kronologi || '')}
                            </p>
                        </div>
                    `
                    )
                    .join('')}
               </div>`
            : `<p class="text-xs text-[#414754] italic bg-[#EFF4FB] p-3 rounded-2xl border border-[#DEE3EA]">Tidak ditemukan pasal yang berpotensi terkait pada konteks dokumen.</p>`;

        const sanksiBadgeMap = {
            pidana: 'bg-[#FFDAD6] text-[#93000A] border-[#BA1A1A]/30',
            administratif: 'bg-[#D6E3FF] text-[#00468D] border-[#AEC6FF]',
            perdata: 'bg-[#FEF3C7] text-[#78350F] border-[#FDE68A]',
            lainnya: 'bg-[#EFF4FB] text-[#171C21] border-[#DEE3EA]',
        };

        const sanksiHtml = ancamanSanksi.length
            ? `<div class="space-y-2">
                ${ancamanSanksi
                    .map((s) => {
                        const jenisKey = String(s.jenis || 'lainnya').toLowerCase();
                        const badgeCls = sanksiBadgeMap[jenisKey] || sanksiBadgeMap.lainnya;
                        return `
                            <div class="p-3.5 rounded-2xl bg-white border border-[#DEE3EA] space-y-1">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-[#171C21]">${e(s.pasal || '')} <span class="font-medium text-[#414754]">• ${e(s.dokumen || '')}</span></span>
                                    <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-bold uppercase ${badgeCls}">
                                        ${e(jenisKey)}
                                    </span>
                                </div>
                                <p class="text-xs text-[#414754] leading-relaxed">${e(s.bunyi_sanksi_ringkas || '')}</p>
                            </div>
                        `;
                    })
                    .join('')}
               </div>`
            : `<p class="text-xs text-[#414754] italic bg-[#EFF4FB] p-3 rounded-2xl border border-[#DEE3EA]">Tidak tercantum dalam potongan pasal yang ditemukan.</p>`;

        // Bangun URL eskalasi ke formulir pengaduan dengan prefill otomatis
        const params = new URLSearchParams();
        if (sessionId) params.set('session_id', sessionId);
        if (msg?.id) params.set('message_id', String(msg.id));
        if (lastUserChronology) params.set('chronology', lastUserChronology);
        if (pasalRelevan.length > 0) {
            const summaryLines = pasalRelevan.map((p) => `- ${p.pasal} (${p.dokumen}): ${p.penjelasan_awam}`);
            ancamanSanksi.forEach((s) => {
                summaryLines.push(`- Estimasi Sanksi ${s.pasal} [${String(s.jenis).toUpperCase()}]: ${s.bunyi_sanksi_ringkas}`);
            });
            params.set('pasal_ringkas', summaryLines.join('\n'));
        }
        if (rujukan.length > 0) {
            params.set(
                'rujukan',
                rujukan.map((r) => `${r.dokumen} (${r.nomor_tahun}) - ${r.tautan}`).join('\n')
            );
        }
        const escalationUrl = `/pengaduan/buat?${params.toString()}`;

        const escalationButtonClass = perluEskalasi
            ? 'w-full min-[641px]:w-auto min-h-[48px] flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-[#F59E0B] hover:bg-[#D97706] text-[#111827] font-extrabold text-xs min-[641px]:text-sm shadow-[0_6px_18px_rgba(245,158,11,0.30)] transition-all no-underline'
            : 'w-full min-[641px]:w-auto min-h-[48px] flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-[#005AB3] hover:bg-[#00468D] text-white font-bold text-xs min-[641px]:text-sm shadow-sm transition-all no-underline';

        const escalationBadge = perluEskalasi
            ? `<span class="px-3 py-1 rounded-full bg-[#FEF3C7] text-[#78350F] border border-[#FDE68A] text-[11px] font-bold">Disarankan Eskalasi</span>`
            : `<span class="px-3 py-1 rounded-full bg-[#9CF2E8] text-[#00504A] text-[11px] font-bold">Terkendali</span>`;

        return `
            <section
                class="legal-analysis-card w-full max-w-2xl bg-white rounded-[24px] border-2 ${perluEskalasi ? 'border-[#F59E0B]/70' : 'border-[#DEE3EA]'} shadow-[0_12px_28px_rgba(20,30,80,0.06)] overflow-hidden mt-1.5"
                aria-label="Hasil Analisis Pasal dan Estimasi Ancaman Sanksi"
            >
                <div class="bg-[#005AB3] text-white px-4 py-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="material-symbols-outlined text-[20px] text-[#D6E3FF] shrink-0" aria-hidden="true">assignment_turned_in</span>
                        <h3 class="text-xs min-[641px]:text-sm font-bold text-white truncate">
                            Ringkasan Validasi Regulasi &amp; Estimasi Sanksi
                        </h3>
                    </div>
                    ${escalationBadge}
                </div>

                <div class="p-4 min-[641px]:p-5 space-y-4">
                    ${
                        isError
                            ? `<div role="alert" class="p-3 rounded-2xl bg-[#FEF3C7] border border-[#FDE68A] text-xs text-[#78350F] font-bold">${e(catatan)}</div>`
                            : ''
                    }

                    <!-- 1. Rujukan Undang-Undang -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#005AB3] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">menu_book</span>
                            Rujukan Undang-Undang &amp; Regulasi (${rujukan.length})
                        </h4>
                        ${rujukanHtml}
                    </div>

                    <!-- 2. Pasal Terkait -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#005AB3] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">fact_check</span>
                                Pasal yang Berpotensi Terkait (${pasalRelevan.length})
                            </h4>
                            <span class="text-[11px] font-semibold text-[#414754]">Berdasarkan kronologi, bukan putusan pelanggaran</span>
                        </div>
                        ${pasalHtml}
                    </div>

                    <!-- 3. Estimasi Ancaman Sanksi -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#005AB3] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">gavel</span>
                                Estimasi Ancaman Sanksi
                            </h4>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#FEF3C7] text-[#78350F] border border-[#FDE68A] text-[10px] font-bold">
                                ${e(sanctionLabel)}
                            </span>
                        </div>
                        ${sanksiHtml}
                    </div>

                    ${
                        !isError && catatan
                            ? `<div class="p-3 rounded-2xl bg-[#D6E3FF]/35 border border-[#AEC6FF] text-xs text-[#171C21] leading-relaxed">${e(catatan)}</div>`
                            : ''
                    }

                    <!-- Disclaimer Singkat -->
                    <p class="text-[11px] text-[#414754] leading-relaxed">
                        <strong class="text-[#171C21]">Disclaimer:</strong> ${e(disclaimer)}
                    </p>

                    <!-- Tombol Eskalasi ke Formulir Pengaduan -->
                    <div class="pt-3 border-t border-[#DEE3EA] flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-3">
                        <span class="text-xs font-semibold ${perluEskalasi ? 'text-[#78350F]' : 'text-[#414754]'}">
                            ${
                                perluEskalasi
                                    ? 'Permasalahan ini disarankan untuk dieskalasi ke petugas verifikator pengaduan.'
                                    : 'Ingin melaporkan kendala ini secara resmi ke petugas verifikator?'
                            }
                        </span>
                        <a
                            href="${e(escalationUrl)}"
                            data-escalate-complaint="1"
                            class="${escalationButtonClass}"
                        >
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">campaign</span>
                            <span>Ajukan Pengaduan</span>
                        </a>
                    </div>
                </div>
            </section>
        `;
    }

    function appendMessageBubble(msg) {
        const isUser = msg.sender === 'user';
        const article = document.createElement('article');
        const trimmedContent = String(msg.content || '').trim();
        const bubbleSizing = getBubbleSizingClasses(trimmedContent);

        if (isUser) {
            lastUserChronology = trimmedContent;
            article.className = 'chat-bubble user-bubble flex flex-col items-end gap-1.5 pl-3 min-[641px]:pl-12';
            article.innerHTML = `
                <div class="flex items-center gap-2 pr-1">
                    <span class="text-xs font-semibold text-[#414754]">Kronologi Warga</span>
                    <div class="w-6 h-6 rounded-full bg-[#D6E3FF] flex items-center justify-center text-[#00468D]">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">person</span>
                    </div>
                </div>
                <div class="${bubbleSizing} bg-[#005AB3] text-white rounded-2xl rounded-tr-sm shadow-[0_8px_20px_rgba(0,90,179,0.20)] text-xs min-[380px]:text-sm leading-relaxed whitespace-pre-wrap break-words">${e(trimmedContent)}</div>
                <span class="text-[11px] text-[#414754] pr-1">
                    Terkirim ${e(formatTime(msg.created_at))} WIB
                </span>
            `;
        } else {
            article.className = 'chat-bubble assistant-bubble flex flex-col items-start gap-2 pr-1 min-[641px]:pr-6';
            const isOutOfScope = msg.retrieval_meta?.status === 'out_of_scope';
            const statusBadge = isOutOfScope
                ? '<span class="px-2.5 py-0.5 bg-[#FEF3C7] text-[#78350F] border border-[#FDE68A] rounded-full text-[11px] font-bold">Diarahkan ke Petugas</span>'
                : '<span class="px-2.5 py-0.5 bg-[#9CF2E8] text-[#00504A] rounded-full text-[11px] font-bold">Penalaran Peraturan Terverifikasi</span>';

            article.innerHTML = `
                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                    <div class="w-7 h-7 rounded-xl bg-[#005AB3] text-white flex items-center justify-center shadow-sm shrink-0">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">smart_toy</span>
                    </div>
                    <span class="text-xs min-[641px]:text-sm font-bold text-[#171C21]">Asisten Hukum Virtual BHIMA</span>
                    ${statusBadge}
                    <span class="text-[11px] text-[#414754]">${e(formatTime(msg.created_at))} WIB</span>
                </div>
                <div class="${bubbleSizing} bg-white text-[#171C21] rounded-2xl rounded-tl-sm border border-[#DEE3EA] shadow-[0_6px_18px_rgba(20,30,80,0.04)] space-y-2 text-xs min-[380px]:text-sm leading-relaxed stitch-answer-body">${formatAssistantBody(trimmedContent)}</div>
                ${renderLegalAnalysisCard(msg.legal_analysis, msg)}
                ${renderSourcesSection(msg.citations, msg.retrieval_meta)}
            `;

            const copyBtn = article.querySelector('[data-copy-answer]');
            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    navigator.clipboard?.writeText(String(msg.content || '')).catch(() => {});
                    copyBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] text-[#0F766E]">check</span>';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<span class="material-symbols-outlined text-[20px]">content_copy</span>';
                    }, 1800);
                });
            }

            if (Array.isArray(msg.citations) && msg.citations.length > 0) {
                updateRightSidebarReferences(msg.citations);
            }
        }

        messagesEl.appendChild(article);
        return article;
    }

    function scrollToBubbleTop(targetEl, behavior = 'smooth') {
        if (!messagesEl || !targetEl) return;
        const containerRect = messagesEl.getBoundingClientRect();
        const targetRect = targetEl.getBoundingClientRect();
        const nextScrollTop = Math.max(0, messagesEl.scrollTop + (targetRect.top - containerRect.top) - 12);
        messagesEl.scrollTo({
            top: nextScrollTop,
            behavior,
        });
    }

    async function loadHistory() {
        try {
            const res = await fetch('/api/konsultasi/riwayat', {
                headers: {
                    Accept: 'application/json',
                    'X-Consultation-Session': sessionId,
                },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.session_id) {
                sessionId = data.session_id;
                window.sessionStorage.setItem('bhima_consult_session', sessionId);
                updateSessionBadge(sessionId);
            }
            if (data.knowledge_stats?.documents) {
                const badgeEl = container.querySelector('#kb-badge-text');
                const docsStatEl = container.querySelector('#stat-total-docs');
                const chunksStatEl = container.querySelector('#stat-total-chunks');
                if (badgeEl) badgeEl.textContent = `${data.knowledge_stats.documents} Dokumen JDIH & SOP`;
                if (docsStatEl) docsStatEl.textContent = `${data.knowledge_stats.documents} Dokumen`;
                if (chunksStatEl) chunksStatEl.textContent = `${data.knowledge_stats.chunks} Chunk`;
            }
            if (Array.isArray(data.messages) && data.messages.length > 0) {
                let lastUserBubble = null;
                data.messages.forEach((msg) => {
                    const bubble = appendMessageBubble(msg);
                    if (msg.sender === 'user') {
                        lastUserBubble = bubble;
                    }
                });
                if (lastUserBubble) {
                    scrollToBubbleTop(lastUserBubble, 'auto');
                }
            }
        } catch {
            // Abaikan kegagalan pemuatan awal secara senyap
        }
    }

    formEl.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (busy) return;

        const chronology = inputEl.value.trim();
        showError('');

        if (chronology.length < 10) {
            showError('Mohon tuliskan kronologi permasalahan hukum minimal 10 karakter.');
            inputEl.focus();
            return;
        }

        busy = true;
        sendBtn.disabled = true;
        loadingEl.hidden = false;

        try {
            const res = await fetch('/api/konsultasi/pesan', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Consultation-Session': sessionId,
                },
                body: JSON.stringify({
                    chronology,
                    session_id: sessionId,
                }),
            });

            const payload = await res.json().catch(() => ({}));
            if (!res.ok) {
                const errMsg =
                    Object.values(payload.errors || {}).flat().join(' ') ||
                    payload.message ||
                    `Permintaan gagal diproses (${res.status}).`;
                showError(errMsg);
                return;
            }

            if (payload.session_id) {
                sessionId = payload.session_id;
                window.sessionStorage.setItem('bhima_consult_session', sessionId);
                updateSessionBadge(sessionId);
            }

            inputEl.value = '';
            updateCounter();

            let userBubbleEl = null;
            if (payload.user_message) {
                userBubbleEl = appendMessageBubble(payload.user_message);
            }
            if (payload.assistant_message) {
                appendMessageBubble(payload.assistant_message);
            }
            if (userBubbleEl) {
                scrollToBubbleTop(userBubbleEl, 'smooth');
            }
        } catch {
            showError('Tidak dapat terhubung ke server konsultasi BHIMA. Periksa koneksi Anda dan coba kembali.');
        } finally {
            busy = false;
            sendBtn.disabled = false;
            loadingEl.hidden = true;
        }
    });

    inputEl.addEventListener('input', updateCounter);
    inputEl.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
            event.preventDefault();
            formEl.requestSubmit();
        }
    });

    container.querySelectorAll('[data-scenario]').forEach((btn) => {
        btn.addEventListener('click', () => {
            inputEl.value = btn.dataset.scenario || '';
            updateCounter();
            inputEl.focus();
        });
    });

    resetBtn?.addEventListener('click', async () => {
        if (busy) return;
        try {
            const res = await fetch('/api/konsultasi/reset', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await res.json().catch(() => ({}));
            if (data.session_id) {
                sessionId = data.session_id;
                window.sessionStorage.setItem('bhima_consult_session', sessionId);
                updateSessionBadge(sessionId);
            }
            messagesEl.querySelectorAll('.chat-bubble:not(.welcome-bubble)').forEach((el) => el.remove());
            if (jdihSidebarList && defaultSidebarHtml) {
                jdihSidebarList.innerHTML = defaultSidebarHtml;
            }
            showError('');
            inputEl.value = '';
            updateCounter();
            inputEl.focus();
        } catch {
            showError('Gagal mengatur ulang sesi percakapan.');
        }
    });

    const initialQuery = new URLSearchParams(window.location.search).get('q');
    if (initialQuery && inputEl) {
        inputEl.value = initialQuery.slice(0, maxLen);
    }

    updateCounter();
    loadHistory();
}

function initBerandaApp(container) {
    const searchForm = container.querySelector('#bhimaSearchForm');
    const searchInput = container.querySelector('#bhimaSearchInput');
    const searchSubmitBtn = container.querySelector('#bhimaSearchSubmitBtn');
    const searchIdle = container.querySelector('#beranda-search-idle');
    const searchLoading = container.querySelector('#beranda-search-loading');
    const searchError = container.querySelector('#beranda-search-error');
    const searchEmpty = container.querySelector('#beranda-search-empty');
    const searchResults = container.querySelector('#beranda-search-results');
    const searchSummary = container.querySelector('#beranda-search-summary');
    const searchList = container.querySelector('#beranda-search-list');
    const searchClose = container.querySelector('#beranda-search-close');

    function setSearchState(state, message = '') {
        if (searchIdle) searchIdle.hidden = state !== 'idle';
        if (searchLoading) searchLoading.hidden = state !== 'loading';
        if (searchError) {
            searchError.hidden = state !== 'error';
            searchError.textContent = message;
        }
        if (searchEmpty) searchEmpty.hidden = state !== 'empty';
        if (searchResults) searchResults.hidden = state !== 'results';
    }

    async function runPortalSearch(rawQuery) {
        const query = String(rawQuery || '').trim();
        setSearchState('loading');
        if (searchSubmitBtn) searchSubmitBtn.disabled = true;

        try {
            const url = `/api/beranda/cari?q=${encodeURIComponent(query || 'semua')}`;
            const res = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                setSearchState(
                    'error',
                    data.message || 'Pencarian dokumen belum dapat dimuat saat ini. Silakan coba kembali.'
                );
                return;
            }

            const items = Array.isArray(data.results) ? data.results : [];
            if (items.length === 0) {
                setSearchState('empty');
                return;
            }

            if (searchSummary) {
                searchSummary.textContent = query
                    ? `Ditemukan ${items.length} Dokumen Terkait untuk "${query}"`
                    : `Menampilkan ${items.length} Dokumen Rujukan Utama JDIH & SOP`;
            }

            if (searchList) {
                searchList.innerHTML = items
                    .map((item) => {
                        const isSop = item.category === 'SOP';
                        const badgeClass = isSop
                            ? 'bg-[#D6E3FF] text-[#00468D] border-[#AEC6FF]'
                            : 'bg-[#9CF2E8] text-[#00504A] border-[#7FD6CD]';
                        const consultHref = `/konsultasi?q=${encodeURIComponent(
                            query || `${item.document_name} (${item.document_number})`
                        )}`;

                        return `
                            <article class="p-4 rounded-xl bg-[#EFF4FC]/90 border border-[#C1C6D6]/50 flex flex-col justify-between gap-3">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border ${badgeClass}">
                                            ${e(item.category || 'JDIH')} • ${e(item.document_number || '')}
                                        </span>
                                        <span class="text-xs font-semibold text-[#414754]">${e(item.topic || '')}</span>
                                    </div>
                                    <h3 class="font-headline text-base sm:text-lg font-bold text-[#171C21] leading-snug">
                                        ${e(item.document_name || '')}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-[#414754] leading-relaxed">
                                        ${e(item.excerpt || '')}
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-[#C1C6D6]/40 flex flex-wrap items-center justify-between gap-2">
                                    <a
                                        href="${e(item.url || '#')}"
                                        class="min-h-[44px] px-3 py-2 rounded-lg bg-white hover:bg-[#DFEAFA] text-[#005AB3] border border-[#C1C6D6]/60 text-xs font-bold inline-flex items-center gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">menu_book</span>
                                        <span>Baca Pasal (${e(item.article_label || 'Lengkap')})</span>
                                    </a>
                                    <a
                                        href="${e(consultHref)}"
                                        class="min-h-[44px] px-3 py-2 rounded-lg bg-[#005AB3] hover:bg-[#00468D] text-white text-xs font-bold inline-flex items-center gap-1.5"
                                    >
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">smart_toy</span>
                                        <span>Tanya Asisten</span>
                                    </a>
                                </div>
                            </article>
                        `;
                    })
                    .join('');
            }

            setSearchState('results');
        } catch {
            setSearchState(
                'error',
                'Tidak dapat terhubung ke layanan pencarian dokumen. Periksa koneksi Anda atau buka Ruang Konsultasi.'
            );
        } finally {
            if (searchSubmitBtn) searchSubmitBtn.disabled = false;
        }
    }

    searchForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const val = searchInput ? searchInput.value.trim() : '';
        if (!val) {
            setSearchState('error', 'Silakan ketik kata kunci layanan, SOP, atau peraturan terlebih dahulu.');
            searchInput?.focus();
            return;
        }
        runPortalSearch(val);
    });

    container.querySelectorAll('[data-search-tag]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const tag = btn.getAttribute('data-search-tag') || '';
            if (searchInput) {
                searchInput.value = tag;
            }
            runPortalSearch(tag);
        });
    });

    container.querySelectorAll('[data-show-all-docs]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            if (searchInput) {
                searchInput.value = '';
            }
            document.getElementById('pencarian-regulasi')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            runPortalSearch('semua');
        });
    });

    searchClose?.addEventListener('click', () => {
        setSearchState('idle');
    });

    const ticketForm = container.querySelector('#beranda-ticket-form');
    const ticketInput = container.querySelector('#ticketCodeInput');
    const ticketBtn = container.querySelector('#ticketTrackBtn');
    const ticketStatusMsg = container.querySelector('#ticketStatusMsg');

    ticketForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!ticketStatusMsg) return;

        const val = ticketInput ? ticketInput.value.trim().toUpperCase() : '';
        if (!val) {
            ticketStatusMsg.textContent = 'Harap isi nomor tiket terlebih dahulu (contoh: BHM-2026-0001).';
            ticketStatusMsg.className = 'text-xs font-bold text-[#991B1B] block mt-1.5';
            ticketInput?.focus();
            return;
        }

        ticketStatusMsg.textContent = `Memeriksa status tiket ${val}...`;
        ticketStatusMsg.className = 'text-xs font-semibold text-[#0F766E] block mt-1.5';
        if (ticketBtn) ticketBtn.disabled = true;

        try {
            const res = await fetch(`/api/beranda/lacak?tiket=${encodeURIComponent(val)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.found) {
                ticketStatusMsg.innerHTML = `${e(data.message || 'Tiket tidak ditemukan.')} <a href="/lacak?tiket=${encodeURIComponent(val)}" class="underline font-bold text-[#0F766E]">Buka halaman Lacak Tiket</a>`;
                ticketStatusMsg.className = 'text-xs font-semibold text-[#991B1B] block mt-1.5';
                return;
            }

            ticketStatusMsg.innerHTML = `Tiket <strong>${e(data.ticket)}</strong> (${e(data.category)}): Status <strong>${e(data.status)}</strong>. <a href="${e(data.detail_url)}" class="underline font-bold text-[#0F766E]">Lihat detail lengkap</a>`;
            ticketStatusMsg.className = 'text-xs font-semibold text-[#166534] block mt-1.5';
        } catch {
            ticketStatusMsg.textContent = 'Gagal memeriksa tiket secara langsung. Silakan coba lagi.';
            ticketStatusMsg.className = 'text-xs font-semibold text-[#991B1B] block mt-1.5';
        } finally {
            if (ticketBtn) ticketBtn.disabled = false;
        }
    });
}
