/**
 * Accessibility Helper Module
 * Provides keyboard navigation, ARIA live regions, and screen reader support
 */

(function() {
    'use strict';
    
    /**
     * Initialize accessibility features
     */
    function initAccessibility() {
        addSkipToContentLink();
        enhanceKeyboardNavigation();
        setupAriaLiveRegions();
        setupFocusManagement();
        announcePageChanges();
        enhanceChartAccessibility();
    }
    
    /**
     * Add skip to main content link for keyboard users
     */
    function addSkipToContentLink() {
        if (document.querySelector('.skip-to-content')) {
            return; // Already exists
        }
        
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-to-content';
        skipLink.textContent = 'Skip to main content';
        skipLink.setAttribute('aria-label', 'Skip to main content');
        
        document.body.insertBefore(skipLink, document.body.firstChild);
        
        // Ensure main content has ID
        const mainContent = document.querySelector('main') || 
                          document.querySelector('[role="main"]') ||
                          document.querySelector('.container-fluid');
        
        if (mainContent && !mainContent.id) {
            mainContent.id = 'main-content';
            mainContent.setAttribute('tabindex', '-1');
        }
    }
    
    /**
     * Enhance keyboard navigation for interactive elements
     */
    function enhanceKeyboardNavigation() {
        // Handle Enter and Space key for elements with onclick
        document.addEventListener('keydown', function(e) {
            const target = e.target;
            
            // Check if element is clickable but not a button or link
            if ((e.key === 'Enter' || e.key === ' ') && 
                target.hasAttribute('onclick') && 
                !target.matches('button, a, input, select, textarea')) {
                
                e.preventDefault();
                target.click();
            }
            
            // Handle Escape key to close modals/dropdowns
            if (e.key === 'Escape') {
                closeOpenElements();
            }
        });
        
        // Add keyboard navigation for metric cards
        const metricCards = document.querySelectorAll('.metric-card-clickable');
        metricCards.forEach(card => {
            if (!card.hasAttribute('tabindex')) {
                card.setAttribute('tabindex', '0');
            }
            
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const link = card.getAttribute('onclick')?.match(/'([^']+)'/)?.[1];
                    if (link) {
                        window.location.href = link;
                    }
                }
            });
        });
        
        // Add keyboard navigation for table rows
        const clickableRows = document.querySelectorAll('tr[onclick]');
        clickableRows.forEach(row => {
            if (!row.hasAttribute('tabindex')) {
                row.setAttribute('tabindex', '0');
            }
        });
    }
    
    /**
     * Setup ARIA live regions for dynamic content updates
     */
    function setupAriaLiveRegions() {
        // Create global announcement region if it doesn't exist
        if (!document.getElementById('aria-live-announcer')) {
            const announcer = document.createElement('div');
            announcer.id = 'aria-live-announcer';
            announcer.className = 'visually-hidden';
            announcer.setAttribute('role', 'status');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('aria-atomic', 'true');
            document.body.appendChild(announcer);
        }
        
        // Create alert region for urgent messages
        if (!document.getElementById('aria-live-alert')) {
            const alertRegion = document.createElement('div');
            alertRegion.id = 'aria-live-alert';
            alertRegion.className = 'visually-hidden';
            alertRegion.setAttribute('role', 'alert');
            alertRegion.setAttribute('aria-live', 'assertive');
            alertRegion.setAttribute('aria-atomic', 'true');
            document.body.appendChild(alertRegion);
        }
    }
    
    /**
     * Announce message to screen readers
     */
    window.announceToScreenReader = function(message, isUrgent = false) {
        const regionId = isUrgent ? 'aria-live-alert' : 'aria-live-announcer';
        const region = document.getElementById(regionId);
        
        if (region) {
            // Clear previous message
            region.textContent = '';
            
            // Set new message after a brief delay to ensure it's announced
            setTimeout(() => {
                region.textContent = message;
            }, 100);
            
            // Clear message after announcement
            setTimeout(() => {
                region.textContent = '';
            }, 3000);
        }
    };
    
    /**
     * Setup focus management for modals and dynamic content
     */
    function setupFocusManagement() {
        // Store last focused element before modal opens
        let lastFocusedElement = null;
        
        // Listen for modal show events
        document.addEventListener('show.bs.modal', function() {
            lastFocusedElement = document.activeElement;
        });
        
        // Restore focus when modal closes
        document.addEventListener('hidden.bs.modal', function() {
            if (lastFocusedElement) {
                lastFocusedElement.focus();
                lastFocusedElement = null;
            }
        });
        
        // Trap focus within modals
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;
            
            const modal = document.querySelector('.modal.show');
            if (!modal) return;
            
            const focusableElements = modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            if (focusableElements.length === 0) return;
            
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            if (e.shiftKey && document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            } else if (!e.shiftKey && document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        });
    }
    
    /**
     * Announce page changes and dynamic updates
     */
    function announcePageChanges() {
        // Announce when dashboard data is refreshed
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args).then(response => {
                if (response.ok && args[0].includes('/dashboard/metrics')) {
                    announceToScreenReader('Dashboard data has been updated');
                }
                return response;
            });
        };
        
        // Announce when filters are applied
        const filterForm = document.getElementById('dashboard-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', function() {
                announceToScreenReader('Applying filters, please wait');
            });
        }
        
        // Announce when exports are generated
        window.addEventListener('export-started', function(e) {
            const format = e.detail?.format || 'file';
            announceToScreenReader(`Generating ${format.toUpperCase()} export, please wait`);
        });
        
        window.addEventListener('export-completed', function(e) {
            const format = e.detail?.format || 'file';
            announceToScreenReader(`${format.toUpperCase()} export completed and downloaded`);
        });
    }
    
    /**
     * Enhance chart accessibility
     */
    function enhanceChartAccessibility() {
        const charts = document.querySelectorAll('canvas[data-chart-type]');
        
        charts.forEach(canvas => {
            // Make canvas focusable
            if (!canvas.hasAttribute('tabindex')) {
                canvas.setAttribute('tabindex', '0');
            }
            
            // Add keyboard navigation for charts
            canvas.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    announceChartData(canvas);
                }
            });
            
            // Add ARIA label if not present
            if (!canvas.hasAttribute('aria-label')) {
                const chartType = canvas.dataset.chartType || 'chart';
                const chartTitle = canvas.closest('.card')?.querySelector('.card-title')?.textContent || 'Data visualization';
                canvas.setAttribute('aria-label', `${chartTitle} - ${chartType} chart. Press Enter to hear data summary.`);
            }
            
            // Create text alternative
            createChartTextAlternative(canvas);
        });
    }
    
    /**
     * Announce chart data to screen readers
     */
    function announceChartData(canvas) {
        try {
            const chartData = JSON.parse(canvas.dataset.chartData || '{}');
            const chartType = canvas.dataset.chartType;
            const labels = chartData.labels || [];
            const values = chartData.values || [];
            
            if (labels.length === 0 || values.length === 0) {
                announceToScreenReader('No data available for this chart');
                return;
            }
            
            let announcement = `${chartType} chart with ${labels.length} data points. `;
            
            // Announce first few data points
            const maxPoints = Math.min(5, labels.length);
            for (let i = 0; i < maxPoints; i++) {
                announcement += `${labels[i]}: ${values[i]}. `;
            }
            
            if (labels.length > maxPoints) {
                announcement += `And ${labels.length - maxPoints} more data points.`;
            }
            
            announceToScreenReader(announcement);
        } catch (error) {
            console.error('Error announcing chart data:', error);
            announceToScreenReader('Unable to read chart data');
        }
    }
    
    /**
     * Create text alternative for charts
     */
    function createChartTextAlternative(canvas) {
        const existingAlt = canvas.nextElementSibling;
        if (existingAlt && existingAlt.classList.contains('chart-text-alternative')) {
            return; // Already exists
        }
        
        try {
            const chartData = JSON.parse(canvas.dataset.chartData || '{}');
            const labels = chartData.labels || [];
            const values = chartData.values || [];
            
            if (labels.length === 0 || values.length === 0) {
                return;
            }
            
            const altDiv = document.createElement('div');
            altDiv.className = 'chart-text-alternative visually-hidden';
            altDiv.setAttribute('role', 'region');
            altDiv.setAttribute('aria-label', 'Chart data table');
            
            let tableHTML = '<table><caption>Chart Data</caption><thead><tr><th>Label</th><th>Value</th></tr></thead><tbody>';
            
            for (let i = 0; i < labels.length; i++) {
                tableHTML += `<tr><td>${labels[i]}</td><td>${values[i]}</td></tr>`;
            }
            
            tableHTML += '</tbody></table>';
            altDiv.innerHTML = tableHTML;
            
            canvas.parentNode.insertBefore(altDiv, canvas.nextSibling);
        } catch (error) {
            console.error('Error creating chart text alternative:', error);
        }
    }
    
    /**
     * Close open dropdowns, modals, etc. on Escape key
     */
    function closeOpenElements() {
        // Close dropdowns
        const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
        openDropdowns.forEach(dropdown => {
            const toggle = dropdown.previousElementSibling;
            if (toggle) {
                bootstrap.Dropdown.getInstance(toggle)?.hide();
            }
        });
        
        // Close modals
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            bootstrap.Modal.getInstance(modal)?.hide();
        });
    }
    
    /**
     * Add loading state announcements
     */
    window.announceLoadingState = function(isLoading, message = '') {
        if (isLoading) {
            announceToScreenReader(message || 'Loading, please wait');
        } else {
            announceToScreenReader(message || 'Loading complete');
        }
    };
    
    /**
     * Enhance form validation messages for screen readers
     */
    function enhanceFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const invalidFields = form.querySelectorAll(':invalid');
                
                if (invalidFields.length > 0) {
                    e.preventDefault();
                    
                    const firstInvalid = invalidFields[0];
                    firstInvalid.focus();
                    
                    const fieldName = firstInvalid.getAttribute('name') || 'field';
                    announceToScreenReader(`Form validation failed. Please check the ${fieldName} field.`, true);
                }
            });
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initAccessibility();
            enhanceFormValidation();
        });
    } else {
        initAccessibility();
        enhanceFormValidation();
    }
    
    // Export functions for external use
    window.accessibilityHelper = {
        announce: window.announceToScreenReader,
        announceLoading: window.announceLoadingState,
        enhanceCharts: enhanceChartAccessibility
    };
    
})();
