# NextGen Medics LMS — API Reference

Base URL: `{API_URL}` (e.g. `https://yourdomain.com/api` or `http://localhost/LMS/backend/public`)

All responses are JSON. Authenticated endpoints require:

```
Authorization: Bearer <access_token>
```

---

## Response Format

**Success (single resource):**
```json
{ "success": true, "message": "Success", "data": { ... } }
```

**Success (login — flat format for frontend compatibility):**
```json
{ "token": "...", "refresh_token": "...", "expires_in": 3600, "user": { ... } }
```

**Paginated:**
```json
{ "success": true, "data": [...], "meta": { "total": 100, "page": 1, "per_page": 20, "total_pages": 5 } }
```

**Error:**
```json
{ "success": false, "message": "Error description", "errors": { "field": ["message"] } }
```

---

## Authentication

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/auth/login` | No | Login with email + password |
| GET | `/auth/me` | Yes | Current user profile |
| POST | `/auth/logout` | Yes | Logout (revoke refresh token) |
| POST | `/auth/refresh` | No | Refresh access token |
| POST | `/auth/change-password` | Yes | Change own password |
| POST | `/auth/forgot-password` | No | Request password reset email |
| POST | `/auth/reset-password` | No | Reset password with token |

### POST /auth/login
```json
{ "email": "admin@nextgenmedics.com", "password": "Admin@123" }
```

### POST /auth/refresh
```json
{ "refresh_token": "..." }
```

### POST /auth/change-password
```json
{ "current_password": "...", "new_password": "..." }
```

---

## Public Endpoints (No Auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | API health check |
| GET | `/courses` | List published courses |
| GET | `/courses/categories` | Course categories |
| GET | `/courses/{slug}` | Course detail by slug |
| GET | `/mentors` | Mentor profiles |
| GET | `/testimonials` | Student testimonials |
| GET | `/resources?type=video` | Free resources |
| GET | `/announcements` | Public announcements |
| POST | `/contact` | Contact form submission |

### POST /contact
```json
{ "name": "John", "email": "john@example.com", "phone": "+123", "subject": "Inquiry", "message": "..." }
```

---

## Dashboards

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/admin/dashboard` | Admin | Stats, pending items, recent activity |
| GET | `/teacher/dashboard` | Teacher | Assigned courses, pending reviews |
| GET | `/student/dashboard` | Student | Enrolled courses, assignments, quizzes |

---

## User Management (Admin)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/users/{role}` | List users by role (`student`, `teacher`, `admin`) |
| POST | `/admin/users/{role}` | Create user |
| GET | `/admin/users/id/{id}` | Get user |
| PUT | `/admin/users/{id}` | Update user |
| PATCH | `/admin/users/{id}/suspend` | Suspend user |
| PATCH | `/admin/users/{id}/activate` | Activate user |
| POST | `/admin/users/{id}/reset-password` | Reset password (returns new password) |
| DELETE | `/admin/users/{id}` | Soft delete user |
| GET | `/admin/roles` | List all roles |

### POST /admin/users/student
```json
{
  "username": "jane.doe",
  "email": "jane@example.com",
  "first_name": "Jane",
  "last_name": "Doe",
  "phone": "+1234567890",
  "password": "optional-if-omitted-auto-generated"
}
```

---

## Courses

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/admin/courses` | Admin | All courses (paginated) |
| POST | `/courses` | Admin | Create course |
| PUT | `/courses/{id}` | Admin/Teacher | Update course |
| DELETE | `/courses/{id}` | Admin | Delete course |
| PATCH | `/courses/{id}/archive` | Admin | Archive course |
| PATCH | `/courses/{id}/publish` | Admin | Publish course |
| POST | `/courses/{id}/duplicate` | Admin | Duplicate course |
| POST | `/courses/{id}/assign-teacher` | Admin | Assign teacher |
| POST | `/courses/{id}/enroll` | Admin | Enroll students |
| GET | `/my/courses` | All | My courses (role-based) |
| GET | `/courses/{id}/structure` | All | Full module/chapter/lecture tree |

### POST /courses
```json
{
  "title": "Anatomy Mastery",
  "category_id": 1,
  "teacher_id": 2,
  "subtitle": "Complete anatomy course",
  "short_description": "...",
  "description": "...",
  "duration": "12 weeks",
  "start_date": "2026-01-01",
  "end_date": "2026-03-31",
  "fee": 299.00,
  "level": "beginner",
  "prerequisites": "...",
  "learning_outcomes": "...",
  "max_students": 50,
  "certificate_available": 1,
  "enrollment_status": "open"
}
```

### POST /courses/{id}/enroll
```json
{ "student_ids": [3, 4, 5] }
```

---

## Course Content

Structure: **Course → Module → Chapter → Lecture → Resources**

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| POST | `/content/modules` | Admin/Teacher | Create module |
| POST | `/content/chapters` | Admin/Teacher | Create chapter |
| POST | `/content/lectures` | Admin/Teacher | Create lecture |
| POST | `/content/resources` | Admin/Teacher | Upload resource (multipart) |
| PUT | `/content/{entity}/{id}` | Admin/Teacher | Update entity |
| DELETE | `/content/{entity}/{id}` | Admin/Teacher | Delete entity |

Entities: `modules`, `chapters`, `lectures`, `lecture_resources`

Resource types: `video`, `pdf`, `slides`, `download`, `link`, `reference`

### POST /content/resources (multipart/form-data)
```
lecture_id=1
type=video
title=Lecture 1 Video
file=<binary>
```

---

## Assignments

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/assignments?course_id=1` | All | List course assignments |
| GET | `/assignments/my` | Student | My assignments |
| POST | `/assignments` | Admin/Teacher | Create assignment |
| GET | `/assignments/{id}` | All | Assignment detail |
| POST | `/assignments/{id}/submit` | Student | Submit assignment |
| GET | `/assignments/{id}/submissions` | Admin/Teacher | List submissions |
| POST | `/assignments/grade` | Admin/Teacher | Grade submission |

