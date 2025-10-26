<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('custom.sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Post routes
    Route::apiResource('post', PostController::class);
    Route::post('/post/{id}/update-image', [PostController::class, 'updateImage']);
    
    // Comment routes
    Route::prefix('post/{postId}')->group(function () {
        Route::apiResource('comment', CommentController::class);
    });
});