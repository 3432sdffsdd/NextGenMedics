-- ============================================================
-- NextGen Medics LMS — LIVE DATABASE UPDATE (SAFE)
-- ============================================================
-- Run this on your EXISTING live database (phpMyAdmin or mysql).
--
-- WHAT THIS DOES:
--   ✓ Creates all NEW tables added in the last few days
--   ✓ Adds NEW columns to existing tables
--   ✓ Seeds badges & roles (if missing)
--   ✓ Does NOT delete, truncate, or modify any data
--
-- WHAT THIS DOES NOT TOUCH:
--   ✗ users table (your students & teachers stay exactly as they are)
--   ✗ courses, enrollments, or any existing content
--
-- BEFORE RUNNING:
--   1. BACK UP your database (Export in phpMyAdmin)
--   2. Replace YOUR_DB_NAME below with your actual database name
-- ============================================================

-- >>> CHANGE THIS to your live database name <<<
USE YOUR_DB_NAME;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Track applied migrations (optional, for future updates)
CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- ROLES (only if missing — no users inserted)
-- ============================================================
INSERT IGNORE INTO roles (id, name, slug, description) VALUES
(1, 'Administrator', 'admin', 'Full system access'),
(2, 'Teacher', 'teacher', 'Course instructor access'),
(3, 'Student', 'student', 'Student access');

