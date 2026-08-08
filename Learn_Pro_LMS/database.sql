CREATE DATABASE IF NOT EXISTS codvion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE codvion;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `quiz_attempts`;
DROP TABLE IF EXISTS `quiz_questions`;
DROP TABLE IF EXISTS `quizzes`;
DROP TABLE IF EXISTS `lesson_progress`;
DROP TABLE IF EXISTS `enrollments`;
DROP TABLE IF EXISTS `lessons`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `login_system`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `login_system` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) NOT NULL,
  `user_name` varchar(80) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `profile_image` varchar(500) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
  `address_line` varchar(220) NOT NULL DEFAULT '',
  `city` varchar(80) NOT NULL DEFAULT '',
  `country` varchar(80) NOT NULL DEFAULT '',
  `education_level` varchar(120) NOT NULL DEFAULT '',
  `profession` varchar(120) NOT NULL DEFAULT '',
  `learning_goal` varchar(220) NOT NULL DEFAULT '',
  `bio` text DEFAULT NULL,
  `emergency_contact_name` varchar(120) NOT NULL DEFAULT '',
  `emergency_contact_phone` varchar(30) NOT NULL DEFAULT '',
  `linkedin_url` varchar(220) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','instructor','student') NOT NULL DEFAULT 'student',
  `status` enum('active','pending','blocked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_login_user_name` (`user_name`),
  UNIQUE KEY `uq_login_email` (`email`),
  UNIQUE KEY `uq_login_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) NOT NULL,
  `category` varchar(90) NOT NULL,
  `level` enum('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  `duration` varchar(60) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cover_image` varchar(500) NOT NULL,
  `description` text NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `instructor_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_courses_instructor` (`instructor_id`),
  KEY `idx_courses_status` (`status`),
  CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `login_system` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lessons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `lesson_order` int(10) unsigned NOT NULL DEFAULT 1,
  `video_url` varchar(700) NOT NULL,
  `duration_minutes` int(10) unsigned NOT NULL DEFAULT 10,
  `content` text NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lessons_course` (`course_id`),
  CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `enrollments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `progress_percent` int(10) unsigned NOT NULL DEFAULT 0,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enrollment_user_course` (`user_id`,`course_id`),
  KEY `idx_enrollments_course` (`course_id`),
  CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) REFERENCES `login_system` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lesson_progress` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `lesson_id` int(10) unsigned NOT NULL,
  `watch_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `can_continue` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_progress_user_lesson` (`user_id`,`lesson_id`),
  KEY `idx_progress_course` (`course_id`),
  KEY `fk_progress_lesson` (`lesson_id`),
  CONSTRAINT `fk_progress_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `login_system` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quizzes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `instructions` text NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `duration_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `total_marks` int(10) unsigned NOT NULL DEFAULT 10,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_quizzes_window` (`start_time`,`end_time`),
  KEY `idx_quizzes_course` (`course_id`),
  CONSTRAINT `fk_quizzes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` int(10) unsigned NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` enum('A','B','C','D') NOT NULL,
  `marks` int(10) unsigned NOT NULL DEFAULT 1,
  `question_order` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_quiz_questions_quiz` (`quiz_id`),
  CONSTRAINT `fk_quiz_questions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `score` int(10) unsigned NOT NULL DEFAULT 0,
  `total_marks` int(10) unsigned NOT NULL DEFAULT 0,
  `time_spent_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quiz_attempt_user` (`quiz_id`,`user_id`),
  KEY `idx_quiz_attempt_user` (`user_id`),
  CONSTRAINT `fk_quiz_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quiz_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `login_system` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `login_system` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `login_system` (`id`, `full_name`, `user_name`, `email`, `phone`, `profile_image`, `date_of_birth`, `gender`, `address_line`, `city`, `country`, `education_level`, `profession`, `learning_goal`, `bio`, `emergency_contact_name`, `emergency_contact_phone`, `linkedin_url`, `password_hash`, `role`, `status`, `created_at`, `last_login`) VALUES
('1', 'CodVion', 'codvion', 'admin@learnpro.com', '03214086800', 'uploads/profiles/user_1_1780411316.png', '2007-07-12', 'Male', '', '', '', '', '', '', '', '', '', '', '$2y$10$vN/yZBAKA9cC4sdpIFJCxug8HoXkjPgJbZHEHr/hnAHeJMn3P5gsq', 'admin', 'active', '2026-06-02 12:09:57', '2026-06-03 00:03:31'),
('6', 'Instructor', 'instructor', 'instructor@learnpro.com', '03123456789', NULL, NULL, NULL, '', '', '', '', '', '', NULL, '', '', '', '$2y$10$RXCUF/EaU47weGjpBGTjOuzMJBx/D.fbSKM1tIv2phE7rtBTR/Tba', 'instructor', 'active', '2026-06-02 23:52:34', '2026-06-03 08:26:06'),
('7', 'Student', 'student', 'student@learnpro.com', '03000000000', NULL, NULL, NULL, '', '', '', '', '', '', NULL, '', '', '', '$2y$10$ijVPATPLgCPOKYyjB5DeHuWQzExNSRg3V7e.y4t/jzQqVuaUcjLoi', 'student', 'active', '2026-06-03 00:01:10', '2026-06-03 00:01:22');

INSERT INTO `courses` (`id`, `title`, `category`, `level`, `duration`, `price`, `cover_image`, `description`, `status`, `instructor_id`, `created_at`, `updated_at`) VALUES
('2', 'Full Stack Web Development Fundamentals', 'Web Development', 'Beginner', '10 lessons / 18 hours', '0.00', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1400&q=80', 'Build a strong foundation in HTML, CSS, JavaScript, PHP, MySQL, Git, and full stack project workflow.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('3', 'Frontend Engineering with HTML CSS JavaScript', 'Frontend Development', 'Beginner', '10 lessons / 15 hours', '0.00', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1400&q=80', 'Learn professional frontend structure, responsive design, JavaScript interactivity, and polished user interface delivery.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('4', 'JavaScript and React Application Development', 'JavaScript', 'Intermediate', '10 lessons / 24 hours', '0.00', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1400&q=80', 'Go deeper into JavaScript fundamentals, DOM workflows, React components, state, and application thinking.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('5', 'PHP and MySQL Backend Development', 'Backend Development', 'Intermediate', '10 lessons / 20 hours', '0.00', 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1400&q=80', 'Develop backend skills with PHP, MySQL, authentication, CRUD flows, server-side validation, and database-driven pages.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('6', 'Python Programming for IT Automation', 'Programming', 'Beginner', '10 lessons / 22 hours', '0.00', 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?auto=format&fit=crop&w=1400&q=80', 'Use Python for programming foundations, file handling, scripts, data processing, and practical IT automation tasks.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('7', 'SQL Databases and Data Analysis', 'Databases', 'Beginner', '10 lessons / 18 hours', '0.00', 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?auto=format&fit=crop&w=1400&q=80', 'Understand relational databases, SQL queries, joins, filtering, reporting, and data analysis workflows for IT systems.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('8', 'Computer Networking and IT Support', 'IT Support', 'Beginner', '10 lessons / 21 hours', '0.00', 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1400&q=80', 'Learn networking foundations, troubleshooting flow, Linux command line basics, support documentation, and IT operations.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('9', 'Linux Administration and Command Line', 'Systems Administration', 'Intermediate', '10 lessons / 19 hours', '0.00', 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?auto=format&fit=crop&w=1400&q=80', 'Practice Linux commands, users, permissions, processes, package management, shell workflow, and server administration basics.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('10', 'Cloud Computing and DevOps Foundations', 'Cloud and DevOps', 'Intermediate', '10 lessons / 25 hours', '0.00', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1400&q=80', 'Start cloud and DevOps with AWS fundamentals, Linux, Git, Docker, Kubernetes, Terraform, and deployment workflow.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34'),
('11', 'Cybersecurity and Ethical Hacking Basics', 'Cybersecurity', 'Beginner', '10 lessons / 28 hours', '0.00', 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1400&q=80', 'Learn defensive security concepts, networking, Linux, Kali basics, ethical hacking workflow, and secure application thinking.', 'published', '6', '2026-06-02 23:47:03', '2026-06-02 23:52:34');

INSERT INTO `lessons` (`id`, `course_id`, `title`, `lesson_order`, `video_url`, `duration_minutes`, `content`, `status`, `created_at`, `updated_at`) VALUES
('11', '2', 'Web Environment and Developer Tools', '1', 'https://www.youtube.com/watch?v=UB1O30fR-EE', '60', 'Study \"Web Environment and Developer Tools\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('12', '2', 'HTML Page Structure', '2', 'https://www.youtube.com/watch?v=qz0aGYrrlhU', '120', 'Study \"HTML Page Structure\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('13', '2', 'CSS Layout and Responsive Design', '3', 'https://www.youtube.com/watch?v=yfoY53QXEnI', '85', 'Study \"CSS Layout and Responsive Design\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('14', '2', 'CSS Components and Visual Polish', '4', 'https://www.youtube.com/watch?v=1PnVor36_40', '20', 'Study \"CSS Components and Visual Polish\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('15', '2', 'JavaScript Programming Basics', '5', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', '48', 'Study \"JavaScript Programming Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('16', '2', 'DOM Events and Interactivity', '6', 'https://www.youtube.com/watch?v=hdI2bqOjy3c', '100', 'Study \"DOM Events and Interactivity\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('17', '2', 'React Component Thinking', '7', 'https://www.youtube.com/watch?v=bMknfKXIFA8', '660', 'Study \"React Component Thinking\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('18', '2', 'PHP Backend Foundations', '8', 'https://www.youtube.com/watch?v=OK_JCtrrv-c', '240', 'Study \"PHP Backend Foundations\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('19', '2', 'MySQL Database Basics', '9', 'https://www.youtube.com/watch?v=HXV3zeQKqGY', '240', 'Study \"MySQL Database Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('20', '2', 'Git and Final Web Project Review', '10', 'https://www.youtube.com/watch?v=RGOj5yH7evk', '70', 'Study \"Git and Final Web Project Review\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('21', '3', 'Modern HTML Semantics', '1', 'https://www.youtube.com/watch?v=qz0aGYrrlhU', '120', 'Study \"Modern HTML Semantics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('22', '3', 'Accessible Page Sections', '2', 'https://www.youtube.com/watch?v=UB1O30fR-EE', '60', 'Study \"Accessible Page Sections\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('23', '3', 'CSS Box Model and Spacing', '3', 'https://www.youtube.com/watch?v=yfoY53QXEnI', '85', 'Study \"CSS Box Model and Spacing\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('24', '3', 'Responsive Layout Patterns', '4', 'https://www.youtube.com/watch?v=1PnVor36_40', '20', 'Study \"Responsive Layout Patterns\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('25', '3', 'JavaScript Variables and Logic', '5', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', '48', 'Study \"JavaScript Variables and Logic\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('26', '3', 'DOM Manipulation Basics', '6', 'https://www.youtube.com/watch?v=hdI2bqOjy3c', '100', 'Study \"DOM Manipulation Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('27', '3', 'Forms and Client Validation', '7', 'https://www.youtube.com/watch?v=PkZNo7MFNFg', '210', 'Study \"Forms and Client Validation\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('28', '3', 'Reusable UI Behavior', '8', 'https://www.youtube.com/watch?v=jS4aFq5-91M', '420', 'Study \"Reusable UI Behavior\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('29', '3', 'React Introduction for Frontend', '9', 'https://www.youtube.com/watch?v=Ke90Tje7VS0', '75', 'Study \"React Introduction for Frontend\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('30', '3', 'Frontend Project Build', '10', 'https://www.youtube.com/watch?v=bMknfKXIFA8', '660', 'Study \"Frontend Project Build\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('31', '4', 'JavaScript Syntax Review', '1', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', '48', 'Study \"JavaScript Syntax Review\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('32', '4', 'Control Flow and Data Structures', '2', 'https://www.youtube.com/watch?v=hdI2bqOjy3c', '100', 'Study \"Control Flow and Data Structures\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('33', '4', 'Functions and Scope', '3', 'https://www.youtube.com/watch?v=PkZNo7MFNFg', '210', 'Study \"Functions and Scope\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('34', '4', 'DOM Events and UI State', '4', 'https://www.youtube.com/watch?v=jS4aFq5-91M', '420', 'Study \"DOM Events and UI State\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('35', '4', 'Async JavaScript Overview', '5', 'https://www.youtube.com/watch?v=Ke90Tje7VS0', '75', 'Study \"Async JavaScript Overview\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('36', '4', 'React Components and Props', '6', 'https://www.youtube.com/watch?v=bMknfKXIFA8', '660', 'Study \"React Components and Props\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('37', '4', 'React State and Events', '7', 'https://www.youtube.com/watch?v=TlB_eWDSMt4', '65', 'Study \"React State and Events\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('38', '4', 'Connecting Frontend to APIs', '8', 'https://www.youtube.com/watch?v=Oe421EPjeBE', '480', 'Study \"Connecting Frontend to APIs\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('39', '4', 'Git Workflow for JS Apps', '9', 'https://www.youtube.com/watch?v=RGOj5yH7evk', '70', 'Study \"Git Workflow for JS Apps\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('40', '4', 'Packaging and Deployment Basics', '10', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Packaging and Deployment Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('41', '5', 'PHP Syntax and Request Flow', '1', 'https://www.youtube.com/watch?v=OK_JCtrrv-c', '240', 'Study \"PHP Syntax and Request Flow\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('42', '5', 'Forms and Server Validation', '2', 'https://www.youtube.com/watch?v=HXV3zeQKqGY', '240', 'Study \"Forms and Server Validation\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('43', '5', 'Sessions and Authentication', '3', 'https://www.youtube.com/watch?v=7S_tz1z_5bA', '180', 'Study \"Sessions and Authentication\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('44', '5', 'MySQL Tables and Relations', '4', 'https://www.youtube.com/watch?v=UB1O30fR-EE', '60', 'Study \"MySQL Tables and Relations\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('45', '5', 'Prepared Statements and Security', '5', 'https://www.youtube.com/watch?v=yfoY53QXEnI', '85', 'Study \"Prepared Statements and Security\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('46', '5', 'CRUD Application Structure', '6', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', '48', 'Study \"CRUD Application Structure\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('47', '5', 'Role Based Access Patterns', '7', 'https://www.youtube.com/watch?v=pKd0Rpw7O48', '60', 'Study \"Role Based Access Patterns\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('48', '5', 'Backend Notifications Flow', '8', 'https://www.youtube.com/watch?v=SWYqp7iY_Tc', '32', 'Study \"Backend Notifications Flow\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('49', '5', 'Deploying PHP with Git', '9', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Deploying PHP with Git\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('50', '5', 'Production Checklist for Backends', '10', 'https://www.youtube.com/watch?v=NhDYbskXRgc', '840', 'Study \"Production Checklist for Backends\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('51', '6', 'Python Setup and Syntax', '1', 'https://www.youtube.com/watch?v=kqtD5dpn9C8', '60', 'Study \"Python Setup and Syntax\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('52', '6', 'Variables and Control Flow', '2', 'https://www.youtube.com/watch?v=rfscVS0vtbw', '270', 'Study \"Variables and Control Flow\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('53', '6', 'Lists Dictionaries and Tuples', '3', 'https://www.youtube.com/watch?v=_uQrJ0TkZlc', '360', 'Study \"Lists Dictionaries and Tuples\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('54', '6', 'Functions and Modules', '4', 'https://www.youtube.com/watch?v=8DvywoWv6fI', '840', 'Study \"Functions and Modules\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('55', '6', 'File Handling for IT Tasks', '5', 'https://www.youtube.com/watch?v=ZtqBQ68cfJc', '300', 'Study \"File Handling for IT Tasks\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('56', '6', 'Working with Data Inputs', '6', 'https://www.youtube.com/watch?v=RGOj5yH7evk', '70', 'Study \"Working with Data Inputs\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('57', '6', 'SQL with Python Thinking', '7', 'https://www.youtube.com/watch?v=HXV3zeQKqGY', '240', 'Study \"SQL with Python Thinking\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('58', '6', 'Automation Scripts and CLI Use', '8', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Automation Scripts and CLI Use\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('59', '6', 'Version Control for Python', '9', 'https://www.youtube.com/watch?v=NhDYbskXRgc', '840', 'Study \"Version Control for Python\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('60', '6', 'Python IT Automation Project', '10', 'https://www.youtube.com/watch?v=qiQR5rTSshw', '540', 'Study \"Python IT Automation Project\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('61', '7', 'Database Concepts and Tables', '1', 'https://www.youtube.com/watch?v=HXV3zeQKqGY', '240', 'Study \"Database Concepts and Tables\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('62', '7', 'SELECT Queries and Filtering', '2', 'https://www.youtube.com/watch?v=7S_tz1z_5bA', '180', 'Study \"SELECT Queries and Filtering\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('63', '7', 'Sorting Grouping and Aggregates', '3', 'https://www.youtube.com/watch?v=8DvywoWv6fI', '840', 'Study \"Sorting Grouping and Aggregates\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('64', '7', 'Joins and Relationships', '4', 'https://www.youtube.com/watch?v=rfscVS0vtbw', '270', 'Study \"Joins and Relationships\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('65', '7', 'Subqueries and Views', '5', 'https://www.youtube.com/watch?v=pKd0Rpw7O48', '60', 'Study \"Subqueries and Views\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('66', '7', 'MySQL Practical Workflow', '6', 'https://www.youtube.com/watch?v=OK_JCtrrv-c', '240', 'Study \"MySQL Practical Workflow\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('67', '7', 'Data Cleaning with SQL', '7', 'https://www.youtube.com/watch?v=RGOj5yH7evk', '70', 'Study \"Data Cleaning with SQL\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('68', '7', 'Reports and Dashboard Queries', '8', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Reports and Dashboard Queries\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('69', '7', 'Connecting Apps to Databases', '9', 'https://www.youtube.com/watch?v=NhDYbskXRgc', '840', 'Study \"Connecting Apps to Databases\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('70', '7', 'Database Project Review', '10', 'https://www.youtube.com/watch?v=ZtqBQ68cfJc', '300', 'Study \"Database Project Review\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('71', '8', 'Networking Models and Devices', '1', 'https://www.youtube.com/watch?v=qiQR5rTSshw', '540', 'Study \"Networking Models and Devices\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('72', '8', 'IP Addressing and Subnets', '2', 'https://www.youtube.com/watch?v=ZtqBQ68cfJc', '300', 'Study \"IP Addressing and Subnets\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('73', '8', 'DNS DHCP and Routing Basics', '3', 'https://www.youtube.com/watch?v=sWbUDq4S6Y8', '70', 'Study \"DNS DHCP and Routing Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('74', '8', 'Linux Command Line for Support', '4', 'https://www.youtube.com/watch?v=iwolPf6kN-k', '360', 'Study \"Linux Command Line for Support\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('75', '8', 'System Troubleshooting Workflow', '5', 'https://www.youtube.com/watch?v=Wvf0mBNGjXY', '166', 'Study \"System Troubleshooting Workflow\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('76', '8', 'Virtualization and Lab Setup', '6', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Virtualization and Lab Setup\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('77', '8', 'Documentation and Ticket Handling', '7', 'https://www.youtube.com/watch?v=SWYqp7iY_Tc', '32', 'Study \"Documentation and Ticket Handling\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('78', '8', 'Cloud Support Fundamentals', '8', 'https://www.youtube.com/watch?v=NhDYbskXRgc', '840', 'Study \"Cloud Support Fundamentals\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('79', '8', 'Security Awareness for Support', '9', 'https://www.youtube.com/watch?v=coQ5dg8wM2o', '300', 'Study \"Security Awareness for Support\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('80', '8', 'IT Support Capstone Review', '10', 'https://www.youtube.com/watch?v=kqtD5dpn9C8', '60', 'Study \"IT Support Capstone Review\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('81', '9', 'Linux Orientation and Setup', '1', 'https://www.youtube.com/watch?v=ZtqBQ68cfJc', '300', 'Study \"Linux Orientation and Setup\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('82', '9', 'Terminal Navigation Commands', '2', 'https://www.youtube.com/watch?v=sWbUDq4S6Y8', '70', 'Study \"Terminal Navigation Commands\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('83', '9', 'Files Directories and Permissions', '3', 'https://www.youtube.com/watch?v=iwolPf6kN-k', '360', 'Study \"Files Directories and Permissions\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('84', '9', 'Users Groups and Sudo', '4', 'https://www.youtube.com/watch?v=Wvf0mBNGjXY', '166', 'Study \"Users Groups and Sudo\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('85', '9', 'Processes Services and Logs', '5', 'https://www.youtube.com/watch?v=qiQR5rTSshw', '540', 'Study \"Processes Services and Logs\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('86', '9', 'Package Management Basics', '6', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Package Management Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('87', '9', 'Shell Productivity and Pipes', '7', 'https://www.youtube.com/watch?v=RGOj5yH7evk', '70', 'Study \"Shell Productivity and Pipes\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('88', '9', 'Networking Commands on Linux', '8', 'https://www.youtube.com/watch?v=coQ5dg8wM2o', '300', 'Study \"Networking Commands on Linux\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('89', '9', 'Linux Server Administration', '9', 'https://www.youtube.com/watch?v=NhDYbskXRgc', '840', 'Study \"Linux Server Administration\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('90', '9', 'Linux Operations Project', '10', 'https://www.youtube.com/watch?v=SLB_c_ayRMo', '120', 'Study \"Linux Operations Project\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('91', '10', 'DevOps Roadmap and Culture', '1', 'https://www.youtube.com/watch?v=Wvf0mBNGjXY', '166', 'Study \"DevOps Roadmap and Culture\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('92', '10', 'Linux and YAML Prerequisites', '2', 'https://www.youtube.com/watch?v=ZtqBQ68cfJc', '300', 'Study \"Linux and YAML Prerequisites\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('93', '10', 'Git Workflow for Teams', '3', 'https://www.youtube.com/watch?v=RGOj5yH7evk', '70', 'Study \"Git Workflow for Teams\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('94', '10', 'Docker Containers Basics', '4', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Docker Containers Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('95', '10', 'Docker Compose and Images', '5', 'https://www.youtube.com/watch?v=9zUHg7xjIqQ', '120', 'Study \"Docker Compose and Images\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('96', '10', 'Kubernetes Concepts', '6', 'https://www.youtube.com/watch?v=pTFZFxd4hOI', '240', 'Study \"Kubernetes Concepts\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('97', '10', 'Terraform Infrastructure Basics', '7', 'https://www.youtube.com/watch?v=SLB_c_ayRMo', '120', 'Study \"Terraform Infrastructure Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('98', '10', 'AWS Cloud Fundamentals', '8', 'https://www.youtube.com/watch?v=NhDYbskXRgc', '840', 'Study \"AWS Cloud Fundamentals\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('99', '10', 'Deploying a Web Application', '9', 'https://www.youtube.com/watch?v=Oe421EPjeBE', '480', 'Study \"Deploying a Web Application\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('100', '10', 'DevOps Capstone Workflow', '10', 'https://www.youtube.com/watch?v=qiQR5rTSshw', '540', 'Study \"DevOps Capstone Workflow\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('101', '11', 'Cybersecurity Mindset and Ethics', '1', 'https://www.youtube.com/watch?v=qiQR5rTSshw', '540', 'Study \"Cybersecurity Mindset and Ethics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('102', '11', 'Networking for Security', '2', 'https://www.youtube.com/watch?v=ZtqBQ68cfJc', '300', 'Study \"Networking for Security\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('103', '11', 'Linux Commands for Security Work', '3', 'https://www.youtube.com/watch?v=coQ5dg8wM2o', '300', 'Study \"Linux Commands for Security Work\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('104', '11', 'Kali Linux Lab Setup', '4', 'https://www.youtube.com/watch?v=3Kq1MIfTWCE', '720', 'Study \"Kali Linux Lab Setup\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('105', '11', 'Reconnaissance Basics', '5', 'https://www.youtube.com/watch?v=rfscVS0vtbw', '270', 'Study \"Reconnaissance Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('106', '11', 'Web Security Concepts', '6', 'https://www.youtube.com/watch?v=HXV3zeQKqGY', '240', 'Study \"Web Security Concepts\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('107', '11', 'SQL Injection Awareness', '7', 'https://www.youtube.com/watch?v=OK_JCtrrv-c', '240', 'Study \"SQL Injection Awareness\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('108', '11', 'Secure Backend Practices', '8', 'https://www.youtube.com/watch?v=3c-iBn73dDE', '166', 'Study \"Secure Backend Practices\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('109', '11', 'Cloud Security Basics', '9', 'https://www.youtube.com/watch?v=NhDYbskXRgc', '840', 'Study \"Cloud Security Basics\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL),
('110', '11', 'Security Project Review', '10', 'https://www.youtube.com/watch?v=RGOj5yH7evk', '70', 'Study \"Security Project Review\" with a practical long-form YouTube lesson. Complete the required watch time before moving to the next topic.', 'published', '2026-06-02 23:47:03', NULL);

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `status`, `progress_percent`, `enrolled_at`, `completed_at`) VALUES
('4', '7', '11', 'active', '10', '2026-06-03 00:01:57', NULL);

INSERT INTO `lesson_progress` (`id`, `user_id`, `course_id`, `lesson_id`, `watch_seconds`, `can_continue`, `completed_at`, `updated_at`) VALUES
('13', '7', '11', '101', '5', '1', '2026-06-03 00:02:16', '2026-06-03 00:02:16');

INSERT INTO `quizzes` (`id`, `course_id`, `title`, `instructions`, `start_time`, `end_time`, `duration_seconds`, `total_marks`, `status`, `created_at`, `updated_at`) VALUES
('14', '2', 'Full Stack Fundamentals Quiz', 'Answer these questions after completing the core full stack lessons.', '2026-06-01 23:47:03', '2026-08-31 23:47:03', '450', '5', 'published', '2026-06-02 23:47:03', NULL),
('15', '3', 'Frontend UI Quiz', 'Review frontend structure, styling, responsiveness, and UI behavior.', '2026-06-01 23:47:03', '2026-08-31 23:47:03', '450', '5', 'published', '2026-06-02 23:47:03', NULL),
('16', '4', 'JavaScript React Quiz', 'Test JavaScript fundamentals and React application concepts.', '2026-06-01 23:47:03', '2026-08-31 23:47:03', '450', '5', 'published', '2026-06-02 23:47:03', NULL),
('17', '5', 'PHP MySQL Backend Quiz', 'Check backend, database, authentication, and CRUD understanding.', '2026-06-01 23:47:03', '2026-08-31 23:47:03', '450', '5', 'published', '2026-06-02 23:47:03', NULL),
('18', '6', 'Python Automation Quiz', 'Answer these questions about Python basics and IT automation workflow.', '2026-06-01 23:47:03', '2026-08-31 23:47:03', '450', '5', 'published', '2026-06-02 23:47:03', NULL),
('19', '10', 'Cloud DevOps Quiz', 'Review cloud, containers, infrastructure, and DevOps basics.', '2026-06-01 23:47:03', '2026-08-31 23:47:03', '450', '5', 'published', '2026-06-02 23:47:03', NULL);

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `marks`, `question_order`) VALUES
('19', '14', 'Which layer handles structure in a web page?', 'CSS', 'HTML', 'SQL', 'Docker', 'B', '1', '1'),
('20', '14', 'Which technology adds browser interactivity?', 'JavaScript', 'MySQL', 'Apache logs', 'SSH', 'A', '1', '2'),
('21', '14', 'Prepared statements mainly help prevent what?', 'SQL injection', 'Image compression', 'CSS overflow', 'DNS delay', 'A', '1', '3'),
('22', '14', 'Which tool is used to track code history?', 'Git', 'FTP only', 'Paint', 'Task Manager', 'A', '1', '4'),
('23', '14', 'A full stack project normally includes what?', 'Frontend, backend, and database work', 'Only one image', 'Only a spreadsheet', 'Only a PDF', 'A', '1', '5'),
('24', '15', 'Semantic HTML improves what?', 'Accessibility and structure', 'Database speed only', 'Server RAM only', 'Password hashing only', 'A', '1', '1'),
('25', '15', 'Which CSS layout model is best for one-dimensional alignment?', 'Flexbox', 'SQL joins', 'PHP sessions', 'DNS', 'A', '1', '2'),
('26', '15', 'Responsive design should adapt to what?', 'Different screen sizes', 'Only admin users', 'Only localhost', 'Only images', 'A', '1', '3'),
('27', '15', 'Client-side form validation runs where?', 'In the browser', 'Inside the router only', 'In a printer', 'In DNS records', 'A', '1', '4'),
('28', '15', 'DOM manipulation changes what?', 'Page elements in the browser', 'Database schema directly', 'Server hardware', 'Email MX records', 'A', '1', '5'),
('29', '16', 'React applications are commonly built with what?', 'Components', 'Only SQL tables', 'Only ZIP files', 'Only raw images', 'A', '1', '1'),
('30', '16', 'Async JavaScript helps with what?', 'Waiting for data without blocking UI flow', 'Changing monitor brightness', 'Formatting a hard drive', 'Printing invoices only', 'A', '1', '2'),
('31', '16', 'State in a UI stores what?', 'Changing screen data', 'Only DNS records', 'Only CSS comments', 'Only server BIOS settings', 'A', '1', '3'),
('32', '16', 'Events are used for what?', 'Responding to user actions', 'Encrypting hard drives automatically', 'Creating database indexes only', 'Buying domains', 'A', '1', '4'),
('33', '16', 'Props in React are used to pass what?', 'Data into components', 'SQL passwords into CSS', 'Images into DNS', 'Ports into HDMI', 'A', '1', '5'),
('34', '17', 'PHP code usually runs where?', 'On the server', 'Only in CSS', 'Inside the monitor', 'Inside DNS cache', 'A', '1', '1'),
('35', '17', 'Sessions are commonly used for what?', 'Remembering logged-in users', 'Cropping images only', 'Changing fonts only', 'Creating USB drives', 'A', '1', '2'),
('36', '17', 'CRUD stands for what?', 'Create, Read, Update, Delete', 'Copy, Render, Upload, Download', 'Code, Review, Undo, Deploy', 'Cache, Route, Use, Debug', 'A', '1', '3'),
('37', '17', 'A foreign key helps define what?', 'A relationship between tables', 'A button color', 'A video duration', 'A browser tab name', 'A', '1', '4'),
('38', '17', 'Server-side validation is important because what?', 'Client input cannot be trusted alone', 'CSS cannot load images', 'Videos need thumbnails', 'Git needs Wi-Fi only', 'A', '1', '5'),
('39', '18', 'Python scripts can help automate what?', 'Repeated IT tasks', 'Only keyboard color', 'Only monitor refresh rate', 'Only HTML comments', 'A', '1', '1'),
('40', '18', 'A list stores what?', 'Multiple ordered values', 'Only one DNS server', 'Only a password hash', 'Only a CSS selector', 'A', '1', '2'),
('41', '18', 'Modules help Python code by doing what?', 'Organizing reusable features', 'Deleting all files automatically', 'Changing IP addresses randomly', 'Replacing SQL tables', 'A', '1', '3'),
('42', '18', 'File handling is useful for what?', 'Reading and writing data files', 'Making coffee', 'Changing router firmware by magic', 'Only making icons', 'A', '1', '4'),
('43', '18', 'Version control is useful for Python projects because what?', 'It tracks changes safely', 'It disables all errors', 'It removes testing', 'It hides source files', 'A', '1', '5'),
('44', '19', 'Docker packages applications into what?', 'Containers', 'Spreadsheets', 'CSS files only', 'DNS names only', 'A', '1', '1'),
('45', '19', 'Infrastructure as Code means what?', 'Managing infrastructure with versioned configuration', 'Drawing servers in paint', 'Sending passwords by email', 'Only buying hardware', 'A', '1', '2'),
('46', '19', 'CI/CD helps teams do what?', 'Build, test, and deploy more reliably', 'Avoid code reviews forever', 'Disable backups', 'Remove logs', 'A', '1', '3'),
('47', '19', 'Cloud services commonly provide what?', 'Scalable compute and storage', 'Only local notebooks', 'Only CSS colors', 'Only images', 'A', '1', '4'),
('48', '19', 'Kubernetes is commonly used to manage what?', 'Containers and workloads', 'Photos only', 'Browser bookmarks', 'Printer queues only', 'A', '1', '5');

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
('1', '1', 'Welcome to LearnPro LMS', 'Your account is ready. Explore your dashboard and continue learning with a focused, professional experience.', 'success', '1', '2026-06-02 12:09:57'),
('22', '1', 'Profile Updated', 'Your detailed profile information has been updated successfully.', 'success', '1', '2026-06-02 19:41:56'),
('23', '1', 'Profile Updated', 'Your detailed profile information has been updated successfully.', 'success', '1', '2026-06-02 19:42:17'),
('32', '1', 'Profile Updated', 'Your detailed profile information has been updated successfully.', 'success', '1', '2026-06-02 23:55:43'),
('33', '1', 'Profile Updated', 'Your detailed profile information has been updated successfully.', 'success', '1', '2026-06-02 23:56:11'),
('34', '7', 'Welcome to LearnPro LMS', 'Your account has been created successfully. Your dashboard is ready.', 'success', '1', '2026-06-03 00:01:10'),
('35', '7', 'Course Enrollment Confirmed', 'You are now enrolled in Cybersecurity and Ethical Hacking Basics.', 'success', '1', '2026-06-03 00:01:57'),
('36', '7', 'Lesson Unlocked', 'You can continue after completing Cybersecurity Mindset and Ethics.', 'success', '1', '2026-06-03 00:02:16');

SET FOREIGN_KEY_CHECKS = 1;
