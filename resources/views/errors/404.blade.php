<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>404 - Page Not Found | SWEEP</title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            .error-container {
                text-align: center;
                max-width: 600px;
                padding: 2rem;
            }

            .error-icon {
                font-size: 8rem;
                color: var(--sweep-accent);
                margin-bottom: 1rem;
            }

            .error-code {
                font-size: 4rem;
                font-weight: 700;
                color: var(--sweep-primary);
                margin-bottom: 1rem;
            }

            .error-title {
                font-size: 2rem;
                font-weight: 600;
                color: var(--sweep-text);
                margin-bottom: 1rem;
            }

            .error-message {
                font-size: 1.1rem;
                color: #6c757d;
                margin-bottom: 2rem;
            }

            .btn-primary {
                background-color: var(--sweep-accent);
                border-color: var(--sweep-accent);
                padding: 0.75rem 2rem;
                font-weight: 600;
            }

            .btn-primary:hover {
                background-color: #3a9d8f;
                border-color: #3a9d8f;
            }

            .btn-secondary {
                background-color: var(--sweep-primary);
                border-color: var(--sweep-primary);
                padding: 0.75rem 2rem;
                font-weight: 600;
            }

            .btn-secondary:hover {
                background-color: #256f45;
                border-color: #256f45;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <i class="bi bi-question-circle error-icon"></i>
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </p>
            <div class="d-flex gap-3 justify-content-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-house-door"></i> Go to Dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                @endauth
            </div>
        </div>
    </body>
</html>
