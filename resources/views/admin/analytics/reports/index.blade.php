@section('title', 'Report Analytics Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="report-analytics" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Report Analytics Dashboard</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.analytics.reports.location') }}" class="btn btn-outline-primary">
                <i class="bi bi-geo-alt"></i> Location Analysis
            </a>
            <a href="{{ route('admin.analytics.reports.type') }}" class="btn btn-outline-primary">
                <i class="bi bi-pie-chart"></i> Type Analysis
            </a>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.analytics.reports.index') }}" method="GET" class="row g-3 align-items-end">
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

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Reports</p>
                            <h2 class="mb-0">{{ number_format($metrics['total_reports']) }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="bi bi-file-text fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Pending Reports</p>
                            <h2 class="mb-0">{{ number_format($metrics['pending_reports']) }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="bi bi-clock fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Avg Resolution Time</p>
                            <h2 class="mb-0">{{ number_format($metrics['average_resolution_time'], 1) }}<small class="fs-6 text-muted">h</small></h2>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded p-3">
                            <i class="bi bi-hourglass-split fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Resolution Rate</p>
                            <h2 class="mb-0">{{ number_format($metrics['resolution_rate'], 1) }}<small class="fs-6 text-muted">%</small></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Report Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Report Submission Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="reportTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Type Distribution Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Distribution and Resolution Time -->
    <div class="row mb-4">
        <!-- Status Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusDistributionChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Resolution Time by Type -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Resolution Time by Type</h5>
                </div>
                <div class="card-body">
                    @if(count($resolutionTimeByType) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Report Type</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">Avg Time (hours)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resolutionTimeByType as $item)
                                        <tr>
                                            <td>{{ $item['type_label'] }}</td>
                                            <td class="text-end">{{ number_format($item['count']) }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-info">{{ number_format($item['average_hours'], 1) }}h</span>
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
        </div>
    </div>

    <!-- Overdue Reports -->
    @if($overdueReports->isNotEmpty())
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> Overdue Reports (48+ hours)</h5>
                <span class="badge bg-warning">{{ $overdueReports->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Resident</th>
                                <th>Age</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueReports as $report)
                                <tr>
                                    <td><strong>{{ $report->reference_number }}</strong></td>
                                    <td>{{ \App\Models\Report::REPORT_TYPES[$report->report_type] ?? $report->report_type }}</td>
                                    <td><i class="bi bi-geo-alt"></i> {{ Str::limit($report->location, 30) }}</td>
                                    <td>{{ $report->resident->name }}</td>
                                    <td>
                                        <span class="text-danger">
                                            {{ $report->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'in_progress' => 'primary',
                                            ];
                                            $color = $statusColors[$report->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ \App\Models\Report::STATUSES[$report->status] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        // Report Trend Chart
        fetch('{{ route('admin.analytics.reports.status-trend') }}?date_from={{ $startDate }}&date_to={{ $endDate }}')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('reportTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Reports',
                            data: data.data,
                            borderColor: '#4FB4A2',
                            backgroundColor: 'rgba(79, 180, 162, 0.1)',
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
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            });

        // Type Distribution Chart
        fetch('{{ route('admin.analytics.reports.type-distribution') }}?date_from={{ $startDate }}&date_to={{ $endDate }}')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('typeDistributionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            backgroundColor: [
                                '#2E8B57',
                                '#F4A300',
                                '#4FB4A2',
                                '#6C757D'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            });

        // Status Distribution Chart
        fetch('{{ route('admin.analytics.reports.status-trend') }}?date_from={{ $startDate }}&date_to={{ $endDate }}')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('statusDistributionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Reports',
                            data: data.data,
                            backgroundColor: [
                                '#FFC107',
                                '#0D6EFD',
                                '#198754',
                                '#6C757D'
                            ]
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
            });
    </script>
    @endpush
</x-app-layout>
