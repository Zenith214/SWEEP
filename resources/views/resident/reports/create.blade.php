@section('title', 'Submit Report')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('resident.schedules') }}">
                <i class="bi bi-calendar3"></i> My Schedule
            </a>
            <a class="nav-link active" href="{{ route('resident.reports.create') }}">
                <i class="bi bi-file-earmark-plus"></i> Submit Report
            </a>
            <a class="nav-link" href="{{ route('resident.reports') }}">
                <i class="bi bi-list-check"></i> My Reports
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Submit Report</h1>
        <a href="{{ route('resident.reports') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to My Reports
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-4">
                Report issues such as missed pickups, uncollected waste, illegal dumping, or other waste management concerns.
            </p>

            <form action="{{ route('resident.reports.store') }}" method="POST" enctype="multipart/form-data" id="reportForm">
                @csrf

                <!-- Report Type -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Report Type <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        @foreach($reportTypes as $value => $label)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="report_type" 
                                        id="type_{{ $value }}" 
                                        value="{{ $value }}"
                                        {{ old('report_type') == $value ? 'checked' : '' }}
                                        required
                                    >
                                    <label class="form-check-label" for="type_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('report_type')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Location -->
                <div class="mb-4">
                    <label for="location" class="form-label fw-bold">Location <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        class="form-control @error('location') is-invalid @enderror" 
                        id="location" 
                        name="location" 
                        value="{{ old('location') }}"
                        placeholder="Enter the address or zone where the issue occurred"
                        maxlength="255"
                        required
                    >
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Provide the specific address or zone identifier.</div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                    <textarea 
                        class="form-control @error('description') is-invalid @enderror" 
                        id="description" 
                        name="description" 
                        rows="5"
                        maxlength="2000"
                        placeholder="Describe the issue in detail..."
                        required
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        <span id="charCount">0</span> / 2000 characters
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Photos (Optional)</label>
                    <div class="border rounded p-4 text-center" id="dropZone" style="border-style: dashed !important; cursor: pointer;">
                        <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                        <p class="mb-2">Drag and drop photos here or click to browse</p>
                        <p class="text-muted small mb-0">Maximum 3 photos, 5MB each (JPEG, PNG, WEBP)</p>
                        <input 
                            type="file" 
                            class="d-none" 
                            id="photoInput" 
                            name="photos[]" 
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                        >
                    </div>
                    @error('photos')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    @error('photos.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    
                    <!-- Photo Previews -->
                    <div id="photoPreview" class="row g-3 mt-3"></div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Submit Report
                    </button>
                    <a href="{{ route('resident.reports') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Character counter for description
        const descriptionTextarea = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        function updateCharCount() {
            charCount.textContent = descriptionTextarea.value.length;
        }
        
        descriptionTextarea.addEventListener('input', updateCharCount);
        updateCharCount();

        // Photo upload handling
        const dropZone = document.getElementById('dropZone');
        const photoInput = document.getElementById('photoInput');
        const photoPreview = document.getElementById('photoPreview');
        let selectedFiles = [];

        // Click to browse
        dropZone.addEventListener('click', () => {
            photoInput.click();
        });

        // Drag and drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--sweep-accent)';
            dropZone.style.backgroundColor = '#f0f9f7';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = '';
            dropZone.style.backgroundColor = '';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '';
            dropZone.style.backgroundColor = '';
            
            const files = Array.from(e.dataTransfer.files);
            handleFiles(files);
        });

        // File input change
        photoInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            handleFiles(files);
        });

        function handleFiles(files) {
            // Filter image files
            const imageFiles = files.filter(file => file.type.startsWith('image/'));
            
            // Check total count
            if (selectedFiles.length + imageFiles.length > 3) {
                alert('You can only upload a maximum of 3 photos.');
                return;
            }

            // Add files
            imageFiles.forEach(file => {
                if (file.size > 5 * 1024 * 1024) {
                    alert(`${file.name} is too large. Maximum size is 5MB.`);
                    return;
                }
                selectedFiles.push(file);
            });

            updatePhotoPreview();
            updateFileInput();
        }

        function updatePhotoPreview() {
            photoPreview.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const col = document.createElement('div');
                col.className = 'col-md-4';
                
                const card = document.createElement('div');
                card.className = 'card';
                
                const img = document.createElement('img');
                img.className = 'card-img-top';
                img.style.height = '200px';
                img.style.objectFit = 'cover';
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                const cardBody = document.createElement('div');
                cardBody.className = 'card-body p-2';
                
                const fileName = document.createElement('p');
                fileName.className = 'card-text small mb-2 text-truncate';
                fileName.textContent = file.name;
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger w-100';
                removeBtn.innerHTML = '<i class="bi bi-trash"></i> Remove';
                removeBtn.onclick = () => removePhoto(index);
                
                cardBody.appendChild(fileName);
                cardBody.appendChild(removeBtn);
                card.appendChild(img);
                card.appendChild(cardBody);
                col.appendChild(card);
                photoPreview.appendChild(col);
            });
        }

        function removePhoto(index) {
            selectedFiles.splice(index, 1);
            updatePhotoPreview();
            updateFileInput();
        }

        function updateFileInput() {
            // Create a new DataTransfer object to update the file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            photoInput.files = dataTransfer.files;
        }
    </script>
    @endpush
</x-app-layout>
