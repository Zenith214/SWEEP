@section('title', 'Crew Performance')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-analytics" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-people"></i> Crew Performance
                </h1>
                <p class="text-muted mb-0">Analyze individual crew member recycling performance</p>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.recycling.analytics.crew') }}" method="GET" class="row g-3 align-items-end">
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
                <a class="nav-link" href="{{ route('admin.recycling.analytics.trends', request()->query()) }}">
                    <i class="bi bi-graph-up"></i> Trends
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link active" href="{{ route('admin.recycling.analytics.crew', request()->query()) }}">
                    <i class="bi bi-people"></i> Crew Performance
                </a>
            </li>
        </ul>

        @if(count($crewPerformance) > 0)
            <!-- Top Performer Highlight -->
            @if(isset($crewPerformance[0]))
                <div class="card mb-4" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);">
                    <div class="card-body text-white">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="bi bi-trophy-fill" style="font-size: 4rem; opacity: 0.8;"></i>
                            </div>
                            <div class="col">
                                <h4 class="mb-1">
                                    <i class="bi bi-star-fill"></i> Top Performer
                                </h4>
                                <h2 class="mb-2">{{ $crewPerformance[0]['crew_member_name'] }}</h2>
                                <div class="row g-3">
                                    <div class="col-auto">
                                        <div class="small opacity-75">Total Weight</div>
                                        <div class="fs-5 fw-bold">{{ number_format($crewPerformance[0]['total_weight'], 2) }} kg</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="small opacity-75">Total Logs</div>
                                        <div class="fs-5 fw-bold">{{ number_format($crewPerformance[0]['log_count']) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="small opacity-75">Average per Log</div>
                                        <div class="fs-5 fw-bold">{{ number_format($crewPerformance[0]['average_per_log'], 2) }} kg</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Crew Ranking Table -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ol"></i> Crew Member Rankings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 80px;">Rank</th>
                                    <th>Crew Member</th>
                                    <th class="text-end">Total Weight (kg)</th>
                                    <th class="text-end">Number of Logs</th>
                                    <th class="text-end">Avg per Log (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($crewPerformance as $index => $crew)
                                    <tr class="{{ $index === 0 ? 'table-warning' : '' }}">
                                        <td class="text-center">
                                            @if($index === 0)
                                                <span class="badge" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); font-size: 1.2rem; padding: 0.5rem 0.75rem;">
                                                    <i class="bi bi-trophy-fill"></i> #1
                                                </span>
                                            @elseif($index === 1)
                                                <span class="badge bg-secondary fs-6" style="background: linear-gradient(135deg, #C0C0C0 0%, #A8A8A8 100%) !important;">
                                                    #2
                                                </span>
                                            @elseif($index === 2)
                                                <span class="badge bg-secondary fs-6" style="background: linear-gradient(135deg, #CD7F32 0%, #B87333 100%) !important;">
                                                    #3
                                                </span>
                                            @else
                                                <span class="badge bg-secondary fs-6">
                                                    #{{ $index + 1 }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle fs-4 me-2 text-muted"></i>
                                                <div>
                                                    <strong>{{ $crew['crew_member_name'] }}</strong>
                                                    @if($index === 0)
                                                        <i class="bi bi-star-fill text-warning ms-2"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <strong class="fs-5">{{ number_format($crew['total_weight'], 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary fs-6">{{ number_format($crew['log_count']) }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($crew['average_per_log'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="text-center">-</th>
                                    <th>Total / Average</th>
                                    <th class="text-end">
                                        <strong class="fs-5">{{ number_format(array_sum(array_column($crewPerformance, 'total_weight')), 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <span class="badge bg-secondary fs-6">{{ number_format(array_sum(array_column($crewPerformance, 'log_count'))) }}</span>
                                    </th>
                                    <th class="text-end">
                                        @php
                                            $totalWeight = array_sum(array_column($crewPerformance, 'total_weight'));
                                            $totalLogs = array_sum(array_column($crewPerformance, 'log_count'));
                                            $overallAvg = $totalLogs > 0 ? $totalWeight / $totalLogs : 0;
                                        @endphp
                                        {{ number_format($overallAvg, 2) }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Performance Insights -->
            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="card text-white" style="background-color: var(--sweep-primary);">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-white-50">Most Active Crew Member</h6>
                            @php
                                $mostActive = collect($crewPerformance)->sortByDesc('log_count')->first();
                            @endphp
                            <h4 class="card-title mb-1">{{ $mostActive['crew_member_name'] }}</h4>
                            <p class="mb-0">{{ number_format($mostActive['log_count']) }} logs created</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white" style="background-color: var(--sweep-accent);">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-white-50">Highest Average per Log</h6>
                            @php
                                $highestAvg = collect($crewPerformance)->sortByDesc('average_per_log')->first();
                            @endphp
                            <h4 class="card-title mb-1">{{ $highestAvg['crew_member_name'] }}</h4>
                            <p class="mb-0">{{ number_format($highestAvg['average_per_log'], 2) }} kg per log</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white" style="background-color: var(--sweep-secondary);">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-white-50">Total Crew Members</h6>
                            <h4 class="card-title mb-1">{{ count($crewPerformance) }}</h4>
                            <p class="mb-0">Active in this period</p>
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
                        No recycling logs found for the selected date range. Try adjusting your date filters.
                    </p>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        /* Top Performer Row Highlighting */
        .table-warning {
            background-color: rgba(255, 215, 0, 0.1) !important;
        }

        .table-warning:hover {
            background-color: rgba(255, 215, 0, 0.2) !important;
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
