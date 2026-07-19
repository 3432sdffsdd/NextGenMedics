-- Allow clearing FCPS exam countdown (exam_date can be unset).
-- Safe to re-run.
SET NAMES utf8mb4;

ALTER TABLE fcps_study_plans
    MODIFY exam_date DATE NULL;
