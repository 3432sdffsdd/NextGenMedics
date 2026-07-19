-- Fix Lecture Videos / study-material 500 (missing watch table).
-- Safe: creates empty table only. Does not change courses/lectures/users.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS student_video_watches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    watched TINYINT(1) NOT NULL DEFAULT 1,
    watched_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_svw_student_resource (student_id, resource_id),
    INDEX idx_svw_student (student_id),
    INDEX idx_svw_resource (resource_id),
    INDEX idx_svw_watched (student_id, watched)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
