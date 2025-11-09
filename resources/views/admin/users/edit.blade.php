@section('title', 'Edit User')

<x-app-layout>
        <x-slot name="sidebar">
        <x-admin-sidebar active="users" />
    </x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit User: {{ $user->name }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email (Read-only) -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled>
                            <div class="form-text">Email addresses cannot be changed for security reasons.</div>
                        </div>

                        <!-- Current Role Display -->
                        <div class="mb-3">
                            <label class="form-label">Current Role</label>
                            <div>
                                @php
                                    $role = $user->roles->first();
                                    $roleName = $role ? $role->name : 'No Role';
                                    $badgeClass = 'badge-' . str_replace('_', '', $roleName);
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6">
                                    {{ ucwords(str_replace('_', ' ', $roleName)) }}
                                </span>
                            </div>
                            <div class="form-text">
                                To change the user's role, use the "Change Role" button on the user list page.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update User
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7">{{ $user->created_at->format('M d, Y') }}</dd>
                        
                        <dt class="col-sm-5">Last Updated:</dt>
                        <dd class="col-sm-7">{{ $user->updated_at->format('M d, Y') }}</dd>
                        
                        <dt class="col-sm-5">User ID:</dt>
                        <dd class="col-sm-7">#{{ $user->id }}</dd>
                    </dl>
                </div>
            </div>

            @if($user->roleChangeLogs && $user->roleChangeLogs->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Role Change History</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @foreach($user->roleChangeLogs->take(5) as $log)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">{{ $log->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                    <div class="small">
                                        <span class="badge badge-{{ str_replace('_', '', $log->old_role) }}">
                                            {{ ucwords(str_replace('_', ' ', $log->old_role)) }}
                                        </span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span class="badge badge-{{ str_replace('_', '', $log->new_role) }}">
                                            {{ ucwords(str_replace('_', ' ', $log->new_role)) }}
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        by {{ $log->changedBy->name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
