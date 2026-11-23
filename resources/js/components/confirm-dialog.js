/**
 * Accessible Native <dialog> Enhancement Layer (modern-web-guidance compliant)
 *
 * Covers:
 * - Runtime probe for discrete `display` transitions (handles Firefox bug 1882408)
 * - Top-layer exit animation fallback via `[data-closing]` + `getAnimations()`
 * - Safari fallback for `closedby="any"` light-dismiss using pointerdown + pointerup bounds
 * - Feature-detected Invoker Commands (`command` / `commandfor`) fallback on `HTMLButtonElement.prototype`
 * - Mandatory `dialog.returnValue = ''` reset on `close` to avoid leaking stale values on Esc/dismiss
 */

let cachedDisplayTransitionSupport;

export function canTransitionDisplay() {
    if (cachedDisplayTransitionSupport !== undefined) {
        return cachedDisplayTransitionSupport;
    }
    if (!window.CSS?.supports?.('transition-behavior', 'allow-discrete') || !document.body) {
        return false;
    }
    const probe = document.createElement('div');
    probe.style.cssText = 'transition: display 1s allow-discrete !important; display: block;';
    document.body.appendChild(probe);
    getComputedStyle(probe).display;
    probe.style.display = 'none';
    cachedDisplayTransitionSupport = getComputedStyle(probe).display === 'block';
    probe.remove();
    return cachedDisplayTransitionSupport;
}

export function supportsTopLayerExit() {
    return Boolean(window.CSS?.supports?.('overlay', 'auto')) && canTransitionDisplay();
}

export async function closeTopLayer(dialog, returnValue) {
    if (!dialog || !dialog.open) return;

    if (!supportsTopLayerExit()) {
        if (!dialog.hasAttribute('data-closing')) {
            dialog.setAttribute('data-closing', '');
            const animations = typeof dialog.getAnimations === 'function'
                ? dialog.getAnimations({ subtree: true })
                : [];
            if (animations.length > 0) {
                await Promise.race([
                    Promise.allSettled(animations.map((a) => a.finished)),
                    new Promise((resolve) => setTimeout(resolve, 450)),
                ]);
            }
            if (!dialog.hasAttribute('data-closing')) return;
            dialog.removeAttribute('data-closing');
        }
    }

    if (returnValue !== undefined) {
        dialog.close(returnValue);
    } else {
        dialog.close();
    }
}

function isPointerOutsideDialog(event, dialog) {
    const rect = dialog.getBoundingClientRect();
    return (
        event.clientX < rect.left ||
        event.clientX > rect.right ||
        event.clientY < rect.top ||
        event.clientY > rect.bottom
    );
}

export function enhanceModalDialog(dialog) {
    if (!dialog || dialog.dataset.bhimaDialogReady === '1') return;
    dialog.dataset.bhimaDialogReady = '1';

    // 1. Intercept Esc / close-request when top-layer exit fallback is needed so exit animates cleanly.
    dialog.addEventListener('cancel', (event) => {
        if (!supportsTopLayerExit()) {
            event.preventDefault();
            closeTopLayer(dialog, '');
        }
    });

    // 2. Intercept <form method="dialog"> submit only when browser lacks native top-layer exit animation.
    dialog.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.getAttribute('method')?.toLowerCase() !== 'dialog') {
            return;
        }
        if (!supportsTopLayerExit()) {
            event.preventDefault();
            const submitterValue = event.submitter?.getAttribute('value') ?? '';
            closeTopLayer(dialog, submitterValue);
        }
    });

    // 3. Safari fallback for declarative light-dismiss `closedby="any"`.
    // Requires BOTH pointerdown and pointerup outside the dialog box so text-selection drags never dismiss.
    if (!('closedBy' in HTMLDialogElement.prototype)) {
        let pressedOutside = false;
        dialog.addEventListener('pointerdown', (event) => {
            if (dialog.getAttribute('closedby') !== 'any') return;
            pressedOutside = isPointerOutsideDialog(event, dialog);
        });
        dialog.addEventListener('pointerup', (event) => {
            if (dialog.getAttribute('closedby') !== 'any') return;
            if (pressedOutside && isPointerOutsideDialog(event, dialog)) {
                closeTopLayer(dialog, '');
            }
            pressedOutside = false;
        });
    }

    // 4. Reset returnValue on close so stale button values never leak on subsequent Esc/dismiss.
    dialog.addEventListener('close', () => {
        dialog.removeAttribute('data-closing');
        const chosen = dialog.returnValue || '';
        dialog.returnValue = '';

        dialog.dispatchEvent(
            new CustomEvent('bhima:dialog-result', {
                bubbles: true,
                detail: { value: chosen, confirmed: chosen === 'confirm' },
            })
        );
    });
}

let invokerFallbackInitialized = false;

export function initInvokerCommandsFallback() {
    if (invokerFallbackInitialized) return;
    invokerFallbackInitialized = true;

    // MANDATORY: Feature detect strictly on HTMLButtonElement.prototype.
    if ('commandForElement' in HTMLButtonElement.prototype) {
        return;
    }

    document.addEventListener('click', (event) => {
        const button = event.target?.closest?.('button[command][commandfor]');
        if (!button || button.disabled) return;

        const targetId = button.getAttribute('commandfor');
        const command = button.getAttribute('command');
        if (!targetId || !command) return;

        const target = document.getElementById(targetId);
        if (!(target instanceof HTMLDialogElement)) return;

        if (command === 'show-modal' && !target.open) {
            target.showModal();
        } else if (command === 'close' && target.open) {
            closeTopLayer(target, '');
        } else if (command === 'request-close' && target.open) {
            if (typeof target.requestClose === 'function') {
                target.requestClose();
            } else {
                const cancelEvent = new Event('cancel', { cancelable: true });
                if (target.dispatchEvent(cancelEvent)) {
                    closeTopLayer(target, '');
                }
            }
        }
    });
}

export function initAccessibleDialogs(root = document) {
    initInvokerCommandsFallback();
    root.querySelectorAll('dialog[data-confirm-dialog], dialog#record-dialog').forEach((dialog) => {
        enhanceModalDialog(dialog);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initAccessibleDialogs());
} else {
    initAccessibleDialogs();
}
