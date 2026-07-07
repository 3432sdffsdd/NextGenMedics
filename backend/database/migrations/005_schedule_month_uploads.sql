-- ============================================================
-- Migration 005: Track monthly Excel schedule uploads per course
-- Safe to run once against an existing nextgen_medics database.
-- ============================================================

USE nextgen_medics;

CREATE TABLE IF NOT EXISTS schedule_month_uploads (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id   INT UNSIGNED NOT NULL,
    month_year  CHAR(7) NOT NULL COMMENT 'YYYY-MM',
    file_path   VARCHAR(500) NULL,
    file_name   VARCHAR(255) NULL,
    row_count   INT UNSIGNED DEFAULT 0,
    uploaded_by INT UNSIGNED NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_schedule_month (course_id, month_year),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_smu_course (course_id)
) ENGINE=InnoDB;
