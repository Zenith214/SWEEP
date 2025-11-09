@section('title', 'Type Analysis')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="report-analytics" />
    </x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.analytics.reports.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Type Analysis</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.analytics.reports.location') }}" class="btn btn-outline-primary">
                <i class="bi bi-geo-alt"></i> Location Analysis
            </a>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.analytics.reports.type') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">From Date</label>
                    <input 
                        type="date" 
                        name="date_from" 
                        id="date_from"
                        class="form-control"
                        value="{{ $startDate }}"
                    >
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">To Date</label>
                    <input 
                        type="date" 
                        name="date_to" 
                        id="date_to"
                        class="form-control"
                        value="{{ $endDate }}"
                    >
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Apply Date Range
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(empty($reportsByType))
        <div class="card">
            <div class="card-body">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <p class="mb-0">No reports found for the selected date range</p>
                </div>
            </div>
        </div>
    @else
        <!-- Type Distribution Chart -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Type Distribution (Pie Chart)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="typeDistributionPieChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Type Distribution (Bar Chart)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="typeDistributionBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Type Statistics Table -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-table"></i> Report Type Statistics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Report Type</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Percentage</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $typeColors = [
                                    'missed_pickup' => 'success',
                                    'uncollected_waste' => 'warning',
                                    'illegal_dumping' => 'danger',
                                    'other' => 'secondary'
                                ];
                            @endphp
                            @foreach($reportsByType as $typeData)
                                <tr>
                                    <td>
                                        <i class="bi bi-file-text"></i>
                                        <strong>{{ $typeData['type_label'] }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $typeColors[$typeData['type']] ?? 'primary' }} fs-6">
                                            {{ number_format($typeData['count']) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <div class="progress me-2" style="width: 150px; height: 24px;">
                                                <div 
                                                    class="progress-bar bg-{{ $typeColors[$typeData['type']] ?? 'primary' }}" 
                                                    role="progressbar" 
                                                    style="width: {{ $typeData['percentage'] }}%"
                                                    aria-valuenow="{{ $typeData['percentage'] }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100"
                                                >
                                                </div>
                                            </div>
                                            <span class="fw-bold">{{ number_format($typeData['percentage'], 1) }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a 
                                            href="{{ route('admin.reports.index', ['report_type' => $typeData['type'], 'date_from' => $startDate, 'date_to' => $endDate]) }}" 
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            <i class="bi bi-eye"></i> View Reports
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Average Resolution Time by Type -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Average Resolution Time by Type</h5>
            </div>
            <div class="card-body">
                @if(count($resolutionTimeByType) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th class="text-end">Resolved Count</th>
                                    <th class="text-end">Average Resolution Time</th>
                                    <th class="text-end">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resolutionTimeByType as $item)
                                    @php
                                        $typeColors = [
                                            'missed_pickup' => 'success',
                                            'uncollected_waste' => 'warning',
                                            'illegal_dumping' => 'danger',
                                            'other' => 'secondary'
                                        ];
                                        $color = $typeColors[$item['type']] ?? 'primary';
                                        
                                        // Performance indicator based on resolution time
                                        $performanceClass = 'success';
                                        $performanceIcon = 'check-circle';
                                        $performanceText = 'Excellent';
                                        
                                        if ($item['average_hours'] > 72) {
                                            $performanceClass = 'danger';
                                            $performanceIcon = 'x-circle';
                                            $performanceText = 'Needs Improvement';
                                        } elseif ($item['average_hours'] > 48) {
                                            $performanceClass = 'warning';
                                            $performanceIcon = 'exclamation-circle';
                                            $performanceText = 'Fair';
                                        } elseif ($item['average_hours'] > 24) {
                                            $performanceClass = 'info';
                                            $performanceIcon = 'info-circle';
                                            $performanceText = 'Good';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-text text-{{ $color }}"></i>
                                            <strong>{{ $item['type_label'] }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-{{ $color }}">{{ number_format($item['count']) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-info fs-6">{{ number_format($item['average_hours'], 1) }} hours</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-{{ $performanceClass }}">
                                                <i class="bi bi-{{ $performanceIcon }}"></i> {{ $performanceText }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        <p class="mb-0">No resolved reports in this date range</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        @if(!empty($reportsByType))
        // Prepare data for charts
        const typeLabels = @json(array_column($reportsByType, 'type_label'));
        const typeCounts = @json(array_column($reportsByType, 'count'));
        const typeColors = [
            '#2E8B57',  // Missed Pickup - Green
            '#F4A300',  // Uncollected Waste - Orange/Yellow
            '#DC3545',  // Illegal Dumping - Red
            '#6C757D'   // Other - Gray
        ];

        // Type Distribution Pie Chart
        const pieCtx = document.getElementById('typeDistributionPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeCounts,
                    backgroundColor: typeColors
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
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Type Distribution Bar Chart
        const barCtx = document.getElementById('typeDistributionBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: typeLabels,
                datasets: [{
                    label: 'Number of Reports',
                    data: typeCounts,
                    backgroundColor: typeColors
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
                            stepSize: 1
                        }
                    }
                }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>
