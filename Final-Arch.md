# LinguaFlow — Final Architecture Decision Record

> **Document Type**: Architecture Decision Record (ADR)  
> **Version**: 2.0 — Supersedes `ARCHITECTURE-DECISION.md`  
> **Date**: April 2026  
> **Author**: Senior Software Architect  
> **Project**: LinguaFlow — Translation & Language Learning Platform  
> **Backend**: Laravel 10/11 REST API  
> **Frontend**: React 19 SPA (11 Entities, 17+ Components)  
> **Database**: MySQL/PostgreSQL (26 Tables, 22 Migrations)

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Architecture Evolution — From Pure Actions to Hybrid](#2-architecture-evolution--from-pure-actions-to-hybrid)
3. [The Hybrid Architecture Defined](#3-the-hybrid-architecture-defined)
4. [Justification — Why Hybrid Wins for a Graduation Project](#4-justification--why-hybrid-wins-for-a-graduation-project)
5. [Project Folder Structure](#5-project-folder-structure)
6. [Interaction Flow — How the Layers Communicate](#6-interaction-flow--how-the-layers-communicate)
7. [Implementation Map — Services](#7-implementation-map--services)
8. [Implementation Map — Actions](#8-implementation-map--actions)
9. [Implementation Map — Queries](#9-implementation-map--queries)
10. [Complete Feature-to-Layer Mapping](#10-complete-feature-to-layer-mapping)
11. [File Count Analysis](#11-file-count-analysis)
12. [Final Verdict](#12-final-verdict)

---

## 1. Executive Summary

After thorough analysis of the LinguaFlow platform — comprising **11 frontend entities**, **26 Eloquent models**, **50+ documented API features**, and **22 database migrations** — this document presents the **final backend architecture decision**.

The original `ARCHITECTURE-DECISION.md` proposed a **pure Action-based pattern** (one class per operation). While theoretically optimal for SOLID compliance, practical analysis revealed that **~60% of our 50+ features are standard CRUD operations** that do not warrant isolated action classes. Wrapping a simple `Course::create($data)` in a dedicated `CreateCourseAction` class adds cognitive overhead without meaningful engineering benefit.

**The solution**: A **Hybrid Architecture** that strategically allocates each feature to the most appropriate pattern:

| Pattern | When Used | Estimated Coverage |
|---------|-----------|-------------------|
| **Service Classes** | Standard CRUD, simple business logic | ~55% of features |
| **Action Classes** | Multi-model workflows, complex logic, side effects | ~25% of features |
| **Query Classes** | Complex data retrieval, analytics, filtered searches | ~20% of features |

---

## 2. Architecture Evolution — From Pure Actions to Hybrid

### Previous Decision (v1.0 — `ARCHITECTURE-DECISION.md`)

The initial analysis scored the Action-based pattern at **92% compatibility** and recommended building **~80 Action classes** covering every CRUD operation. While this maximized SRP adherence, it introduced:

- **File explosion**: 80+ action files for a graduation project, many containing trivial 5-line `execute()` methods.
- **Navigation burden**: Finding `UpdatePodcastAction.php` among 80+ files is slower than scanning `PodcastService@update()`.
- **Redundant abstraction**: Wrapping `Podcast::findOrFail($id)->update($data)` in a dedicated class adds architectural theater without engineering substance.

### Current Decision (v2.0 — This Document)

Retain the Action pattern **exclusively where it adds genuine value** — multi-step workflows, cross-model side effects, and complex business rules. Delegate everything else to lean Service and Query classes.

> **Principle**: *"Use the simplest abstraction that correctly solves the problem. Promote to a more complex pattern only when justified by business logic complexity."*

---

## 3. The Hybrid Architecture Defined

### 3.1 Service Classes (`app/Services/`)

**Purpose**: Encapsulate standard CRUD operations and simple business logic for a single model domain.

**Characteristics**:
- One Service per model domain (e.g., `CourseService` handles `Course` model CRUD).
- Methods are short (5–20 lines), delegating to Eloquent directly.
- Injected into Controllers via Laravel's Service Container.
- May call shared utility services (e.g., `FileUploadService`).

**Example** — `PodcastService`:
```php
namespace App\Services;

use App\Models\Podcast;
use Illuminate\Http\UploadedFile;

class PodcastService
{
    public function __construct(
        private FileUploadService $fileUpload
    ) {}

    public function list(array $filters): LengthAwarePaginator
    {
        return Podcast::query()
            ->when($filters['category'] ?? null, fn($q, $cat) => $q->where('category', $cat))
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->with('instructor.user')
            ->latest()
            ->paginate(20);
    }

    public function create(array $data, UploadedFile $audio): Podcast
    {
        $data['audio_url'] = $this->fileUpload->store($audio, 'podcasts');
        return Podcast::create($data);
    }

    public function update(Podcast $podcast, array $data): Podcast
    {
        $podcast->update($data);
        return $podcast->fresh();
    }

    public function delete(Podcast $podcast): void
    {
        $this->fileUpload->delete($podcast->audio_url);
        $podcast->delete();
    }
}
```

---

### 3.2 Action Classes (`app/Actions/`)

**Purpose**: Isolate complex, multi-step business operations that touch multiple models, trigger side effects (notifications, certificate generation), or implement non-trivial algorithms.

**Characteristics**:
- One class per business operation.
- Single public method: `execute(...)`.
- Allowed to depend on Services, Models, and other Actions.
- Wraps everything in a database transaction when needed.
- Each Action has a clearly documented "why this isn't a Service method" rationale.

**Example** — `CompleteLessonAction`:
```php
namespace App\Actions\Learning;

use App\Models\{Enrollment, Lesson, LessonCompletion, Certificate};
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class CompleteLessonAction
{
    public function __construct(
        private NotificationService $notifications
    ) {}

    /**
     * Why this is an Action and not a Service method:
     * Completing a lesson involves 5 interdependent operations across
     * 4 models (LessonCompletion, Enrollment, Certificate, Notification)
     * with conditional branching (certificate generation only if course is 100%).
     */
    public function execute(Enrollment $enrollment, Lesson $lesson, int $score): array
    {
        return DB::transaction(function () use ($enrollment, $lesson, $score) {
            // 1. Record the lesson completion
            $completion = LessonCompletion::create([
                'enrollment_id' => $enrollment->id,
                'lesson_id'     => $lesson->id,
                'score'         => $score,
                'passed'        => $score >= 70,
                'completed_at'  => now(),
            ]);

            // 2. Update enrollment progress
            $totalLessons = $enrollment->course->total_lessons;
            $completedCount = $enrollment->completions()->count();
            $progress = round(($completedCount / $totalLessons) * 100, 2);

            $enrollment->update([
                'completed_lessons' => $completedCount,
                'current_lesson'    => min($lesson->order + 1, $totalLessons),
                'progress'          => $progress,
                'status'            => $progress >= 100 ? 'completed' : 'active',
            ]);

            // 3. Generate certificate if course is complete
            $certificate = null;
            if ($progress >= 100) {
                $certificate = Certificate::create([
                    'user_id'            => $enrollment->user_id,
                    'course_id'          => $enrollment->course_id,
                    'title'              => $enrollment->course->title . ' Certificate',
                    'certificate_number' => 'LF-' . strtoupper(uniqid()),
                    'level'              => $enrollment->course->level,
                    'category'           => $enrollment->course->category,
                    'issued_at'          => now(),
                ]);

                // 4. Notify user of achievement
                $this->notifications->create(
                    $enrollment->user_id,
                    'certificate_earned',
                    'Course Completed! 🎉',
                    "You earned a certificate for {$enrollment->course->title}"
                );
            }

            return [
                'completion'  => $completion,
                'progress'    => $progress,
                'certificate' => $certificate,
            ];
        });
    }
}
```

---

### 3.3 Query Classes (`app/Queries/`)

**Purpose**: Encapsulate complex read-only data retrieval operations that involve aggregations, multi-table joins, computed fields, or heavy filtering logic.

**Characteristics**:
- One class per complex query operation.
- Single public method: `execute(...)` or `get(...)`.
- Returns DTOs, arrays, or Eloquent Collections — never modifies data.
- Keeps Controllers and Services free of 30+ line query chains.
- Ideal for dashboard analytics, feed algorithms, and search/match systems.

**Example** — `StudentProgressQuery`:
```php
namespace App\Queries\Dashboard;

use App\Models\{User, StudyDay, QuizResult, LessonCompletion, Enrollment};
use Carbon\Carbon;

class StudentProgressQuery
{
    public function execute(User $user): array
    {
        $studyDays = StudyDay::where('user_id', $user->id)
            ->orderBy('date')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        return [
            'currentLevel'          => $user->cefr_level,
            'totalCoursesEnrolled'  => Enrollment::where('user_id', $user->id)->count(),
            'totalLessonsCompleted' => LessonCompletion::whereHas('enrollment',
                fn($q) => $q->where('user_id', $user->id)
            )->count(),
            'currentStreak'         => $this->calculateStreak($studyDays),
            'longestStreak'         => $this->calculateLongestStreak($studyDays),
            'totalStudyDays'        => count($studyDays),
            'studyDates'            => $studyDays,
            'overallCompletion'     => $this->calculateOverallCompletion($user),
            'completionHistory'     => $this->getCompletionHistory($user),
            'quizResults'           => QuizResult::where('user_id', $user->id)
                ->latest()
                ->limit(20)
                ->get(),
        ];
    }

    private function calculateStreak(array $dates): int { /* streak algorithm */ }
    private function calculateLongestStreak(array $dates): int { /* longest streak */ }
    private function calculateOverallCompletion(User $user): float { /* aggregate */ }
    private function getCompletionHistory(User $user): array { /* join query */ }
}
```

---

## 4. Justification — Why Hybrid Wins for a Graduation Project

### 4.1 Engineering Maturity Without Over-Engineering

| Criterion | Pure Service-Repository | Pure Action-Based | **Hybrid (Chosen)** |
|-----------|------------------------|-------------------|---------------------|
| SRP Compliance | ⚠️ Moderate — Services grow fat | ✅ Perfect — one class per operation | ✅ High — Actions/Queries for complex; Services for simple |
| File Count (estimated) | ~20 files | ~80+ files | **~45 files** |
| Discovery (finding code) | ✅ Easy — one Service per domain | ⚠️ Hard — 80+ files to navigate | ✅ Intuitive — clear folders by responsibility type |
| Testability | ⚠️ Large test setup per Service | ✅ Surgical unit tests | ✅ Precise tests for Actions; standard tests for CRUD |
| Development Velocity | ✅ Fast for CRUD | ⚠️ Slow — boilerplate overhead for simple ops | ✅ **Fast for CRUD, precise for complex logic** |
| Demonstrates Architecture Knowledge | ⚠️ Basic (everyone does this) | ✅ Advanced | ✅ **Advanced — shows judgment of when to apply patterns** |

### 4.2 The Key Insight for Evaluators

A graduation project that uses a **pure Action pattern** for simple CRUD demonstrates pattern knowledge but **poor engineering judgment**. A project that uses a **Hybrid pattern** demonstrates the harder skill: **knowing when NOT to apply a pattern**.

Senior architects evaluate:
> *"Does this candidate apply complex patterns because they're trendy, or because the problem demands it?"*

The Hybrid approach directly answers this question by showing deliberate, context-aware decision-making.

### 4.3 SOLID Compliance Score

| Principle | How the Hybrid Approach Satisfies It |
|-----------|--------------------------------------|
| **S** — Single Responsibility | Services handle CRUD. Actions handle workflows. Queries handle reads. Controllers only orchestrate. |
| **O** — Open/Closed | New features = new Action/Query classes. Existing Services stay untouched. |
| **L** — Liskov Substitution | All Actions implement an implicit `execute()` contract, making them swappable in tests. |
| **I** — Interface Segregation | Controllers depend only on the specific Service/Action/Query they need — never a God-service. |
| **D** — Dependency Inversion | All classes are injected via Laravel's Service Container. Controllers never instantiate dependencies. |

---

## 5. Project Folder Structure

```
app/
├── Actions/                        # Complex business operations (multi-model/side-effects)
│   ├── Auth/
│   │   ├── CompleteOnboardingAction.php
│   │   └── HandleGoogleOAuthAction.php
│   ├── Learning/
│   │   ├── BookInstructorSessionAction.php
│   │   ├── CompleteLessonAction.php
│   │   ├── EnrollStudentAction.php
│   │   └── EvaluateQuizAction.php
│   ├── Chat/
│   │   ├── SendMessageAction.php
│   │   └── CreateGroupChatAction.php
│   ├── Community/
│   │   ├── SubmitMomentCorrectionAction.php
│   │   └── DiscoverLanguagePartnersAction.php
│   ├── Certificates/
│   │   └── GenerateCertificatePdfAction.php
│   └── Profile/
│       ├── ProcessSubscriptionAction.php
│       └── DeleteAccountAction.php
│
├── Queries/                        # Complex read-only data retrieval
│   ├── Dashboard/
│   │   ├── StudentProgressQuery.php
│   │   ├── EnrolledCoursesQuery.php
│   │   └── UpcomingBookingsQuery.php
│   ├── Learning/
│   │   ├── InstructorCatalogQuery.php
│   │   ├── LessonCatalogQuery.php
│   │   ├── CourseDetailsQuery.php
│   │   └── InstructorSlotsQuery.php
│   ├── Community/
│   │   ├── MomentsFeedQuery.php
│   │   └── LanguagePartnerMatchQuery.php
│   ├── Instructor/
│   │   ├── InstructorDashboardStatsQuery.php
│   │   ├── StudentFeedbackQuery.php
│   │   └── AssessmentResultsQuery.php
│   └── Chat/
│       └── ChatListQuery.php
│
├── Services/                       # Standard CRUD + simple business logic
│   ├── AuthService.php
│   ├── CourseService.php
│   ├── LessonService.php
│   ├── LessonMaterialService.php
│   ├── InstructorSlotService.php
│   ├── BookingService.php
│   ├── ReviewService.php
│   ├── MomentService.php
│   ├── ChatService.php
│   ├── MessageService.php
│   ├── PodcastService.php
│   ├── ProblemService.php
│   ├── CertificateService.php
│   ├── ProfileService.php
│   ├── NotificationService.php
│   ├── SubscriptionService.php
│   ├── QuizService.php
│   ├── FileUploadService.php
│   ├── TranslationService.php
│   └── PublicService.php
│
├── Http/
│   ├── Controllers/Api/            # Thin orchestration controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── InstructorController.php
│   │   ├── CourseController.php
│   │   ├── LessonController.php
│   │   ├── BookingController.php
│   │   ├── MomentController.php
│   │   ├── ChatController.php
│   │   ├── PodcastController.php
│   │   ├── CertificateController.php
│   │   ├── ProblemController.php
│   │   ├── ProfileController.php
│   │   ├── NotificationController.php
│   │   ├── SubscriptionController.php
│   │   ├── PublicController.php
│   │   └── TranslationController.php
│   │
│   ├── Requests/                   # FormRequest validation classes (28+)
│   │   ├── Auth/
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   └── OnboardingRequest.php
│   │   ├── Learning/
│   │   │   ├── StoreCourseRequest.php
│   │   │   ├── UpdateCourseRequest.php
│   │   │   ├── StoreLessonRequest.php
│   │   │   ├── StoreBookingRequest.php
│   │   │   ├── StoreEnrollmentRequest.php
│   │   │   ├── CompleteLessonRequest.php
│   │   │   ├── SubmitQuizRequest.php
│   │   │   ├── StoreReviewRequest.php
│   │   │   ├── StoreSlotRequest.php
│   │   │   ├── StoreMaterialRequest.php
│   │   │   └── StoreQuizQuestionRequest.php
│   │   ├── Community/
│   │   │   ├── StoreMomentRequest.php
│   │   │   ├── StoreCorrectionRequest.php
│   │   │   └── StoreFriendRequest.php
│   │   ├── Chat/
│   │   │   ├── StoreMessageRequest.php
│   │   │   ├── StoreGroupChatRequest.php
│   │   │   └── StoreMessageCorrectionRequest.php
│   │   ├── Media/
│   │   │   ├── StorePodcastRequest.php
│   │   │   └── UpdatePodcastRequest.php
│   │   ├── Problems/
│   │   │   ├── StoreProblemRequest.php
│   │   │   ├── UpdateProblemRequest.php
│   │   │   └── StoreProblemCommentRequest.php
│   │   ├── Profile/
│   │   │   ├── UpdateProfileRequest.php
│   │   │   ├── UpdateAvatarRequest.php
│   │   │   ├── ChangePasswordRequest.php
│   │   │   └── StoreSubscriptionRequest.php
│   │   └── Shared/
│   │       └── TranslateRequest.php
│   │
│   ├── Resources/                  # API Resource transformers
│   │   ├── UserResource.php
│   │   ├── CourseResource.php
│   │   ├── LessonResource.php
│   │   ├── InstructorResource.php
│   │   ├── BookingResource.php
│   │   ├── MomentResource.php
│   │   ├── ChatResource.php
│   │   ├── MessageResource.php
│   │   ├── PodcastResource.php
│   │   ├── CertificateResource.php
│   │   ├── ReviewResource.php
│   │   ├── NotificationResource.php
│   │   ├── ProblemResource.php
│   │   ├── ProfileResource.php
│   │   ├── QuizResultResource.php
│   │   ├── EnrollmentResource.php
│   │   └── ProgressResource.php
│   │
│   └── Middleware/
│       └── CheckRole.php           # Custom role:instructor middleware
│
├── Policies/                       # Authorization (11 Policies)
│   ├── CoursePolicy.php
│   ├── LessonPolicy.php
│   ├── BookingPolicy.php
│   ├── EnrollmentPolicy.php
│   ├── MomentPolicy.php
│   ├── ChatPolicy.php
│   ├── PodcastPolicy.php
│   ├── CertificatePolicy.php
│   ├── ProblemPolicy.php
│   ├── NotificationPolicy.php
│   └── ReviewPolicy.php
│
└── Models/                         # 26 Eloquent Models (existing)
    ├── User.php
    ├── UserLanguage.php
    ├── UserInterest.php
    ├── Instructor.php
    ├── InstructorSlot.php
    ├── Course.php
    ├── Lesson.php
    ├── LessonMaterial.php
    ├── QuizQuestion.php
    ├── Enrollment.php
    ├── LessonCompletion.php
    ├── QuizResult.php
    ├── Booking.php
    ├── Review.php
    ├── Chat.php
    ├── ChatMember.php
    ├── Message.php
    ├── Moment.php
    ├── MomentCorrection.php
    ├── MomentLike.php
    ├── MomentComment.php
    ├── Podcast.php
    ├── Certificate.php
    ├── Subscription.php
    ├── StudyDay.php
    └── Notification.php
```

---

## 6. Interaction Flow — How the Layers Communicate

### 6.1 Decision Tree — Which Layer Handles What?

```
Incoming HTTP Request
        │
        ▼
   Controller (thin orchestrator)
        │
        ├── Is it a simple CRUD operation?
        │       │
        │       └── YES → Service Class
        │                   │
        │                   └── Eloquent Model → Database
        │
        ├── Is it a complex read with aggregations/filters?
        │       │
        │       └── YES → Query Class
        │                   │
        │                   └── Eloquent Builder → Database
        │
        └── Is it a multi-step workflow with side effects?
                │
                └── YES → Action Class
                            │
                            ├── Service(s) (if needed)
                            ├── Model(s) directly
                            ├── Notification dispatch
                            └── Database Transaction
```

### 6.2 Concrete Controller Example

```php
class LessonController extends Controller
{
    // CRUD → Service
    public function index(Request $request, LessonCatalogQuery $query)
    {
        return LessonResource::collection($query->execute($request->all()));
    }

    // CRUD → Service
    public function store(StoreLessonRequest $request, LessonService $service)
    {
        $lesson = $service->create($request->validated());
        return new LessonResource($lesson);
    }

    // Complex Workflow → Action
    public function complete(
        CompleteLessonRequest $request,
        Lesson $lesson,
        CompleteLessonAction $action
    ) {
        $enrollment = Enrollment::where('user_id', auth()->id())
            ->where('course_id', $lesson->course_id)
            ->firstOrFail();

        $result = $action->execute($enrollment, $lesson, $request->score);

        return response()->json([
            'message'     => 'Lesson completed!',
            'progress'    => $result['progress'],
            'certificate' => $result['certificate']
                ? new CertificateResource($result['certificate'])
                : null,
        ]);
    }
}
```

### 6.3 Architectural Sequence Diagram

```
┌──────────┐    ┌────────────┐    ┌─────────────┐    ┌──────────┐    ┌────────┐
│  React   │───▶│ Controller │───▶│FormRequest  │───▶│ Service/ │───▶│ Model  │
│ Frontend │    │  (thin)    │    │(validation) │    │ Action/  │    │(Eloqu.)│
│          │◀───│            │◀───│             │    │ Query    │◀───│        │
└──────────┘    └────────────┘    └─────────────┘    └──────────┘    └────────┘
                       │                                    │
                       │                              ┌─────┴──────┐
                       │                              │ Side Effects│
                       │                              │(Notifs, PDF,│
                       │                              │ Events)     │
                       ▼                              └────────────┘
                 ┌────────────┐
                 │ API Resource│
                 │ (JSON shape)│
                 └────────────┘
```

---

## 7. Implementation Map — Services

Services handle the **standard CRUD** for each model domain. Each Service is grouped below with its methods and the models it manages.

### 7.1 Core Services

| Service | Model(s) | Methods |
|---------|----------|---------|
| `AuthService` | `User` | `register()`, `login()`, `logout()`, `refreshToken()` |
| `ProfileService` | `User`, `UserLanguage`, `UserInterest` | `show()`, `update()`, `updateAvatar()`, `changePassword()`, `changeEmail()` |
| `CourseService` | `Course` | `create()`, `update()`, `delete()`, `publish()`, `list()` |
| `LessonService` | `Lesson` | `create()`, `update()`, `delete()`, `reorder()` |
| `LessonMaterialService` | `LessonMaterial` | `upload()`, `delete()` |
| `InstructorSlotService` | `InstructorSlot` | `create()`, `update()`, `delete()`, `listAvailable()` |
| `BookingService` | `Booking` | `list()`, `cancel()`, `confirm()` |
| `ReviewService` | `Review` | `create()`, `listForInstructor()` |
| `MomentService` | `Moment`, `MomentLike`, `MomentComment` | `create()`, `delete()`, `toggleLike()`, `addComment()` |
| `ChatService` | `Chat`, `ChatMember` | `leave()`, `disband()`, `getMembers()` |
| `MessageService` | `Message` | `list()`, `markRead()` |
| `PodcastService` | `Podcast` | `list()`, `create()`, `update()`, `delete()` |
| `ProblemService` | `Problem`, `ProblemVote`, `ProblemComment` | `create()`, `update()`, `delete()`, `upvote()`, `addComment()` |
| `CertificateService` | `Certificate` | `list()`, `verify()` |
| `NotificationService` | `Notification` | `create()`, `markRead()`, `markAllRead()`, `delete()`, `list()` |
| `SubscriptionService` | `Subscription` | `show()`, `cancel()` |
| `QuizService` | `QuizQuestion` | `create()`, `update()`, `delete()`, `listForCourse()` |
| `FileUploadService` | — (utility) | `store()`, `delete()` |
| `TranslationService` | — (external API) | `translate()` |
| `PublicService` | `User`, `Course`, `Instructor` | `getStats()`, `getPublicCourses()` |

**Total: 20 Service Classes**

---

## 8. Implementation Map — Actions

Actions are reserved for operations where **at least one** of the following is true:
- Touches **3+ models** in a single transaction
- Triggers **side effects** (notifications, file generation, external API calls)
- Implements a **non-trivial algorithm** (matching, scoring, streak calculation)
- Requires **conditional branching** that affects multiple database operations

### 8.1 Auth Actions (`app/Actions/Auth/`)

| Action | Complexity Justification | Models Touched |
|--------|--------------------------|----------------|
| `CompleteOnboardingAction` | Multi-model: updates `User.cefr_level`, bulk-creates `UserLanguage[]`, bulk-creates `UserInterest[]`, evaluates placement quiz score | `User`, `UserLanguage`, `UserInterest`, `QuizResult` |
| `HandleGoogleOAuthAction` | External OAuth flow: finds or creates user via `google_id`, generates Sanctum token, handles first-time vs returning user branching | `User` + Socialite |

### 8.2 Learning Actions (`app/Actions/Learning/`)

| Action | Complexity Justification | Models Touched |
|--------|--------------------------|----------------|
| `EnrollStudentAction` | Creates enrollment + increments `Course.enrolled_count` + creates welcome notification | `Enrollment`, `Course`, `Notification` |
| `CompleteLessonAction` | **Most complex action.** Creates `LessonCompletion`, updates `Enrollment` progress, conditionally generates `Certificate`, sends achievement notification | `LessonCompletion`, `Enrollment`, `Certificate`, `Notification` |
| `EvaluateQuizAction` | Compares submitted answers against `QuizQuestion.correct_answer`, calculates score, determines pass/fail, creates `QuizResult`, creates `StudyDay` | `QuizQuestion`, `QuizResult`, `StudyDay` |
| `BookInstructorSessionAction` | Validates slot, marks `InstructorSlot.is_booked`, calculates price, creates `Booking`, sends notification to instructor | `InstructorSlot`, `Booking`, `Notification` |

### 8.3 Community Actions (`app/Actions/Community/`)

| Action | Complexity Justification | Models Touched |
|--------|--------------------------|----------------|
| `SubmitMomentCorrectionAction` | Creates `MomentCorrection`, sends notification to original author with the corrected text | `MomentCorrection`, `Notification` |
| `DiscoverLanguagePartnersAction` | Algorithmic: cross-references `UserLanguage` records to compute match percentages, filters by gender/level/online, excludes self and existing connections | `User`, `UserLanguage`, `UserInterest` |

### 8.4 Chat Actions (`app/Actions/Chat/`)

| Action | Complexity Justification | Models Touched |
|--------|--------------------------|----------------|
| `SendMessageAction` | Creates `Message`, updates `Chat.updated_at` for sort order, increments `ChatMember.unread_count` for all other members, broadcasts via WebSocket | `Message`, `Chat`, `ChatMember` + Broadcasting |
| `CreateGroupChatAction` | Creates `Chat` (type=group), creates `ChatMember` entries for creator (role=admin) and all invited members, sends notification to invited users | `Chat`, `ChatMember`, `Notification` |

### 8.5 Certificate Actions (`app/Actions/Certificates/`)

| Action | Complexity Justification | Models Touched |
|--------|--------------------------|----------------|
| `GenerateCertificatePdfAction` | Loads certificate with relations, renders a Blade/DomPDF template with user name, course name, score, date, and verification code, stores PDF to disk | `Certificate`, `Course`, `User` + DomPDF |

### 8.6 Profile Actions (`app/Actions/Profile/`)

| Action | Complexity Justification | Models Touched |
|--------|--------------------------|----------------|
| `ProcessSubscriptionAction` | Stripe integration: creates Stripe Customer + Subscription, stores IDs, updates `User.is_vip`, creates `Subscription` record | `User`, `Subscription` + Stripe API |
| `DeleteAccountAction` | Cascading cleanup: revokes tokens, deletes avatar from storage, soft-deletes or hard-deletes user and all related data | `User` + Storage + Sanctum |

**Total: 14 Action Classes**

---

## 9. Implementation Map — Queries

Queries encapsulate **read-only operations** that involve aggregations, computed fields, or multi-table joins too complex for a controller or simple Service method.

### 9.1 Dashboard Queries (`app/Queries/Dashboard/`)

| Query | Purpose | Source Data |
|-------|---------|-------------|
| `StudentProgressQuery` | Builds the full progress dashboard: streak calculation, heatmap dates, completion history, quiz results, overall completion percentage | `StudyDay`, `LessonCompletion`, `QuizResult`, `Enrollment` |
| `EnrolledCoursesQuery` | Fetches user enrollments with course details, instructor name, calculated progress bar data, current lesson pointer | `Enrollment` → `Course` → `Instructor` → `Lesson` |
| `UpcomingBookingsQuery` | Retrieves future confirmed bookings with instructor profile and slot details | `Booking` → `Instructor` → `User`, `InstructorSlot` |

### 9.2 Learning Queries (`app/Queries/Learning/`)

| Query | Purpose | Source Data |
|-------|---------|-------------|
| `InstructorCatalogQuery` | Multi-filter search: name, category (Medical/Legal/Business), type (Free/Paid), gender, level — with eager-loaded courses and reviews | `Instructor` → `User`, `Course`, `Review` |
| `LessonCatalogQuery` | Filtered lesson listing: by level, status (live/recorded), instructor — with materials count and quiz availability | `Lesson` → `Course` → `Instructor` |
| `CourseDetailsQuery` | Full course page: ordered lessons with unlock status calculated from user's enrollment progress, materials, quiz questions count | `Course` → `Lesson` → `LessonMaterial`, `Enrollment`, `LessonCompletion` |
| `InstructorSlotsQuery` | Monthly availability calendar: groups available (non-booked) slots by date for a given instructor and month | `InstructorSlot` (filtered by date range + `is_booked = false`) |

### 9.3 Community Queries (`app/Queries/Community/`)

| Query | Purpose | Source Data |
|-------|---------|-------------|
| `MomentsFeedQuery` | Paginated social feed: category filter, includes user profile, corrections, like status for current user, comments count | `Moment` → `User`, `MomentCorrection`, `MomentLike` |
| `LanguagePartnerMatchQuery` | Discovery algorithm: computes match percentages based on language exchange compatibility, applies gender/level/online filters | `User` → `UserLanguage`, `UserInterest` |

### 9.4 Instructor Dashboard Queries (`app/Queries/Instructor/`)

| Query | Purpose | Source Data |
|-------|---------|-------------|
| `InstructorDashboardStatsQuery` | Aggregated analytics: total revenue, active students, completion rate, average rating, top courses, student-by-level breakdown, upcoming sessions | `Booking`, `Enrollment`, `Course`, `Review`, `InstructorSlot` |
| `StudentFeedbackQuery` | Per-student completion rates and quiz performance for the instructor's courses | `QuizResult`, `LessonCompletion` → `Enrollment` → `User` |
| `AssessmentResultsQuery` | Quiz results grouped by course and lesson for the instructor's content | `QuizResult` → `Lesson` → `Course` |

### 9.5 Chat Queries (`app/Queries/Chat/`)

| Query | Purpose | Source Data |
|-------|---------|-------------|
| `ChatListQuery` | User's conversations: latest message preview, unread count, online status of other participant, type filter (direct/instructor/group) | `Chat` → `ChatMember` → `User`, `Message` (latest) |

**Total: 14 Query Classes**

---

## 10. Complete Feature-to-Layer Mapping

This table maps every feature from `BACKEND-FEATURES-ANALYSIS.md` to its assigned architectural layer.

### Auth Entity (Features 1.1–1.5)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 1.1 User Registration | `POST /api/auth/register` | Service | `AuthService@register` |
| 1.2 User Login | `POST /api/auth/login` | Service | `AuthService@login` |
| 1.3 Google OAuth | `GET /api/auth/google/*` | **Action** | `HandleGoogleOAuthAction` |
| 1.4 Onboarding Wizard | `POST /api/auth/onboarding` | **Action** | `CompleteOnboardingAction` |
| 1.5 Logout | `POST /api/auth/logout` | Service | `AuthService@logout` |

### Dashboard Entity (Features 2.1–2.5)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 2.1 Learning Progress | `GET /api/dashboard/progress` | **Query** | `StudentProgressQuery` |
| 2.2 Enrolled Courses | `GET /api/dashboard/courses` | **Query** | `EnrolledCoursesQuery` |
| 2.3 Daily Check-In | `POST /api/dashboard/check-in` | Service | `NotificationService` + `StudyDay::create` |
| 2.4 Upcoming Bookings | `GET /api/dashboard/bookings` | **Query** | `UpcomingBookingsQuery` |
| 2.5 Notifications CRUD | `GET/PATCH/DELETE /api/notifications` | Service | `NotificationService` |

### Learning Entity (Features 3.1–3.10)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 3.1 List Instructors | `GET /api/instructors` | **Query** | `InstructorCatalogQuery` |
| 3.2 Instructor Profile | `GET /api/instructors/{id}` | Service | `InstructorService@show` (via Eloquent eager load) |
| 3.3 Book Session | `POST /api/bookings` | **Action** | `BookInstructorSessionAction` |
| 3.4 List Lessons | `GET /api/lessons` | **Query** | `LessonCatalogQuery` |
| 3.5 Course Details | `GET /api/courses/{id}` | **Query** | `CourseDetailsQuery` |
| 3.6 Enroll in Course | `POST /api/enrollments` | **Action** | `EnrollStudentAction` |
| 3.7 Complete Lesson | `POST /api/lessons/{id}/complete` | **Action** | `CompleteLessonAction` |
| 3.8 Submit Quiz | `POST /api/quizzes/submit` | **Action** | `EvaluateQuizAction` |
| 3.9 Submit Review | `POST /api/reviews` | Service | `ReviewService@create` |
| 3.10 Instructor Slots | `GET /api/instructors/{id}/slots` | **Query** | `InstructorSlotsQuery` |

### Community Entity (Features 4.1–4.7)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 4.1 Moments Feed | `GET /api/moments` | **Query** | `MomentsFeedQuery` |
| 4.2 Create Moment | `POST /api/moments` | Service | `MomentService@create` |
| 4.3 Like/Unlike | `POST /api/moments/{id}/like` | Service | `MomentService@toggleLike` |
| 4.4 Submit Correction | `POST /api/moments/{id}/corrections` | **Action** | `SubmitMomentCorrectionAction` |
| 4.5 Translate Moment | `POST /api/translate` | Service | `TranslationService@translate` |
| 4.6 Discover Partners | `GET /api/users/discover` | **Action** | `DiscoverLanguagePartnersAction` |
| 4.7 Friend Request | `POST /api/friend-requests` | Service | (future `FriendService@sendRequest`) |

### Chat Entity (Features 5.1–5.7)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 5.1 Chat List | `GET /api/chats` | **Query** | `ChatListQuery` |
| 5.2 Chat Messages | `GET /api/chats/{id}/messages` | Service | `MessageService@list` |
| 5.3 Send Message | `POST /api/chats/{id}/messages` | **Action** | `SendMessageAction` |
| 5.4 Translate Message | `POST /api/translate` | Service | `TranslationService@translate` |
| 5.5 Chat Correction | `POST /api/chats/{chatId}/messages/{msgId}/corrections` | Service | `MessageService@createCorrection` |
| 5.6 Create Group | `POST /api/chats/group` | **Action** | `CreateGroupChatAction` |
| 5.7 Leave / Block / Disband | `POST/DELETE` | Service | `ChatService@leave/disband` |

### Instructor Entity (Features 6.1–6.10)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 6.1 Dashboard Stats | `GET /api/instructor/dashboard` | **Query** | `InstructorDashboardStatsQuery` |
| 6.2 CRUD Courses | `GET/POST/PUT/DELETE /api/instructor/courses` | Service | `CourseService` |
| 6.3 CRUD Lessons | `POST/PUT/DELETE /api/instructor/lessons` | Service | `LessonService` |
| 6.4 CRUD Materials | `POST /api/instructor/lessons/{id}/materials` | Service | `LessonMaterialService` |
| 6.5 CRUD Quizzes | `GET/POST/PUT/DELETE /api/instructor/quizzes` | Service | `QuizService` |
| 6.6 Student Feedback | `GET /api/instructor/feedback` | **Query** | `StudentFeedbackQuery` |
| 6.7 View Ratings | `GET /api/instructor/reviews` | Service | `ReviewService@listForInstructor` |
| 6.8 Manage Slots | `GET/POST/PUT/DELETE /api/instructor/slots` | Service | `InstructorSlotService` |
| 6.9 Assessments | `GET /api/instructor/assessments` | **Query** | `AssessmentResultsQuery` |
| 6.10 Learning Groups | `POST /api/instructor/groups` | Service | `ChatService@createGroup` (reuses group chat logic) |

### Media Entity (Features 7.1–7.4)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 7.1 List Podcasts | `GET /api/podcasts` | Service | `PodcastService@list` |
| 7.2 Upload Podcast | `POST /api/podcasts` | Service | `PodcastService@create` |
| 7.3 Update Podcast | `PUT /api/podcasts/{id}` | Service | `PodcastService@update` |
| 7.4 Delete Podcast | `DELETE /api/podcasts/{id}` | Service | `PodcastService@delete` |

### Certificates Entity (Features 8.1–8.3)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 8.1 List Certificates | `GET /api/certificates` | Service | `CertificateService@list` |
| 8.2 Download PDF | `GET /api/certificates/{id}/download` | **Action** | `GenerateCertificatePdfAction` |
| 8.3 Verify Certificate | `GET /api/certificates/verify/{code}` | Service | `CertificateService@verify` |

### Problems Entity (Features 9.1–9.6)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 9.1 List Problems | `GET /api/problems` | Service | `ProblemService@list` |
| 9.2 Create Problem | `POST /api/problems` | Service | `ProblemService@create` |
| 9.3 Edit Problem | `PUT /api/problems/{id}` | Service | `ProblemService@update` |
| 9.4 Delete Problem | `DELETE /api/problems/{id}` | Service | `ProblemService@delete` |
| 9.5 Upvote | `POST /api/problems/{id}/upvote` | Service | `ProblemService@upvote` |
| 9.6 Comment | `POST /api/problems/{id}/comments` | Service | `ProblemService@addComment` |

### Profile Entity (Features 10.1–10.7)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 10.1 Fetch Profile | `GET /api/profile` | Service | `ProfileService@show` |
| 10.2 Update Profile | `PATCH /api/profile` | Service | `ProfileService@update` |
| 10.3 Change Avatar | `POST /api/profile/avatar` | Service | `ProfileService@updateAvatar` |
| 10.4 Post Moment | `POST /api/moments` | Service | `MomentService@create` (shared) |
| 10.5 Delete Moment | `DELETE /api/moments/{id}` | Service | `MomentService@delete` |
| 10.6 Settings (Password, Email, Delete) | `PUT/DELETE /api/profile/*` | Service / **Action** | `ProfileService` / `DeleteAccountAction` |
| 10.7 Subscription | `POST/DELETE /api/subscription` | **Action** | `ProcessSubscriptionAction` |

### Guest Entity (Features 11.1–11.2)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 11.1 Landing Stats | `GET /api/public/stats` | Service | `PublicService@getStats` |
| 11.2 Public Catalog | `GET /api/public/courses` | Service | `PublicService@getCourses` |

### Shared Features (12.1–12.3)

| Feature | Endpoint | Layer | Class |
|---------|----------|-------|-------|
| 12.1 Translation API | `POST /api/translate` | Service | `TranslationService@translate` |
| 12.2 File Upload | Internal | Service | `FileUploadService@store` |
| 12.3 Notification System | Internal | Service | `NotificationService@create` |

---

## 11. File Count Analysis

| Category | Count | Pattern Used |
|----------|-------|-------------|
| **Controllers** | 16 | Thin orchestration |
| **FormRequests** | 28 | Validation |
| **Services** | 20 | Standard CRUD |
| **Actions** | 14 | Complex workflows |
| **Queries** | 14 | Complex reads |
| **Resources** | 17 | JSON transformation |
| **Policies** | 11 | Authorization |
| **Middleware** | 1 | Role checking |
| **Models** | 26 | Data layer (existing) |
| **Factories** | 26 | Testing (existing) |
| **Seeders** | 6 | Database population (existing) |
| **Migrations** | 22 | Schema (existing) |

**Total new files to create: ~121**  
**Total logic files (Services + Actions + Queries): 48**

Compared to the **pure Action-based approach** (~80 Actions alone), this is a **40% reduction in logic-layer files** with zero loss of architectural clarity.

---

## 12. Final Verdict

### Why the Hybrid Architecture Is the Most Professional Choice for LinguaFlow

The Hybrid Architecture represents the **highest level of engineering judgment** a graduation project can demonstrate.

It shows that the developer:

1. **Understands multiple architectural patterns** — and can articulate when each one is appropriate, rather than dogmatically applying a single pattern everywhere.

2. **Prioritizes pragmatic engineering over theoretical purity** — a `PodcastService` with 4 clean CRUD methods is the correct abstraction for podcast management. A `CompleteLessonAction` with transactional multi-model operations is the correct abstraction for lesson completion. The ability to make this distinction is what separates junior developers from senior architects.

3. **Designs for long-term maintainability** — the `app/Queries/` directory keeps controllers thin without bloating services. The `app/Actions/` directory prevents services from accumulating multi-hundred-line methods. Each layer has one job and does it well.

4. **Builds production-grade architecture** — this is the exact pattern used by leading Laravel companies including Spatie (creators of `laravel-data`, `laravel-medialibrary`), and is consistent with the patterns demonstrated in Laravel Fortify, Jetstream, and Cashier.

5. **Optimizes both development velocity and code quality** — CRUD features ship fast via Services. Complex features are isolated and thoroughly testable via Actions. Dashboard analytics are cleanly separated via Queries. No layer is overloaded.

---

> **Architecture**: Hybrid (Service + Action + Query)  
> **Repository Layer**: Omitted — Eloquent ORM is sufficient as the Active Record implementation  
> **Testing Strategy**: Unit tests for Actions and Queries; Feature tests for Controller → Service flows  
> **Status**: Approved for implementation  
> **Supersedes**: `ARCHITECTURE-DECISION.md` (v1.0 — Pure Action-Based)

---

*This document was generated by analyzing 11 React entities, 26 Eloquent models, 50+ feature specifications from `BACKEND-FEATURES-ANALYSIS.md`, the complete frontend architecture from `FRONTEND-ARCHITECTURE-GUIDE.md`, and the initial architecture decision from `ARCHITECTURE-DECISION.md`. It is intended to serve as the authoritative backend architecture reference for the LinguaFlow graduation project.*
