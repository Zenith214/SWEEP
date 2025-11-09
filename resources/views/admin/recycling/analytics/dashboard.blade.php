@section('title', 'Recycling Analytics Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-analytics" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-bar-chart-line"></i> Recycling Analytics Dashboard
                </h1>
                <p class="text-muted mb-0">Comprehensive recycling performance insights</p>
            </div>
        </div>

        <!-- Date Range Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.recycling.analytics.dashboard') }}" method="GET" class="row g-3 align-items-end" role="search" aria-label="Filter analytics by date range">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date" 
                               class="form-control" 
                               value="{{ $startDate }}"
                               aria-label="Select start date for analytics">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date" 
                               class="form-control" 
                               value="{{ $endDate }}"
                               aria-label="Select end date for analytics">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100" aria-label="Apply date range filter">
                            <i class="bi bi-funnel" aria-hidden="true"></i> Apply Date Range
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab Navigation -->
        <nav aria-label="Analytics navigation tabs">
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" 
                       href="{{ route('admin.recycling.analytics.dashboard', request()->query()) }}"
                       aria-current="page"
                       aria-label="Overview analytics tab">
                        <i class="bi bi-speedometer2" aria-hidden="true"></i> Overview
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" 
                       href="{{ route('admin.recycling.analytics.materials', request()->query()) }}"
                       aria-label="Materials analytics tab">
                        <i class="bi bi-box-seam" aria-hidden="true"></i> Materials
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" 
                       href="{{ route('admin.recycling.analytics.zones', request()->query()) }}"
                       aria-label="Zones analytics tab">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i> Zones
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" 
                       href="{{ route('admin.recycling.analytics.trends', request()->query()) }}"
                       aria-label="Trends analytics tab">
                        <i class="bi bi-graph-up" aria-hidden="true"></i> Trends
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" 
                       href="{{ route('admin.recycling.analytics.crew', request()->query()) }}"
                       aria-label="Crew performance analytics tab">
                        <i class="bi bi-people" aria-hidden="true"></i> Crew Performance
                    </a>
                </li>
            </ul>
        </nav>

        @if($metrics['total_logs'] > 0)
            <!-- Key Metrics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card text-white" style="background-color: var(--sweep-primary);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-white-50">Total Weight</h6>
                                    <h2 class="card-title mb-0">{{ number_format($metrics['total_weight'], 0) }}</h2>
                                    <small class="text-white-50">kilograms</small>
                                </div>
                                <i class="bi bi-box-seam fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white" style="background-color: var(--sweep-accent);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-white-50">Total Logs</h6>
                                    <h2 class="card-title mb-0">{{ number_format($metrics['total_logs']) }}</h2>
                                    <small class="text-white-50">collection logs</small>
                                </div>
                                <i class="bi bi-clipboard-data fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white" style="background-color: var(--sweep-secondary);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-white-50">Avg per Log</h6>
                                    <h2 class="card-title mb-0">{{ number_format($metrics['average_per_log'], 1) }}</h2>
                                    <small class="text-white-50">kg per log</small>
                                </div>
                                <i class="bi bi-calculator fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-white-50">Recycling Rate</h6>
                                    <h2 class="card-title mb-0">{{ number_format($metrics['recycling_rate'], 1) }}</h2>
                                    <small class="text-white-50">kg per zone</small>
                                </div>
                                <i class="bi bi-graph-up-arrow fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Material Breakdown Pie Chart -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="bi bi-pie-chart"></i> Material Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="materialBreakdownChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Weekly Trend Line Chart -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up"></i> Weekly Trend (Last 12 Weeks)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="weeklyTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target Progress Section -->
            @if(count($targets) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-bullseye"></i> Target Progress (Current Month)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($targets as $target)
                                @php
                                    $progressClass = 'secondary';
                                    if ($target['is_achieved']) {
                                        $progressClass = 'success';
                                    } elseif ($target['progress'] >= 80) {
                                        $progressClass = 'warning';
                                    }
                                @endphp
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>
                                                @if($target['material_type'])
                                                    {{ ucfirst($target['material_type']) }}
                                                @else
                                                    Total Recyclables
                                                @endif
                                            </strong>
                                            @if($target['is_achieved'])
                                                <i class="bi bi-check-circle-fill text-success ms-2"></i>
                                            @endif
                                        </div>
                                        <span class="text-muted">
                                            {{ number_format($target['current_weight'], 0) }} / {{ number_format($target['target_weight'], 0) }} kg
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-{{ $progressClass }}" 
                                             role="progressbar" 
                                             style="width: {{ min($target['progress'], 100) }}%"
                                             aria-valuenow="{{ $target['progress'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ number_format($target['progress'], 0) }}%
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Stats Table -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-table"></i> Material Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Material Type</th>
                                    <th class="text-end">Total Weight (kg)</th>
                                    <th class="text-end">Percentage</th>
                                    <th class="text-end">Number of Logs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($materialTotals as $material)
                                    <tr>
                                        <td>
                                            <span class="badge material-badge material-{{ $material['material_type'] }}">
                                                {{ ucfirst($material['material_type']) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($material['total_weight'], 2) }}</td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <div class="progress me-2" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar material-{{ $material['material_type'] }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $material['percentage'] }}%">
                                                    </div>
                                                </div>
                                                <span>{{ number_format($material['percentage'], 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-end">{{ number_format($material['log_count']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox" style="font-size: 5rem; color: var(--sweep-accent); opacity: 0.5;"></i>
                    <h3 class="mt-4 mb-2">No Data Available</h3>
                    <p class="text-muted mb-4">
                        No recycling logs found for the selected date range. Try adjusting your date filters.
                    </p>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        /* Material Type Badge Colors */
        .material-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }

        .material-plastic {
            background-color: #3B82F6;
            color: white;
        }

        .material-paper {
            background-color: #92400E;
            color: white;
        }

        .material-glass {
            background-color: #10B981;
            color: white;
        }

        .material-metal {
            background-color: #6B7280;
            color: white;
        }

        .material-cardboard {
            background-color: #D97706;
            color: white;
        }

        .material-organic {
            background-color: #84CC16;
            color: white;
        }

        /* Progress Bar Colors */
        .progress-bar.material-plastic {
            background-color: #3B82F6;
        }

        .progress-bar.material-paper {
            background-color: #92400E;
        }

        .progress-bar.material-glass {
            background-color: #10B981;
        }

        .progress-bar.material-metal {
            background-color: #6B7280;
        }

        .progress-bar.material-cardboard {
            background-color: #D97706;
        }

        .progress-bar.material-organic {
            background-color: #84CC16;
        }

        /* Tab Styling */
        .nav-tabs .nav-link {
            color: var(--sweep-text);
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-tabs .nav-link.active {
            color: var(--sweep-primary);
            border-bottom: 3px solid var(--sweep-primary);
        }

        /* Touch-friendly buttons (min 44px) */
        .btn {
            min-height: 44px;
            min-width: 44px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .nav-tabs .nav-link {
                white-space: nowrap;
                font-size: 0.9rem;
            }

            .col-md-3, .col-md-4 {
                width: 100%;
            }

            .material-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            /* Stack metric cards on mobile */
            .row.g-4 {
                gap: 1rem !important;
            }
        }

        @media (max-width: 576px) {
            .card-title {
                font-size: 1.5rem;
            }

            .fs-1 {
                font-size: 2rem !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        @if($metrics['total_logs'] > 0)
        // Material Breakdown Pie Chart
        const materialLabels = @json(array_column($materialTotals, 'material_type'));
        const materialWeights = @json(array_column($materialTotals, 'total_weight'));
        const materialColors = {
            'plastic': '#3B82F6',
            'paper': '#92400E',
            'glass': '#10B981',
            'metal': '#6B7280',
            'cardboard': '#D97706',
            'organic': '#84CC16'
        };
        
        const pieCanvas = document.getElementById('materialBreakdownChart');
        pieCanvas.setAttribute('role', 'img');
        pieCanvas.setAttribute('aria-label', 'Pie chart showing material breakdown by weight');
        
        const pieCtx = pieCanvas.getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: materialLabels.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
                datasets: [{
                    data: materialWeights,
                    backgroundColor: materialLabels.map(m => materialColors[m])
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return label + ': ' + value.toFixed(2) + ' kg';
                            }
                        }
                    }
                }
            }
        });

        // Weekly Trend Line Chart
        const trendLabels = @json(array_column($weeklyTrend, 'period_label'));
        const trendWeights = @json(array_column($weeklyTrend, 'total_weight'));
        
        const lineCanvas = document.getElementById('weeklyTrendChart');
        lineCanvas.setAttribute('role', 'img');
        lineCanvas.setAttribute('aria-label', 'Line chart showing weekly recycling weight trends over the last 12 weeks');
        
        const lineCtx = lineCanvas.getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Total Weight (kg)',
                    data: trendWeights,
                    borderColor: '#2E8B57',
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
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' kg';
                            }
                        }
                    }
                }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>
