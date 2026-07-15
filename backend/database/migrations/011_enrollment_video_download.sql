-- ============================================================
-- Migration 011: Per-student video download permission
-- Default OFF — only students you enable can download videos.
-- Streaming / watching still works for all enrolled students.
-- ============================================================

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
