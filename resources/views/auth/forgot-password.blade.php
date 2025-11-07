@section('title', 'Forgot Password')

<x-guest-layout>
    <div class="mb-4 text-muted">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-envelope"></i> Email Password Reset Link
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}">
                <i class="bi bi-arrow-left"></i> Back to Login
            </a>
        </div>
    </form>
</x-guest-layout>
