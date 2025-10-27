<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Transformers\PermissionTransformer;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Fractal\Facades\Fractal;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $permissions = Permission::create([
                'user_id' => $user->id,
            ]);

            $user->load('permissions');

            $token = $user->createToken('auth_token')->plainTextToken;

            $data = [
                'user' => Fractal::item($user, new UserTransformer())->toArray(),
                'token' => $token,
                'token_type' => 'Bearer',
            ];

            return ApiResponseService::created($data, 'User registered successfully');
        } catch (ValidationException $e) {
            return ApiResponseService::validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return ApiResponseService::unauthorized('Invalid credentials');
            }

            $user = User::with('permissions')->where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            $data = [
                'user' => Fractal::item($user, new UserTransformer())->toArray(),
                'token' => $token,
                'token_type' => 'Bearer',
            ];

            return ApiResponseService::success($data, 'Login successful');
        } catch (ValidationException $e) {
            return ApiResponseService::validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return ApiResponseService::success(null, 'Logged out successfully');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            $user->load('permissions');

            $data = [
                'user' => Fractal::item($user, new UserTransformer())->toArray(),
            ];

            return ApiResponseService::success($data, 'User data retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('Failed to retrieve user data: ' . $e->getMessage());
        }
    }
}
