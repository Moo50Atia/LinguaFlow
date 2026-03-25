# LinguaFlow — Database Migrations Documentation

> Complete reference for all Laravel migrations in the Translations Graduation Project.  
> **Total Migration Files:** 22  
> **Total Database Tables:** 26  
> **Database:** MySQL / PostgreSQL (via Laravel Schema Builder)

---

## 📌 Migration Execution Order

| # | Migration File | Tables Created/Modified |
|---|----------------|------------------------|
| 1 | `2026_03_25_000001_update_users_table.php` | `users` (ALTER) |
| 2 | `2026_03_25_000002_create_user_languages_table.php` | `user_languages` |
| 3 | `2026_03_25_000003_create_user_interests_table.php` | `user_interests` |
| 4 | `2026_03_25_000004_create_instructors_table.php` | `instructors` |
| 5 | `2026_03_25_000005_create_instructor_slots_table.php` | `instructor_slots` |
| 6 | `2026_03_25_000006_create_courses_table.php` | `courses` |
| 7 | `2026_03_25_000007_create_lessons_table.php` | `lessons` |
| 8 | `2026_03_25_000008_create_lesson_materials_table.php` | `lesson_materials` |
| 9 | `2026_03_25_000009_create_quiz_questions_table.php` | `quiz_questions` |
| 10 | `2026_03_25_000010_create_enrollments_table.php` | `enrollments` |
| 11 | `2026_03_25_000011_create_lesson_completions_table.php` | `lesson_completions` |
| 12 | `2026_03_25_000012_create_quiz_results_table.php` | `quiz_results` |
| 13 | `2026_03_25_000013_create_bookings_table.php` | `bookings` |
| 14 | `2026_03_25_000014_create_reviews_table.php` | `reviews` |
| 15 | `2026_03_25_000015_create_chats_table.php` | `chats`, `chat_members` |
| 16 | `2026_03_25_000016_create_messages_table.php` | `messages` |
| 17 | `2026_03_25_000017_create_moments_tables.php` | `moments`, `moment_corrections`, `moment_likes`, `moment_comments` |
| 18 | `2026_03_25_000018_create_podcasts_table.php` | `podcasts` |
| 19 | `2026_03_25_000019_create_certificates_table.php` | `certificates` |
| 20 | `2026_03_25_000020_create_subscriptions_table.php` | `subscriptions` |
| 21 | `2026_03_25_000021_create_study_days_table.php` | `study_days` |
| 22 | `2026_03_25_000022_create_notifications_table.php` | `notifications` |

---

## 📐 Entity Relationship Diagram

```
                        ┌─────────────┐
                        │    users    │
                        └──────┬──────┘
           ┌───────┬──────┬────┼────┬──────┬──────┬──────┐
           ▼       ▼      ▼    ▼    ▼      ▼      ▼      ▼
     user_langs  user_   instr  enr  book  cert   subs   study
               interests uctors olls ings  ific.  crip.  days
                    │                 │
                    ▼                 ▼
              instr_slots        reviews
                    │
           ┌────────┼────────┐
           ▼        ▼        ▼
        courses   chats   moments
           │        │        │
           ▼        ▼        ├── moment_corrections
        lessons  chat_mem   ├── moment_likes
           │        │       └── moment_comments
      ┌────┼────┐   ▼
      ▼    ▼    ▼  messages
   mater  quiz  lesson_
   ials  quest  complet.
          ions
           │
           ▼
      quiz_results
```

---

## 📝 Detailed Table Descriptions

---

### 1. `users` (ALTER — extends Laravel default)

**Purpose:** Extends the default Laravel `users` table with LinguaFlow-specific profile fields.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `avatar` | string | ✅ | NULL | Profile picture URL or path |
| `bio` | text | ✅ | NULL | User biography text |
| `gender` | enum(male, female) | ✅ | NULL | User gender |
| `date_of_birth` | date | ✅ | NULL | Date of birth |
| `location` | string | ✅ | NULL | City/Country (e.g. "New York, USA") |
| `native_language` | string | ✅ | NULL | Primary native language |
| `cefr_level` | string(10) | ❌ | `A1.1` | Current CEFR proficiency level |
| `role` | enum(student, instructor, admin) | ❌ | `student` | User role in the platform |
| `is_vip` | boolean | ❌ | `false` | Whether user has active VIP subscription |
| `is_online` | boolean | ❌ | `false` | Real-time online presence status |
| `google_id` | string | ✅ | NULL | Google OAuth ID (unique) |
| `last_seen_at` | timestamp | ✅ | NULL | Last activity timestamp |

