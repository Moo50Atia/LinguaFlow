# Architecture Decision Record: Action-Based Services Pattern

**Date**: April 2026  
**Project**: LinguaFlow - Translation & Language Learning Platform  
**Target Backend**: Laravel 10/11 REST API  

## 1. Compatibility Score: 92% (Highly Suitable)

The **Action-based architecture** is exceptionally well-suited for a platform like LinguaFlow, especially given the heavy business logic involved in course progression, chat thread management (with corrections), and complex instructor booking rules. 

**Justification:**
In a standard Service class model, an `InstructorService` or `CourseService` would quickly degrade into a "God Class" thousands of lines long. For instance, handling a `CompleteLessonAction` entails:
1. Validating the quiz score
2. Creating the `LessonCompletion` pivot
3. Updating the `Enrollment` overall progress
4. Checking if the course is finished
5. Generating a `Certificate` if finished
6. Emitting a `Notification` 

Stacking this alongside Create, Update, Delete, and Read methods inside a single `LessonService` creates tangled dependencies. The Action pattern isolates this exact execution context into one highly testable, distinct file.

---

## 2. Pros & Cons (Action-Based vs. Service-Repository)

### Code Maintainability & SOLID Principles
*   **Action-Based (Winner):** Forces strict adherence to the **Single Responsibility Principle (SRP)** and **Open-Closed Principle (OCP)**. If you need to change how a user books a session, you modify `BookSessionAction` without risking the stability of `CancelSessionAction` or `RescheduleSessionAction`. Dependencies injected into the constructor are strictly limited to what that specific action needs.
*   **Service-Repository:** Traditional services often violate SRP down the line. A `CourseService` might need to inject 8 different repositories (User, Course, Lesson, Material, Quiz) just to function, even if the `$service->publishCourse()` method only uses one of them.

### Performance & Memory Footprint
*   **Action-Based (Winner):** Lighter memory footprint per request. PHP only loads the specific Action class and its exact dependencies via the Service Container.
*   **Service-Repository:** Loading a mega-service means resolving and instantiating dependencies that the current HTTP request might not even touch, increasing overhead.

### Developer Experience 
*   **Service-Repository (Winner):** Easier initial discovery. Developers look at `CourseService.php` to see everything related to courses.
*   **Action-Based:** Can result in **100+ small files**. Managing this requires strict folder organization (e.g., `app/Actions/Course/CreateCourseAction.php`). If organized poorly, finding the correct business logic logic can be overwhelming.

---

## 3. Refactoring & Implementation Effort

*   **Current State:** 26 Data Models, 11 Frontend Entities, 0 Backend Logic files created.
*   **Effort Estimate:** **Moderate-to-High**
*   **Explanation:** Since the backend logic is not yet written, there is no "refactoring" debt. The effort comes from the sheer volume of classes to scaffold. Based on the 26 models, we are estimating around **80 to 90 distinct Action classes**.
*   **Quick Tip:** Create a custom Artisan stub (`php artisan make:action`) to speed up the boilerplate generation for these action classes.

---

## 4. Verdict & Recommendation

**Verdict: Proceed with the Action-Based Architecture.**

For a high-quality Graduation Project, implementing the Action Pattern is an extremely strong **Engineering Maturity Signal**. 

It demonstrates to job recruiters and senior technical reviewers that you:
1. Understand the pitfalls of MVC "Fat Controllers" and "Fat Models".
2. Know how to architect for scalability and unit-testability out-of-the-box.
3. Understand modern Laravel ecosystem trends (widely popularized by internal Laravel packages like Fortify and Jetstream).

*Recommendation:* Do not overcomplicate the database interactions. You can safely combine **Actions + Eloquent Models** (skipping the Repository layer). Eloquent is an Active Record implementation; wrapping it in pure Repositories alongside Actions often leads to unnecessary anemic abstractions unless you plan to swap out MySQL for MongoDB in the near future. 

---

## 5. The Action Map (Organized by Entity)

Based on the 11 Frontend Entities and 26 Database Models, here are the exact Action classes you need to create under `app/Actions/`.

