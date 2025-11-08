@section('title', 'Log Collection')

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
            <h1 class="h2 mb-1">
                <i class="bi bi-clipboard-check"></i> Log Collection
            </h1>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar3"></i> {{ $assignment->assignment_date->format('l, F j, Y') }}
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <!-- Assignment Info Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-2">
                                    <i class="bi bi-truck text-primary"></i> {{ $assignment->truck->truck_number }}
                                </h5>
                                <p class="text-muted mb-0">{{ $assignment->truck->license_plate }}</p>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <h5 class="mb-2">
                                    <i class="bi bi-map text-primary"></i> {{ $assignment->route->name }}
                                </h5>
                                <p class="text-muted mb-0">Zone: {{ $assignment->route->zone }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collection Logging Form -->
                <div class="card border-0 shadow">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Collection Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Validation Errors -->
                        <x-validation-errors />

                        <form action="{{ route('crew.collections.store', $assignment) }}" method="POST" enctype="multipart/form-data" id="collectionForm">
                            @csrf

                            <!-- Status Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-check-circle"></i> Collection Status <span class="text-danger">*</span>
                                </label>
                                <div class="status-options">
                                    <div class="form-check status-option">
                                        <input class="form-check-input" type="radio" name="status" id="statusCompleted" 
                                               value="completed" {{ old('status') === 'completed' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="statusCompleted">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <strong>Completed</strong>
                                            <small class="d-block text-muted">Collection was fully completed</small>
                                        </label>
                                    </div>

                                    <div class="form-check status-option">
                                        <input class="form-check-input" type="radio" name="status" id="statusIncomplete" 
                                               value="incomplete" {{ old('status') === 'incomplete' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusIncomplete">
                                            <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                            <strong>Incomplete</strong>
                                            <small class="d-block text-muted">Collection was partially completed</small>
                                        </label>
                                    </div>

                                    <div class="form-check status-option">
                                        <input class="form-check-input" type="radio" name="status" id="statusIssue" 
                                               value="issue_reported" {{ old('status') === 'issue_reported' ? 'checked' : '' }}>
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
                                           value="{{ old('completion_time') }}">
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
                                               name="completion_percentage" min="0" max="100" value="{{ old('completion_percentage', 50) }}" 
                                               oninput="document.getElementById('percentageValue').textContent = this.value + '%'">
                                        <span id="percentageValue" class="ms-3 badge bg-primary" style="min-width: 60px;">50%</span>
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
                                            <option value="{{ $key }}" {{ old('issue_type') === $key ? 'selected' : '' }}>
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
                                              rows="4" placeholder="Describe the issue in detail...">{{ old('issue_description') }}</textarea>
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
                                          maxlength="1000" placeholder="Add any additional notes about this collection...">{{ old('crew_notes') }}</textarea>
                                <small class="text-muted">Optional. Maximum 1000 characters.</small>
                                @error('crew_notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Photo Upload -->
                            <div class="mb-4">
                                <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="bi bi-camera"></i> Photos (Optional)
                                    </span>
                                    <span class="photo-count-badge text-muted">
                                        <i class="bi bi-images"></i> <span id="photoCount">0</span>/5 photos
                                    </span>
                                </label>
                                <div class="photo-upload-area" id="photoUploadArea">
                                    <div class="upload-prompt text-center py-4">
                                        <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--sweep-accent);"></i>
                                        <p class="mb-2 mt-2 fw-bold">Drag & drop photos here or click to browse</p>
                                        <small class="text-muted">Maximum 5 photos, 5MB each (JPEG, PNG, WEBP)</small>
                                    </div>
                                    <input type="file" class="d-none" id="photoInput" name="photos[]" 
                                           accept="image/jpeg,image/png,image/webp" multiple>
                                </div>
                                <div id="photoPreview" class="photo-preview mt-3"></div>
                                @error('photos')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('photos.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Form Actions -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                                <a href="{{ route('crew.collections') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" style="background-color: var(--sweep-accent); border-color: var(--sweep-accent);">
                                    <i class="bi bi-check-circle"></i> Submit Collection Log
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

        .photo-count-badge {
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s ease;
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

        .upload-prompt {
            transition: all 0.3s ease;
        }

        .photo-upload-area:hover .upload-prompt i {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
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

        .conditional-fields {
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem !important;
            }

            .photo-preview {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }

            .photo-preview-item .remove-photo {
                width: 28px;
                height: 28px;
            }

            .upload-prompt p {
                font-size: 0.9rem;
            }

            .upload-prompt i {
                font-size: 2.5rem !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/photo-upload.js') }}"></script>
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

            // Initialize enhanced photo upload
            const photoUploader = SWEEP.initPhotoUpload({
                uploadAreaId: 'photoUploadArea',
                inputId: 'photoInput',
                previewId: 'photoPreview',
                countId: 'photoCount',
                maxFiles: 5,
                ajaxUpload: false // Form submission handles upload
            });

            // Set completion percentage value on page load if old value exists
            const percentageSlider = document.getElementById('completion_percentage');
            if (percentageSlider) {
                document.getElementById('percentageValue').textContent = percentageSlider.value + '%';
            }
        });
    </script>
    @endpush
</x-app-layout>
