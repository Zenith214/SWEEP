@section('title', 'Issue Analysis')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="collection-logs" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h2 mb-1">
                <i class="bi bi-exclamation-triangle"></i> Issue Analysis
            </h1>
            <p class="text-muted mb-0">
                Identify routes with recurring issues and analyze problem patterns
            </p>
        </div>

        <!-- Date Range Filter -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-range"></i> Date Range
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.collection-logs.issues.analysis') }}" id="dateRangeForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="threshold" class="form-label">Min Issues</label>
                            <input type="number" class="form-control" id="threshold" name="threshold" 
                                   value="{{ $threshold }}" min="1">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Issue Type Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart"></i> Issue Type Breakdown
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($issuesByType) > 0)
                            <canvas id="issueTypeChart" style="max-height: 400px;"></canvas>
                            
                            <!-- Issue Type List -->
                            <div class="mt-4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Issue Type</th>
                                            <th class="text-end">Count</th>
                                            <th class="text-end">Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalIssues = array_sum(array_column($issuesByType, 'count'));
                                        @endphp
                                        @foreach($issuesByType as $type => $data)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-circle-fill" style="color: {{ $loop->index === 0 ? '#dc3545' : ($loop->index === 1 ? '#fd7e14' : ($loop->index === 2 ? '#ffc107' : ($loop->index === 3 ? '#0dcaf0' : '#6c757d'))) }}; font-size: 0.5rem;"></i>
                                                    {{ $data['label'] }}
                                                </td>
                                                <td class="text-end">
                                                    <strong>{{ $data['count'] }}</strong>
                                                </td>
                                                <td class="text-end">
                                                    {{ $totalIssues > 0 ? round(($data['count'] / $totalIssues) * 100, 1) : 0 }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle" style="font-size: 4rem; color: var(--sweep-primary); opacity: 0.3;"></i>
                                <h5 class="mt-3 text-muted">No Issues Reported</h5>
                                <p class="text-muted">No issues were reported during the selected period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Routes with Recurring Issues -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-map"></i> Routes with Recurring Issues
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($routesWithIssues->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Route</th>
                                            <th>Zone</th>
                                            <th class="text-center">Issues</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($routesWithIssues as $routeData)
                                            <tr>
                                                <td>
                                                    <strong>{{ $routeData->route_name }}</strong>
                                                </td>
                                                <td>
                                                    <i class="bi bi-geo-alt"></i> {{ $routeData->route_zone }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger fs-6">
                                                        {{ $routeData->issue_count }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.collection-logs.route-issues', $routeData->route_id) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle" style="font-size: 4rem; color: var(--sweep-primary); opacity: 0.3;"></i>
                                <h5 class="mt-3 text-muted">No Recurring Issues</h5>
                                <p class="text-muted">No routes have {{ $threshold }} or more issues during the selected period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        @if(count($issuesByType) > 0 || $routesWithIssues->count() > 0)
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        @php
                            $totalIssuesCount = array_sum(array_column($issuesByType, 'count'));
                        @endphp
                        <div class="col-md-3">
                            <div class="stat-card p-3">
                                <div class="stat-value text-danger" style="font-size: 2.5rem; font-weight: bold;">
                                    {{ $totalIssuesCount }}
                                </div>
                                <div class="stat-label text-muted">Total Issues</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card p-3">
                                <div class="stat-value text-warning" style="font-size: 2.5rem; font-weight: bold;">
                                    {{ $routesWithIssues->count() }}
                                </div>
                                <div class="stat-label text-muted">Affected Routes</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card p-3">
                                <div class="stat-value text-info" style="font-size: 2.5rem; font-weight: bold;">
                                    {{ count(array_filter(array_column($issuesByType, 'count'))) }}
                                </div>
                                <div class="stat-label text-muted">Issue Types</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card p-3">
                                <div class="stat-value" style="font-size: 2.5rem; font-weight: bold; color: var(--sweep-accent);">
                                    {{ $routesWithIssues->count() > 0 ? round($totalIssuesCount / $routesWithIssues->count(), 1) : 0 }}
                                </div>
                                <div class="stat-label text-muted">Avg Issues/Route</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        @if(count($issuesByType) > 0)
            // Issue Type Chart
            const issueTypeCtx = document.getElementById('issueTypeChart').getContext('2d');
            const issueTypeData = @json($issuesByType);
            const issueTypeLabels = Object.values(issueTypeData).map(item => item.label);
            const issueTypeCounts = Object.values(issueTypeData).map(item => item.count);

            new Chart(issueTypeCtx, {
                type: 'bar',
                data: {
                    labels: issueTypeLabels,
                    datasets: [{
                        label: 'Number of Issues',
                        data: issueTypeCounts,
                        backgroundColor: [
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(253, 126, 20, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(13, 202, 240, 0.8)',
                            'rgba(108, 117, 125, 0.8)',
                            'rgba(13, 148, 136, 0.8)'
                        ],
                        borderColor: [
                            'rgba(220, 53, 69, 1)',
                            'rgba(253, 126, 20, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(13, 202, 240, 1)',
                            'rgba(108, 117, 125, 1)',
                            'rgba(13, 148, 136, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
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

    @push('styles')
    <style>
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
    @endpush
</x-app-layout>
