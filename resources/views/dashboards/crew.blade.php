@section('title', 'Collection Crew Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <x-crew-sidebar active="dashboard" />
    </x-slot>

    <div class="container-fluid px-3 px-md-4">
        <h1 class="h2 mb-4">Collection Crew Dashboard</h1>

        <div class="alert alert-info mb-4" role="alert">
            <i class="bi bi-info-circle-fill"></i> Welcome, <strong>{{ $user->name }}</strong>! You are logged in as a Collection Crew member.
        </div>

        <!-- Today's Assignment - Prominent Display -->
        <div class="card mb-4 shadow-sm" style="border-left: 4px solid var(--sweep-secondary);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="card-title mb-1">
                            <i class="bi bi-calendar-check"></i> Today's Assignment
                        </h5>
                        <p class="text-muted mb-0">{{ now()->format('l, F d, Y') }}</p>
                    </div>
                    @if($metrics['today_assignment'])
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">No Assignment</span>
                    @endif
                </div>
                
                @if($metrics['today_assignment'])
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-map text-primary fs-4 me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Route</small>
                                    <strong>{{ $metrics['today_assignment']['route_name'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt text-info fs-4 me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Zone</small>
                                    <strong>{{ $metrics['today_assignment']['route_zone'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-truck text-warning fs-4 me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Truck</small>
                                    <strong>{{ $metrics['today_assignment']['truck_number'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <a href="{{ route('crew.collections.create', $metrics['today_assignment']['id']) }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-clipboard-check"></i> Log Collection
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> No assignment scheduled for today. Check your upcoming assignments below.
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-muted mb-0">Collections Completed</h6>
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <h2 class="mb-0">{{ $metrics['performance']['completed'] ?? 0 }}</h2>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-muted mb-0">Total Assignments</h6>
                            <i class="bi bi-list-check text-primary fs-4"></i>
                        </div>
                        <h2 class="mb-0">{{ $metrics['performance']['total_collections'] ?? 0 }}</h2>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-muted mb-0">Completion Rate</h6>
                            <i class="bi bi-graph-up text-info fs-4"></i>
                        </div>
                        <h2 class="mb-0">{{ number_format($metrics['performance']['completion_rate'] ?? 0, 1) }}%</h2>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard-check text-primary fs-1 mb-3"></i>
                        <h5 class="card-title">Log Collection</h5>
                        <p class="card-text text-muted">Record completed collections</p>
                        @if($metrics['today_assignment'])
                            <a href="{{ route('crew.collections.create', $metrics['today_assignment']['id']) }}" 
                               class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Log Now
                            </a>
                        @else
                            <button class="btn btn-outline-secondary" disabled>No Active Assignment</button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history text-info fs-1 mb-3"></i>
                        <h5 class="card-title">Collection History</h5>
                        <p class="card-text text-muted">View your past collections</p>
                        <a href="{{ route('crew.collections.history') }}" class="btn btn-outline-primary">
                            <i class="bi bi-list"></i> View History
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar3 text-success fs-1 mb-3"></i>
                        <h5 class="card-title">My Schedule</h5>
                        <p class="card-text text-muted">View upcoming assignments</p>
                        <a href="{{ route('crew.collections') }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar"></i> View Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Collection Logs -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-journal-text"></i> Recent Collection Logs
                            </h5>
                            <a href="{{ route('crew.collections.history') }}" class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(count($metrics['recent_logs']) > 0)
                            <div class="list-group list-group-flush">
                                @foreach($metrics['recent_logs'] as $log)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $log['route_name'] }}</h6>
                                                <p class="mb-1 text-muted small">
                                                    <i class="bi bi-clock"></i> {{ $log['logged_at'] }}
                                                </p>
                                                @if($log['notes'])
                                                    <p class="mb-0 small text-muted">
                                                        <i class="bi bi-chat-left-text"></i> {{ Str::limit($log['notes'], 50) }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="ms-3">
                                                @if($log['status'] === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($log['status'] === 'incomplete')
                                                    <span class="badge bg-warning">Incomplete</span>
                                                @elseif($log['status'] === 'issue_reported')
                                                    <span class="badge bg-danger">Issue</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($log['status']) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                                <p class="text-muted mb-0">No collection logs yet</p>
                                <small class="text-muted">Your logged collections will appear here</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Upcoming Assignments -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-week"></i> Upcoming Assignments
                            </h5>
                            <a href="{{ route('crew.collections') }}" class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(count($metrics['upcoming_assignments']) > 0)
                            <div class="list-group list-group-flush">
                                @foreach($metrics['upcoming_assignments'] as $assignment)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $assignment['route_name'] }}</h6>
                                                <p class="mb-1 text-muted small">
                                                    <i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($assignment['assignment_date'])->format('l, M d, Y') }}
                                                </p>
                                                <p class="mb-0 small text-muted">
                                                    <i class="bi bi-truck"></i> Truck {{ $assignment['truck_number'] }}
                                                </p>
                                            </div>
                                            <div class="ms-3">
                                                <span class="badge bg-info">
                                                    {{ \Carbon\Carbon::parse($assignment['assignment_date'])->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x text-muted fs-1 d-block mb-2"></i>
                                <p class="text-muted mb-0">No upcoming assignments</p>
                                <small class="text-muted">Check back later for new assignments</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile-specific styles for field use -->
    <style>
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            h1.h2 {
                font-size: 1.5rem;
            }
            
            h2 {
                font-size: 1.75rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            /* Make cards more touch-friendly */
            .list-group-item {
                padding: 1rem;
            }
            
            /* Larger tap targets for mobile */
            .btn, a.btn {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }
        
        /* Ensure good contrast for accessibility */
        .text-muted {
            color: #6c757d !important;
        }
        
        /* Focus indicators for keyboard navigation */
        .btn:focus,
        a:focus {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
        }
    </style>
</x-app-layout>
