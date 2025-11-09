@section('title', 'Route Issues - ' . $route->name)

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="collection-logs" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="bi bi-exclamation-triangle"></i> Route Issues
                    </h1>
                    <p class="text-muted mb-0">
                        All issues reported for <strong>{{ $route->name }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.collection-logs.issues.analysis') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Analysis
                    </a>
                </div>
            </div>
        </div>

        <!-- Route Information Card -->
        <div class="card mb-4 border-0 shadow-sm" style="border-left: 4px solid var(--sweep-accent) !important;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-2">
                            <i class="bi bi-map"></i> {{ $route->name }}
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>Zone:</strong> {{ $route->zone }}
                                </p>
                                @if($route->description)
                                    <p class="mb-0 text-muted">{{ $route->description }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>Collection Day:</strong> {{ ucfirst($route->collection_day) }}
                                </p>
                                <p class="mb-0">
                                    <strong>Status:</strong> 
                                    @if($route->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="stat-box p-3 bg-light rounded">
                            <div class="stat-value text-danger" style="font-size: 2.5rem; font-weight: bold;">
                                {{ $issueLogs->count() }}
                            </div>
                            <div class="stat-label text-muted">Total Issues</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-range"></i> Date Range
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('routes.issues', $route) }}">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-5">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="mb-3">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="listView">
                    <i class="bi bi-list-ul"></i> List View
                </label>

                <input type="radio" class="btn-check" name="viewMode" id="timelineView" autocomplete="off">
                <label class="btn btn-outline-primary" for="timelineView">
                    <i class="bi bi-clock-history"></i> Timeline View
                </label>
            </div>
        </div>

        @if($issueLogs->count() > 0)
            <!-- List View -->
            <div id="listViewContent">
                @foreach($issueLogs as $log)
                    <div class="card mb-3 border-0 shadow-sm issue-card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Issue Header -->
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="issue-icon me-3">
                                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">
                                                {{ \App\Models\CollectionLog::ISSUE_TYPES[$log->issue_type] ?? $log->issue_type }}
                                            </h5>
                                            <div class="text-muted small">
                                                <i class="bi bi-calendar3"></i> {{ $log->assignment->assignment_date->format('F j, Y') }}
                                                <span class="mx-2">•</span>
                                                <i class="bi bi-person"></i> {{ $log->creator->name }}
                                                <span class="mx-2">•</span>
                                                <i class="bi bi-truck"></i> {{ $log->assignment->truck->truck_number }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Issue Description -->
                                    <div class="mb-3">
                                        <label class="text-muted small mb-1">Description</label>
                                        <div class="p-3 bg-light rounded">
                                            {{ $log->issue_description }}
                                        </div>
                                    </div>

                                    <!-- Photos -->
                                    @if($log->photos->count() > 0)
                                        <div class="mb-3">
                                            <label class="text-muted small mb-2">
                                                <i class="bi bi-camera"></i> Photos ({{ $log->photos->count() }})
                                            </label>
                                            <div class="photo-gallery-small">
                                                @foreach($log->photos as $photo)
                                                    <a href="{{ asset('storage/' . $photo->file_path) }}" 
                                                       data-lightbox="issue-{{ $log->id }}" 
                                                       data-title="{{ $photo->file_name }}">
                                                        <img src="{{ asset('storage/collection_photos/thumbnails/' . basename($photo->file_path)) }}" 
                                                             alt="{{ $photo->file_name }}" 
                                                             class="img-thumbnail">
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Admin Notes -->
                                    @if($log->adminNotes->count() > 0)
                                        <div>
                                            <label class="text-muted small mb-2">
                                                <i class="bi bi-chat-left-text"></i> Admin Notes ({{ $log->adminNotes->count() }})
                                            </label>
                                            @foreach($log->adminNotes as $note)
                                                <div class="admin-note-small mb-2 p-2 border rounded bg-white">
                                                    <div class="d-flex justify-content-between">
                                                        <strong class="small">{{ $note->admin->name }}</strong>
                                                        <small class="text-muted">{{ $note->created_at->format('M j, Y') }}</small>
                                                    </div>
                                                    <div class="small mt-1">{{ $note->note }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-4">
                                    <!-- Sidebar Info -->
                                    <div class="info-box p-3 bg-light rounded mb-3">
                                        <h6 class="mb-3">Details</h6>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Logged At</small>
                                            <strong>{{ $log->created_at->format('M j, Y g:i A') }}</strong>
                                        </div>
                                        @if($log->completion_percentage !== null)
                                            <div class="mb-2">
                                                <small class="text-muted d-block">Completion</small>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $log->completion_percentage }}%;" 
                                                         aria-valuenow="{{ $log->completion_percentage }}" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ $log->completion_percentage }}%
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-grid">
                                        <a href="{{ route('admin.collection-logs.show', $log) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> View Full Log
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Timeline View -->
            <div id="timelineViewContent" style="display: none;">
                <div class="timeline">
                    @foreach($issueLogs as $log)
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="mb-0">
                                                {{ \App\Models\CollectionLog::ISSUE_TYPES[$log->issue_type] ?? $log->issue_type }}
                                            </h5>
                                            <span class="badge bg-light text-dark">
                                                {{ $log->assignment->assignment_date->format('M j, Y') }}
                                            </span>
                                        </div>
                                        <div class="text-muted small mb-3">
                                            <i class="bi bi-person"></i> {{ $log->creator->name }}
                                            <span class="mx-2">•</span>
                                            <i class="bi bi-truck"></i> {{ $log->assignment->truck->truck_number }}
                                            <span class="mx-2">•</span>
                                            <i class="bi bi-clock"></i> {{ $log->created_at->format('g:i A') }}
                                        </div>
                                        <p class="mb-2">{{ $log->issue_description }}</p>
                                        @if($log->photos->count() > 0)
                                            <div class="mb-2">
                                                <span class="badge bg-info">
                                                    <i class="bi bi-camera"></i> {{ $log->photos->count() }} photos
                                                </span>
                                            </div>
                                        @endif
                                        @if($log->adminNotes->count() > 0)
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-chat-left-text"></i> {{ $log->adminNotes->count() }} admin notes
                                                </span>
                                            </div>
                                        @endif
                                        <a href="{{ route('admin.collection-logs.show', $log) }}" 
                                           class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-check-circle" style="font-size: 5rem; color: var(--sweep-primary); opacity: 0.3;"></i>
                    <h4 class="mt-3 text-muted">No Issues Found</h4>
                    <p class="text-muted">No issues were reported for this route during the selected period.</p>
                    <a href="{{ route('admin.collection-logs.issues.analysis') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-left"></i> Back to Issue Analysis
                    </a>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .issue-card {
            transition: transform 0.2s ease;
        }

        .issue-card:hover {
            transform: translateY(-2px);
        }

        .photo-gallery-small {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .photo-gallery-small a {
            display: block;
            width: 80px;
            height: 80px;
        }

        .photo-gallery-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0.25rem;
        }

        .admin-note-small {
            background-color: #f8f9fa;
        }

        .info-box {
            background-color: #f8f9fa;
        }

        .stat-box {
            text-align: center;
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--sweep-accent);
        }

        .timeline-item {
            position: relative;
            padding-left: 70px;
            margin-bottom: 30px;
        }

        .timeline-marker {
            position: absolute;
            left: 15px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--sweep-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        .timeline-marker i {
            font-size: 0.875rem;
        }

        .timeline-content {
            flex: 1;
        }

        @media (max-width: 768px) {
            .timeline::before {
                left: 15px;
            }

            .timeline-item {
                padding-left: 50px;
            }

            .timeline-marker {
                left: 0;
                width: 25px;
                height: 25px;
            }

            .photo-gallery-small a {
                width: 60px;
                height: 60px;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Lightbox for photo viewing -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Photo %1 of %2'
        });

        // View toggle functionality
        document.getElementById('listView').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('listViewContent').style.display = 'block';
                document.getElementById('timelineViewContent').style.display = 'none';
            }
        });

        document.getElementById('timelineView').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('listViewContent').style.display = 'none';
                document.getElementById('timelineViewContent').style.display = 'block';
            }
        });
    </script>
    @endpush
</x-app-layout>
