@section('title', 'Edit Collection Log')

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
        <!-- Header with Edit Time Warning -->
        <div class="mb-4">
            <h1 class="h2 mb-1">
                <i class="bi bi-pencil-square"></i> Edit Collection Log
            </h1>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar3"></i> {{ $collectionLog->assignment->assignment_date->format('l, F j, Y') }}
            </p>
        </div>

        <!-- Edit Time Remaining Alert -->
        @if($editTimeRemaining)
            <div class="alert alert-warning mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock-fill" style="font-size: 2rem; margin-right: 1rem;"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Edit Time Remaining</h5>
                        <p class="mb-0">
                            You have <strong>{{ $editTimeRemaining }} minutes</strong> remaining to edit this log. 
                            After that, only administrators can make changes.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <!-- Assignment Info Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-2">
                                    <i class="bi bi-truck text-primary"></i> {{ $collectionLog->assignment->truck->truck_number }}
                                </h5>
                                <p class="text-muted mb-0">{{ $collectionLog->assignment->truck->license_plate }}</p>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <h5 class="mb-2">
                                    <i class="bi bi-map text-primary"></i> {{ $collectionLog->assignment->route->name }}
                                </h5>
                                <p class="text-muted mb-0">Zone: {{ $collectionLog->assignment->route->zone }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="card border-0 shadow">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Update Collection Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Validation Errors -->
                        <x-validation-errors />

                        <form action="{{ route('crew.collections.update', $collectionLog) }}" method="POST" id="collectionForm">
                            @csrf
                            @method('PATCH')

                            <!-- Status Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-check-circle"></i> Collection Status <span class="text-danger">*</span>
                                </label>
                                <div class="status-options">
                                    <div class="form-check status-option">
                                        <input class="form-check-input" type="radio" name="status" id="statusCompleted" 
                                               value="completed" {{ old('status', $collectionLog->status) === 'completed' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="statusCompleted">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <strong>Completed</strong>
                                            <small class="d-block text-muted">Collection was fully completed</small>
                                        </label>
                                    </div>

                                    <div class="form-check status-option">
                                        <input class="form-check-input" type="radio" name="status" id="statusIncomplete" 
                                               value="incomplete" {{ old('status', $collectionLog->status) === 'incomplete' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusIncomplete">
                                            <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                            <strong>Incomplete</strong>
                                            <small class="d-block text-muted">Collection was partially completed</small>
                                        </label>
                                    </div>

                                    <div class="form-check status-option">
                                        <input class="form-check-input" type="radio" name="status" id="statusIssue" 
                                               value="issue_reported" {{ old('status', $collectionLog->status) === 'issue_reported' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusIssue">
                                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                            <strong>Issue Reported</strong>
                                            <small class="d-block text-muted">An issue prevented normal collection</small>
                                        </label>
                                    </div>
                                </div>
                                @error('status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Completed Fields -->
                            <div id="completedFields" class="conditional-fields" style="display: none;">
                                <div class="mb-4">
                                    <label for="completion_time" class="form-label fw-bold">
                                        <i class="bi bi-clock"></i> Completion Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control" id="completion_time" name="completion_time" 
                                           value="{{ old('completion_time', $collectionLog->completion_time?->format('H:i')) }}">
                                    @error('completion_time')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Incomplete Fields -->
                            <div id="incompleteFields" class="conditional-fields" style="display: none;">
                                <div class="mb-4">
                                    <label for="completion_percentage" class="form-label fw-bold">
                                        <i class="bi bi-percent"></i> Completion Percentage
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input type="range" class="form-range flex-grow-1" id="completion_percentage" 
                                               name="completion_percentage" min="0" max="100" 
                                               value="{{ old('completion_percentage', $collectionLog->completion_percentage ?? 50) }}" 
                                               oninput="document.getElementById('percentageValue').textContent = this.value + '%'">
                                        <span id="percentageValue" class="ms-3 badge bg-primary" style="min-width: 60px;">
                                            {{ old('completion_percentage', $collectionLog->completion_percentage ?? 50) }}%
                                        </span>
                                    </div>
                                    @error('completion_percentage')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Issue Fields -->
                            <div id="issueFields" class="conditional-fields" style="display: none;">
                                <div class="mb-4">
                                    <label for="issue_type" class="form-label fw-bold">
                                        <i class="bi bi-exclamation-triangle"></i> Issue Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="issue_type" name="issue_type">
                                        <option value="">Select issue type...</option>
                                        @foreach(\App\Models\CollectionLog::ISSUE_TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ old('issue_type', $collectionLog->issue_type) === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('issue_type')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="issue_description" class="form-label fw-bold">
                                        <i class="bi bi-file-text"></i> Issue Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="issue_description" name="issue_description" 
                                              rows="4" placeholder="Describe the issue in detail...">{{ old('issue_description', $collectionLog->issue_description) }}</textarea>
                                    @error('issue_description')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Crew Notes (Always Visible) -->
                            <div class="mb-4">
                                <label for="crew_notes" class="form-label fw-bold">
                                    <i class="bi bi-sticky"></i> Additional Notes
                                </label>
                                <textarea class="form-control" id="crew_notes" name="crew_notes" rows="3" 
                                          maxlength="1000" placeholder="Add any additional notes about this collection...">{{ old('crew_notes', $collectionLog->crew_notes) }}</textarea>
                                <small class="text-muted">Optional. Maximum 1000 characters.</small>
                                @error('crew_notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Existing Photos -->
                            @if($collectionLog->photos->count() > 0)
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-camera"></i> Existing Photos ({{ $collectionLog->photos->count() }})
                                    </label>
                                    <div class="photo-gallery">
                                        @foreach($collectionLog->photos as $photo)
                                            <div class="photo-item" id="photo-{{ $photo->id }}">
                                                <img src="{{ asset('storage/collection_photos/thumbnails/' . basename($photo->file_path)) }}" 
                                                     alt="{{ $photo->file_name }}" class="img-fluid rounded">
                                                <button type="button" class="btn btn-danger btn-sm delete-photo" 
                                                        data-photo-id="{{ $photo->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Click the trash icon to remove a photo</small>
                                </div>
                            @endif

                            <!-- Add More Photos -->
                            @if($collectionLog->photos->count() < 5)
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-camera-fill"></i> Add More Photos
                                    </label>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> 
                                        You can add {{ 5 - $collectionLog->photos->count() }} more photo(s).
                                        Use the <a href="{{ route('crew.collections.show', $collectionLog) }}">view page</a> to upload additional photos.
                                    </div>
                                </div>
                            @endif

                            <!-- Form Actions -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                                <a href="{{ route('crew.collections.show', $collectionLog) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" style="background-color: var(--sweep-accent); border-color: var(--sweep-accent);">
                                    <i class="bi bi-check-circle"></i> Update Collection Log
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .status-option {
            padding: 1rem;
            margin-bottom: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .status-option:hover {
            background-color: #f8f9fa;
            border-color: var(--sweep-accent);
        }

        .status-option input[type="radio"]:checked + label {
            font-weight: 600;
        }

        .status-option input[type="radio"]:checked {
            border-color: var(--sweep-accent);
            background-color: var(--sweep-accent);
        }

        .status-option label {
            cursor: pointer;
            margin-bottom: 0;
            width: 100%;
        }

        .conditional-fields {
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
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
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-item .delete-photo {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .photo-item:hover .delete-photo {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem !important;
            }

            .photo-gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .photo-item .delete-photo {
                opacity: 1;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusRadios = document.querySelectorAll('input[name="status"]');
            const completedFields = document.getElementById('completedFields');
            const incompleteFields = document.getElementById('incompleteFields');
            const issueFields = document.getElementById('issueFields');

            // Handle status change
            function updateConditionalFields() {
                const selectedStatus = document.querySelector('input[name="status"]:checked')?.value;
                
                completedFields.style.display = 'none';
                incompleteFields.style.display = 'none';
                issueFields.style.display = 'none';

                if (selectedStatus === 'completed') {
                    completedFields.style.display = 'block';
                } else if (selectedStatus === 'incomplete') {
                    incompleteFields.style.display = 'block';
                } else if (selectedStatus === 'issue_reported') {
                    issueFields.style.display = 'block';
                }
            }

            statusRadios.forEach(radio => {
                radio.addEventListener('change', updateConditionalFields);
            });

            // Initialize on page load
            updateConditionalFields();

            // Handle photo deletion
            document.querySelectorAll('.delete-photo').forEach(button => {
                button.addEventListener('click', function() {
                    const photoId = this.dataset.photoId;
                    
                    SWEEP.confirmAction(
                        'Are you sure you want to delete this photo? This action cannot be undone.',
                        function() {
                            // Send AJAX request to delete photo
                            fetch(`/crew/photos/${photoId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove photo from DOM
                                    document.getElementById(`photo-${photoId}`).remove();
                                    
                                    // Show success message
                                    SWEEP.showToast('Photo deleted successfully', 'success');
                                    
                                    // Reload page after a short delay to update photo count
                                    setTimeout(() => location.reload(), 1500);
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
