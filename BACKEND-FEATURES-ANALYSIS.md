# Backend Features Analysis — Full Frontend-to-Backend Feature Map

> **Purpose**: Map every React Frontend Feature to its corresponding Laravel Backend Execution Journey using Best Practices.
>
> **Pattern**: `Route (Middleware) → Controller → FormRequest → Service → Repository → Response`
>
> **Frontend Project**: `The React For Translation Company V2`  
> **Backend Project**: `Translation_Graduation_Projoect`

---

## Table of Contents

1. [Auth Entity](#1-auth-entity)
2. [Dashboard Entity](#2-dashboard-entity)
3. [Learning Entity](#3-learning-entity)
4. [Community Entity](#4-community-entity)
5. [Chat Entity](#5-chat-entity)
6. [Instructor Entity](#6-instructor-entity)
7. [Media Entity (Podcasts)](#7-media-entity-podcasts)
8. [Certificates Entity](#8-certificates-entity)
9. [Problems Entity](#9-problems-entity)
10. [Profile Entity](#10-profile-entity)
11. [Guest Entity](#11-guest-entity)
12. [Global / Shared Features](#12-global--shared-features)

---

## 1. Auth Entity

> **React Source**: `src/entities/Auth/index.jsx` → `AuthSelection`, `LoginView`, `SignupView`, `OnboardingWizard`  
> **Related Models**: `User`, `UserLanguage`, `UserInterest`

---

### Feature 1.1: User Registration (Signup)

| Layer | Detail |
|---|---|
| **Entity** | AuthEntity → `SignupView` |
| **Endpoint** | `POST /api/auth/register` |
| **Middleware** | `guest` (no auth required) |
| **Policy** | None |
| **FormRequest** | `RegisterRequest` |
| **Validation** | `name: required|string|max:255`, `email: required|email|unique:users`, `password: required|min:8|confirmed`, `gender: nullable|in:male,female` |
| **Service** | `AuthService@register` — Hash password, create user, generate Sanctum token |
| **Repository** | `UserRepository@create` |
| **Response** | `201 Created` → `{ user: UserResource, token: string }` |

---

### Feature 1.2: User Login

| Layer | Detail |
|---|---|
| **Entity** | AuthEntity → `LoginView` |
| **Endpoint** | `POST /api/auth/login` |
| **Middleware** | `guest` |
| **Policy** | None |
| **FormRequest** | `LoginRequest` |
| **Validation** | `email: required|email`, `password: required|string` |
| **Service** | `AuthService@login` — Validate credentials, update `is_online`, `last_seen_at`, generate Sanctum token |
| **Repository** | `UserRepository@find` (by email) |
| **Response** | `200 OK` → `{ user: UserResource, token: string }` |
| **Error** | `401 Unauthorized` → `{ message: 'Invalid credentials' }` |

---

### Feature 1.3: Google OAuth Login

| Layer | Detail |
|---|---|
| **Entity** | AuthEntity → `LoginView` (Google Button) |
| **Endpoint** | `GET /api/auth/google/redirect`, `GET /api/auth/google/callback` |
| **Middleware** | `guest` |
| **Service** | `AuthService@handleGoogleCallback` — Find or create user via `google_id`, generate token |
| **Repository** | `UserRepository@create` / `UserRepository@find` |
| **Response** | Redirect to frontend with token in query param |

---

### Feature 1.4: Onboarding Wizard (Language & Interest Selection)

| Layer | Detail |
|---|---|
| **Entity** | AuthEntity → `OnboardingWizard` (Steps 1-5) |
| **Endpoint** | `POST /api/auth/onboarding` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `OnboardingRequest` |
| **Validation** | `native_language: required|string`, `learning_languages: required|array|min:1`, `learning_languages.*.name: required|string`, `learning_languages.*.level: required|in:A1,A2,B1,B2,C1,C2`, `interests: required|array|min:1`, `interests.*: required|string` |
| **Service** | `OnboardingService@complete` — Update user `native_language`, `cefr_level`, store `UserLanguage` entries, store `UserInterest` entries |
| **Repository** | `UserRepository@update`, `UserLanguageRepository@create`, `UserInterestRepository@create` |
| **Response** | `200 OK` → `{ message: 'Onboarding complete', user: UserResource }` |

---

### Feature 1.5: User Logout

| Layer | Detail |
|---|---|
| **Entity** | AuthEntity / Sidebar Logout Button |
| **Endpoint** | `POST /api/auth/logout` |
| **Middleware** | `auth:sanctum` |
| **Service** | `AuthService@logout` — Revoke current Sanctum token, set `is_online = false` |
| **Response** | `200 OK` → `{ message: 'Logged out' }` |

---

## 2. Dashboard Entity

> **React Source**: `src/entities/Dashboard/index.jsx` → `MyProgress`  
> **Related Models**: `User`, `Enrollment`, `Course`, `StudyDay`, `QuizResult`, `LessonCompletion`, `Notification`, `Booking`

---

### Feature 2.1: Fetch Learning Analytics / Progress

| Layer | Detail |
|---|---|
| **Entity** | DashboardEntity → `MyProgress` |
| **Endpoint** | `GET /api/dashboard/progress` |
| **Middleware** | `auth:sanctum` |
| **Policy** | None (user accesses own data) |
| **Service** | `DashboardService@getProgress` — Calculate `currentStreak`, `longestStreak`, `totalLessonsCompleted`, `currentLevel`, `overallCompletion`, build `studyDates` heatmap from `StudyDay`, gather `quizResults` from `QuizResult`, gather `completionHistory` from `LessonCompletion` |
| **Repository** | `StudyDayRepository@all`, `QuizResultRepository@all`, `LessonCompletionRepository@all`, `EnrollmentRepository@all` |
| **Response** | `200 OK` → `{ progress: ProgressResource }` |

---

### Feature 2.2: Fetch Enrolled Courses with Progress

| Layer | Detail |
|---|---|
| **Entity** | DashboardEntity → `MyProgress` (Courses Tab) |
| **Endpoint** | `GET /api/dashboard/courses` |
| **Middleware** | `auth:sanctum` |
| **Service** | `DashboardService@getEnrolledCourses` — Fetch user enrollments with course details, calculate `completedLessons`, `totalLessons`, `progress` percentage, `currentLesson` |
| **Repository** | `EnrollmentRepository@all` (with Course eager load) |
| **Response** | `200 OK` → `{ courses: EnrolledCourseResource[] }` |

---

### Feature 2.3: Daily Check-In (Study Streak)

| Layer | Detail |
|---|---|
| **Entity** | DashboardEntity → `MyProgress` (Check-In Button) |
| **Endpoint** | `POST /api/dashboard/check-in` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | No extra fields (auto-detected from auth) |
| **Service** | `DashboardService@checkIn` — Create `StudyDay` for today if not exists, recalculate streak, create Notification |
| **Repository** | `StudyDayRepository@create`, `NotificationRepository@create` |
| **Response** | `200 OK` → `{ message: 'Checked in!', streak: int }` |

---

### Feature 2.4: Fetch Upcoming Meetings / Bookings

| Layer | Detail |
|---|---|
| **Entity** | DashboardEntity → `MyProgress` (Instructor Sessions) |
| **Endpoint** | `GET /api/dashboard/bookings` |
| **Middleware** | `auth:sanctum` |
| **Service** | `BookingService@getUpcoming` — Fetch bookings where `status = confirmed` and `date >= today`, eager load `instructor.user` |
| **Repository** | `BookingRepository@all` (with filters) |
| **Response** | `200 OK` → `{ bookings: BookingResource[] }` |

---

### Feature 2.5: Notifications CRUD

| Layer | Detail |
|---|---|
| **Entity** | DashboardEntity → `MyProgress` (Notifications Panel) |
| **Endpoint (List)** | `GET /api/notifications` |
| **Endpoint (Read)** | `PATCH /api/notifications/{id}/read` |
| **Endpoint (Read All)** | `PATCH /api/notifications/read-all` |
| **Endpoint (Delete)** | `DELETE /api/notifications/{id}` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `NotificationPolicy@view` → user owns notification |
| **Service** | `NotificationService@list`, `NotificationService@markRead`, `NotificationService@markAllRead`, `NotificationService@delete` |
| **Repository** | `NotificationRepository@all / update / delete` |
| **Response** | `200 OK` → `{ notifications: NotificationResource[] }` / `{ message: 'Marked' }` |

---

## 3. Learning Entity

> **React Source**: `src/entities/Learning/index.jsx` → `LearnHub`, `InstructorProfile`, `BookingModal`, `CourseView`, `LessonInterface`, `ReviewSection`  
> **Related Models**: `Instructor`, `Course`, `Lesson`, `LessonMaterial`, `LessonCompletion`, `QuizQuestion`, `QuizResult`, `Review`, `Enrollment`, `Booking`, `InstructorSlot`

---

### Feature 3.1: List Instructors (with Filters)

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `LearnHub` (Instructors Tab) |
| **Endpoint** | `GET /api/instructors` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?search=&level=&category=&type=&gender=` |
| **Service** | `InstructorService@list` — Search by name/bio, filter by level, category, price type, gender; eager load `user`, `courses` |
| **Repository** | `InstructorRepository@all` (with dynamic filters) |
| **Response** | `200 OK` → `{ instructors: InstructorResource[] }` |

---

### Feature 3.2: View Instructor Profile

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `InstructorProfile` |
| **Endpoint** | `GET /api/instructors/{id}` |
| **Middleware** | `auth:sanctum` |
| **Service** | `InstructorService@show` — Load instructor with `user`, `slots`, `courses`, `reviews` |
| **Repository** | `InstructorRepository@find` |
| **Response** | `200 OK` → `{ instructor: InstructorDetailResource }` |

---

### Feature 3.3: Book Instructor Session

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `BookingModal` |
| **Endpoint** | `POST /api/bookings` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `BookingPolicy@create` → user cannot book themselves |
| **FormRequest** | `StoreBookingRequest` |
| **Validation** | `instructor_id: required|exists:instructors,id`, `instructor_slot_id: required|exists:instructor_slots,id`, `booking_type: required|in:private,group`, `course_style: required|in:conversation,exam_prep,translation_practice,custom`, `date: required|date|after_or_equal:today`, `time: required|string`, `notes: nullable|string` |
| **Service** | `BookingService@create` — Verify slot availability, calculate price, mark slot as booked, create booking, create notification for instructor |
| **Repository** | `BookingRepository@create`, `InstructorSlotRepository@update`, `NotificationRepository@create` |
| **Response** | `201 Created` → `{ booking: BookingResource, message: 'Session booked!' }` |

---

### Feature 3.4: List Lessons (with Filters)

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `LearnHub` (Lessons Tab) |
| **Endpoint** | `GET /api/lessons` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?search=&level=&status=&instructor=` |
| **Service** | `LessonService@list` — Filter lessons by level, status (live/recorded), instructor; eager load `course`, `materials` |
| **Repository** | `LessonRepository@all` (with dynamic filters) |
| **Response** | `200 OK` → `{ lessons: LessonResource[] }` |

---

### Feature 3.5: View Course Details

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `CourseView` |
| **Endpoint** | `GET /api/courses/{id}` |
| **Middleware** | `auth:sanctum` |
| **Service** | `CourseService@show` — Load course with ordered `lessons`, `materials`, `instructor`, user's `enrollment` progress, `quizQuestions` |
| **Repository** | `CourseRepository@find` |
| **Response** | `200 OK` → `{ course: CourseDetailResource }` |

---

### Feature 3.6: Enroll in Course

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `CourseView` (Enroll Button) |
| **Endpoint** | `POST /api/enrollments` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `EnrollmentPolicy@create` → user not already enrolled |
| **FormRequest** | `StoreEnrollmentRequest` |
| **Validation** | `course_id: required|exists:courses,id` |
| **Service** | `EnrollmentService@enroll` — Create enrollment, increment `enrolled_count` on course, create notification |
| **Repository** | `EnrollmentRepository@create`, `CourseRepository@update` |
| **Response** | `201 Created` → `{ enrollment: EnrollmentResource, message: 'Successfully enrolled!' }` |

---

### Feature 3.7: Open Lesson Interface / Mark Lesson Complete

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `LessonInterface` |
| **Endpoint (Fetch)** | `GET /api/lessons/{id}` |
| **Endpoint (Complete)** | `POST /api/lessons/{id}/complete` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `LessonPolicy@view` → user is enrolled in the course |
| **FormRequest (Complete)** | `CompleteLessonRequest` → `score: nullable|integer|min:0|max:100` |
| **Service** | `LessonService@show` (load materials, quiz), `LessonService@markComplete` — Create `LessonCompletion`, update enrollment progress, check if course complete to trigger certificate |
| **Repository** | `LessonRepository@find`, `LessonCompletionRepository@create`, `EnrollmentRepository@update` |
| **Response (Fetch)** | `200 OK` → `{ lesson: LessonDetailResource }` |
| **Response (Complete)** | `200 OK` → `{ message: 'Lesson completed!', progress: int }` |

---

### Feature 3.8: Submit Quiz

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `LessonInterface` (Quiz Section) |
| **Endpoint** | `POST /api/quizzes/submit` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `SubmitQuizRequest` |
| **Validation** | `lesson_id: required|exists:lessons,id`, `answers: required|array`, `answers.*.question_id: required|exists:quiz_questions,id`, `answers.*.selected_option: required|string` |
| **Service** | `QuizService@evaluate` — Compare answers against correct ones, calculate score, determine pass/fail, create `QuizResult` |
| **Repository** | `QuizQuestionRepository@find`, `QuizResultRepository@create` |
| **Response** | `200 OK` → `{ result: QuizResultResource, score: int, passed: bool }` |

---

### Feature 3.9: Submit Instructor Review

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `ReviewSection` |
| **Endpoint** | `POST /api/reviews` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `ReviewPolicy@create` → user has at least one booking/enrollment with instructor |
| **FormRequest** | `StoreReviewRequest` |
| **Validation** | `instructor_id: required|exists:instructors,id`, `rating: required|integer|min:1|max:5`, `comment: required|string|max:1000` |
| **Service** | `ReviewService@create` — Create review, recalculate instructor's average `rating` and `total_reviews` |
| **Repository** | `ReviewRepository@create`, `InstructorRepository@update` |
| **Response** | `201 Created` → `{ review: ReviewResource }` |

---

### Feature 3.10: Fetch Instructor Availability Slots

| Layer | Detail |
|---|---|
| **Entity** | LearningEntity → `BookingModal` (Calendar) |
| **Endpoint** | `GET /api/instructors/{id}/slots` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?month=&year=` |
| **Service** | `InstructorSlotService@list` — Fetch available slots for a given month, filter by `is_booked = false` |
| **Repository** | `InstructorSlotRepository@all` |
| **Response** | `200 OK` → `{ slots: { 'YYYY-MM-DD': ['09:00', '10:00', ...] } }` |

---

## 4. Community Entity

> **React Source**: `src/entities/Community/index.jsx` → `MomentsFeed`, `ConnectHub`  
> **Related Models**: `Moment`, `MomentLike`, `MomentComment`, `MomentCorrection`, `User`, `Chat`

---

### Feature 4.1: Fetch Moments Feed

| Layer | Detail |
|---|---|
| **Entity** | CommunityEntity → `MomentsFeed` |
| **Endpoint** | `GET /api/moments` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?filter=following|learn|help|recommend` |
| **Service** | `MomentService@feed` — Fetch moments ordered by recency, include `user`, `corrections`, `likes_count`, `comments_count`; apply category filter |
| **Repository** | `MomentRepository@all` |
| **Response** | `200 OK` → `{ moments: MomentResource[] }` |

---

### Feature 4.2: Create Moment (Post)

| Layer | Detail |
|---|---|
| **Entity** | CommunityEntity → `MomentsFeed` (Composer Modal) |
| **Endpoint** | `POST /api/moments` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreMomentRequest` |
| **Validation** | `content: required|string|max:5000`, `category: required|in:General,Learn,Help,Recommend,Vocabulary,Culture`, `images: nullable|array|max:4`, `images.*: image|mimes:jpg,png,webp|max:5120` |
| **Service** | `MomentService@create` — Store moment, handle image uploads to `public/moments/`, create notification for followers |
| **Repository** | `MomentRepository@create` |
| **Response** | `201 Created` → `{ moment: MomentResource }` |

---

### Feature 4.3: Like / Unlike Moment

| Layer | Detail |
|---|---|
| **Entity** | CommunityEntity → `MomentsFeed` (Heart Button) |
| **Endpoint** | `POST /api/moments/{id}/like` |
| **Middleware** | `auth:sanctum` |
| **Service** | `MomentService@toggleLike` — Check if `MomentLike` exists → delete (unlike) / create (like) |
| **Repository** | `MomentLikeRepository@create / delete` |
| **Response** | `200 OK` → `{ liked: bool, likes_count: int }` |

---

### Feature 4.4: Submit Grammar Correction on Moment

| Layer | Detail |
|---|---|
| **Entity** | CommunityEntity → `MomentsFeed` (Correction Modal) |
| **Endpoint** | `POST /api/moments/{id}/corrections` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreCorrectionRequest` |
| **Validation** | `corrected_text: required|string|max:5000` |
| **Service** | `MomentCorrectionService@create` — Create correction record, notify moment author |
| **Repository** | `MomentCorrectionRepository@create`, `NotificationRepository@create` |
| **Response** | `201 Created` → `{ correction: CorrectionResource }` |

---

### Feature 4.5: Translate Moment

| Layer | Detail |
|---|---|
| **Entity** | CommunityEntity → `MomentsFeed` (Translate Button) |
| **Endpoint** | `POST /api/translate` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `TranslateRequest` |
| **Validation** | `text: required|string`, `target_lang: required|string|max:5` |
| **Service** | `TranslationService@translate` — Call external translation API (e.g., Google Translate, DeepL), cache result |
| **Response** | `200 OK` → `{ translated_text: string }` |

---

### Feature 4.6: Discover Language Partners

| Layer | Detail |
|---|---|
| **Entity** | CommunityEntity → `ConnectHub` |
| **Endpoint** | `GET /api/users/discover` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?search=&level=&gender=&online=` |
| **Service** | `UserService@discover` — Exclude self, filter by level/gender/online status, calculate `match` percentage based on shared languages and interests |
| **Repository** | `UserRepository@all` (with filters) |
| **Response** | `200 OK` → `{ users: DiscoverUserResource[] }` |

---

### Feature 4.7: Send Friend Request

| Layer | Detail |
|---|---|
| **Entity** | CommunityEntity → `ConnectHub` (Add Friend Button) |
| **Endpoint** | `POST /api/friend-requests` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreFriendRequest` |
| **Validation** | `user_id: required|exists:users,id|not_in:{auth_id}` |
| **Service** | `FriendService@sendRequest` — Create friend request record, notify target user |
| **Repository** | (New) `FriendRequestRepository@create`, `NotificationRepository@create` |
| **Response** | `201 Created` → `{ message: 'Friend request sent!' }` |

---

## 5. Chat Entity

> **React Source**: `src/entities/Chat/index.jsx` → `ChatHub`  
> **Related Models**: `Chat`, `ChatMember`, `Message`, `User`

---

### Feature 5.1: Fetch Chat List

| Layer | Detail |
|---|---|
| **Entity** | ChatEntity → `ChatHub` (Left Panel) |
| **Endpoint** | `GET /api/chats` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?type=direct|instructors|group&search=` |
| **Service** | `ChatService@list` — Fetch user's chats via `chat_members`, include latest message, unread count, other participants |
| **Repository** | `ChatRepository@all` (via user relationship) |
| **Response** | `200 OK` → `{ chats: ChatResource[] }` |

---

### Feature 5.2: Fetch Chat Messages

| Layer | Detail |
|---|---|
| **Entity** | ChatEntity → `ChatHub` (Right Panel) |
| **Endpoint** | `GET /api/chats/{id}/messages` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `ChatPolicy@view` → user is a member of the chat |
| **Query Params** | `?page=&per_page=50` (cursor pagination) |
| **Service** | `ChatService@getMessages` — Fetch paginated messages, mark as read, reset unread_count |
| **Repository** | `MessageRepository@all`, `ChatMemberRepository@update` |
| **Response** | `200 OK` → `{ messages: MessageResource[], next_cursor: string|null }` |

---

### Feature 5.3: Send Message

| Layer | Detail |
|---|---|
| **Entity** | ChatEntity → `ChatHub` (Message Input) |
| **Endpoint** | `POST /api/chats/{id}/messages` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `ChatPolicy@sendMessage` → user is a member |
| **FormRequest** | `StoreMessageRequest` |
| **Validation** | `text: required|string|max:10000`, `attachment: nullable|file|max:10240` |
| **Service** | `ChatService@sendMessage` — Create message, update chat `last_message`, increment unread for other members, broadcast via WebSocket |
| **Repository** | `MessageRepository@create`, `ChatRepository@update`, `ChatMemberRepository@update` |
| **Response** | `201 Created` → `{ message: MessageResource }` |

---

### Feature 5.4: Translate Chat Message

| Layer | Detail |
|---|---|
| **Entity** | ChatEntity → `ChatHub` (Translate Button) |
| **Endpoint** | `POST /api/translate` |
| **Middleware** | `auth:sanctum` |
| **Service** | `TranslationService@translate` (shared with Moments) |
| **Response** | `200 OK` → `{ translated_text: string }` |

---

### Feature 5.5: Submit Chat Correction

| Layer | Detail |
|---|---|
| **Entity** | ChatEntity → `ChatHub` (Correction Modal) |
| **Endpoint** | `POST /api/chats/{chatId}/messages/{msgId}/corrections` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreMessageCorrectionRequest` |
| **Validation** | `corrected_text: required|string|max:10000` |
| **Service** | `ChatService@submitCorrection` — Create a special correction message in the chat thread |
| **Repository** | `MessageRepository@create` (with `is_correction = true`) |
| **Response** | `201 Created` → `{ message: MessageResource }` |

---

### Feature 5.6: Create Group Chat

| Layer | Detail |
|---|---|
| **Entity** | ChatEntity → `ChatHub` (Create Group Flow) |
| **Endpoint** | `POST /api/chats/group` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreGroupChatRequest` |
| **Validation** | `name: required|string|max:100`, `member_ids: required|array|min:1`, `member_ids.*: required|exists:users,id` |
| **Service** | `ChatService@createGroup` — Create `Chat` (type=group), add `ChatMember` entries for creator (role=admin) and all members |
| **Repository** | `ChatRepository@create`, `ChatMemberRepository@create` |
| **Response** | `201 Created` → `{ chat: ChatResource }` |

---

### Feature 5.7: Leave Group / Block User / Disband Group

| Layer | Detail |
|---|---|
| **Entity** | ChatEntity → `ChatSettingsModal` |
| **Endpoint (Leave)** | `POST /api/chats/{id}/leave` |
| **Endpoint (Block)** | `POST /api/users/{id}/block` |
| **Endpoint (Disband)** | `DELETE /api/chats/{id}` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `ChatPolicy@leave` / `ChatPolicy@delete` (admin only for disband) |
| **Service** | `ChatService@leave`, `UserService@block`, `ChatService@disband` |
| **Repository** | `ChatMemberRepository@delete`, `ChatRepository@delete` |
| **Response** | `200 OK` → `{ message: 'Success' }` |

---

## 6. Instructor Entity

> **React Source**: `src/entities/Instructor/index.jsx` → `CallDashboard` → `OverviewTab`, `MyCourses`, `QuizzesTab`, `AssessmentsTab`, `LearningGroupsTab`, `FeedbackTab`, `RatingsTab`, `AvailabilityTab`  
> **Auth**: `auth:sanctum` + `role:instructor` on ALL routes  
> **Related Models**: `Instructor`, `Course`, `Lesson`, `LessonMaterial`, `QuizQuestion`, `QuizResult`, `Review`, `Booking`, `InstructorSlot`

---

### Feature 6.1: Dashboard Overview (Stats)

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `CallDashboard` → `OverviewTab` |
| **Endpoint** | `GET /api/instructor/dashboard` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Service** | `InstructorDashboardService@overview` — Aggregate: `totalRevenue` (from bookings), `activeStudents` (enrollment count), `completionRate` (avg progress), `avgRating`, `topCourses`, `recentActivity`, `upcomingSessions`, `studentsByLevel` |
| **Repository** | `BookingRepository`, `EnrollmentRepository`, `CourseRepository`, `ReviewRepository` |
| **Response** | `200 OK` → `{ overview: DashboardOverviewResource }` |

---

### Feature 6.2: CRUD Courses (Instructor's My Courses)

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `MyCourses` |
| **Endpoint (List)** | `GET /api/instructor/courses` |
| **Endpoint (Create)** | `POST /api/instructor/courses` |
| **Endpoint (Update)** | `PUT /api/instructor/courses/{id}` |
| **Endpoint (Delete)** | `DELETE /api/instructor/courses/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Policy** | `CoursePolicy@update/delete` → instructor_id matches |
| **FormRequest** | `StoreCourseRequest` / `UpdateCourseRequest` |
| **Validation** | `title: required|string|max:255`, `level: required|in:A1,A2,B1,B2,C1,C2`, `language: required|string`, `price: required|numeric|min:0`, `image: nullable|image|max:5120`, `description: required|string`, `category: required|in:Medical,Legal,Business,Technical,General`, `is_published: boolean` |
| **Service** | `CourseService@create/update/delete` — Handle thumbnail upload, manage course visibility |
| **Repository** | `CourseRepository@create/update/delete` |
| **Response (Create)** | `201 Created` → `{ course: CourseResource }` |
| **Response (Update)** | `200 OK` → `{ course: CourseResource }` |
| **Response (Delete)** | `200 OK` → `{ message: 'Course deleted' }` |

---

### Feature 6.3: CRUD Lessons within a Course

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `MyCourses` (Lesson Management) |
| **Endpoint (Create)** | `POST /api/instructor/courses/{courseId}/lessons` |
| **Endpoint (Update)** | `PUT /api/instructor/lessons/{id}` |
| **Endpoint (Delete)** | `DELETE /api/instructor/lessons/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Policy** | `LessonPolicy@create/update/delete` → course belongs to instructor |
| **FormRequest** | `StoreLessonRequest` |
| **Validation** | `title: required|string|max:255`, `description: nullable|string`, `video_url: nullable|url`, `duration: nullable|string`, `order: required|integer|min:1`, `level: required|string`, `status: required|in:active,recorded`, `has_quiz: boolean` |
| **Service** | `LessonService@create/update/delete` — Handle video/material uploads, reorder lessons, update course total_lessons |
| **Repository** | `LessonRepository@create/update/delete`, `CourseRepository@update` |
| **Response** | `201 Created` / `200 OK` → `{ lesson: LessonResource }` |

---

### Feature 6.4: CRUD Lesson Materials

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `MyCourses` (Material Management) |
| **Endpoint** | `POST /api/instructor/lessons/{lessonId}/materials` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **FormRequest** | `StoreMaterialRequest` |
| **Validation** | `name: required|string`, `file: required|file|max:20480`, `type: nullable|in:PDF,DOC,PPT` |
| **Service** | `LessonMaterialService@upload` — Store file to `storage/materials/`, create `LessonMaterial` record |
| **Repository** | `LessonMaterialRepository@create` |
| **Response** | `201 Created` → `{ material: MaterialResource }` |

---

### Feature 6.5: CRUD Quiz Questions

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `QuizzesTab` |
| **Endpoint (List)** | `GET /api/instructor/courses/{courseId}/quizzes` |
| **Endpoint (Create)** | `POST /api/instructor/courses/{courseId}/quizzes` |
| **Endpoint (Update)** | `PUT /api/instructor/quizzes/{id}` |
| **Endpoint (Delete)** | `DELETE /api/instructor/quizzes/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **FormRequest** | `StoreQuizQuestionRequest` |
| **Validation** | `lesson_id: nullable|exists:lessons,id`, `question_text: required|string`, `options: required|array|min:2`, `options.*: required|string`, `correct_option: required|string`, `explanation: nullable|string` |
| **Service** | `QuizService@create/update/delete` |
| **Repository** | `QuizQuestionRepository@create/update/delete` |
| **Response** | `201 Created` → `{ question: QuizQuestionResource }` |

---

### Feature 6.6: View Student Feedback

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `FeedbackTab` |
| **Endpoint** | `GET /api/instructor/feedback` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Service** | `InstructorDashboardService@getFeedback` — Aggregate quiz results, completion rates per student |
| **Repository** | `QuizResultRepository@all`, `LessonCompletionRepository@all` |
| **Response** | `200 OK` → `{ feedback: FeedbackResource[] }` |

---

### Feature 6.7: View Ratings & Reviews

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `RatingsTab` |
| **Endpoint** | `GET /api/instructor/reviews` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Service** | `ReviewService@listForInstructor` — Fetch all reviews with `user` eager load |
| **Repository** | `ReviewRepository@all` |
| **Response** | `200 OK` → `{ reviews: ReviewResource[], avg_rating: float, total_reviews: int }` |

---

### Feature 6.8: Manage Availability Slots

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `AvailabilityTab` |
| **Endpoint (List)** | `GET /api/instructor/slots` |
| **Endpoint (Create)** | `POST /api/instructor/slots` |
| **Endpoint (Update)** | `PUT /api/instructor/slots/{id}` |
| **Endpoint (Delete)** | `DELETE /api/instructor/slots/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **FormRequest** | `StoreSlotRequest` |
| **Validation** | `date: required|date|after_or_equal:today`, `start_time: required|date_format:H:i`, `end_time: required|date_format:H:i|after:start_time` |
| **Service** | `InstructorSlotService@create/update/delete` — Validate no overlaps, handle recurring slots |
| **Repository** | `InstructorSlotRepository@create/update/delete` |
| **Response** | `201 Created` → `{ slot: SlotResource }` |

---

### Feature 6.9: Manage Assessments

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `AssessmentsTab` |
| **Endpoint** | `GET /api/instructor/assessments` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Service** | `InstructorDashboardService@getAssessments` — Fetch quiz results grouped by course/lesson, include student data |
| **Repository** | `QuizResultRepository@all` |
| **Response** | `200 OK` → `{ assessments: AssessmentResource[] }` |

---

### Feature 6.10: Manage Learning Groups

| Layer | Detail |
|---|---|
| **Entity** | InstructorEntity → `LearningGroupsTab` |
| **Endpoint (List)** | `GET /api/instructor/groups` |
| **Endpoint (Create)** | `POST /api/instructor/groups` |
| **Endpoint (Update)** | `PUT /api/instructor/groups/{id}` |
| **Endpoint (Delete)** | `DELETE /api/instructor/groups/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **FormRequest** | `StoreGroupRequest` |
| **Validation** | `name: required|string|max:255`, `description: nullable|string`, `course_id: nullable|exists:courses,id`, `max_members: required|integer|min:2|max:50`, `member_ids: nullable|array`, `member_ids.*: exists:users,id` |
| **Service** | `LearningGroupService@create/update/delete` — Create group chat, assign members |
| **Repository** | `ChatRepository@create`, `ChatMemberRepository@create` |
| **Response** | `201 Created` → `{ group: GroupResource }` |

---

## 7. Media Entity (Podcasts)

> **React Source**: `src/entities/Media/index.jsx` → `PodcastTab`  
> **Auth for CRUD**: `role:instructor` | **Auth for Read**: `auth:sanctum`  
> **Related Models**: `Podcast`, `Instructor`

---

### Feature 7.1: List Podcasts

| Layer | Detail |
|---|---|
| **Entity** | MediaEntity → `PodcastTab` |
| **Endpoint** | `GET /api/podcasts` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?category=&search=` |
| **Service** | `PodcastService@list` — Filter by category, eager load `instructor.user` |
| **Repository** | `PodcastRepository@all` |
| **Response** | `200 OK` → `{ podcasts: PodcastResource[] }` |

---

### Feature 7.2: Upload Podcast (Instructor Only)

| Layer | Detail |
|---|---|
| **Entity** | MediaEntity → `PodcastTab` (Upload Modal) |
| **Endpoint** | `POST /api/podcasts` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **FormRequest** | `StorePodcastRequest` |
| **Validation** | `title: required|string|max:255`, `description: required|string`, `category: required|in:General,Legal,Medical,Business,Technical,Literary`, `audio_file: required|file|mimes:mp3,wav,m4a|max:102400`, `thumbnail: nullable|image|max:5120` |
| **Service** | `PodcastService@create` — Store audio file to `storage/podcasts/`, store thumbnail, calculate duration, link to instructor |
| **Repository** | `PodcastRepository@create` |
| **Response** | `201 Created` → `{ podcast: PodcastResource }` |

---

### Feature 7.3: Update Podcast (Instructor Only)

| Layer | Detail |
|---|---|
| **Entity** | MediaEntity → `PodcastTab` (Edit Button) |
| **Endpoint** | `PUT /api/podcasts/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Policy** | `PodcastPolicy@update` → instructor owns the podcast |
| **FormRequest** | `UpdatePodcastRequest` |
| **Service** | `PodcastService@update` |
| **Repository** | `PodcastRepository@update` |
| **Response** | `200 OK` → `{ podcast: PodcastResource }` |

---

### Feature 7.4: Delete Podcast (Instructor Only)

| Layer | Detail |
|---|---|
| **Entity** | MediaEntity → `PodcastTab` (Delete Button) |
| **Endpoint** | `DELETE /api/podcasts/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Policy** | `PodcastPolicy@delete` → instructor owns the podcast |
| **Service** | `PodcastService@delete` — Remove audio file and thumbnail from storage |
| **Repository** | `PodcastRepository@delete` |
| **Response** | `200 OK` → `{ message: 'Podcast deleted' }` |

---

## 8. Certificates Entity

> **React Source**: `src/entities/Certificates/index.jsx` → `CertificatesTab`  
> **Related Models**: `Certificate`, `Course`, `User`

---

### Feature 8.1: List My Certificates

| Layer | Detail |
|---|---|
| **Entity** | CertificatesEntity → `CertificatesTab` |
| **Endpoint** | `GET /api/certificates` |
| **Middleware** | `auth:sanctum` |
| **Service** | `CertificateService@list` — Fetch user's certificates with `course`, `instructor` relations |
| **Repository** | `CertificateRepository@all` |
| **Response** | `200 OK` → `{ certificates: CertificateResource[] }` |

---

### Feature 8.2: Download Certificate PDF

| Layer | Detail |
|---|---|
| **Entity** | CertificatesEntity → `CertificatesTab` (Download Button) |
| **Endpoint** | `GET /api/certificates/{id}/download` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `CertificatePolicy@download` → user owns the certificate |
| **Service** | `CertificateService@download` — Generate PDF using a Blade template or DomPDF with user name, course name, score, date, verification code |
| **Response** | `200 OK` → PDF file download (`Content-Type: application/pdf`) |

---

### Feature 8.3: Verify Certificate (Public)

| Layer | Detail |
|---|---|
| **Entity** | CertificatesEntity → `CertificatesTab` (Verify Link) |
| **Endpoint** | `GET /api/certificates/verify/{code}` |
| **Middleware** | None (public) |
| **Service** | `CertificateService@verify` — Lookup by `verification_code`, return validity |
| **Repository** | `CertificateRepository@find` |
| **Response** | `200 OK` → `{ valid: bool, certificate: CertificateResource|null }` |

---

## 9. Problems Entity

> **React Source**: `src/entities/Problems/index.jsx` → `ProblemsTab`  
> **Related Models**: (New) `Problem`, `ProblemComment`, `ProblemVote` — or use existing models with polymorphic approach  
> **Note**: This feature represents a Q&A/forum for translation problems. No existing models mapped yet; schema extension is recommended.

---

### Feature 9.1: List Translation Problems

| Layer | Detail |
|---|---|
| **Entity** | ProblemsEntity → `ProblemsTab` |
| **Endpoint** | `GET /api/problems` |
| **Middleware** | `auth:sanctum` |
| **Query Params** | `?type=Writing|Reading|Lesson&search=` |
| **Service** | `ProblemService@list` — Filter by type, search by title/description, include `reporter`, `upvotes_count`, `comments_count` |
| **Repository** | (New) `ProblemRepository@all` |
| **Response** | `200 OK` → `{ problems: ProblemResource[] }` |

---

### Feature 9.2: Create Problem

| Layer | Detail |
|---|---|
| **Entity** | ProblemsEntity → `ProblemsTab` (Report New Problem) |
| **Endpoint** | `POST /api/problems` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreProblemRequest` |
| **Validation** | `title: required|string|max:255`, `type: required|in:Writing,Reading,Lesson`, `level: required|in:Beginner,Intermediate,Advanced`, `description: required|string|max:5000` |
| **Service** | `ProblemService@create` — Create problem with status `Open` |
| **Repository** | `ProblemRepository@create` |
| **Response** | `201 Created` → `{ problem: ProblemResource }` |

---

### Feature 9.3: Edit Problem (Instructor Only)

| Layer | Detail |
|---|---|
| **Entity** | ProblemsEntity → `ProblemsTab` (Edit Button) |
| **Endpoint** | `PUT /api/problems/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Policy** | `ProblemPolicy@update` |
| **FormRequest** | `UpdateProblemRequest` |
| **Validation** | Same as create + `status: required|in:Open,In Discussion,Resolved` |
| **Service** | `ProblemService@update` |
| **Repository** | `ProblemRepository@update` |
| **Response** | `200 OK` → `{ problem: ProblemResource }` |

---

### Feature 9.4: Delete Problem (Instructor Only)

| Layer | Detail |
|---|---|
| **Entity** | ProblemsEntity → `ProblemsTab` (Delete Button) |
| **Endpoint** | `DELETE /api/problems/{id}` |
| **Middleware** | `auth:sanctum`, `role:instructor` |
| **Policy** | `ProblemPolicy@delete` |
| **Service** | `ProblemService@delete` |
| **Repository** | `ProblemRepository@delete` |
| **Response** | `200 OK` → `{ message: 'Problem deleted' }` |

---

### Feature 9.5: Upvote Problem

| Layer | Detail |
|---|---|
| **Entity** | ProblemsEntity → `ProblemsTab` (I Have This Too) |
| **Endpoint** | `POST /api/problems/{id}/upvote` |
| **Middleware** | `auth:sanctum` |
| **Service** | `ProblemService@upvote` — Check if user already voted, create vote record |
| **Repository** | (New) `ProblemVoteRepository@create` |
| **Response** | `200 OK` → `{ upvotes: int }` |

---

### Feature 9.6: Comment on Problem

| Layer | Detail |
|---|---|
| **Entity** | ProblemsEntity → `ProblemsTab` (Expanded Details) |
| **Endpoint** | `POST /api/problems/{id}/comments` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreProblemCommentRequest` |
| **Validation** | `text: required|string|max:2000` |
| **Service** | `ProblemCommentService@create` — Create comment, increment commentsCount |
| **Repository** | (New) `ProblemCommentRepository@create` |
| **Response** | `201 Created` → `{ comment: ProblemCommentResource }` |

---

## 10. Profile Entity

> **React Source**: `src/entities/Profile/index.jsx` → `MyProfileScreen`, `ProfileSettings`  
> **Related Models**: `User`, `UserLanguage`, `UserInterest`, `Moment`, `Subscription`

---

### Feature 10.1: Fetch My Profile

| Layer | Detail |
|---|---|
| **Entity** | ProfileEntity → `MyProfileScreen` |
| **Endpoint** | `GET /api/profile` |
| **Middleware** | `auth:sanctum` |
| **Service** | `ProfileService@show` — Load user with `languages`, `interests`, `moments`, `subscription`, stats (moments count, words learned, corrections given, streak) |
| **Repository** | `UserRepository@find` |
| **Response** | `200 OK` → `{ profile: ProfileResource }` |

---

### Feature 10.2: Update Profile Field

| Layer | Detail |
|---|---|
| **Entity** | ProfileEntity → `MyProfileScreen` (Inline Edit) |
| **Endpoint** | `PATCH /api/profile` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `UpdateProfileRequest` |
| **Validation** | `name: sometimes|string|max:255`, `bio: sometimes|string|max:1000`, `location: sometimes|string|max:100`, `avatar: sometimes|image|max:5120` |
| **Service** | `ProfileService@update` — Handle avatar upload, update user record |
| **Repository** | `UserRepository@update` |
| **Response** | `200 OK` → `{ profile: ProfileResource, message: 'Profile updated' }` |

---

### Feature 10.3: Change Avatar

| Layer | Detail |
|---|---|
| **Entity** | ProfileEntity → `MyProfileScreen` (Camera Icon) |
| **Endpoint** | `POST /api/profile/avatar` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `UpdateAvatarRequest` |
| **Validation** | `avatar: required|image|mimes:jpg,png,webp|max:5120` |
| **Service** | `ProfileService@updateAvatar` — Delete old avatar, store new to `storage/avatars/`, update user |
| **Repository** | `UserRepository@update` |
| **Response** | `200 OK` → `{ avatar_url: string }` |

---

### Feature 10.4: Post Moment from Profile

| Layer | Detail |
|---|---|
| **Entity** | ProfileEntity → `MyProfileScreen` (My Moments Composer) |
| **Endpoint** | `POST /api/moments` (same as Feature 4.2) |
| **Middleware** | `auth:sanctum` |
| **Service** | `MomentService@create` |
| **Response** | `201 Created` → `{ moment: MomentResource }` |

---

### Feature 10.5: Delete Own Moment

| Layer | Detail |
|---|---|
| **Entity** | ProfileEntity → `MyProfileScreen` (Delete Button) |
| **Endpoint** | `DELETE /api/moments/{id}` |
| **Middleware** | `auth:sanctum` |
| **Policy** | `MomentPolicy@delete` → user owns the moment |
| **Service** | `MomentService@delete` — Remove images from storage, delete moment |
| **Repository** | `MomentRepository@delete` |
| **Response** | `200 OK` → `{ message: 'Moment deleted' }` |

---

### Feature 10.6: Profile Settings (Account Management)

| Layer | Detail |
|---|---|
| **Entity** | ProfileEntity → `ProfileSettings` |
| **Endpoint (Password)** | `PUT /api/profile/password` |
| **Endpoint (Email)** | `PUT /api/profile/email` |
| **Endpoint (Notifications)** | `PUT /api/profile/notifications-settings` |
| **Endpoint (Delete)** | `DELETE /api/profile` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `ChangePasswordRequest` → `current_password: required, new_password: required|min:8|confirmed` |
| **Service** | `ProfileService@changePassword`, `ProfileService@changeEmail`, `ProfileService@deleteAccount` |
| **Repository** | `UserRepository@update / delete` |
| **Response** | `200 OK` → `{ message: 'Updated' }` |

---

### Feature 10.7: Manage Subscription

| Layer | Detail |
|---|---|
| **Entity** | ProfileEntity → `ProfileSettings` (Subscription Section) |
| **Endpoint (View)** | `GET /api/subscription` |
| **Endpoint (Upgrade)** | `POST /api/subscription` |
| **Endpoint (Cancel)** | `DELETE /api/subscription` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `StoreSubscriptionRequest` → `plan: required|in:monthly,yearly`, `payment_method: required|string` |
| **Service** | `SubscriptionService@subscribe/cancel` — Handle payment integration, update `is_vip` on user |
| **Repository** | `SubscriptionRepository@create/update/delete`, `UserRepository@update` |
| **Response** | `201 Created` → `{ subscription: SubscriptionResource }` |

---

## 11. Guest Entity

> **React Source**: `src/entities/Guest/index.jsx` → `LandingView`, `CourseCatalogView`, `TranslationTypesView`  
> **Auth**: None (public routes)

---

### Feature 11.1: Landing Page Data

| Layer | Detail |
|---|---|
| **Entity** | GuestEntity → `LandingView` |
| **Endpoint** | `GET /api/public/stats` |
| **Middleware** | None |
| **Service** | `PublicService@getStats` — Return total students count, total courses count, featured instructors |
| **Repository** | `UserRepository`, `CourseRepository`, `InstructorRepository` |
| **Response** | `200 OK` → `{ total_students: int, total_courses: int, featured_instructors: InstructorResource[] }` |

---

### Feature 11.2: Public Course Catalog

| Layer | Detail |
|---|---|
| **Entity** | GuestEntity → `CourseCatalogView` |
| **Endpoint** | `GET /api/public/courses` |
| **Middleware** | None |
| **Query Params** | `?category=&level=&search=` |
| **Service** | `PublicService@getCourses` — Fetch published courses with instructor name, filtered |
| **Repository** | `CourseRepository@all` (where `is_published = true`) |
| **Response** | `200 OK` → `{ courses: PublicCourseResource[] }` |

---

## 12. Global / Shared Features

---

### Feature 12.1: Translation API (Shared)

| Layer | Detail |
|---|---|
| **Entity** | Used by: Chat, Moments, Lessons |
| **Endpoint** | `POST /api/translate` |
| **Middleware** | `auth:sanctum` |
| **FormRequest** | `TranslateRequest` |
| **Validation** | `text: required|string|max:10000`, `source_lang: nullable|string`, `target_lang: required|string` |
| **Service** | `TranslationService@translate` — Call external API (Google Translate / DeepL), cache commonly requested translations |
| **Response** | `200 OK` → `{ translated_text: string, detected_source: string }` |

---

### Feature 12.2: File Upload Handler (Shared)

| Layer | Detail |
|---|---|
| **Entity** | Used by: Profile (avatar), Courses (thumbnail), Podcasts (audio), Moments (images), Lessons (materials) |
| **Service** | `FileUploadService@upload($file, $directory)` — Store to `storage/app/public/{directory}/`, return public URL |
| **Disks** | `public` disk with symbolic link (`php artisan storage:link`) |

---

### Feature 12.3: Notification System (Shared)

| Layer | Detail |
|---|---|
| **Entity** | Used by: Dashboard, Bookings, Moments, Chat |
| **Service** | `NotificationService@create($userId, $type, $title, $body, $data)` — Create in-app notification, optionally trigger push via FCM |
| **Repository** | `NotificationRepository@create` |
| **Model** | `Notification` → `user_id, type, title, body, data (json), read_at` |

---

## Summary: Files to Create

### Controllers
| File | Purpose |
|---|---|
| `AuthController` | Register, Login, Google OAuth, Logout |
| `DashboardController` | Progress, Courses, Check-In, Bookings |
| `InstructorController` | List, Show, Slots |
| `CourseController` | CRUD (instructor), Show (student) |
| `LessonController` | CRUD (instructor), Show, Complete |
| `BookingController` | Create, List |
| `MomentController` | CRUD, Like, Correct, Translate |
| `ChatController` | List, Messages, Send, Group, Leave |
| `PodcastController` | CRUD |
| `CertificateController` | List, Download, Verify |
| `ProblemController` | CRUD, Upvote, Comment |
| `ProfileController` | Show, Update, Avatar, Password, Delete |
| `NotificationController` | List, MarkRead, Delete |
| `SubscriptionController` | Show, Subscribe, Cancel |
| `PublicController` | Stats, Course Catalog |
| `TranslationController` | Translate text |

### FormRequests (22+)
`RegisterRequest`, `LoginRequest`, `OnboardingRequest`, `StoreCourseRequest`, `UpdateCourseRequest`, `StoreLessonRequest`, `StoreBookingRequest`, `StoreEnrollmentRequest`, `CompleteLessonRequest`, `SubmitQuizRequest`, `StoreReviewRequest`, `StoreMomentRequest`, `StoreCorrectionRequest`, `TranslateRequest`, `StoreMessageRequest`, `StoreGroupChatRequest`, `StorePodcastRequest`, `UpdatePodcastRequest`, `StoreProblemRequest`, `UpdateProblemRequest`, `StoreProblemCommentRequest`, `UpdateProfileRequest`, `UpdateAvatarRequest`, `ChangePasswordRequest`, `StoreSubscriptionRequest`, `StoreSlotRequest`, `StoreQuizQuestionRequest`, `StoreMaterialRequest`

### Services (14+)
`AuthService`, `OnboardingService`, `DashboardService`, `InstructorService`, `InstructorDashboardService`, `CourseService`, `LessonService`, `BookingService`, `MomentService`, `MomentCorrectionService`, `ChatService`, `PodcastService`, `CertificateService`, `ProblemService`, `ProblemCommentService`, `ProfileService`, `NotificationService`, `SubscriptionService`, `TranslationService`, `FileUploadService`, `UserService`, `FriendService`, `QuizService`, `InstructorSlotService`, `LearningGroupService`, `PublicService`, `ReviewService`, `LessonMaterialService`

### Policies (10+)
`CoursePolicy`, `LessonPolicy`, `BookingPolicy`, `EnrollmentPolicy`, `MomentPolicy`, `ChatPolicy`, `PodcastPolicy`, `CertificatePolicy`, `ProblemPolicy`, `NotificationPolicy`, `ReviewPolicy`

### Middleware
| Middleware | Purpose |
|---|---|
| `auth:sanctum` | API token authentication |
| `role:{role}` | Custom middleware to check `$user->role === $role` |

### New Migrations Needed
| Table | Reason |
|---|---|
| `problems` | Q&A forum for translation problems |
| `problem_comments` | Comments on problems |
| `problem_votes` | Upvotes on problems |
| `friend_requests` | Friend request system for ConnectHub |

---

> **Document generated by analyzing all 11 React Entities, 17+ Components, and 26 Laravel Models to ensure zero feature gaps.**
