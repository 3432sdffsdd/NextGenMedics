-- Quiz review mode and safe column additions (existing data preserved)
ALTER TABLE quizzes ADD COLUMN show_review TINYINT(1) NOT NULL DEFAULT 1 AFTER auto_evaluate;
