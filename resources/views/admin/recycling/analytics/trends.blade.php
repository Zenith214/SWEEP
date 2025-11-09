@section('title', 'Trend Analysis')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-analytics" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-graph-up"></i> Trend Analysis
                </h1>
                <p class="text-muted mb-0">Track recycling trends over time</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.recycling.analytics.trends') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                               value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" 
                               value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Interval</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="interval" value="daily" 
                                       id="interval_daily" {{ $interval === 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label" for="interval_daily">
                                    Daily
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="interval" value="weekly" 
                                       id="interval_weekly" {{ $interval === 'weekly' ? 'checked' : '' }}>
                                <label class="form-check-label" for="interval_weekly">
                                    Weekly
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="interval" value="monthly" 
                                       id="interval_monthly" {{ $interval === 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label" for="interval_monthly">
                                    Monthly
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Apply Filters
                        </button>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Material Types (Multi-Select)</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(['plastic', 'paper', 'glass', 'metal', 'cardboard', 'organic'] as $material)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="materials[]" 
                                           value="{{ $material }}" id="material_{{ $material }}"
                                           {{ in_array($material, request('materials', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="material_{{ $material }}">
                                        {{ ucfirst($material) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
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
                <a class="nav-link" href="{{ route('admin.recycling.analytics.materials', request()->query()) }}">
                    <i class="bi bi-box-seam"></i> Materials
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.recycling.analytics.zones', request()->query()) }}">
                    <i class="bi bi-geo-alt"></i> Zones
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link active" href="{{ route('admin.recycling.analytics.trends', request()->query()) }}">
                    <i class="bi bi-graph-up"></i> Trends
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.recycling.analytics.crew', request()->query()) }}">
                    <i class="bi bi-people"></i> Crew Performance
                </a>
            </li>
        </ul>

        @if(count($trendData) > 0)
            <!-- Line Chart -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up"></i> Weight Over Time ({{ ucfirst($interval) }} Intervals)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="trendLineChart"></canvas>
                </div>
            </div>

            <!-- Data Table with Percentage Changes -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-table"></i> Detailed Trend Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th class="text-end">Total Weight (kg)</th>
                                    <th class="text-end">Number of Logs</th>
                                    <th class="text-end">Avg per Log (kg)</th>
                                    <th class="text-end">Change from Previous</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trendData as $index => $period)
                                    <tr>
                                        <td>
                                            <strong>{{ $period['period_label'] }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <strong class="fs-6">{{ number_format($period['total_weight'], 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary">{{ number_format($period['log_count']) }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($period['average_per_log'], 2) }}
                                        </td>
                                        <td class="text-end">
                                            @if($index > 0 && isset($period['percentage_change']))
                                                @if($period['percentage_change'] > 0)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-arrow-up"></i>
                                                        +{{ number_format($period['percentage_change'], 1) }}%
                                                    </span>
                                                @elseif($period['percentage_change'] < 0)
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-arrow-down"></i>
                                                        {{ number_format($period['percentage_change'], 1) }}%
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-dash"></i>
                                                        0%
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total / Average</th>
                                    <th class="text-end">
                                        <strong class="fs-6">{{ number_format(array_sum(array_column($trendData, 'total_weight')), 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <span class="badge bg-secondary">{{ number_format(array_sum(array_column($trendData, 'log_count'))) }}</span>
                                    </th>
                                    <th class="text-end">
                                        @php
                                            $totalWeight = array_sum(array_column($trendData, 'total_weight'));
                                            $totalLogs = array_sum(array_column($trendData, 'log_count'));
                                            $overallAvg = $totalLogs > 0 ? $totalWeight / $totalLogs : 0;
                                        @endphp
                                        {{ number_format($overallAvg, 2) }}
                                    </th>
                                    <th class="text-end">-</th>
                                </tr>
                            </tfoot>
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
                        No recycling logs found for the selected date range and filters. Try adjusting your filters.
                    </p>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
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
        @if(count($trendData) > 0)
        // Line Chart
        const trendLabels = @json(array_column($trendData, 'period_label'));
        const trendWeights = @json(array_column($trendData, 'total_weight'));
        
        const lineCtx = document.getElementById('trendLineChart').getContext('2d');
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
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#2E8B57',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
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
                                return 'Weight: ' + context.parsed.y.toFixed(2) + ' kg';
                            }
                        }
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
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>
