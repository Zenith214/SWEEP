@props([
    'alerts' => [],
    'title' => 'Alerts & Notifications',
    'dismissible' => true,
    'maxHeight' => '400'
])

<div class="card border-0 shadow-sm" role="region" aria-labelledby="alert-panel-title">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="alert-panel-title">
                <i class="bi bi-bell me-2" aria-hidden="true"></i>
                {{ $title }}
            </h5>
            @if(count($alerts) > 0)
                <span class="badge bg-danger rounded-pill" aria-label="{{ count($alerts) }} alerts">
                    {{ count($alerts) }}
                </span>
            @endif
        </div>
    </div>
    
    <div class="card-body p-0">
        @if(count($alerts) === 0)
            <div class="text-center py-5">
                <i class="bi bi-check-circle text-success fs-1 mb-3 d-block" aria-hidden="true"></i>
                <p class="text-muted mb-0">No alerts at this time</p>
                <p class="small text-muted">All systems operating normally</p>
            </div>
        @else
            <div class="list-group list-group-flush" 
                 style="max-height: {{ $maxHeight }}px; overflow-y: auto;"
                 role="list">
                @foreach($alerts as $index => $alert)
                    @php
                        $alertType = $alert['type'] ?? 'info';
                        $alertIcon = match($alertType) {
                            'danger', 'error' => 'exclamation-circle-fill',
                            'warning' => 'exclamation-triangle-fill',
                            'success' => 'check-circle-fill',
                            'info' => 'info-circle-fill',
                            default => 'bell-fill'
                        };
                        
                        $alertColor = match($alertType) {
                            'danger', 'error' => 'danger',
                            'warning' => 'warning',
                            'success' => 'success',
                            'info' => 'info',
                            default => 'primary'
                        };
                    @endphp
                    
                    <div class="list-group-item list-group-item-action d-flex align-items-start {{ $alert['link'] ?? false ? 'cursor-pointer' : '' }}"
                         role="listitem"
                         @if($alert['link'] ?? false)
                             onclick="window.location.href='{{ $alert['link'] }}'"
                             tabindex="0"
                             onkeypress="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ $alert['link'] }}'; }"
                             style="cursor: pointer;"
                         @endif
                         aria-label="{{ $alert['title'] ?? 'Alert' }}: {{ $alert['message'] ?? '' }}">
                        
                        <div class="me-3 mt-1">
                            <i class="bi bi-{{ $alertIcon }} text-{{ $alertColor }} fs-5" 
                               aria-hidden="true"></i>
                        </div>
                        
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 fw-semibold">{{ $alert['title'] ?? 'Notification' }}</h6>
                                @if(isset($alert['count']) && $alert['count'] > 0)
                                    <span class="badge bg-{{ $alertColor }} rounded-pill ms-2" 
                                          aria-label="{{ $alert['count'] }} items">
                                        {{ $alert['count'] }}
                                    </span>
                                @endif
                            </div>
                            
                            <p class="mb-1 small text-muted">{{ $alert['message'] ?? '' }}</p>
                            
                            @if(isset($alert['timestamp']))
                                <p class="mb-0 small text-muted">
                                    <i class="bi bi-clock me-1" aria-hidden="true"></i>
                                    <time datetime="{{ $alert['timestamp'] }}">
                                        {{ \Carbon\Carbon::parse($alert['timestamp'])->diffForHumans() }}
                                    </time>
                                </p>
                            @endif
                            
                            @if($alert['link'] ?? false)
                                <p class="mb-0 small text-primary mt-1">
                                    <i class="bi bi-arrow-right-circle me-1" aria-hidden="true"></i>
                                    {{ $alert['linkText'] ?? 'View details' }}
                                </p>
                            @endif
                        </div>
                        
                        @if($dismissible && isset($alert['id']))
                            <button type="button" 
                                    class="btn btn-sm btn-link text-muted p-0 ms-2"
                                    onclick="event.stopPropagation(); dismissAlert('{{ $alert['id'] }}', this)"
                                    aria-label="Dismiss alert: {{ $alert['title'] ?? 'notification' }}"
                                    title="Dismiss">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    
    @if(count($alerts) > 0 && $dismissible)
        <div class="card-footer bg-white border-top text-center">
            <button type="button" 
                    class="btn btn-sm btn-link text-muted"
                    onclick="dismissAllAlerts()"
                    aria-label="Dismiss all alerts">
                <i class="bi bi-check-all me-1" aria-hidden="true"></i>
                Dismiss all
            </button>
        </div>
    @endif
</div>

@once
    @push('scripts')
    <script>
        function dismissAlert(alertId, button) {
            // Remove the alert from the DOM with animation
            const alertItem = button.closest('.list-group-item');
            alertItem.style.transition = 'opacity 0.3s ease';
            alertItem.style.opacity = '0';
            
            setTimeout(() => {
                alertItem.remove();
                
                // Update badge count
                const badge = document.querySelector('#alert-panel-title + .badge');
                if (badge) {
                    const currentCount = parseInt(badge.textContent);
                    const newCount = currentCount - 1;
                    if (newCount > 0) {
                        badge.textContent = newCount;
                        badge.setAttribute('aria-label', `${newCount} alerts`);
                    } else {
                        badge.remove();
                    }
                }
                
                // Show "no alerts" message if all dismissed
                const listGroup = document.querySelector('.list-group');
                if (listGroup && listGroup.children.length === 0) {
                    listGroup.parentElement.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-1 mb-3 d-block" aria-hidden="true"></i>
                            <p class="text-muted mb-0">No alerts at this time</p>
                            <p class="small text-muted">All systems operating normally</p>
                        </div>
                    `;
                }
            }, 300);
            
            // Send AJAX request to persist dismissal
            fetch(`/dashboard/alerts/${alertId}/dismiss`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).catch(error => console.error('Error dismissing alert:', error));
        }
        
        function dismissAllAlerts() {
            const alerts = document.querySelectorAll('.list-group-item');
            alerts.forEach((alert, index) => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, index * 50);
            });
            
            setTimeout(() => {
                const listGroup = document.querySelector('.list-group');
                if (listGroup) {
                    listGroup.parentElement.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-1 mb-3 d-block" aria-hidden="true"></i>
                            <p class="text-muted mb-0">No alerts at this time</p>
                            <p class="small text-muted">All systems operating normally</p>
                        </div>
                    `;
                }
                
                const badge = document.querySelector('#alert-panel-title + .badge');
                if (badge) badge.remove();
            }, alerts.length * 50 + 300);
            
            // Send AJAX request to persist dismissal
            fetch('/dashboard/alerts/dismiss-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).catch(error => console.error('Error dismissing alerts:', error));
        }
    </script>
    @endpush
@endonce
