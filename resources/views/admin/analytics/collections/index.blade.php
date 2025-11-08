@section('title', 'Collection Analytics')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a class="nav-link" href="{{ route('admin.routes.index') }}">
                <i class="bi bi-map"></i> Routes
            </a>
            <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                <i class="bi bi-calendar"></i> Schedules
            </a>
            <a class="nav-link" href="{{ route('admin.holidays.index') }}">
                <i class="bi bi-calendar-x"></i> Holidays
            </a>
            <a class="nav-link" href="{{ route('admin.trucks.index') }}">
                <i class="bi bi-truck"></i> Trucks
            </a>
            <a class="nav-link" href="{{ route('admin.assignments.index') }}">
                <i class="bi bi-clipboard-check"></i> Assignments
            </a>
            <a class="nav-link" href="{{ route('admin.truck-availability.index') }}">
                <i class="bi bi-calendar-check"></i> Truck Availability
            </a>
            <a class="nav-link" href="{{ route('admin.collection-logs.index') }}">
                <i class="bi bi-clipboard-data"></i> Collection Logs
            </a>
            <a class="nav-link active" href="{{ route('admin.analytics.collections.index') }}">
                <i class="bi bi-graph-up"></i> Collection Analytics
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-file-text"></i> Reports <small>(Coming Soon)</small>
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-recycle"></i> Recycling <small>(Coming Soon)</small>
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-graph-up"></i> Collection Analytics
        </h1>
    </div>

    <!-- Date Range Selector -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-calendar-range"></i> Date Range
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.collections.index') }}" id="dateRangeForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-clockwise"></i> Update Dashboard
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Collections Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100" style="border-left: 4px solid var(--sweep-accent);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600;">
                                Total Collections
                            </h6>
                            <h2 class="mb-0" style="color: var(--sweep-accent); font-weight: 700;">
                                {{ number_format($totalCollections) }}
                            </h2>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px; background-color: rgba(79, 180, 162, 0.1);">
                            <i class="bi bi-clipboard-check" style="font-size: 1.5rem; color: var(--sweep-accent);"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-calendar3"></i> 
                            {{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion Rate Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100" style="border-left: 4px solid var(--sweep-primary);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600;">
                                Completion Rate
                            </h6>
                            <h2 class="mb-0" style="color: var(--sweep-primary); font-weight: 700;">
                                {{ number_format($completionRate, 1) }}%
                            </h2>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px; background-color: rgba(46, 139, 87, 0.1);">
                            <i class="bi bi-check-circle" style="font-size: 1.5rem; color: var(--sweep-primary);"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $completionRate }}%; background-color: var(--sweep-primary);"
                                 aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Completion Time Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100" style="border-left: 4px solid var(--sweep-secondary);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600;">
                                Avg Completion Time
                            </h6>
                            <h2 class="mb-0" style="color: var(--sweep-secondary); font-weight: 700;">
                                @if($avgCompletionTime > 0)
                                    {{ number_format($avgCompletionTime, 1) }}h
                                @else
                                    —
                                @endif
                            </h2>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px; background-color: rgba(244, 163, 0, 0.1);">
                            <i class="bi bi-clock-history" style="font-size: 1.5rem; color: var(--sweep-secondary);"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Hours per collection
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Issue Count Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; font-weight: 600;">
                                Issues Reported
                            </h6>
                            <h2 class="mb-0" style="color: #dc3545; font-weight: 700;">
                                {{ number_format($issueCount) }}
                            </h2>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px; background-color: rgba(220, 53, 69, 0.1);">
                            <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem; color: #dc3545;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-arrow-right"></i> 
                            <a href="{{ route('admin.collection-logs.issues.analysis') }}" class="text-decoration-none">
                                View Analysis
                            </a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Completion Trend Chart -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up-arrow"></i> Completion Trend
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="completionTrendChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Breakdown Chart -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart"></i> Status Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="statusBreakdownChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tables Row -->
    <div class="row g-4">
        <!-- Crew Performance Table -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> Crew Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div id="crewPerformanceLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="crewPerformanceTable" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Crew Member</th>
                                        <th class="text-center">Collections</th>
                                        <th class="text-center">Completion Rate</th>
                                        <th class="text-center">Issues</th>
                                    </tr>
                                </thead>
                                <tbody id="crewPerformanceBody">
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Route Performance Table -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-map"></i> Route Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div id="routePerformanceLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="routePerformanceTable" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Route</th>
                                        <th class="text-center">Collections</th>
                                        <th class="text-center">Completion Rate</th>
                                        <th class="text-center">Issues</th>
                                    </tr>
                                </thead>
                                <tbody id="routePerformanceBody">
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        // Store date range for AJAX requests
        const startDate = '{{ $startDate }}';
        const endDate = '{{ $endDate }}';
        
        // Initialize charts and load data
        document.addEventListener('DOMContentLoaded', function() {
            initializeCompletionTrendChart();
            initializeStatusBreakdownChart();
            loadCrewPerformance();
            loadRoutePerformance();
        });
        
        // Completion Trend Chart
        let completionTrendChart;
        function initializeCompletionTrendChart() {
            const ctx = document.getElementById('completionTrendChart').getContext('2d');
            
            fetch(`{{ route('admin.analytics.collections.completion-rates') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(result => {
                    const data = result.data;
                    
                    completionTrendChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(item => item.date_formatted),
                            datasets: [{
                                label: 'Completion Rate (%)',
                                data: data.map(item => item.completion_rate),
                                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--sweep-primary').trim(),
                                backgroundColor: 'rgba(46, 139, 87, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Completion Rate: ' + context.parsed.y.toFixed(1) + '%';
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
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading completion trend:', error);
                });
        }
        
        // Status Breakdown Chart
        let statusBreakdownChart;
        function initializeStatusBreakdownChart() {
            const ctx = document.getElementById('statusBreakdownChart').getContext('2d');
            
            fetch(`{{ route('admin.analytics.collections.status-breakdown') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(result => {
                    const data = result.data;
                    
                    const statusColors = {
                        'completed': '#2E8B57',
                        'incomplete': '#F4A300',
                        'issue_reported': '#dc3545',
                        'pending': '#6c757d'
                    };
                    
                    const statusLabels = {
                        'completed': 'Completed',
                        'incomplete': 'Incomplete',
                        'issue_reported': 'Issue Reported',
                        'pending': 'Pending'
                    };
                    
                    statusBreakdownChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(data).map(key => statusLabels[key] || key),
                            datasets: [{
                                data: Object.values(data),
                                backgroundColor: Object.keys(data).map(key => statusColors[key] || '#6c757d'),
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        usePointStyle: true
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading status breakdown:', error);
                });
        }
        
        // Load Crew Performance
        function loadCrewPerformance() {
            fetch(`{{ route('admin.analytics.collections.crew-performance') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(result => {
                    const data = result.data;
                    const tbody = document.getElementById('crewPerformanceBody');
                    tbody.innerHTML = '';
                    
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No data available</td></tr>';
                    } else {
                        data.forEach(crew => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td><i class="bi bi-person"></i> ${crew.user_name}</td>
                                <td class="text-center"><strong>${crew.total_collections}</strong></td>
                                <td class="text-center">
                                    <span class="badge ${crew.completion_rate >= 80 ? 'bg-success' : crew.completion_rate >= 60 ? 'bg-warning' : 'bg-danger'}">
                                        ${crew.completion_rate.toFixed(1)}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    ${crew.issues_reported > 0 ? `<span class="badge bg-danger">${crew.issues_reported}</span>` : '<span class="text-muted">—</span>'}
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                    
                    document.getElementById('crewPerformanceLoading').style.display = 'none';
                    document.getElementById('crewPerformanceTable').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading crew performance:', error);
                    document.getElementById('crewPerformanceLoading').innerHTML = 
                        '<div class="alert alert-danger">Error loading data</div>';
                });
        }
        
        // Load Route Performance
        function loadRoutePerformance() {
            fetch(`{{ route('admin.analytics.collections.route-performance') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(result => {
                    const data = result.data;
                    const tbody = document.getElementById('routePerformanceBody');
                    tbody.innerHTML = '';
                    
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No data available</td></tr>';
                    } else {
                        data.forEach(route => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <strong>${route.route_name}</strong><br>
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> ${route.route.zone}</small>
                                </td>
                                <td class="text-center"><strong>${route.total_collections}</strong></td>
                                <td class="text-center">
                                    <span class="badge ${route.completion_rate >= 80 ? 'bg-success' : route.completion_rate >= 60 ? 'bg-warning' : 'bg-danger'}">
                                        ${route.completion_rate.toFixed(1)}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    ${route.issues_reported > 0 ? `<span class="badge bg-danger">${route.issues_reported}</span>` : '<span class="text-muted">—</span>'}
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                    
                    document.getElementById('routePerformanceLoading').style.display = 'none';
                    document.getElementById('routePerformanceTable').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading route performance:', error);
                    document.getElementById('routePerformanceLoading').innerHTML = 
                        '<div class="alert alert-danger">Error loading data</div>';
                });
        }
    </script>
    @endpush
</x-app-layout>
