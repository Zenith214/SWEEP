/**
 * Dashboard Navigation and Context Preservation
 * Handles drill-down navigation with filter context preservation
 */

// Store current dashboard context
let dashboardContext = {
    filters: {},
    returnUrl: null,
    timestamp: null
};

/**
 * Initialize dashboard context from URL parameters
 */
function initializeDashboardContext() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Extract filter parameters
    dashboardContext.filters = {
        start_date: urlParams.get('start_date'),
        end_date: urlParams.get('end_date'),
        route_id: urlParams.get('route_id'),
        zone_id: urlParams.get('zone_id'),
        period: urlParams.get('period') || '30days',
        compare_period: urlParams.get('compare_period')
    };
    
    // Remove null values
    Object.keys(dashboardContext.filters).forEach(key => {
        if (dashboardContext.filters[key] === null) {
            delete dashboardContext.filters[key];
        }
    });
    
    // Store return URL if coming from dashboard
    const returnUrl = urlParams.get('return_url');
    if (returnUrl) {
        dashboardContext.returnUrl = decodeURIComponent(returnUrl);
    }
    
    dashboardContext.timestamp = Date.now();
    
    // Store in sessionStorage for persistence
    sessionStorage.setItem('dashboardContext', JSON.stringify(dashboardContext));
}

/**
 * Get current dashboard context
 */
function getDashboardContext() {
    const stored = sessionStorage.getItem('dashboardContext');
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            console.error('Failed to parse dashboard context:', e);
        }
    }
    return dashboardContext;
}

/**
 * Build URL with context parameters
 */
function buildUrlWithContext(baseUrl, preserveContext = true) {
    if (!preserveContext) {
        return baseUrl;
    }
    
    const context = getDashboardContext();
    const url = new URL(baseUrl, window.location.origin);
    
    // Add filter parameters
    Object.keys(context.filters).forEach(key => {
        if (context.filters[key]) {
            url.searchParams.set(key, context.filters[key]);
        }
    });
    
    // Add return URL (current dashboard page)
    const currentUrl = window.location.pathname + window.location.search;
    url.searchParams.set('return_url', encodeURIComponent(currentUrl));
    
    return url.toString();
}

/**
 * Handle metric card click with context preservation
 */
function handleMetricClick(event, targetUrl, preserveContext = true) {
    // Don't navigate if clicking on the new tab icon
    if (event.target.closest('a[target="_blank"]')) {
        return;
    }
    
    const urlWithContext = buildUrlWithContext(targetUrl, preserveContext);
    window.location.href = urlWithContext;
}

/**
 * Open link in new tab with context
 */
function openInNewTab(event, targetUrl, preserveContext = true) {
    event.preventDefault();
    event.stopPropagation();
    
    const urlWithContext = buildUrlWithContext(targetUrl, preserveContext);
    window.open(urlWithContext, '_blank');
}

/**
 * Navigate back to dashboard with preserved context
 */
function returnToDashboard() {
    const context = getDashboardContext();
    
    if (context.returnUrl) {
        window.location.href = context.returnUrl;
    } else {
        // Fallback to dashboard with current filters
        const dashboardUrl = new URL('/dashboard', window.location.origin);
        Object.keys(context.filters).forEach(key => {
            if (context.filters[key]) {
                dashboardUrl.searchParams.set(key, context.filters[key]);
            }
        });
        window.location.href = dashboardUrl.toString();
    }
}

/**
 * Update filter in context and reload
 */
function updateDashboardFilter(filterName, filterValue) {
    const context = getDashboardContext();
    context.filters[filterName] = filterValue;
    context.timestamp = Date.now();
    
    sessionStorage.setItem('dashboardContext', JSON.stringify(context));
    
    // Reload page with new filter
    const url = new URL(window.location.href);
    url.searchParams.set(filterName, filterValue);
    window.location.href = url.toString();
}

/**
 * Clear dashboard context
 */
function clearDashboardContext() {
    dashboardContext = {
        filters: {},
        returnUrl: null,
        timestamp: null
    };
    sessionStorage.removeItem('dashboardContext');
}

/**
 * Build breadcrumb from context
 */
function buildBreadcrumbFromContext() {
    const context = getDashboardContext();
    const breadcrumbs = [];
    
    // Always start with dashboard
    breadcrumbs.push({
        label: 'Dashboard',
        url: context.returnUrl || '/dashboard',
        icon: 'speedometer2'
    });
    
    // Add current page
    const pageTitle = document.querySelector('h1, h2')?.textContent?.trim();
    if (pageTitle && pageTitle !== 'Dashboard') {
        breadcrumbs.push({
            label: pageTitle,
            url: window.location.href,
            icon: null
        });
    }
    
    return breadcrumbs;
}

/**
 * Render breadcrumb navigation
 */
function renderBreadcrumb(containerId = 'breadcrumb-container') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const breadcrumbs = buildBreadcrumbFromContext();
    if (breadcrumbs.length <= 1) {
        container.style.display = 'none';
        return;
    }
    
    let html = '<nav aria-label="breadcrumb" class="mb-3">';
    html += '<ol class="breadcrumb bg-white p-3 rounded shadow-sm">';
    
    breadcrumbs.forEach((item, index) => {
        const isLast = index === breadcrumbs.length - 1;
        const icon = item.icon ? `<i class="bi bi-${item.icon} me-1"></i>` : '';
        
        if (isLast) {
            html += `<li class="breadcrumb-item active" aria-current="page">${icon}${item.label}</li>`;
        } else {
            html += `<li class="breadcrumb-item"><a href="${item.url}" class="text-decoration-none">${icon}${item.label}</a></li>`;
        }
    });
    
    html += '</ol></nav>';
    container.innerHTML = html;
    container.style.display = 'block';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardContext();
    
    // Render breadcrumb if container exists
    if (document.getElementById('breadcrumb-container')) {
        renderBreadcrumb();
    }
    
    // Add keyboard shortcut to return to dashboard (Alt+D)
    document.addEventListener('keydown', function(event) {
        if (event.altKey && event.key === 'd') {
            event.preventDefault();
            returnToDashboard();
        }
    });
});

// Clean up old context (older than 1 hour)
setInterval(function() {
    const context = getDashboardContext();
    if (context.timestamp && Date.now() - context.timestamp > 3600000) {
        clearDashboardContext();
    }
}, 60000); // Check every minute
