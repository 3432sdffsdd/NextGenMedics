-- ============================================================
-- Migration 012: Assignment-scoped discussion threads
-- Safe — adds nullable column only. Does not change existing data.
-- ============================================================

SET @db = DATABASE();

SET @c = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'discussion_threads'
    AND COLUMN_NAME = 'assignment_id'
);
SET @sql = IF(
  @c = 0,
  'ALTER TABLE discussion_threads ADD COLUMN assignment_id INT UNSIGNED NULL AFTER lecture_id, ADD INDEX idx_discussion_assignment (assignment_id)',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @fk = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'discussion_threads'
    AND CONSTRAINT_NAME = 'discussion_threads_assignment_fk'
);
SET @col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'discussion_threads'
    AND COLUMN_NAME = 'assignment_id'
);
SET @sql = IF(
  @fk = 0 AND @col > 0,
  'ALTER TABLE discussion_threads ADD CONSTRAINT discussion_threads_assignment_fk FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
