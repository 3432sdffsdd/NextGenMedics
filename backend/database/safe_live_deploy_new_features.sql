-- =============================================================================
-- SAFE LIVE DEPLOY — schema only (NextGen Medics)
-- =============================================================================
-- What this does:
--   • Creates NEW empty tables for Video Tracking + Personal Study Planner
--   • Uses CREATE TABLE IF NOT EXISTS (safe to re-run)
--
-- What this does NOT do:
--   • Does NOT DROP / TRUNCATE / UPDATE / DELETE any existing data
--   • Does NOT touch: users, courses, enrollments, lectures, lecture_resources,
--     assignments, assignment_submissions, quizzes, attendance, etc.
--
-- Student Performance (teacher) needs NO new tables — it only READs existing data.
-- =============================================================================

SET NAMES utf8mb4;

-- Optional safety check (run separately first if you want):
-- SELECT DATABASE();
-- SHOW TABLES LIKE 'users';
-- SHOW TABLES LIKE 'courses';

-- ── Video Tracking (empty until students watch videos) ─────────────────────
CREATE TABLE IF NOT EXISTS vt_video_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    lecture_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    duration_seconds DECIMAL(12,2) NOT NULL DEFAULT 0,
    watched_seconds DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_position DECIMAL(12,2) NOT NULL DEFAULT 0,
    last_position DECIMAL(12,2) NOT NULL DEFAULT 0,
    completion_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    status ENUM('not_started','watching','completed') NOT NULL DEFAULT 'not_started',
    play_count INT UNSIGNED NOT NULL DEFAULT 0,
    replay_count INT UNSIGNED NOT NULL DEFAULT 0,
    pause_count INT UNSIGNED NOT NULL DEFAULT 0,
    seek_forward_count INT UNSIGNED NOT NULL DEFAULT 0,
    seek_backward_count INT UNSIGNED NOT NULL DEFAULT 0,
    playback_speed DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    device_type VARCHAR(32) NULL,
    browser VARCHAR(64) NULL,
    os_name VARCHAR(64) NULL,
    ip_address VARCHAR(45) NULL,
    first_watched_at TIMESTAMP NULL,
    last_watched_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_vt_progress (student_id, resource_id),
    INDEX idx_vt_prog_student (student_id, status),
    INDEX idx_vt_prog_course (course_id, student_id),
    INDEX idx_vt_prog_lecture (lecture_id),
    CONSTRAINT fk_vt_prog_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vt_prog_resource FOREIGN KEY (resource_id) REFERENCES lecture_resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vt_video_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    lecture_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    event_type VARCHAR(40) NOT NULL,
    position_seconds DECIMAL(12,2) NULL,
    duration_seconds DECIMAL(12,2) NULL,
    watched_delta DECIMAL(12,2) NULL,
    playback_speed DECIMAL(4,2) NULL,
    meta_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vt_evt_student_time (student_id, created_at),
    INDEX idx_vt_evt_resource (resource_id, created_at),
    INDEX idx_vt_evt_type (event_type),
    CONSTRAINT fk_vt_evt_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vt_evt_resource FOREIGN KEY (resource_id) REFERENCES lecture_resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vt_watch_segments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    start_pos DECIMAL(12,2) NOT NULL DEFAULT 0,
    end_pos DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vt_seg_student_res (student_id, resource_id),
    CONSTRAINT fk_vt_seg_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vt_seg_resource FOREIGN KEY (resource_id) REFERENCES lecture_resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Personal Study Planner (empty until students create plans) ──────────────
