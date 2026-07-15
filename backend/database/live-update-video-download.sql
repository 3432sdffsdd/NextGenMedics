-- ============================================================
-- LIVE SAFE UPDATE — NextGen Medics LMS
-- ============================================================
-- Run this ONCE on your LIVE database in phpMyAdmin / StackCP.
--
-- ✓ Adds 1 column only (video download permission)
-- ✓ Does NOT delete/update users, teachers, students, courses,
--   quizzes, assignments, lectures, enrollments, or files
-- ✓ Safe to run twice (skips if column already exists)
-- ============================================================

USE `nextgenmedics-3530353392cf`;

SET @db = DATABASE();

SET @c = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'course_enrollments'
    AND COLUMN_NAME = 'can_download_videos'
);
SET @sql = IF(
  @c = 0,
  'ALTER TABLE course_enrollments ADD COLUMN can_download_videos TINYINT(1) NOT NULL DEFAULT 0 AFTER progress',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (migration) VALUES ('011_enrollment_video_download.sql');

-- Verify after running:
-- SHOW COLUMNS FROM course_enrollments LIKE 'can_download_videos';
