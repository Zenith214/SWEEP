/**
 * SWEEP Error Handling Utilities
 * Provides client-side error handling and user feedback functionality
 */

(function() {
    'use strict';

    /**
     * Display a toast notification
     */
    function showToast(message, type = 'info') {
        const toastContainer = getOrCreateToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        const iconMap = {
            'success': 'check-circle-fill',
            'danger': 'exclamation-triangle-fill',
            'warning': 'exclamation-circle-fill',
            'info': 'info-circle-fill'
        };
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${iconMap[type] || 'info-circle-fill'}"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    /**
     * Get or create toast container
     */
    function getOrCreateToastContainer() {
        let container = document.getElementById('toastContainer');
        
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        return container;
    }

    /**
     * Confirm action with modal
     */
    function confirmAction(message, onConfirm, options = {}) {
        const defaults = {
            title: 'Confirm Action',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            confirmClass: 'btn-danger',
            icon: 'exclamation-triangle-fill'
        };
        
        const settings = { ...defaults, ...options };
        
        // Create modal
        const modalId = 'confirmModal_' + Date.now();
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = modalId;
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');
        
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-${settings.icon}"></i> ${settings.title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            ${settings.cancelText}
                        </button>
                        <button type="button" class="btn ${settings.confirmClass}" id="confirmBtn_${modalId}">
                            ${settings.confirmText}
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const bsModal = new bootstrap.Modal(modal);
        
        document.getElementById('confirmBtn_' + modalId).addEventListener('click', function() {
            bsModal.hide();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            modal.remove();
        });
        
        bsModal.show();
    }

    /**
     * Handle AJAX errors
     */
    function handleAjaxError(error, defaultMessage = 'An error occurred') {
        let message = defaultMessage;
        
        if (error.response) {
            if (error.response.data && error.response.data.message) {
                message = error.response.data.message;
            } else if (error.response.data && error.response.data.errors) {
                // Handle validation errors
                const errors = error.response.data.errors;
                const errorMessages = Object.values(errors).flat();
                message = errorMessages.join('<br>');
            } else if (error.response.statusText) {
                message = error.response.statusText;
            }
        } else if (error.message) {
            message = error.message;
        }
        
        showToast(message, 'danger');
    }

    /**
     * Validate file upload
     */
    function validateFile(file, options = {}) {
        const defaults = {
            maxSize: 5 * 1024 * 1024, // 5MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'webp']
        };
        
        const settings = { ...defaults, ...options };
        
        // Check file size
        if (file.size > settings.maxSize) {
            const sizeMB = (settings.maxSize / (1024 * 1024)).toFixed(0);
            return {
                valid: false,
                error: `File "${file.name}" is too large. Maximum size is ${sizeMB}MB.`
            };
        }
        
        // Check file type
        if (settings.allowedTypes.length > 0 && !settings.allowedTypes.includes(file.type)) {
            return {
                valid: false,
                error: `File "${file.name}" has an invalid type. Only ${settings.allowedExtensions.join(', ').toUpperCase()} files are allowed.`
            };
        }
        
        // Check file extension
        const extension = file.name.split('.').pop().toLowerCase();
        if (settings.allowedExtensions.length > 0 && !settings.allowedExtensions.includes(extension)) {
            return {
                valid: false,
                error: `File "${file.name}" has an invalid extension. Only ${settings.allowedExtensions.join(', ').toUpperCase()} files are allowed.`
            };
        }
        
        return { valid: true };
    }

    /**
     * Format file size
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Auto-dismiss alerts after delay
     */
    function autoDismissAlerts(selector = '.alert-dismissible', delay = 5000) {
        const alerts = document.querySelectorAll(selector);
        
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, delay);
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts
        autoDismissAlerts();
        
        // Add confirmation to delete buttons
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                
                const message = this.getAttribute('data-confirm');
                const form = this.closest('form');
                const href = this.getAttribute('href');
                
                confirmAction(message, function() {
                    if (form) {
                        form.submit();
                    } else if (href) {
                        window.location.href = href;
                    }
                }, {
                    title: 'Confirm Deletion',
                    confirmText: 'Delete',
                    confirmClass: 'btn-danger',
                    icon: 'trash-fill'
                });
            });
        });
    });

    // Expose utilities globally
    window.SWEEP = window.SWEEP || {};
    window.SWEEP.showToast = showToast;
    window.SWEEP.confirmAction = confirmAction;
    window.SWEEP.handleAjaxError = handleAjaxError;
    window.SWEEP.validateFile = validateFile;
    window.SWEEP.formatFileSize = formatFileSize;
    window.SWEEP.autoDismissAlerts = autoDismissAlerts;
})();
