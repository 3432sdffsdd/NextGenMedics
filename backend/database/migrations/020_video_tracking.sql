-- Automatic video watch tracking & analytics (extends lecture_resources; keeps student_video_watches).
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS vt_video_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    lecture_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    duration_seconds DECIMAL(12,2) NOT NULL DEFAULT 0,
    watched_seconds DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_position DECIMAL(12,2) NOT NULL DEFAULT 0,
    last_position DECIMAL(12,2) NOT NULL DEFAULT 0,
    completion_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    status ENUM('not_started','watching','completed') NOT NULL DEFAULT 'not_started',
    play_count INT UNSIGNED NOT NULL DEFAULT 0,
    replay_count INT UNSIGNED NOT NULL DEFAULT 0,
    pause_count INT UNSIGNED NOT NULL DEFAULT 0,
    seek_forward_count INT UNSIGNED NOT NULL DEFAULT 0,
    seek_backward_count INT UNSIGNED NOT NULL DEFAULT 0,
    playback_speed DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    device_type VARCHAR(32) NULL,
    browser VARCHAR(64) NULL,
    os_name VARCHAR(64) NULL,
    ip_address VARCHAR(45) NULL,
    first_watched_at TIMESTAMP NULL,
    last_watched_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_vt_progress (student_id, resource_id),
    INDEX idx_vt_prog_student (student_id, status),
    INDEX idx_vt_prog_course (course_id, student_id),
    INDEX idx_vt_prog_lecture (lecture_id),
    CONSTRAINT fk_vt_prog_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vt_prog_resource FOREIGN KEY (resource_id) REFERENCES lecture_resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vt_video_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    lecture_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    event_type VARCHAR(40) NOT NULL,
    position_seconds DECIMAL(12,2) NULL,
    duration_seconds DECIMAL(12,2) NULL,
    watched_delta DECIMAL(12,2) NULL,
    playback_speed DECIMAL(4,2) NULL,
    meta_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vt_evt_student_time (student_id, created_at),
    INDEX idx_vt_evt_resource (resource_id, created_at),
    INDEX idx_vt_evt_type (event_type),
    CONSTRAINT fk_vt_evt_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vt_evt_resource FOREIGN KEY (resource_id) REFERENCES lecture_resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vt_watch_segments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    start_pos DECIMAL(12,2) NOT NULL DEFAULT 0,
    end_pos DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vt_seg_student_res (student_id, resource_id),
    CONSTRAINT fk_vt_seg_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vt_seg_resource FOREIGN KEY (resource_id) REFERENCES lecture_resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;
