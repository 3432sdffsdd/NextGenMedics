-- Premium AI Study Planner (Groq-powered).
-- Uses dedicated ai_study_* tables — does NOT modify existing study_plans / study_plan_tasks.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS ai_study_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    exam_date DATE NOT NULL,
    start_date DATE NOT NULL,
    hours_per_day DECIMAL(4,1) NOT NULL DEFAULT 3.0,
    preferred_days JSON NOT NULL,
    sessions_per_day TINYINT UNSIGNED NOT NULL DEFAULT 2,
    preferred_time ENUM('morning','afternoon','evening','night') NOT NULL DEFAULT 'evening',
    subjects_completed JSON NULL,
    subjects_remaining JSON NOT NULL,
    subjects_weak JSON NULL,
    subjects_strong JSON NULL,
    daily_mcq_target SMALLINT UNSIGNED NOT NULL DEFAULT 40,
    daily_flashcard_target SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    revision_preference ENUM('every_3_days','every_5_days','every_7_days','weekends_only','after_each_subject') NOT NULL DEFAULT 'every_7_days',
    ai_strategy JSON NULL,
    subject_order JSON NULL,
    motivation_quote TEXT NULL,
    weekly_milestones JSON NULL,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    completion_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    streak_days INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ai_plan_student_status (student_id, status),
    CONSTRAINT fk_ai_plan_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_study_plan_days (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    plan_date DATE NOT NULL,
    is_study_day TINYINT(1) NOT NULL DEFAULT 1,
    day_status ENUM('upcoming','completed','partial','missed','rest') NOT NULL DEFAULT 'upcoming',
    topics JSON NULL,
    mcq_target SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    flashcard_target SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    revision_subject VARCHAR(191) NULL,
    sessions_meta JSON NULL,
    completed_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    UNIQUE KEY uk_ai_day (plan_id, plan_date),
    INDEX idx_ai_day_date (plan_date),
    CONSTRAINT fk_ai_day_plan FOREIGN KEY (plan_id) REFERENCES ai_study_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_study_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    day_id INT UNSIGNED NOT NULL,
    plan_date DATE NOT NULL,
    task_type ENUM('study','mcq','flashcard','revision') NOT NULL,
    subject VARCHAR(191) NULL,
    title VARCHAR(255) NOT NULL,
    session_number TINYINT UNSIGNED NOT NULL DEFAULT 1,
    target_count SMALLINT UNSIGNED NULL,
    status ENUM('pending','completed','skipped','missed') NOT NULL DEFAULT 'pending',
    sort_order INT NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL,
    INDEX idx_ai_task_plan_date (plan_id, plan_date),
    INDEX idx_ai_task_day (day_id),
    INDEX idx_ai_task_status (status),
    CONSTRAINT fk_ai_task_plan FOREIGN KEY (plan_id) REFERENCES ai_study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_ai_task_day FOREIGN KEY (day_id) REFERENCES ai_study_plan_days(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_study_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    subject VARCHAR(191) NOT NULL,
    total_tasks INT UNSIGNED NOT NULL DEFAULT 0,
    completed_tasks INT UNSIGNED NOT NULL DEFAULT 0,
    completion_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    last_studied_at TIMESTAMP NULL,
    UNIQUE KEY uk_ai_progress (plan_id, subject),
    INDEX idx_ai_progress_student (student_id),
    CONSTRAINT fk_ai_progress_plan FOREIGN KEY (plan_id) REFERENCES ai_study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_ai_progress_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_study_statistics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    stat_date DATE NOT NULL,
    tasks_total SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    tasks_completed SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    mcqs_done SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    flashcards_done SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    study_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY uk_ai_stat (plan_id, stat_date),
    INDEX idx_ai_stat_student (student_id),
    CONSTRAINT fk_ai_stat_plan FOREIGN KEY (plan_id) REFERENCES ai_study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_ai_stat_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_study_ai_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NULL,
    student_id INT UNSIGNED NOT NULL,
    role ENUM('user','assistant','system') NOT NULL,
    message MEDIUMTEXT NOT NULL,
    context_type VARCHAR(64) NULL,
    meta JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ai_hist_student (student_id, created_at),
    INDEX idx_ai_hist_plan (plan_id),
    CONSTRAINT fk_ai_hist_plan FOREIGN KEY (plan_id) REFERENCES ai_study_plans(id) ON DELETE SET NULL,
    CONSTRAINT fk_ai_hist_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_study_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    day_id INT UNSIGNED NOT NULL,
    session_number TINYINT UNSIGNED NOT NULL DEFAULT 1,
    time_label VARCHAR(64) NOT NULL,
    subject VARCHAR(191) NULL,
    focus VARCHAR(255) NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    status ENUM('pending','completed','skipped') NOT NULL DEFAULT 'pending',
    INDEX idx_ai_sess_day (day_id),
    CONSTRAINT fk_ai_sess_plan FOREIGN KEY (plan_id) REFERENCES ai_study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_ai_sess_day FOREIGN KEY (day_id) REFERENCES ai_study_plan_days(id) ON DELETE CASCADE
) ENGINE=InnoDB;
