-- NextGen Medics LMS Seed Data
USE nextgen_medics;

-- Roles
INSERT INTO roles (name, slug, description) VALUES
('Administrator', 'admin', 'Full system access'),
('Teacher', 'teacher', 'Course instructor access'),
('Student', 'student', 'Student access');

-- Default admin — run database/install.php to set passwords (Admin@123, Teacher@123, Student@123)
-- Temporary dev hash below equals "password" until install.php is run
INSERT INTO users (role_id, username, email, password, first_name, last_name, status) VALUES
(1, 'admin', 'admin@nextgenmedics.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'active');

-- Sample teacher — run install.php for Teacher@123
INSERT INTO users (role_id, username, email, password, first_name, last_name, status) VALUES
(2, 'dr.smith', 'teacher@nextgenmedics.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'active');

-- Sample student — run install.php for Student@123
INSERT INTO users (role_id, username, email, password, first_name, last_name, status) VALUES
(3, 'student1', 'student@nextgenmedics.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Doe', 'active');

-- Categories
INSERT INTO course_categories (name, slug, description, sort_order) VALUES
('Medical Sciences', 'medical-sciences', 'Core medical education courses', 1),
('Clinical Skills', 'clinical-skills', 'Hands-on clinical training', 2),
('Exam Preparation', 'exam-preparation', 'USMLE, PLAB, and other exam prep', 3);

-- Sample courses
INSERT INTO courses (category_id, teacher_id, title, slug, subtitle, short_description, description, duration, level, status, certificate_available, enrollment_status, fee, created_by) VALUES
(1, 2, 'Anatomy & Physiology Mastery', 'anatomy-physiology-mastery', 'Complete foundation for medical students', 'Master human anatomy and physiology with expert-led lectures.', 'A comprehensive course covering all major body systems with clinical correlations.', '12 weeks', 'beginner', 'published', 1, 'open', 299.00, 1),
(2, 2, 'Clinical Examination Skills', 'clinical-examination-skills', 'OSCE-ready clinical skills', 'Learn systematic clinical examination techniques.', 'Step-by-step guide to history taking and physical examination for OSCE success.', '8 weeks', 'intermediate', 'published', 1, 'open', 249.00, 1),
(3, 2, 'USMLE Step 1 Prep Intensive', 'usmle-step-1-prep', 'High-yield Step 1 preparation', 'Intensive USMLE Step 1 preparation program.', 'Cover high-yield topics, practice questions, and exam strategies.', '16 weeks', 'advanced', 'published', 1, 'open', 499.00, 1);

INSERT INTO course_teachers (course_id, teacher_id) VALUES (1, 2), (2, 2), (3, 2);
INSERT INTO course_enrollments (course_id, student_id, status, progress) VALUES (1, 3, 'active', 25.00);

-- Mentors
INSERT INTO mentors (name, title, specialty, bio, sort_order) VALUES
('Dr. Sarah Ahmed', 'MD, FCPS', 'Internal Medicine', 'Board-certified internist with 15 years of teaching experience.', 1),
('Dr. Michael Chen', 'MD, PhD', 'Anatomy', 'Anatomy professor specializing in clinical correlations.', 2);

-- Testimonials
INSERT INTO testimonials (student_name, course_name, content, rating, sort_order) VALUES
('Ahmed Khan', 'Anatomy & Physiology Mastery', 'This course transformed my understanding of anatomy. Highly recommended!', 5, 1),
('Fatima Ali', 'USMLE Step 1 Prep Intensive', 'Excellent high-yield content. Passed Step 1 on first attempt!', 5, 2);

-- Free resources
INSERT INTO free_resources (title, description, type, external_url, sort_order) VALUES
('Introduction to Medical Terminology', 'Free introductory lecture on medical terminology.', 'video', 'https://example.com/video1', 1),
('Study Guide: Cardiovascular System', 'Downloadable PDF study guide.', 'pdf', NULL, 2);

-- Announcements
INSERT INTO announcements (course_id, author_id, title, content, priority, published_at) VALUES
(NULL, 1, 'Welcome to NextGen Medics LMS', 'Welcome to our new learning platform. Explore courses and start learning today!', 'high', NOW()),
(1, 2, 'Module 2 Now Available', 'Module 2: Cardiovascular System is now live. Complete Module 1 before proceeding.', 'normal', NOW());

-- Settings
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'NextGen Medics', 'general'),
('site_email', 'info@nextgenmedics.com', 'general'),
('certificate_prefix', 'NGM', 'certificates'),
('max_login_attempts', '5', 'security');

-- Certificate template
INSERT INTO certificate_templates (name, template_html, is_default) VALUES
('Default Certificate', '<div class="certificate"><h1>Certificate of Completion</h1><p>This certifies that {{student_name}} has successfully completed {{course_title}}.</p><p>Issued: {{issue_date}}</p><p>Certificate No: {{certificate_number}}</p></div>', 1);
