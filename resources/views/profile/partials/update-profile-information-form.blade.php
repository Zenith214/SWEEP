<div class="card-header">
    <h5 class="mb-0">Profile Information</h5>
</div>
<div class="card-body">
    <p class="text-muted mb-4">Update your account's profile information.</p>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled>
            <div class="form-text">Email addresses cannot be changed for security reasons.</div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success">
                    <i class="bi bi-check-circle-fill"></i> Saved!
                </span>
            @endif
        </div>
    </form>
</div>
