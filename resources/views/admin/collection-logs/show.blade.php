@section('title', 'Collection Log Details')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a class="nav-link" href="{{ route('admin.routes.index') }}">
                <i class="bi bi-map"></i> Routes
            </a>
            <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                <i class="bi bi-calendar"></i> Schedules
            </a>
            <a class="nav-link" href="{{ route('admin.holidays.index') }}">
                <i class="bi bi-calendar-x"></i> Holidays
            </a>
            <a class="nav-link" href="{{ route('admin.trucks.index') }}">
                <i class="bi bi-truck"></i> Trucks
            </a>
            <a class="nav-link" href="{{ route('admin.assignments.index') }}">
                <i class="bi bi-clipboard-check"></i> Assignments
            </a>
            <a class="nav-link" href="{{ route('admin.truck-availability.index') }}">
                <i class="bi bi-calendar-check"></i> Truck Availability
            </a>
            <a class="nav-link active" href="{{ route('admin.collection-logs.index') }}">
                <i class="bi bi-clipboard-data"></i> Collection Logs
            </a>
            <a class="nav-link" href="{{ route('admin.analytics.collections.index') }}">
                <i class="bi bi-graph-up"></i> Collection Analytics
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-file-text"></i> Reports <small>(Coming Soon)</small>
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-recycle"></i> Recycling <small>(Coming Soon)</small>
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="bi bi-file-text"></i> Collection Log Details
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar3"></i> {{ $collectionLog->assignment->assignment_date->format('l, F j, Y') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.collection-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Status Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-3">Collection Status</h5>
                                @if($collectionLog->status === 'completed')
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="bi bi-check-circle-fill"></i> Completed
                                    </span>
                                @elseif($collectionLog->status === 'incomplete')
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                        <i class="bi bi-exclamation-circle-fill"></i> Incomplete
                                    </span>
                                @elseif($collectionLog->status === 'issue_reported')
                                    <span class="badge bg-danger fs-6 px-3 py-2">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Issue Reported
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Details -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-truck"></i> Assignment Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="text-muted small mb-1">Truck</label>
                                    <div class="fw-bold">{{ $collectionLog->assignment->truck->truck_number }}</div>
                                    <div class="text-muted small">{{ $collectionLog->assignment->truck->license_plate }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="text-muted small mb-1">Route</label>
                                    <div class="fw-bold">{{ $collectionLog->assignment->route->name }}</div>
                                    <div class="text-muted small">Zone: {{ $collectionLog->assignment->route->zone }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="text-muted small mb-1">Crew Member</label>
                                    <div class="fw-bold">{{ $collectionLog->creator->name }}</div>
                                    <div class="text-muted small">{{ $collectionLog->creator->email }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="text-muted small mb-1">Assignment Date</label>
                                    <div class="fw-bold">{{ $collectionLog->assignment->assignment_date->format('F j, Y') }}</div>
                                </div>
                            </div>
                            @if($collectionLog->completion_time)
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <label class="text-muted small mb-1">Completion Time</label>
                                        <div class="fw-bold">{{ $collectionLog->completion_time->format('g:i A') }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($collectionLog->completion_percentage !== null)
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <label class="text-muted small mb-1">Completion Percentage</label>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ $collectionLog->completion_percentage }}%;" 
                                                 aria-valuenow="{{ $collectionLog->completion_percentage }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ $collectionLog->completion_percentage }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Issue Details (if applicable) -->
                @if($collectionLog->hasIssue())
                    <div class="card mb-4 border-0 shadow-sm border-start border-danger border-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> Issue Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Issue Type</label>
                                <div class="fw-bold">
                                    {{ \App\Models\CollectionLog::ISSUE_TYPES[$collectionLog->issue_type] ?? $collectionLog->issue_type }}
                                </div>
                            </div>
                            <div>
                                <label class="text-muted small mb-1">Description</label>
                                <div class="p-3 bg-light rounded">{{ $collectionLog->issue_description }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Crew Notes -->
                @if($collectionLog->crew_notes)
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-sticky"></i> Crew Notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="p-3 bg-light rounded">{{ $collectionLog->crew_notes }}</div>
                        </div>
                    </div>
                @endif

                <!-- Photos -->
                @if($collectionLog->photos->count() > 0)
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-camera"></i> Photos ({{ $collectionLog->photos->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="photo-gallery">
                                @foreach($collectionLog->photos as $photo)
                                    <div class="photo-item">
                                        <a href="{{ asset('storage/' . $photo->file_path) }}" data-lightbox="collection-photos" 
                                           data-title="{{ $photo->file_name }} - {{ $photo->getFileSizeFormatted() }}">
                                            <img src="{{ asset('storage/collection_photos/thumbnails/' . basename($photo->file_path)) }}" 
                                                 alt="{{ $photo->file_name }}" class="img-fluid rounded">
                                            <div class="photo-overlay">
                                                <i class="bi bi-zoom-in"></i>
                                            </div>
                                        </a>
                                        <div class="photo-info mt-2">
                                            <small class="text-muted d-block">{{ $photo->file_name }}</small>
                                            <small class="text-muted">{{ $photo->getFileSizeFormatted() }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Admin Notes Section -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-chat-left-text"></i> Administrative Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Existing Admin Notes -->
                        @if($collectionLog->adminNotes->count() > 0)
                            <div class="admin-notes-list mb-4">
                                @foreach($collectionLog->adminNotes as $note)
                                    <div class="admin-note-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle me-2" style="font-size: 1.5rem; color: var(--sweep-accent);"></i>
                                                <div>
                                                    <strong>{{ $note->admin->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock"></i> {{ $note->created_at->format('M j, Y g:i A') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="note-content ps-4">
                                            {{ $note->note }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3 mb-4">
                                <i class="bi bi-chat-left-text" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="text-muted mt-2 mb-0">No administrative notes yet</p>
                            </div>
                        @endif

                        <!-- Add Admin Note Form -->
                        <div class="add-note-form">
                            <h6 class="mb-3">Add Administrative Note</h6>
                            <form method="POST" action="{{ route('admin.collection-logs.notes.add', $collectionLog) }}">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control @error('note') is-invalid @enderror" 
                                              name="note" 
                                              rows="4" 
                                              placeholder="Enter your administrative note here..."
                                              required></textarea>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add Note
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Timestamp Info -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Timeline
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline-item mb-3">
                            <label class="text-muted small mb-1">Created</label>
                            <div class="fw-bold">{{ $collectionLog->created_at->format('M j, Y') }}</div>
                            <div class="text-muted small">{{ $collectionLog->created_at->format('g:i A') }}</div>
                        </div>
                        @if($collectionLog->edited_at)
                            <div class="timeline-item mb-3">
                                <label class="text-muted small mb-1">Last Edited</label>
                                <div class="fw-bold">{{ $collectionLog->edited_at->format('M j, Y') }}</div>
                                <div class="text-muted small">{{ $collectionLog->edited_at->format('g:i A') }}</div>
                            </div>
                        @endif
                        <div class="timeline-item">
                            <label class="text-muted small mb-1">Created By</label>
                            <div class="fw-bold">{{ $collectionLog->creator->name }}</div>
                            <div class="text-muted small">{{ $collectionLog->creator->email }}</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart"></i> Quick Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Photos</span>
                                <span class="badge bg-info">{{ $collectionLog->photos->count() }}</span>
                            </div>
                        </div>
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Admin Notes</span>
                                <span class="badge bg-secondary">{{ $collectionLog->adminNotes->count() }}</span>
                            </div>
                        </div>
                        @if($collectionLog->edited_at)
                            <div class="stat-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Edited</span>
                                    <span class="badge bg-warning text-dark">Yes</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.collection-logs.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            @if($collectionLog->hasIssue())
                                <a href="{{ route('routes.issues', $collectionLog->assignment->route) }}" 
                                   class="btn btn-outline-danger">
                                    <i class="bi bi-exclamation-triangle"></i> View Route Issues
                                </a>
                            @endif
                            <a href="{{ route('admin.analytics.collections.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-graph-up"></i> View Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .detail-item {
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            height: 100%;
        }

        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.5rem;
        }

        .photo-item {
            position: relative;
        }

        .photo-item a {
            display: block;
            aspect-ratio: 1;
            overflow: hidden;
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.2s ease;
            position: relative;
        }

        .photo-item a:hover {
            border-color: var(--sweep-accent);
            transform: scale(1.02);
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .photo-item a:hover .photo-overlay {
            opacity: 1;
        }

        .photo-overlay i {
            color: white;
            font-size: 2rem;
        }

        .photo-info {
            text-align: center;
        }

        .timeline-item {
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .admin-note-item {
            background-color: #f8f9fa;
        }

        .stat-item {
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-item:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .photo-gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 1rem;
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
    </script>
    @endpush
</x-app-layout>
