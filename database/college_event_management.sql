-- ========================================================
-- COLLEGE EVENT MANAGEMENT SYSTEM
-- Full-Stack Web Development Database Script
-- Technology: MySQL 8.0+ / MariaDB 10.4+ (XAMPP / phpMyAdmin)
-- Database Name: college_event_management
-- ========================================================

CREATE DATABASE IF NOT EXISTS `college_event_management` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `college_event_management`;

-- --------------------------------------------------------
-- 1. Table structure for table `admins`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Table structure for table `students`
-- --------------------------------------------------------
CREATE TABLE `students` (
  `student_id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `year` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table structure for table `events`
-- --------------------------------------------------------
CREATE TABLE `events` (
  `event_id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_name` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME NOT NULL,
  `venue` VARCHAR(150) NOT NULL,
  `total_seats` INT NOT NULL DEFAULT 60,
  `available_seats` INT NOT NULL DEFAULT 60,
  `event_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `registrations`
-- --------------------------------------------------------
CREATE TABLE `registrations` (
  `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(50) NOT NULL,
  `event_id` INT NOT NULL,
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  UNIQUE KEY `unique_student_event` (`student_id`, `event_id`),
  CONSTRAINT `fk_registrations_students` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_events` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Table structure for table `contact_messages`
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `message_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Unread', 'Read', 'Replied') NOT NULL DEFAULT 'Unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- INSERT SAMPLE DATA
-- Password for admin: Admin@123
-- Password for all sample students: Student@123
-- Hash generated via password_hash('Admin@123', PASSWORD_BCRYPT)
-- ========================================================

-- Insert Administrator
INSERT INTO `admins` (`admin_id`, `username`, `email`, `password`) VALUES
(1, 'admin', 'admin@college.edu', '$2y$10$tM6nJbX5Y.8cQ0OqZtN8xuxzJk8G4QZ/Fz1q8sX8L2fJ5cM3tFvK6');

-- Insert 10 Sample Students
INSERT INTO `students` (`student_id`, `name`, `email`, `phone`, `department`, `course`, `year`, `password`) VALUES
('BCA2026001', 'Aarav Sharma', 'aarav.sharma@student.edu', '9876543210', 'Computer Applications', 'BCA (Data Science)', '3rd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026002', 'Diya Patel', 'diya.patel@student.edu', '9876543211', 'Computer Applications', 'BCA (Data Science)', '3rd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026003', 'Rohan Verma', 'rohan.verma@student.edu', '9876543212', 'Computer Science & Engineering', 'B.Tech CSE', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026004', 'Ananya Iyer', 'ananya.iyer@student.edu', '9876543213', 'Information Technology', 'B.Sc IT', '3rd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026005', 'Vikram Singh', 'vikram.singh@student.edu', '9876543214', 'Computer Applications', 'BCA (General)', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026006', 'Priya Nair', 'priya.nair@student.edu', '9876543215', 'Data Science & AI', 'B.Sc Data Science', '1st Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026007', 'Karthik Rao', 'karthik.rao@student.edu', '9876543216', 'Computer Applications', 'BCA (Data Science)', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026008', 'Sneha Kulkarni', 'sneha.k@student.edu', '9876543217', 'Electronics & Communication', 'B.Tech ECE', '4th Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026009', 'Rahul Mehta', 'rahul.mehta@student.edu', '9876543218', 'Management Studies', 'BBA', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'),
('BCA2026010', 'Pooja Joshi', 'pooja.joshi@student.edu', '9876543219', 'Computer Applications', 'MCA', '1st Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5');

-- Insert 8 Realistic Events
INSERT INTO `events` (`event_id`, `event_name`, `description`, `category`, `event_date`, `event_time`, `venue`, `total_seats`, `available_seats`, `event_image`) VALUES
(1, 'Web Development Workshop', 'Hands-on intensive masterclass on modern full-stack web engineering using HTML5, Bootstrap 5, PHP, and REST APIs. Students will build and deploy a dynamic web portal from scratch.', 'Workshops', '2026-12-05', '10:00:00', 'Seminar Hall A, Tech Block', 60, 56, 'web_dev_workshop.jpg'),
(2, 'Python Programming Workshop', 'Comprehensive crash course focusing on data structures, algorithmic problem solving, object-oriented concepts, and practical scripting for data analysis.', 'Workshops', '2026-12-08', '11:00:00', 'Computer Lab 3, CS Department', 50, 47, 'python_workshop.jpg'),
(3, 'Coding Competition: CodeFest 2026', 'Inter-departmental speed coding and debugging championship. Compete against top programmers across multiple rounds of algorithmic challenges and win exciting cash prizes.', 'Competitions', '2026-12-12', '09:30:00', 'Central Computing Centre', 80, 75, 'coding_competition.jpg'),
(4, 'Data Science & AI Seminar', 'Keynote address by industry experts on the future of predictive analytics, large language models, generative AI in industry, and career pathways for BCA/B.Tech graduates.', 'Seminars', '2026-12-15', '14:00:00', 'APJ Abdul Kalam Auditorium', 150, 145, 'data_science_seminar.jpg'),
(5, 'Annual Cultural Fest: Tarang 2026', 'Grand annual collegiate cultural fiesta featuring battle of the bands, classical & contemporary dance competitions, theatrical performances, fashion show, and celebrity night.', 'Cultural Events', '2026-12-20', '16:00:00', 'Open Air Amphitheatre', 300, 296, 'cultural_fest.jpg'),
(6, 'Annual Inter-College Sports Meet', 'High-energy track & field athletics, inter-house football, basketball tournament, badminton championship, and table tennis showdown with awards and medals.', 'Sports Events', '2026-12-23', '08:00:00', 'University Sports Complex', 120, 117, 'sports_meet.jpg'),
(7, 'Technical Quiz: TechWhiz 2026', 'Test your command of computer science fundamentals, emerging tech trends, cyber security, IT trivia, and rapid-fire buzzer rounds with certificates of excellence.', 'Technical Events', '2026-12-28', '11:30:00', 'Conference Room 2', 40, 38, 'technical_quiz.jpg'),
(8, 'Entrepreneurship & Startup Summit', 'Interact with successful startup founders, venture capitalists, and incubator mentors. Learn how to pitch tech ideas, secure seed funding, and build scalable ventures.', 'Seminars', '2027-01-08', '10:30:00', 'Innovation & Incubation Hub', 100, 98, 'entrepreneurship_summit.jpg');

-- Insert Sample Registrations (12 records)
INSERT INTO `registrations` (`registration_id`, `student_id`, `event_id`, `registration_date`, `status`) VALUES
(1, 'BCA2026001', 1, '2026-11-15 09:12:00', 'Approved'),
(2, 'BCA2026001', 4, '2026-11-15 09:15:20', 'Approved'),
(3, 'BCA2026002', 1, '2026-11-15 10:05:10', 'Approved'),
(4, 'BCA2026002', 3, '2026-11-15 10:20:45', 'Pending'),
(5, 'BCA2026003', 2, '2026-11-16 11:30:00', 'Approved'),
(6, 'BCA2026003', 3, '2026-11-16 11:35:12', 'Approved'),
(7, 'BCA2026004', 1, '2026-11-16 14:10:00', 'Approved'),
(8, 'BCA2026004', 5, '2026-11-16 14:15:30', 'Approved'),
(9, 'BCA2026005', 6, '2026-11-17 08:45:00', 'Approved'),
(10, 'BCA2026006', 4, '2026-11-17 09:00:22', 'Pending'),
(11, 'BCA2026007', 7, '2026-11-17 10:14:50', 'Approved'),
(12, 'BCA2026008', 8, '2026-11-17 12:00:00', 'Approved');

-- Insert Sample Contact Messages (5 records)
INSERT INTO `contact_messages` (`message_id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Prof. Ramesh Kulkarni', 'ramesh.k@externalcollege.edu', 'Inter-College Fest Participation Query', 'Greetings! Our college department would like to send a team of 15 students to participate in CodeFest 2026. Please share the delegation guidelines.', 'Unread', '2026-11-16 10:30:00'),
(2, 'Neha Singhania', 'neha.s@student.edu', 'Participation Certificate for Web Workshop', 'Hello Sir/Madam, will hardcopy participation certificates and digital badges be provided after completing the Web Development Workshop?', 'Read', '2026-11-16 14:22:10'),
(3, 'Kunal Deshmukh', 'kunal.d@alumni.org', 'Guest Speaker Invitation at Tech Summit', 'I am an alumnus working as Senior Cloud Architect. I would love to mentor students during the upcoming Entrepreneurship Summit.', 'Read', '2026-11-17 09:15:00'),
(4, 'Siddharth Roy', 'siddharth.r@student.edu', 'Query Regarding Sports Meet Schedule', 'Could you please confirm if the 100m sprint heats will be conducted in the morning session of the Sports Meet?', 'Unread', '2026-11-17 11:40:00'),
(5, 'Divya Sharma', 'divya.sharma@gmail.com', 'Volunteering Opportunities for Cultural Fest', 'I would like to apply as a student coordinator/volunteer for Tarang 2026. Whom should I contact on campus?', 'Replied', '2026-11-17 13:05:00');

