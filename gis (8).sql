-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 11:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gis`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking_equipment`
--

CREATE TABLE `booking_equipment` (
  `booking_equipment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_equipment`
--

INSERT INTO `booking_equipment` (`booking_equipment_id`, `booking_id`, `item_id`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 3, 25, 1, 1.00, 20.00, '2025-11-05 21:25:15'),
(2, 3, 24, 1, 3.00, 60.00, '2025-11-05 21:25:15'),
(3, 3, 27, 1, 4.00, 80.00, '2025-11-05 21:25:15');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_description` text DEFAULT NULL,
  `completion_date` date NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `issued_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','revoked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `company_name`, `email`, `password`, `phone`, `industry`, `address`, `website`, `logo_path`, `created_at`, `status`, `reset_code`, `reset_code_expiry`) VALUES
(13, 'dot lebanon', 'ghinadarwish99@gmail.com', '$2y$10$Q7syndTo8LQZgIwPpAw0CecG6t9d.8FMkqEXSKBGdyV3ZwlsNLTE6', '', 'general', '', '', '', '2025-10-11 11:24:44', 'approved', NULL, NULL),
(18, 'MATCHA', 'kabadakisara@gmail.com', '$2y$10$NNZ8CukWL5zohgZAYPXeSue7A8yIPdQY3ZZyg8x5QRRutsQk4lMQy', '03123678', 'training', NULL, 'https://chatgpt.com/c/68e19546-e3d0-8333-8efd-8e21ea11f2fd', 'uploads/companies/logo/1762208341_Gemini_Generated_Image_8a5g48a5g48a5g48.png', '2025-11-03 22:19:01', 'approved', NULL, NULL),
(19, 'mobile', 'halawaniomar33@gmail.com', '$2y$10$YFtuDd5r.InpmoU7DWmJg.Hx7fzdTl8ns47i06kPVdC5.8lvtiUP6', '', 'mobile ', NULL, '', '', '2025-11-05 21:39:38', 'approved', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_locations`
--

CREATE TABLE `company_locations` (
  `location_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location_type` enum('head_office','branch','training_center') NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_locations`
--

INSERT INTO `company_locations` (`location_id`, `company_id`, `location_name`, `location_type`, `address`, `city`, `country`, `postal_code`, `phone`, `email`, `is_primary`, `status`, `created_at`, `updated_at`) VALUES
(3, 13, 'boulverad ', 'branch', 'Tripoli,North Lebanon', 'Hadadin', 'Lebanon', '1300', '76137857', 'joelle@gmail.com', 1, 'active', '2025-10-15 20:59:58', '2025-10-15 20:59:58'),
(4, 18, 'main office', 'branch', 'TRIPOLI ', 'ABOU SAMRAA', 'TRIPOLI', '1300', '03123789', '', 1, 'active', '2025-11-03 22:19:01', '2025-11-03 22:19:01'),
(5, 19, 'main office', 'head_office', 'abou samraa', 'tripoli', 'lebanon', '', '', '', 1, 'active', '2025-11-05 21:39:38', '2025-11-05 21:39:38');

--
-- Triggers `company_locations`
--
DELIMITER $$
CREATE TRIGGER `ensure_single_primary_location_update` BEFORE UPDATE ON `company_locations` FOR EACH ROW BEGIN
        IF NEW.is_primary = TRUE AND OLD.is_primary = FALSE THEN
            UPDATE company_locations 
            SET is_primary = FALSE 
            WHERE company_id = NEW.company_id AND location_id != NEW.location_id;
        END IF;
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `created_by_type` enum('company','instructor') DEFAULT 'instructor',
  `created_by_id` int(11) DEFAULT NULL,
  `related_internship_id` int(11) DEFAULT NULL,
  `course_title` varchar(255) NOT NULL,
  `course_description` text NOT NULL,
  `course_category` enum('technical','business','language','soft_skills','certification','workshop','seminar','other') NOT NULL,
  `course_level` enum('beginner','intermediate','advanced','expert') NOT NULL,
  `course_duration` varchar(100) NOT NULL,
  `course_price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `is_online` tinyint(1) DEFAULT 1,
  `location` varchar(255) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `max_students` int(11) DEFAULT NULL,
  `course_image` varchar(500) DEFAULT NULL,
  `course_materials` text DEFAULT NULL,
  `prerequisites` text DEFAULT NULL,
  `learning_outcomes` text DEFAULT NULL,
  `course_schedule` text DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `certificate_option` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `instructor_id`, `created_by_type`, `created_by_id`, `related_internship_id`, `course_title`, `course_description`, `course_category`, `course_level`, `course_duration`, `course_price`, `currency`, `is_online`, `location`, `requirements`, `max_students`, `course_image`, `course_materials`, `prerequisites`, `learning_outcomes`, `course_schedule`, `status`, `is_featured`, `created_at`, `updated_at`, `category`, `start_date`, `end_date`, `certificate_option`) VALUES
(10, 10, 'company', 18, 1, 'aws cloud', 'the student will be able to learn ..', 'technical', 'beginner', '4 MONTHS', 20.11, 'USD', 1, 'TRIPOLI', 'cnjsd', 12, '', 'ccxsxse', 'prereudhffhf', 'wwew', 'ddss', '', 1, '2025-11-04 22:32:15', '2025-11-04 22:32:15', 'technical', '2026-01-01', '2026-04-04', 1),
(11, 10, 'company', 18, 1, 'ai basic', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'technical', 'beginner', '4 weeks', 20.00, 'USD', 1, 'TRIPOLI', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 10, '', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', '{\"monday\":{\"start\":\"13:00\",\"end\":\"14:00\"},\"tuesday\":{\"start\":\"13:00\",\"end\":\"14:00\"}}', '', 1, '2025-11-04 22:34:37', '2025-11-04 22:42:25', 'technical', '2026-01-01', '2026-01-29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','completed','dropped') DEFAULT 'enrolled',
  `completion_date` timestamp NULL DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_path` varchar(500) DEFAULT NULL,
  `progress_percentage` int(11) DEFAULT 0,
  `last_accessed` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_evaluations`
--

CREATE TABLE `course_evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `evaluation_text` text DEFAULT NULL,
  `course_content_quality` int(11) NOT NULL CHECK (`course_content_quality` >= 1 and `course_content_quality` <= 5),
  `instructor_effectiveness` int(11) NOT NULL CHECK (`instructor_effectiveness` >= 1 and `instructor_effectiveness` <= 5),
  `practical_application` int(11) NOT NULL CHECK (`practical_application` >= 1 and `practical_application` <= 5),
  `learning_environment` int(11) NOT NULL CHECK (`learning_environment` >= 1 and `learning_environment` <= 5),
  `would_recommend` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_instructors`
--

CREATE TABLE `course_instructors` (
  `course_instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected','completed') DEFAULT 'pending',
  `compensation_type` enum('hourly','salary','project','negotiable') DEFAULT 'negotiable',
  `compensation_amount` decimal(10,2) DEFAULT 0.00,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_messages`
--

CREATE TABLE `course_messages` (
  `message_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('company','instructor') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `recipient_type` enum('company','instructor') NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_reviews`
--

CREATE TABLE `course_reviews` (
  `review_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `content_quality` int(11) NOT NULL CHECK (`content_quality` >= 1 and `content_quality` <= 5),
  `instructor_effectiveness` int(11) NOT NULL CHECK (`instructor_effectiveness` >= 1 and `instructor_effectiveness` <= 5),
  `course_structure` int(11) NOT NULL CHECK (`course_structure` >= 1 and `course_structure` <= 5),
  `would_recommend` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_categories`
--

CREATE TABLE `equipment_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `icon_class` varchar(50) DEFAULT 'fas fa-cog',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_categories`
--

INSERT INTO `equipment_categories` (`category_id`, `category_name`, `icon_class`, `status`, `created_at`) VALUES
(7, 'Laboratory Equipment', 'fas fa-flask', 'active', '2025-11-05 20:35:06'),
(8, 'Scientific Instruments', 'fas fa-microscope', 'active', '2025-11-05 20:35:06'),
(9, 'Safety Equipment', 'fas fa-hard-hat', 'active', '2025-11-05 20:35:06'),
(10, 'Measurement Tools', 'fas fa-ruler', 'active', '2025-11-05 20:35:06'),
(11, 'Training Equipment', 'fas fa-chalkboard-teacher', 'active', '2025-11-05 20:35:06'),
(12, 'Presentation Tools', 'fas fa-projector', 'active', '2025-11-05 20:35:06'),
(13, 'Audio/Video Equipment', 'fas fa-video', 'active', '2025-11-05 20:35:06'),
(14, 'Furniture', 'fas fa-chair', 'active', '2025-11-05 20:35:06'),
(15, 'Networking Equipment', 'fas fa-network-wired', 'active', '2025-11-05 20:35:06'),
(16, 'Workshop Tools', 'fas fa-tools', 'active', '2025-11-05 20:35:06'),
(17, 'Power Tools', 'fas fa-bolt', 'active', '2025-11-05 20:35:06'),
(18, 'Hand Tools', 'fas fa-wrench', 'active', '2025-11-05 20:35:06'),
(19, 'Lighting', 'fas fa-lightbulb', 'active', '2025-11-05 20:35:06'),
(20, 'Event Equipment', 'fas fa-calendar-alt', 'active', '2025-11-05 20:35:06'),
(21, 'Office Equipment', 'fas fa-print', 'active', '2025-11-05 20:35:06'),
(22, 'Printing Equipment', 'fas fa-print', 'active', '2025-11-05 20:35:06');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_items`
--

CREATE TABLE `equipment_items` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `item_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `standard_price` decimal(10,2) DEFAULT 0.00,
  `unit_type` enum('per_hour','per_day','per_week','per_month') DEFAULT 'per_hour',
  `is_custom` tinyint(1) DEFAULT 0,
  `company_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_items`
--

INSERT INTO `equipment_items` (`item_id`, `category_id`, `item_name`, `description`, `standard_price`, `unit_type`, `is_custom`, `company_id`, `status`, `created_at`) VALUES
(13, 7, 'Microscope', 'High-quality optical microscope for research', 15.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(14, 7, 'Centrifuge', 'Laboratory centrifuge for sample separation', 25.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(15, 7, 'Autoclave', 'Sterilization equipment', 20.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(16, 7, 'Fume Hood', 'Safety fume hood for chemical work', 30.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(17, 7, 'Lab Bench', 'Sturdy laboratory workbench', 10.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(18, 8, 'Spectrophotometer', 'UV-Vis spectrophotometer', 40.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(19, 8, 'pH Meter', 'Digital pH measurement device', 8.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(20, 8, 'Balance Scale', 'Precision analytical balance', 12.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(21, 8, 'Thermocycler', 'PCR thermal cycler', 35.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(22, 8, 'Incubator', 'Laboratory incubator', 18.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(23, 9, 'Safety Goggles', 'Protective eyewear set', 2.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(24, 9, 'Lab Coat', 'Protective laboratory coat', 3.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(25, 9, 'Safety Gloves', 'Disposable safety gloves pack', 1.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(26, 9, 'Fire Extinguisher', 'Laboratory fire safety equipment', 5.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(27, 9, 'First Aid Kit', 'Complete first aid kit', 4.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(28, 10, 'Digital Caliper', 'Precision measuring tool', 5.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(29, 10, 'Multimeter', 'Electrical measurement device', 8.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(30, 10, 'Oscilloscope', 'Electronic test instrument', 30.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(31, 10, 'Thermometer', 'Digital thermometer', 2.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(32, 11, 'Whiteboard', 'Large whiteboard for training', 8.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(33, 11, 'Flip Chart', 'Portable flip chart stand', 5.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(34, 11, 'Training Materials', 'Printed training materials set', 10.00, 'per_day', 0, NULL, 'active', '2025-11-05 20:35:46'),
(35, 12, 'Projector', 'HD projector for presentations', 25.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(36, 12, 'Projection Screen', 'Retractable projection screen', 8.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(37, 12, 'Laser Pointer', 'Presentation laser pointer', 2.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(38, 12, 'Wireless Presenter', 'Remote presentation control', 5.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(39, 13, 'Sound System', 'Complete PA sound system', 40.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(40, 13, 'Microphone Set', 'Wireless microphone system', 15.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(41, 13, 'Video Camera', 'HD video recording camera', 30.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(42, 13, 'Speakers', 'Professional speakers', 20.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(43, 14, 'Conference Table', 'Large conference table', 15.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(44, 14, 'Office Chairs', 'Ergonomic office chairs (set of 10)', 25.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(45, 14, 'Training Desks', 'Portable training desks (set of 20)', 30.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(46, 15, 'WiFi Router', 'High-speed WiFi router', 10.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(47, 15, 'Network Switch', 'Ethernet network switch', 12.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(48, 15, 'Cables & Connectors', 'Network cables and connectors set', 5.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(49, 16, 'Drill Press', 'Bench drill press', 20.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(50, 16, 'Table Saw', 'Professional table saw', 25.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(51, 16, 'Workbench', 'Heavy-duty workbench', 12.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(52, 17, 'Circular Saw', 'Power circular saw', 15.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(53, 17, 'Angle Grinder', 'Power angle grinder', 12.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(54, 17, 'Power Drill', 'Cordless power drill', 10.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(55, 18, 'Tool Set', 'Complete hand tool set', 8.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(56, 18, 'Wrench Set', 'Professional wrench set', 5.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(57, 19, 'LED Lights', 'Professional LED lighting setup', 15.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(58, 19, 'Stage Lights', 'Stage lighting system', 30.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(59, 20, 'Stage Setup', 'Complete stage setup', 50.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(60, 20, 'Tables & Chairs', 'Event tables and chairs (set of 50)', 60.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(61, 21, 'Printer', 'Multifunction printer', 8.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46'),
(62, 21, 'Scanner', 'Document scanner', 5.00, 'per_hour', 0, NULL, 'active', '2025-11-05 20:35:46');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_packages`
--

CREATE TABLE `equipment_packages` (
  `package_id` int(11) NOT NULL,
  `package_name` varchar(200) NOT NULL,
  `package_description` text DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `package_type` enum('predefined','custom') DEFAULT 'predefined',
  `company_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_packages`
--

INSERT INTO `equipment_packages` (`package_id`, `package_name`, `package_description`, `total_price`, `package_type`, `company_id`, `status`, `created_at`) VALUES
(1, 'Laboratory Package', 'Complete laboratory equipment package for research and experiments', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:36:24'),
(2, 'Training Room Package', 'Complete training room equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:36:24'),
(3, 'Conference Room Package', 'Complete conference room equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:36:24'),
(4, 'Workshop Space Package', 'Complete workshop equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:36:24'),
(5, 'Workshop Package', 'Complete workshop equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:38:04'),
(6, 'Event Hall Package', 'Complete event hall equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:38:04'),
(7, 'Workshop Package', 'Complete workshop equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:38:25'),
(8, 'Event Hall Package', 'Complete event hall equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:38:25'),
(9, 'Office Space Package', 'Complete office space equipment package', 0.00, 'predefined', NULL, 'active', '2025-11-05 20:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `instructor_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `university_name` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `upload_cv` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`instructor_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `department`, `university_name`, `photo_path`, `upload_cv`, `bio`, `created_at`, `status`, `reset_code`, `reset_code_expiry`) VALUES
(9, 'walid', 'kabadaki', 'ghinadarwish80@gmail.com', '$2y$10$QPR/demWaauTDZf6fjfjEukdO27keCyB0Ya1q4WlB3AjccPSPToe2', '03552519', 'CS', 'JINAN', '', '', '', '2025-10-11 22:26:30', 'approved', NULL, NULL),
(10, 'jana', 'darwish', 'gheenafouad@gmail.com', '$2y$10$dQW58lWos43iUttaAmtcgOxaoTaeF1t1W.BbXMimeqtUOg54G9v6W', '76137857', 'faculty of engineering ', 'jinan', 'uploads/instructors/photo/1760557301_aws.jpeg', NULL, 'mnmnmnmnmnmnmnmmmmmmmmmmmmmmmmmmmmmmm', '2025-10-15 19:41:41', 'approved', NULL, NULL),
(11, 'safiah', 'darwish', 'safiahdarwish89@gmail.com', '$2y$10$zAZBKQhE279NoWDvAhLx9uO9GIjh5UVUavjoTYFyFERMA2Gih7mnO', '00961 76137857', 'PROGRAMMING', 'jinan', 'uploads/instructors/photo/1761336056_Gemini_Generated_Image_8a5g48a5g48a5g48-removebg-preview.png', 'uploads/instructors/cv/1761336056_triangle_templates_A4_true_size.pdf', 'nnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn', '2025-10-24 20:00:56', 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `instructor_applications`
--

CREATE TABLE `instructor_applications` (
  `application_id` int(11) NOT NULL,
  `instructor_request_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `motivation_message` text NOT NULL,
  `relevant_experience` text NOT NULL,
  `availability` text NOT NULL,
  `additional_info` text DEFAULT NULL,
  `cv_path` varchar(500) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_applications`
--

INSERT INTO `instructor_applications` (`application_id`, `instructor_request_id`, `instructor_id`, `motivation_message`, `relevant_experience`, `availability`, `additional_info`, `cv_path`, `status`, `applied_at`, `reviewed_at`, `review_notes`) VALUES
(1, 1, 9, 'hvghjbkjbjknkljljlkn', 'trtghvhjgkhbjnkllkmn,,.', 'rdrdhjbvkjbkjbkjhkjhjn,m ', 'waersedryfuvhjbvhuh', NULL, 'accepted', '2025-10-12 22:43:06', '2025-10-12 22:51:55', 'you are beautiful'),
(2, 5, 10, 'zzzzzzzzzzzzzzzz', 'zzzzzzzzzzzzzzzzzzzzzzzzz', 'zzzzzzzzzzzzzzzzzzzzz', 'zzzzzzzzzzzzzzzzzz', 'uploads/instructor_applications/68f00a60e189e_1760561760.docx', 'accepted', '2025-10-15 20:56:00', '2025-10-15 20:56:17', '');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_evaluations`
--

CREATE TABLE `instructor_evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `instructor_request_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `evaluation_text` text DEFAULT NULL,
  `teaching_quality` int(11) NOT NULL CHECK (`teaching_quality` >= 1 and `teaching_quality` <= 5),
  `communication` int(11) NOT NULL CHECK (`communication` >= 1 and `communication` <= 5),
  `punctuality` int(11) NOT NULL CHECK (`punctuality` >= 1 and `punctuality` <= 5),
  `professionalism` int(11) NOT NULL CHECK (`professionalism` >= 1 and `professionalism` <= 5),
  `would_recommend` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_evaluations`
--

INSERT INTO `instructor_evaluations` (`evaluation_id`, `company_id`, `instructor_id`, `instructor_request_id`, `rating`, `evaluation_text`, `teaching_quality`, `communication`, `punctuality`, `professionalism`, `would_recommend`, `created_at`, `updated_at`) VALUES
(1, 13, 10, 5, 1, 'gooood', 4, 4, 4, 4, 1, '2025-10-15 19:21:26', '2025-10-15 19:21:26');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_requests`
--

CREATE TABLE `instructor_requests` (
  `instructor_request_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `course_title` varchar(255) NOT NULL,
  `course_description` text NOT NULL,
  `required_qualifications` text NOT NULL,
  `skills_required` text NOT NULL,
  `course_duration` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `compensation_type` enum('hourly','salary','project','negotiable') NOT NULL,
  `compensation_amount` decimal(10,2) NOT NULL,
  `application_deadline` date NOT NULL,
  `max_applications` int(11) DEFAULT NULL,
  `course_type` enum('technical','business','language','soft_skills','certification','workshop','seminar','other') NOT NULL,
  `experience_level` enum('beginner','intermediate','advanced','expert') NOT NULL,
  `status` enum('active','closed','filled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `working_days` text DEFAULT NULL,
  `area_of_expertise` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_requests`
--

INSERT INTO `instructor_requests` (`instructor_request_id`, `company_id`, `course_title`, `course_description`, `required_qualifications`, `skills_required`, `course_duration`, `location`, `is_online`, `compensation_type`, `compensation_amount`, `application_deadline`, `max_applications`, `course_type`, `experience_level`, `status`, `created_at`, `updated_at`, `working_days`, `area_of_expertise`) VALUES
(1, 13, 'aws', 'cloud course', 'gvghfhhukmklmklmlkiuo', 'ghbhvfgfhgvhlk;kkpokl ,m', '6 months', 'tripoli', 0, 'salary', 6000.00, '2025-10-22', 30, 'technical', 'advanced', 'active', '2025-10-12 21:56:00', '2025-10-12 21:56:00', NULL, NULL),
(5, 13, 'software development', 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'lllllllllllllllllllllllllllllll', 'mmmmmmmmmmmmmmmmmmmmmmmmmm', '3 months', 'TRIPOLI', 0, 'salary', 88.00, '2025-12-12', 18, 'technical', 'intermediate', 'active', '2025-10-15 20:53:34', '2025-10-15 20:53:34', 'monday,tuesday,wednesday,thursday,friday', 'software_engineering');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_reviews`
--

CREATE TABLE `instructor_reviews` (
  `review_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `instructor_request_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `teaching_quality` int(11) NOT NULL CHECK (`teaching_quality` >= 1 and `teaching_quality` <= 5),
  `communication` int(11) NOT NULL CHECK (`communication` >= 1 and `communication` <= 5),
  `punctuality` int(11) NOT NULL CHECK (`punctuality` >= 1 and `punctuality` <= 5),
  `professionalism` int(11) NOT NULL CHECK (`professionalism` >= 1 and `professionalism` <= 5),
  `course_completion` tinyint(1) DEFAULT 1,
  `would_recommend` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `internship_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text NOT NULL,
  `benefits` text DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `stipend` varchar(100) DEFAULT NULL,
  `type` enum('full-time','part-time','remote','hybrid') NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `skills_required` text DEFAULT NULL,
  `application_deadline` date NOT NULL,
  `max_applications` int(11) DEFAULT NULL,
  `status` enum('active','paused','closed','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `working_days` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`internship_id`, `company_id`, `poster_path`, `title`, `description`, `requirements`, `benefits`, `location`, `duration`, `start_date`, `end_date`, `stipend`, `type`, `department`, `skills_required`, `application_deadline`, `max_applications`, `status`, `created_at`, `updated_at`, `working_days`) VALUES
(3, 13, 'uploads/poster_1760182142_68ea3f7e91d7d.jpeg', 'aws', 'course to be familiar with cloud', 'communication skill', '', 'tripoli', '6 months', '2025-10-30', '2026-04-30', 'UNPAID', 'part-time', 'CS', 'python, networking ', '2025-10-21', 30, 'active', '2025-10-11 11:29:02', '2025-10-11 11:29:02', NULL),
(7, 18, 'uploads/poster_1762211068_690934fc07421.jpeg', 'software engineering', 'KKKLLLKKKLL', 'LLLLKKK', 'LKLJOIIUHUIJ', 'TRIPOLI', '4 MONTHS', '2026-01-01', '2026-01-04', 'Unpaid', 'part-time', 'ENGINEERING AND PROGRAMMING', 'LJLKJKHKJGYUYTFHTCV', '2025-12-12', 12, 'active', '2025-11-03 23:04:28', '2025-11-03 23:04:28', 'monday,tuesday,wednesday,thursday');

-- --------------------------------------------------------

--
-- Table structure for table `internship_applications`
--

CREATE TABLE `internship_applications` (
  `application_id` int(11) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `cover_letter` text NOT NULL,
  `motivation` text NOT NULL,
  `relevant_experience` text DEFAULT NULL,
  `why_this_company` text NOT NULL,
  `career_goals` text DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','reviewed','shortlisted','interviewed','accepted','rejected') DEFAULT 'pending',
  `company_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_applications`
--

INSERT INTO `internship_applications` (`application_id`, `internship_id`, `student_id`, `cover_letter`, `motivation`, `relevant_experience`, `why_this_company`, `career_goals`, `additional_info`, `application_date`, `status`, `company_notes`, `created_at`, `updated_at`) VALUES
(3, 3, 11, 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', '2025-10-11 11:34:29', 'accepted', NULL, '2025-10-11 11:34:29', '2025-10-11 11:36:09');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_content` text NOT NULL,
  `sender_type` enum('student','company') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `replied` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_items`
--

CREATE TABLE `package_items` (
  `package_item_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_items`
--

INSERT INTO `package_items` (`package_item_id`, `package_id`, `item_id`, `quantity`) VALUES
(1, 1, 13, 2),
(2, 1, 14, 1),
(3, 1, 16, 1),
(4, 1, 17, 4),
(5, 1, 19, 2),
(6, 1, 20, 1),
(7, 1, 23, 10),
(8, 1, 24, 10),
(9, 1, 25, 20),
(10, 1, 27, 1),
(11, 2, 35, 1),
(12, 2, 36, 1),
(13, 2, 32, 2),
(14, 2, 33, 1),
(15, 2, 45, 1),
(16, 2, 44, 1),
(17, 2, 40, 1),
(18, 2, 46, 1),
(19, 3, 35, 1),
(20, 3, 36, 1),
(21, 3, 43, 1),
(22, 3, 44, 1),
(23, 3, 40, 1),
(24, 3, 46, 1),
(25, 3, 32, 1),
(26, 5, 51, 2),
(27, 5, 50, 1),
(28, 5, 54, 2),
(29, 5, 55, 2),
(30, 5, 23, 5),
(31, 5, 27, 1),
(32, 5, 51, 2),
(33, 5, 50, 1),
(34, 5, 54, 2),
(35, 5, 55, 2),
(36, 5, 23, 5),
(37, 5, 27, 1),
(38, 6, 39, 1),
(39, 6, 40, 1),
(40, 6, 58, 1),
(41, 6, 59, 1),
(42, 6, 60, 1),
(43, 6, 35, 1),
(44, 6, 36, 1),
(45, 9, 43, 1),
(46, 9, 44, 1),
(47, 9, 61, 1),
(48, 9, 62, 1),
(49, 9, 46, 1),
(50, 9, 32, 1);

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `place_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `place_name` varchar(255) NOT NULL,
  `place_type` enum('conference_room','meeting_room','workspace','event_space','laboratory','training_room','coworking_space','office_space','other') NOT NULL,
  `space_type` enum('short_term','long_term') NOT NULL DEFAULT 'short_term',
  `description` text NOT NULL,
  `capacity` int(11) NOT NULL,
  `hourly_rate` decimal(10,2) DEFAULT 0.00,
  `daily_rate` decimal(10,2) DEFAULT 0.00,
  `weekly_rate` decimal(10,2) DEFAULT 0.00,
  `monthly_rate` decimal(10,2) DEFAULT 0.00,
  `weekend_hourly_rate` decimal(10,2) DEFAULT NULL,
  `weekend_daily_rate` decimal(10,2) DEFAULT NULL,
  `weekend_weekly_rate` decimal(10,2) DEFAULT NULL,
  `weekend_monthly_rate` decimal(10,2) DEFAULT NULL,
  `weekday_hourly_rate` decimal(10,2) DEFAULT NULL,
  `weekday_daily_rate` decimal(10,2) DEFAULT NULL,
  `weekday_weekly_rate` decimal(10,2) DEFAULT NULL,
  `weekday_monthly_rate` decimal(10,2) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `availability_schedule` text DEFAULT NULL,
  `booking_policy` text DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `is_equipment_included` tinyint(1) DEFAULT 0,
  `equipment_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`place_id`, `company_id`, `place_name`, `place_type`, `space_type`, `description`, `capacity`, `hourly_rate`, `daily_rate`, `weekly_rate`, `monthly_rate`, `weekend_hourly_rate`, `weekend_daily_rate`, `weekend_weekly_rate`, `weekend_monthly_rate`, `weekday_hourly_rate`, `weekday_daily_rate`, `weekday_weekly_rate`, `weekday_monthly_rate`, `address`, `city`, `country`, `postal_code`, `latitude`, `longitude`, `amenities`, `images`, `availability_schedule`, `booking_policy`, `cancellation_policy`, `status`, `is_equipment_included`, `equipment_info`, `created_at`, `updated_at`) VALUES
(1, 13, 'almni foundation', 'workspace', 'short_term', 'including tv fast wifi ', 40, 10.00, 30.00, 70.00, 300.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fo2 shawerma abu sebhi ', 'tripoli', 'lebanon', NULL, 99.99999999, 100.00000000, '[\"wifi\",\"parking\",\"air_conditioning\",\"projector\",\"whiteboard\",\"kitchen\",\"restroom\"]', '[\"uploads\\/places\\/68ea43385e37d_1760183096.jpg\"]', '{\"monday\":{\"start\":\"08:00\",\"end\":\"17:00\",\"available\":true},\"tuesday\":{\"start\":\"08:00\",\"end\":\"17:00\",\"available\":true},\"wednesday\":{\"start\":\"08:00\",\"end\":\"17:00\",\"available\":true},\"thursday\":{\"start\":\"08:00\",\"end\":\"17:00\",\"available\":true},\"friday\":{\"start\":\"08:00\",\"end\":\"17:00\",\"available\":true},\"saturday\":{\"start\":\"08:00\",\"end\":\"17:00\",\"available\":true},\"sunday\":{\"start\":\"\",\"end\":\"\",\"available\":false}}', '', '', 'active', 1, NULL, '2025-10-11 11:44:56', '2025-10-11 11:44:56'),
(2, 13, 'coffeshop', 'office_space', 'short_term', 'mmmmmmmmmmmmmmmmmmmmmmm', 8, 5.00, 20.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tripoli,North Lebanon', 'Hadadin', 'Lebanon', '1300', NULL, NULL, '[\"wifi\",\"air_conditioning\",\"projector\",\"kitchen\"]', '[\"uploads\\/places\\/68f015757d30e_1760564597.png\"]', '{\"monday\":{\"start\":\"10:00\",\"end\":\"13:00\",\"available\":true,\"hourly_rate\":5},\"tuesday\":{\"start\":\"10:00\",\"end\":\"13:00\",\"available\":true,\"hourly_rate\":5},\"wednesday\":{\"start\":\"10:00\",\"end\":\"13:00\",\"available\":true,\"hourly_rate\":5},\"thursday\":{\"start\":\"10:00\",\"end\":\"13:00\",\"available\":true,\"hourly_rate\":5},\"friday\":{\"start\":\"10:00\",\"end\":\"13:00\",\"available\":true,\"hourly_rate\":5},\"saturday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":7},\"sunday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":7}}', '.............', 'mmmmmmmmmmmmmmmmm', 'active', 1, '{\"selected_equipment\":[],\"total_equipment_cost\":0,\"base_hourly_rate\":5,\"final_hourly_rate\":5}', '2025-10-15 21:43:17', '2025-10-15 21:43:17'),
(3, 18, 'COFFESTATION', 'training_room', 'long_term', 'KJKJKJKJKJKJKJ', 12, 70.00, 560.00, 2810.00, 11240.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TRIPOLI ', 'ABOU SAMRAA', 'TRIPOLI', '1300', NULL, NULL, '[\"wifi\",\"parking\",\"air_conditioning\",\"projector\",\"whiteboard\"]', '[\"uploads\\/places\\/690935b8bde8e_1762211256.jpeg\"]', '{\"monday\":{\"start\":\"01:00\",\"end\":\"05:00\",\"available\":true,\"hourly_rate\":10},\"tuesday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"wednesday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"thursday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"friday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"saturday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":13},\"sunday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":13}}', 'LLLLLLLLLLL', 'LLLLLLLLLLLLLLLL', 'active', 1, '{\"selected_equipment\":[{\"item_id\":3,\"name\":\"Microphone\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"15.00\",\"total_cost\":15,\"unit_type\":\"per_hour\"},{\"item_id\":1,\"name\":\"Projector\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"25.00\",\"total_cost\":25,\"unit_type\":\"per_hour\"},{\"item_id\":2,\"name\":\"Sound System\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"30.00\",\"total_cost\":30,\"unit_type\":\"per_hour\"}],\"total_equipment_cost\":70,\"base_hourly_rate\":0,\"final_hourly_rate\":70}', '2025-11-03 23:07:36', '2025-11-03 23:07:36'),
(4, 18, 'COFFESTATION', 'training_room', 'long_term', 'KJKJKJKJKJKJKJ', 12, 70.00, 560.00, 2810.00, 11240.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TRIPOLI ', 'ABOU SAMRAA', 'TRIPOLI', '1300', NULL, NULL, '[\"wifi\",\"parking\",\"air_conditioning\",\"projector\",\"whiteboard\"]', '[\"uploads\\/places\\/69093619426e1_1762211353.jpeg\"]', '{\"monday\":{\"start\":\"01:00\",\"end\":\"05:00\",\"available\":true,\"hourly_rate\":10},\"tuesday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"wednesday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"thursday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"friday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"saturday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":13},\"sunday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":13}}', 'LLLLLLLLLLL', 'LLLLLLLLLLLLLLLL', 'active', 1, '{\"selected_equipment\":[{\"item_id\":3,\"name\":\"Microphone\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"15.00\",\"total_cost\":15,\"unit_type\":\"per_hour\"},{\"item_id\":1,\"name\":\"Projector\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"25.00\",\"total_cost\":25,\"unit_type\":\"per_hour\"},{\"item_id\":2,\"name\":\"Sound System\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"30.00\",\"total_cost\":30,\"unit_type\":\"per_hour\"}],\"total_equipment_cost\":70,\"base_hourly_rate\":0,\"final_hourly_rate\":70}', '2025-11-03 23:09:13', '2025-11-03 23:09:13'),
(5, 18, 'COFFESTATION', 'training_room', 'long_term', 'KJKJKJKJKJKJKJ', 12, 70.00, 560.00, 2810.00, 11240.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TRIPOLI ', 'ABOU SAMRAA', 'TRIPOLI', '1300', NULL, NULL, '[\"wifi\",\"parking\",\"air_conditioning\",\"projector\",\"whiteboard\"]', '[\"uploads\\/places\\/69093624e7fdd_1762211364.jpeg\"]', '{\"monday\":{\"start\":\"01:00\",\"end\":\"05:00\",\"available\":true,\"hourly_rate\":10},\"tuesday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"wednesday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"thursday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"friday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":10},\"saturday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":13},\"sunday\":{\"start\":\"\",\"end\":\"\",\"available\":false,\"hourly_rate\":13}}', 'LLLLLLLLLLL', 'LLLLLLLLLLLLLLLL', 'active', 1, '{\"selected_equipment\":[{\"item_id\":3,\"name\":\"Microphone\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"15.00\",\"total_cost\":15,\"unit_type\":\"per_hour\"},{\"item_id\":1,\"name\":\"Projector\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"25.00\",\"total_cost\":25,\"unit_type\":\"per_hour\"},{\"item_id\":2,\"name\":\"Sound System\",\"category\":\"Audio\\/Visual\",\"quantity\":1,\"unit_price\":\"30.00\",\"total_cost\":30,\"unit_type\":\"per_hour\"}],\"total_equipment_cost\":70,\"base_hourly_rate\":0,\"final_hourly_rate\":70}', '2025-11-03 23:09:24', '2025-11-03 23:09:24'),
(6, 18, 'kahwa', 'training_room', '', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 10, 1.00, 10.00, 20.00, 80.00, 0.00, 0.00, 0.00, 0.00, 50.00, 50.00, 50.00, 50.00, 'tripoli near abou samraa', 'tripoli', 'lebanon', '1300', NULL, NULL, 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', '{\"monday\":{\"start\":\"01:00\",\"end\":\"02:00\"},\"tuesday\":{\"start\":\"01:00\",\"end\":\"02:00\"}}', 'قاA course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'active', 1, 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', '2025-11-04 23:15:48', '2025-11-04 23:15:48'),
(7, 18, 'kahwa', 'training_room', '', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 10, 1.00, 10.00, 20.00, 80.00, 0.00, 0.00, 0.00, 0.00, 50.00, 50.00, 50.00, 50.00, 'tripoli near abou samraa', 'tripoli', 'lebanon', '1300', NULL, NULL, 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', '{\"monday\":{\"start\":\"01:00\",\"end\":\"02:00\"},\"tuesday\":{\"start\":\"01:00\",\"end\":\"02:00\"}}', 'قاA course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', 'active', 1, 'A course is a structured program of study designed to teach specific knowledge or skills within a particular field. It usually includes lessons, assignments, and assessments to help learners understand and apply what they learn. Courses can be taken online or in person and often lead to certifications or degrees that enhance personal or professional growth.', '2025-11-04 23:21:34', '2025-11-04 23:21:34'),
(8, 18, 'gheena lab', 'laboratory', '', 'suitable to biochemistry student', 20, 0.50, 4.00, 20.00, 1.60, 0.00, 0.00, 0.00, 0.00, 0.50, 4.00, 20.00, 80.00, 'tyre', 'tyre', 'lebanon', '123', NULL, 6.00000000, 'mmmmmmmmmmmm', 'data:image/jpeg;base64,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', '{\"monday\":{\"start\":\"10:00\",\"end\":\"13:00\"},\"tuesday\":{\"start\":\"10:00\",\"end\":\"13:00\"}}', '......................', '............................', 'active', 0, NULL, '2025-11-05 21:03:15', '2025-11-05 21:03:15');

-- --------------------------------------------------------

--
-- Table structure for table `place_availability`
--

CREATE TABLE `place_availability` (
  `availability_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `place_availability`
--

INSERT INTO `place_availability` (`availability_id`, `place_id`, `day_of_week`, `start_time`, `end_time`, `hourly_rate`, `is_available`, `created_at`) VALUES
(1, 1, 'monday', '08:00:00', '17:00:00', NULL, 1, '2025-10-11 11:44:56'),
(2, 1, 'tuesday', '08:00:00', '17:00:00', NULL, 1, '2025-10-11 11:44:56'),
(3, 1, 'wednesday', '08:00:00', '17:00:00', NULL, 1, '2025-10-11 11:44:56'),
(4, 1, 'thursday', '08:00:00', '17:00:00', NULL, 1, '2025-10-11 11:44:56'),
(5, 1, 'friday', '08:00:00', '17:00:00', NULL, 1, '2025-10-11 11:44:56'),
(6, 1, 'saturday', '08:00:00', '17:00:00', NULL, 1, '2025-10-11 11:44:56'),
(7, 2, 'monday', '10:00:00', '13:00:00', 5.00, 1, '2025-10-15 21:43:17'),
(8, 2, 'tuesday', '10:00:00', '13:00:00', 5.00, 1, '2025-10-15 21:43:17'),
(9, 2, 'wednesday', '10:00:00', '13:00:00', 5.00, 1, '2025-10-15 21:43:17'),
(10, 2, 'thursday', '10:00:00', '13:00:00', 5.00, 1, '2025-10-15 21:43:17'),
(11, 2, 'friday', '10:00:00', '13:00:00', 5.00, 1, '2025-10-15 21:43:17');

-- --------------------------------------------------------

--
-- Table structure for table `place_bookings`
--

CREATE TABLE `place_bookings` (
  `booking_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `booking_type` enum('hourly','daily','weekly','monthly') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 1,
  `duration_hours` decimal(10,2) DEFAULT 0.00,
  `booking_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`booking_days`)),
  `total_days` int(11) NOT NULL DEFAULT 1,
  `base_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `equipment_cost` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_cost` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `equipment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`equipment_details`)),
  `booking_status` enum('pending','confirmed','cancelled','completed','no_show') DEFAULT 'pending',
  `payment_status` enum('pending','partial','paid','refunded') DEFAULT 'pending',
  `special_requirements` text DEFAULT NULL,
  `contact_person` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(100) DEFAULT NULL,
  `booking_notes` text DEFAULT NULL,
  `additional_requests` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `place_bookings`
--

INSERT INTO `place_bookings` (`booking_id`, `place_id`, `company_id`, `booking_type`, `start_date`, `end_date`, `start_time`, `end_time`, `duration_days`, `duration_hours`, `booking_days`, `total_days`, `base_rate`, `equipment_cost`, `subtotal`, `discount_amount`, `tax_amount`, `total_cost`, `currency`, `equipment_details`, `booking_status`, `payment_status`, `special_requirements`, `contact_person`, `contact_email`, `contact_phone`, `booking_notes`, `additional_requests`, `cancellation_reason`, `created_at`, `updated_at`, `confirmed_at`, `cancelled_at`) VALUES
(2, 2, 18, 'hourly', '2026-01-01', '2026-04-04', '13:59:00', '13:59:00', 1, 0.00, NULL, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 5.00, 'USD', NULL, 'pending', 'pending', 'mmmmmmmmmmmmmmm', 'Darwish', 'ghinadarwish99@gmail.com', '76137857', 'mmmmmmmmmmmmmmmmmmmm', NULL, NULL, '2025-11-05 19:47:33', '2025-11-05 19:47:33', NULL, NULL),
(3, 8, 13, 'hourly', '2026-01-01', '2026-01-20', '13:00:00', '01:00:00', 20, 444.00, '[\"2026-01-01\",\"2026-01-02\",\"2026-01-03\",\"2026-01-04\",\"2026-01-05\",\"2026-01-06\",\"2026-01-07\",\"2026-01-08\",\"2026-01-09\",\"2026-01-10\",\"2026-01-11\",\"2026-01-12\",\"2026-01-13\",\"2026-01-14\",\"2026-01-15\",\"2026-01-16\",\"2026-01-17\",\"2026-01-18\",\"2026-01-19\",\"2026-01-20\"]', 20, 222.00, 160.00, 382.00, 0.00, 38.20, 420.20, 'USD', '[{\"item_id\":25,\"quantity\":1,\"unit_price\":1,\"total_price\":20},{\"item_id\":24,\"quantity\":1,\"unit_price\":3,\"total_price\":60},{\"item_id\":27,\"quantity\":1,\"unit_price\":4,\"total_price\":80}]', 'pending', 'pending', '', 'Darwish', 'hello@gmail.com', '76137857', '', '', NULL, '2025-11-05 21:25:15', '2025-11-05 21:25:15', NULL, NULL),
(4, 7, 19, 'hourly', '2025-11-11', '2026-01-01', '01:00:00', '02:00:00', 38, 1225.00, '[\"2025-11-11\",\"2025-11-12\",\"2025-11-13\",\"2025-11-14\",\"2025-11-17\",\"2025-11-18\",\"2025-11-19\",\"2025-11-20\",\"2025-11-21\",\"2025-11-24\",\"2025-11-25\",\"2025-11-26\",\"2025-11-27\",\"2025-11-28\",\"2025-12-01\",\"2025-12-02\",\"2025-12-03\",\"2025-12-04\",\"2025-12-05\",\"2025-12-08\",\"2025-12-09\",\"2025-12-10\",\"2025-12-11\",\"2025-12-12\",\"2025-12-15\",\"2025-12-16\",\"2025-12-17\",\"2025-12-18\",\"2025-12-19\",\"2025-12-22\",\"2025-12-23\",\"2025-12-24\",\"2025-12-25\",\"2025-12-26\",\"2025-12-29\",\"2025-12-30\",\"2025-12-31\",\"2026-01-01\"]', 38, 61250.00, 0.00, 61250.00, 0.00, 6125.00, 67375.00, 'USD', '[]', 'pending', 'pending', '', 'Darwish', 'hello@gmail.com', '76137857', '', '', NULL, '2025-11-05 21:50:30', '2025-11-05 21:50:30', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `place_equipment`
--

CREATE TABLE `place_equipment` (
  `place_equipment_id` int(11) NOT NULL,
  `place_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity_available` int(11) DEFAULT 1,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `place_equipment`
--

INSERT INTO `place_equipment` (`place_equipment_id`, `place_id`, `item_id`, `quantity_available`, `custom_price`, `is_available`, `created_at`) VALUES
(1, 8, 13, 2, 15.00, 1, '2025-11-05 21:03:15'),
(2, 8, 14, 1, 25.00, 1, '2025-11-05 21:03:15'),
(3, 8, 16, 1, 30.00, 1, '2025-11-05 21:03:15'),
(4, 8, 17, 4, 10.00, 1, '2025-11-05 21:03:15'),
(5, 8, 19, 2, 8.00, 1, '2025-11-05 21:03:15'),
(6, 8, 20, 1, 12.00, 1, '2025-11-05 21:03:15'),
(7, 8, 23, 10, 2.00, 1, '2025-11-05 21:03:15'),
(8, 8, 24, 10, 3.00, 1, '2025-11-05 21:03:15'),
(9, 8, 25, 20, 1.00, 1, '2025-11-05 21:03:15'),
(10, 8, 27, 1, 4.00, 1, '2025-11-05 21:03:15');

-- --------------------------------------------------------

--
-- Table structure for table `place_evaluations`
--

CREATE TABLE `place_evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `evaluation_text` text DEFAULT NULL,
  `location_quality` int(11) NOT NULL CHECK (`location_quality` >= 1 and `location_quality` <= 5),
  `cleanliness` int(11) NOT NULL CHECK (`cleanliness` >= 1 and `cleanliness` <= 5),
  `amenities` int(11) NOT NULL CHECK (`amenities` >= 1 and `amenities` <= 5),
  `value_for_money` int(11) NOT NULL CHECK (`value_for_money` >= 1 and `value_for_money` <= 5),
  `would_recommend` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `place_packages`
--

CREATE TABLE `place_packages` (
  `place_package_id` int(11) NOT NULL,
  `place_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `place_reviews`
--

CREATE TABLE `place_reviews` (
  `review_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `cleanliness_rating` int(11) DEFAULT NULL CHECK (`cleanliness_rating` >= 1 and `cleanliness_rating` <= 5),
  `equipment_rating` int(11) DEFAULT NULL CHECK (`equipment_rating` >= 1 and `equipment_rating` <= 5),
  `location_rating` int(11) DEFAULT NULL CHECK (`location_rating` >= 1 and `location_rating` <= 5),
  `value_rating` int(11) DEFAULT NULL CHECK (`value_rating` >= 1 and `value_rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `university` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `student_id_number` varchar(50) DEFAULT NULL,
  `academic_year` varchar(50) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `expected_graduation` varchar(7) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `training_interests` text DEFAULT NULL,
  `preferred_fields` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `profile_photo` varchar(500) DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','graduated') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `university`, `email`, `password`, `department`, `phone`, `student_id_number`, `academic_year`, `gpa`, `expected_graduation`, `date_of_birth`, `training_interests`, `preferred_fields`, `skills`, `profile_photo`, `cv_path`, `created_at`, `reset_code`, `reset_code_expiry`, `instructor_id`, `status`) VALUES
(11, 'khaled', 'kabadaki', 'jinan', 'kabadakik4@gmail.com', '$2y$10$fUXwd8c42HF8xlWjiWSwyOzpl7Ithj0ilLhdKM1vWf4/GLMmb9Xiu', 'CS', '71860084', 'STU11', 'Not specified', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/students/cv/1760136066_Industry manager resume.pdf', '2025-10-10 22:41:06', NULL, NULL, NULL, 'active'),
(12, 'fadi', 'kabadaki', 'LIU', 'kabadakik10@gmail.com', '$2y$10$9EixfAua.GYNv3AWgzOem.IuGeLGWwaUTnd0Uhmzy6/Yb8L/Dl52a', 'accounting', '', 'STU12', 'Not specified', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/students/cv/1760136153_Modern nursing resume.pdf', '2025-10-10 22:42:33', NULL, NULL, NULL, 'active'),
(13, 'ayman', 'darwish', 'jinan', 'darwishayman@gmail.com', '$2y$10$loXe4muFDjFo1D3S56RgRuBhzNokEeAtk8IGG9npVEeCR3/ZNUw/y', 'faculty of science', '76137857', 'STU13', 'Not specified', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/students/cv/1760556386_triangle_worksheet_A4.pdf', '2025-10-15 19:26:26', NULL, NULL, NULL, 'active'),
(14, 'jana', 'darwish', 'jinan', 'darwishayman442@gmail.com', '$2y$10$ux1ktjqrBadBvqvOROC85OmNvYEGvYEu3jp.Kc7Iy8EzsBDW.l3Ny', 'faculty of engineering', '00961 76137857', 'STU14', '5th Year', NULL, '2026-02', NULL, '................................,,,,,,,,MKJH', ',M,NKJHGHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHH', 'HGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGG', 'uploads/students/profile_photos/68fbd3a288146_1761334178.png', 'uploads/students/cv/1760559741_triangle_worksheet_A4.pdf', '2025-10-15 20:22:21', NULL, NULL, NULL, 'active'),
(17, 'gheena', 'kabadaki', 'AUB', 'gheenakabadaki@gmail.com', '$2y$10$305BYuPwekM2.icx3pwDi.uns483A64d5IE4LD5.9nk93SZpw8uTC', 'faculty of science', '76137857', '10220898', '5th Year', 3.00, '2025-12', '2004-11-28', 'KKKKKKKKKKKKKKKKKKK', 'KKKKKKKKKKKKKKKKKKK', 'KKKKKKKKK', 'uploads/students/profile_photos/1762212505_Gemini_Generated_Image_8a5g48a5g48a5g48-removebg-preview.png', 'uploads/students/cv/1762212505_triangle_worksheet_A4.pdf', '2025-11-03 23:28:25', NULL, NULL, NULL, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_equipment`
--
ALTER TABLE `booking_equipment`
  ADD PRIMARY KEY (`booking_equipment_id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_item_id` (`item_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_certificate_number` (`certificate_number`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `company_locations`
--
ALTER TABLE `company_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_location_type` (`location_type`),
  ADD KEY `idx_is_primary` (`is_primary`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `idx_instructor_id` (`instructor_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`course_category`),
  ADD KEY `idx_level` (`course_level`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_courses_instructor_status` (`instructor_id`,`status`),
  ADD KEY `idx_courses_category_status` (`course_category`,`status`),
  ADD KEY `idx_created_by` (`created_by_type`,`created_by_id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`course_id`,`student_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_enrollments_course_status` (`course_id`,`status`);

--
-- Indexes for table `course_evaluations`
--
ALTER TABLE `course_evaluations`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD UNIQUE KEY `unique_course_evaluation` (`student_id`,`internship_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_internship_id` (`internship_id`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD PRIMARY KEY (`course_instructor_id`),
  ADD UNIQUE KEY `unique_course_instructor` (`course_id`,`instructor_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `course_messages`
--
ALTER TABLE `course_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`course_id`,`student_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_reviews_course_rating` (`course_id`,`rating`);

--
-- Indexes for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `equipment_items`
--
ALTER TABLE `equipment_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `equipment_packages`
--
ALTER TABLE `equipment_packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`instructor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `instructor_applications`
--
ALTER TABLE `instructor_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `unique_application` (`instructor_request_id`,`instructor_id`),
  ADD KEY `idx_instructor_request_id` (`instructor_request_id`),
  ADD KEY `idx_instructor_id` (`instructor_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_applied_at` (`applied_at`),
  ADD KEY `idx_instructor_applications_request_status` (`instructor_request_id`,`status`);

--
-- Indexes for table `instructor_evaluations`
--
ALTER TABLE `instructor_evaluations`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD UNIQUE KEY `unique_evaluation` (`company_id`,`instructor_id`,`instructor_request_id`),
  ADD KEY `instructor_request_id` (`instructor_request_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_instructor_id` (`instructor_id`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `instructor_requests`
--
ALTER TABLE `instructor_requests`
  ADD PRIMARY KEY (`instructor_request_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_course_type` (`course_type`),
  ADD KEY `idx_experience_level` (`experience_level`),
  ADD KEY `idx_application_deadline` (`application_deadline`),
  ADD KEY `idx_instructor_requests_company_status` (`company_id`,`status`),
  ADD KEY `idx_instructor_requests_deadline_status` (`application_deadline`,`status`);

--
-- Indexes for table `instructor_reviews`
--
ALTER TABLE `instructor_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`company_id`,`instructor_id`,`instructor_request_id`),
  ADD KEY `instructor_request_id` (`instructor_request_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_instructor_id` (`instructor_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_application_deadline` (`application_deadline`);

--
-- Indexes for table `internship_applications`
--
ALTER TABLE `internship_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `unique_application` (`internship_id`,`student_id`),
  ADD KEY `idx_applications_internship` (`internship_id`),
  ADD KEY `idx_applications_student` (`student_id`),
  ADD KEY `idx_applications_status` (`status`),
  ADD KEY `idx_applications_date` (`application_date`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `package_items`
--
ALTER TABLE `package_items`
  ADD PRIMARY KEY (`package_item_id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`place_id`),
  ADD KEY `idx_places_company` (`company_id`),
  ADD KEY `idx_places_type` (`place_type`),
  ADD KEY `idx_places_city` (`city`),
  ADD KEY `idx_places_status` (`status`),
  ADD KEY `idx_space_type` (`space_type`);

--
-- Indexes for table `place_availability`
--
ALTER TABLE `place_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD UNIQUE KEY `unique_place_day_time` (`place_id`,`day_of_week`,`start_time`),
  ADD KEY `idx_availability_place` (`place_id`);

--
-- Indexes for table `place_bookings`
--
ALTER TABLE `place_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_bookings_place` (`place_id`),
  ADD KEY `idx_bookings_company` (`company_id`),
  ADD KEY `idx_bookings_status` (`booking_status`),
  ADD KEY `idx_bookings_dates` (`start_date`,`end_date`);

--
-- Indexes for table `place_equipment`
--
ALTER TABLE `place_equipment`
  ADD PRIMARY KEY (`place_equipment_id`);

--
-- Indexes for table `place_evaluations`
--
ALTER TABLE `place_evaluations`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD UNIQUE KEY `unique_place_evaluation` (`company_id`,`place_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_place_id` (`place_id`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `place_packages`
--
ALTER TABLE `place_packages`
  ADD PRIMARY KEY (`place_package_id`);

--
-- Indexes for table `place_reviews`
--
ALTER TABLE `place_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_booking_review` (`booking_id`),
  ADD KEY `idx_reviews_place` (`place_id`),
  ADD KEY `idx_reviews_company` (`company_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_students_instructor` (`instructor_id`),
  ADD KEY `idx_students_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_equipment`
--
ALTER TABLE `booking_equipment`
  MODIFY `booking_equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `company_locations`
--
ALTER TABLE `company_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_evaluations`
--
ALTER TABLE `course_evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_instructors`
--
ALTER TABLE `course_instructors`
  MODIFY `course_instructor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_messages`
--
ALTER TABLE `course_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_reviews`
--
ALTER TABLE `course_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `equipment_items`
--
ALTER TABLE `equipment_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `equipment_packages`
--
ALTER TABLE `equipment_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `instructor_applications`
--
ALTER TABLE `instructor_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `instructor_evaluations`
--
ALTER TABLE `instructor_evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `instructor_requests`
--
ALTER TABLE `instructor_requests`
  MODIFY `instructor_request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `instructor_reviews`
--
ALTER TABLE `instructor_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `internship_applications`
--
ALTER TABLE `internship_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `package_items`
--
ALTER TABLE `package_items`
  MODIFY `package_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `place_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `place_availability`
--
ALTER TABLE `place_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `place_bookings`
--
ALTER TABLE `place_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `place_equipment`
--
ALTER TABLE `place_equipment`
  MODIFY `place_equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `place_evaluations`
--
ALTER TABLE `place_evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `place_packages`
--
ALTER TABLE `place_packages`
  MODIFY `place_package_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `place_reviews`
--
ALTER TABLE `place_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `company_locations`
--
ALTER TABLE `company_locations`
  ADD CONSTRAINT `company_locations_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_evaluations`
--
ALTER TABLE `course_evaluations`
  ADD CONSTRAINT `course_evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_evaluations_ibfk_2` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD CONSTRAINT `course_instructors_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_instructors_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_messages`
--
ALTER TABLE `course_messages`
  ADD CONSTRAINT `course_messages_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD CONSTRAINT `course_reviews_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_reviews_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment_items`
--
ALTER TABLE `equipment_items`
  ADD CONSTRAINT `equipment_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_applications`
--
ALTER TABLE `instructor_applications`
  ADD CONSTRAINT `instructor_applications_ibfk_1` FOREIGN KEY (`instructor_request_id`) REFERENCES `instructor_requests` (`instructor_request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instructor_applications_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_evaluations`
--
ALTER TABLE `instructor_evaluations`
  ADD CONSTRAINT `instructor_evaluations_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instructor_evaluations_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instructor_evaluations_ibfk_3` FOREIGN KEY (`instructor_request_id`) REFERENCES `instructor_requests` (`instructor_request_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_requests`
--
ALTER TABLE `instructor_requests`
  ADD CONSTRAINT `instructor_requests_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_reviews`
--
ALTER TABLE `instructor_reviews`
  ADD CONSTRAINT `instructor_reviews_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instructor_reviews_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instructor_reviews_ibfk_3` FOREIGN KEY (`instructor_request_id`) REFERENCES `instructor_requests` (`instructor_request_id`) ON DELETE CASCADE;

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `internship_applications`
--
ALTER TABLE `internship_applications`
  ADD CONSTRAINT `internship_applications_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internship_applications_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`) ON DELETE CASCADE;

--
-- Constraints for table `places`
--
ALTER TABLE `places`
  ADD CONSTRAINT `places_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `place_availability`
--
ALTER TABLE `place_availability`
  ADD CONSTRAINT `place_availability_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`place_id`) ON DELETE CASCADE;

--
-- Constraints for table `place_bookings`
--
ALTER TABLE `place_bookings`
  ADD CONSTRAINT `place_bookings_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`place_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `place_bookings_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `place_evaluations`
--
ALTER TABLE `place_evaluations`
  ADD CONSTRAINT `place_evaluations_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `place_evaluations_ibfk_2` FOREIGN KEY (`place_id`) REFERENCES `places` (`place_id`) ON DELETE CASCADE;

--
-- Constraints for table `place_reviews`
--
ALTER TABLE `place_reviews`
  ADD CONSTRAINT `place_reviews_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`place_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `place_reviews_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `place_reviews_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `place_bookings` (`booking_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
