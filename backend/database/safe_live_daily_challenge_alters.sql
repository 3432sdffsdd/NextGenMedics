-- Run ONLY if Daily Challenge still errors about missing columns
-- (e.g. quiz_question_ids / question_source).
-- If you see "Duplicate column name", that column already exists — ignore and continue.

ALTER TABLE daily_challenge_sets
    ADD COLUMN question_source ENUM('mcq','quiz') NOT NULL DEFAULT 'quiz' AFTER mcq_ids;

ALTER TABLE daily_challenge_sets
    ADD COLUMN quiz_question_ids JSON NULL AFTER question_source;

ALTER TABLE daily_challenge_sets
    ADD COLUMN correct_count SMALLINT UNSIGNED NULL AFTER quiz_question_ids;

ALTER TABLE daily_challenge_sets
    ADD COLUMN wrong_count SMALLINT UNSIGNED NULL AFTER correct_count;

ALTER TABLE daily_challenge_sets
    ADD COLUMN score DECIMAL(5,2) NULL AFTER wrong_count;

ALTER TABLE daily_challenge_sets
    ADD COLUMN time_spent_seconds INT UNSIGNED NULL AFTER score;
