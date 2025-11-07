<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has any of the specified roles
        if (!$user->hasAnyRole($roles)) {
            // Redirect to user's role-appropriate dashboard with error message
            return redirect()
                ->route('dashboard')
                ->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
