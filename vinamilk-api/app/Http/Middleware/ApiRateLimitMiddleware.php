<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxRequests = 60, $minutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);
        $limit = $maxRequests;
        $decay = $minutes * 60;

        if ($this->attempt($key, $limit, $decay)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $decay,
        ], 429);
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return sha1($user->id);
        }

        return sha1($request->ip());
    }

    /**
     * Attempt to acquire lock.
     */
    protected function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        if (Redis::connection('cache')->get($key . ':lockout')) {
            return false;
        }

        $current = Redis::connection('cache')->get($key, 0);

        if ($current >= $maxAttempts) {
            Redis::connection('cache')->setex($key . ':lockout', $decaySeconds, 1);
            return false;
        }

        Redis::connection('cache')->incr($key);
        Redis::connection('cache')->expire($key, $decaySeconds);

        return true;
    }
}
