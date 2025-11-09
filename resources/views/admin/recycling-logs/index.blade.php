@section('title', 'All Recycling Logs')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-logs" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-recycle" aria-hidden="true"></i> All Recycling Logs
                </h1>
                <p class="text-muted mb-0">View and manage all recycling collection activities</p>
            </div>
            <a href="{{ route('admin.recycling-logs.export', request()->query()) }}" 
               class="btn btn-success"
               aria-label="Export filtered recycling logs to CSV">
                <i class="bi bi-download" aria-hidden="true"></i> Export to CSV
            </a>
        </div>

        <!-- Advanced Filter Panel -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Advanced Filters
                    </h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                        <i class="bi bi-chevron-down"></i> Toggle Filters
                    </button>
                </div>
            </div>
            <div class="collapse show" id="filterPanel">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.recycling-logs.index') }}" class="row g-3" role="search" aria-label="Filter recycling logs">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="{{ request('start_date') }}"
                                   aria-label="Filter by start date">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="{{ request('end_date') }}"
                                   aria-label="Filter by end date">
                        </div>

                        <!-- Material Types -->
                        <div class="col-md-6">
                            <label class="form-label">Material Types</label>
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

                        <!-- Route/Zone -->
                        <div class="col-md-3">
                            <label for="route_id" class="form-label">Route</label>
                            <select class="form-select" id="route_id" name="route_id">
                                <option value="">All Routes</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }} (Zone: {{ $route->zone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Crew Member -->
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">Crew Member</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">All Crew Members</option>
                                @foreach($crewMembers as $crew)
                                    <option value="{{ $crew->id }}" {{ request('user_id') == $crew->id ? 'selected' : '' }}>
                                        {{ $crew->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quality Issues -->
                        <div class="col-md-3">
                            <label class="form-label d-block">Quality Issues</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="quality_issues" 
                                       id="quality_issues" value="1" {{ request('quality_issues') ? 'checked' : '' }}>
                                <label class="form-check-label" for="quality_issues">
                                    Show only logs with quality issues
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-fill" aria-label="Apply filters to recycling logs">
                                <i class="bi bi-search" aria-hidden="true"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.recycling-logs.index') }}" class="btn btn-outline-secondary" aria-label="Clear all filters">
                                <i class="bi bi-x-circle" aria-hidden="true"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Count and Pagination -->
        @if($logs->total() > 0)
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <p class="text-muted mb-0">
                    Showing <strong>{{ $logs->firstItem() }}</strong> to <strong>{{ $logs->lastItem() }}</strong> 
                    of <strong>{{ $logs->total() }}</strong> recycling logs
                </p>
                @if($logs->hasPages())
                    <div>
                        {{ $logs->links('vendor.pagination.bootstrap-5-simple') }}
                    </div>
                @endif
            </div>
        @endif

        @if($logs->count() > 0)
            <!-- Recycling Logs Table -->
            <div class="card">
                <div class="card-body">
                    <!-- Desktop Table View -->
                    <div class="table-responsive desktop-table">
                        <table class="table table-hover align-middle" role="table" aria-label="Recycling logs table">
                            <thead>
                                <tr>
                                    <th scope="col">Collection Date</th>
                                    <th scope="col">Crew Member</th>
                                    <th scope="col">Route / Zone</th>
                                    <th scope="col">Materials</th>
                                    <th scope="col">Total Weight (kg)</th>
                                    <th scope="col" class="text-center">Quality Issue</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr class="{{ $log->quality_issue ? 'table-warning' : '' }}">
                                        <td>
                                            <i class="bi bi-calendar3"></i>
                                            {{ $log->collection_date->format('M d, Y') }}
                                        </td>
                                        <td>
                                            <i class="bi bi-person"></i>
                                            <strong>{{ $log->user->name }}</strong>
                                        </td>
                                        <td>
                                            @if($log->route)
                                                <div>
                                                    <strong>{{ $log->route->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-geo-alt"></i> Zone: {{ $log->route->zone }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">No route assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($log->materials as $material)
                                                    <span class="badge material-badge material-{{ $material->material_type }}">
                                                        {{ ucfirst($material->material_type) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($log->getTotalWeight(), 2) }}</strong> kg
                                        </td>
                                        <td class="text-center">
                                            @if($log->quality_issue)
                                                <i class="bi bi-exclamation-triangle-fill text-warning fs-5" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Quality issue reported"
                                                   aria-label="Quality issue reported"
                                                   role="img"></i>
                                            @else
                                                <i class="bi bi-check-circle text-success" 
                                                   data-bs-toggle="tooltip" 
                                                   title="No issues"
                                                   aria-label="No quality issues"
                                                   role="img"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.recycling-logs.show', $log) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               aria-label="View details for log from {{ $log->collection_date->format('M d, Y') }} by {{ $log->user->name }}">
                                                <i class="bi bi-eye" aria-hidden="true"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="mobile-log-cards">
                        @foreach($logs as $log)
                            <div class="mobile-log-card {{ $log->quality_issue ? 'has-quality-issue' : '' }}">
                                <div class="card-header">
                                    <div>
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        <strong>{{ $log->collection_date->format('M d, Y') }}</strong>
                                        @if($log->quality_issue)
                                            <i class="bi bi-exclamation-triangle-fill text-warning ms-2" 
                                               aria-label="Quality issue reported"></i>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.recycling-logs.show', $log) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       aria-label="View details for log from {{ $log->collection_date->format('M d, Y') }}">
                                        <i class="bi bi-eye" aria-hidden="true"></i> View
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <span class="info-label">Crew Member:</span>
                                        <strong>{{ $log->user->name }}</strong>
                                    </div>
                                    @if($log->route)
                                        <div class="info-row">
                                            <span class="info-label">Route / Zone:</span>
                                            <span>
                                                <strong>{{ $log->route->name }}</strong><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt" aria-hidden="true"></i> Zone: {{ $log->route->zone }}
                                                </small>
                                            </span>
                                        </div>
                                    @endif
                                    <div class="info-row">
                                        <span class="info-label">Materials:</span>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($log->materials as $material)
                                                <span class="badge material-badge material-{{ $material->material_type }}">
                                                    {{ ucfirst($material->material_type) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Total Weight:</span>
                                        <strong>{{ number_format($log->getTotalWeight(), 2) }} kg</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


        @else
            <!-- Empty State -->
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox" style="font-size: 5rem; color: var(--sweep-accent); opacity: 0.5;"></i>
                    <h3 class="mt-4 mb-2">No Recycling Logs Found</h3>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['start_date', 'end_date', 'materials', 'route_id', 'user_id', 'quality_issues']))
                            No logs found matching your filter criteria. Try adjusting your filters.
                        @else
                            No recycling logs have been created yet.
                        @endif
                    </p>
                    <a href="{{ route('admin.recycling-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        /* Material Type Badge Colors */
        .material-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }

        .material-plastic {
            background-color: #3B82F6;
            color: white;
        }

        .material-paper {
            background-color: #92400E;
            color: white;
        }

        .material-glass {
            background-color: #10B981;
            color: white;
        }

        .material-metal {
            background-color: #6B7280;
            color: white;
        }

        .material-cardboard {
            background-color: #D97706;
            color: white;
        }

        .material-organic {
            background-color: #84CC16;
            color: white;
        }

        /* Quality Issue Row Highlighting */
        .table-warning {
            background-color: rgba(244, 163, 0, 0.1) !important;
        }

        .table-warning:hover {
            background-color: rgba(244, 163, 0, 0.2) !important;
        }

        /* Touch-friendly buttons (min 44px) */
        .btn, .btn-sm {
            min-height: 44px;
            min-width: 44px;
        }

        /* Pagination Styling */
        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            min-height: auto;
            min-width: auto;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--sweep-primary, #007bff);
            border-color: var(--sweep-primary, #007bff);
        }

        /* Mobile Card Layout */
        .mobile-log-cards {
            display: none;
        }

        .mobile-log-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: white;
        }

        .mobile-log-card.has-quality-issue {
            background-color: rgba(244, 163, 0, 0.1);
            border-left: 4px solid #F4A300;
        }

        .mobile-log-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }

        .mobile-log-card .card-body {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .mobile-log-card .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .mobile-log-card .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .desktop-table {
                display: none;
            }

            .mobile-log-cards {
                display: block;
            }

            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .btn-success {
                width: 100%;
            }

            /* Filter panel full-screen on mobile */
            #filterPanel .row.g-3 > div {
                width: 100%;
            }

            .material-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }

        @media (min-width: 769px) {
            .desktop-table {
                display: block;
            }

            .mobile-log-cards {
                display: none;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Initialize Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @endpush
</x-app-layout>