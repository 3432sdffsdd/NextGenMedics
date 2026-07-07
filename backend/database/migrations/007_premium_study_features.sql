-- Premium study features: daily challenge sets, mistakes, study planner, revision sessions
-- Safe to run on existing databases (IF NOT EXISTS / conditional alters).

SET NAMES utf8mb4;

-- Extend MCQ attempt sources
ALTER TABLE mcq_attempts
    MODIFY COLUMN source ENUM('challenge','practice','lecture','daily','mistakes','bank','revision') NOT NULL DEFAULT 'practice';

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

CREATE INDEX IF NOT EXISTS idx_mcq_attempt_answers_mcq_correct ON mcq_attempt_answers (mcq_id, is_correct);
CREATE INDEX IF NOT EXISTS idx_mcqs_topic_status ON mcqs (topic, status, difficulty);
