@section('title', 'Material Analysis')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-analytics" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-box-seam"></i> Material Analysis
                </h1>
                <p class="text-muted mb-0">Detailed breakdown of recycling materials</p>
            </div>
        </div>

        <!-- Date Range Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.recycling.analytics.materials') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                               value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" 
                               value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Apply Date Range
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.recycling.analytics.dashboard', request()->query()) }}">
                    <i class="bi bi-speedometer2"></i> Overview
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link active" href="{{ route('admin.recycling.analytics.materials', request()->query()) }}">
                    <i class="bi bi-box-seam"></i> Materials
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.recycling.analytics.zones', request()->query()) }}">
                    <i class="bi bi-geo-alt"></i> Zones
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.recycling.analytics.trends', request()->query()) }}">
                    <i class="bi bi-graph-up"></i> Trends
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.recycling.analytics.crew', request()->query()) }}">
                    <i class="bi bi-people"></i> Crew Performance
                </a>
            </li>
        </ul>

        @if(count($materialTotals) > 0)
            <!-- Comparison with Previous Period -->
            @if(isset($comparison) && $comparison['has_previous_data'])
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                        <div>
                            <strong>Period Comparison:</strong>
                            @if($comparison['percentage_change'] > 0)
                                <span class="text-success">
                                    <i class="bi bi-arrow-up"></i>
                                    {{ number_format(abs($comparison['percentage_change']), 1) }}% increase
                                </span>
                            @elseif($comparison['percentage_change'] < 0)
                                <span class="text-danger">
                                    <i class="bi bi-arrow-down"></i>
                                    {{ number_format(abs($comparison['percentage_change']), 1) }}% decrease
                                </span>
                            @else
                                <span class="text-muted">
                                    <i class="bi bi-dash"></i>
                                    No change
                                </span>
                            @endif
                            compared to previous period
                            ({{ number_format($comparison['previous_weight'], 2) }} kg vs {{ number_format($comparison['current_weight'], 2) }} kg)
                        </div>
                    </div>
                </div>
            @endif

            <!-- Material Totals Table -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-table"></i> Material Totals
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
                                        <td class="text-end">
                                            <strong class="fs-5">{{ number_format($material['total_weight'], 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <div class="progress me-2" style="width: 150px; height: 24px;">
                                                    <div class="progress-bar material-{{ $material['material_type'] }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $material['percentage'] }}%">
                                                    </div>
                                                </div>
                                                <span class="fw-bold">{{ number_format($material['percentage'], 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary fs-6">{{ number_format($material['log_count']) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">
                                        <strong class="fs-5">{{ number_format(array_sum(array_column($materialTotals, 'total_weight')), 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong>100%</strong>
                                    </th>
                                    <th class="text-end">
                                        <span class="badge bg-secondary fs-6">{{ number_format(array_sum(array_column($materialTotals, 'log_count'))) }}</span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Horizontal Bar Chart -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-fill"></i> Materials by Weight (Sorted Descending)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="materialBarChart"></canvas>
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
        }

        .nav-tabs .nav-link.active {
            color: var(--sweep-primary);
            border-bottom: 3px solid var(--sweep-primary);
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        @if(count($materialTotals) > 0)
        // Horizontal Bar Chart
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
        
        const barCtx = document.getElementById('materialBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: materialLabels.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
                datasets: [{
                    label: 'Total Weight (kg)',
                    data: materialWeights,
                    backgroundColor: materialLabels.map(m => materialColors[m]),
                    borderWidth: 0
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Weight: ' + context.parsed.x.toFixed(2) + ' kg';
                            }
                        }
                    }
                },
                scales: {
                    x: {
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
