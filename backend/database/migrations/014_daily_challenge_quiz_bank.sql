-- Daily Challenge from teacher-uploaded quizzes + per-student question history.
-- Safe: adds tables/columns only. Does not drop existing data.
-- Column ALTERs are applied by migrate.php guards when missing.

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
    INDEX idx_sqh_challenge_date (daily_challenge_date),
    INDEX idx_sqh_student_correct (student_id, is_correct),
    CONSTRAINT fk_sqh_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sqh_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
    INDEX idx_dca_question (question_id),
    CONSTRAINT fk_dca_set FOREIGN KEY (challenge_set_id) REFERENCES daily_challenge_sets(id) ON DELETE CASCADE,
    CONSTRAINT fk_dca_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_dca_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;
