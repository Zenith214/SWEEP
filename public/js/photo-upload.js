/**
 * SWEEP Photo Upload Enhancement
 * Provides drag-and-drop, preview, and AJAX upload functionality
 */

(function() {
    'use strict';

    /**
     * Initialize photo upload functionality
     */
    function initPhotoUpload(options = {}) {
        const defaults = {
            uploadAreaId: 'photoUploadArea',
            inputId: 'photoInput',
            previewId: 'photoPreview',
            countId: 'photoCount',
            maxFiles: 5,
            maxSize: 5 * 1024 * 1024, // 5MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
            ajaxUpload: false,
            uploadUrl: null,
            csrfToken: null
        };

        const settings = { ...defaults, ...options };
        
        const uploadArea = document.getElementById(settings.uploadAreaId);
        const photoInput = document.getElementById(settings.inputId);
        const photoPreview = document.getElementById(settings.previewId);
        const photoCount = document.getElementById(settings.countId);
        
        if (!uploadArea || !photoInput || !photoPreview || !photoCount) {
            console.error('Photo upload: Required elements not found');
            return;
        }

        let selectedFiles = [];

        // Click to upload
        uploadArea.addEventListener('click', (e) => {
            if (!e.target.closest('.remove-photo')) {
                photoInput.click();
            }
        });

        // Drag and drop events
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        // File input change
        photoInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        /**
         * Handle file selection
         */
        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (selectedFiles.length >= settings.maxFiles) {
                    SWEEP.showToast(`Maximum ${settings.maxFiles} photos allowed`, 'warning');
                    return;
                }

                // Validate file
                const validation = validateFile(file);
                if (!validation.valid) {
                    SWEEP.showToast(validation.error, 'danger');
                    return;
                }

                // Add to selected files
                selectedFiles.push(file);
            });

            updatePhotoPreview();
            updateFileInput();
        }

        /**
         * Validate individual file
         */
        function validateFile(file) {
            // Check file size
            if (file.size > settings.maxSize) {
                const sizeMB = (settings.maxSize / (1024 * 1024)).toFixed(0);
                return {
                    valid: false,
                    error: `File "${file.name}" is too large. Maximum size is ${sizeMB}MB.`
                };
            }

            // Check file type
            if (!settings.allowedTypes.includes(file.type)) {
                return {
                    valid: false,
                    error: `File "${file.name}" has an invalid type. Only ${settings.allowedExtensions.join(', ').toUpperCase()} files are allowed.`
                };
            }

            // Check file extension
            const extension = file.name.split('.').pop().toLowerCase();
            if (!settings.allowedExtensions.includes(extension)) {
                return {
                    valid: false,
                    error: `File "${file.name}" has an invalid extension. Only ${settings.allowedExtensions.join(', ').toUpperCase()} files are allowed.`
                };
            }

            return { valid: true };
        }

        /**
         * Update photo preview with thumbnails
         */
        function updatePhotoPreview() {
            photoPreview.innerHTML = '';
            photoCount.textContent = selectedFiles.length;

            // Update count indicator styling
            const countBadge = photoCount.closest('.photo-count-badge');
            if (countBadge) {
                countBadge.classList.remove('text-muted', 'text-success', 'text-warning', 'text-danger');
                if (selectedFiles.length === 0) {
                    countBadge.classList.add('text-muted');
                } else if (selectedFiles.length < settings.maxFiles) {
                    countBadge.classList.add('text-success');
                } else {
                    countBadge.classList.add('text-warning');
                }
            }

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'photo-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="photo-preview-overlay">
                            <div class="photo-preview-info">
                                <small>${file.name}</small>
                                <small>${SWEEP.formatFileSize(file.size)}</small>
                            </div>
                        </div>
                        <button type="button" class="remove-photo" data-index="${index}" title="Remove photo">
                            <i class="bi bi-x"></i>
                        </button>
                        ${settings.ajaxUpload ? '<div class="upload-progress"><div class="progress-bar"></div></div>' : ''}
                    `;
                    photoPreview.appendChild(div);

                    // Add remove handler
                    div.querySelector('.remove-photo').addEventListener('click', (e) => {
                        e.stopPropagation();
                        removePhoto(index);
                    });

                    // AJAX upload if enabled
                    if (settings.ajaxUpload && settings.uploadUrl) {
                        uploadPhotoAjax(file, div, index);
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        /**
         * Remove photo from selection
         */
        function removePhoto(index) {
            SWEEP.confirmAction(
                'Are you sure you want to remove this photo?',
                function() {
                    selectedFiles.splice(index, 1);
                    updatePhotoPreview();
                    updateFileInput();
                    SWEEP.showToast('Photo removed', 'info');
                },
                {
                    title: 'Remove Photo',
                    confirmText: 'Remove',
                    confirmClass: 'btn-warning',
                    icon: 'image-fill'
                }
            );
        }

        /**
         * Update file input with selected files
         */
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            photoInput.files = dataTransfer.files;
        }

        /**
         * Upload photo via AJAX
         */
        function uploadPhotoAjax(file, previewElement, index) {
            const formData = new FormData();
            formData.append('photo', file);

            const progressBar = previewElement.querySelector('.progress-bar');
            const xhr = new XMLHttpRequest();

            // Progress tracking
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    if (progressBar) {
                        progressBar.style.width = percentComplete + '%';
                    }
                }
            });

            // Upload complete
            xhr.addEventListener('load', () => {
                if (xhr.status === 200 || xhr.status === 201) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            previewElement.classList.add('upload-success');
                            if (progressBar) {
                                progressBar.parentElement.remove();
                            }
                            SWEEP.showToast('Photo uploaded successfully', 'success');
                        } else {
                            handleUploadError(previewElement, response.message || 'Upload failed');
                        }
                    } catch (e) {
                        handleUploadError(previewElement, 'Invalid response from server');
                    }
                } else {
                    handleUploadError(previewElement, `Upload failed with status ${xhr.status}`);
                }
            });

            // Upload error
            xhr.addEventListener('error', () => {
                handleUploadError(previewElement, 'Network error during upload');
            });

            // Send request
            xhr.open('POST', settings.uploadUrl);
            if (settings.csrfToken) {
                xhr.setRequestHeader('X-CSRF-TOKEN', settings.csrfToken);
            }
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        }

        /**
         * Handle upload error
         */
        function handleUploadError(previewElement, message) {
            previewElement.classList.add('upload-error');
            const progressBar = previewElement.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.parentElement.innerHTML = '<small class="text-danger">Upload failed</small>';
            }
            SWEEP.showToast(message, 'danger');
        }

        // Return public API
        return {
            getSelectedFiles: () => selectedFiles,
            clearFiles: () => {
                selectedFiles = [];
                updatePhotoPreview();
                updateFileInput();
            },
            addFile: (file) => {
                if (selectedFiles.length < settings.maxFiles) {
                    const validation = validateFile(file);
                    if (validation.valid) {
                        selectedFiles.push(file);
                        updatePhotoPreview();
                        updateFileInput();
                        return true;
                    } else {
                        SWEEP.showToast(validation.error, 'danger');
                        return false;
                    }
                } else {
                    SWEEP.showToast(`Maximum ${settings.maxFiles} photos allowed`, 'warning');
                    return false;
                }
            }
        };
    }

    /**
     * Initialize AJAX photo upload for existing logs
     */
    function initAjaxPhotoUpload(collectionLogId, options = {}) {
        const defaults = {
            uploadUrl: `/crew/collections/${collectionLogId}/photos`,
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
            onSuccess: null,
            onError: null
        };

        const settings = { ...defaults, ...options };

        return initPhotoUpload({
            ...settings,
            ajaxUpload: true
        });
    }

    // Expose globally
    window.SWEEP = window.SWEEP || {};
    window.SWEEP.initPhotoUpload = initPhotoUpload;
    window.SWEEP.initAjaxPhotoUpload = initAjaxPhotoUpload;
})();
