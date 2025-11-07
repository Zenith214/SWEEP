@props(['conflicts' => []])

@if(session('conflicts') || !empty($conflicts))
    @php
        $conflictList = session('conflicts', $conflicts);
    @endphp
    
    @if(!empty($conflictList))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1">
                    <strong>Assignment Conflicts Detected:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($conflictList as $conflict)
                            <li>
                                <strong>{{ $conflict['truck_number'] ?? 'Truck' }}</strong> 
                                @if(isset($conflict['route_name']))
                                    → {{ $conflict['route_name'] }}
                                @endif
                                @if(isset($conflict['crew_name']))
                                    (Crew: {{ $conflict['crew_name'] }})
                                @endif
                                @if(isset($conflict['reason']))
                                    <br><small class="text-muted">{{ $conflict['reason'] }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endif
