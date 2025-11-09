@props([
    'geoData' => [],
    'title' => 'Geographic Distribution',
    'showFilters' => true,
])

<div class="card border-0 shadow-sm" role="region" aria-labelledby="geo-distribution-title">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="geo-distribution-title">
                <i class="bi bi-geo-alt me-2" aria-hidden="true"></i>
                {{ $title }}
            </h5>
            @if($showFilters)
                <div class="btn-group btn-group-sm" role="group" aria-label="View options">
                    <button type="button" class="btn btn-outline-primary active" data-view="collections">
                        Collections
                    </button>
                    <button type="button" class="btn btn-outline-primary" data-view="reports">
                        Reports
                    </button>
                </div>
            @endif
        </div>
    </div>
    
    <div class="card-body">
        @if(empty($geoData['collections_by_zone']) && empty($geoData['reports_by_zone']))
            <div class="text-center py-5">
                <i class="bi bi-map text-muted fs-1 mb-3 d-block" aria-hidden="true"></i>
                <p class="text-muted mb-0">No geographic data available</p>
                <p class="small text-muted">Data will appear once collections are logged</p>
            </div>
        @else
            <!-- Collections View -->
            <div id="collections-view" class="geo-view">
                <div class="row g-3">
                    @foreach($geoData['collections_by_zone'] ?? [] as $zone)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border" 
                                 style="border-left: 4px solid {{ $zone['color'] ?? '#6c757d' }} !important;"
                                 role="article"
                                 aria-label="Zone {{ $zone['zone'] }} statistics">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0 fw-bold">
                                            <i class="bi bi-pin-map-fill me-1" aria-hidden="true"></i>
                                            {{ $zone['zone'] }}
                                        </h6>
                                        <span class="badge bg-{{ $zone['performance_level'] === 'high' ? 'success' : ($zone['performance_level'] === 'medium' ? 'warning' : 'danger') }}"
                                              aria-label="Performance: {{ $zone['performance_level'] }}">
                                            {{ ucfirst($zone['performance_level']) }}
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted">Completion Rate</span>
                                            <span class="fw-semibold">{{ $zone['completion_rate'] }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;" role="progressbar" 
                                             aria-valuenow="{{ $zone['completion_rate'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"
                                             aria-label="Completion rate: {{ $zone['completion_rate'] }}%">
                                            <div class="progress-bar" 
                                                 style="width: {{ $zone['completion_rate'] }}%; background-color: {{ $zone['color'] ?? '#6c757d' }};">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <div class="text-muted">Total Collections</div>
                                            <div class="fw-semibold">{{ $zone['total_collections'] ?? 0 }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted">Completed</div>
                                            <div class="fw-semibold text-success">{{ $zone['completed_collections'] ?? 0 }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted">Issues</div>
                                            <div class="fw-semibold text-danger">{{ $zone['issues_reported'] ?? 0 }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted">Recycling Logs</div>
                                            <div class="fw-semibold text-info">{{ $zone['recycling_logs'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if(!empty($geoData['zones_without_collections']))
                    <div class="alert alert-warning mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                        <strong>Zones without collections:</strong>
                        {{ implode(', ', $geoData['zones_without_collections']) }}
                    </div>
                @endif
            </div>
            
            <!-- Reports View -->
            <div id="reports-view" class="geo-view d-none">
                <div class="row g-3">
                    @foreach($geoData['reports_by_zone'] ?? [] as $zone)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border"
                                 role="article"
                                 aria-label="Zone {{ $zone['zone'] }} report statistics">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0 fw-bold">
                                            <i class="bi bi-pin-map-fill me-1" aria-hidden="true"></i>
                                            {{ $zone['zone'] }}
                                        </h6>
                                        @if(isset($zone['activity_level']))
                                        <span class="badge bg-{{ $zone['activity_level'] === 'high' ? 'danger' : ($zone['activity_level'] === 'medium' ? 'warning' : 'success') }}"
                                              aria-label="Activity: {{ $zone['activity_level'] }}">
                                            {{ ucfirst($zone['activity_level']) }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <div class="text-muted">Total Reports</div>
                                            <div class="fw-semibold fs-4">{{ $zone['total_reports'] ?? 0 }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted">Pending</div>
                                            <div class="fw-semibold fs-4 text-warning">{{ $zone['pending_reports'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    
    @if($showFilters && (!empty($geoData['collections_by_zone']) || !empty($geoData['reports_by_zone'])))
        <div class="card-footer bg-white border-top">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <label for="zone-filter" class="form-label small mb-1">Filter by Zone</label>
                    <select id="zone-filter" class="form-select form-select-sm" aria-label="Filter by zone">
                        <option value="">All Zones</option>
                        @foreach($geoData['collections_by_zone'] ?? [] as $zone)
                            <option value="{{ $zone['zone'] }}">{{ $zone['zone'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="date-range-filter" class="form-label small mb-1">Date Range</label>
                    <select id="date-range-filter" class="form-select form-select-sm" aria-label="Select date range">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View toggle functionality
            const viewButtons = document.querySelectorAll('[data-view]');
            const collectionsView = document.getElementById('collections-view');
            const reportsView = document.getElementById('reports-view');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const view = this.dataset.view;
                    
                    // Update active button
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Toggle views
                    if (view === 'collections') {
                        collectionsView?.classList.remove('d-none');
                        reportsView?.classList.add('d-none');
                    } else {
                        collectionsView?.classList.add('d-none');
                        reportsView?.classList.remove('d-none');
                    }
                });
            });
            
            // Zone filter functionality
            const zoneFilter = document.getElementById('zone-filter');
            const dateRangeFilter = document.getElementById('date-range-filter');
            
            function applyFilters() {
                const zone = zoneFilter?.value || '';
                const dateRange = dateRangeFilter?.value || '30';
                
                // Build query parameters
                const params = new URLSearchParams();
                if (zone) params.append('zone', zone);
                params.append('days', dateRange);
                
                // Fetch updated data
                fetch(`/dashboard/geographic-distribution?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page with new data or update the component
                        window.location.href = `${window.location.pathname}?${params.toString()}`;
                    }
                })
                .catch(error => console.error('Error fetching geographic data:', error));
            }
            
            zoneFilter?.addEventListener('change', applyFilters);
            dateRangeFilter?.addEventListener('change', applyFilters);
        });
    </script>
    @endpush
@endonce

</content>
