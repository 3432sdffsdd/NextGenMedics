-- Goal-based Study Planner — references existing LMS content (no data duplication).
-- Separate from legacy study_plans and fcps_study_* tables.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS gb_study_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    goal_type ENUM(
        'full_syllabus','selected_subjects','selected_lectures','selected_quizzes',
        'selected_flashcards','selected_notes','mock_exam','revision_only','custom'
    ) NOT NULL DEFAULT 'custom',
    goal_title VARCHAR(255) NOT NULL DEFAULT 'My Study Plan',
    start_date DATE NOT NULL,
    target_date DATE NOT NULL,
    exam_date DATE NULL,
    hours_per_day DECIMAL(4,1) NOT NULL DEFAULT 3.0,
    preferred_days JSON NOT NULL,
    sessions_per_day TINYINT UNSIGNED NOT NULL DEFAULT 2,
    preferred_time ENUM('morning','afternoon','evening','night') NOT NULL DEFAULT 'evening',
    daily_mcq_target SMALLINT UNSIGNED NOT NULL DEFAULT 40,
    daily_flashcard_target SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    revision_preference ENUM('every_3_days','every_5_days','every_7_days','every_sunday','after_each_subject') NOT NULL DEFAULT 'every_7_days',
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    completion_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    streak_days INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gb_plan_student (student_id, status),
    CONSTRAINT fk_gb_plan_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gb_study_plan_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    item_type ENUM('lecture','video','note','quiz','flashcard_set','revision') NOT NULL,
    ref_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    lecture_id INT UNSIGNED NULL,
    subject_title VARCHAR(255) NULL,
    title VARCHAR(255) NOT NULL,
    estimated_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 45,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_gb_items_plan (plan_id),
    INDEX idx_gb_items_type (item_type, ref_id),
    CONSTRAINT fk_gb_items_plan FOREIGN KEY (plan_id) REFERENCES gb_study_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gb_study_days (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    plan_date DATE NOT NULL,
    is_study_day TINYINT(1) NOT NULL DEFAULT 1,
    day_status ENUM('upcoming','completed','partial','missed','rest') NOT NULL DEFAULT 'upcoming',
    completed_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uk_gb_day (plan_id, plan_date),
    CONSTRAINT fk_gb_day_plan FOREIGN KEY (plan_id) REFERENCES gb_study_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gb_study_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    day_id INT UNSIGNED NOT NULL,
    plan_item_id INT UNSIGNED NULL,
    plan_date DATE NOT NULL,
    task_type ENUM('lecture','video','note','quiz','flashcard','revision','mcq_practice') NOT NULL,
    ref_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    lecture_id INT UNSIGNED NULL,
    subject_title VARCHAR(255) NULL,
    title VARCHAR(255) NOT NULL,
    target_count SMALLINT UNSIGNED NULL,
    status ENUM('pending','completed','skipped','missed','rescheduled') NOT NULL DEFAULT 'pending',
    sort_order INT NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL,
    INDEX idx_gb_task_plan_date (plan_id, plan_date),
    INDEX idx_gb_task_status (status),
    CONSTRAINT fk_gb_task_plan FOREIGN KEY (plan_id) REFERENCES gb_study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_gb_task_day FOREIGN KEY (day_id) REFERENCES gb_study_days(id) ON DELETE CASCADE,
    CONSTRAINT fk_gb_task_item FOREIGN KEY (plan_item_id) REFERENCES gb_study_plan_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gb_study_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    subject_title VARCHAR(255) NOT NULL,
    total_lectures INT UNSIGNED NOT NULL DEFAULT 0,
    completed_lectures INT UNSIGNED NOT NULL DEFAULT 0,
    total_quizzes INT UNSIGNED NOT NULL DEFAULT 0,
    completed_quizzes INT UNSIGNED NOT NULL DEFAULT 0,
    total_notes INT UNSIGNED NOT NULL DEFAULT 0,
    completed_notes INT UNSIGNED NOT NULL DEFAULT 0,
    total_flashcards INT UNSIGNED NOT NULL DEFAULT 0,
    completed_flashcards INT UNSIGNED NOT NULL DEFAULT 0,
    total_revision INT UNSIGNED NOT NULL DEFAULT 0,
    completed_revision INT UNSIGNED NOT NULL DEFAULT 0,
    completion_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uk_gb_prog (plan_id, subject_title),
    CONSTRAINT fk_gb_prog_plan FOREIGN KEY (plan_id) REFERENCES gb_study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_gb_prog_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gb_study_challenges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    challenge_type ENUM('subject_sprint','mcq_count','deadline','syllabus') NOT NULL DEFAULT 'deadline',
    target_value INT UNSIGNED NOT NULL DEFAULT 0,
    current_value INT UNSIGNED NOT NULL DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active','completed','failed','cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gb_chal_student (student_id, status),
    CONSTRAINT fk_gb_chal_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_gb_chal_plan FOREIGN KEY (plan_id) REFERENCES gb_study_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gb_study_statistics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    stat_date DATE NOT NULL,
    tasks_total SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    tasks_completed SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    study_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY uk_gb_stat (plan_id, stat_date),
    CONSTRAINT fk_gb_stat_plan FOREIGN KEY (plan_id) REFERENCES gb_study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_gb_stat_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
