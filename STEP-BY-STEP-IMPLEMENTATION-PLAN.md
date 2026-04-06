# LinguaFlow — Step-by-Step Implementation Plan

> **Document Type**: Implementation Roadmap  
> **Version**: 1.0  
> **Date**: April 2026  
> **Architecture Reference**: [Final-Arch.md](./Final-Arch.md) (Hybrid: Service + Action + Query)  
> **Frontend Reference**: [FRONTEND-ARCHITECTURE-GUIDE.md](../The%20React%20For%20Translation%20Company%20V2/FRONTEND-ARCHITECTURE-GUIDE.md)  
> **Feature Map Reference**: [BACKEND-FEATURES-ANALYSIS.md](./BACKEND-FEATURES-ANALYSIS.md)  
> **Total Sprints**: 10  
> **Estimated Files to Create**: ~121

---

## How to Use This Plan

This plan is designed for **token-efficient, incremental prompting**. Each Sprint is self-contained and can be built with a single prompt using this template:

```
"Build Sprint X: [Sprint Name] following the STEP-BY-STEP-IMPLEMENTATION-PLAN.md. 
Create all files listed in that sprint. Reference Final-Arch.md for patterns."
```

After each Sprint, verify the work before moving to the next one.

---

## Sprint Overview

| Sprint | Name | Priority | Dependencies | New Files | Key Deliverables |
|--------|------|----------|--------------|-----------|-----------------|
| 0 | Core Foundation | 🔴 Critical | None | ~12 | Base classes, exception handling, middleware, route structure |
| 1 | Authentication & Onboarding | 🔴 Critical | Sprint 0 | ~14 | Register, Login, OAuth, Onboarding, Sanctum tokens |
| 2 | Instructor & Course Management | 🔴 Critical | Sprint 1 | ~18 | Instructor CRUD, Course CRUD, Lesson CRUD, Materials, Quizzes |
| 3 | Learning & Enrollment | 🔴 Critical | Sprint 2 | ~12 | Enroll, Lesson interface, Quiz evaluation, Lesson completion |
| 4 | Booking System | 🟡 High | Sprint 2 | ~8 | Instructor slots, Session booking, Calendar availability |
| 5 | Community & Social | 🟡 High | Sprint 1 | ~10 | Moments feed, Corrections, Likes, Partner discovery |
| 6 | Chat & Messaging | 🟡 High | Sprint 1 | ~10 | Direct messages, Group chats, Corrections, Real-time prep |
| 7 | Dashboard & Analytics | 🟢 Medium | Sprints 3-4 | ~8 | Student progress, Streaks, Instructor dashboard stats |
| 8 | Profile, Certificates & Subscriptions | 🟢 Medium | Sprint 3 | ~10 | Profile CRUD, PDF generation, Subscription management |
| 9 | Media, Problems & Public API | 🟢 Medium | Sprint 1 | ~8 | Podcasts, Translation Q&A, Landing page data |
| 10 | React Frontend Integration | 🔵 Final | All Sprints | ~5 | Replace all mock data, API client setup, Token management |

---

## Sprint 0: Core Foundation

> **Goal**: Establish the architectural skeleton — base classes, shared utilities, error handling, route structure, and middleware. Every subsequent sprint depends on this.

### Prerequisites
- [x] Laravel project initialized
- [x] 22 Migrations created (26 tables)
- [x] 26 Eloquent Models created with relationships
- [x] 26 Factories + 6 Seeders created
- [ ] `php artisan migrate:fresh --seed` runs successfully

### Step 0.1 — Directory Structure

Create the following empty directories:

```
app/
├── Actions/
│   ├── Auth/
│   ├── Learning/
│   ├── Chat/
│   ├── Community/
│   ├── Certificates/
│   └── Profile/
├── Queries/
│   ├── Dashboard/
│   ├── Learning/
│   ├── Community/
│   ├── Instructor/
│   └── Chat/
├── Services/
├── Http/
│   ├── Controllers/Api/
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── Learning/
│   │   ├── Community/
│   │   ├── Chat/
│   │   ├── Media/
│   │   ├── Problems/
│   │   ├── Profile/
│   │   └── Shared/
│   └── Resources/
└── Policies/
```

### Step 0.2 — Base API Controller

**File**: `app/Http/Controllers/Api/BaseController.php`

**Purpose**: Provides standardized JSON response helpers for all API controllers.

**Methods to implement**:
- `sendSuccess($data, $message, $code = 200)` → `{ success: true, data: ..., message: ... }`
- `sendError($message, $code = 400)` → `{ success: false, message: ... }`
- `sendCreated($data, $message)` → wraps `sendSuccess` with 201
- `sendDeleted($message)` → wraps `sendSuccess` with 200

### Step 0.3 — Global Exception Handler

**File**: `app/Exceptions/Handler.php` (modify existing)

**Purpose**: Catch all exceptions and return consistent JSON for the React SPA.

**Handle these cases**:
- `ValidationException` → 422 with field-level errors
- `AuthenticationException` → 401 with `{ message: 'Unauthenticated' }`
- `AuthorizationException` → 403 with `{ message: 'Forbidden' }`
- `ModelNotFoundException` → 404 with `{ message: 'Resource not found' }`
- `ThrottleRequestsException` → 429 with `{ message: 'Too many requests' }`
- `Throwable` (fallback) → 500 with `{ message: 'Server error' }` (hide details in production)

### Step 0.4 — CheckRole Middleware

**File**: `app/Http/Middleware/CheckRole.php`

**Purpose**: Enforce `role:instructor` and `role:admin` on protected routes.

**Logic**:
```php
public function handle($request, Closure $next, ...$roles)
{
    if (!in_array($request->user()->role, $roles)) {
        abort(403, 'Insufficient permissions.');
    }
    return $next($request);
}
```

**Register** in `bootstrap/app.php` (Laravel 11) or `app/Http/Kernel.php` (Laravel 10):
```php
'role' => \App\Http\Middleware\CheckRole::class,
```

### Step 0.5 — Shared Utility Services

**File**: `app/Services/FileUploadService.php`

**Methods**:
- `store(UploadedFile $file, string $directory): string` → Stores to `storage/app/public/{directory}/`, returns public URL
- `delete(string $path): void` → Removes file from storage

**File**: `app/Services/NotificationService.php`

**Methods**:
- `create(int $userId, string $type, string $title, string $body, array $data = []): Notification`
- `markRead(Notification $notification): void`
- `markAllRead(int $userId): void`

**File**: `app/Services/TranslationService.php`

**Methods**:
- `translate(string $text, string $targetLang, ?string $sourceLang = null): array`  
  (Calls external API — Google Translate / DeepL. Return `['translated_text' => ..., 'detected_source' => ...]`)

