@props([
    'showPeriodSelector' => true,
    'showComparisonSelector' => true,
    'showExportButton' => true,
    'currentPeriod' => '30days',
    'comparisonPeriod' => null,
])

<div {{ $attributes->merge(['class' => 'card mb-4']) }} x-data="filterBar">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            @if($showPeriodSelector)
            <!-- Period Selector -->
            <div class="col-md-3">
                <label for="period-selector" class="form-label fw-semibold">
                    <i class="bi bi-calendar-range"></i> Time Period
                </label>
                <select id="period-selector" 
                        class="form-select" 
                        x-model="selectedPeriod"
                        @change="applyFilters">
                    <option value="7days">Last 7 Days</option>
                    <option value="30days" selected>Last 30 Days</option>
                    <option value="90days">Last 90 Days</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            @endif

            @if($showComparisonSelector)
            <!-- Comparison Period Selector -->
            <div class="col-md-3">
                <label for="comparison-selector" class="form-label fw-semibold">
                    <i class="bi bi-arrow-left-right"></i> Compare To
                </label>
                <select id="comparison-selector" 
                        class="form-select" 
                        x-model="comparisonPeriod"
                        @change="applyFilters">
                    <option value="">No Comparison</option>
                    <option value="previous_week">Previous Week</option>
                    <option value="previous_month">Previous Month</option>
                    <option value="previous_quarter">Previous Quarter</option>
                    <option value="previous_year">Previous Year</option>
                </select>
            </div>
            @endif

            <!-- Custom Date Range (shown when custom is selected) -->
            <div class="col-md-4" x-show="selectedPeriod === 'custom'" x-cloak>
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar3"></i> Custom Date Range
                </label>
                <div class="input-group">
                    <input type="date" 
                           class="form-control" 
                           x-model="customStartDate"
                           :max="customEndDate || today">
                    <span class="input-group-text">to</span>
                    <input type="date" 
                           class="form-control" 
                           x-model="customEndDate"
                           :min="customStartDate"
                           :max="today">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-2 ms-auto">
                <div class="d-flex gap-2">
                    <button type="button" 
                            class="btn btn-primary w-100"
                            @click="applyFilters"
                            :disabled="loading">
                        <span x-show="!loading">
                            <i class="bi bi-funnel"></i> Apply
                        </span>
                        <span x-show="loading">
                            <span class="spinner-border spinner-border-sm"></span>
                        </span>
                    </button>
                </div>
            </div>

            @if($showExportButton)
            <!-- Export Button -->
            <div class="col-md-12">
                <div class="d-flex justify-content-end gap-2">
                    <div class="btn-group" role="group" aria-label="Export options">
                        <button type="button" 
                                class="btn btn-outline-success"
                                @click="exportData('pdf')"
                                :disabled="exporting">
                            <span x-show="!exporting || exportFormat !== 'pdf'">
                                <i class="bi bi-file-pdf"></i> Export PDF
                            </span>
                            <span x-show="exporting && exportFormat === 'pdf'">
                                <span class="spinner-border spinner-border-sm me-1"></span> Generating...
                            </span>
                        </button>
                        <button type="button" 
                                class="btn btn-outline-success"
                                @click="exportData('csv')"
                                :disabled="exporting">
                            <span x-show="!exporting || exportFormat !== 'csv'">
                                <i class="bi bi-file-spreadsheet"></i> Export CSV
                            </span>
                            <span x-show="exporting && exportFormat === 'csv'">
                                <span class="spinner-border spinner-border-sm me-1"></span> Generating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('filterBar', () => ({
        selectedPeriod: '{{ $currentPeriod }}',
        comparisonPeriod: '{{ $comparisonPeriod }}',
        customStartDate: '',
        customEndDate: '',
        loading: false,
        exporting: false,
        exportFormat: null,
        today: new Date().toISOString().split('T')[0],
        
        applyFilters() {
            this.loading = true;
            
            // Build query parameters
            const params = new URLSearchParams();
            
            if (this.selectedPeriod === 'custom') {
                if (this.customStartDate) params.append('start_date', this.customStartDate);
                if (this.customEndDate) params.append('end_date', this.customEndDate);
                params.append('period', 'custom');
            } else {
                params.append('period', this.selectedPeriod);
            }
            
            if (this.comparisonPeriod) {
                params.append('compare_period', this.comparisonPeriod);
            }
            
            // Reload page with new filters
            window.location.href = window.location.pathname + '?' + params.toString();
        },
        
        async exportData(format) {
            this.exporting = true;
            this.exportFormat = format;
            
            try {
                // Build query parameters
                const params = new URLSearchParams();
                params.append('format', format);
                
                if (this.selectedPeriod === 'custom') {
                    if (this.customStartDate) params.append('start_date', this.customStartDate);
                    if (this.customEndDate) params.append('end_date', this.customEndDate);
                } else {
                    params.append('period', this.selectedPeriod);
                }
                
                // Trigger download
                const url = '{{ route("dashboard.export") }}?' + params.toString();
                window.location.href = url;
                
                // Show success message after a delay
                setTimeout(() => {
                    this.showNotification('Export started. Your download will begin shortly.', 'success');
                }, 500);
            } catch (error) {
                console.error('Export error:', error);
                this.showNotification('Failed to export data. Please try again.', 'error');
            } finally {
                setTimeout(() => {
                    this.exporting = false;
                    this.exportFormat = null;
                }, 2000);
            }
        },
        
        showNotification(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = '9999';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}-fill me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }));
});
</script>
@endpush
