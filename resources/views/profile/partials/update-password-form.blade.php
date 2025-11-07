<div class="card-header">
    <h5 class="mb-0">Update Password</h5>
</div>
<div class="card-body">
    <p class="text-muted mb-4">Ensure your account is using a long, random password to stay secure.</p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password" 
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">New Password</label>
            <input id="update_password_password" name="password" type="password" 
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                   autocomplete="new-password">
            <div class="form-text">Password must be at least 8 characters.</div>
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                   autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-key"></i> Update Password
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success">
                    <i class="bi bi-check-circle-fill"></i> Password updated!
                </span>
            @endif
        </div>
    </form>
</div>
