# LinguaFlow — Component Architecture & Logic

This document describes the functional logic, state management, and data flow for every major React component in the **LinguaFlow** platform.

---

## 🏗️ 1. Core Architecture & Layout

### `App.jsx` (Root Component)
- **Purpose**: Manages global application state, routing (view-based), and theme (Dark/Light mode).
- **Logic**:
  - Uses `useState` for `currentView` (e.g., 'landing', 'login', 'dashboard').
  - Implements a `currentView === 'dashboard'` conditional to wrap the main app in `DashboardLayout`.
  - Manages `isDark` state and persists it to `localStorage`.
  - Handles the `userProfile` global state for the logged-in user.
- **Data Flow**: Passes `setActiveDashboardTab` to navigation and `userProfile` to various sub-views.

### `DashboardLayout.jsx` (SidebarNav & RightSidebar)
- **SidebarNav**: Handles navigation between dashboard tabs (Progress, Learn, Chat, etc.). It includes the theme toggle and logout logic.
- **RightSidebar**: A collapsible/persistent panel used in certain views to show active users, upcoming sessions, or shortcuts. It receives `userProfile` to display the mini-profile header.

---

## 🔐 2. Authentication & Onboarding

### `AuthViews.jsx`
Contains the primary entry points for users:
- **`AuthSelection`**: Simple splash screen for choosing between Login and Signup.
- **`LoginView`**: Email/Password form with validation logic and "show password" toggle.
- **`SignupView`**: Multi-input form including a "Verification Code" flow for email security.
- **`OnboardingWizard`**:
  - **Logic**: A 5-step state-driven wizard.
  - **Step 1 (Profile)**: Basic info (Name, DOB, Gender).
  - **Step 2 (Photo)**: Image upload simulation.
  - **Step 3 (Placement)**: User selects their perceived level or opts for an assessment.
  - **Step 4 (Assessment/Verification)**: Runs a 3-question quiz (`MOCK_QUIZ_QUESTIONS`) and calculates a score to recommend a CEFR level.
  - **Step 5 (Recommendations)**: Shows tailored courses based on the final determined level.
- **Backend Mapping**: Populates the `users`, `user_languages`, and `user_interests` tables.

---

## 📊 3. Primary Dashboard Tabs

### `MyProgress.jsx`
- **Purpose**: A data-heavy dashboard for student performance.
- **Logic**:
  - Uses `MOCK_PROGRESS` to render stats cards (Streak, Lessons Completed, etc.).
  - **Heatmap**: Renders a calendar view where days are colored based on activity (`study_dates` array).
  - **Charts**: Uses `recharts` to visualize learning progress over time.
  - **History**: Lists recently completed lessons with scores.
- **Backend Mapping**: Queries `study_days`, `enrollments`, and `lesson_completions`.

### `MomentsFeed.jsx`
- **Purpose**: A social media-style feed for language learners.
- **Logic**:
  - Renders `MOCK_MOMENTS`. Includes Like/Comment interactions.
  - **Correction Feature**: Displays original and corrected text side-by-side using the `corrections` data array.
  - **Categories**: Users can filter posts by type (e.g., 'Grammar', 'Vocabulary').
- **Backend Mapping**: Maps to `moments`, `moment_corrections`, `moment_likes`, and `moment_comments`.

### `ChatHub.jsx`
- **Purpose**: Real-time messaging center for DMs and groups.
- **Logic**:
  - **Sidebar**: Lists conversations (`MOCK_CHATS`) with unread counts and online status.
  - **Message Pane**: Renders `MOCK_MESSAGES` for the active chat ID.
  - **Translation/Correction**: Special message types that show language improvements within the chat bubble.
- **Backend Mapping**: Maps to `chats`, `chat_members`, and `messages`.

### `ConnectHub.jsx`
- **Purpose**: Peer discovery and matching.
- **Logic**:
  - Filters `MOCK_DISCOVER_USERS` based on user proficiency and native/learning languages.
  - Displays a "Match %" based on how well the other user's native language matches the current user's learning goal.
- **Backend Mapping**: Scans the `users` and `user_languages` tables.

---

## 📚 4. Learning & Education

### `LearnHub.jsx`
- **Purpose**: Central hub for courses.
- **Logic**:
  - Switches between "My Courses" (active enrollments) and course categories.
  - Clicking a course toggles navigation to the `CourseView` or `LessonInterface`.
- **Backend Mapping**: Reads `enrollments` and `courses`.

### `LessonInterface.jsx`
- **Purpose**: The immersive learning environment.
- **Logic**:
  - **Content Area**: Shows lesson title, description, and instructor notes.
  - **Materials**: Lists downloadable assets (`lesson_materials` table).
  - **Quiz System**: When "Start Quiz" is clicked, it shifts to a step-by-step MCQ form.
  - **Completion**: Updates progress when the quiz is finished with a passing score.
- **Backend Mapping**: Interacts with `lessons`, `lesson_materials`, `quiz_questions`, and `lesson_completions`.

---

## 👨‍🏫 5. Instructor & Booking

### `InstructorProfile.jsx`
- **Purpose**: Public/Private profile for teachers.
- **Logic**:
  - Displays aggregate stats (Rating, Student count, Experience).
  - **Reviews Section**: Lists user feedback.
  - **Booking Trigger**: Launches the `BookingModal`.
- **Backend Mapping**: Reads from `instructors` and `reviews`.

### `BookingModal.jsx`
- **Purpose**: Handles the reservation flow.
- **Logic**:
  - **Step 1**: Choose between "Complete Course" or "Specific Session".
  - **Step 2**: A dynamic calendar component. It fetches `availableSlots` for the instructor and disables past or fully-booked dates.
  - **Step 3**: Time slot selection dependent on the chosen date.
  - **Step 4**: Final confirmation and "payment" simulation.
- **Backend Mapping**: Creates entries in `bookings` and updates `instructor_slots`.

---

## 🌐 6. Public & Marketing

### `LandingView.jsx`
- **Purpose**: High-conversion homepage.
- **Logic**:
  - Uses `framer-motion` for complex entry animations and "floating" UI elements.
  - Implements a sticky header that changes style on scroll using `useScroll`.
  - Includes specialized sections for Testimonials, Features, and Specializations.

### `TranslationTypesView.jsx`
- **Purpose**: Educational page about niches (Legal, Medical, etc.).
- **Logic**: Provides detailed cards with specialization descriptions and routes users to the catalog for that specific niche.

---

## 🛠️ 7. Shared Components (`ui.jsx`)
- **`Button`**: Handles variants (primary, secondary, outline, ghost) and loading states.
- **`Input`**: Standardized text inputs with icon support.
- **`Select`**: Custom dropdowns for forms.
- **`Badge`**: Status indicators for levels and types.
- **`Logo`**: SVG-based animated logo component.

---

## 🔄 Dynamic States Summary

| Component | Shared State (Prop/Context) | Local UI State |
|-----------|------------------|----------------|
| `App` | `userProfile`, `isDark` | `currentView` |
| `BookingModal` | `instructor` data | `step`, `selectedDate`, `selectedTime` |
| `LessonInterface` | `lesson` data | `activeTab`, `quizActive`, `currentQuestion` |
| `AuthViews` | `setView` callback | `onboardingStep`, `formData` |
| `ChatHub` | `MOCK_CHATS` | `activeChatId`, `messageText` |
