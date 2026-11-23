/**
 * Progressive Motion & Scroll-Driven Fallback Module (modern-web-guidance compliant)
 *
 * - Native CSS scroll-driven animations run automatically in supporting browsers.
 * - Fallback IntersectionObserver runs ONLY when native `animation-timeline: view()` +
 *   `animation-range: entry` is unsupported AND `prefers-reduced-motion: no-preference`.
 * - Fallback header scroll state runs ONLY when native `animation-timeline: scroll()` +
 *   `animation-range: 0% 100%` is unsupported AND `prefers-reduced-motion: no-preference`.
 * - Helper `hideDiscreteElement` / `showDiscreteElement` provides Firefox bug 1882408 exit fallback.
 */

import { canTransitionDisplay } from './confirm-dialog';

export async function hideDiscreteElement(el) {
    if (!el || el.hidden) return;

    if (canTransitionDisplay()) {
        el.hidden = true;
        return;
    }

    el.setAttribute('data-closing', '');
    const animations = typeof el.getAnimations === 'function' ? el.getAnimations() : [];
    if (animations.length > 0) {
        await Promise.race([
            Promise.allSettled(animations.map((a) => a.finished)),
            new Promise((resolve) => setTimeout(resolve, 450)),
        ]);
    }

    if (el.hasAttribute('data-closing')) {
        el.removeAttribute('data-closing');
        el.hidden = true;
    }
}

export function showDiscreteElement(el) {
    if (!el) return;
    el.removeAttribute('data-closing');
    el.hidden = false;
}

function initScrollRevealFallback() {
    const prefersMotion = window.matchMedia?.('(prefers-reduced-motion: no-preference)')?.matches;
    if (!prefersMotion) return;

    const supportsNativeViewTimeline = Boolean(
        window.CSS?.supports?.('(animation-timeline: view()) and (animation-range: entry)')
    );
    if (supportsNativeViewTimeline) return;

    const targets = Array.from(document.querySelectorAll('.reveal-on-scroll'));
    if (targets.length === 0 || typeof IntersectionObserver === 'undefined') return;

    document.documentElement.classList.add('motion-io-ready');

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            }
        },
        {
            threshold: 0.08,
            rootMargin: '0px 0px -32px 0px',
        }
    );

    targets.forEach((el) => {
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight * 0.92) {
            el.classList.add('is-visible');
        } else {
            observer.observe(el);
        }
    });
}

function initHeaderScrollFallback() {
    const prefersMotion = window.matchMedia?.('(prefers-reduced-motion: no-preference)')?.matches;
    if (!prefersMotion) return;

    const supportsNativeScrollTimeline = Boolean(
        window.CSS?.supports?.('(animation-timeline: scroll()) and (animation-range: 0% 100%)')
    );
    if (supportsNativeScrollTimeline) return;

    const headerPill = document.querySelector('.bhima-header-pill');
    if (!headerPill) return;

    let ticking = false;
    const syncHeader = () => {
        ticking = false;
        headerPill.classList.toggle('is-scrolled', window.scrollY > 28);
    };

    window.addEventListener(
        'scroll',
        () => {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(syncHeader);
            }
        },
        { passive: true }
    );

    syncHeader();
}

export function initBhimaMotion() {
    initScrollRevealFallback();
    initHeaderScrollFallback();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBhimaMotion);
} else {
    initBhimaMotion();
}
