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
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::get('google/redirect', [\App\Http\Controllers\Api\AuthController::class, 'googleRedirect']);
    Route::get('google/callback', [\App\Http\Controllers\Api\AuthController::class, 'googleCallback']);
});

Route::prefix('certificates/verify')->group(function () {
    // Sprint 8: Public certificate verification
});

// ┌─────────────────────────────────────────┐
// │       AUTHENTICATED ROUTES              │
// └─────────────────────────────────────────┘
Route::middleware('auth:sanctum')->group(function () {

    // Auth (logout, onboarding) - Sprint 1
    Route::post('auth/onboarding', [\App\Http\Controllers\Api\AuthController::class, 'onboarding']);
    Route::post('auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    
    // Dashboard - Sprint 7
    
    // Learning (courses, lessons, instructors) - Sprint 2 & 3
    Route::get('instructors', [\App\Http\Controllers\Api\InstructorController::class, 'index']);
    Route::get('instructors/{instructor}', [\App\Http\Controllers\Api\InstructorController::class, 'show']);
    Route::get('instructors/{instructor}/slots', [\App\Http\Controllers\Api\InstructorController::class, 'slots']);
    
    // Sprint 3: Students Learning Endpoints
    Route::get('courses/{course}', [\App\Http\Controllers\Api\CourseController::class, 'show']);
    Route::get('lessons', [\App\Http\Controllers\Api\LessonController::class, 'index']);
    Route::get('lessons/{lesson}', [\App\Http\Controllers\Api\LessonController::class, 'show']);
    Route::post('enrollments', [\App\Http\Controllers\Api\EnrollmentController::class, 'store']);
    Route::post('lessons/{lesson}/complete', [\App\Http\Controllers\Api\LessonController::class, 'complete']);
    Route::post('quizzes/submit', [\App\Http\Controllers\Api\QuizController::class, 'submit']);
    Route::post('reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
    
    // Notifications - Sprint 7
    
    // Shared - Sprint 0
    Route::post('translate', [\App\Http\Controllers\Api\TranslationController::class, 'translate']);

    // ┌─────────────────────────────────────┐
    // │    INSTRUCTOR-ONLY ROUTES           │
    // └─────────────────────────────────────┘
    Route::middleware('role:instructor,admin')->prefix('instructor')->group(function () {
        // Sprint 2: Course/Lesson/Quiz/Material CRUD
        Route::apiResource('courses', \App\Http\Controllers\Api\InstructorCourseController::class);
        Route::post('courses/{course}/lessons', [\App\Http\Controllers\Api\LessonController::class, 'store']);
        Route::put('lessons/{lesson}', [\App\Http\Controllers\Api\LessonController::class, 'update']);
        Route::delete('lessons/{lesson}', [\App\Http\Controllers\Api\LessonController::class, 'destroy']);
        Route::post('lessons/{lesson}/materials', [\App\Http\Controllers\Api\MaterialController::class, 'store']);
        Route::apiResource('quizzes', \App\Http\Controllers\Api\QuizController::class)->except('show');
        
        // Sprint 4 & Sprint 7 stubs
        Route::apiResource('slots', \App\Http\Controllers\Api\SlotController::class)->except(['show', 'update']);
        // Dashboard stats goes here
    });
});
