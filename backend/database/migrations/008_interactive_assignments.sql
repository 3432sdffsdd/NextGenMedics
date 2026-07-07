-- Interactive MCQ assignments (HTML / link import)

ALTER TABLE assignments
    ADD COLUMN assignment_type ENUM('file', 'interactive_test') NOT NULL DEFAULT 'file' AFTER max_marks,
    ADD COLUMN external_url VARCHAR(500) NULL AFTER attachment_path;

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

ALTER TABLE assignment_submissions
    ADD COLUMN answers_json JSON NULL AFTER submission_text,
    ADD COLUMN percentage DECIMAL(5,2) NULL AFTER marks,
    ADD COLUMN passed TINYINT(1) NULL AFTER percentage;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('008_interactive_assignments.sql');
