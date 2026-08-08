# LinguaFlow — Language Education & Translation API Ecosystem

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev)
[![Sanctum](https://img.shields.io/badge/Auth-Laravel%20Sanctum-red?style=for-the-badge)](https://laravel.com/docs/11.x/sanctum)
[![Pest](https://img.shields.io/badge/Testing-Pest-purple?style=for-the-badge)](https://pestphp.com)

---

## 🎯 Product Overview & Business Value

**LinguaFlow** is an enterprise-grade backend API and educational ecosystem built for translators and language learners. It addresses the challenge of specialized translation education by providing CEFR-aligned structured courses, live instructor scheduling, real-time translation feedback, and peer-to-peer community translation corrections ("Moments").

### Who Uses LinguaFlow?
* **Language Students & Translators**: Enroll in CEFR-level courses (Legal, Medical, Literary), take placement quizzes, track study streaks, and post translation work for peer review.
* **Specialized Instructors**: Set calendar availability, manage bookable time slots, conduct courses, and publish educational materials.
* **Platform Administrators**: Oversee subscription plans, course approvals, user roles, and platform analytics.

---

## 🏗️ Backend Architecture & Engineering Highlights

LinguaFlow was designed with strict adherence to **SOLID principles** and a decoupled **4-tier layered architecture** to ensure maintainability, testability, and separation of concerns across a **26-entity domain model**.

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                               LINGUAFLOW ARCHITECTURE                                  │
│                                                                                        │
│  [ HTTP Request ] ──► [ Controller / Form Request ]                                   │
│                              │                                                         │
│                              ▼                                                         │
│                     [ Action Classes ] ──► [ Service Layer ]                           │
│                                                   │                                    │
│                                                   ▼                                    │
│                     [ Query Objects ]  ◄── [ Repository Layer ] (Contracts/Interfaces) │
│                                                   │                                    │
│                                                   ▼                                    │
│                                          [ MySQL Database ]                            │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

### Key Engineering Contributions Implemented:

1. **Service Layer Pattern (`app/Services`)**:
   * Isolated core business logic into 14 dedicated service classes (`BookingService`, `CourseService`, `QuizService`, `SubscriptionService`, `TranslationService`).
   * Ensured thin HTTP controllers focused exclusively on request/response handling.

2. **Repository Pattern with Strict Interfaces (`app/Repositories/Interfaces`)**:
   * Defined PHP Contracts for all data operations, binding 27 Eloquent repositories (`BookingRepository`, `CourseRepository`, `UserRepository`, `MomentRepository`).
   * Decoupled Eloquent ORM from business domain handlers, enabling easy mocking for unit tests.

3. **Single-Action Classes (`app/Actions`)**:
   * Encapsulated single-responsibility tasks under `app/Actions/Auth`, `app/Actions/Community`, and `app/Actions/Learning`.

4. **Dedicated Query Objects (`app/Queries`)**:
   * Separated complex analytical database queries into dedicated query objects (`Analytics`, `Community`, `Instructor`, `Learning`).

5. **Fine-Grained API Security & Policies (`app/Policies`)**:
   * Protected sensitive endpoints using **Laravel Sanctum** token-based stateless authentication.
   * Implemented Policy-based authorization (`BookingPolicy`, `CoursePolicy`, `EnrollmentPolicy`, `LessonPolicy`, `MessagePolicy`, `MomentPolicy`) to prevent unauthorized access and IDOR vulnerabilities.

6. **Relational Database Design (26 Domain Entities)**:
   * Designed a normalized MySQL schema handling multi-entity relationships (`InstructorSlot` -> `Booking`, `Course` -> `Lesson` -> `LessonCompletion`, `Moment` -> `MomentCorrection`).
   * Utilized database transactions and eager loading (`with()`) to prevent N+1 query performance issues.

---

## 💻 Technology Stack

* **Core Framework**: Laravel 11 (PHP 8.3)
* **Frontend Integration**: React 19 / Inertia.js
* **API & Security**: Laravel Sanctum, Form Requests, Laravel Policies
* **Database**: MySQL (22 Migrations, 26 Tables)
* **Testing & Quality**: Pest PHP, PHPUnit

---

## ⚡ Quick Start & Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Moo50Atia/LinguaFlow.git
   cd LinguaFlow
   ```

2. **Install PHP & Node dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment & Run Migrations**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```

4. **Run Automated Test Suite**:
   ```bash
   php artisan test
   # Or using Pest
   ./vendor/bin/pest
   ```
