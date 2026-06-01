<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized - Please login first',
            ], 401);
        }

        // Check if user has admin role
        $user = Auth::user();
        if (!$this->hasAdminRole($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden - Admin access required',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has admin role
     */
    private function hasAdminRole($user): bool
    {
        // Check if user has role column or relationship
        if (isset($user->role) && in_array($user->role, ['admin', 'super_admin', 'manager'])) {
            return true;
        }

        // Check if user has roles relationship
        if (method_exists($user, 'roles') && $user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        // Check if user is super admin by ID (temporary solution)
        if ($user->id === 1) {
            return true;
        }

        return false;
    }
}
