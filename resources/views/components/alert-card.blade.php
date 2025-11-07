@props(['type', 'title', 'count', 'message', 'link', 'linkText', 'bgColor' => 'warning'])

<div class="card border-0 shadow-sm h-100" style="background-color: {{ $bgColor === 'amber' ? 'var(--sweep-secondary)' : ($bgColor === 'teal' ? 'var(--sweep-accent)' : 'var(--bs-warning)') }};">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-{{ $bgColor === 'amber' ? 'exclamation-triangle' : 'info-circle' }}-fill fs-3 me-3 {{ $bgColor === 'teal' ? 'text-white' : 'text-dark' }}"></i>
                <div>
                    <h5 class="card-title mb-0 {{ $bgColor === 'teal' ? 'text-white' : 'text-dark' }}">{{ $title }}</h5>
                    <span class="badge {{ $bgColor === 'amber' ? 'bg-dark text-white' : 'bg-white text-dark' }} mt-2">{{ $count }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.dashboard.dismiss-alert') }}">
                @csrf
                <input type="hidden" name="alert_type" value="{{ $type }}">
                <button type="submit" class="btn-close {{ $bgColor === 'teal' ? 'btn-close-white' : '' }}" aria-label="Dismiss" title="Dismiss for 24 hours"></button>
            </form>
        </div>
        
        <p class="card-text mb-3 {{ $bgColor === 'teal' ? 'text-white' : 'text-dark' }}">{{ $message }}</p>
        
        @if($link)
            <a href="{{ $link }}" class="btn {{ $bgColor === 'amber' ? 'btn-dark' : 'btn-light' }} btn-sm">
                <i class="bi bi-arrow-right-circle me-1"></i>
                {{ $linkText }}
            </a>
        @endif
    </div>
</div>
