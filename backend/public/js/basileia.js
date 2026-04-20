/* ==========================================
   BASILÉIA VENDAS - UI Components
   Toast, Confirm Dialog, Modal helpers
   ========================================== */

// === TOAST NOTIFICATION SYSTEM ===
const BasileiaToast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'info', duration = 4000) {
        this.init();
        const icons = {
            success: 'ri-check-double-line',
            danger: 'ri-error-warning-line',
            warning: 'ri-alert-line',
            info: 'ri-information-line'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="${icons[type] || icons.info}"></i>
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.classList.add('removing'); setTimeout(() => this.parentElement.remove(), 250);">
                <i class="ri-close-line"></i>
            </button>
        `;

        this.container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 250);
            }
        }, duration);

        return toast;
    },

    success(message, duration) { return this.show(message, 'success', duration); },
    error(message, duration) { return this.show(message, 'danger', duration); },
    warning(message, duration) { return this.show(message, 'warning', duration); },
    info(message, duration) { return this.show(message, 'info', duration); }
};

// === CONFIRM DIALOG ===
const BasileiaConfirm = {
    show(options = {}) {
        const {
            title = 'Confirmar ação',
            message = 'Tem certeza que deseja continuar?',
            type = 'warning', // warning, danger, success
            confirmText = 'Confirmar',
            cancelText = 'Cancelar',
            onConfirm = () => {},
            onCancel = () => {}
        } = options;

        const icons = {
            warning: 'ri-alert-line',
            danger: 'ri-error-warning-line',
            success: 'ri-check-line'
        };

        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';
        overlay.innerHTML = `
            <div class="confirm-dialog">
                <div class="confirm-icon ${type}">
                    <i class="${icons[type] || icons.warning}"></i>
                </div>
                <h3>${title}</h3>
                <p>${message}</p>
                <div class="confirm-actions">
                    <button class="btn btn-outline confirm-cancel">${cancelText}</button>
                    <button class="btn btn-${type === 'danger' ? 'danger' : 'primary'} confirm-ok">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        overlay.querySelector('.confirm-ok').addEventListener('click', () => {
            overlay.remove();
            onConfirm();
        });

        overlay.querySelector('.confirm-cancel').addEventListener('click', () => {
            overlay.remove();
            onCancel();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.remove();
                onCancel();
            }
        });

        return overlay;
    }
};

// === MODAL HELPER ===
const BasileiaModal = {
    open(modalId) {
        document.querySelectorAll('.modal-overlay.show').forEach(m => {
            m.classList.remove('show');
        });
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    },

    close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    },

    closeAll() {
        document.querySelectorAll('.modal-overlay.show').forEach(m => {
            m.classList.remove('show');
        });
        document.body.style.overflow = '';
    }
};

// Close modals on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay') && e.target.classList.contains('show')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        BasileiaModal.closeAll();
    }
});

// === REPLACE NATIVE ALERT ===
const originalAlert = window.alert;
window.alert = function(message) {
    if (message.toLowerCase().includes('sucesso') || message.toLowerCase().includes('success')) {
        BasileiaToast.success(message);
    } else if (message.toLowerCase().includes('erro') || message.toLowerCase().includes('error')) {
        BasileiaToast.error(message);
    } else if (message.toLowerCase().includes('atenção') || message.toLowerCase().includes('aviso')) {
        BasileiaToast.warning(message);
    } else {
        BasileiaToast.info(message);
    }
};

// === REPLACE NATIVE CONFIRM ===
const originalConfirm = window.confirm;
window.confirm = function(message) {
    return new Promise((resolve) => {
        BasileiaConfirm.show({
            message: message,
            onConfirm: () => resolve(true),
            onCancel: () => resolve(false)
        });
    });
};

// === FORM VALIDATION HELPER ===
const BasileiaForm = {
    showError(input, message) {
        this.clearError(input);
        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        input.parentElement.appendChild(errorDiv);
    },

    clearError(input) {
        input.classList.remove('is-invalid');
        const existing = input.parentElement.querySelector('.field-error');
        if (existing) existing.remove();
    },

    clearAllErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.field-error').forEach(el => el.remove());
    }
};

// === COPY TO CLIPBOARD ===
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        BasileiaToast.success('Copiado para a área de transferência!');
    }).catch(() => {
        prompt('Copie o conteúdo abaixo:', text);
    });
}

// === LOADING STATE FOR BUTTONS ===
function setButtonLoading(btn, loading = true) {
    if (loading) {
        btn.disabled = true;
        btn.dataset.originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 1s linear infinite;"></i> Processando...';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
    }
}

// Add spin animation
const style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);

// === TAB SWITCHING ===
function switchTab(tabId, clickedBtn) {
    const container = clickedBtn.closest('.tabs').parentElement;
    container.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
    container.querySelectorAll('.tab-btn').forEach(tb => tb.classList.remove('active'));

    document.getElementById(tabId).classList.add('active');
    clickedBtn.classList.add('active');
}

// === MENU DROPDOWN TOGGLE ===
function toggleMenuDropdown(id) {
    const content = document.getElementById(id);
    const header = content?.previousElementSibling;
    if (!content || !header) return;

    const wasOpen = content.classList.contains('show');
    document.querySelectorAll('.menu-dropdown-content.show').forEach(el => el.classList.remove('show'));
    document.querySelectorAll('.menu-dropdown-header').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.menu-dropdown-icon').forEach(el => el.classList.remove('rotated'));

    if (!wasOpen) {
        content.classList.add('show');
        header.classList.add('active');
        header.querySelector('.menu-dropdown-icon')?.classList.add('rotated');
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.menu-dropdown')) {
        document.querySelectorAll('.menu-dropdown-content.show').forEach(el => el.classList.remove('show'));
        document.querySelectorAll('.menu-dropdown-header.active').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.menu-dropdown-icon.rotated').forEach(el => el.classList.remove('rotated'));
    }
});

// === AUTO INITIALIZE ===
document.addEventListener('DOMContentLoaded', function() {
    BasileiaToast.init();
});