### Step 0.6 — API Route Structure

**File**: `routes/api.php`

Set up the top-level route groups with middleware:

```php
<?php
use Illuminate\Support\Facades\Route;

// ┌─────────────────────────────────────────┐
// │         PUBLIC ROUTES (No Auth)          │
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

    // Auth (logout, onboarding)
    // Sprint 1

    // Dashboard
    // Sprint 7

    // Learning (courses, lessons, instructors)
    // Sprint 2 & 3

    // Booking
    // Sprint 4

    // Community (moments, connect)
    // Sprint 5

    // Chat
    // Sprint 6

    // Profile
    // Sprint 8

    // Notifications
    // Sprint 7

    // Translation
    // Sprint 0 (shared)
    Route::post('translate', [TranslationController::class, 'translate']);

    // ┌─────────────────────────────────────┐
    // │    INSTRUCTOR-ONLY ROUTES           │
    // └─────────────────────────────────────┘
    Route::middleware('role:instructor,admin')->prefix('instructor')->group(function () {
        // Sprint 2: Course/Lesson/Quiz/Material CRUD
        // Sprint 4: Slots management
        // Sprint 7: Dashboard stats
    });
});
```

### Step 0.7 — CORS Configuration

**File**: `config/cors.php`

Ensure React dev server (`localhost:5173`) can communicate:
```php
'allowed_origins' => ['http://localhost:5173', 'http://localhost:3000'],
'supports_credentials' => true,
```

### Step 0.8 — Sanctum Configuration

