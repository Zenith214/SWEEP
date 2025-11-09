@section('title', 'My Recycling Logs')

<x-app-layout>
    <x-slot name="sidebar">
        <x-crew-sidebar active="recycling-logs" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-recycle"></i> My Recycling Logs
                </h1>
                <p class="text-muted mb-0">Track your recycling collection activities</p>
            </div>
            <a href="{{ route('crew.recycling-logs.create') }}" class="btn btn-primary" aria-label="Create new recycling log">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> Create New Log
            </a>
        </div>

        <!-- Filter Panel -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('crew.recycling-logs.index') }}" class="row g-3" role="search" aria-label="Filter recycling logs">
                    <div class="col-md-5">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ request('start_date') }}"
                               aria-label="Filter by start date">
                    </div>
                    <div class="col-md-5">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ request('end_date') }}"
                               aria-label="Filter by end date">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary w-100" aria-label="Apply date filters">
                            <i class="bi bi-funnel" aria-hidden="true"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($logs->count() > 0)
            <!-- Recycling Logs Table -->
            <div class="card">
                <div class="card-body">
                    <!-- Desktop Table View -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" role="table" aria-label="Recycling logs table">
                            <thead>
                                <tr>
                                    <th>Collection Date</th>
                                    <th>Route / Zone</th>
                                    <th>Materials</th>
                                    <th>Total Weight (kg)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>
                                            <i class="bi bi-calendar3"></i>
                                            {{ $log->collection_date->format('M d, Y') }}
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
                                        <td>
                                            @if($log->isWithinEditWindow())
                                                <a href="{{ route('crew.recycling-logs.edit', $log) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   aria-label="Edit recycling log from {{ $log->collection_date->format('M d, Y') }}">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        disabled 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="Edit window expired (2 hours)"
                                                        aria-label="Edit window expired for log from {{ $log->collection_date->format('M d, Y') }}">
                                                    <i class="bi bi-lock" aria-hidden="true"></i> Edit
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="mobile-log-cards">
                        @foreach($logs as $log)
                            <div class="mobile-log-card">
                                <div class="card-header">
                                    <div>
                                        <i class="bi bi-calendar3"></i>
                                        <strong>{{ $log->collection_date->format('M d, Y') }}</strong>
                                    </div>
                                    @if($log->isWithinEditWindow())
                                        <a href="{{ route('crew.recycling-logs.edit', $log) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           aria-label="Edit recycling log from {{ $log->collection_date->format('M d, Y') }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                disabled 
                                                aria-label="Edit window expired for log from {{ $log->collection_date->format('M d, Y') }}"
                                                title="Edit window expired (2 hours)">
                                            <i class="bi bi-lock"></i> Edit
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($log->route)
                                        <div class="info-row">
                                            <span class="info-label">Route / Zone:</span>
                                            <span>
                                                <strong>{{ $log->route->name }}</strong><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i> Zone: {{ $log->route->zone }}
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

            <!-- Pagination -->
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox" style="font-size: 5rem; color: var(--sweep-accent); opacity: 0.5;"></i>
                    <h3 class="mt-4 mb-2">No Recycling Logs Found</h3>
                    <p class="text-muted mb-4">
                        @if(request('start_date') || request('end_date'))
                            No logs found for the selected date range. Try adjusting your filters.
                        @else
                            You haven't created any recycling logs yet. Start by creating your first log!
                        @endif
                    </p>
                    <a href="{{ route('crew.recycling-logs.create') }}" class="btn btn-primary" aria-label="Create your first recycling log">
                        <i class="bi bi-plus-circle" aria-hidden="true"></i> Create Your First Log
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

        /* Touch-friendly buttons (min 44px) */
        .btn, .btn-sm {
            min-height: 44px;
            min-width: 44px;
        }

        /* Responsive header */
        @media (max-width: 768px) {
            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .d-flex.justify-content-between.align-items-center .btn {
                width: 100%;
            }
        }

        /* Responsive filter form */
        @media (max-width: 576px) {
            .card-body form .col-md-5,
            .card-body form .col-md-2 {
                width: 100%;
            }

            .card-body form .btn {
                width: 100%;
            }
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }

            .material-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }

        /* Card Layout for Mobile */
        @media (max-width: 768px) {
            .table-responsive table {
                display: none;
            }

            .mobile-log-cards {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .mobile-log-cards {
                display: none;
            }
        }

        .mobile-log-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: white;
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
            align-items: center;
        }

        .mobile-log-card .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.875rem;
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
