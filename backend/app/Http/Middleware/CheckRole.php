<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage: Route::middleware(['auth:sanctum', 'role:seller,admin'])
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  Allowed roles (e.g. 'admin', 'seller', 'customer')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.',
                'data'    => null,
            ], 401);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have permission to access this resource.',
                'data'    => null,
            ], 403);
        }

        return $next($request);
    }
}
