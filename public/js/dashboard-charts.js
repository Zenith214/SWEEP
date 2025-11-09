/**
 * Dashboard Charts Module
 * Handles Chart.js initialization and management for dashboard visualizations
 */

// Store chart instances for cleanup and refresh
const chartInstances = {};

/**
 * Initialize all charts on the page
 */
function initializeCharts() {
    // Find all chart canvases
    const chartElements = document.querySelectorAll('canvas[data-chart-type]');
    
    chartElements.forEach(canvas => {
        const chartId = canvas.id;
        const chartType = canvas.dataset.chartType;
        const chartData = JSON.parse(canvas.dataset.chartData || '{}');
        
        // Destroy existing chart if it exists
        if (chartInstances[chartId]) {
            chartInstances[chartId].destroy();
        }
        
        // Create new chart based on type
        switch(chartType) {
            case 'line':
                chartInstances[chartId] = createLineChart(canvas, chartData);
                break;
            case 'pie':
                chartInstances[chartId] = createPieChart(canvas, chartData);
                break;
            case 'bar':
                chartInstances[chartId] = createBarChart(canvas, chartData);
                break;
            case 'area':
                chartInstances[chartId] = createAreaChart(canvas, chartData);
                break;
            default:
                console.warn(`Unknown chart type: ${chartType}`);
        }
    });
}

/**
 * Create a line chart for collection completion trends
 */
function createLineChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    // Add ARIA label
    const chartTitle = data.title || 'Collection Completion Trends';
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', `${chartTitle}. Line chart showing ${(data.labels || []).length} data points. Press Enter for data summary.`);
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: data.label || 'Completion Rate (%)',
                data: data.values || [],
                borderColor: 'rgb(46, 139, 87)',
                backgroundColor: 'rgba(46, 139, 87, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(46, 139, 87)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15,
                        color: '#2c2c2c' // Enhanced contrast
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.9)', // Enhanced contrast
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        color: '#2c2c2c' // Enhanced contrast
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)' // Enhanced contrast
                    }
                },
                x: {
                    ticks: {
                        color: '#2c2c2c' // Enhanced contrast
                    },
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

/**
 * Create a pie chart for recycling breakdown by material type
 */
function createPieChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    // Define color palette for materials with good contrast
    const colors = [
        'rgb(46, 139, 87)',   // Green
        'rgb(79, 180, 162)',  // Teal
        'rgb(244, 163, 0)',   // Orange
        'rgb(52, 152, 219)',  // Blue
        'rgb(155, 89, 182)',  // Purple
        'rgb(192, 57, 43)',   // Red (enhanced contrast)
        'rgb(127, 140, 141)', // Gray (enhanced contrast)
        'rgb(241, 196, 15)'   // Yellow
    ];
    
    // Add ARIA label
    const chartTitle = data.title || 'Recycling Breakdown by Material Type';
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', `${chartTitle}. Pie chart showing distribution across ${(data.labels || []).length} categories. Press Enter for data summary.`);
    
    return new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: colors.slice(0, (data.labels || []).length),
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15,
                        color: '#2c2c2c', // Enhanced contrast
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    
                                    return {
                                        text: `${label}: ${percentage}%`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)', // Enhanced contrast
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value.toFixed(2)} kg (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Create a bar chart for route performance comparison
 */
function createBarChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    // Add ARIA label
    const chartTitle = data.title || 'Route Performance Comparison';
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', `${chartTitle}. Bar chart comparing ${(data.labels || []).length} routes. Press Enter for data summary.`);
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: data.label || 'Completion Rate (%)',
                data: data.values || [],
                backgroundColor: 'rgba(46, 139, 87, 0.8)',
                borderColor: 'rgb(46, 139, 87)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15,
                        color: '#2c2c2c' // Enhanced contrast
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)', // Enhanced contrast
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        color: '#2c2c2c' // Enhanced contrast
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)' // Enhanced contrast
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        color: '#2c2c2c' // Enhanced contrast
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Create an area chart for cost trends over time
 */
function createAreaChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    // Add ARIA label
    const chartTitle = data.title || 'Cost Trends Over Time';
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', `${chartTitle}. Area chart showing cost trends across ${(data.labels || []).length} time periods. Press Enter for data summary.`);
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: data.label || 'Cost ($)',
                data: data.values || [],
                borderColor: 'rgb(244, 163, 0)',
                backgroundColor: 'rgba(244, 163, 0, 0.2)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(244, 163, 0)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15,
                        color: '#2c2c2c' // Enhanced contrast
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.9)', // Enhanced contrast
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                        }
                    }
                },
                filler: {
                    propagate: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        },
                        color: '#2c2c2c' // Enhanced contrast
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)' // Enhanced contrast
                    }
                },
                x: {
                    ticks: {
                        color: '#2c2c2c' // Enhanced contrast
                    },
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

/**
 * Refresh chart data via AJAX
 */
async function refreshChartData(chartId, filters = {}) {
    try {
        const canvas = document.getElementById(chartId);
        if (!canvas) {
            console.error(`Chart canvas not found: ${chartId}`);
            return;
        }
        
        const chartType = canvas.dataset.chartType;
        const metricType = canvas.dataset.metricType;
        
        // Build query string from filters
        const queryParams = new URLSearchParams(filters).toString();
        const url = `/dashboard/chart-data/${metricType}?${queryParams}`;
        
        // Fetch new data
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const newData = await response.json();
        
        // Update chart data
        const chart = chartInstances[chartId];
        if (chart) {
            chart.data.labels = newData.labels || [];
            chart.data.datasets[0].data = newData.values || [];
            chart.update('active');
        }
        
    } catch (error) {
        console.error('Error refreshing chart data:', error);
    }
}

/**
 * Refresh all charts on the page
 */
async function refreshAllCharts(filters = {}) {
    const chartElements = document.querySelectorAll('canvas[data-chart-type]');
    
    for (const canvas of chartElements) {
        await refreshChartData(canvas.id, filters);
    }
}

/**
 * Destroy all chart instances (cleanup)
 */
function destroyAllCharts() {
    Object.keys(chartInstances).forEach(chartId => {
        if (chartInstances[chartId]) {
            chartInstances[chartId].destroy();
            delete chartInstances[chartId];
        }
    });
}

// Initialize charts when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCharts);
} else {
    initializeCharts();
}

// Cleanup on page unload
window.addEventListener('beforeunload', destroyAllCharts);

// Export functions for external use
window.dashboardCharts = {
    initialize: initializeCharts,
    refresh: refreshChartData,
    refreshAll: refreshAllCharts,
    destroy: destroyAllCharts
};
