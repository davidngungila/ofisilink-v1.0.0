/**
 * Advanced Toast Notification System
 * Beautiful, customizable toast notifications for OfisiLink
 */

class AdvancedToast {
    constructor() {
        this.queue = [];
        this.maxToasts = 5;
        this.defaultDuration = 5000;
        this.init();
    }

    init() {
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            document.body.appendChild(this.container);
            this.setupStyles();
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    setupStyles() {
        const style = document.createElement('style');
        style.textContent = `
            #toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 99999;
                max-width: 400px;
                width: 100%;
                pointer-events: none;
            }
            .advanced-toast {
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
                margin-bottom: 12px;
                padding: 0;
                overflow: hidden;
                pointer-events: auto;
                animation: slideInRight 0.3s ease-out;
                border-left: 4px solid;
                position: relative;
                max-width: 400px;
                min-width: 320px;
            }
            .advanced-toast.success { border-left-color: #28a745; }
            .advanced-toast.error { border-left-color: #dc3545; }
            .advanced-toast.warning { border-left-color: #ffc107; }
            .advanced-toast.info { border-left-color: #17a2b8; }
            .advanced-toast.primary { border-left-color: #940000; }
            .toast-header {
                display: flex;
                align-items: center;
                padding: 16px 20px;
                background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%);
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }
            .toast-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 12px;
                font-size: 20px;
                flex-shrink: 0;
            }
            .toast-icon.success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
            .toast-icon.error { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
            .toast-icon.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
            .toast-icon.info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
            .toast-icon.primary { background: rgba(148, 0, 0, 0.1); color: #940000; }
            .toast-content { flex: 1; min-width: 0; }
            .toast-title {
                font-weight: 600;
                font-size: 15px;
                color: #212529;
                margin: 0 0 4px 0;
                line-height: 1.4;
            }
            .toast-message {
                font-size: 13px;
                color: #6c757d;
                margin: 0;
                line-height: 1.5;
                word-wrap: break-word;
            }
            .toast-body { padding: 12px 20px 16px 20px; }
            .toast-actions {
                display: flex;
                gap: 8px;
                margin-top: 12px;
                flex-wrap: wrap;
            }
            .toast-action-btn {
                padding: 6px 14px;
                border: none;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                flex: 1;
                min-width: 80px;
            }
            .toast-action-btn.primary {
                background: #940000;
                color: white;
            }
            .toast-action-btn.primary:hover { background: #a80000; }
            .toast-action-btn.secondary {
                background: #6c757d;
                color: white;
            }
            .toast-action-btn.secondary:hover { background: #5a6268; }
            .toast-close {
                position: absolute;
                top: 12px;
                right: 12px;
                background: transparent;
                border: none;
                font-size: 18px;
                color: #6c757d;
                cursor: pointer;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: all 0.2s;
                padding: 0;
            }
            .toast-close:hover {
                background: rgba(0, 0, 0, 0.05);
                color: #212529;
            }
            .toast-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(0, 0, 0, 0.1);
                width: 100%;
            }
            .toast-progress-bar {
                height: 100%;
                background: currentColor;
                transition: width linear;
                width: 100%;
            }
            .advanced-toast.success .toast-progress-bar { background: #28a745; }
            .advanced-toast.error .toast-progress-bar { background: #dc3545; }
            .advanced-toast.warning .toast-progress-bar { background: #ffc107; }
            .advanced-toast.info .toast-progress-bar { background: #17a2b8; }
            .advanced-toast.primary .toast-progress-bar { background: #940000; }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .advanced-toast.hiding {
                animation: slideOutRight 0.3s ease-in forwards;
            }
            .toast-html-content {
                font-size: 13px;
                color: #495057;
                line-height: 1.6;
            }
        `;
        document.head.appendChild(style);
    }

    show(options) {
        const config = {
            type: options.type || 'info',
            title: options.title || '',
            message: options.message || '',
            html: options.html || null,
            duration: options.duration !== undefined ? options.duration : this.defaultDuration,
            persistent: options.persistent || false,
            icon: options.icon || null,
            actions: options.actions || [],
            onClose: options.onClose || null,
            sound: options.sound !== undefined ? options.sound : false,
            ...options
        };

        const toast = this.createToast(config);
        this.container.appendChild(toast);

        if (config.sound) this.playSound(config.type);
        if (!config.persistent && config.duration > 0) {
            this.autoDismiss(toast, config.duration);
        }

        this.limitToasts();
        return toast;
    }

    createToast(config) {
        const toast = document.createElement('div');
        toast.className = `advanced-toast ${config.type}`;
        toast.setAttribute('data-toast-id', Date.now() + Math.random());

        const icon = this.getIcon(config.type, config.icon);
        let actionsHtml = '';
        
        if (config.actions && config.actions.length > 0) {
            actionsHtml = '<div class="toast-actions">';
            config.actions.forEach(action => {
                const btnClass = action.class || 'secondary';
                actionsHtml += `<button class="toast-action-btn ${btnClass}" data-action="${action.name || 'action'}">${action.label || 'Action'}</button>`;
            });
            actionsHtml += '</div>';
        }

        const messageContent = config.html 
            ? `<div class="toast-html-content">${config.html}</div>` 
            : `<p class="toast-message">${this.escapeHtml(config.message)}</p>`;

        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-icon ${config.type}">${icon}</div>
                <div class="toast-content">
                    <h6 class="toast-title">${this.escapeHtml(config.title)}</h6>
                </div>
                <button class="toast-close" aria-label="Close">&times;</button>
            </div>
            <div class="toast-body">
                ${messageContent}
                ${actionsHtml}
            </div>
            ${!config.persistent && config.duration > 0 ? '<div class="toast-progress"><div class="toast-progress-bar"></div></div>' : ''}
        `;

        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.dismiss(toast, config.onClose));

        if (config.actions && config.actions.length > 0) {
            toast.querySelectorAll('.toast-action-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const actionName = btn.getAttribute('data-action');
                    const action = config.actions.find(a => (a.name || 'action') === actionName);
                    if (action && action.callback) action.callback();
                    if (action && action.dismiss !== false) this.dismiss(toast, config.onClose);
                });
            });
        }

        toast._toastConfig = config;
        return toast;
    }

    getIcon(type, customIcon) {
        if (customIcon) return customIcon;
        const icons = {
            success: '<i class="bx bx-check-circle"></i>',
            error: '<i class="bx bx-error-circle"></i>',
            warning: '<i class="bx bx-error"></i>',
            info: '<i class="bx bx-info-circle"></i>',
            primary: '<i class="bx bx-bell"></i>'
        };
        return icons[type] || icons.info;
    }

    autoDismiss(toast, duration) {
        const progressBar = toast.querySelector('.toast-progress-bar');
        if (progressBar) {
            progressBar.style.transition = `width ${duration}ms linear`;
            setTimeout(() => progressBar.style.width = '0%', 10);
        }
        setTimeout(() => this.dismiss(toast), duration);
    }

    dismiss(toast, onClose) {
        if (!toast || !toast.parentNode) return;
        toast.classList.add('hiding');
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
            if (onClose && typeof onClose === 'function') onClose();
        }, 300);
    }

    limitToasts() {
        const toasts = this.container.querySelectorAll('.advanced-toast');
        if (toasts.length > this.maxToasts) {
            const excess = toasts.length - this.maxToasts;
            for (let i = 0; i < excess; i++) {
                this.dismiss(toasts[i]);
            }
        }
    }

    playSound(type) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            const frequencies = { success: 800, error: 400, warning: 600, info: 500, primary: 700 };
            oscillator.frequency.value = frequencies[type] || 500;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (e) {}
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    success(title, message, options = {}) {
        return this.show({ type: 'success', title, message, ...options });
    }

    error(title, message, options = {}) {
        return this.show({ type: 'error', title, message, ...options });
    }

    warning(title, message, options = {}) {
        return this.show({ type: 'warning', title, message, ...options });
    }

    info(title, message, options = {}) {
        return this.show({ type: 'info', title, message, ...options });
    }

    primary(title, message, options = {}) {
        return this.show({ type: 'primary', title, message, ...options });
    }

    dismissAll() {
        const toasts = this.container.querySelectorAll('.advanced-toast');
        toasts.forEach(toast => this.dismiss(toast));
    }
}

window.AdvancedToast = new AdvancedToast();
window.showToast = (type, title, message, options) => window.AdvancedToast.show({ type, title, message, ...options });
window.toastSuccess = (title, message, options) => window.AdvancedToast.success(title, message, options);
window.toastError = (title, message, options) => window.AdvancedToast.error(title, message, options);
window.toastWarning = (title, message, options) => window.AdvancedToast.warning(title, message, options);
window.toastInfo = (title, message, options) => window.AdvancedToast.info(title, message, options);
window.toastPrimary = (title, message, options) => window.AdvancedToast.primary(title, message, options);

// Helper function to show AJAX response messages
window.showAjaxToast = function(response, defaultTitle = 'Notification') {
    if (!response) return;
    
    const type = response.type || (response.success ? 'success' : 'error');
    const title = response.title || defaultTitle;
    const message = response.message || response.error || 'Operation completed';
    
    window.AdvancedToast.show({
        type: type,
        title: title,
        message: message,
        duration: type === 'error' ? 7000 : 5000,
        sound: true
    });
};

// Helper function to show validation errors
window.showValidationErrors = function(errors) {
    if (!errors || typeof errors !== 'object') return;
    
    let errorMessages = [];
    if (Array.isArray(errors)) {
        errorMessages = errors;
    } else if (errors.errors) {
        // Laravel validation errors format
        Object.keys(errors.errors).forEach(key => {
            if (Array.isArray(errors.errors[key])) {
                errors.errors[key].forEach(msg => errorMessages.push(`${key}: ${msg}`));
            } else {
                errorMessages.push(`${key}: ${errors.errors[key]}`);
            }
        });
    } else {
        Object.keys(errors).forEach(key => {
            if (Array.isArray(errors[key])) {
                errors[key].forEach(msg => errorMessages.push(`${key}: ${msg}`));
            } else {
                errorMessages.push(`${key}: ${errors[key]}`);
            }
        });
    }
    
    if (errorMessages.length > 0) {
        const errorHtml = '<ul style="margin: 8px 0 0 0; padding-left: 20px;"><li>' + 
                         errorMessages.map(msg => window.AdvancedToast.prototype.escapeHtml(msg)).join('</li><li>') + 
                         '</li></ul>';
        
        window.AdvancedToast.error('Validation Error', '', {
            html: errorHtml,
            duration: 8000,
            sound: true
        });
    }
};