CREATE TABLE IF NOT EXISTS psp_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    exam_date DATE NULL,
    hours_per_day DECIMAL(4,1) NOT NULL DEFAULT 3.0,
    preferred_days JSON NOT NULL,
    setup_completed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_psp_settings_student (student_id),
    CONSTRAINT fk_psp_settings_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS psp_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    plan_name VARCHAR(255) NOT NULL DEFAULT 'My Study Plan',
    plan_mode ENUM('lms','manual','mixed') NOT NULL DEFAULT 'lms',
    duration_days SMALLINT UNSIGNED NOT NULL DEFAULT 7,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active','completed','archived','draft') NOT NULL DEFAULT 'active',
    completion_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_psp_plans_student (student_id, status),
    CONSTRAINT fk_psp_plans_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS psp_plan_days (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    day_number SMALLINT UNSIGNED NOT NULL,
    plan_date DATE NOT NULL,
    day_status ENUM('upcoming','in_progress','completed','partial','missed') NOT NULL DEFAULT 'upcoming',
    completed_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uk_psp_day (plan_id, plan_date),
    INDEX idx_psp_day_num (plan_id, day_number),
    CONSTRAINT fk_psp_days_plan FOREIGN KEY (plan_id) REFERENCES psp_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS psp_plan_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    day_id INT UNSIGNED NOT NULL,
    plan_date DATE NOT NULL,
    day_number SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    source ENUM('lms','manual') NOT NULL DEFAULT 'lms',
    task_type ENUM('lecture','video','quiz','flashcard','note','revision','manual','mcq') NOT NULL,
    ref_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    lecture_id INT UNSIGNED NULL,
    subject_title VARCHAR(255) NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    target_count SMALLINT UNSIGNED NULL,
    estimated_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    status ENUM('pending','completed','skipped') NOT NULL DEFAULT 'pending',
    sort_order INT NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL,
    INDEX idx_psp_task_plan_date (plan_id, plan_date),
    INDEX idx_psp_task_status (plan_id, status),
    CONSTRAINT fk_psp_tasks_plan FOREIGN KEY (plan_id) REFERENCES psp_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_psp_tasks_day FOREIGN KEY (day_id) REFERENCES psp_plan_days(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS psp_plan_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    videos_completed INT UNSIGNED NOT NULL DEFAULT 0,
    videos_total INT UNSIGNED NOT NULL DEFAULT 0,
    quizzes_completed INT UNSIGNED NOT NULL DEFAULT 0,
    quizzes_total INT UNSIGNED NOT NULL DEFAULT 0,
    flashcards_completed INT UNSIGNED NOT NULL DEFAULT 0,
    flashcards_total INT UNSIGNED NOT NULL DEFAULT 0,
    notes_completed INT UNSIGNED NOT NULL DEFAULT 0,
    notes_total INT UNSIGNED NOT NULL DEFAULT 0,
    revision_completed INT UNSIGNED NOT NULL DEFAULT 0,
    revision_total INT UNSIGNED NOT NULL DEFAULT 0,
    manual_completed INT UNSIGNED NOT NULL DEFAULT 0,
    manual_total INT UNSIGNED NOT NULL DEFAULT 0,
    overall_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    streak_days INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_psp_progress_plan (plan_id),
    CONSTRAINT fk_psp_progress_plan FOREIGN KEY (plan_id) REFERENCES psp_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_psp_progress_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS psp_statistics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    stat_date DATE NOT NULL,
    tasks_completed INT UNSIGNED NOT NULL DEFAULT 0,
    tasks_pending INT UNSIGNED NOT NULL DEFAULT 0,
    tasks_skipped INT UNSIGNED NOT NULL DEFAULT 0,
    study_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    videos_watched INT UNSIGNED NOT NULL DEFAULT 0,
    quizzes_attempted INT UNSIGNED NOT NULL DEFAULT 0,
    flashcards_reviewed INT UNSIGNED NOT NULL DEFAULT 0,
    manual_completed INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY uk_psp_stat (student_id, stat_date),
    CONSTRAINT fk_psp_stat_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Verify (read-only) ─────────────────────────────────────────────────────
-- SELECT COUNT(*) AS users_unchanged FROM users;
-- SELECT COUNT(*) AS courses_unchanged FROM courses;
-- SELECT COUNT(*) AS lectures_unchanged FROM lectures;
-- SELECT COUNT(*) AS assignments_unchanged FROM assignments;
-- SHOW TABLES LIKE 'vt_%';
-- SHOW TABLES LIKE 'psp_%';
