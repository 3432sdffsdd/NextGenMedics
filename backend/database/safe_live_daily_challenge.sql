-- Fix: create missing Daily Challenge tables (safe — no data loss).
-- Run in phpMyAdmin on database: nextgenmedics-3530353392cf

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS student_question_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    is_correct TINYINT(1) NULL,
    selected_option VARCHAR(5) NULL,
    attempt_date DATE NOT NULL,
    daily_challenge_date DATE NULL,
    challenge_set_id INT UNSIGNED NULL,
    source ENUM('daily','quiz','practice','weak') NOT NULL DEFAULT 'daily',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sqh_student_question (student_id, question_id),
    INDEX idx_sqh_student (student_id),
    INDEX idx_sqh_question (question_id),
    INDEX idx_sqh_attempt_date (attempt_date),
    INDEX idx_sqh_challenge_date (daily_challenge_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS daily_challenge_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    challenge_set_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    selected_option VARCHAR(5) NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_dca_set_question (challenge_set_id, question_id),
    INDEX idx_dca_student (student_id),
    INDEX idx_dca_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure daily_challenge_sets has quiz columns (skip any line that says Duplicate column name)
CREATE TABLE IF NOT EXISTS daily_challenge_sets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    challenge_date DATE NOT NULL,
    mcq_ids JSON NOT NULL,
    question_source ENUM('mcq','quiz') NOT NULL DEFAULT 'quiz',
    quiz_question_ids JSON NULL,
    correct_count SMALLINT UNSIGNED NULL,
    wrong_count SMALLINT UNSIGNED NULL,
    score DECIMAL(5,2) NULL,
    time_spent_seconds INT UNSIGNED NULL,
    attempt_id INT UNSIGNED NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_daily_challenge_student_date (student_id, challenge_date),
    INDEX idx_dcs_date (challenge_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