-- ============================================================
-- MIGRATION 001: Weekly class timetable
-- ============================================================
CREATE TABLE IF NOT EXISTS course_class_schedule (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Sun … 6=Sat',
    start_time TIME NOT NULL,
    duration_minutes INT UNSIGNED DEFAULT 60,
    title VARCHAR(255) NULL,
    meeting_url VARCHAR(500) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_schedule_course (course_id),
    INDEX idx_schedule_day (day_of_week)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS class_reminder_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT UNSIGNED NOT NULL,
    occurrence_date DATE NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    channel ENUM('whatsapp', 'in_app', 'email') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_class_reminder (schedule_id, occurrence_date, user_id, role, channel),
    FOREIGN KEY (schedule_id) REFERENCES course_class_schedule(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('001_course_class_schedule.sql');

-- ============================================================
-- MIGRATION 002: Batches + date-based schedule
-- ============================================================
CREATE TABLE IF NOT EXISTS batches (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id   INT UNSIGNED NOT NULL,
    name        VARCHAR(150) NOT NULL,
    code        VARCHAR(50)  NULL,
    start_date  DATE NULL,
    end_date    DATE NULL,
    is_active   TINYINT(1) DEFAULT 1,
    created_by  INT UNSIGNED NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)   ON DELETE SET NULL,
    INDEX idx_batches_course (course_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS batch_students (
    batch_id    INT UNSIGNED NOT NULL,
    student_id  INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (batch_id, student_id),
    FOREIGN KEY (batch_id)   REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS class_schedule (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id        INT UNSIGNED NOT NULL,
    batch_id         INT UNSIGNED NULL,
    teacher_id       INT UNSIGNED NULL,
    subject          VARCHAR(150) NULL,
    lecture_title    VARCHAR(255) NOT NULL,
    lecture_number   INT UNSIGNED NULL,
    topic_covered    VARCHAR(500) NULL,
    description      TEXT NULL,
    class_date       DATE NOT NULL,
    start_time       TIME NOT NULL,
    end_time         TIME NOT NULL,
    duration_minutes INT UNSIGNED NULL,
    meeting_link     VARCHAR(500) NULL,
    recording_link   VARCHAR(500) NULL,
    attachment_path  VARCHAR(500) NULL,
    attachment_name  VARCHAR(255) NULL,
    remarks          VARCHAR(500) NULL,
    status           ENUM('upcoming','live','completed','cancelled','postponed','rescheduled') DEFAULT 'upcoming',
    is_status_locked TINYINT(1) DEFAULT 0,
    rescheduled_from_id INT UNSIGNED NULL,
    created_by       INT UNSIGNED NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id)   REFERENCES batches(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id)   ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)   ON DELETE SET NULL,
    INDEX idx_cs_course (course_id),
    INDEX idx_cs_date (class_date),
    INDEX idx_cs_teacher (teacher_id),
    INDEX idx_cs_batch (batch_id),
    INDEX idx_cs_status (status)
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('002_schedule_and_batches.sql');

-- ============================================================
-- MIGRATION 003: Study tools (flashcards, MCQs, streaks, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS ai_generation_jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    requested_by INT UNSIGNED NULL,
    status ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
    current_step ENUM('extract','summary','notes','flashcards','mcqs','done') NOT NULL DEFAULT 'extract',
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    options JSON NULL,
    source_text LONGTEXT NULL,
    source_chars INT UNSIGNED NOT NULL DEFAULT 0,
    flashcard_target SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    flashcard_done SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    mcq_target SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    mcq_done SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ai_jobs_status (status),
    INDEX idx_ai_jobs_lecture (lecture_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_lecture_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    summary LONGTEXT NULL,
    revision_notes LONGTEXT NULL,
    high_yield_points JSON NULL,
    clinical_pearls JSON NULL,
    common_mistakes JSON NULL,
    key_definitions JSON NULL,
    memory_tricks JSON NULL,
    key_takeaways JSON NULL,
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    generated_by INT UNSIGNED NULL,
    approved_by INT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ai_content_lecture (lecture_id),
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ai_content_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS flashcards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    topic VARCHAR(255) NULL,
    difficulty ENUM('easy','moderate','difficult') NOT NULL DEFAULT 'moderate',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    status ENUM('draft','approved') NOT NULL DEFAULT 'draft',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_flashcards_lecture (lecture_id),
    INDEX idx_flashcards_course (course_id),
    INDEX idx_flashcards_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_flashcard_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    flashcard_id INT UNSIGNED NOT NULL,
    status ENUM('new','learning','mastered') NOT NULL DEFAULT 'new',
    is_favorite TINYINT(1) NOT NULL DEFAULT 0,
    is_difficult TINYINT(1) NOT NULL DEFAULT 0,
    review_count INT UNSIGNED NOT NULL DEFAULT 0,
    last_reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_flashcard_progress (student_id, flashcard_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (flashcard_id) REFERENCES flashcards(id) ON DELETE CASCADE,
    INDEX idx_fc_progress_student (student_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mcqs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    question TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NULL,
    option_d TEXT NULL,
    option_e TEXT NULL,
    correct_option ENUM('A','B','C','D','E') NOT NULL,
    explanation TEXT NULL,
    option_explanations JSON NULL,
    topic VARCHAR(255) NULL,
    difficulty ENUM('easy','moderate','difficult') NOT NULL DEFAULT 'moderate',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mcqs_lecture (lecture_id),
    INDEX idx_mcqs_course (course_id),
    INDEX idx_mcqs_status (status),
    INDEX idx_mcqs_difficulty (difficulty)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS daily_challenges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 0,
    mcqs_per_day SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    start_date DATE NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_daily_challenge_lecture (lecture_id),
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mcq_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    source ENUM('challenge','practice','lecture') NOT NULL DEFAULT 'practice',
    lecture_id INT UNSIGNED NULL,
    challenge_id INT UNSIGNED NULL,
    challenge_day SMALLINT UNSIGNED NULL,
    total_questions SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    correct_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    wrong_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    time_spent_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE SET NULL,
    FOREIGN KEY (challenge_id) REFERENCES daily_challenges(id) ON DELETE SET NULL,
    INDEX idx_mcq_attempts_student (student_id),
    INDEX idx_mcq_attempts_lecture (lecture_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mcq_attempt_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT UNSIGNED NOT NULL,
    mcq_id INT UNSIGNED NOT NULL,
    selected_option ENUM('A','B','C','D','E') NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    time_spent_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (attempt_id) REFERENCES mcq_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (mcq_id) REFERENCES mcqs(id) ON DELETE CASCADE,
    INDEX idx_mcq_answers_attempt (attempt_id),
    INDEX idx_mcq_answers_mcq (mcq_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS study_streaks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    current_streak INT UNSIGNED NOT NULL DEFAULT 0,
    longest_streak INT UNSIGNED NOT NULL DEFAULT 0,
    last_activity_date DATE NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_study_streak_student (student_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS study_activity_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    activity_date DATE NOT NULL,
    activity_type ENUM('login','lecture','mcq','revision','flashcard') NOT NULL,
    count INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_activity (student_id, activity_date, activity_type),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity_student_date (student_id, activity_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS badges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(500) NULL,
    icon VARCHAR(50) NULL,
    criteria_type ENUM('streak','mcq','flashcard','lecture','revision') NOT NULL DEFAULT 'streak',
    threshold INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_badges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    badge_id INT UNSIGNED NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_badge (student_id, badge_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS content_bookmarks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    content_type ENUM('note','flashcard','mcq','lecture') NOT NULL,
    content_id INT UNSIGNED NOT NULL,
    note VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bookmark (student_id, content_type, content_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_bookmark_student (student_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS note_highlights (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    lecture_id INT UNSIGNED NOT NULL,
    section VARCHAR(50) NULL,
    highlighted_text TEXT NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT 'yellow',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    INDEX idx_highlight_student_lecture (student_id, lecture_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS discussion_reply_likes (
    reply_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (reply_id, user_id),
    FOREIGN KEY (reply_id) REFERENCES discussion_replies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS discussion_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id INT UNSIGNED NULL,
    reply_id INT UNSIGNED NULL,
    reporter_id INT UNSIGNED NOT NULL,
    reason VARCHAR(255) NULL,
    status ENUM('open','resolved','dismissed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (thread_id) REFERENCES discussion_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_id) REFERENCES discussion_replies(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reports_status (status)
) ENGINE=InnoDB;

-- Discussion columns (safe — skips if already exists)
SET @db = DATABASE();

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='discussion_threads' AND COLUMN_NAME='lecture_id');
SET @sql = IF(@c=0, 'ALTER TABLE discussion_threads ADD COLUMN lecture_id INT UNSIGNED NULL AFTER course_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='discussion_replies' AND COLUMN_NAME='parent_id');
SET @sql = IF(@c=0, 'ALTER TABLE discussion_replies ADD COLUMN parent_id INT UNSIGNED NULL AFTER thread_id', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='discussion_replies' AND COLUMN_NAME='is_pinned');
SET @sql = IF(@c=0, 'ALTER TABLE discussion_replies ADD COLUMN is_pinned TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='discussion_replies' AND COLUMN_NAME='is_teacher_approved');
SET @sql = IF(@c=0, 'ALTER TABLE discussion_replies ADD COLUMN is_teacher_approved TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='discussion_replies' AND COLUMN_NAME='likes_count');
SET @sql = IF(@c=0, 'ALTER TABLE discussion_replies ADD COLUMN likes_count INT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- FK for lecture_id on threads (ignore error if exists)
SET @c = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='discussion_threads' AND CONSTRAINT_NAME='fk_thread_lecture');
SET @sql = IF(@c=0, 'ALTER TABLE discussion_threads ADD CONSTRAINT fk_thread_lecture FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

INSERT INTO badges (code, name, description, icon, criteria_type, threshold) VALUES
    ('streak_7',   '7-Day Streak',    'Studied every day for 7 days in a row',   'flame',  'streak', 7),
    ('streak_30',  '30-Day Streak',   'Studied every day for 30 days in a row',  'flame',  'streak', 30),
    ('streak_100', '100-Day Streak',  'Studied every day for 100 days in a row', 'crown',  'streak', 100),
    ('mcq_100',    'Century Maker',   'Answered 100 MCQs',                        'target', 'mcq', 100),
    ('mcq_1000',   'MCQ Master',      'Answered 1000 MCQs',                       'trophy', 'mcq', 1000),
    ('flash_100',  'Flash Learner',   'Reviewed 100 flashcards',                  'zap',    'flashcard', 100)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT IGNORE INTO schema_migrations (migration) VALUES ('003_ai_learning_module.sql');

-- ============================================================
-- MIGRATION 004: Material uploader tracking
-- ============================================================
SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='lecture_resources' AND COLUMN_NAME='uploaded_by');
SET @sql = IF(@c=0,
    'ALTER TABLE lecture_resources ADD COLUMN uploaded_by INT UNSIGNED NULL AFTER sort_order',
    'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='lecture_resources' AND CONSTRAINT_NAME='fk_resource_uploader');
SET @sql = IF(@c=0,
    'ALTER TABLE lecture_resources ADD CONSTRAINT fk_resource_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('004_material_uploader.sql');

-- ============================================================
-- MIGRATION 005: Monthly Excel schedule uploads
-- ============================================================
CREATE TABLE IF NOT EXISTS schedule_month_uploads (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id   INT UNSIGNED NOT NULL,
    month_year  CHAR(7) NOT NULL COMMENT 'YYYY-MM',
    file_path   VARCHAR(500) NULL,
    file_name   VARCHAR(255) NULL,
    row_count   INT UNSIGNED DEFAULT 0,
    uploaded_by INT UNSIGNED NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_schedule_month (course_id, month_year),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_smu_course (course_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('005_schedule_month_uploads.sql');

-- ============================================================
-- MIGRATION 006: Quiz review mode (Word import + auto-eval)
-- ============================================================
SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='quizzes' AND COLUMN_NAME='show_review');
SET @sql = IF(@c=0,
    'ALTER TABLE quizzes ADD COLUMN show_review TINYINT(1) NOT NULL DEFAULT 1 AFTER auto_evaluate',
    'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('006_quiz_show_review.sql');

-- ============================================================
-- MIGRATION 007: Premium study features
-- ============================================================
SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='mcq_attempts' AND COLUMN_TYPE LIKE '%daily%');
SET @sql = IF(@c=0,
    "ALTER TABLE mcq_attempts MODIFY COLUMN source ENUM('challenge','practice','lecture','daily','mistakes','bank','revision') NOT NULL DEFAULT 'practice'",
    'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS daily_challenge_sets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    challenge_date DATE NOT NULL,
    mcq_ids JSON NOT NULL,
    attempt_id INT UNSIGNED NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_daily_challenge_student_date (student_id, challenge_date),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dcs_date (challenge_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_mistakes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    mcq_id INT UNSIGNED NOT NULL,
    subject VARCHAR(150) NULL,
    chapter VARCHAR(150) NULL,
    topic VARCHAR(255) NULL,
    wrong_count INT UNSIGNED NOT NULL DEFAULT 1,
    consecutive_correct INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','mastered') NOT NULL DEFAULT 'active',
    last_wrong_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_mistake (student_id, mcq_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mcq_id) REFERENCES mcqs(id) ON DELETE CASCADE,
    INDEX idx_mistakes_student_status (student_id, status),
    INDEX idx_mistakes_topic (topic)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS study_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    exam_date DATE NOT NULL,
    hours_per_day DECIMAL(4,1) NOT NULL DEFAULT 2.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_study_plan_student (student_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS study_plan_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    task_date DATE NOT NULL,
    task_type ENUM('lecture','mcq','flashcard','revision','review') NOT NULL,
    title VARCHAR(255) NOT NULL,
    lecture_id INT UNSIGNED NULL,
    target_count SMALLINT UNSIGNED NULL,
    status ENUM('pending','completed','skipped') NOT NULL DEFAULT 'pending',
    sort_order INT NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (plan_id) REFERENCES study_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE SET NULL,
    INDEX idx_spt_plan_date (plan_id, task_date),
    INDEX idx_spt_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS revision_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    duration_seconds INT UNSIGNED NULL,
    topics_revised JSON NULL,
    mcqs_solved SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    mcqs_correct SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    accuracy DECIMAL(5,2) NULL,
    summary JSON NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_revision_student (student_id),
    INDEX idx_revision_completed (completed_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS revision_session_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    item_type ENUM('mcq','flashcard','note','lecture') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    is_correct TINYINT(1) NULL,
    FOREIGN KEY (session_id) REFERENCES revision_sessions(id) ON DELETE CASCADE,
    INDEX idx_rsi_session (session_id)
) ENGINE=InnoDB;

SET @c = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='mcq_attempt_answers' AND INDEX_NAME='idx_mcq_attempt_answers_mcq_correct');
SET @sql = IF(@c=0, 'CREATE INDEX idx_mcq_attempt_answers_mcq_correct ON mcq_attempt_answers (mcq_id, is_correct)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='mcqs' AND INDEX_NAME='idx_mcqs_topic_status');
SET @sql = IF(@c=0, 'CREATE INDEX idx_mcqs_topic_status ON mcqs (topic, status, difficulty)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('007_premium_study_features.sql');

-- 008: Interactive MCQ assignments
SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='assignments' AND COLUMN_NAME='assignment_type');
SET @sql = IF(@c=0, "ALTER TABLE assignments ADD COLUMN assignment_type ENUM('file','interactive_test') NOT NULL DEFAULT 'file' AFTER max_marks", 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='assignments' AND COLUMN_NAME='external_url');
SET @sql = IF(@c=0, 'ALTER TABLE assignments ADD COLUMN external_url VARCHAR(500) NULL AFTER attachment_path', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS assignment_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    explanation TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    INDEX idx_assignment_questions_assignment (assignment_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assignment_question_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES assignment_questions(id) ON DELETE CASCADE,
    INDEX idx_assignment_options_question (question_id)
) ENGINE=InnoDB;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='assignment_submissions' AND COLUMN_NAME='answers_json');
SET @sql = IF(@c=0, 'ALTER TABLE assignment_submissions ADD COLUMN answers_json JSON NULL AFTER submission_text', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='assignment_submissions' AND COLUMN_NAME='percentage');
SET @sql = IF(@c=0, 'ALTER TABLE assignment_submissions ADD COLUMN percentage DECIMAL(5,2) NULL AFTER marks', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @c = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='assignment_submissions' AND COLUMN_NAME='passed');
SET @sql = IF(@c=0, 'ALTER TABLE assignment_submissions ADD COLUMN passed TINYINT(1) NULL AFTER percentage', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('008_interactive_assignments.sql');

-- 009: Multiple files per assignment / submission
CREATE TABLE IF NOT EXISTS assignment_attachments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NULL,
    mime_type VARCHAR(120) NULL,
    file_size INT UNSIGNED NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    INDEX idx_assignment_attachments_assignment (assignment_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assignment_submission_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NULL,
    mime_type VARCHAR(120) NULL,
    file_size INT UNSIGNED NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES assignment_submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_files_submission (submission_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('009_assignment_multi_files.sql');

-- 010: Backfill legacy single-file paths into new tables (non-destructive — keeps old columns)
INSERT INTO assignment_attachments (assignment_id, title, file_path, original_filename, sort_order)
SELECT a.id,
       COALESCE(NULLIF(TRIM(SUBSTRING_INDEX(a.attachment_path, '/', -1)), ''), 'Attachment'),
       a.attachment_path,
       SUBSTRING_INDEX(a.attachment_path, '/', -1),
       0
FROM assignments a
WHERE a.attachment_path IS NOT NULL
  AND TRIM(a.attachment_path) <> ''
  AND NOT EXISTS (
    SELECT 1 FROM assignment_attachments aa
    WHERE aa.assignment_id = a.id AND aa.file_path = a.attachment_path
  );

INSERT INTO assignment_submission_files (submission_id, title, file_path, original_filename, sort_order)
SELECT s.id,
       COALESCE(NULLIF(TRIM(s.original_filename), ''), NULLIF(TRIM(SUBSTRING_INDEX(s.file_path, '/', -1)), ''), 'Uploaded file'),
       s.file_path,
       s.original_filename,
       0
FROM assignment_submissions s
WHERE s.file_path IS NOT NULL
  AND TRIM(s.file_path) <> ''
  AND NOT EXISTS (
    SELECT 1 FROM assignment_submission_files sf
    WHERE sf.submission_id = s.id AND sf.file_path = s.file_path
  );

INSERT IGNORE INTO schema_migrations (migration) VALUES ('010_assignment_files_backfill.sql');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DONE — verify (optional):
-- SELECT migration FROM schema_migrations ORDER BY id;
-- SELECT COUNT(*) AS users FROM users;  -- should be unchanged
-- ============================================================
