@section('title', 'Location Analysis')

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
        <h1 class="h2 mb-0">Location Analysis</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.analytics.reports.type') }}" class="btn btn-outline-primary">
                <i class="bi bi-pie-chart"></i> Type Analysis
            </a>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.analytics.reports.location') }}" method="GET" class="row g-3 align-items-end">
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

    <!-- Hotspots Alert -->
    @if($hotspots->isNotEmpty())
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>{{ $hotspots->count() }} Location Hotspot{{ $hotspots->count() > 1 ? 's' : '' }} Detected</strong>
                <p class="mb-0 small">Locations with 3 or more reports require attention</p>
            </div>
        </div>
    @endif

    <!-- Location Hotspots -->
    @if($hotspots->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-fire"></i> Location Hotspots (3+ Reports)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th class="text-end">Report Count</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hotspots as $location)
                                <tr>
                                    <td>
                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                        <strong>{{ $location->location }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-danger fs-6">{{ $location->count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a 
                                            href="{{ route('admin.reports.index', ['search' => $location->location, 'date_from' => $startDate, 'date_to' => $endDate]) }}" 
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
    @endif

    <!-- All Locations -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-geo-alt"></i> All Locations ({{ $reportsByLocation->count() }})</h5>
        </div>
        <div class="card-body">
            @if($reportsByLocation->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <p class="mb-0">No reports found for the selected date range</p>
                </div>
            @else
                <!-- Desktop Table View -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Location</th>
                                <th class="text-end">Report Count</th>
                                <th class="text-end">Percentage</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalReports = $reportsByLocation->sum('count');
                                $rank = 1;
                            @endphp
                            @foreach($reportsByLocation as $location)
                                @php
                                    $percentage = $totalReports > 0 ? round(($location->count / $totalReports) * 100, 1) : 0;
                                    $isHotspot = $location->count >= 3;
                                @endphp
                                <tr class="{{ $isHotspot ? 'table-warning' : '' }}">
                                    <td>
                                        <strong>#{{ $rank++ }}</strong>
                                    </td>
                                    <td>
                                        @if($isHotspot)
                                            <i class="bi bi-fire text-danger"></i>
                                        @else
                                            <i class="bi bi-geo-alt"></i>
                                        @endif
                                        {{ $location->location }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge {{ $isHotspot ? 'bg-danger' : 'bg-primary' }}">
                                            {{ $location->count }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <div class="progress me-2" style="width: 100px; height: 20px;">
                                                <div 
                                                    class="progress-bar {{ $isHotspot ? 'bg-danger' : 'bg-primary' }}" 
                                                    role="progressbar" 
                                                    style="width: {{ $percentage }}%"
                                                    aria-valuenow="{{ $percentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100"
                                                >
                                                </div>
                                            </div>
                                            <span>{{ $percentage }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a 
                                            href="{{ route('admin.reports.index', ['search' => $location->location, 'date_from' => $startDate, 'date_to' => $endDate]) }}" 
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="d-md-none">
                    @php
                        $totalReports = $reportsByLocation->sum('count');
                        $rank = 1;
                    @endphp
                    @foreach($reportsByLocation as $location)
                        @php
                            $percentage = $totalReports > 0 ? round(($location->count / $totalReports) * 100, 1) : 0;
                            $isHotspot = $location->count >= 3;
                        @endphp
                        <div class="card mb-3 {{ $isHotspot ? 'border-danger' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-secondary mb-2">#{{ $rank++ }}</span>
                                        <h6 class="mb-0">
                                            @if($isHotspot)
                                                <i class="bi bi-fire text-danger"></i>
                                            @else
                                                <i class="bi bi-geo-alt"></i>
                                            @endif
                                            {{ $location->location }}
                                        </h6>
                                    </div>
                                    <span class="badge {{ $isHotspot ? 'bg-danger' : 'bg-primary' }} fs-6">
                                        {{ $location->count }}
                                    </span>
                                </div>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div 
                                        class="progress-bar {{ $isHotspot ? 'bg-danger' : 'bg-primary' }}" 
                                        role="progressbar" 
                                        style="width: {{ $percentage }}%"
                                    >
                                        {{ $percentage }}%
                                    </div>
                                </div>
                                <a 
                                    href="{{ route('admin.reports.index', ['search' => $location->location, 'date_from' => $startDate, 'date_to' => $endDate]) }}" 
                                    class="btn btn-sm btn-primary w-100"
                                >
                                    <i class="bi bi-eye"></i> View Reports
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
