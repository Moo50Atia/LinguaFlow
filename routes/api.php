<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ┌─────────────────────────────────────────┐
// │         PUBLIC ROUTES (No Auth)         │
// └─────────────────────────────────────────┘
Route::prefix('public')->group(function () {
    // Sprint 9: Landing stats, public courses
});

Route::prefix('auth')->group(function () {
    // Sprint 1: Register, Login, Google OAuth
});

Route::prefix('certificates/verify')->group(function () {
    // Sprint 8: Public certificate verification
});

// ┌─────────────────────────────────────────┐
// │       AUTHENTICATED ROUTES              │
// └─────────────────────────────────────────┘
Route::middleware('auth:sanctum')->group(function () {

    // Auth (logout, onboarding) - Sprint 1
    
    // Dashboard - Sprint 7
    
    // Learning (courses, lessons, instructors) - Sprint 2 & 3
    
    // Booking - Sprint 4
    
    // Community (moments, connect) - Sprint 5
    
    // Chat - Sprint 6
    
    // Profile - Sprint 8
    
    // Notifications - Sprint 7
    
    // Shared - Sprint 0
    Route::post('translate', [\App\Http\Controllers\Api\TranslationController::class, 'translate']);

    // ┌─────────────────────────────────────┐
    // │    INSTRUCTOR-ONLY ROUTES           │
    // └─────────────────────────────────────┘
    Route::middleware('role:instructor,admin')->prefix('instructor')->group(function () {
        // Sprint 2: Course/Lesson/Quiz/Material CRUD
        // Sprint 4: Slots management
        // Sprint 7: Dashboard stats
    });
});
