<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'rate.limit.login' => \App\Http\Middleware\RateLimitLogin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authorization exceptions with redirect to dashboard
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to access this resource.'
                ], 403);
            }

            // Redirect authenticated users to their dashboard with error message
            if (auth()->check()) {
                return redirect()
                    ->route('dashboard')
                    ->with('error', 'You do not have permission to access this resource.');
            }

            // Redirect unauthenticated users to login
            return redirect()->route('login');
        });

        // Handle authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            return redirect()->guest(route('login'));
        });
    })->create();
