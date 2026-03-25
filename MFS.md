# LinguaFlow — Models, Factories, & Seeders (MFS) Documentation

This document describes the backend data layer implementation for the LinguaFlow project, detailing the Eloquent models, their relationships, and the mock data generation strategy.

---

## 🏗️ 1. Eloquent Models & Relationships

All models are located in `app/Models/` and include comprehensive relationship methods with explanatory comments.

### Core User & Profiles
- **`User`**: The central entity. Includes relationships to almost all other entities (enrollments, bookings, moments, chats, etc.).
- **`UserLanguage`**: Stores proficiency levels for multiple languages per user. (1:N with User)
- **`UserInterest`**: Stores interest tags for discovery and matching. (1:N with User)
- **`Instructor`**: Extends the User model with professional details (category, price, bio). (1:1 with User)
- **`InstructorSlot`**: Manages availability for the booking calendar. (1:N with Instructor)

### Education System
- **`Course`**: Translation courses created by instructors. (1:N with Instructor)
- **`Lesson`**: Sequential modules within a course. (1:N with Course)
- **`LessonMaterial`**: Downloadable PDF/DOC/MP3 files for a lesson. (1:N with Lesson)
- **`QuizQuestion`**: Multiple-choice questions for lessons and assessments. (1:N with Lesson/Course)
- **`Enrollment`**: Links students to courses and tracks their progress. (N:M with User & Course)
- **`LessonCompletion`**: Tracks when a student finishes a specific lesson. (1:N with Enrollment & Lesson)
- **`QuizResult`**: Historical record of every quiz attempted by a student. (1:N with User)

### Social & Communication
- **`Chat`**: Supports both `direct` (1-on-1) and `group` conversations.
- **`ChatMember`**: Pivot model for chat participants with `role` and `unread_count`.
- **`Message`**: Individual messages within chats, supporting language corrections (`original_text`, `corrected_text`).
- **`Moment`**: Social feed posts with categories. (1:N with User)
- **`MomentCorrection`**: Community-driven corrections for moments. (1:N with Moment)
- **`MomentLike` / `MomentComment`**: Social engagement entities.

### Business & Content
- **`Podcast`**: Audio library content. (1:N with Instructor)
- **`Certificate`**: Earned credentials for course completion. (1:N with User & Course)
- **`Subscription`**: VIP plan management (Pro, VIP, Enterprise). (1:1 with User)
- **`StudyDay`**: High-frequency activity tracking for heatmaps and streaks. (1:N with User)
- **`Notification`**: Specialized UUID-based notification system.

---

## 🏭 2. Model Factories

Found in `database/factories/`, these use the `fake()` helper to generate realistic data:
- **`UserFactory`**: Generates avatars (via DiceBear API), bio, gender, and role-specific states (`instructor()`, `admin()`).
- **`CourseFactory`**: Generates titles, CEFR levels, and price strings.
- **`InstructorFactory`**: Generates professional bios and serialized specialty arrays.
- **`MessageFactory`**: Simulates both regular and correction-type messages.
- **`NotificationFactory`**: Generates UUIDs and JSON data payloads.

---

## 🌱 3. Database Seeders

The seeding strategy is orchestrated by `DatabaseSeeder.php` to ensure correct relationship ordering.

### Seeding Order:
1.  **`UserSeeder`**: 
    - Creates a Super Admin (`admin@linguaflow.com`).
    - Creates 3 specific instructors (`Sam`, `Carlos`, `Wei`) to match the React mock data.
    - Creates a Demo Student (`student@linguaflow.com`).
    - Generates 20 random students with languages and interests.
2.  **`InstructorSeeder`**: Creates instructor profiles for all users with the `instructor` role and generates 7 days of availability slots.
3.  **`CourseSeeder`**: Generates courses for each instructor, complete with lessons, materials, and quizzes.
4.  **`SocialSeeder`**: Generates moments with community corrections and establishes various chat rooms with message histories.
5.  **`ProgressSeeder`**: Enrolls students in random courses, marks lessons as completed, generates study activity for heatmaps, and creates VIP subscriptions.

---

## 🚀 How to Reset and Seed

To refresh the entire database and populate it with the new mock data, run:

```bash
php artisan migrate:fresh --seed
```

> **Credential Note:** All seeded users have the password `password123`.