**Relationships:** One-to-Many → `user_languages`, `user_interests`, `enrollments`, `bookings`, `reviews`, `moments`, `certificates`, `subscriptions`, `study_days`

---

### 2. `user_languages`

**Purpose:** Tracks languages each user speaks or is learning, with CEFR proficiency level.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Language owner |
| `language` | string | ❌ | Language name (e.g. "Spanish") |
| `flag` | string | ✅ | Emoji flag (e.g. "🇪🇸") |
| `level` | string(10) | ❌ | CEFR level or "Native" |
| `is_native` | boolean | ❌ | Whether this is a native language |

**Unique Constraint:** `(user_id, language)` — one entry per language per user  
**React Source:** `userProfile.learningLanguages`, `MOCK_DISCOVER_USERS.native/learning`

---

### 3. `user_interests`

**Purpose:** Stores user interest tags for profile display and Connect Hub matching.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Interest owner |
| `interest` | string | ❌ | Interest tag (e.g. "Travel", "Music") |

**Unique Constraint:** `(user_id, interest)`  
**React Source:** `userProfile.interests`

---

### 4. `instructors`

**Purpose:** Stores instructor-specific profile data, extending user accounts that have the `instructor` role.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users (unique) | ❌ | Linked user account |
| `category` | enum | ❌ | Medical, Legal, Business, Literary, Technical, Interpretation |
| `type` | enum(Paid, Free) | ❌ | Pricing model |
| `price_per_hour` | decimal(8,2) | ✅ | Hourly rate (NULL = free) |
| `bio` | text | ✅ | Professional biography |
| `specialties` | JSON | ✅ | Array of specialty strings |
| `schedule` | string | ✅ | Availability description |
| `years_experience` | unsigned int | ❌ | Teaching experience |
| `total_students` | unsigned int | ❌ | Aggregate student count |
| `rating` | decimal(3,2) | ❌ | Average rating (0.00–5.00) |
| `total_reviews` | unsigned int | ❌ | Total review count |

**React Source:** `MOCK_INSTRUCTORS` — Sam Jenkins, Carlos Mateo, Wei Lin

---

### 5. `instructor_slots`

**Purpose:** Calendar availability for instructor booking. Each row = one bookable time slot.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `instructor_id` | FK → instructors | ❌ | Slot owner |
| `date` | date | ❌ | Available date |
| `time` | time | ❌ | Available time (e.g. 09:00) |
| `is_booked` | boolean | ❌ | Whether slot is already taken |

**Unique Constraint:** `(instructor_id, date, time)`  
**React Source:** `MOCK_INSTRUCTORS[].availableSlots`

---

### 6. `courses`

**Purpose:** Translation courses offered by instructors.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `instructor_id` | FK → instructors | ❌ | Course creator |
| `title` | string | ❌ | Course title |
| `level` | string(10) | ❌ | CEFR level (A1, A2, B1, etc.) |
| `language` | string | ❌ | Target language |
| `language_flag` | string | ✅ | Emoji flag |
| `total_lessons` | unsigned int | ❌ | Number of lessons |
| `price` | string | ✅ | Display price (e.g. "$120" or "Free") |
| `image` | string | ✅ | Cover image path |
| `description` | text | ✅ | Course description |
| `category` | enum | ❌ | Translation specialization |
| `is_published` | boolean | ❌ | Visibility flag |
| `enrolled_count` | unsigned int | ❌ | Total enrolled students |

**React Source:** `MOCK_COURSES`, `MOCK_INSTRUCTORS[].coursesOffered`

---

### 7. `lessons`

