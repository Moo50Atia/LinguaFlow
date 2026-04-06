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

    // Community (moments, connect) - Sprint 5
    Route::get('moments', [\App\Http\Controllers\Api\MomentController::class, 'index']);
    Route::post('moments', [\App\Http\Controllers\Api\MomentController::class, 'store']);
    Route::delete('moments/{moment}', [\App\Http\Controllers\Api\MomentController::class, 'destroy']);
    Route::post('moments/{moment}/like', [\App\Http\Controllers\Api\MomentController::class, 'like']);
    Route::post('moments/{moment}/corrections', [\App\Http\Controllers\Api\MomentController::class, 'correct']);
    Route::get('users/discover', [\App\Http\Controllers\Api\CommunityController::class, 'discover']);
    Route::post('friend-requests', [\App\Http\Controllers\Api\CommunityController::class, 'sendFriendRequest']);

    // Auth (logout, onboarding) - Sprint 1
    Route::post('auth/onboarding', [\App\Http\Controllers\Api\AuthController::class, 'onboarding']);
    Route::post('auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    
    // Dashboard - Sprint 7
    Route::get('dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);
    
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
    Route::get('notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::patch('notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    
    // Booking - Sprint 4
    Route::post('bookings', [\App\Http\Controllers\Api\BookingController::class, 'store']);
    Route::get('bookings', [\App\Http\Controllers\Api\BookingController::class, 'index']);
    Route::patch('bookings/{booking}/cancel', [\App\Http\Controllers\Api\BookingController::class, 'cancel']);
    Route::patch('bookings/{booking}/confirm', [\App\Http\Controllers\Api\BookingController::class, 'confirm']);
    
    // Chat - Sprint 6
    Route::get('chat', [\App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::get('chat/{user}', [\App\Http\Controllers\Api\ChatController::class, 'show']);
    Route::post('chat', [\App\Http\Controllers\Api\ChatController::class, 'store']);
    
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
        Route::get('dashboard', [\App\Http\Controllers\Api\InstructorDashboardController::class, 'index']);
    });
});
