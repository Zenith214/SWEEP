<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');

        if (!$email) {
            return $next($request);
        }

        // Create a unique key for this email address
        $key = 'login-attempts:' . strtolower($email);

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$minutes} minutes.",
            ])->withInput($request->only('email'));
        }

        // Increment the attempt counter
        // The attempts will be cleared on successful login in the controller
        RateLimiter::hit($key, 1800); // 1800 seconds = 30 minutes

        $response = $next($request);

        // If login was successful (no validation errors), clear the rate limiter
        if ($response->getStatusCode() === 302 && !session()->has('errors')) {
            RateLimiter::clear($key);
        }

        return $response;
    }
}
