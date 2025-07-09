<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SkillController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ExchangeController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\API\SocialAuthController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// OAuth routes
Route::prefix('auth')->group(function () {
    Route::get('/{provider}/redirect', [SocialAuthController::class, 'getRedirectUrl']);
    Route::get('/{provider}/callback', [SocialAuthController::class, 'callback']);
    Route::post('/{provider}/token', [SocialAuthController::class, 'handleToken']);
});

// Public routes for browsing
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/skills', [SkillController::class, 'index']);
Route::get('/skills/{skill}', [SkillController::class, 'show']);
Route::get('/stats', [StatsController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/users/{userId}/profile', [AuthController::class, 'getUserProfile']);

    // Skills routes
    Route::get('/my-skills', [SkillController::class, 'mySkills']);
    Route::get('/skill-matches', [SkillController::class, 'findMatches']);
    Route::post('/skills', [SkillController::class, 'store']);
    Route::put('/skills/{skill}', [SkillController::class, 'update']);
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy']);

    // Categories routes (admin only for now)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Exchanges routes
    Route::apiResource('exchanges', ExchangeController::class);

    // Messages routes
    Route::apiResource('messages', MessageController::class);
    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::get('/conversations/{userId}', [MessageController::class, 'conversation']);
    
    // User stats routes
    Route::get('/user-stats', [StatsController::class, 'userStats']);
    Route::get('/weekly-stats', [StatsController::class, 'weeklyStats']);
}); 