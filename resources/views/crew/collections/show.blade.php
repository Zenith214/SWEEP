@section('title', 'Collection Log Details')

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
            <a class="nav-link active" href="{{ route('crew.collections') }}">
                <i class="bi bi-clipboard-check"></i> Log Collection
            </a>
            <a class="nav-link" href="{{ route('crew.collections.history') }}">
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="bi bi-file-text"></i> Collection Log Details
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar3"></i> {{ $collectionLog->assignment->assignment_date->format('l, F j, Y') }}
                    </p>
                </div>
                @if($canEdit)
                    <div>
                        <a href="{{ route('crew.collections.edit', $collectionLog) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Log
                        </a>
                    </div>
                @endif
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
                            @if($canEdit && $editTimeRemaining)
                                <div class="text-end">
                                    <small class="text-muted d-block">Edit time remaining</small>
                                    <span class="badge bg-info fs-6 px-3 py-2">
                                        <i class="bi bi-clock"></i> {{ $editTimeRemaining }} minutes
                                    </span>
                                </div>
                            @endif
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
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-camera"></i> Photos
                        </h5>
                        <span class="badge bg-primary">{{ $collectionLog->photos->count() }}/5</span>
                    </div>
                    <div class="card-body">
                        @if($collectionLog->photos->count() > 0)
                            <div class="photo-gallery mb-3">
                                @foreach($collectionLog->photos as $photo)
                                    <div class="photo-item" id="photo-{{ $photo->id }}">
                                        <a href="{{ asset('storage/' . $photo->file_path) }}" data-lightbox="collection-photos" 
                                           data-title="{{ $photo->file_name }}">
                                            <img src="{{ asset('storage/collection_photos/thumbnails/' . basename($photo->file_path)) }}" 
                                                 alt="{{ $photo->file_name }}" class="img-fluid rounded">
                                        </a>
                                        @if($canEdit)
                                            <button type="button" class="btn btn-danger btn-sm delete-photo-btn" 
                                                    data-photo-id="{{ $photo->id }}" title="Delete photo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-camera" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">No photos uploaded</p>
                            </div>
                        @endif

                        <!-- Add Photos Section (if editable and under limit) -->
                        @if($canEdit && $collectionLog->photos->count() < 5)
                            <div class="border-top pt-3 mt-3">
                                <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="bi bi-plus-circle"></i> Add More Photos
                                    </span>
                                    <span class="photo-count-badge text-muted">
                                        <i class="bi bi-images"></i> <span id="photoCount">0</span>/{{ 5 - $collectionLog->photos->count() }} available
                                    </span>
                                </label>
                                <div class="photo-upload-area" id="photoUploadArea">
                                    <div class="upload-prompt text-center py-3">
                                        <i class="bi bi-cloud-upload" style="font-size: 2.5rem; color: var(--sweep-accent);"></i>
                                        <p class="mb-1 mt-2 fw-bold">Drag & drop photos here or click to browse</p>
                                        <small class="text-muted">Maximum {{ 5 - $collectionLog->photos->count() }} more photo(s), 5MB each</small>
                                    </div>
                                    <input type="file" class="d-none" id="photoInput" 
                                           accept="image/jpeg,image/png,image/webp" multiple>
                                </div>
                                <div id="photoPreview" class="photo-preview mt-3"></div>
                            </div>
                        @endif
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
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($canEdit)
                                <a href="{{ route('crew.collections.edit', $collectionLog) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Edit Log
                                </a>
                            @endif
                            <a href="{{ route('crew.collections') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Today
                            </a>
                            <a href="{{ route('crew.collections.history') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-clock-history"></i> View History
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

        .photo-count-badge {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .photo-upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .photo-upload-area:hover {
            border-color: var(--sweep-accent);
            background: linear-gradient(135deg, #f8f9fa 0%, #e7f5f3 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .photo-upload-area.dragover {
            border-color: var(--sweep-accent);
            background: linear-gradient(135deg, #e7f5f3 0%, #d4ede8 100%);
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(0, 150, 136, 0.2);
        }

        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .photo-item {
            position: relative;
            aspect-ratio: 1;
            overflow: hidden;
            border-radius: 0.75rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .photo-item:hover {
            border-color: var(--sweep-accent);
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .photo-item:hover img {
            transform: scale(1.05);
        }

        .photo-item .delete-photo-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .photo-item:hover .delete-photo-btn {
            opacity: 1;
        }

        .photo-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }

        .photo-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .photo-preview-item:hover {
            border-color: var(--sweep-accent);
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .photo-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .photo-preview-item:hover img {
            transform: scale(1.05);
        }

        .photo-preview-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            padding: 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .photo-preview-item:hover .photo-preview-overlay {
            opacity: 1;
        }

        .photo-preview-info {
            color: white;
            font-size: 0.7rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .photo-preview-info small {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .photo-preview-item .remove-photo {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background-color: rgba(220, 53, 69, 0.95);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .photo-preview-item .remove-photo:hover {
            background-color: rgba(220, 53, 69, 1);
            transform: scale(1.15) rotate(90deg);
        }

        .upload-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background-color: rgba(0, 0, 0, 0.1);
        }

        .upload-progress .progress-bar {
            height: 100%;
            background-color: var(--sweep-accent);
            transition: width 0.3s ease;
        }

        .photo-preview-item.upload-success {
            border-color: #28a745;
        }

        .photo-preview-item.upload-error {
            border-color: #dc3545;
        }

        .timeline-item {
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .photo-gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .photo-preview {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }

            .photo-item .delete-photo-btn {
                opacity: 1;
            }

            .photo-preview-item .remove-photo {
                width: 28px;
                height: 28px;
            }

            .upload-prompt p {
                font-size: 0.9rem;
            }

            .upload-prompt i {
                font-size: 2rem !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Lightbox for photo viewing -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script src="{{ asset('js/photo-upload.js') }}"></script>
    <script>
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Photo %1 of %2'
        });

        document.addEventListener('DOMContentLoaded', function() {
            @if($canEdit && $collectionLog->photos->count() < 5)
                // Initialize AJAX photo upload
                const photoUploader = SWEEP.initAjaxPhotoUpload({{ $collectionLog->id }}, {
                    uploadAreaId: 'photoUploadArea',
                    inputId: 'photoInput',
                    previewId: 'photoPreview',
                    countId: 'photoCount',
                    maxFiles: {{ 5 - $collectionLog->photos->count() }},
                    uploadUrl: '{{ route('crew.collections.photos.upload', $collectionLog) }}',
                    csrfToken: '{{ csrf_token() }}',
                    onSuccess: function(response) {
                        // Reload page after successful upload to show new photo
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                });
            @endif

            // Handle photo deletion
            document.querySelectorAll('.delete-photo-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const photoId = this.dataset.photoId;
                    
                    SWEEP.confirmAction(
                        'Are you sure you want to delete this photo? This action cannot be undone.',
                        function() {
                            // Send AJAX request to delete photo
                            fetch('{{ url('crew/photos') }}/' + photoId, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove photo from DOM with animation
                                    const photoElement = document.getElementById(`photo-${photoId}`);
                                    photoElement.style.opacity = '0';
                                    photoElement.style.transform = 'scale(0.8)';
                                    
                                    setTimeout(() => {
                                        photoElement.remove();
                                        SWEEP.showToast('Photo deleted successfully', 'success');
                                        
                                        // Reload page after a short delay to update photo count
                                        setTimeout(() => location.reload(), 1000);
                                    }, 300);
                                } else {
                                    SWEEP.showToast(data.message || 'Failed to delete photo', 'danger');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                SWEEP.handleAjaxError(error, 'An error occurred while deleting the photo');
                            });
                        },
                        {
                            title: 'Delete Photo',
                            confirmText: 'Delete',
                            confirmClass: 'btn-danger',
                            icon: 'trash-fill'
                        }
                    );
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
