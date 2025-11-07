<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SWEEP') }} - @yield('title', 'Dashboard')</title>

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
                background-color: var(--sweep-background);
                color: var(--sweep-text);
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            .navbar {
                background-color: var(--sweep-primary) !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .navbar-brand, .navbar-nav .nav-link {
                color: white !important;
            }

            .navbar-nav .nav-link:hover {
                color: var(--sweep-secondary) !important;
            }

            .btn-primary {
                background-color: var(--sweep-accent);
                border-color: var(--sweep-accent);
            }

            .btn-primary:hover {
                background-color: #3a9d8f;
                border-color: #3a9d8f;
            }

            .btn-secondary {
                background-color: var(--sweep-secondary);
                border-color: var(--sweep-secondary);
            }

            .btn-success {
                background-color: var(--sweep-primary);
                border-color: var(--sweep-primary);
            }

            .sidebar {
                min-height: calc(100vh - 56px);
                background-color: white;
                border-right: 1px solid #dee2e6;
                padding: 1.5rem 0;
            }

            .sidebar .nav-link {
                color: var(--sweep-text);
                padding: 0.75rem 1.5rem;
                border-left: 3px solid transparent;
            }

            .sidebar .nav-link:hover {
                background-color: var(--sweep-background);
                border-left-color: var(--sweep-accent);
            }

            .sidebar .nav-link.active {
                background-color: var(--sweep-background);
                border-left-color: var(--sweep-primary);
                font-weight: 600;
            }

            .card {
                border: none;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            .badge-administrator {
                background-color: var(--sweep-primary);
            }

            .badge-collection_crew {
                background-color: var(--sweep-secondary);
            }

            .badge-resident {
                background-color: var(--sweep-accent);
            }

            .main-content {
                padding: 2rem;
            }

            @media (max-width: 768px) {
                .sidebar {
                    min-height: auto;
                    border-right: none;
                    border-bottom: 1px solid #dee2e6;
                }

                .main-content {
                    padding: 1rem;
                }
            }
        </style>

        @stack('styles')
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <i class="bi bi-recycle"></i> SWEEP
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid">
            <div class="row">
                @if(isset($sidebar) && $sidebar)
                    <!-- Sidebar -->
                    <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                        {{ $sidebar }}
                    </nav>
                    <!-- Main Content Area -->
                    <main class="col-md-9 col-lg-10 main-content">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
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
                    </main>
                @else
                    <!-- Full Width Content -->
                    <main class="col-12 main-content">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
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
                    </main>
                @endif
            </div>
        </div>

        <!-- Bootstrap 5 JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- SWEEP Error Handling Utilities -->
        <script src="{{ asset('js/error-handling.js') }}"></script>
        
        @stack('scripts')
    </body>
</html>
