/**
 * SWEEP Error Handling and User Feedback Utilities
 */

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(function(alert) {
        // Only auto-dismiss success and info alerts
        if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }
    });
});

/**
 * Show confirmation modal before form submission
 * @param {string} formId - The ID of the form to submit
 * @param {string} message - The confirmation message
 * @param {string} title - The modal title (optional)
 */
function confirmAction(formId, message, title = 'Confirm Action') {
    // Create modal if it doesn't exist
    let modal = document.getElementById('dynamicConfirmModal');
    
    if (!modal) {
        const modalHtml = `
            <div class="modal fade" id="dynamicConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                <span id="dynamicModalTitle"></span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="dynamicModalMessage" class="mb-0"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="dynamicModalConfirm">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('dynamicConfirmModal');
    }
    
    // Update modal content
    document.getElementById('dynamicModalTitle').textContent = title;
    document.getElementById('dynamicModalMessage').textContent = message;
    
    // Set up confirm button
    const confirmBtn = document.getElementById('dynamicModalConfirm');
    confirmBtn.onclick = function() {
        document.getElementById(formId).submit();
    };
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    return false;
}

/**
 * Show confirmation modal for deletion
 * @param {string} formId - The ID of the form to submit
 * @param {string} itemName - The name of the item being deleted
 */
function confirmDelete(formId, itemName) {
    return confirmAction(
        formId,
        `Are you sure you want to delete ${itemName}? This action cannot be undone.`,
        'Confirm Deletion'
    );
}

/**
 * Show confirmation modal for cancellation
 * @param {string} formId - The ID of the form to submit
 * @param {string} itemName - The name of the item being cancelled
 */
function confirmCancel(formId, itemName) {
    return confirmAction(
        formId,
        `Are you sure you want to cancel ${itemName}?`,
        'Confirm Cancellation'
    );
}

/**
 * Show status change warning modal
 * @param {string} formId - The ID of the form to submit
 * @param {string} newStatus - The new status
 * @param {boolean} hasFutureAssignments - Whether truck has future assignments
 */
function confirmStatusChange(formId, newStatus, hasFutureAssignments) {
    let message = `Are you sure you want to change the truck status to "${newStatus}"?`;
    
    if (hasFutureAssignments && (newStatus === 'maintenance' || newStatus === 'out_of_service')) {
        message += '\n\nWarning: This truck has future assignments that may be affected by this status change.';
    }
    
    return confirmAction(formId, message, 'Confirm Status Change');
}

/**
 * Validate form before submission
 * @param {HTMLFormElement} form - The form element
 * @returns {boolean} - Whether the form is valid
 */
function validateForm(form) {
    // Remove previous error messages
    const existingErrors = form.querySelectorAll('.invalid-feedback');
    existingErrors.forEach(error => error.remove());
    
    const existingInvalidInputs = form.querySelectorAll('.is-invalid');
    existingInvalidInputs.forEach(input => input.classList.remove('is-invalid'));
    
    let isValid = true;
    
    // Check required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = 'This field is required.';
            field.parentNode.appendChild(errorDiv);
        }
    });
    
    // Check date fields
    const dateFields = form.querySelectorAll('input[type="date"]');
    dateFields.forEach(field => {
        if (field.value && field.hasAttribute('min')) {
            const minDate = new Date(field.getAttribute('min'));
            const selectedDate = new Date(field.value);
            
            if (selectedDate < minDate) {
                isValid = false;
                field.classList.add('is-invalid');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Date cannot be in the past.';
                field.parentNode.appendChild(errorDiv);
            }
        }
    });
    
    return isValid;
}

/**
 * Show inline error message for a field
 * @param {string} fieldId - The ID of the field
 * @param {string} message - The error message
 */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Remove existing error
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error class and message
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 * @param {string} fieldId - The ID of the field
 */
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Show toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast (success, error, warning, info)
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Map type to Bootstrap classes
    const typeClasses = {
        success: 'bg-success text-white',
        error: 'bg-danger text-white',
        warning: 'bg-warning text-dark',
        info: 'bg-info text-white'
    };
    
    const typeIcons = {
        success: 'check-circle-fill',
        error: 'exclamation-triangle-fill',
        warning: 'exclamation-circle-fill',
        info: 'info-circle-fill'
    };
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast ${typeClasses[type] || typeClasses.info}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${typeClasses[type] || typeClasses.info}">
                <i class="bi bi-${typeIcons[type] || typeIcons.info} me-2"></i>
                <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Export functions for use in other scripts
window.confirmAction = confirmAction;
window.confirmDelete = confirmDelete;
window.confirmCancel = confirmCancel;
window.confirmStatusChange = confirmStatusChange;
window.validateForm = validateForm;
window.showFieldError = showFieldError;
window.clearFieldError = clearFieldError;
window.showToast = showToast;
