@section('title', 'Resident Reports')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="reports" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Resident Reports</h1>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        id="search"
                        class="form-control" 
                        placeholder="Reference or resident name..."
                        value="{{ $filters['search'] ?? '' }}"
                    >
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="report_type" class="form-label">Type</label>
                    <select name="report_type" id="report_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($reportTypes as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['report_type'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input 
                        type="date" 
                        name="date_from" 
                        id="date_from"
                        class="form-control"
                        value="{{ $filters['date_from'] ?? '' }}"
                    >
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input 
                        type="date" 
                        name="date_to" 
                        id="date_to"
                        class="form-control"
                        value="{{ $filters['date_to'] ?? '' }}"
                    >
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </form>
            @if(!empty($filters))
                <div class="mt-3">
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Reports Table -->
    @if($reports->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Reports Found</h5>
                <p class="text-muted mb-0">
                    @if(!empty($filters))
                        No reports match your filter criteria.
                    @else
                        No resident reports have been submitted yet.
                    @endif
                </p>
            </div>
        </div>
    @else
        <!-- Desktop Table View -->
        <div class="card d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Resident</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr style="cursor: pointer;" onclick="window.location='{{ route('admin.reports.show', $report) }}'">
                                <td>
                                    <strong>{{ $report->reference_number }}</strong>
                                </td>
                                <td>
                                    <i class="bi bi-person"></i> {{ $report->resident->name }}
                                </td>
                                <td>
                                    {{ $reportTypes[$report->report_type] }}
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt"></i> {{ Str::limit($report->location, 25) }}
                                </td>
                                <td>
                                    <small>{{ $report->created_at->format('M d, Y') }}</small><br>
                                    <small class="text-muted">{{ $report->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'primary',
                                            'resolved' => 'success',
                                            'closed' => 'secondary'
                                        ];
                                        $color = $statusColors[$report->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $statuses[$report->status] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation()">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile/Tablet Card View -->
        <div class="d-lg-none">
            @foreach($reports as $report)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">
                                <strong>{{ $report->reference_number }}</strong>
                            </h6>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'in_progress' => 'primary',
                                    'resolved' => 'success',
                                    'closed' => 'secondary'
                                ];
                                $color = $statusColors[$report->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">
                                {{ $statuses[$report->status] }}
                            </span>
                        </div>
                        <p class="mb-1">
                            <strong>Resident:</strong> {{ $report->resident->name }}
                        </p>
                        <p class="mb-1">
                            <strong>Type:</strong> {{ $reportTypes[$report->report_type] }}
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-geo-alt"></i> {{ $report->location }}
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-calendar"></i> {{ $report->created_at->format('M d, Y h:i A') }}
                        </p>
                        <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($reports->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reports->appends($filters)->links() }}
            </div>
        @endif
    @endif
</x-app-layout>
