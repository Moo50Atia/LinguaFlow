# LinguaFlow — Translation Company Platform V2

## 📋 Project Overview

**LinguaFlow** is a full-featured, modern web platform for a professional **Translation & Language Learning Company**. It enables users to learn translation specializations (Legal, Medical, Business, Literary, Technical, Interpretation), take structured courses with quizzes, connect with certified instructors, and earn credentials — all within a premium, animated UI.

> **Project Type:** Graduation Project — Full-Stack Web Application  
> **Frontend:** React 19 + Vite + Tailwind CSS + Framer Motion  
> **Backend Target:** Laravel (PHP) — REST API  
> **Architecture:** SPA Frontend → Laravel API Backend

---

## 🎯 Business Objective

Build a **one-stop translation education ecosystem** where:
- **Students** can enroll in translation courses, take quizzes, earn certificates, discover peers, chat, and book sessions with instructors.
- **Instructors** can create courses, manage availability, receive bookings, and get rated/reviewed.
- **Admins** can manage the platform, users, content, and subscriptions.

---

## 🏗️ Core Modules & Features

### 1. 🔐 Authentication & Onboarding
| Feature | Description |
|---------|-------------|
| **Auth Selection** | Login/Signup chooser with Google OAuth option |
| **Login** | Email + password with visibility toggle, "Forgot password" link |
| **Signup** | Email + verification code + password creation |
| **Onboarding Wizard** | 5-step flow: Profile Setup → Photo Upload → Level Selection → Assessment Quiz → Recommendations |
| **Level Assessment** | CEFR-based quiz (A1.1 → C2) to determine user proficiency |
| **Level Verification** | Score-based confirmation or downgrade of self-reported level |

### 2. 📊 Dashboard (Authenticated Area)
The main authenticated area uses a sidebar navigation layout with tabs:

| Tab | Component | Description |
|-----|-----------|-------------|
| **Progress** | `MyProgress` | Study streaks, heatmap, completion history, quiz results, overall stats |
| **Moments** | `MomentsFeed` | Social feed with posts, corrections, categories (Grammar, Vocabulary, Culture, etc.) |
| **Chat** | `ChatHub` | Direct messages + Group chats with real-time corrections |
| **Connect** | `ConnectHub` | Discover users by language, level, location with match percentage |
| **Learn** | `LearnHub` | Course listing, lesson interface, quizzes, assessments, materials |
| **Profile** | `MyProfileScreen` | View/edit profile, bio, languages, interests, avatar |
| **Call Dashboard** | `CallDashboard` | Video/voice call management |
| **Podcasts** | `PodcastTab` | Audio content library for language learning |
| **Problems** | `ProblemsTab` | Practice problems and exercises |
| **Certificates** | `CertificatesTab` | Earned credentials and downloadable certificates |
| **VIP Upgrade** | `VIPUpgradeModal` | Subscription plans (Pro Learner, VIP Elite, Enterprise) |

### 3. 📚 Course & Learning System
| Feature | Description |
|---------|-------------|
| **Course Structure** | Courses have ordered lessons with sequential unlocking |
| **Lesson Content** | Title, duration, description, materials (PDF/DOC/MP3), notes |
| **Lesson Status** | `completed` (with score) → `current` → `locked` |
| **Quizzes** | Multiple-choice quizzes per lesson with scoring |
| **Final Assessment** | End-of-course comprehensive exam with passing score threshold |
| **Course Progress** | Tracks completedLessons, currentLesson, progress percentage |
| **Materials** | Downloadable resources (PDF, DOC, MP3) with name, type, size |

### 4. 👨‍🏫 Instructor System
| Feature | Description |
|---------|-------------|
| **Instructor Profile** | Full profile with bio, specialties, languages, experience, total students |
| **Category** | Specialization area: Medical, Legal, Business |
| **Type** | Paid ($X/hr) or Free |
| **Rating System** | Overall rating + distribution (5-star to 1-star counts) |
| **Reviews** | Text reviews with user info, rating, date |
| **Available Slots** | Date → time-slot mapping for calendar booking |
| **Courses Offered** | List of courses with level, lessons count, enrollment, price |

### 5. 📅 Booking System
| Feature | Description |
|---------|-------------|
| **Booking Types** | Complete Course (Private 1-on-1 or Group) OR Specific Session |
| **Calendar Picker** | Month-based calendar with available dates highlighted |
| **Time Slot Selection** | Available time slots for the selected date |
| **Booking Confirmation** | Summary of instructor, type, date, time, and total price |

### 6. 💬 Chat & Messaging
| Feature | Description |
|---------|-------------|
| **Direct Messages** | 1-on-1 chat with online status and language info |
| **Group Chats** | Multi-user groups with admin roles and member management |
| **Corrections** | Language correction feature in messages (original → corrected) |
| **Online Status** | Real-time presence indicators |

### 7. 🤝 Connect Hub (User Discovery)
| Feature | Description |
|---------|-------------|
| **User Profiles** | Avatar, native language, learning language, level, gender, bio, location |
| **Match System** | Percentage-based matching based on language exchange compatibility |
| **Online Filter** | Filter by online status |
| **User Cards** | Rich card display with all user info |

### 8. 📱 Social Moments Feed
| Feature | Description |
|---------|-------------|
| **Posts** | Text + image content with categories (Vocabulary, Grammar, General, etc.) |
| **Corrections** | Community-driven language corrections on posts |
| **Engagement** | Likes, comments, share timestamp |
| **Categories** | General, Grammar, Vocabulary, Culture, Pronunciation, Questions, Advice, Daily Life |

