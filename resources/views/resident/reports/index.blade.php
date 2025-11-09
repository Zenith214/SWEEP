@section('title', 'My Reports')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('resident.schedules') }}">
                <i class="bi bi-calendar3"></i> My Schedule
            </a>
            <a class="nav-link" href="{{ route('resident.reports.create') }}">
                <i class="bi bi-file-earmark-plus"></i> Submit Report
            </a>
            <a class="nav-link active" href="{{ route('resident.reports') }}">
                <i class="bi bi-list-check"></i> My Reports
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">My Reports</h1>
        <a href="{{ route('resident.reports.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Submit New Report
        </a>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('resident.reports') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">Filter by Status</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $selectedStatus == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Search by Reference Number</label>
                    <form action="{{ route('resident.reports.search') }}" method="GET" class="input-group">
                        <input 
                            type="text" 
                            name="reference_number" 
                            class="form-control" 
                            placeholder="e.g., REP-20251108-0001"
                            required
                        >
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports List -->
    @if($reports->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Reports Found</h5>
                <p class="text-muted mb-3">
                    @if($selectedStatus)
                        No reports with status "{{ $statuses[$selectedStatus] }}" found.
                    @else
                        You haven't submitted any reports yet.
                    @endif
                </p>
                <a href="{{ route('resident.reports.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Submit Your First Report
                </a>
            </div>
        </div>
    @else
        <!-- Desktop Table View -->
        <div class="card d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference Number</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr style="cursor: pointer;" onclick="window.location='{{ route('resident.reports.show', $report) }}'">
                                <td>
                                    <strong>{{ $report->reference_number }}</strong>
                                </td>
                                <td>
                                    {{ \App\Models\Report::REPORT_TYPES[$report->report_type] }}
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt"></i> {{ Str::limit($report->location, 30) }}
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
                                        {{ \App\Models\Report::STATUSES[$report->status] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('resident.reports.show', $report) }}" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation()">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="d-md-none">
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
                                {{ \App\Models\Report::STATUSES[$report->status] }}
                            </span>
                        </div>
                        <p class="mb-1">
                            <strong>Type:</strong> {{ \App\Models\Report::REPORT_TYPES[$report->report_type] }}
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-geo-alt"></i> {{ $report->location }}
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-calendar"></i> {{ $report->created_at->format('M d, Y h:i A') }}
                        </p>
                        <a href="{{ route('resident.reports.show', $report) }}" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($reports->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reports->links() }}
            </div>
        @endif
    @endif
</x-app-layout>
