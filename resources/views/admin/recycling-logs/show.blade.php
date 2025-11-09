@section('title', 'Recycling Log Details')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-logs" />
    </x-slot>

    <div class="container-fluid">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.recycling-logs.index') }}" class="btn btn-outline-secondary" aria-label="Back to all recycling logs">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to All Logs
            </a>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-recycle"></i> Recycling Log Details
                </h1>
                <p class="text-muted mb-0">Log ID: #{{ $log->id }}</p>
            </div>
            @if($log->quality_issue)
                <span class="badge bg-warning text-dark fs-6" role="status" aria-label="Quality issue reported">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Quality Issue Reported
                </span>
            @endif
        </div>

        <div class="row g-4">
            <!-- Collection Information -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Collection Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="text-muted small">Collection Date</label>
                                <div class="fw-bold">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $log->collection_date->format('l, F d, Y') }}
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="text-muted small">Crew Member</label>
                                <div class="fw-bold">
                                    <i class="bi bi-person"></i>
                                    {{ $log->user->name }}
                                </div>
                            </div>

                            @if($log->route)
                                <div class="col-12">
                                    <label class="text-muted small">Route</label>
                                    <div class="fw-bold">
                                        <i class="bi bi-map"></i>
                                        {{ $log->route->name }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="text-muted small">Zone</label>
                                    <div class="fw-bold">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $log->route->zone }}
                                    </div>
                                </div>
                            @endif

                            @if($log->assignment)
                                <div class="col-12">
                                    <label class="text-muted small">Assignment</label>
                                    <div class="fw-bold">
                                        <i class="bi bi-clipboard-check"></i>
                                        Assignment #{{ $log->assignment->id }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials Collected -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam"></i> Materials Collected
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm mb-3" role="table" aria-label="Materials breakdown table">
                                <thead>
                                    <tr>
                                        <th scope="col">Material Type</th>
                                        <th scope="col" class="text-end">Weight (kg)</th>
                                        <th scope="col" class="text-end">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $breakdown = $log->getMaterialBreakdown();
                                    @endphp
                                    @foreach($breakdown as $item)
                                        <tr>
                                            <td>
                                                <span class="badge material-badge material-{{ $item['material_type'] }}">
                                                    {{ ucfirst($item['material_type']) }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($item['weight'], 2) }}</td>
                                            <td class="text-end">{{ number_format($item['percentage'], 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th>Total Weight</th>
                                        <th class="text-end">{{ number_format($log->getTotalWeight(), 2) }} kg</th>
                                        <th class="text-end">100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Visual Breakdown -->
                        <div class="mt-3">
                            <label class="text-muted small mb-2">Weight Distribution</label>
                            <div class="progress" style="height: 30px;" role="group" aria-label="Material weight distribution chart">
                                @foreach($breakdown as $item)
                                    <div class="progress-bar material-{{ $item['material_type'] }}" 
                                         role="progressbar" 
                                         style="width: {{ $item['percentage'] }}%"
                                         aria-valuenow="{{ $item['percentage'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-label="{{ ucfirst($item['material_type']) }}: {{ number_format($item['percentage'], 1) }}%"
                                         data-bs-toggle="tooltip"
                                         title="{{ ucfirst($item['material_type']) }}: {{ number_format($item['percentage'], 1) }}%">
                                        @if($item['percentage'] > 10)
                                            {{ number_format($item['percentage'], 0) }}%
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-file-text"></i> Additional Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Quality Issue</label>
                                <div>
                                    @if($log->quality_issue)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Yes - Issue Reported
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> No Issues
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small">Notes</label>
                                <div>
                                    @if($log->notes)
                                        <p class="mb-0">{{ $log->notes }}</p>
                                    @else
                                        <span class="text-muted fst-italic">No notes provided</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modification History -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Modification History
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Created At</label>
                                <div class="fw-bold">
                                    <i class="bi bi-plus-circle text-success"></i>
                                    {{ $log->created_at->format('M d, Y g:i A') }}
                                </div>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </div>

                            <div class="col-md-4">
                                <label class="text-muted small">Last Updated</label>
                                <div class="fw-bold">
                                    @if($log->created_at->ne($log->updated_at))
                                        <i class="bi bi-pencil text-warning"></i>
                                        {{ $log->updated_at->format('M d, Y g:i A') }}
                                    @else
                                        <span class="text-muted fst-italic">Not modified</span>
                                    @endif
                                </div>
                                @if($log->created_at->ne($log->updated_at))
                                    <small class="text-muted">{{ $log->updated_at->diffForHumans() }}</small>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="text-muted small">Edit Status</label>
                                <div>
                                    @if($log->created_at->ne($log->updated_at))
                                        <span class="badge bg-info">
                                            <i class="bi bi-pencil-square"></i> Modified
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-file-earmark"></i> Original
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($log->created_at->ne($log->updated_at))
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle"></i>
                                <strong>Note:</strong> This log was edited by {{ $log->user->name }} 
                                on {{ $log->updated_at->format('M d, Y') }} at {{ $log->updated_at->format('g:i A') }}.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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

        /* Card Styling */
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card-header {
            border-bottom: 2px solid #dee2e6;
        }

        /* Progress Bar Colors */
        .progress-bar.material-plastic {
            background-color: #3B82F6;
        }

        .progress-bar.material-paper {
            background-color: #92400E;
        }

        .progress-bar.material-glass {
            background-color: #10B981;
        }

        .progress-bar.material-metal {
            background-color: #6B7280;
        }

        .progress-bar.material-cardboard {
            background-color: #D97706;
        }

        .progress-bar.material-organic {
            background-color: #84CC16;
        }

        /* Touch-friendly buttons (min 44px) */
        .btn {
            min-height: 44px;
            min-width: 44px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .badge.fs-6 {
                font-size: 0.9rem !important;
            }

            .table-responsive {
                font-size: 0.9rem;
            }

            .material-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .col-md-4, .col-md-6 {
                width: 100%;
            }

            .progress {
                height: 25px !important;
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