### 9. 🏆 Progress & Gamification
| Feature | Description |
|---------|-------------|
| **Current Level** | CEFR level (A1.1 → C2) |
| **Study Streaks** | Current streak, longest streak, total study days |
| **Study Heatmap** | Visual calendar with study date tracking |
| **Completion History** | Lesson-by-lesson completion log with scores |
| **Quiz Results** | All quiz attempts with scores, pass/fail status |

### 10. 💎 Subscription / VIP System
| Plan | Price | Key Features |
|------|-------|--------------|
| **Pro Learner** | $19.99/month | Unlimited Podcasts, Priority Booking, Ad-free, Analytics |
| **VIP Elite** | $49.99/month | All Pro + Weekly 1-on-1, VIP Webinars, Personal Learning Path, 24/7 Support |
| **Enterprise** | Custom | Bulk Licensing, LMS Integration, Custom Content, Admin Dashboard |

---

## 🌐 Public Pages (Unauthenticated)

### Landing Page
- Hero section with animated gradients and floating elements
- Trust bar (Microsoft, Google, Translation Guild, etc.)
- Feature showcase (Guided Learning, Podcasts, Expert Mentors, AI Practice)
- How-it-works 3-step methodology
- Featured courses grid with ratings and enrollment counts
- Translation specialization grid (Legal, Medical, Business, Literary, Technical, Interpretation)
- Testimonials section
- CTA section
- Full footer with social links and contact info

### Course Catalog
- Browsable catalog of all available courses
- Filter and search capabilities

### Translation Types
- Detailed view of 6 translation specializations with descriptions

---

## 🔤 CEFR Language Levels Used
| Level | Label |
|-------|-------|
| A1.1 | Beginner |
| A1.2 | Upper Beginner |
| A2 | Pre-Intermediate |
| B1.1 | Intermediate |
| B1.2 | Upper Intermediate |
| B2 | Advanced |
| C1 | Proficient |
| C2 | Mastery |

---

## 🛠 Technology Stack

### Frontend (Current — React SPA)
| Technology | Purpose |
|-----------|---------|
| **React 19** | UI Component Library |
| **Vite 8** | Build tool & dev server |
| **Tailwind CSS 3** | Utility-first CSS styling |
| **Framer Motion** | Animation & transitions |
| **Lucide React** | Icon library |
| **Recharts** | Charts & data visualization |

### Backend (Target — Laravel)
| Technology | Purpose |
|-----------|---------|
| **Laravel 10+** | PHP Backend Framework |
| **MySQL / PostgreSQL** | Relational Database |
| **Laravel Sanctum** | API Authentication |
| **Laravel Broadcasting** | Real-time Chat (Pusher/WebSockets) |
| **Laravel Storage** | File uploads (avatars, materials) |
| **Laravel Notifications** | Email, push notifications |
| **Stripe / Payment Gateway** | Subscription billing |

---

## 📊 Data Entities Summary

| Entity | Key Fields |
|--------|-----------|
| **User** | name, email, password, avatar, bio, gender, dob, location, native_language, role, cefr_level, is_vip |
| **UserLanguage** | user_id, language, level (CEFR), is_native |
| **Instructor** | user_id, category, type, price, bio, specialties, schedule, years_experience |
| **Course** | title, level, language, instructor_id, total_lessons, price, image |
| **Lesson** | course_id, order, title, duration, description, has_quiz |
| **LessonMaterial** | lesson_id, name, type, file_path, size |
| **QuizQuestion** | lesson_id/assessment_id, question, options (JSON), correct_answer |
| **Enrollment** | user_id, course_id, current_lesson, progress, status |
| **LessonCompletion** | enrollment_id, lesson_id, score, completed_at |
| **Booking** | user_id, instructor_id, type, course_style, date, time, status, price |
| **InstructorSlot** | instructor_id, date, time, is_booked |
| **Review** | user_id, instructor_id, rating, comment |
| **Chat** | type (direct/group), name, avatar |
| **ChatMember** | chat_id, user_id, role (admin/member) |
| **Message** | chat_id, sender_id, text, is_correction, original, corrected |
| **Moment** | user_id, content, category, images (JSON) |
| **MomentCorrection** | moment_id, user_id, original, corrected |
| **MomentLike** | moment_id, user_id |
| **Certificate** | user_id, course_id, title, issued_at |
| **Subscription** | user_id, plan, price, status, starts_at, ends_at |
| **StudyDay** | user_id, date |
| **UserInterest** | user_id, interest |
| **Podcast** | title, description, audio_url, duration, category |

---

## 👤 User Roles
| Role | Permissions |
|------|------------|
| **Student** | Browse, enroll, learn, chat, post moments, book sessions |
| **Instructor** | Create courses, manage lessons, set availability, receive bookings |
| **Admin** | Full platform management, user management, content moderation |

---

## 📐 Architecture Diagram

```
┌─────────────────────────────────────────────────┐
│                  React SPA (Frontend)            │
│  Landing │ Auth │ Dashboard │ Learn │ Chat │ ... │
└──────────────────────┬──────────────────────────┘
                       │ REST API (JSON)
                       ▼
┌─────────────────────────────────────────────────┐
│              Laravel Backend (API)               │
│  Controllers → Services → Models → Database      │
│  Sanctum Auth │ Broadcasting │ Storage │ Queue    │
└──────────────────────┬──────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────┐
│          MySQL / PostgreSQL Database             │
│  users │ courses │ lessons │ chats │ bookings    │
│  enrollments │ reviews │ moments │ certificates  │
└─────────────────────────────────────────────────┘
```

---

> **Author:** Graduation Project Team  
> **Date:** March 2026  
> **Status:** Frontend Complete — Backend In Development
