<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CustomSanctumAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Token not provided',
                'status' => 401
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Invalid token',
                'status' => 401
            ], 401);
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Token expired',
                'status' => 401
            ], 401);
        }

        $user = $accessToken->tokenable;
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'User not found',
                'status' => 401
            ], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
