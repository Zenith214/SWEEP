@section('title', 'Zone Performance')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-analytics" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-geo-alt"></i> Zone Performance
                </h1>
                <p class="text-muted mb-0">Analyze recycling performance by zone</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.recycling.analytics.zones') }}" method="GET" class="row g-3 align-items-end">
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
                    <div class="col-md-4">
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
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Apply Filters
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
                <a class="nav-link" href="{{ route('admin.recycling.analytics.materials', request()->query()) }}">
                    <i class="bi bi-box-seam"></i> Materials
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link active" href="{{ route('admin.recycling.analytics.zones', request()->query()) }}">
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

        @if(count($zonePerformance) > 0)
            <!-- Average Indicator -->
            <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle fs-4 me-3"></i>
                    <div>
                        <strong>Average Weight per Zone:</strong>
                        {{ number_format($averageWeight, 2) }} kg
                        <span class="text-muted ms-2">
                            (Zones above this average are highlighted)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Zone Performance Table -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-table"></i> Zone Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Zone / Route</th>
                                    <th class="text-end">Total Weight (kg)</th>
                                    <th class="text-end">Number of Logs</th>
                                    <th class="text-end">Avg per Log (kg)</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zonePerformance as $zone)
                                    <tr class="{{ $zone['above_average'] ? 'table-success' : '' }}">
                                        <td>
                                            <div>
                                                <strong>{{ $zone['route_name'] }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i> Zone: {{ $zone['zone'] }}
                                                </small>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <strong class="fs-5">{{ number_format($zone['total_weight'], 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary fs-6">{{ number_format($zone['log_count']) }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($zone['average_per_log'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if($zone['above_average'])
                                                <span class="badge bg-success">
                                                    <i class="bi bi-arrow-up-circle"></i> Above Average
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-dash-circle"></i> Below Average
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total / Average</th>
                                    <th class="text-end">
                                        <strong class="fs-5">{{ number_format(array_sum(array_column($zonePerformance, 'total_weight')), 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <span class="badge bg-secondary fs-6">{{ number_format(array_sum(array_column($zonePerformance, 'log_count'))) }}</span>
                                    </th>
                                    <th class="text-end">
                                        {{ number_format($averageWeight, 2) }}
                                    </th>
                                    <th class="text-center">-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="bi bi-info-circle"></i> Legend
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white px-3 py-2 rounded me-3">
                                    <i class="bi bi-arrow-up-circle"></i>
                                </div>
                                <div>
                                    <strong>Above Average</strong>
                                    <div class="small text-muted">
                                        Zones with total weight above {{ number_format($averageWeight, 2) }} kg
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary text-white px-3 py-2 rounded me-3">
                                    <i class="bi bi-dash-circle"></i>
                                </div>
                                <div>
                                    <strong>Below Average</strong>
                                    <div class="small text-muted">
                                        Zones with total weight below {{ number_format($averageWeight, 2) }} kg
                                    </div>
                                </div>
                            </div>
                        </div>
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
        /* Above Average Row Highlighting */
        .table-success {
            background-color: rgba(46, 139, 87, 0.1) !important;
        }

        .table-success:hover {
            background-color: rgba(46, 139, 87, 0.2) !important;
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
</x-app-layout>
