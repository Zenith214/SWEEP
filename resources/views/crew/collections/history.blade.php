@section('title', 'Collection History')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('crew.schedules') }}">
                <i class="bi bi-calendar-check"></i> Today's Routes
            </a>
            <a class="nav-link" href="{{ route('crew.schedules.upcoming') }}">
                <i class="bi bi-calendar3"></i> Upcoming Routes
            </a>
            <a class="nav-link" href="{{ route('crew.assignments') }}">
                <i class="bi bi-clipboard-check"></i> My Assignment
            </a>
            <a class="nav-link" href="{{ route('crew.assignments.upcoming') }}">
                <i class="bi bi-calendar-week"></i> Upcoming Assignments
            </a>
            <a class="nav-link" href="{{ route('crew.collections') }}">
                <i class="bi bi-clipboard-check"></i> Log Collection
            </a>
            <a class="nav-link active" href="{{ route('crew.collections.history') }}">
                <i class="bi bi-clock-history"></i> Collection History
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h2 mb-1">
                <i class="bi bi-clock-history"></i> Collection History
            </h1>
            <p class="text-muted mb-0">View your past collection logs</p>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('crew.collections.history') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label fw-bold">
                            <i class="bi bi-calendar-event"></i> Start Date
                        </label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label fw-bold">
                            <i class="bi bi-calendar-event"></i> End Date
                        </label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="mb-3">
            <p class="text-muted mb-0">
                Showing {{ $logs->count() }} collection log(s) from 
                <strong>{{ $startDate->format('M j, Y') }}</strong> to 
                <strong>{{ $endDate->format('M j, Y') }}</strong>
            </p>
        </div>

        @if($logs->count() > 0)
            <!-- Collection Logs List -->
            <div class="row g-3">
                @foreach($logs as $log)
                    <div class="col-12">
                        <div class="card history-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Date Column -->
                                    <div class="col-md-2">
                                        <div class="date-badge text-center">
                                            <div class="date-day">{{ $log->assignment->assignment_date->format('d') }}</div>
                                            <div class="date-month">{{ $log->assignment->assignment_date->format('M') }}</div>
                                            <div class="date-year">{{ $log->assignment->assignment_date->format('Y') }}</div>
                                        </div>
                                    </div>

                                    <!-- Route Info Column -->
                                    <div class="col-md-4 mt-3 mt-md-0">
                                        <h5 class="mb-1">
                                            <i class="bi bi-map text-primary"></i> {{ $log->assignment->route->name }}
                                        </h5>
                                        <p class="text-muted mb-0 small">
                                            <i class="bi bi-geo-alt"></i> Zone: {{ $log->assignment->route->zone }}
                                        </p>
                                        <p class="text-muted mb-0 small">
                                            <i class="bi bi-truck"></i> {{ $log->assignment->truck->truck_number }}
                                        </p>
                                    </div>

                                    <!-- Status Column -->
                                    <div class="col-md-2 mt-3 mt-md-0">
                                        @if($log->status === 'completed')
                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                <i class="bi bi-check-circle-fill"></i> Completed
                                            </span>
                                        @elseif($log->status === 'incomplete')
                                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                                <i class="bi bi-exclamation-circle-fill"></i> Incomplete
                                            </span>
                                        @elseif($log->status === 'issue_reported')
                                            <span class="badge bg-danger fs-6 px-3 py-2">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Issue
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Completion Time Column -->
                                    <div class="col-md-2 mt-3 mt-md-0">
                                        @if($log->completion_time)
                                            <div class="text-center">
                                                <i class="bi bi-clock text-muted"></i>
                                                <div class="fw-bold">{{ $log->completion_time->format('g:i A') }}</div>
                                                <small class="text-muted">Completed</small>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">
                                                <i class="bi bi-dash-circle"></i>
                                                <div class="small">N/A</div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions Column -->
                                    <div class="col-md-2 mt-3 mt-md-0 text-md-end">
                                        <a href="{{ route('crew.collections.show', $log) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>

                                <!-- Additional Info Row (for mobile) -->
                                <div class="row mt-3 d-md-none">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            @if($log->completion_time)
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> {{ $log->completion_time->format('g:i A') }}
                                                </small>
                                            @endif
                                            @if($log->photos->count() > 0)
                                                <small class="text-muted">
                                                    <i class="bi bi-camera"></i> {{ $log->photos->count() }} photo(s)
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="card text-center py-5 border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-inbox" style="font-size: 5rem; color: var(--sweep-accent); opacity: 0.5;"></i>
                    <h3 class="mt-4 mb-2">No Collection Logs Found</h3>
                    <p class="text-muted mb-4 px-3">
                        You don't have any collection logs for the selected date range. 
                        Try adjusting your date filter or log a new collection.
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('crew.collections') }}" class="btn btn-primary">
                            <i class="bi bi-clipboard-check"></i> Log Collection
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetDateFilter()">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .history-card {
            transition: all 0.2s ease;
        }

        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        .date-badge {
            background: linear-gradient(135deg, var(--sweep-primary) 0%, var(--sweep-accent) 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            display: inline-block;
            min-width: 80px;
        }

        .date-day {
            font-size: 2rem;
            font-weight: bold;
            line-height: 1;
        }

        .date-month {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.25rem;
        }

        .date-year {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .date-badge {
                min-width: 100%;
                padding: 0.75rem;
            }

            .date-day {
                font-size: 1.5rem;
            }

            .history-card .card-body {
                padding: 1rem;
            }

            .badge {
                font-size: 0.875rem !important;
                padding: 0.5rem 1rem !important;
            }
        }

        /* Pagination styling */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            color: var(--sweep-primary);
        }

        .page-link:hover {
            color: var(--sweep-accent);
            background-color: #f8f9fa;
        }

        .page-item.active .page-link {
            background-color: var(--sweep-accent);
            border-color: var(--sweep-accent);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function resetDateFilter() {
            // Reset to last 30 days
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);

            document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
            document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
            
            // Submit the form
            document.querySelector('form').submit();
        }

        // Set max date to today for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').setAttribute('max', today);
            document.getElementById('end_date').setAttribute('max', today);
        });
    </script>
    @endpush
</x-app-layout>
