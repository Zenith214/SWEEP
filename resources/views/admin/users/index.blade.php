@section('title', 'User Management')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link active" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a class="nav-link" href="{{ route('admin.routes.index') }}">
                <i class="bi bi-map"></i> Routes
            </a>
            <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                <i class="bi bi-calendar"></i> Schedules
            </a>
            <a class="nav-link" href="{{ route('admin.holidays.index') }}">
                <i class="bi bi-calendar-x"></i> Holidays
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-file-text"></i> Reports <small>(Coming Soon)</small>
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-recycle"></i> Recycling <small>(Coming Soon)</small>
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add New User
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="administrator" {{ request('role') == 'administrator' ? 'selected' : '' }}>Administrator</option>
                        <option value="collection_crew" {{ request('role') == 'collection_crew' ? 'selected' : '' }}>Collection Crew</option>
                        <option value="resident" {{ request('role') == 'resident' ? 'selected' : '' }}>Resident</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->id === Auth::id())
                                        <span class="badge bg-info">You</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $role = $user->roles->first();
                                        $roleName = $role ? $role->name : 'No Role';
                                        $badgeClass = 'badge-' . str_replace('_', '', $roleName);
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucwords(str_replace('_', ' ', $roleName)) }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        @if($user->id !== Auth::id())
                                            <button type="button" class="btn btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#roleModal{{ $user->id }}"
                                                    title="Change Role">
                                                <i class="bi bi-person-badge"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $user->id }}"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Role Change Modal -->
                            <div class="modal fade" id="roleModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Change Role for {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Select New Role</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="administrator" {{ $user->hasRole('administrator') ? 'selected' : '' }}>Administrator</option>
                                                        <option value="collection_crew" {{ $user->hasRole('collection_crew') ? 'selected' : '' }}>Collection Crew</option>
                                                        <option value="resident" {{ $user->hasRole('resident') ? 'selected' : '' }}>Resident</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">Change Role</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete <strong>{{ $user->name }}</strong>?</p>
                                            <p class="text-danger">This action cannot be undone.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete User</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
