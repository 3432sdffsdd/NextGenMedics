-- ============================================================
-- Migration 002: Batches + date-based Class Schedule
-- Adds proper batch grouping and a rich, date-based lecture
-- schedule table (distinct from the recurring course_class_schedule).
-- Safe to run multiple times (IF NOT EXISTS guards).
-- ============================================================

-- Batches (a course can run multiple batches/cohorts) ---------
CREATE TABLE IF NOT EXISTS batches (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id   INT UNSIGNED NOT NULL,
    name        VARCHAR(150) NOT NULL,
    code        VARCHAR(50)  NULL,
    start_date  DATE NULL,
    end_date    DATE NULL,
    is_active   TINYINT(1) DEFAULT 1,
    created_by  INT UNSIGNED NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)   ON DELETE SET NULL,
    INDEX idx_batches_course (course_id)
) ENGINE=InnoDB;

-- Students assigned to a batch --------------------------------
CREATE TABLE IF NOT EXISTS batch_students (
    batch_id    INT UNSIGNED NOT NULL,
    student_id  INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (batch_id, student_id),
    FOREIGN KEY (batch_id)   REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- Date-based lecture schedule ---------------------------------
CREATE TABLE IF NOT EXISTS class_schedule (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id        INT UNSIGNED NOT NULL,
    batch_id         INT UNSIGNED NULL,
    teacher_id       INT UNSIGNED NULL,
    subject          VARCHAR(150) NULL,
    lecture_title    VARCHAR(255) NOT NULL,
    lecture_number   INT UNSIGNED NULL,
    topic_covered    VARCHAR(500) NULL,
    description      TEXT NULL,
    class_date       DATE NOT NULL,
    start_time       TIME NOT NULL,
    end_time         TIME NOT NULL,
    duration_minutes INT UNSIGNED NULL,
    meeting_link     VARCHAR(500) NULL,
    recording_link   VARCHAR(500) NULL,
    attachment_path  VARCHAR(500) NULL,
    attachment_name  VARCHAR(255) NULL,
    remarks          VARCHAR(500) NULL,
    status           ENUM('upcoming','live','completed','cancelled','postponed','rescheduled') DEFAULT 'upcoming',
    is_status_locked TINYINT(1) DEFAULT 0,
    rescheduled_from_id INT UNSIGNED NULL,
    created_by       INT UNSIGNED NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id)   REFERENCES batches(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id)   ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)   ON DELETE SET NULL,
    INDEX idx_cs_course (course_id),
    INDEX idx_cs_date (class_date),
    INDEX idx_cs_teacher (teacher_id),
    INDEX idx_cs_batch (batch_id),
    INDEX idx_cs_status (status)
) ENGINE=InnoDB;