**File**: `config/sanctum.php`

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:5173,localhost:3000')),
```

### 📋 Sprint 0 — Verification Checklist

```
[ ] Directory structure created
[ ] BaseController has sendSuccess/sendError helpers
[ ] Exception Handler returns JSON for all error types
[ ] CheckRole middleware registered and working
[ ] FileUploadService can store and delete files
[ ] NotificationService can create and mark notifications
[ ] TranslationService stub exists (external API call)
[ ] routes/api.php has skeleton route groups
[ ] CORS allows React dev server
[ ] Sanctum configured for SPA authentication
[ ] `php artisan migrate:fresh --seed` still passes
```

---

## Sprint 1: Authentication & Onboarding

> **Goal**: Complete authentication lifecycle — Register, Login, Google OAuth, Onboarding Wizard, Logout.  
> **React Entities**: `Auth` (4 sub-components: AuthSelection, LoginView, SignupView, OnboardingWizard)  
> **Models Used**: `User`, `UserLanguage`, `UserInterest`, `QuizResult`  
> **Endpoints**: 5 routes

### Step 1.1 — FormRequests

| File | Validation Rules |
|------|-----------------|
| `app/Http/Requests/Auth/RegisterRequest.php` | `name: required\|string\|max:255`, `email: required\|email\|unique:users`, `password: required\|min:8\|confirmed`, `gender: nullable\|in:male,female` |
| `app/Http/Requests/Auth/LoginRequest.php` | `email: required\|email`, `password: required\|string` |
| `app/Http/Requests/Auth/OnboardingRequest.php` | `native_language: required\|string`, `learning_languages: required\|array\|min:1`, `learning_languages.*.name: required\|string`, `learning_languages.*.level: required\|in:A1,A2,B1,B2,C1,C2`, `interests: required\|array\|min:1`, `interests.*: required\|string` |

### Step 1.2 — AuthService (CRUD/Simple Logic)

**File**: `app/Services/AuthService.php`

| Method | Logic |
|--------|-------|
| `register(array $data): array` | Hash password → `User::create()` → Generate Sanctum token → Return `['user' => ..., 'token' => ...]` |
| `login(array $credentials): array` | `Auth::attempt()` → Update `is_online = true`, `last_seen_at = now()` → Generate token → Return user + token |
| `logout(User $user): void` | `$user->currentAccessToken()->delete()` → Set `is_online = false` |

### Step 1.3 — CompleteOnboardingAction (Complex Logic)

**File**: `app/Actions/Auth/CompleteOnboardingAction.php`

**Why Action**: Touches 3+ models in a single transaction (User, UserLanguage, UserInterest).

**Logic**:
1. Begin DB transaction
2. Update `User.native_language` and `User.cefr_level`
3. Bulk-insert `UserLanguage` records for each learning language (with flag emoji lookup)
4. Bulk-insert `UserInterest` records
5. Commit transaction
6. Return updated user with relations loaded

### Step 1.4 — HandleGoogleOAuthAction

**File**: `app/Actions/Auth/HandleGoogleOAuthAction.php`

**Why Action**: External OAuth flow with find-or-create branching.

**Logic**:
1. Receive Google user data from Socialite
2. Find user by `google_id` OR `email`
3. If found → update `google_id`, generate token
4. If not found → create new User with Google data, generate token
5. Update `is_online = true`
6. Return user + token

### Step 1.5 — API Resource

**File**: `app/Http/Resources/UserResource.php`

**Fields**: `id`, `name`, `email`, `avatar`, `bio`, `gender`, `location`, `native_language`, `cefr_level`, `role`, `is_vip`, `is_online`, `learningLanguages` (nested), `interests` (nested), `created_at`

### Step 1.6 — Controller

**File**: `app/Http/Controllers/Api/AuthController.php`

| Method | Route | Layer |
|--------|-------|-------|
| `register(RegisterRequest)` | `POST /api/auth/register` | → `AuthService@register` |
| `login(LoginRequest)` | `POST /api/auth/login` | → `AuthService@login` |
| `googleRedirect()` | `GET /api/auth/google/redirect` | → Socialite redirect |
| `googleCallback()` | `GET /api/auth/google/callback` | → `HandleGoogleOAuthAction` |
| `onboarding(OnboardingRequest)` | `POST /api/auth/onboarding` | → `CompleteOnboardingAction` |
| `logout()` | `POST /api/auth/logout` | → `AuthService@logout` |

### Step 1.7 — Routes

```php
// routes/api.php — Auth section
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('google/redirect', [AuthController::class, 'googleRedirect']);
    Route::get('google/callback', [AuthController::class, 'googleCallback']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/onboarding', [AuthController::class, 'onboarding']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});
```

### Step 1.8 — React Integration Points

| React File | Current State | Replace With |
|------------|---------------|--------------|
| `entities/Auth/components/LoginView.jsx` | Mock `login()` via AppContext | `POST /api/auth/login` → store token in `localStorage` → `login(userData)` |
| `entities/Auth/components/SignupView.jsx` | Mock registration | `POST /api/auth/register` → navigate to onboarding |
| `entities/Auth/components/OnboardingWizard.jsx` | Mock step completion | `POST /api/auth/onboarding` → navigate to dashboard |
| `context/AppContext.jsx` | No token management | Add `token` state, `Authorization: Bearer {token}` header helper |

### 📋 Sprint 1 — Verification Checklist

```
[ ] POST /api/auth/register → creates user → returns token (test with Postman)
[ ] POST /api/auth/login → validates credentials → returns token
[ ] POST /api/auth/logout → revokes token → sets is_online = false
[ ] POST /api/auth/onboarding → creates UserLanguage + UserInterest records
[ ] Unauthenticated requests to protected routes → 401 JSON response
[ ] Invalid registration data → 422 with field errors
[ ] Duplicate email registration → 422 with 'email already taken'
```

---

## Sprint 2: Instructor & Course Management

> **Goal**: Build the instructor dashboard CRUD — Courses, Lessons, Materials, Quizzes, Availability Slots.  
> **React Entity**: `Instructor` (CallDashboard → MyCourses, QuizzesTab, AvailabilityTab)  
> **Models Used**: `Instructor`, `Course`, `Lesson`, `LessonMaterial`, `QuizQuestion`, `InstructorSlot`  
> **Endpoints**: ~15 routes

### Step 2.1 — FormRequests

| File | Key Rules |
|------|-----------|
| `Requests/Learning/StoreCourseRequest.php` | `title: required\|max:255`, `level: required\|in:A1,A2,...`, `language: required`, `price: required\|numeric`, `category: required\|in:Medical,Legal,...` |
| `Requests/Learning/UpdateCourseRequest.php` | Same as Store but all fields `sometimes` |
| `Requests/Learning/StoreLessonRequest.php` | `title: required`, `order: required\|integer`, `level: required`, `status: required\|in:active,recorded` |
| `Requests/Learning/StoreMaterialRequest.php` | `name: required`, `file: required\|file\|max:20480` |
| `Requests/Learning/StoreQuizQuestionRequest.php` | `question_text: required`, `options: required\|array\|min:2`, `correct_option: required` |
| `Requests/Learning/StoreSlotRequest.php` | `date: required\|date\|after_or_equal:today`, `start_time: required`, `end_time: required\|after:start_time` |

### Step 2.2 — Services (CRUD)

| Service | Methods |
|---------|---------|
| `CourseService` | `list(instructorId)`, `create(data)`, `update(course, data)`, `delete(course)`, `publish(course)` |
| `LessonService` | `create(courseId, data)`, `update(lesson, data)`, `delete(lesson)`, `reorder(courseId, orderMap)` |
| `LessonMaterialService` | `upload(lessonId, file, name)`, `delete(material)` |
| `QuizService` | `listForCourse(courseId)`, `create(data)`, `update(question, data)`, `delete(question)` |
| `InstructorSlotService` | `listAvailable(instructorId, month, year)`, `create(data)`, `delete(slot)` |

### Step 2.3 — Policies

| Policy | Rules |
|--------|-------|
| `CoursePolicy` | `update/delete` → `$course->instructor->user_id === $user->id` |
| `LessonPolicy` | `create/update/delete` → course belongs to instructor |

### Step 2.4 — API Resources

| Resource | Key Fields |
|----------|-----------|
| `InstructorResource` | `id`, `user` (nested), `category`, `type`, `price_per_hour`, `bio`, `specialties`, `rating`, `total_reviews`, `total_students`, `years_experience` |
| `CourseResource` | `id`, `title`, `level`, `language`, `instructor` (nested), `total_lessons`, `price`, `category`, `enrolled_count`, `is_published` |
| `LessonResource` | `id`, `title`, `order`, `duration`, `description`, `has_quiz`, `materials_count` |
| `QuizQuestionResource` | `id`, `question`, `options`, `correct_answer`, `order` |

### Step 2.5 — Controllers

| Controller | Routes | Layer |
|------------|--------|-------|
| `InstructorController` | `GET /api/instructors`, `GET /api/instructors/{id}` | Query / Service |
| `CourseController` | `GET/POST/PUT/DELETE /api/instructor/courses` | Service |
| `LessonController` | `POST/PUT/DELETE /api/instructor/courses/{id}/lessons` | Service |
| `QuizController` | `GET/POST/PUT/DELETE /api/instructor/quizzes` | Service |
| `SlotController` | `GET/POST/DELETE /api/instructor/slots` | Service |

### Step 2.6 — Routes

```php
// Instructor-only routes
Route::middleware('role:instructor,admin')->prefix('instructor')->group(function () {
    Route::apiResource('courses', InstructorCourseController::class);
    Route::post('courses/{course}/lessons', [LessonController::class, 'store']);
    Route::put('lessons/{lesson}', [LessonController::class, 'update']);
    Route::delete('lessons/{lesson}', [LessonController::class, 'destroy']);
    Route::post('lessons/{lesson}/materials', [MaterialController::class, 'store']);
    Route::apiResource('quizzes', QuizController::class)->except('show');
    Route::apiResource('slots', SlotController::class)->except(['show', 'update']);
});

// Public instructor viewing (authenticated students)
Route::get('instructors', [InstructorController::class, 'index']);
Route::get('instructors/{instructor}', [InstructorController::class, 'show']);
Route::get('instructors/{instructor}/slots', [InstructorController::class, 'slots']);
```

### Step 2.7 — Queries (Complex Reads)

| Query | Purpose |
|-------|---------|
| `InstructorCatalogQuery` | Multi-filter search: category, type, gender, level. Eager loads `user`, `courses`, `reviews`. |
| `InstructorSlotsQuery` | Monthly availability: groups non-booked slots by date for calendar display. |

### Step 2.8 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `LearnHub.jsx` (Instructors tab) | `MOCK_INSTRUCTORS` → `GET /api/instructors?category=&type=&gender=` |
| `InstructorProfile.jsx` | Static instructor data → `GET /api/instructors/{id}` |
| `CallDashboard.jsx` → `MyCourses` | Inline mock courses → `GET /api/instructor/courses` |
| `AvailabilityTab.jsx` | Mock slots → `GET/POST/DELETE /api/instructor/slots` |

### 📋 Sprint 2 — Verification Checklist

```
[ ] GET /api/instructors → returns paginated list with filters working
[ ] GET /api/instructors/{id} → returns full profile with courses, reviews, slots
[ ] POST /api/instructor/courses → instructor can create course
[ ] PUT /api/instructor/courses/{id} → owner can update, others get 403
[ ] POST /api/instructor/courses/{id}/lessons → lesson created with correct order
[ ] POST /api/instructor/lessons/{id}/materials → file uploaded to storage
[ ] POST/DELETE /api/instructor/slots → availability managed correctly
[ ] Non-instructor user → 403 on all /instructor/* routes
```

---

## Sprint 3: Learning & Enrollment

> **Goal**: Student-facing learning features — Enroll in courses, view lessons, take quizzes, complete lessons, earn certificates.  
> **React Entity**: `Learning` (LearnHub → CourseView, LessonInterface)  
> **Models Used**: `Enrollment`, `LessonCompletion`, `QuizResult`, `Certificate`, `StudyDay`  
> **Actions**: `EnrollStudentAction`, `CompleteLessonAction`, `EvaluateQuizAction`

### Step 3.1 — FormRequests

| File | Rules |
|------|-------|
| `Requests/Learning/StoreEnrollmentRequest.php` | `course_id: required\|exists:courses,id` |
| `Requests/Learning/CompleteLessonRequest.php` | `score: nullable\|integer\|min:0\|max:100` |
| `Requests/Learning/SubmitQuizRequest.php` | `lesson_id: required\|exists:lessons,id`, `answers: required\|array`, `answers.*.question_id: required`, `answers.*.selected_option: required` |

### Step 3.2 — Actions (Complex Workflows)

| Action | File | Multi-Model Justification |
|--------|------|--------------------------|
| `EnrollStudentAction` | `app/Actions/Learning/EnrollStudentAction.php` | Creates `Enrollment` + increments `Course.enrolled_count` + creates welcome `Notification` |
| `EvaluateQuizAction` | `app/Actions/Learning/EvaluateQuizAction.php` | Fetches `QuizQuestion` answers + calculates score + creates `QuizResult` + creates/updates `StudyDay` |
| `CompleteLessonAction` | `app/Actions/Learning/CompleteLessonAction.php` | Creates `LessonCompletion` + updates `Enrollment` progress + conditionally creates `Certificate` + sends `Notification` |

### Step 3.3 — Queries (Complex Reads)

| Query | File | Purpose |
|-------|------|---------|
| `CourseDetailsQuery` | `app/Queries/Learning/CourseDetailsQuery.php` | Full course with ordered lessons, user's enrollment progress, lesson unlock status, material counts |
| `LessonCatalogQuery` | `app/Queries/Learning/LessonCatalogQuery.php` | Filtered lesson listing by level, status, instructor |

### Step 3.4 — Policies

| Policy | Rules |
|--------|-------|
| `EnrollmentPolicy@create` | User not already enrolled in the course |
| `LessonPolicy@view` | User is enrolled in the lesson's course |

### Step 3.5 — API Resources

| Resource | Fields |
|----------|--------|
| `EnrollmentResource` | `id`, `course` (nested), `current_lesson`, `completed_lessons`, `progress`, `status` |
| `QuizResultResource` | `id`, `quiz_title`, `course_name`, `score`, `total_questions`, `passed`, `type` |
| `CertificateResource` | `id`, `title`, `certificate_number`, `level`, `category`, `issued_at` |

### Step 3.6 — Controller & Routes

```php
// Student learning routes
Route::get('courses/{course}', [CourseController::class, 'show']);           // → CourseDetailsQuery
Route::get('lessons', [LessonController::class, 'index']);                   // → LessonCatalogQuery
Route::get('lessons/{lesson}', [LessonController::class, 'show']);           // → Service
Route::post('enrollments', [EnrollmentController::class, 'store']);          // → EnrollStudentAction
Route::post('lessons/{lesson}/complete', [LessonController::class, 'complete']); // → CompleteLessonAction
Route::post('quizzes/submit', [QuizController::class, 'submit']);           // → EvaluateQuizAction
Route::post('reviews', [ReviewController::class, 'store']);                  // → ReviewService
```

### Step 3.7 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `LearnHub.jsx` (Lessons tab) | `MOCK_LESSONS` → `GET /api/lessons?level=&status=` |
| `CourseView.jsx` | `MOCK_COURSES[id]` → `GET /api/courses/{id}` |
| `LessonInterface.jsx` | Mock quiz scoring → `POST /api/quizzes/submit` then `POST /api/lessons/{id}/complete` |
| `ReviewSection.jsx` | Mock review → `POST /api/reviews` |

### 📋 Sprint 3 — Verification Checklist

```
[ ] POST /api/enrollments → creates enrollment, increments enrolled_count
[ ] Duplicate enrollment → 422 error
[ ] GET /api/courses/{id} → shows lessons with correct unlock status
[ ] POST /api/quizzes/submit → calculates score, creates QuizResult
[ ] POST /api/lessons/{id}/complete → updates progress percentage
[ ] Completing final lesson → auto-generates Certificate + Notification
[ ] Unenrolled user cannot access locked lessons → 403
```

---

## Sprint 4: Booking System

> **Goal**: Instructor session booking — view availability, book sessions, manage bookings.  
> **React Components**: `BookingModal.jsx` (3-step wizard)  
> **Models Used**: `Booking`, `InstructorSlot`  
> **Actions**: `BookInstructorSessionAction`

### Step 4.1 — FormRequests

| File | Rules |
|------|-------|
| `Requests/Learning/StoreBookingRequest.php` | `instructor_id: required\|exists`, `instructor_slot_id: required\|exists`, `booking_type: required\|in:complete-course,specific-session`, `course_style: nullable\|in:private,group`, `date: required\|date\|after_or_equal:today`, `time: required`, `notes: nullable` |

### Step 4.2 — BookInstructorSessionAction

**File**: `app/Actions/Learning/BookInstructorSessionAction.php`

**Logic**:
1. Verify slot is not already booked (`is_booked = false`)
2. Calculate price from `Instructor.price_per_hour` and booking type
3. Mark `InstructorSlot.is_booked = true`
4. Create `Booking` record with `status = pending`
5. Send `Notification` to instructor ("New booking request from {user}")
6. Return booking with instructor details

### Step 4.3 — BookingService (Simple CRUD)

**File**: `app/Services/BookingService.php`

| Method | Logic |
|--------|-------|
| `listForUser(userId)` | Fetch user's bookings with instructor eager load |
| `cancel(booking)` | Set `status = cancelled`, unmark slot `is_booked = false` |
| `confirm(booking)` | Set `status = confirmed` (instructor action) |

### Step 4.4 — Controller & Routes

```php
Route::post('bookings', [BookingController::class, 'store']);        // → BookInstructorSessionAction
Route::get('bookings', [BookingController::class, 'index']);         // → BookingService@listForUser
Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
Route::patch('bookings/{booking}/confirm', [BookingController::class, 'confirm']);
```

### Step 4.5 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `BookingModal.jsx` Step 2 | `instructor.availableSlots` → `GET /api/instructors/{id}/slots?month=&year=` |
| `BookingModal.jsx` Step 3 | Mock confirmation → `POST /api/bookings` |

### 📋 Sprint 4 — Verification Checklist

```
[ ] GET /api/instructors/{id}/slots → returns available (non-booked) slots grouped by date
[ ] POST /api/bookings → creates booking, marks slot as booked, sends notification
[ ] Booking already-booked slot → 422 error
[ ] User cannot book themselves (if instructor) → 403
[ ] PATCH /api/bookings/{id}/cancel → restores slot availability
```

---

## Sprint 5: Community & Social

> **Goal**: Moments feed, grammar corrections, likes, language partner discovery.  
> **React Entity**: `Community` (MomentsFeed, ConnectHub)  
> **Models Used**: `Moment`, `MomentCorrection`, `MomentLike`, `MomentComment`  
> **Actions**: `SubmitMomentCorrectionAction`, `DiscoverLanguagePartnersAction`

### Step 5.1 — FormRequests

| File | Rules |
|------|-------|
| `Requests/Community/StoreMomentRequest.php` | `content: required\|max:5000`, `category: required\|in:General,Grammar,...`, `images: nullable\|array\|max:4`, `images.*: image\|max:5120` |
| `Requests/Community/StoreCorrectionRequest.php` | `corrected_text: required\|max:5000` |
| `Requests/Community/StoreFriendRequest.php` | `user_id: required\|exists:users,id` |

### Step 5.2 — MomentService (CRUD)

**File**: `app/Services/MomentService.php`

| Method | Logic |
|--------|-------|
| `create(data, images)` | Store images via `FileUploadService`, create Moment |
| `delete(moment)` | Delete images from storage, delete moment |
| `toggleLike(momentId, userId)` | Find/create MomentLike, update `likes_count` cache |
| `addComment(momentId, data)` | Create MomentComment, increment `comments_count` |

### Step 5.3 — Actions (Complex Logic)

| Action | Logic |
|--------|-------|
| `SubmitMomentCorrectionAction` | Creates `MomentCorrection` + sends `Notification` to moment author with corrected text |
| `DiscoverLanguagePartnersAction` | Cross-references `UserLanguage` to compute match %, applies filters, excludes self and existing connections |

### Step 5.4 — Queries

| Query | Purpose |
|-------|---------|
| `MomentsFeedQuery` | Paginated feed with category filter, user profile, corrections, like status for current user |
| `LanguagePartnerMatchQuery` | Discovery algorithm with match % computation |

### Step 5.5 — Controller & Routes

```php
Route::get('moments', [MomentController::class, 'index']);                     // → MomentsFeedQuery
Route::post('moments', [MomentController::class, 'store']);                    // → MomentService@create
Route::delete('moments/{moment}', [MomentController::class, 'destroy']);       // → MomentService@delete
Route::post('moments/{moment}/like', [MomentController::class, 'like']);       // → MomentService@toggleLike
Route::post('moments/{moment}/corrections', [MomentController::class, 'correct']); // → SubmitMomentCorrectionAction
Route::get('users/discover', [CommunityController::class, 'discover']);        // → DiscoverLanguagePartnersAction
Route::post('friend-requests', [CommunityController::class, 'sendFriendRequest']);
```

### Step 5.6 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `MomentsFeed.jsx` | `MOCK_MOMENTS` → `GET /api/moments?filter=` |
| `MomentsFeed.jsx` (composer) | Mock post → `POST /api/moments` (multipart) |
| `MomentsFeed.jsx` (heart) | Mock like toggle → `POST /api/moments/{id}/like` |
| `ConnectHub.jsx` | `MOCK_DISCOVER_USERS` → `GET /api/users/discover?level=&gender=&online=` |

### 📋 Sprint 5 — Verification Checklist

```
[ ] GET /api/moments → paginated feed with category filter
[ ] POST /api/moments → uploads images, creates post
[ ] POST /api/moments/{id}/like → toggles like, returns updated count
[ ] POST /api/moments/{id}/corrections → creates correction + notification
[ ] DELETE /api/moments/{id} → only owner can delete → 403 for others
[ ] GET /api/users/discover → returns users with match percentages
```

---

## Sprint 6: Chat & Messaging

> **Goal**: Direct messaging, group chats, chat corrections, message sending.  
> **React Entity**: `Chat` (ChatHub)  
> **Models Used**: `Chat`, `ChatMember`, `Message`  
> **Actions**: `SendMessageAction`, `CreateGroupChatAction`

### Step 6.1 — FormRequests

| File | Rules |
|------|-------|
| `Requests/Chat/StoreMessageRequest.php` | `text: required\|max:10000`, `attachment: nullable\|file\|max:10240` |
| `Requests/Chat/StoreGroupChatRequest.php` | `name: required\|max:100`, `member_ids: required\|array\|min:1`, `member_ids.*: exists:users,id` |
| `Requests/Chat/StoreMessageCorrectionRequest.php` | `corrected_text: required\|max:10000` |

### Step 6.2 — Services (Simple CRUD)

| Service | Methods |
|---------|---------|
| `ChatService` | `leave(chat, user)`, `disband(chat)`, `getMembers(chat)` |
| `MessageService` | `list(chatId, cursor)`, `markRead(chatMember)`, `createCorrection(messageId, data)` |

### Step 6.3 — Actions (Complex Logic)

| Action | Logic |
|--------|-------|
| `SendMessageAction` | Creates `Message` + updates `Chat.updated_at` + increments `ChatMember.unread_count` for other members + broadcasts event |
| `CreateGroupChatAction` | Creates `Chat` (type=group) + creates `ChatMember` entries (creator=admin) + sends `Notification` to members |

### Step 6.4 — Queries

| Query | Purpose |
|-------|---------|
| `ChatListQuery` | User's chats with latest message, unread count, online status, type filter |

### Step 6.5 — Policies

| Policy | Rules |
|--------|-------|
| `ChatPolicy@view` | User is a member of the chat |
| `ChatPolicy@sendMessage` | User is a member of the chat |
| `ChatPolicy@delete` | User is admin of the group chat |

### Step 6.6 — Controller & Routes

```php
Route::get('chats', [ChatController::class, 'index']);                          // → ChatListQuery
Route::get('chats/{chat}/messages', [ChatController::class, 'messages']);        // → MessageService@list
Route::post('chats/{chat}/messages', [ChatController::class, 'sendMessage']);    // → SendMessageAction
Route::post('chats/group', [ChatController::class, 'createGroup']);             // → CreateGroupChatAction
Route::post('chats/{chat}/leave', [ChatController::class, 'leave']);            // → ChatService@leave
Route::delete('chats/{chat}', [ChatController::class, 'destroy']);              // → ChatService@disband
Route::post('chats/{chat}/messages/{message}/corrections', [ChatController::class, 'correct']);
```

### Step 6.7 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `ChatHub.jsx` (sidebar) | `MOCK_CHATS` → `GET /api/chats?type=direct\|group` |
| `ChatHub.jsx` (messages) | `MOCK_MESSAGES[chatId]` → `GET /api/chats/{id}/messages` |
| `ChatHub.jsx` (send) | Mock send → `POST /api/chats/{id}/messages` |
| `ChatHub.jsx` (create group) | Mock → `POST /api/chats/group` |

### 📋 Sprint 6 — Verification Checklist

```
[ ] GET /api/chats → lists user's chats with correct unread counts
[ ] GET /api/chats/{id}/messages → paginated messages (cursor)
[ ] POST /api/chats/{id}/messages → creates message, increments unread for others
[ ] POST /api/chats/group → creates group chat with members
[ ] POST /api/chats/{id}/leave → removes user from group
[ ] Non-member cannot view chat → 403
[ ] Admin-only can delete group → 403 for members
```

---

## Sprint 7: Dashboard & Analytics

> **Goal**: Student progress dashboard, study streaks, heatmap, instructor dashboard stats, notifications.  
> **React Entity**: `Dashboard` (MyProgress), `Instructor` (OverviewTab, FeedbackTab, AssessmentsTab)  
> **Models Used**: `StudyDay`, `QuizResult`, `LessonCompletion`, `Enrollment`, `Notification`, `Booking`

### Step 7.1 — Queries (All reads — no Actions needed)

| Query | File | Purpose |
|-------|------|---------|
| `StudentProgressQuery` | `app/Queries/Dashboard/StudentProgressQuery.php` | Streak calculation, heatmap dates, completion history, quiz results, overall % |
| `EnrolledCoursesQuery` | `app/Queries/Dashboard/EnrolledCoursesQuery.php` | User enrollments with course, instructor, progress bar data |
| `UpcomingBookingsQuery` | `app/Queries/Dashboard/UpcomingBookingsQuery.php` | Future confirmed bookings with instructor details |
| `InstructorDashboardStatsQuery` | `app/Queries/Instructor/InstructorDashboardStatsQuery.php` | Revenue, active students, completion rate, avg rating, top courses, student-by-level |
| `StudentFeedbackQuery` | `app/Queries/Instructor/StudentFeedbackQuery.php` | Per-student quiz performance for instructor's courses |
| `AssessmentResultsQuery` | `app/Queries/Instructor/AssessmentResultsQuery.php` | Quiz results grouped by course/lesson |

### Step 7.2 — Daily Check-In Logic

Handled in `DashboardController@checkIn` using `NotificationService`:
1. `StudyDay::firstOrCreate(['user_id' => ..., 'date' => today()])`
2. Recalculate streak (delegate to `StudentProgressQuery` helper)
3. Return updated streak count

### Step 7.3 — Controllers & Routes

```php
// Dashboard routes (student)
Route::prefix('dashboard')->group(function () {
    Route::get('progress', [DashboardController::class, 'progress']);      // → StudentProgressQuery
    Route::get('courses', [DashboardController::class, 'courses']);        // → EnrolledCoursesQuery
    Route::post('check-in', [DashboardController::class, 'checkIn']);      // → StudyDay + streak calc
    Route::get('bookings', [DashboardController::class, 'bookings']);      // → UpcomingBookingsQuery
});

// Notification routes
Route::apiResource('notifications', NotificationController::class)->only(['index', 'destroy']);
Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead']);
Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);

// Instructor dashboard (within instructor middleware group)
Route::get('instructor/dashboard', [InstructorDashboardController::class, 'overview']);
Route::get('instructor/feedback', [InstructorDashboardController::class, 'feedback']);
Route::get('instructor/assessments', [InstructorDashboardController::class, 'assessments']);
Route::get('instructor/reviews', [InstructorDashboardController::class, 'reviews']);
```

### Step 7.4 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `MyProgress.jsx` | `MOCK_PROGRESS` → `GET /api/dashboard/progress` |
| `MyProgress.jsx` (courses) | `MOCK_COURSES` → `GET /api/dashboard/courses` |
| `MyProgress.jsx` (check-in) | Mock check-in → `POST /api/dashboard/check-in` |
| `MyProgress.jsx` (notifications) | Mock data → `GET /api/notifications` |
| `CallDashboard.jsx` → OverviewTab | Mock stats → `GET /api/instructor/dashboard` |

### 📋 Sprint 7 — Verification Checklist

```
[ ] GET /api/dashboard/progress → returns correct streak, heatmap, quiz results
[ ] POST /api/dashboard/check-in → creates StudyDay, returns streak
[ ] Double check-in same day → no duplicate, returns same streak
[ ] GET /api/dashboard/courses → returns enrolled courses with progress
[ ] GET /api/dashboard/bookings → returns only future confirmed bookings
[ ] GET /api/notifications → lists user's notifications (paginated)
[ ] PATCH /api/notifications/{id}/read → sets read_at timestamp
[ ] GET /api/instructor/dashboard → aggregated stats for instructor
```

---

## Sprint 8: Profile, Certificates & Subscriptions

> **Goal**: Profile management, certificate PDF generation, VIP subscription handling.  
> **React Entity**: `Profile` (MyProfileScreen, ProfileSettings), `Certificates` (CertificatesTab)  
> **Actions**: `GenerateCertificatePdfAction`, `ProcessSubscriptionAction`, `DeleteAccountAction`

### Step 8.1 — FormRequests

| File | Rules |
|------|-------|
| `Requests/Profile/UpdateProfileRequest.php` | `name: sometimes\|max:255`, `bio: sometimes\|max:1000`, `location: sometimes\|max:100` |
| `Requests/Profile/UpdateAvatarRequest.php` | `avatar: required\|image\|mimes:jpg,png,webp\|max:5120` |
| `Requests/Profile/ChangePasswordRequest.php` | `current_password: required`, `new_password: required\|min:8\|confirmed` |
| `Requests/Profile/StoreSubscriptionRequest.php` | `plan: required\|in:pro_learner,vip_elite,enterprise`, `payment_method: required` |

### Step 8.2 — Services (CRUD)

| Service | Methods |
|---------|---------|
| `ProfileService` | `show(user)`, `update(user, data)`, `updateAvatar(user, file)`, `changePassword(user, data)`, `changeEmail(user, data)` |
| `CertificateService` | `list(userId)`, `verify(code)` |
| `SubscriptionService` | `show(userId)`, `cancel(subscription)` |

### Step 8.3 — Actions (Complex Logic)

| Action | Logic |
|--------|-------|
| `GenerateCertificatePdfAction` | Loads certificate with course/user relations → renders Blade template via DomPDF → stores PDF to disk → returns file path |
| `ProcessSubscriptionAction` | Creates Stripe Customer → creates Stripe Subscription → stores `stripe_customer_id` and `stripe_subscription_id` → updates `User.is_vip = true` → creates local `Subscription` record |
| `DeleteAccountAction` | Revokes all Sanctum tokens → deletes avatar from storage → cancels active subscription → deletes user record (or soft-delete) |

### Step 8.4 — Controller & Routes

```php
// Profile
Route::get('profile', [ProfileController::class, 'show']);
Route::patch('profile', [ProfileController::class, 'update']);
Route::post('profile/avatar', [ProfileController::class, 'updateAvatar']);
Route::put('profile/password', [ProfileController::class, 'changePassword']);
Route::delete('profile', [ProfileController::class, 'destroy']);           // → DeleteAccountAction

// Certificates
Route::get('certificates', [CertificateController::class, 'index']);
Route::get('certificates/{certificate}/download', [CertificateController::class, 'download']); // → GenerateCertificatePdfAction

// Public certificate verification (no auth)
Route::get('certificates/verify/{code}', [CertificateController::class, 'verify']);

// Subscription
Route::get('subscription', [SubscriptionController::class, 'show']);
Route::post('subscription', [SubscriptionController::class, 'store']);     // → ProcessSubscriptionAction
Route::delete('subscription', [SubscriptionController::class, 'cancel']);
```

### Step 8.5 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `MyProfileScreen.jsx` | Mock profile → `GET /api/profile` + `PATCH /api/profile` |
| `ProfileSettings.jsx` (avatar) | Mock upload → `POST /api/profile/avatar` |
| `ProfileSettings.jsx` (password) | Mock → `PUT /api/profile/password` |
| `CertificatesTab.jsx` | Mock certificates → `GET /api/certificates` |
| `CertificatesTab.jsx` (download) | Mock → `GET /api/certificates/{id}/download` (PDF blob) |
| `VIPUpgradeModal.jsx` | Mock → `POST /api/subscription` |

### 📋 Sprint 8 — Verification Checklist

```
[ ] GET /api/profile → returns full user profile with languages, interests
[ ] PATCH /api/profile → updates specific fields only
[ ] POST /api/profile/avatar → uploads image, returns new URL
[ ] PUT /api/profile/password → validates current password, updates hash
[ ] GET /api/certificates → lists earned certificates
[ ] GET /api/certificates/{id}/download → returns PDF file
[ ] GET /api/certificates/verify/{code} → public verification works without auth
[ ] POST /api/subscription → creates Stripe subscription (or mock)
[ ] DELETE /api/profile → deletes account and cascades
```

---

## Sprint 9: Media, Problems & Public API

> **Goal**: Podcasts CRUD, Translation Q&A forum, Public landing page data.  
> **React Entities**: `Media` (PodcastTab), `Problems` (ProblemsTab), `Guest` (LandingView)  
> **Models Used**: `Podcast`, `Problem*` (new), `Course`, `User`, `Instructor`

### Step 9.1 — New Migrations (if not already created)

| Migration | Table | Columns |
|-----------|-------|---------|
| `create_problems_table` | `problems` | `id`, `user_id`, `title`, `type` (Writing/Reading/Lesson), `level`, `description`, `status` (Open/In Discussion/Resolved), `upvotes_count`, `comments_count` |
| `create_problem_comments_table` | `problem_comments` | `id`, `problem_id`, `user_id`, `body` |
| `create_problem_votes_table` | `problem_votes` | `id`, `problem_id`, `user_id` (unique composite) |

### Step 9.2 — Services

| Service | Methods |
|---------|---------|
| `PodcastService` | `list(filters)`, `create(data, audio)`, `update(podcast, data)`, `delete(podcast)` |
| `ProblemService` | `list(filters)`, `create(data)`, `update(problem, data)`, `delete(problem)`, `upvote(problemId, userId)`, `addComment(problemId, data)` |
| `PublicService` | `getStats()` (total students, courses, featured instructors), `getPublicCourses(filters)` |

### Step 9.3 — Controller & Routes

```php
// Podcasts
Route::get('podcasts', [PodcastController::class, 'index']);
Route::post('podcasts', [PodcastController::class, 'store'])->middleware('role:instructor,admin');
Route::put('podcasts/{podcast}', [PodcastController::class, 'update'])->middleware('role:instructor,admin');
Route::delete('podcasts/{podcast}', [PodcastController::class, 'destroy'])->middleware('role:instructor,admin');

// Problems
Route::apiResource('problems', ProblemController::class);
Route::post('problems/{problem}/upvote', [ProblemController::class, 'upvote']);
Route::post('problems/{problem}/comments', [ProblemController::class, 'comment']);

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('stats', [PublicController::class, 'stats']);
    Route::get('courses', [PublicController::class, 'courses']);
});
```

### Step 9.4 — React Integration Points

| React Component | Replace |
|-----------------|---------|
| `PodcastTab.jsx` | Mock podcast data → `GET /api/podcasts?category=` |
| `ProblemsTab.jsx` | Mock problems → `GET /api/problems?type=&search=` |
| `LandingView.jsx` | Hardcoded stats → `GET /api/public/stats` |
| `CourseCatalogView.jsx` | Mock catalog → `GET /api/public/courses?category=&level=` |

### 📋 Sprint 9 — Verification Checklist

```
[ ] GET /api/podcasts → paginated with category filter
[ ] POST /api/podcasts → instructor can upload audio + thumbnail
[ ] Non-instructor → 403 on podcast POST/PUT/DELETE
[ ] GET /api/problems → filtered by type and search
[ ] POST /api/problems/{id}/upvote → toggles vote
[ ] GET /api/public/stats → returns counts without authentication
[ ] GET /api/public/courses → returns published courses only
```

---

## Sprint 10: React Frontend Integration

> **Goal**: Connect ALL React components to the live Laravel API. Replace every `MOCK_*` import with real API calls.

### Step 10.1 — API Client Setup

**File**: `src/services/api.js` (new file in React project)

```javascript
const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const api = {
    getToken: () => localStorage.getItem('auth_token'),

    headers: () => ({
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(api.getToken() ? { 'Authorization': `Bearer ${api.getToken()}` } : {}),
    }),

    get: async (endpoint, params = {}) => { /* ... */ },
    post: async (endpoint, data) => { /* ... */ },
    patch: async (endpoint, data) => { /* ... */ },
    put: async (endpoint, data) => { /* ... */ },
    delete: async (endpoint) => { /* ... */ },
    upload: async (endpoint, formData) => { /* multipart/form-data */ },
};

