@section('title', 'Resident Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link active" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('resident.schedules') }}">
                <i class="bi bi-calendar3"></i> My Schedule
            </a>
            <a class="nav-link" href="{{ route('resident.reports.create') }}">
                <i class="bi bi-file-earmark-plus"></i> Submit Report
            </a>
            <a class="nav-link" href="{{ route('resident.reports') }}">
                <i class="bi bi-list-check"></i> My Reports
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <h1 class="h2 mb-4">Resident Dashboard</h1>

    <div class="alert alert-success mb-4" role="alert">
        <i class="bi bi-check-circle-fill"></i> Welcome, <strong>{{ $user->name }}</strong>! You are logged in as a Resident.
    </div>

    <!-- Next Collection - Prominent Display -->
    <div class="card mb-4 shadow-sm" style="border-left: 5px solid var(--sweep-accent);">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title mb-1">
                        <i class="bi bi-calendar-event text-primary"></i> Next Scheduled Collection
                    </h4>
                    @if($metrics['zone'])
                        <p class="text-muted mb-0">Your Zone: <strong>{{ $metrics['zone'] }}</strong></p>
                    @endif
                </div>
                <a href="{{ route('resident.schedules') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-calendar3"></i> View Full Schedule
                </a>
            </div>
            
            @if($metrics['next_collection'])
                <div class="alert alert-info mb-0" role="alert">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2">
                                <i class="bi bi-truck"></i> {{ $metrics['next_collection']['date_formatted'] }}
                            </h5>
                            <p class="mb-1"><strong>Route:</strong> {{ $metrics['next_collection']['route_name'] }}</p>
                            <p class="mb-0">
                                <i class="bi bi-clock"></i> 
                                @if($metrics['next_collection']['days_until'] == 0)
                                    <strong class="text-success">Collection is today!</strong>
                                @elseif($metrics['next_collection']['days_until'] == 1)
                                    <strong class="text-warning">Collection is tomorrow</strong>
                                @else
                                    In {{ $metrics['next_collection']['days_until'] }} days
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-4 text-primary">
                                {{ $metrics['next_collection']['days_until'] }}
                            </div>
                            <small class="text-muted">days until collection</small>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-info-circle"></i> 
                    @if($metrics['zone'])
                        No upcoming collections scheduled for your zone. Please check back later.
                    @else
                        To view your collection schedule, please search for your zone using the Collection Schedule feature.
                    @endif
                </div>
                <a href="{{ route('resident.schedules') }}" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search My Zone
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Action: Submit Report -->
    <div class="card mb-4 shadow-sm" style="border-left: 5px solid var(--sweep-primary);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="card-title mb-2">
                        <i class="bi bi-file-earmark-plus text-primary"></i> Report an Issue
                    </h5>
                    <p class="text-muted mb-0">Missed collection? Other concerns? Let us know and we'll address it promptly.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('resident.reports.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> Submit Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- My Reports Section -->
    @if(count($metrics['recent_reports']) > 0)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> My Recent Reports
                    </h5>
                    <a href="{{ route('resident.reports') }}" class="btn btn-sm btn-outline-primary">
                        View All Reports
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference #</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['recent_reports'] as $report)
                                <tr>
                                    <td>
                                        <strong>{{ $report['reference_number'] }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $report['type_label'] }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($report['status']) {
                                                'pending' => 'bg-warning',
                                                'in_progress' => 'bg-info',
                                                'resolved' => 'bg-success',
                                                'closed' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $report['status_label'] }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $report['submitted_at'] }}</small>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($report['description'], 50) }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-2">
                            <div class="h4 mb-0">{{ $metrics['report_statistics']['total_reports'] }}</div>
                            <small class="text-muted">Total Reports</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2">
                            <div class="h4 mb-0 text-warning">{{ $metrics['report_statistics']['pending_reports'] }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2">
                            <div class="h4 mb-0 text-success">{{ $metrics['report_statistics']['resolved_reports'] }}</div>
                            <small class="text-muted">Resolved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Collection Schedule Information -->
    @if(count($metrics['collection_schedule']) > 0)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-week"></i> Upcoming Collections (Next 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($metrics['collection_schedule'] as $schedule)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-calendar-check fs-3 text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $schedule['date_formatted'] }}</div>
                                        <small class="text-muted">{{ $schedule['route_name'] }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Information Cards -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background-color: var(--sweep-primary); color: white;">
                    <h5 class="mb-0"><i class="bi bi-recycle"></i> Recycling Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Rinse containers</strong> before recycling to prevent contamination
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Remove caps</strong> from bottles and place them separately
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Flatten cardboard boxes</strong> to save space
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Keep recyclables dry</strong> and clean for better processing
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Separate materials</strong> by type when possible
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background-color: var(--sweep-accent); color: white;">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Important Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-calendar3"></i> Collection Guidelines
                        </h6>
                        <p class="mb-1"><strong>Bin Placement:</strong> Place bins at curb by 7:00 AM on collection day</p>
                        <p class="mb-1"><strong>Bin Distance:</strong> Keep bins at least 3 feet apart</p>
                        <p class="mb-0"><strong>Accessibility:</strong> Ensure clear access to bins</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-exclamation-triangle"></i> Holiday Schedule
                        </h6>
                        <p class="mb-0">Collection schedules may change during holidays. Check your schedule regularly for updates.</p>
                    </div>
                    <div>
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-question-circle"></i> Need Help?
                        </h6>
                        <p class="mb-0">Contact your administrator or submit a report for any questions or concerns.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