### 🛡️ 1. Auth Entity (`app/Actions/Auth/`)
*   `RegisterUserAction`
*   `AuthenticateUserAction` (Login)
*   `HandleGoogleOAuthAction`
*   `CompleteOnboardingAction` (Handles CefrLevel, UserLanguage, UserInterest)
*   `LogoutUserAction`

### 📊 2. Dashboard Entity (`app/Actions/Dashboard/`)
*   `GetStudentProgressStatsAction`
*   `GetEnrolledCoursesAction`
*   `ProcessDailyCheckInAction` (Handles StudyDay + Streaks)
*   `GetUpcomingBookingsAction`
*   `MarkNotificationAsReadAction`
*   `DeleteNotificationAction`

### 📚 3. Learning Entity (`app/Actions/Learning/`)
*   `GetInstructorCatalogAction` (With filters)
*   `GetInstructorDetailedProfileAction`
*   `CreateBookingSessionAction` (Handles slot reservation & pricing)
*   `GetLessonCatalogAction`
*   `GetCourseDetailsAction`
*   `EnrollStudentInCourseAction`
*   `EvaluateLessonQuizAction` (Handles QuizQuestions -> QuizResults)
*   `MarkLessonCompleteAction` (Progress tracking + Certificate trigger)
*   `SubmitInstructorReviewAction`
*   `GetInstructorAvailableSlotsAction`

### 🌍 4. Community Entity (`app/Actions/Community/`)
*   `GetMomentsFeedAction`
*   `CreateMomentPostAction`
*   `ToggleMomentLikeAction`
*   `SubmitMomentCorrectionAction`
*   `DiscoverLanguagePartnersAction` (Matching algorithm)
*   `SendFriendRequestAction`

### 💬 5. Chat Entity (`app/Actions/Chat/`)
*   `GetUserChatsAction`
*   `GetChatMessagesAction`
*   `SendChatMessageAction`
*   `SubmitMessageCorrectionAction` (Creates special correction message view)
*   `CreateGroupChatAction`
*   `LeaveChatAction`
*   `DisbandGroupChatAction`

### 👨‍🏫 6. Instructor Entity (`app/Actions/Instructor/`)
*   `GetInstructorDashboardStatsAction`
*   `CreateCourseAction`
*   `UpdateCourseAction`
*   `DeleteCourseAction`
*   `PublishCourseAction`
*   `CreateLessonAction`
*   `UpdateLessonAction`
*   `DeleteLessonAction`
*   `UploadLessonMaterialAction`
*   `CreateQuizQuestionAction`
*   `GetStudentFeedbackAction`
*   `CreateAvailabilitySlotAction`
*   `DeleteAvailabilitySlotAction`
*   `CreateLearningGroupAction`

### 🎧 7. Media Entity (`app/Actions/Media/`)
*   `GetPodcastsAction`
*   `UploadNewPodcastAction`
*   `UpdatePodcastAction`
*   `DeletePodcastAction`

### 🏆 8. Certificates Entity (`app/Actions/Certificates/`)
*   `GetUserCertificatesAction`
*   `GenerateCertificatePdfAction`
*   `VerifyPublicCertificateAction`

### ❓ 9. Problems Entity (`app/Actions/Problems/`)
*   `GetProblemsFeedAction`
*   `CreateProblemReportAction`
*   `UpdateProblemAction`
*   `DeleteProblemAction`
*   `ToggleProblemUpvoteAction`
*   `AddProblemCommentAction`

### 👤 10. Profile Entity (`app/Actions/Profile/`)
*   `GetUserProfileDataAction`
*   `UpdateProfileInformationAction`
*   `UploadProfileAvatarAction`
*   `ChangeUserPasswordAction`
*   `DeleteUserAccountAction`
*   `ProcessSubscriptionUpgradeAction` (Stripe integration)
*   `CancelSubscriptionAction`

### 🛠️ 11. Shared / Global Utilities (`app/Actions/Shared/`)
*   `TranslateTextAction` (External API wrapper wrapper)
*   `UploadFileAction` (Generic storage handler)
*   `SendInAppNotificationAction`
