-- Run once if DB already exists: mysql -u root nextgen_medics < database/migrations/001_course_class_schedule.sql

CREATE TABLE IF NOT EXISTS course_class_schedule (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday, 1=Monday, ... 6=Saturday',
    start_time TIME NOT NULL,
    duration_minutes INT UNSIGNED DEFAULT 60,
    title VARCHAR(255) NULL,
    meeting_url VARCHAR(500) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_schedule_course (course_id),
    INDEX idx_schedule_day (day_of_week)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS class_reminder_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT UNSIGNED NOT NULL,
    occurrence_date DATE NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    channel ENUM('whatsapp', 'in_app', 'email') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_class_reminder (schedule_id, occurrence_date, user_id, role, channel),
    FOREIGN KEY (schedule_id) REFERENCES course_class_schedule(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
