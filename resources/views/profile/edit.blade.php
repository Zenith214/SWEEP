@section('title', 'Profile')

<x-app-layout>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="mb-3">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <h1 class="h2 mb-4">My Profile</h1>

            <div class="card mb-4">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="card mb-4">
                @include('profile.partials.update-password-form')
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Role:</dt>
                        <dd class="col-sm-8">
                            @php
                                $role = Auth::user()->roles->first();
                                $roleName = $role ? $role->name : 'No Role';
                                $badgeClass = 'badge-' . str_replace('_', '', $roleName);
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ ucwords(str_replace('_', ' ', $roleName)) }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Member Since:</dt>
                        <dd class="col-sm-8">{{ Auth::user()->created_at->format('F d, Y') }}</dd>
                        
                        <dt class="col-sm-4">Last Updated:</dt>
                        <dd class="col-sm-8">{{ Auth::user()->updated_at->format('F d, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
