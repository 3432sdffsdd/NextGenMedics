-- =============================================================================
-- SAFE LIVE DEPLOY — 2026-07-20
-- FCPS exam date clear + My Mistakes / quiz review (code upload separately)
-- =============================================================================
-- SAFE:
--   • No DROP / TRUNCATE / DELETE / UPDATE of user data
--   • Does NOT touch: users, courses, lectures, lecture_resources (videos),
--     assignments, assignment_submissions, quizzes, quiz_questions,
--     enrollments, attendance, uploads, or media files
--
-- ONLY CHANGE:
--   • fcps_study_plans.exam_date → allow NULL (so students can clear countdown)
--
-- How to run (phpMyAdmin → your live DB → SQL tab):
--   1. Confirm database name at top of phpMyAdmin
--   2. Paste this file and click Go
--   3. Ignore "Duplicate column" style errors if you re-run (none expected here)
-- =============================================================================

SET NAMES utf8mb4;

-- Allow clearing FCPS exam countdown (exam_date can be unset).
-- Existing exam dates stay as they are. No rows deleted.
ALTER TABLE fcps_study_plans
    MODIFY exam_date DATE NULL;

-- Optional verify (run after):
-- SHOW COLUMNS FROM fcps_study_plans LIKE 'exam_date';
-- Expected: Null = YES