export default api;
```

### Step 10.2 — AppContext Token Management

**File**: `src/context/AppContext.jsx` (modify)

Add:
- `token` state (persisted to `localStorage`)
- `login(userData, authToken)` → saves both
- `logout()` → clears token + calls `POST /api/auth/logout`
- `isAuthenticated` computed boolean

### Step 10.3 — Entity-by-Entity Mock Replacement

| Priority | Entity | Mock Data Source | API Replacement |
|----------|--------|-----------------|-----------------|
| 1 | Auth | Inline mock login | `POST /api/auth/login`, `POST /api/auth/register` |
| 2 | Dashboard | `MOCK_PROGRESS` | `GET /api/dashboard/progress` |
| 3 | Learning | `MOCK_INSTRUCTORS`, `MOCK_LESSONS`, `MOCK_COURSES` | `GET /api/instructors`, `GET /api/lessons`, `GET /api/courses/{id}` |
| 4 | Community | `MOCK_MOMENTS`, `MOCK_DISCOVER_USERS` | `GET /api/moments`, `GET /api/users/discover` |
| 5 | Chat | `MOCK_CHATS`, `MOCK_MESSAGES` | `GET /api/chats`, `GET /api/chats/{id}/messages` |
| 6 | Media | Inline mock podcasts | `GET /api/podcasts` |
| 7 | Profile | `userProfile` from context | `GET /api/profile` |
| 8 | Certificates | Inline mock | `GET /api/certificates` |
| 9 | Problems | Inline mock | `GET /api/problems` |
| 10 | Guest/Public | Inline data | `GET /api/public/stats`, `GET /api/public/courses` |

### Step 10.4 — Loading & Error States

For each component receiving API data, implement:
- `isLoading` state → show skeleton/spinner
- `error` state → show error toast/banner
- `useEffect` with cleanup to prevent memory leaks

### Step 10.5 — Environment Configuration

**File**: `The React For Translation Company V2/.env`

```
VITE_API_URL=http://localhost:8000/api
```

### 📋 Sprint 10 — Verification Checklist

```
[ ] Full auth flow: Register → Onboarding → Dashboard works end-to-end
[ ] Dashboard loads real progress data from API
[ ] Learning hub shows real instructors and courses
[ ] Booking flow creates real booking in database
[ ] Moments feed loads and posts real content
[ ] Chat sends and receives real messages
[ ] Profile edits persist to database
[ ] Certificate download returns real PDF
[ ] Mock data file (mockData.js) no longer imported anywhere
[ ] All 401/403/404 errors display user-friendly messages
```

---

## Appendix A: Complete File Inventory

### Files Created Per Sprint

| Sprint | Controllers | FormRequests | Services | Actions | Queries | Resources | Policies | Other |
|--------|------------|-------------|----------|---------|---------|-----------|----------|-------|
| 0 | 1 (Base) | 0 | 3 | 0 | 0 | 0 | 0 | 2 (Middleware, Handler) |
| 1 | 1 | 3 | 1 | 2 | 0 | 1 | 0 | 0 |
| 2 | 5 | 6 | 5 | 0 | 2 | 4 | 2 | 0 |
| 3 | 3 | 3 | 0 | 3 | 2 | 3 | 2 | 0 |
| 4 | 1 | 1 | 1 | 1 | 0 | 1 | 1 | 0 |
| 5 | 2 | 3 | 1 | 2 | 2 | 2 | 1 | 0 |
| 6 | 1 | 3 | 2 | 2 | 1 | 2 | 1 | 0 |
| 7 | 2 | 0 | 0 | 0 | 6 | 2 | 1 | 0 |
| 8 | 3 | 4 | 3 | 3 | 0 | 1 | 1 | 0 |
| 9 | 3 | 4 | 3 | 0 | 0 | 3 | 2 | 3 (Migrations) |
| 10 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 2 (React files) |
| **Total** | **22** | **27** | **19** | **13** | **13** | **19** | **11** | **7** |

**Grand Total: ~131 new files**

---

## Appendix B: Prompt Templates for Each Sprint

Use these exact prompts to build each sprint efficiently:

### Sprint 0
```
Build Sprint 0 (Core Foundation) from STEP-BY-STEP-IMPLEMENTATION-PLAN.md.
Create: BaseController, Exception Handler (JSON), CheckRole middleware,
FileUploadService, NotificationService, TranslationService, and routes/api.php skeleton.
Follow the patterns from Final-Arch.md.
```

### Sprint 1
```
Build Sprint 1 (Authentication & Onboarding) from STEP-BY-STEP-IMPLEMENTATION-PLAN.md.
Create: RegisterRequest, LoginRequest, OnboardingRequest, AuthService,
CompleteOnboardingAction, HandleGoogleOAuthAction, UserResource, AuthController.
Wire the routes in api.php. Follow Final-Arch.md patterns.
```

### Sprint 2
```
Build Sprint 2 (Instructor & Course Management) from STEP-BY-STEP-IMPLEMENTATION-PLAN.md.
Create all FormRequests, CourseService, LessonService, LessonMaterialService,
QuizService, InstructorSlotService, InstructorCatalogQuery, InstructorSlotsQuery,
CoursePolicy, LessonPolicy, all Resources, all Controllers. Wire routes.
```

### Sprint 3
```
Build Sprint 3 (Learning & Enrollment) from STEP-BY-STEP-IMPLEMENTATION-PLAN.md.
Create: EnrollStudentAction, CompleteLessonAction, EvaluateQuizAction,
CourseDetailsQuery, LessonCatalogQuery, EnrollmentPolicy, LessonPolicy@view,
all Resources and Controllers. Wire routes.
```

### Sprints 4–9
```
Build Sprint [N] ([Sprint Name]) from STEP-BY-STEP-IMPLEMENTATION-PLAN.md.
Create all files listed for this sprint. Follow Final-Arch.md patterns.
```

### Sprint 10
```
Build Sprint 10 (React Frontend Integration) from STEP-BY-STEP-IMPLEMENTATION-PLAN.md.
Create src/services/api.js, update AppContext.jsx with token management,
and replace all MOCK_* imports in [specific entity] with real API calls.
```

---

## Appendix C: Development Environment Checklist

Before starting Sprint 0, ensure:

```
[ ] PHP 8.1+ installed
[ ] Composer installed 
[ ] MySQL/PostgreSQL running
[ ] Node.js 18+ installed (for React)
[ ] Laravel project: `composer install` completed
[ ] `.env` configured with DB credentials
[ ] `php artisan key:generate` executed
[ ] `php artisan migrate:fresh --seed` runs without errors
[ ] `php artisan storage:link` executed (for file uploads)
[ ] `php artisan serve` starts on port 8000
[ ] React project: `npm install` completed
[ ] React project: `npm run dev` starts on port 5173
[ ] Postman or Insomnia installed for API testing
```

---

> **Document Status**: Ready for implementation  
> **Architecture**: Hybrid (Service + Action + Query) per Final-Arch.md  
> **Estimated Implementation Time**: 10 sprints × 2-4 hours each = 20-40 hours  
> **Author**: Senior Software Architect  
> **Date**: April 2026
