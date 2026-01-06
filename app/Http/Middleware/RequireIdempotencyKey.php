<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireIdempotencyKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = trim((string) $request->header('Idempotency-Key'));
        if (!$key || $key === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Idempotency-Key header is required',
            ], 400);
        }

        //length guard (prevents abuse)
        if (mb_strlen($key) > 255) {
            return response()->json([
                'status' => 'error',
                'message' => 'Idempotency-Key is too long (max 255)',
            ], 400);
        }

        return $next($request);
    }
}