### POST /assignments
```json
{
  "course_id": 1,
  "title": "Case Study Report",
  "description": "...",
  "instructions": "...",
  "due_date": "2026-07-15 23:59:59",
  "max_marks": 100
}
```

### POST /assignments/grade
```json
{
  "submission_id": 1,
  "marks": 85,
  "remarks": "Well structured report",
  "status": "graded"
}
```

---

## Quizzes

Supported question types: `single_choice`, `multiple_choice`, `true_false`, `fill_blank`, `matching`, `essay`

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/quizzes?course_id=1` | All | List quizzes |
| POST | `/quizzes` | Admin/Teacher | Create quiz |
| GET | `/quizzes/{id}` | All | Quiz with questions |
| POST | `/quizzes/questions` | Admin/Teacher | Add question |
| POST | `/quizzes/{id}/start` | Student | Start attempt |
| POST | `/quizzes/attempts/{attemptId}/submit` | Student | Submit answers |
| GET | `/quizzes/{id}/leaderboard` | All | Quiz leaderboard |

### POST /quizzes
```json
{
  "course_id": 1,
  "title": "Module 1 Quiz",
  "duration_minutes": 30,
  "passing_marks": 50,
  "total_marks": 100,
  "random_questions": true,
  "negative_marking": false,
  "shuffle_questions": true,
  "max_attempts": 2,
  "auto_evaluate": true
}
```

### POST /quizzes/questions
```json
{
  "quiz_id": 1,
  "question_type": "single_choice",
  "question_text": "Which bone is the longest in the body?",
  "marks": 2,
  "options": [
    { "option_text": "Femur", "is_correct": 1 },
    { "option_text": "Tibia", "is_correct": 0 },
    { "option_text": "Humerus", "is_correct": 0 }
  ]
}
```

### POST /quizzes/attempts/{attemptId}/submit
```json
{
  "answers": {
    "1": 5,
    "2": [6, 7],
    "3": "femur"
  }
}
```

---

## Attendance

Statuses: `present`, `absent`, `late`, `leave`

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| POST | `/attendance/sessions` | Admin/Teacher | Create session |
| POST | `/attendance/mark` | Admin/Teacher | Mark attendance |
| GET | `/attendance/course/{courseId}` | All | Course attendance |
| GET | `/attendance/my` | All | My attendance |
| GET | `/attendance/reports?course_id=1` | Admin/Teacher | Reports |

### POST /attendance/mark
```json
{
  "records": [
    { "session_id": 1, "student_id": 3, "status": "present" },
    { "session_id": 1, "student_id": 4, "status": "absent", "remarks": "Unexcused" }
  ]
}
```

---

## Announcements

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/announcements` | Public/Auth | List announcements |
| POST | `/announcements` | Admin/Teacher | Create |
| PUT | `/announcements/{id}` | Admin/Teacher | Update |
| DELETE | `/announcements/{id}` | Admin/Teacher | Delete |

---

## Discussion Forum

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/discussions/course/{courseId}` | All | List threads |
| GET | `/discussions/{id}` | All | Thread with replies |
| POST | `/discussions` | All | Create thread |
| POST | `/discussions/{id}/reply` | All | Reply to thread |
| PATCH | `/discussions/{id}/moderate` | Admin | Moderate thread |

---

## Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | List notifications |
| GET | `/notifications/unread-count` | Unread count |
| PATCH | `/notifications/{id}/read` | Mark as read |
| PATCH | `/notifications/read-all` | Mark all read |

---

## Profile

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT | `/profile` | Update own profile |

```json
{ "first_name": "Jane", "last_name": "Doe", "phone": "+123", "bio": "..." }
```

---

## User Roles & Permissions

| Role | Slug | Access |
|------|------|--------|
| Administrator | `admin` | Full system access |
| Teacher | `teacher` | Assigned courses only |
| Student | `student` | Enrolled courses only |

Students cannot self-register. Admins create all accounts.

---

## File Uploads

Supported formats:
- **Video:** mp4, webm, mov (max 500MB)
- **Documents:** pdf, doc, docx, ppt, pptx (max 50MB)
- **Images:** jpg, jpeg, png, gif, webp
- **Archives:** zip

Files stored in `storage/uploads/` with randomized filenames.

---

## Error Codes

| Code | Meaning |
|------|---------|
| 400 | Bad request |
| 401 | Unauthorized / invalid token |
| 403 | Forbidden / insufficient role |
| 404 | Not found |
| 422 | Validation error |
| 429 | Rate limit exceeded |
| 500 | Server error |

---

## Frontend Environment

```env
# frontend/.env
VITE_API_URL=http://localhost/LMS/backend/public
```

For production:
```env
VITE_API_URL=https://api.nextgenmedics.com
```

Ensure the production frontend URL is added to `config/config.php` → `cors.allowed_origins`.
