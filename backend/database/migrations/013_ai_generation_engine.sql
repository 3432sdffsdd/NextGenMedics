-- ============================================================
-- AI Generation Engine (NextGen Medics — Gemini)
-- Migration 013
-- MySQL 8.0+ / MariaDB 10.5+
--
-- Adds the staged generation engine on top of the existing AI module.
-- Extends ai_generation_jobs (new nullable columns only) and adds the
-- per-stage tracking table plus one dedicated table per content type.
-- Existing tables/data are NOT modified destructively.
-- ============================================================

USE nextgen_medics;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Extend the existing AI job record for engine runs.
-- (New nullable/defaulted columns — safe additive change.)
-- ------------------------------------------------------------
ALTER TABLE ai_generation_jobs
    MODIFY status ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    ADD COLUMN engine TINYINT(1) NOT NULL DEFAULT 0 AFTER requested_by,
    ADD COLUMN model VARCHAR(100) NULL AFTER options,
    ADD COLUMN stage_label VARCHAR(150) NULL AFTER progress,
    ADD COLUMN prompt_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER mcq_done,
    ADD COLUMN completion_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER prompt_tokens,
    ADD COLUMN total_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER completion_tokens,
    ADD COLUMN retries SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER total_tokens,
    ADD COLUMN estimated_cost DECIMAL(12,6) NOT NULL DEFAULT 0 AFTER retries,
    ADD COLUMN generation_seconds INT UNSIGNED NOT NULL DEFAULT 0 AFTER estimated_cost,
    ADD COLUMN started_at TIMESTAMP NULL AFTER generation_seconds;

-- Extend the shared lecture-content row with engine sections.
ALTER TABLE ai_lecture_content
    ADD COLUMN detailed_notes LONGTEXT NULL AFTER summary,
    ADD COLUMN high_yield_notes LONGTEXT NULL AFTER detailed_notes;

-- ------------------------------------------------------------
-- AI JOB STAGES (one row per deliverable — drives the live checklist)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ai_job_stages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    stage_group TINYINT UNSIGNED NOT NULL DEFAULT 0,
    stage_key VARCHAR(50) NOT NULL,
    title VARCHAR(150) NOT NULL,
    status ENUM('pending','running','completed','failed','skipped') NOT NULL DEFAULT 'pending',
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    target SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    done SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    prompt_tokens INT UNSIGNED NOT NULL DEFAULT 0,
    completion_tokens INT UNSIGNED NOT NULL DEFAULT 0,
    total_tokens INT UNSIGNED NOT NULL DEFAULT 0,
    retries SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    latency_ms INT UNSIGNED NOT NULL DEFAULT 0,
    error TEXT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_job_stage (job_id, stage_key),
    FOREIGN KEY (job_id) REFERENCES ai_generation_jobs(id) ON DELETE CASCADE,
    INDEX idx_job_stages_job (job_id, status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DRUG TABLES (one row per drug — Stage 2)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS drug_tables (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    drug_name VARCHAR(255) NOT NULL,
    drug_class VARCHAR(255) NULL,
    mechanism TEXT NULL,
    indications TEXT NULL,
    adverse_effects TEXT NULL,
    notes TEXT NULL,
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_drug_tables_lecture (lecture_id),
    INDEX idx_drug_tables_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DISEASE COMPARISONS (one row per comparison matrix — Stage 3)
-- diseases:        ["Disease A","Disease B", ...]
-- comparison_rows: [{"feature":"Onset","values":{"Disease A":"acute","Disease B":"chronic"}}]
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS disease_comparisons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    diseases JSON NULL,
    comparison_rows JSON NULL,
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_disease_comp_lecture (lecture_id),
    INDEX idx_disease_comp_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MNEMONICS (one row per mnemonic — Stage 3)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mnemonics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    topic VARCHAR(255) NULL,
    mnemonic TEXT NOT NULL,
    explanation TEXT NULL,
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mnemonics_lecture (lecture_id),
    INDEX idx_mnemonics_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- VIVA QUESTIONS (one row per question — Stage 4)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS viva_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    topic VARCHAR(255) NULL,
    difficulty ENUM('easy','moderate','difficult') NOT NULL DEFAULT 'moderate',
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_viva_lecture (lecture_id),
    INDEX idx_viva_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CLINICAL CASES (one row per scenario — Stage 5)
-- questions: [{"question":"...","answer":"..."}]
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clinical_cases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    scenario TEXT NOT NULL,
    questions JSON NULL,
    diagnosis TEXT NULL,
    discussion TEXT NULL,
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    sort_order INT DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cases_lecture (lecture_id),
    INDEX idx_cases_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REVISION SHEETS (5-minute revision — Stage 6, one per lecture)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS revision_sheets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    content LONGTEXT NULL,
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_revision_lecture (lecture_id),
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_revision_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- VIDEO SIMULATIONS (teaching script pack — Stage 6, one per lecture)
-- scenes/timeline/diagrams stored as JSON structures.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS video_simulations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lecture_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    title VARCHAR(255) NULL,
    teaching_script LONGTEXT NULL,
    voice_over LONGTEXT NULL,
    scenes JSON NULL,
    timeline JSON NULL,
    diagrams JSON NULL,
    subtitles LONGTEXT NULL,
    camera_guidance LONGTEXT NULL,
    duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
    source ENUM('ai','manual') NOT NULL DEFAULT 'ai',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_video_lecture (lecture_id),
    FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_video_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- AI GENERATION LOGS (tokens, latency, retries, errors per call)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ai_generation_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NULL,
    stage_id INT UNSIGNED NULL,
    stage_key VARCHAR(50) NULL,
    model VARCHAR(100) NULL,
    status ENUM('success','error') NOT NULL DEFAULT 'success',
    prompt_tokens INT UNSIGNED NOT NULL DEFAULT 0,
    completion_tokens INT UNSIGNED NOT NULL DEFAULT 0,
    total_tokens INT UNSIGNED NOT NULL DEFAULT 0,
    latency_ms INT UNSIGNED NOT NULL DEFAULT 0,
    retries SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    estimated_cost DECIMAL(12,6) NOT NULL DEFAULT 0,
    error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES ai_generation_jobs(id) ON DELETE CASCADE,
    INDEX idx_ai_logs_job (job_id),
    INDEX idx_ai_logs_created (created_at)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
