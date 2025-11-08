<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CollectionLog;

class EnsureLogIsEditable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the collection log from the route parameter
        $collectionLog = $request->route('collectionLog');

        // If no collection log found, let the controller handle it
        if (!$collectionLog instanceof CollectionLog) {
            return $next($request);
        }

        // Check if the log can be edited by the current user
        if (!$collectionLog->canBeEditedBy($request->user())) {
            $user = $request->user();
            
            // Determine specific error message
            if ($collectionLog->created_by !== $user->id) {
                $errorMessage = 'You can only edit your own collection logs.';
            } elseif (!$collectionLog->isEditable()) {
                $errorMessage = 'This log can no longer be edited. The 2-hour edit window has expired.';
            } else {
                $errorMessage = 'You do not have permission to edit this log.';
            }
            
            return redirect()
                ->route('crew.collections.show', $collectionLog)
                ->with('error', $errorMessage);
        }

        return $next($request);
    }
}
