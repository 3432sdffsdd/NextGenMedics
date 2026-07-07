-- ============================================================
-- AI Learning Assistant Module (FCPS Part-I)
-- Migration 003
-- MySQL 8.0+ / MariaDB 10.5+
-- Safe to run once against an existing nextgen_medics database.
-- ============================================================

USE nextgen_medics;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- AI GENERATION JOBS (async, step-wise processing queue)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- AI LECTURE CONTENT (summary + revision-note sections)
-- One row per lecture. All content starts as draft (teacher review).
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- FLASHCARDS
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- MCQs (FCPS-style Single Best Answer)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- DAILY MCQ CHALLENGE
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- MCQ ATTEMPTS (challenge or practice)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- STUDY STREAKS & DAILY ACTIVITY
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- ACHIEVEMENT BADGES
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- BOOKMARKS & HIGHLIGHTS (Revision Center)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- DISCUSSION ENHANCEMENTS (per-lecture, likes, pins, reports)
-- ------------------------------------------------------------
ALTER TABLE discussion_threads
    ADD COLUMN lecture_id INT UNSIGNED NULL AFTER course_id,
    ADD CONSTRAINT fk_thread_lecture FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE;

ALTER TABLE discussion_replies
    ADD COLUMN parent_id INT UNSIGNED NULL AFTER thread_id,
    ADD COLUMN is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN is_teacher_approved TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN likes_count INT UNSIGNED NOT NULL DEFAULT 0;

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

-- ------------------------------------------------------------
-- SEED DEFAULT BADGES
-- ------------------------------------------------------------
INSERT INTO badges (code, name, description, icon, criteria_type, threshold) VALUES
    ('streak_7',   '7-Day Streak',    'Studied every day for 7 days in a row',   'flame',  'streak', 7),
    ('streak_30',  '30-Day Streak',   'Studied every day for 30 days in a row',  'flame',  'streak', 30),
    ('streak_100', '100-Day Streak',  'Studied every day for 100 days in a row', 'crown',  'streak', 100),
    ('mcq_100',    'Century Maker',   'Answered 100 MCQs',                        'target', 'mcq', 100),
    ('mcq_1000',   'MCQ Master',      'Answered 1000 MCQs',                       'trophy', 'mcq', 1000),
    ('flash_100',  'Flash Learner',   'Reviewed 100 flashcards',                  'zap',    'flashcard', 100)
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET FOREIGN_KEY_CHECKS = 1;
