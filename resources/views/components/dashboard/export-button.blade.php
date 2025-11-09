@props([
    'formats' => ['pdf', 'csv'],
    'endpoint' => '/dashboard/export',
    'filename' => 'dashboard-export',
    'size' => 'md',
    'variant' => 'outline-primary',
    'label' => 'Export',
    'icon' => 'download',
    'includeFilters' => true
])

@php
    $buttonSizeClass = match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => ''
    };
@endphp

<div class="btn-group" role="group" aria-label="Export dashboard data">
    @if(count($formats) === 1)
        <button type="button" 
                class="btn btn-{{ $variant }} {{ $buttonSizeClass }}"
                onclick="exportDashboard('{{ $formats[0] }}')"
                aria-label="Export dashboard as {{ strtoupper($formats[0]) }}">
            <i class="bi bi-{{ $icon }} me-1" aria-hidden="true"></i>
            {{ $label }}
            @if(count($formats) === 1)
                ({{ strtoupper($formats[0]) }})
            @endif
        </button>
    @else
        <button type="button" 
                class="btn btn-{{ $variant }} {{ $buttonSizeClass }} dropdown-toggle"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Export dashboard - select format">
            <i class="bi bi-{{ $icon }} me-1" aria-hidden="true"></i>
            {{ $label }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @foreach($formats as $format)
                @php
                    $formatIcon = match($format) {
                        'pdf' => 'file-pdf',
                        'csv' => 'file-spreadsheet',
                        'excel' => 'file-excel',
                        'json' => 'file-code',
                        default => 'file-earmark'
                    };
                    
                    $formatLabel = match($format) {
                        'pdf' => 'PDF Document',
                        'csv' => 'CSV Spreadsheet',
                        'excel' => 'Excel Workbook',
                        'json' => 'JSON Data',
                        default => strtoupper($format)
                    };
                @endphp
                
                <li>
                    <button type="button" 
                            class="dropdown-item"
                            onclick="exportDashboard('{{ $format }}')"
                            aria-label="Export as {{ $formatLabel }}">
                        <i class="bi bi-{{ $formatIcon }} me-2" aria-hidden="true"></i>
                        {{ $formatLabel }}
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<!-- Loading overlay -->
<div id="export-loading-overlay" 
     class="position-fixed top-0 start-0 w-100 h-100 d-none" 
     style="background-color: rgba(0, 0, 0, 0.5); z-index: 9999;"
     role="alert"
     aria-live="assertive"
     aria-atomic="true">
    <div class="position-absolute top-50 start-50 translate-middle text-center">
        <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Generating export...</span>
        </div>
        <p class="text-white fs-5 fw-semibold">Generating export...</p>
        <p class="text-white-50">This may take a moment</p>
    </div>
</div>

@once
    @push('scripts')
    <script>
        function exportDashboard(format) {
            const overlay = document.getElementById('export-loading-overlay');
            
            // Show loading overlay
            if (overlay) {
                overlay.classList.remove('d-none');
            }
            
            // Gather current filters if enabled
            let params = new URLSearchParams();
            params.append('format', format);
            params.append('filename', '{{ $filename }}');
            
            @if($includeFilters)
                // Get filter values from the filter form if it exists
                const filterForm = document.getElementById('dashboard-filter-form');
                if (filterForm) {
                    const formData = new FormData(filterForm);
                    for (let [key, value] of formData.entries()) {
                        if (value) {
                            params.append(key, value);
                        }
                    }
                }
                
                // Get visible widgets if customization is enabled
                const visibleWidgets = getVisibleWidgets();
                if (visibleWidgets.length > 0) {
                    params.append('widgets', visibleWidgets.join(','));
                }
            @endif
            
            // Create download link
            const url = `{{ $endpoint }}?${params.toString()}`;
            
            // Use fetch to handle the download
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/octet-stream'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Export failed');
                }
                
                // Get filename from Content-Disposition header if available
                const contentDisposition = response.headers.get('Content-Disposition');
                let filename = '{{ $filename }}-' + new Date().toISOString().split('T')[0] + '.' + format;
                
                if (contentDisposition) {
                    const filenameMatch = contentDisposition.match(/filename="?(.+)"?/);
                    if (filenameMatch) {
                        filename = filenameMatch[1];
                    }
                }
                
                return response.blob().then(blob => ({ blob, filename }));
            })
            .then(({ blob, filename }) => {
                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(downloadUrl);
                document.body.removeChild(a);
                
                // Hide loading overlay
                if (overlay) {
                    overlay.classList.add('d-none');
                }
                
                // Show success message
                showExportSuccess(format);
            })
            .catch(error => {
                console.error('Export error:', error);
                
                // Hide loading overlay
                if (overlay) {
                    overlay.classList.add('d-none');
                }
                
                // Show error message
                showExportError(format);
            });
        }
        
        function getVisibleWidgets() {
            // This function should return an array of visible widget IDs
            // Implementation depends on dashboard customization feature
            const widgets = document.querySelectorAll('[data-widget-id]');
            const visible = [];
            
            widgets.forEach(widget => {
                if (!widget.classList.contains('d-none') && widget.offsetParent !== null) {
                    visible.push(widget.dataset.widgetId);
                }
            });
            
            return visible;
        }
        
        function showExportSuccess(format) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '11';
            toast.innerHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-success text-white">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong class="me-auto">Export Successful</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Your ${format.toUpperCase()} export has been downloaded successfully.
                    </div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        function showExportError(format) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '11';
            toast.innerHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-danger text-white">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong class="me-auto">Export Failed</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Failed to generate ${format.toUpperCase()} export. Please try again.
                    </div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    </script>
    @endpush
@endonce
