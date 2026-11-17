import { escape as e } from '../dashboard/ui';

const root = document.getElementById('consultation-app');
if (root) {
    initConsultationApp(root);
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
    const mobileMenuBtn = container.querySelector('#mobile-menu-btn');
    const mobileMenuIcon = container.querySelector('#mobile-menu-icon');
    const mobileNavClose = container.querySelector('#mobile-nav-close');
    const mobileNavBackdrop = container.querySelector('#mobile-nav-backdrop');
    const consultSidebar = container.querySelector('#consult-sidebar');

    let busy = false;

    function setMobileDrawerOpen(isOpen) {
        if (!consultSidebar) return;
        if (isOpen) {
            consultSidebar.classList.remove('-translate-x-full');
            consultSidebar.classList.add('translate-x-0');
            if (mobileNavBackdrop) mobileNavBackdrop.hidden = false;
            if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'true');
            if (mobileMenuIcon) mobileMenuIcon.textContent = 'close';
        } else {
            consultSidebar.classList.add('-translate-x-full');
            consultSidebar.classList.remove('translate-x-0');
            if (mobileNavBackdrop) mobileNavBackdrop.hidden = true;
            if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'false');
            if (mobileMenuIcon) mobileMenuIcon.textContent = 'menu';
        }
    }

    mobileMenuBtn?.addEventListener('click', () => {
        const isOpen = consultSidebar && !consultSidebar.classList.contains('-translate-x-full');
        setMobileDrawerOpen(!isOpen);
    });
    mobileNavClose?.addEventListener('click', () => setMobileDrawerOpen(false));
    mobileNavBackdrop?.addEventListener('click', () => setMobileDrawerOpen(false));
    container.querySelectorAll('[data-drawer-link]').forEach((link) => {
        link.addEventListener('click', () => setMobileDrawerOpen(false));
    });
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMobileDrawerOpen(false);
        }
    });

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
        const lines = String(rawText || '').split('\n');
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
                    ? 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]'
                    : 'bg-[#FEE2E2] text-[#991B1B] border-[#FECACA]';
                const iconName = isSop ? 'description' : 'picture_as_pdf';
                const scorePct = (Number(c.score || 0) * 100).toFixed(1);

                return `
                    <div class="p-3.5 bg-[#F0FAF8] rounded-xl border border-[#CCECE6] hover:border-[#0F766E] transition-all flex flex-col justify-between gap-2.5">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg ${iconBoxClass} flex items-center justify-center shrink-0 mt-0.5 border">
                                <span class="material-symbols-outlined text-[20px]">${iconName}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs text-[#0F766E] font-bold block uppercase tracking-wider">
                                    ${e(c.document_number || c.category || 'JDIH')} (${e(c.document_year || '-')})
                                </span>
                                <h3 class="text-sm text-text-dark font-semibold truncate" title="${e(c.document_name)}">
                                    ${e(c.document_name)}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-1 text-text-muted text-[11px] sm:text-xs flex-wrap">
                                    <span class="px-2 py-0.5 bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] rounded font-bold">
                                        ${e(c.article_label || 'Pasal Terkait')}
                                    </span>
                                    <span>•</span>
                                    <span>Kemiripan ${scorePct}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <a
                                href="${e(c.source_url)}"
                                target="_blank"
                                rel="noopener"
                                class="flex-1 py-1.5 px-2 rounded-lg bg-white border border-[#CCECE6] hover:bg-[#E6F5F2] text-[#0F766E] font-semibold text-xs flex items-center justify-center gap-1 transition-colors"
                            >
                                <span class="material-symbols-outlined text-[16px]">visibility</span> Baca Pasal
                            </a>
                            <a
                                href="${e(c.source_url)}"
                                target="_blank"
                                rel="noopener"
                                class="py-1.5 px-3 rounded-lg bg-[#0F766E] hover:bg-[#115E59] text-white font-semibold text-xs flex items-center justify-center gap-1 shadow-sm transition-colors"
                            >
                                <span class="material-symbols-outlined text-[16px]">open_in_new</span> Sumber
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
                <details class="w-fit max-w-[95%] sm:max-w-xl bg-white rounded-xl border border-[#FDE68A] shadow-sm overflow-hidden mt-1.5">
                    <summary class="bg-[#FEF3C7] text-[#92400E] px-3.5 sm:px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 sm:gap-3 cursor-pointer select-none">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-[#D97706] shrink-0">warning</span>
                            <span class="text-[11px] sm:text-xs font-bold text-[#78350F]">
                                Sumber Rujukan (0 Pasal di Atas Ambang Kemiripan)
                            </span>
                        </div>
                        <span class="px-2 sm:px-2.5 py-0.5 rounded-full bg-white text-[#92400E] border border-[#FDE68A] text-[10px] sm:text-[11px] font-bold">
                            Di Luar Cakupan ▾
                        </span>
                    </summary>
                    <div class="p-3.5 sm:p-4 bg-white text-xs text-text-muted leading-relaxed">
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
                const badgeBg = isSop ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#E0F2FE] text-[#0369A1]';
                const badgeLabel = isSop ? 'SOP Layanan' : 'Dokumen JDIH';

                return `
                    <div class="p-3 sm:p-3.5 bg-[#F0FAF8] rounded-xl border border-[#CCECE6] space-y-2">
                        <div class="flex items-center justify-between flex-wrap gap-1.5 sm:gap-2">
                            <span class="text-xs sm:text-sm font-bold text-[#0F766E] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[#0F766E] text-[17px] sm:text-[18px] shrink-0">${iconName}</span>
                                <span>${e(c.document_number || c.document_key)} • Tahun ${e(c.document_year || '-')}</span>
                            </span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 ${badgeBg} rounded-md text-[10px] sm:text-[11px] font-semibold">${badgeLabel}</span>
                                <span class="px-2 py-0.5 bg-white border border-[#CCECE6] text-[#0F766E] rounded-md text-[10px] sm:text-[11px] font-bold">
                                    Relevansi ${scorePct}%
                                </span>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-text-dark font-semibold">
                            ${e(c.document_name)}
                        </p>
                        <div class="bg-white p-2.5 rounded-lg border border-[#E2E8F0] space-y-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-[11px] text-[#0F766E] uppercase font-bold tracking-wide">
                                    Kutipan ${e(c.article_label)}:
                                </span>
                                <a
                                    href="${e(c.source_url)}"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-[11px] font-bold text-[#0F766E] hover:text-[#115E59] hover:underline flex items-center gap-1"
                                >
                                    <span>Buka Sumber (${e(c.document_key)})</span>
                                    <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                                </a>
                            </div>
                            <p class="text-xs text-text-body italic leading-relaxed">"${e(String(c.excerpt || '').trim())}"</p>
                        </div>
                    </div>
                `;
            })
            .join('');

        return `
            <details class="w-full max-w-2xl bg-white rounded-2xl border border-[#CCECE6] shadow-sm overflow-hidden mt-1.5">
                <summary class="bg-[#0F766E] text-white px-3.5 sm:px-4 py-2.5 sm:py-3 flex flex-wrap items-center justify-between gap-2.5 sm:gap-3 cursor-pointer select-none">
                    <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                        <span class="material-symbols-outlined text-[20px] sm:text-[22px] text-[#9cf2e8] shrink-0">balance</span>
                        <div class="min-w-0">
                            <h3 class="font-headline text-xs sm:text-sm md:text-base font-bold tracking-tight text-white">
                                Sumber Rujukan &amp; Analisis Pasal (${count} Dokumen)
                            </h3>
                            <p class="text-[10px] sm:text-[11px] text-[#CCECE6]">
                                Klik untuk membuka kutipan pasal JDIH &amp; SOP Layanan
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-auto">
                        <span class="px-2 sm:px-2.5 py-0.5 rounded-full bg-[#115E59] text-[#9cf2e8] border border-[#2dd4bf]/40 text-[10px] sm:text-[11px] font-bold tracking-wide">
                            Relevansi ${topScorePct}%
                        </span>
                        <span class="material-symbols-outlined text-[18px] text-[#9cf2e8] transition-transform summary-chevron">expand_more</span>
                    </div>
                </summary>
                <div class="p-3 sm:p-4 md:p-5 space-y-3 sm:space-y-3.5 bg-white">
                    ${itemsHtml}

                    <!-- Official Advisory Disclaimer -->
                    <div class="p-2.5 sm:p-3 bg-[#F3F4F6] border border-[#E5E7EB] rounded-xl flex items-start gap-2">
                        <span class="material-symbols-outlined text-[#4B5563] text-[17px] sm:text-[18px] shrink-0 mt-0.5">info</span>
                        <p class="text-[11px] sm:text-xs text-text-muted leading-relaxed">
                            <strong class="text-text-dark font-semibold">Pemberitahuan Resmi:</strong>
                            Hasil analisis ini diproses secara komputasi berdasarkan regulasi terpublikasi di JDIH resmi dan SOP layanan publik. Informasi ini bersifat edukasi hukum awal dan bukan nasihat hukum resmi.
                        </p>
                    </div>

                    <!-- Action Button Matrix -->
                    <div class="pt-1 flex flex-col sm:flex-row sm:flex-wrap gap-2 items-stretch sm:items-center">
                        <a
                            class="flex-1 flex items-center justify-center gap-2 bg-[#0F766E] hover:bg-[#115E59] text-white py-2.5 px-3.5 sm:px-4 rounded-xl font-bold text-xs sm:text-sm shadow-sm hover:shadow transition-all active:scale-[0.99] text-center"
                            href="${e(primaryUrl)}"
                            target="_blank"
                            rel="noopener"
                        >
                            <span class="material-symbols-outlined text-[17px] sm:text-[18px]">menu_book</span>
                            <span>Lihat Dokumen Rujukan Lengkap</span>
                        </a>
                        <div class="flex items-center gap-2">
                            <a
                                class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 bg-[#E6F5F2] hover:bg-[#CCECE6] text-[#0F766E] border border-[#CCECE6] py-2.5 px-3.5 rounded-xl font-bold text-xs sm:text-sm transition-all"
                                href="#posbakum-panel"
                            >
                                <span class="material-symbols-outlined text-[17px] sm:text-[18px]">support_agent</span>
                                <span>Posbakum</span>
                            </a>
                            <button
                                type="button"
                                data-copy-answer="1"
                                class="p-2.5 rounded-xl bg-white border border-[#E2E8F0] text-text-muted hover:text-[#0F766E] hover:border-[#CCECE6] hover:bg-[#F0FAF8] transition-colors cursor-pointer shrink-0"
                                title="Salin Ringkasan Jawaban"
                            >
                                <span class="material-symbols-outlined text-[18px]">content_copy</span>
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
            ? `<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                ${rujukan
                    .map(
                        (r) => `
                        <a
                            href="${e(r.tautan || '#jdih-reference-panel')}"
                            target="_blank"
                            rel="noopener"
                            class="p-2.5 rounded-xl bg-[#F0FAF8] hover:bg-[#E6F5F2] border border-[#CCECE6] flex items-start justify-between gap-2 transition-colors no-underline group"
                        >
                            <div class="min-w-0">
                                <span class="text-[11px] font-bold text-[#0F766E] block uppercase tracking-wide">${e(r.nomor_tahun || '-')}</span>
                                <span class="text-xs font-semibold text-text-dark block truncate group-hover:text-[#0F766E]" title="${e(r.dokumen || '')}">${e(r.dokumen || '')}</span>
                            </div>
                            <span class="material-symbols-outlined text-[16px] text-[#0F766E] shrink-0 mt-0.5">open_in_new</span>
                        </a>
                    `
                    )
                    .join('')}
               </div>`
            : `<p class="text-xs text-text-muted italic bg-[#F9FAFB] p-2.5 rounded-xl border border-[#E5E7EB]">Belum ada rujukan undang-undang yang cocok di atas ambang kemiripan.</p>`;

        const pasalHtml = pasalRelevan.length
            ? `<div class="space-y-2.5">
                ${pasalRelevan
                    .map(
                        (p) => `
                        <div class="p-3 rounded-xl bg-[#F0FAF8] border border-[#CCECE6] space-y-1.5">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-xs font-bold text-[#0F766E] flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">gavel</span>
                                    ${e(p.pasal || '')}
                                </span>
                                <span class="text-[11px] font-semibold text-text-muted">${e(p.dokumen || '')}</span>
                            </div>
                            <p class="text-xs text-text-dark leading-relaxed">${e(p.penjelasan_awam || '')}</p>
                            <p class="text-[11px] text-text-muted leading-relaxed border-t border-[#CCECE6]/60 pt-1">
                                <strong class="text-[#0F766E]">Keterkaitan Kronologi:</strong> ${e(p.alasan_terkait_kronologi || '')}
                            </p>
                        </div>
                    `
                    )
                    .join('')}
               </div>`
            : `<p class="text-xs text-text-muted italic bg-[#F9FAFB] p-2.5 rounded-xl border border-[#E5E7EB]">Tidak ditemukan pasal yang berpotensi terkait pada konteks dokumen.</p>`;

        const sanksiBadgeMap = {
            pidana: 'bg-[#FEE2E2] text-[#991B1B] border-[#FECACA]',
            administratif: 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]',
            perdata: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]',
            lainnya: 'bg-[#F3F4F6] text-[#374151] border-[#E5E7EB]',
        };

        const sanksiHtml = ancamanSanksi.length
            ? `<div class="space-y-2">
                ${ancamanSanksi
                    .map((s) => {
                        const jenisKey = String(s.jenis || 'lainnya').toLowerCase();
                        const badgeCls = sanksiBadgeMap[jenisKey] || sanksiBadgeMap.lainnya;
                        return `
                            <div class="p-3 rounded-xl bg-white border border-[#E2E8F0] space-y-1">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-text-dark">${e(s.pasal || '')} <span class="font-normal text-text-muted">• ${e(s.dokumen || '')}</span></span>
                                    <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase ${badgeCls}">
                                        ${e(jenisKey)}
                                    </span>
                                </div>
                                <p class="text-xs text-text-body leading-relaxed">${e(s.bunyi_sanksi_ringkas || '')}</p>
                            </div>
                        `;
                    })
                    .join('')}
               </div>`
            : `<p class="text-xs text-text-muted italic bg-[#F9FAFB] p-2.5 rounded-xl border border-[#E5E7EB]">Tidak tercantum dalam potongan pasal yang ditemukan.</p>`;

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
                rujukan.map((r) => `${r.dokumen} (${r.nomor_tahun}) — ${r.tautan}`).join('\n')
            );
        }
        const escalationUrl = `/pengaduan/buat?${params.toString()}`;

        const escalationButtonClass = perluEskalasi
            ? 'w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#F59E0B] hover:bg-[#D97706] text-[#111827] font-extrabold text-xs sm:text-sm shadow-md ring-2 ring-[#F59E0B]/30 transition-all no-underline'
            : 'w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-[#0F766E] hover:bg-[#115E59] text-white font-bold text-xs sm:text-sm shadow-sm transition-all no-underline';

        const escalationBadge = perluEskalasi
            ? `<span class="px-2.5 py-0.5 rounded-full bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A] text-[10px] sm:text-[11px] font-bold">Disarankan Eskalasi</span>`
            : `<span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] text-[10px] sm:text-[11px] font-bold">Terkendali</span>`;

        return `
            <section
                class="legal-analysis-card w-full max-w-2xl bg-white rounded-2xl border-2 ${perluEskalasi ? 'border-[#F59E0B]/70' : 'border-[#CCECE6]'} shadow-sm overflow-hidden mt-1.5"
                aria-label="Hasil Analisis Pasal dan Estimasi Ancaman Sanksi"
            >
                <div class="bg-[#115E59] text-white px-3.5 sm:px-4 py-2.5 sm:py-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="material-symbols-outlined text-[20px] text-[#9cf2e8] shrink-0">policy</span>
                        <h3 class="font-headline text-xs sm:text-sm md:text-base font-bold text-white truncate">
                            Analisis Pasal &amp; Estimasi Ancaman Sanksi
                        </h3>
                    </div>
                    ${escalationBadge}
                </div>

                <div class="p-3.5 sm:p-4 md:p-5 space-y-4">
                    ${
                        isError
                            ? `<div role="alert" class="p-3 rounded-xl bg-[#FEF3C7] border border-[#FDE68A] text-xs text-[#92400E] font-medium">${e(catatan)}</div>`
                            : ''
                    }

                    <!-- 1. Rujukan Undang-Undang -->
                    <div class="space-y-1.5">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0F766E] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">menu_book</span>
                            Rujukan Undang-Undang &amp; Regulasi (${rujukan.length})
                        </h4>
                        ${rujukanHtml}
                    </div>

                    <!-- 2. Pasal Terkait -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0F766E] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">fact_check</span>
                                Pasal yang Berpotensi Terkait (${pasalRelevan.length})
                            </h4>
                            <span class="text-[10px] font-semibold text-text-muted">Berdasarkan kronologi, bukan putusan pelanggaran</span>
                        </div>
                        ${pasalHtml}
                    </div>

                    <!-- 3. Estimasi Ancaman Sanksi -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#0F766E] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">gavel</span>
                                Estimasi Ancaman Sanksi
                            </h4>
                            <span class="px-2 py-0.5 rounded-md bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A] text-[10px] font-bold">
                                ${e(sanctionLabel)}
                            </span>
                        </div>
                        ${sanksiHtml}
                    </div>

                    ${
                        !isError && catatan
                            ? `<div class="p-2.5 rounded-xl bg-[#F0FAF8] border border-[#CCECE6] text-[11px] sm:text-xs text-text-body leading-relaxed">${e(catatan)}</div>`
                            : ''
                    }

                    <!-- Disclaimer Singkat -->
                    <p class="text-[11px] text-text-muted leading-relaxed">
                        <strong>Disclaimer:</strong> ${e(disclaimer)}
                    </p>

                    <!-- Tombol Eskalasi ke Formulir Pengaduan -->
                    <div class="pt-2 border-t border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <span class="text-[11px] sm:text-xs font-semibold ${perluEskalasi ? 'text-[#92400E]' : 'text-text-muted'}">
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
                            <span class="material-symbols-outlined text-[18px]">campaign</span>
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
            article.className = 'chat-bubble user-bubble flex flex-col items-end gap-1 pl-3 sm:pl-12 md:pl-16';
            article.innerHTML = `
                <div class="flex items-center gap-1.5 mb-0.5">
                    <span class="text-[11px] sm:text-xs font-medium text-text-muted">Kronologi Warga</span>
                    <div class="w-5 h-5 rounded-full bg-[#CCECE6] flex items-center justify-center text-[#115E59]">
                        <span class="material-symbols-outlined text-[13px]">person</span>
                    </div>
                </div>
                <div class="${bubbleSizing} bg-[#115E59] text-white rounded-2xl rounded-tr-sm shadow-sm text-xs sm:text-sm leading-relaxed whitespace-pre-wrap break-words">${e(trimmedContent)}</div>
                <span class="text-[10px] sm:text-[11px] text-text-muted pr-1">
                    Terkirim ${e(formatTime(msg.created_at))} WIB
                </span>
            `;
        } else {
            article.className = 'chat-bubble assistant-bubble flex flex-col items-start gap-2 pr-1 sm:pr-6 md:pr-8';
            const isOutOfScope = msg.retrieval_meta?.status === 'out_of_scope';
            const statusBadge = isOutOfScope
                ? '<span class="px-2 sm:px-2.5 py-0.5 bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A] rounded-full text-[10px] sm:text-xs font-bold">Diarahkan ke Petugas</span>'
                : '<span class="px-2 sm:px-2.5 py-0.5 bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0] rounded-full text-[10px] sm:text-xs font-bold">Penalaran Peraturan Terverifikasi</span>';

            article.innerHTML = `
                <div class="flex items-center gap-1.5 sm:gap-2 mb-0.5 flex-wrap">
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-[#0F766E] text-white flex items-center justify-center shadow-sm shrink-0">
                        <span class="material-symbols-outlined text-[15px] sm:text-[17px]">smart_toy</span>
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-text-dark">Asisten Hukum Virtual BHIMA</span>
                    ${statusBadge}
                    <span class="text-[11px] sm:text-xs text-text-muted">${e(formatTime(msg.created_at))} WIB</span>
                </div>
                <div class="${bubbleSizing} bg-white text-text-body rounded-2xl rounded-tl-sm border border-[#E2E8F0] shadow-sm space-y-2 text-xs sm:text-sm leading-relaxed stitch-answer-body">${formatAssistantBody(trimmedContent)}</div>
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

    updateCounter();
    loadHistory();
}