**Purpose:** Individual lessons within a course, ordered sequentially. Lessons unlock progressively.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `course_id` | FK → courses | ❌ | Parent course |
| `order` | unsigned int | ❌ | Sequential position (1, 2, 3...) |
| `title` | string | ❌ | Lesson title |
| `duration` | string | ✅ | Duration display (e.g. "45 min") |
| `description` | text | ✅ | Lesson description |
| `notes` | text | ✅ | Instructor notes & key phrases |
| `image` | string | ✅ | Lesson thumbnail |
| `has_quiz` | boolean | ❌ | Whether lesson has a quiz |

**Index:** `(course_id, order)` for fast sequential access  
**React Source:** `MOCK_COURSES[].lessons` — status is determined by enrollment progress, not stored here

---

### 8. `lesson_materials`

**Purpose:** Downloadable resources attached to lessons (PDFs, audio files, worksheets).

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `lesson_id` | FK → lessons | ❌ | Parent lesson |
| `name` | string | ❌ | Display name |
| `type` | string | ❌ | File type (PDF, DOC, MP3) |
| `file_path` | string | ❌ | Storage path |
| `size` | string | ✅ | Display size (e.g. "2.1 MB") |

**React Source:** `MOCK_COURSES[].lessons[].materials`, `MOCK_LESSONS[].materials`

---

### 9. `quiz_questions`

**Purpose:** Multiple-choice questions for lesson quizzes, final course assessments, and onboarding placement tests.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `lesson_id` | FK → lessons | ✅ | For lesson quizzes |
| `course_id` | FK → courses | ✅ | For final assessments |
| `type` | enum | ❌ | `lesson_quiz`, `final_assessment`, `onboarding` |
| `question` | text | ❌ | Question text |
| `options` | JSON | ❌ | Array of answer options |
| `correct_answer` | tinyint unsigned | ❌ | 0-based index of correct option |
| `order` | unsigned int | ❌ | Display order |

**React Source:** `MOCK_COURSES[].lessons[].quiz`, `MOCK_COURSES[].finalAssessment.quiz`, `MOCK_QUIZ_QUESTIONS`

---

### 10. `enrollments`

**Purpose:** Tracks which user is enrolled in which course with real-time progress.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Enrolled student |
| `course_id` | FK → courses | ❌ | Enrolled course |
| `current_lesson` | unsigned int | ❌ | Current lesson order position |
| `completed_lessons` | unsigned int | ❌ | Count of completed lessons |
| `progress` | decimal(5,2) | ❌ | Progress percentage (0.00–100.00) |
| `status` | enum | ❌ | `active`, `completed`, `dropped` |

**Unique Constraint:** `(user_id, course_id)` — one enrollment per course per user  
**React Source:** `MOCK_COURSES[].completedLessons`, `MOCK_COURSES[].progress`

---

### 11. `lesson_completions`

**Purpose:** Records when a user completes a specific lesson, with their quiz score.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `enrollment_id` | FK → enrollments | ❌ | Parent enrollment |
| `lesson_id` | FK → lessons | ❌ | Completed lesson |
| `score` | tinyint unsigned | ✅ | Quiz score 0–100 |
| `passed` | boolean | ❌ | Whether the student passed |
| `completed_at` | timestamp | ❌ | Completion time |

**Unique Constraint:** `(enrollment_id, lesson_id)`  
**React Source:** `MOCK_PROGRESS.completionHistory`

---

### 12. `quiz_results`

**Purpose:** Historical record of all quiz attempts for the progress dashboard.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Quiz taker |
| `lesson_id` | FK → lessons | ✅ | Source lesson (if lesson quiz) |
| `course_id` | FK → courses | ✅ | Source course (if final assessment) |
| `quiz_title` | string | ❌ | Display title |
| `course_name` | string | ✅ | Course name for display |
| `score` | tinyint unsigned | ❌ | Percentage score |
| `total_questions` | smallint unsigned | ❌ | Number of questions |
| `passed` | boolean | ❌ | Pass/fail flag |
| `type` | enum | ❌ | `lesson_quiz`, `final_assessment`, `onboarding` |

**React Source:** `MOCK_PROGRESS.quizResults`

---

