@section('title', 'Collection Logs')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="collection-logs" />
    </x-slot>

    @php
        $breadcrumbItems = [];
        if (request('return_url')) {
            $breadcrumbItems[] = [
                'label' => 'Dashboard',
                'url' => request('return_url'),
                'icon' => 'speedometer2'
            ];
        }
        $breadcrumbItems[] = [
            'label' => 'Collection Logs',
            'url' => route('admin.collection-logs.index'),
            'icon' => 'file-text'
        ];
    @endphp

    @if(count($breadcrumbItems) > 1)
        <x-dashboard.breadcrumb :items="$breadcrumbItems" />
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-file-text"></i> Collection Logs
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.collection-logs.issues.analysis') }}" class="btn btn-outline-warning">
                <i class="bi bi-exclamation-triangle"></i> Issue Analysis
            </a>
            <button type="button" class="btn btn-outline-primary" id="exportBtn">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.collection-logs.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Date Range -->
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $filters['start_date'] }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $filters['end_date'] }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Route Filter -->
                    <div class="col-md-3">
                        <label for="route_id" class="form-label">Route</label>
                        <select class="form-select" id="route_id" name="route_id">
                            <option value="">All Routes</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}" {{ $filters['route_id'] == $route->id ? 'selected' : '' }}>
                                    {{ $route->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Crew Filter -->
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">Crew Member</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Crew Members</option>
                            @foreach($crewMembers as $crew)
                                <option value="{{ $crew->id }}" {{ $filters['user_id'] == $crew->id ? 'selected' : '' }}>
                                    {{ $crew->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by route, crew, or notes..." 
                               value="{{ $filters['search'] }}">
                    </div>

                    <!-- Filter Buttons -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Collection Logs Table -->
    <div class="card">
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Route</th>
                                <th>Crew</th>
                                <th>Truck</th>
                                <th>Status</th>
                                <th>Completion Time</th>
                                <th class="text-center">Photos</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <span class="text-nowrap">
                                            <i class="bi bi-calendar3"></i>
                                            {{ $log->assignment->assignment_date->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $log->assignment->route->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> {{ $log->assignment->route->zone }}
                                        </small>
                                    </td>
                                    <td>
                                        <i class="bi bi-person"></i> {{ $log->creator->name }}
                                    </td>
                                    <td>
                                        <i class="bi bi-truck"></i> {{ $log->assignment->truck->truck_number }}
                                    </td>
                                    <td>
                                        @if($log->status === 'completed')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Completed
                                            </span>
                                        @elseif($log->status === 'incomplete')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-exclamation-circle"></i> Incomplete
                                            </span>
                                        @elseif($log->status === 'issue_reported')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-exclamation-triangle"></i> Issue Reported
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->completion_time)
                                            <span class="text-nowrap">
                                                <i class="bi bi-clock"></i>
                                                {{ $log->completion_time->format('g:i A') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->photos->count() > 0)
                                            <span class="badge bg-info">
                                                <i class="bi bi-camera"></i> {{ $log->photos->count() }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.collection-logs.show', $log) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: var(--sweep-accent); opacity: 0.3;"></i>
                    <h4 class="mt-3 text-muted">No Collection Logs Found</h4>
                    <p class="text-muted">Try adjusting your filters to see more results.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('exportBtn').addEventListener('click', function() {
            // Get current filter parameters
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            // Add export parameter
            params.append('export', 'csv');
            
            // Create download link
            window.location.href = '{{ route("admin.collection-logs.index") }}?' + params.toString();
        });
    </script>
    @endpush
</x-app-layout>
