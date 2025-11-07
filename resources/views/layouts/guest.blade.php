<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SWEEP') }} - @yield('title', 'Login')</title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

        <!-- Custom Styles -->
        <style>
            :root {
                --sweep-primary: #2E8B57;
                --sweep-secondary: #F4A300;
                --sweep-accent: #4FB4A2;
                --sweep-background: #F9FAFB;
                --sweep-text: #333333;
            }

            body {
                background: linear-gradient(135deg, var(--sweep-primary) 0%, var(--sweep-accent) 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            .auth-card {
                background: white;
                border-radius: 1rem;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                padding: 2.5rem;
                max-width: 450px;
                width: 100%;
            }

            .logo-container {
                text-align: center;
                margin-bottom: 2rem;
            }

            .logo-icon {
                font-size: 4rem;
                color: var(--sweep-primary);
            }

            .logo-text {
                font-size: 2rem;
                font-weight: 700;
                color: var(--sweep-primary);
                margin: 0;
            }

            .logo-tagline {
                color: #6c757d;
                font-size: 0.9rem;
                margin-top: 0.25rem;
            }

            .btn-primary {
                background-color: var(--sweep-accent);
                border-color: var(--sweep-accent);
                padding: 0.75rem;
                font-weight: 600;
            }

            .btn-primary:hover {
                background-color: #3a9d8f;
                border-color: #3a9d8f;
            }

            .form-control:focus {
                border-color: var(--sweep-accent);
                box-shadow: 0 0 0 0.25rem rgba(79, 180, 162, 0.25);
            }

            .form-check-input:checked {
                background-color: var(--sweep-accent);
                border-color: var(--sweep-accent);
            }

            a {
                color: var(--sweep-accent);
                text-decoration: none;
            }

            a:hover {
                color: var(--sweep-primary);
                text-decoration: underline;
            }

            @media (max-width: 576px) {
                .auth-card {
                    margin: 1rem;
                    padding: 1.5rem;
                }
            }
        </style>

        @stack('styles')
    </head>
    <body>
        <div class="auth-card">
            <div class="logo-container">
                <i class="bi bi-recycle logo-icon"></i>
                <h1 class="logo-text">SWEEP</h1>
                <p class="logo-tagline">Solid Waste Evaluation & Efficiency Platform</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{ $slot }}
        </div>

        <!-- Bootstrap 5 JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        @stack('scripts')
    </body>
</html>