### 13. `bookings`

**Purpose:** Session booking records between students and instructors.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Booking student |
| `instructor_id` | FK → instructors | ❌ | Booked instructor |
| `instructor_slot_id` | FK → instructor_slots | ✅ | Reserved time slot |
| `booking_type` | enum | ❌ | `complete-course` or `specific-session` |
| `course_style` | enum(private, group) | ✅ | Only for complete-course bookings |
| `date` | date | ❌ | Session date |
| `time` | time | ❌ | Session time |
| `price` | decimal(8,2) | ✅ | Session price |
| `status` | enum | ❌ | `pending` → `confirmed` → `completed` / `cancelled` |
| `notes` | text | ✅ | Additional notes |

**React Source:** `BookingModal.jsx` — step-by-step booking flow

---

### 14. `reviews`

**Purpose:** Student reviews and star ratings for instructors.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Reviewer |
| `instructor_id` | FK → instructors | ❌ | Reviewed instructor |
| `rating` | tinyint unsigned | ❌ | 1–5 star rating |
| `comment` | text | ✅ | Review text |

**Unique Constraint:** `(user_id, instructor_id)` — one review per instructor per user  
**React Source:** `MOCK_REVIEWS`

---

### 15. `chats` + `chat_members`

**Purpose:** Chat conversations (direct 1-on-1 or group) with member management.

#### `chats`
| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `type` | enum(direct, group) | ❌ | Conversation type |
| `name` | string | ✅ | Group name (NULL for direct chats) |
| `avatar` | string | ✅ | Group avatar image |

#### `chat_members`
| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `chat_id` | FK → chats | ❌ | Parent chat |
| `user_id` | FK → users | ❌ | Member |
| `role` | enum(admin, member) | ❌ | Member role |
| `unread_count` | unsigned int | ❌ | Unread message count |

**React Source:** `MOCK_CHATS` — includes direct and group conversations with member lists

---

### 16. `messages`

**Purpose:** Individual messages in chat conversations, with inline language correction support.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `chat_id` | FK → chats | ❌ | Parent conversation |
| `sender_id` | FK → users | ❌ | Message sender |
| `text` | text | ✅ | Message content |
| `is_correction` | boolean | ❌ | Whether this is a correction message |
| `original_text` | text | ✅ | Original text (for corrections) |
| `corrected_text` | text | ✅ | Corrected version (for corrections) |

**Index:** `(chat_id, created_at)` for efficient message loading  
**React Source:** `MOCK_MESSAGES` — including correction messages with `isCorrection: true`

---

### 17. `moments` + `moment_corrections` + `moment_likes` + `moment_comments`

**Purpose:** Social feed ("Moments") with community language corrections and engagement.

#### `moments`
| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Post author |
| `content` | text | ❌ | Post text |
| `category` | enum | ❌ | General, Grammar, Vocabulary, Culture, Pronunciation, Questions, Advice, Daily Life |
| `images` | JSON | ✅ | Array of image URLs |
| `likes_count` | unsigned int | ❌ | Cached like count |
| `comments_count` | unsigned int | ❌ | Cached comment count |

#### `moment_corrections`
| Column | Type | Description |
|--------|------|-------------|
| `moment_id` | FK → moments | Corrected post |
| `user_id` | FK → users | Corrector |
| `original_text` | text | Text that was wrong |
| `corrected_text` | text | Corrected version |

#### `moment_likes`
Unique constraint on `(moment_id, user_id)` — one like per user per post.

#### `moment_comments`
Standard comment with `body` text.

**React Source:** `MOCK_MOMENTS`, `POST_CATEGORIES`

---

### 18. `podcasts`

**Purpose:** Audio content library for language learning.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `title` | string | ❌ | Episode title |
| `description` | text | ✅ | Episode description |
| `audio_url` | string | ❌ | Audio file URL/path |
| `cover_image` | string | ✅ | Episode cover art |
| `duration` | string | ✅ | Duration display |
| `category` | string | ✅ | Topic category |
| `language` | string | ❌ | Content language |
| `level` | string(10) | ✅ | Target CEFR level |
| `instructor_id` | FK → instructors | ✅ | Associated instructor |
| `plays_count` | unsigned int | ❌ | Play counter |
| `is_premium` | boolean | ❌ | VIP-only flag |

