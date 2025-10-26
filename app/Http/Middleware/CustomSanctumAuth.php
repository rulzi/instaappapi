<?php

namespace App\Http\Middleware;

use App\Services\ApiResponseService;
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
            return ApiResponseService::unauthorized('Token not provided');
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return ApiResponseService::unauthorized('Invalid token');
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return ApiResponseService::unauthorized('Token expired');
        }

        $user = $accessToken->tokenable;
        
        if (!$user) {
            return ApiResponseService::unauthorized('User not found');
        }

        Auth::setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
