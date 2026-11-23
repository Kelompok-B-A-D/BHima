import { enhanceModalDialog } from './components/confirm-dialog';

const deleteDialog = document.getElementById('confirm-delete-service-dialog');
let pendingDeleteForm = null;

if (deleteDialog) {
    enhanceModalDialog(deleteDialog);
    deleteDialog.addEventListener('bhima:dialog-result', (event) => {
        if (event.detail?.confirmed && pendingDeleteForm) {
            const formToSubmit = pendingDeleteForm;
            pendingDeleteForm = null;
            formToSubmit.dataset.confirmedDelete = '1';
            formToSubmit.submit();
            return;
        }
        pendingDeleteForm = null;
    });
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.confirmedDelete === '1') {
            return;
        }
        if (deleteDialog && typeof deleteDialog.showModal === 'function') {
            event.preventDefault();
            pendingDeleteForm = form;
            const descEl = document.getElementById('confirm-delete-service-dialog-desc');
            if (descEl && form.dataset.confirm) {
                descEl.textContent = form.dataset.confirm;
            }
            deleteDialog.showModal();
        }
    });
});

document.querySelectorAll('form[data-pending]').forEach((form) => {
    const file = form.querySelector('input[type=file]');
    file?.addEventListener('change', () => {
        const selected = file.files[0];
        file.setCustomValidity(
            selected && (!/\.pdf$/i.test(selected.name) || selected.size > 10 * 1024 * 1024)
                ? 'Pilih dokumen PDF maksimal 10 MB.'
                : ''
        );
        const fileStatus = form.querySelector('[data-file-status]');
        if (fileStatus) {
            fileStatus.textContent = selected ? selected.name : 'Tidak memilih file baru.';
        }
        file.reportValidity();
    });
    form.addEventListener('submit', () => {
        form.querySelectorAll('button').forEach((button) => {
            button.disabled = true;
        });
        const status = form.querySelector('[data-submit-status]');
        if (status) {
            status.textContent = 'Menyimpan informasi, mohon tunggu...';
        }
    });
});

const menuToggle = document.querySelector('[data-menu-toggle]');
function setMenu(open) {
    document.body.classList.toggle('nav-open', open);
    menuToggle?.setAttribute('aria-expanded', String(open));
}
menuToggle?.addEventListener('click', () => setMenu(!document.body.classList.contains('nav-open')));
document.querySelector('[data-menu-close]')?.addEventListener('click', () => setMenu(false));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.body.classList.contains('nav-open')) {
        setMenu(false);
        menuToggle?.focus();
    }
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-pending] button').forEach((button) => {
        button.disabled = false;
    });
    document.querySelectorAll('[data-submit-status]').forEach((status) => {
        status.textContent = '';
    });
});
