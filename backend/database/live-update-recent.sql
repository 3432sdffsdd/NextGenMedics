-- ============================================================
-- NextGen Medics LMS — RECENT LIVE UPDATE (last ~2 days)
-- ============================================================
-- Safe to run on production:
--   ✓ Schema only (tables + columns)
--   ✓ Backfills assignment file paths into new tables
--   ✗ Does NOT change users, teachers, students, enrollments
--
-- BEFORE RUNNING: BACK UP your database first!
-- Replace with your live database name (use backticks if it contains hyphens):
-- USE `nextgenmedics-3530353392cf`;
USE `YOUR_DB_NAME`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @db = DATABASE();

-- ============================================================
-- 008: Interactive MCQ assignments + external link
-- ============================================================
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

-- ============================================================
-- 009: Multiple files per assignment / submission
-- ============================================================
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

-- ============================================================
-- 010: Backfill existing single-file paths (non-destructive)
-- Keeps assignments.attachment_path — only copies into new table
-- ============================================================
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

-- Verify (optional):
-- SELECT migration FROM schema_migrations WHERE migration LIKE '008%' OR migration LIKE '009%' OR migration LIKE '010%';
-- SELECT COUNT(*) AS users_unchanged FROM users;
-- SHOW TABLES LIKE 'assignment_%';
