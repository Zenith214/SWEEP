@section('title', isset($log) ? 'Edit Recycling Log' : 'Create Recycling Log')

<x-app-layout>
    <x-slot name="sidebar">
        <x-crew-sidebar active="recycling-logs" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h2 mb-1">
                <i class="bi bi-recycle"></i> {{ isset($log) ? 'Edit Recycling Log' : 'Create Recycling Log' }}
            </h1>
            <p class="text-muted mb-0">Record recyclable materials collected during your route</p>
        </div>

        @if(isset($log) && $log->isWithinEditWindow())
            <!-- Edit Window Warning -->
            <div class="alert alert-warning mb-4" role="alert" aria-live="polite">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <strong>Edit Window Active:</strong> 
                You can edit this log for <span id="time-remaining" aria-live="polite" aria-atomic="true"></span> more.
            </div>
        @endif

        <!-- Form -->
        <form method="POST" 
              action="{{ isset($log) ? route('crew.recycling-logs.update', $log) : route('crew.recycling-logs.store') }}" 
              id="recycling-log-form"
              aria-label="{{ isset($log) ? 'Edit recycling log' : 'Create new recycling log' }}"
              novalidate>
            @csrf
            @if(isset($log))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <!-- Collection Details Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar3"></i> Collection Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="collection_date" class="form-label">
                                        Collection Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('collection_date') is-invalid @enderror" 
                                           id="collection_date" 
                                           name="collection_date" 
                                           value="{{ old('collection_date', isset($log) ? $log->collection_date->format('Y-m-d') : date('Y-m-d')) }}"
                                           max="{{ date('Y-m-d') }}"
                                           required>
                                    @error('collection_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Route / Zone</label>
                                    @if(isset($assignment) && $assignment)
                                        <div class="form-control-plaintext">
                                            <strong>{{ $assignment->route->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt"></i> Zone: {{ $assignment->route->zone }}
                                            </small>
                                        </div>
                                        <input type="hidden" name="route_id" value="{{ $assignment->route_id }}">
                                        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                                    @elseif(isset($log) && $log->route)
                                        <div class="form-control-plaintext">
                                            <strong>{{ $log->route->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt"></i> Zone: {{ $log->route->zone }}
                                            </small>
                                        </div>
                                    @else
                                        <div class="form-control-plaintext text-muted">
                                            No active assignment
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Collected Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-box-seam"></i> Materials Collected
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="materials-container">
                                @if(old('materials'))
                                    @foreach(old('materials') as $index => $material)
                                        <div class="material-row mb-3" data-index="{{ $index }}">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <label class="form-label">Material Type <span class="text-danger">*</span></label>
                                                    <select class="form-select material-type-select @error('materials.'.$index.'.material_type') is-invalid @enderror" 
                                                            name="materials[{{ $index }}][material_type]" 
                                                            required>
                                                        <option value="">Select material...</option>
                                                        <option value="plastic" {{ $material['material_type'] == 'plastic' ? 'selected' : '' }}>Plastic</option>
                                                        <option value="paper" {{ $material['material_type'] == 'paper' ? 'selected' : '' }}>Paper</option>
                                                        <option value="glass" {{ $material['material_type'] == 'glass' ? 'selected' : '' }}>Glass</option>
                                                        <option value="metal" {{ $material['material_type'] == 'metal' ? 'selected' : '' }}>Metal</option>
                                                        <option value="cardboard" {{ $material['material_type'] == 'cardboard' ? 'selected' : '' }}>Cardboard</option>
                                                        <option value="organic" {{ $material['material_type'] == 'organic' ? 'selected' : '' }}>Organic</option>
                                                    </select>
                                                    @error('materials.'.$index.'.material_type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                                    <input type="number" 
                                                           class="form-control weight-input @error('materials.'.$index.'.weight') is-invalid @enderror" 
                                                           name="materials[{{ $index }}][weight]" 
                                                           step="0.01" 
                                                           min="0.01" 
                                                           max="10000"
                                                           value="{{ $material['weight'] }}"
                                                           placeholder="0.00"
                                                           required>
                                                    @error('materials.'.$index.'.weight')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger w-100 remove-material-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif(isset($log))
                                    @foreach($log->materials as $index => $material)
                                        <div class="material-row mb-3" data-index="{{ $index }}">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <label class="form-label">Material Type <span class="text-danger">*</span></label>
                                                    <select class="form-select material-type-select" 
                                                            name="materials[{{ $index }}][material_type]" 
                                                            required>
                                                        <option value="">Select material...</option>
                                                        <option value="plastic" {{ $material->material_type == 'plastic' ? 'selected' : '' }}>Plastic</option>
                                                        <option value="paper" {{ $material->material_type == 'paper' ? 'selected' : '' }}>Paper</option>
                                                        <option value="glass" {{ $material->material_type == 'glass' ? 'selected' : '' }}>Glass</option>
                                                        <option value="metal" {{ $material->material_type == 'metal' ? 'selected' : '' }}>Metal</option>
                                                        <option value="cardboard" {{ $material->material_type == 'cardboard' ? 'selected' : '' }}>Cardboard</option>
                                                        <option value="organic" {{ $material->material_type == 'organic' ? 'selected' : '' }}>Organic</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                                    <input type="number" 
                                                           class="form-control weight-input" 
                                                           name="materials[{{ $index }}][weight]" 
                                                           step="0.01" 
                                                           min="0.01" 
                                                           max="10000"
                                                           value="{{ $material->weight }}"
                                                           placeholder="0.00"
                                                           required>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger w-100 remove-material-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Initial empty material row -->
                                    <div class="material-row mb-3" data-index="0">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label class="form-label">Material Type <span class="text-danger">*</span></label>
                                                <select class="form-select material-type-select" 
                                                        name="materials[0][material_type]" 
                                                        required>
                                                    <option value="">Select material...</option>
                                                    <option value="plastic">Plastic</option>
                                                    <option value="paper">Paper</option>
                                                    <option value="glass">Glass</option>
                                                    <option value="metal">Metal</option>
                                                    <option value="cardboard">Cardboard</option>
                                                    <option value="organic">Organic</option>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                                <input type="number" 
                                                       class="form-control weight-input" 
                                                       name="materials[0][weight]" 
                                                       step="0.01" 
                                                       min="0.01" 
                                                       max="10000"
                                                       placeholder="0.00"
                                                       required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger w-100 remove-material-btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @error('materials')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror

                            <button type="button" id="add-material-btn" class="btn btn-outline-secondary" aria-label="Add another material type">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Material
                            </button>
                            <small class="text-muted ms-2" id="material-count-text" aria-live="polite">
                                (Maximum 6 materials)
                            </small>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle"></i> Additional Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="4" 
                                          maxlength="500"
                                          aria-describedby="notes-help char-count-display"
                                          placeholder="Add any observations, special circumstances, or additional details...">{{ old('notes', isset($log) ? $log->notes : '') }}</textarea>
                                <div class="form-text" id="notes-help">
                                    <span id="char-count-display" aria-live="polite" aria-atomic="true">
                                        <span id="char-count">0</span> / 500 characters
                                    </span>
                                </div>
                                @error('notes')
                                    <div class="invalid-feedback" role="alert">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="quality_issue" 
                                       name="quality_issue" 
                                       value="1"
                                       {{ old('quality_issue', isset($log) && $log->quality_issue ? 'checked' : '') }}>
                                <label class="form-check-label" for="quality_issue">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                    Mark if contamination or quality issues observed
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-2 mb-4">
                        <button type="submit" class="btn btn-primary" aria-label="Save recycling log">
                            <i class="bi bi-check-circle" aria-hidden="true"></i> Save Recycling Log
                        </button>
                        <a href="{{ route('crew.recycling-logs.index') }}" class="btn btn-outline-secondary" aria-label="Cancel and return to recycling logs list">
                            <i class="bi bi-x-circle" aria-hidden="true"></i> Cancel
                        </a>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 1rem;">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-calculator"></i> Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h2 class="display-4 mb-0" id="total-weight">0.00</h2>
                                <p class="text-muted mb-0">Total Weight (kg)</p>
                            </div>
                            <hr>
                            <div id="material-summary">
                                <p class="text-muted text-center">
                                    <small>Add materials to see breakdown</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
    <style>
        .material-row {
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            border-left: 3px solid var(--sweep-accent);
        }

        .sticky-top {
            position: sticky;
        }

        /* Touch-friendly buttons (min 44px) */
        .btn {
            min-height: 44px;
            min-width: 44px;
        }

        /* Responsive layout */
        @media (max-width: 992px) {
            .sticky-top {
                position: relative;
                top: 0 !important;
            }
        }

        @media (max-width: 768px) {
            .material-row .row {
                flex-direction: column;
            }

            .material-row .col-md-5,
            .material-row .col-md-2 {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .material-row .col-md-2 {
                margin-bottom: 0;
            }

            .d-flex.gap-2 {
                flex-direction: column;
            }

            .d-flex.gap-2 .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .col-md-6 {
                width: 100%;
            }

            .alert {
                font-size: 0.9rem;
            }
        }

        /* Material badge colors for summary */
        .material-plastic { color: #3B82F6; }
        .material-paper { color: #92400E; }
        .material-glass { color: #10B981; }
        .material-metal { color: #6B7280; }
        .material-cardboard { color: #D97706; }
        .material-organic { color: #84CC16; }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let materialIndex = {{ isset($log) ? count($log->materials) : (old('materials') ? count(old('materials')) : 1) }};
            const maxMaterials = 6;
            const materialsContainer = document.getElementById('materials-container');
            const addMaterialBtn = document.getElementById('add-material-btn');
            const notesTextarea = document.getElementById('notes');
            const charCount = document.getElementById('char-count');
            const totalWeightDisplay = document.getElementById('total-weight');
            const materialSummary = document.getElementById('material-summary');

            // Material type options
            const materialTypes = ['plastic', 'paper', 'glass', 'metal', 'cardboard', 'organic'];
            const materialLabels = {
                'plastic': 'Plastic',
                'paper': 'Paper',
                'glass': 'Glass',
                'metal': 'Metal',
                'cardboard': 'Cardboard',
                'organic': 'Organic'
            };

            // Initialize character counter
            updateCharCount();

            // Initialize weight calculation
            updateTotalWeight();

            // Add material row
            addMaterialBtn.addEventListener('click', function() {
                const currentCount = materialsContainer.querySelectorAll('.material-row').length;
                
                if (currentCount >= maxMaterials) {
                    alert('Maximum of 6 materials allowed per log.');
                    return;
                }

                const newRow = createMaterialRow(materialIndex);
                materialsContainer.insertAdjacentHTML('beforeend', newRow);
                materialIndex++;
                
                updateMaterialControls();
                attachRowEventListeners();
            });

            // Create material row HTML
            function createMaterialRow(index) {
                return `
                    <div class="material-row mb-3" data-index="${index}">
                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-label" for="material_type_${index}">Material Type <span class="text-danger">*</span></label>
                                <select class="form-select material-type-select" 
                                        id="material_type_${index}"
                                        name="materials[${index}][material_type]" 
                                        aria-label="Select material type"
                                        required>
                                    <option value="">Select material...</option>
                                    <option value="plastic">Plastic</option>
                                    <option value="paper">Paper</option>
                                    <option value="glass">Glass</option>
                                    <option value="metal">Metal</option>
                                    <option value="cardboard">Cardboard</option>
                                    <option value="organic">Organic</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="material_weight_${index}">Weight (kg) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control weight-input" 
                                       id="material_weight_${index}"
                                       name="materials[${index}][weight]" 
                                       step="0.01" 
                                       min="0.01" 
                                       max="10000"
                                       placeholder="0.00"
                                       aria-label="Enter weight in kilograms"
                                       required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger w-100 remove-material-btn" aria-label="Remove this material">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Remove material row
            function attachRowEventListeners() {
                const removeButtons = materialsContainer.querySelectorAll('.remove-material-btn');
                removeButtons.forEach(button => {
                    button.removeEventListener('click', handleRemoveRow);
                    button.addEventListener('click', handleRemoveRow);
                });

                const materialSelects = materialsContainer.querySelectorAll('.material-type-select');
                materialSelects.forEach(select => {
                    select.removeEventListener('change', handleMaterialChange);
                    select.addEventListener('change', handleMaterialChange);
                });

                const weightInputs = materialsContainer.querySelectorAll('.weight-input');
                weightInputs.forEach(input => {
                    input.removeEventListener('input', handleWeightChange);
                    input.addEventListener('input', handleWeightChange);
                });
            }

            function handleRemoveRow(e) {
                const currentCount = materialsContainer.querySelectorAll('.material-row').length;
                
                if (currentCount <= 1) {
                    alert('At least one material is required.');
                    return;
                }

                e.target.closest('.material-row').remove();
                updateMaterialControls();
                updateTotalWeight();
                validateMaterialUniqueness();
            }

            function handleMaterialChange(e) {
                validateMaterialUniqueness();
                updateTotalWeight();
            }

            function handleWeightChange(e) {
                const value = parseFloat(e.target.value);
                
                // Validate weight range
                if (value < 0.01 || value > 10000) {
                    e.target.setCustomValidity('Weight must be between 0.01 and 10,000 kg');
                } else {
                    e.target.setCustomValidity('');
                }

                updateTotalWeight();
            }

            // Update material controls (enable/disable add button)
            function updateMaterialControls() {
                const currentCount = materialsContainer.querySelectorAll('.material-row').length;
                
                if (currentCount >= maxMaterials) {
                    addMaterialBtn.disabled = true;
                    addMaterialBtn.classList.add('disabled');
                } else {
                    addMaterialBtn.disabled = false;
                    addMaterialBtn.classList.remove('disabled');
                }
            }

            // Validate material type uniqueness
            function validateMaterialUniqueness() {
                const selects = materialsContainer.querySelectorAll('.material-type-select');
                const selectedMaterials = [];
                let hasDuplicate = false;

                selects.forEach(select => {
                    const value = select.value;
                    if (value) {
                        if (selectedMaterials.includes(value)) {
                            select.setCustomValidity('This material type is already selected');
                            select.classList.add('is-invalid');
                            hasDuplicate = true;
                        } else {
                            select.setCustomValidity('');
                            select.classList.remove('is-invalid');
                            selectedMaterials.push(value);
                        }
                    }
                });

                return !hasDuplicate;
            }

            // Update total weight and summary
            function updateTotalWeight() {
                const weightInputs = materialsContainer.querySelectorAll('.weight-input');
                const materialSelects = materialsContainer.querySelectorAll('.material-type-select');
                let total = 0;
                const breakdown = {};

                weightInputs.forEach((input, index) => {
                    const weight = parseFloat(input.value) || 0;
                    const materialType = materialSelects[index].value;
                    
                    if (weight > 0 && materialType) {
                        total += weight;
                        breakdown[materialType] = (breakdown[materialType] || 0) + weight;
                    }
                });

                // Update total display
                totalWeightDisplay.textContent = total.toFixed(2);

                // Update material breakdown
                if (Object.keys(breakdown).length > 0) {
                    let summaryHTML = '<div class="list-group list-group-flush">';
                    
                    Object.entries(breakdown).forEach(([type, weight]) => {
                        const percentage = ((weight / total) * 100).toFixed(1);
                        summaryHTML += `
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="material-${type}">
                                        <i class="bi bi-circle-fill"></i> ${materialLabels[type]}
                                    </span>
                                    <span>
                                        <strong>${weight.toFixed(2)}</strong> kg
                                        <small class="text-muted">(${percentage}%)</small>
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    
                    summaryHTML += '</div>';
                    materialSummary.innerHTML = summaryHTML;
                } else {
                    materialSummary.innerHTML = '<p class="text-muted text-center"><small>Add materials to see breakdown</small></p>';
                }
            }

            // Update character count for notes
            function updateCharCount() {
                const count = notesTextarea.value.length;
                charCount.textContent = count;
                
                if (count > 500) {
                    charCount.classList.add('text-danger');
                } else if (count > 450) {
                    charCount.classList.add('text-warning');
                    charCount.classList.remove('text-danger');
                } else {
                    charCount.classList.remove('text-warning', 'text-danger');
                }
            }

            notesTextarea.addEventListener('input', updateCharCount);

            // Form submission validation
            document.getElementById('recycling-log-form').addEventListener('submit', function(e) {
                if (!validateMaterialUniqueness()) {
                    e.preventDefault();
                    alert('Please ensure all material types are unique.');
                    return false;
                }

                const weightInputs = materialsContainer.querySelectorAll('.weight-input');
                let hasInvalidWeight = false;

                weightInputs.forEach(input => {
                    const value = parseFloat(input.value);
                    if (value < 0.01 || value > 10000) {
                        hasInvalidWeight = true;
                    }
                });

                if (hasInvalidWeight) {
                    e.preventDefault();
                    alert('Please ensure all weights are between 0.01 and 10,000 kg.');
                    return false;
                }
            });

            // Initialize event listeners
            attachRowEventListeners();
            updateMaterialControls();

            @if(isset($log) && $log->isWithinEditWindow())
            // Edit window countdown
            const createdAt = new Date('{{ $log->created_at->toIso8601String() }}');
            const editWindowEnd = new Date(createdAt.getTime() + (2 * 60 * 60 * 1000)); // 2 hours
            
            function updateTimeRemaining() {
                const now = new Date();
                const remaining = editWindowEnd - now;
                
                if (remaining <= 0) {
                    document.getElementById('time-remaining').textContent = 'expired';
                    // Optionally disable form
                    return;
                }
                
                const hours = Math.floor(remaining / (1000 * 60 * 60));
                const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                
                document.getElementById('time-remaining').textContent = 
                    `${hours}h ${minutes}m`;
            }
            
            updateTimeRemaining();
            setInterval(updateTimeRemaining, 60000); // Update every minute
            @endif
        });
    </script>
    @endpush
</x-app-layout>
