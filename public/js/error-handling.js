/**
 * SWEEP Error Handling Utilities
 * Provides consistent error handling and user feedback across the application
 */

/**
 * Display a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, warning, info)
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Map type to Bootstrap alert class
    const typeMap = {
        'success': 'success',
        'error': 'danger',
        'warning': 'warning',
        'info': 'info'
    };
    const alertClass = typeMap[type] || 'info';

    // Map type to icon
    const iconMap = {
        'success': 'check-circle-fill',
        'error': 'exclamation-triangle-fill',
        'warning': 'exclamation-circle-fill',
        'info': 'info-circle-fill'
    };
    const icon = iconMap[type] || 'info-circle-fill';

    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `alert alert-${alertClass} alert-dismissible fade show`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <i class="bi bi-${icon}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    toastContainer.appendChild(toast);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            const bsAlert = new bootstrap.Alert(toastElement);
            bsAlert.close();
        }
    }, 5000);
}

/**
 * Handle AJAX errors consistently
 * @param {object} error - The error object from fetch or axios
 */
function handleAjaxError(error) {
    console.error('AJAX Error:', error);
    
    let message = 'An unexpected error occurred. Please try again.';
    
    if (error.response) {
        // Server responded with error status
        if (error.response.data && error.response.data.message) {
            message = error.response.data.message;
        } else if (error.response.status === 404) {
            message = 'The requested resource was not found.';
        } else if (error.response.status === 403) {
            message = 'You do not have permission to perform this action.';
        } else if (error.response.status === 422) {
            message = 'Validation failed. Please check your input.';
        } else if (error.response.status >= 500) {
            message = 'A server error occurred. Please try again later.';
        }
    } else if (error.request) {
        // Request was made but no response received
        message = 'Unable to connect to the server. Please check your internet connection.';
    }
    
    showToast(message, 'error');
}

/**
 * Confirm deletion with a modal
 * @param {string} itemName - The name of the item being deleted
 * @param {function} onConfirm - Callback function to execute on confirmation
 */
function confirmDelete(itemName, onConfirm) {
    const confirmed = confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`);
    if (confirmed && typeof onConfirm === 'function') {
        onConfirm();
    }
}

/**
 * Validate form before submission
 * @param {HTMLFormElement} form - The form element to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function validateForm(form) {
    // Check HTML5 validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    // Additional custom validation can be added here
    return true;
}

/**
 * Display validation errors on a form
 * @param {HTMLFormElement} form - The form element
 * @param {object} errors - Object with field names as keys and error messages as values
 */
function displayValidationErrors(form, errors) {
    // Clear existing errors
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
    
    // Display new errors
    Object.keys(errors).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('is-invalid');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = Array.isArray(errors[fieldName]) 
                ? errors[fieldName][0] 
                : errors[fieldName];
            
            field.parentNode.appendChild(errorDiv);
        }
    });
    
    // Scroll to first error
    const firstError = form.querySelector('.is-invalid');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
    }
}

/**
 * Handle form submission with loading state
 * @param {HTMLFormElement} form - The form element
 * @param {HTMLButtonElement} submitButton - The submit button
 */
function handleFormSubmit(form, submitButton) {
    if (!validateForm(form)) {
        return;
    }
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    // Re-enable after form submission (in case of validation errors)
    setTimeout(() => {
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    }, 3000);
}

/**
 * Initialize error handling for all forms
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add submit handler to all forms with data-validate attribute
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                handleFormSubmit(form, submitButton);
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showToast,
        handleAjaxError,
        confirmDelete,
        validateForm,
        displayValidationErrors,
        handleFormSubmit
    };
}