**React Source:** `PodcastTab.jsx`

---

### 19. `certificates`

**Purpose:** Credentials earned by students upon completing courses or assessments.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Certificate holder |
| `course_id` | FK → courses | ✅ | Source course |
| `title` | string | ❌ | Certificate title |
| `certificate_number` | string (unique) | ❌ | Unique credential ID |
| `level` | string(10) | ✅ | CEFR level achieved |
| `category` | string | ✅ | Translation specialization |
| `file_path` | string | ✅ | Generated PDF path |
| `issued_at` | timestamp | ❌ | Issue date |

**React Source:** `CertificatesTab.jsx`

---

### 20. `subscriptions`

**Purpose:** VIP subscription management with Stripe integration.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Subscriber |
| `plan` | enum | ❌ | `pro_learner`, `vip_elite`, `enterprise` |
| `price` | decimal(8,2) | ❌ | Subscription price |
| `billing_cycle` | enum | ❌ | `monthly` or `yearly` |
| `status` | enum | ❌ | `active`, `cancelled`, `expired`, `trial` |
| `stripe_subscription_id` | string | ✅ | Stripe subscription reference |
| `stripe_customer_id` | string | ✅ | Stripe customer reference |
| `trial_ends_at` | timestamp | ✅ | Trial expiration |
| `starts_at` | timestamp | ❌ | Subscription start date |
| `ends_at` | timestamp | ✅ | Subscription end date |
| `cancelled_at` | timestamp | ✅ | Cancellation timestamp |

**React Source:** `VIPUpgradeModal.jsx` — Pro Learner ($19.99), VIP Elite ($49.99), Enterprise (Custom)

---

### 21. `study_days`

**Purpose:** Tracks daily study activity for streak calculations and progress heatmap visualization.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint (PK) | ❌ | Auto-increment |
| `user_id` | FK → users | ❌ | Studying user |
| `date` | date | ❌ | Study date |
| `minutes_studied` | smallint unsigned | ❌ | Total minutes studied that day |

**Unique Constraint:** `(user_id, date)`  
**React Source:** `MOCK_PROGRESS.studyDates`, `MOCK_PROGRESS.currentStreak`, `MOCK_PROGRESS.longestStreak`

---

### 22. `notifications`

**Purpose:** In-app notifications for all user events (bookings, corrections, achievements, messages).

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | UUID (PK) | ❌ | Universally unique ID |
| `user_id` | FK → users | ❌ | Notification recipient |
| `type` | string | ❌ | Notification type identifier |
| `title` | string | ❌ | Display title |
| `body` | text | ✅ | Notification body text |
| `icon` | string | ✅ | Icon identifier |
| `data` | JSON | ✅ | Additional structured data |
| `read_at` | timestamp | ✅ | Read timestamp (NULL = unread) |

**Index:** `(user_id, read_at)` for efficient unread queries

---

## 🔑 Key Constraints & Indexes Summary

| Constraint Type | Tables |
|----------------|--------|
| **Unique (user, resource)** | `user_languages`, `user_interests`, `enrollments`, `reviews`, `moment_likes`, `study_days`, `chat_members` |
| **Unique (composite)** | `instructor_slots (instructor, date, time)` |
| **Cascade Delete** | All FK relationships cascade on user/parent delete |
| **JSON Columns** | `instructors.specialties`, `quiz_questions.options`, `moments.images`, `notifications.data` |
| **Composite Indexes** | `lessons (course_id, order)`, `messages (chat_id, created_at)`, `notifications (user_id, read_at)` |

---

## 🚀 How to Run

```bash
# Run all migrations
php artisan migrate

# Run with seeding
php artisan migrate --seed

# Rollback all
php artisan migrate:rollback

# Fresh install (drop all + re-migrate)
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

---

> **Note:** These migrations are designed to work with the default Laravel `users`, `password_reset_tokens`, `sessions`, `cache`, and `jobs` tables already present in the Laravel installation.
