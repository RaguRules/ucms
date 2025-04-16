-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 12, 2025 at 11:36 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `courtsmanagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `appeals`
--

DROP TABLE IF EXISTS `appeals`;
CREATE TABLE IF NOT EXISTS `appeals` (
  `appeal_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `appellant_party_id` varchar(10) DEFAULT NULL,
  `respondent_party_id` varchar(10) DEFAULT NULL,
  `appeal_date` date DEFAULT NULL,
  `appeal_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`appeal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

DROP TABLE IF EXISTS `cases`;
CREATE TABLE IF NOT EXISTS `cases` (
  `case_id` varchar(10) NOT NULL,
  `case_name` varchar(20) DEFAULT NULL,
  `plaintiff` varchar(50) DEFAULT NULL,
  `defendant` varchar(50) DEFAULT NULL,
  `plaintiff_lawyer` varchar(50) DEFAULT NULL,
  `defendant_lawyer` varchar(50) DEFAULT NULL,
  `registered_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `nature` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `is_warrant` tinyint(1) DEFAULT '0',
  `next_date` date DEFAULT NULL,
  `for_what` varchar(100) DEFAULT NULL,
  `staff_id` varchar(10) DEFAULT NULL,
  `court_name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

DROP TABLE IF EXISTS `courts`;
CREATE TABLE IF NOT EXISTS `courts` (
  `court_id` varchar(5) NOT NULL,
  `court_name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`court_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dailycaseactivities`
--

DROP TABLE IF EXISTS `dailycaseactivities`;
CREATE TABLE IF NOT EXISTS `dailycaseactivities` (
  `activity_id` varchar(10) NOT NULL,
  `case_name` varchar(20) DEFAULT NULL,
  `summary` varchar(20) DEFAULT NULL,
  `next_date` date DEFAULT NULL,
  `for_what` varchar(100) DEFAULT NULL,
  `current_status` varchar(20) DEFAULT NULL,
  `next_status` varchar(20) DEFAULT NULL,
  `is_taken` tinyint(1) DEFAULT '0',
  `activity_date` date DEFAULT NULL,
  `staff_id` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

DROP TABLE IF EXISTS `fines`;
CREATE TABLE IF NOT EXISTS `fines` (
  `fine_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `party_id` varchar(10) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `imposed_on` date DEFAULT NULL,
  `paid_on` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`fine_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `judgements`
--

DROP TABLE IF EXISTS `judgements`;
CREATE TABLE IF NOT EXISTS `judgements` (
  `jud_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `is_contested` tinyint(1) DEFAULT '0',
  `given_on` date DEFAULT NULL,
  `staff_id` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`jud_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer`
--

DROP TABLE IF EXISTS `lawyer`;
CREATE TABLE IF NOT EXISTS `lawyer` (
  `lawyer_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` int DEFAULT NULL,
  `email_address` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `enrolment_number` varchar(20) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role_id` varchar(3) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`lawyer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
CREATE TABLE IF NOT EXISTS `login` (
  `username` varchar(50) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `attempt` int DEFAULT '0',
  `otp` int DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `user_type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `motions`
--

DROP TABLE IF EXISTS `motions`;
CREATE TABLE IF NOT EXISTS `motions` (
  `motion_id` varchar(10) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `filed_date` date DEFAULT NULL,
  PRIMARY KEY (`motion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `note_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `details` text,
  `created_date` date DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `role_id` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` varchar(10) NOT NULL,
  `record_id` varchar(10) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `message` text,
  `receiver_id` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `is_calculated` tinyint(1) DEFAULT '0',
  `given_on` date DEFAULT NULL,
  `staff_id` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

DROP TABLE IF EXISTS `parties`;
CREATE TABLE IF NOT EXISTS `parties` (
  `party_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile_number` int DEFAULT NULL,
  `nic_no` varchar(12) DEFAULT NULL,
  `email_address` varchar(50) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`party_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `category` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `slip_no` varchar(50) NOT NULL,
  `staff_id` int NOT NULL,
  `case_no` int NOT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `slip_no` (`slip_no`),
  KEY `staff_id` (`staff_id`),
  KEY `case_no` (`case_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `police`
--

DROP TABLE IF EXISTS `police`;
CREATE TABLE IF NOT EXISTS `police` (
  `police_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` int DEFAULT NULL,
  `email_address` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `badge_number` varchar(20) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role_id` varchar(3) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`police_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

DROP TABLE IF EXISTS `registration`;
CREATE TABLE IF NOT EXISTS `registration` (
  `reg_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` int DEFAULT NULL,
  `email_address` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `badge_number` varchar(20) DEFAULT NULL,
  `enrolment_number` varchar(20) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `status` varchar(10) DEFAULT 'Pending',
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`reg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` varchar(3) NOT NULL,
  `role_name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
('R01', 'ADMIN'),
('R02', 'JUDGE'),
('R03', 'REGISTRAR'),
('R04', 'INTERPRETER'),
('R05', 'COMMON_STAFF'),
('R06', 'LAWYER'),
('R07', 'POLICE');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` varchar(10) NOT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `mobile_number` int DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `court_name` varchar(20) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role_id` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `mobile_number`, `nic_number`, `date_of_birth`, `email`, `address`, `court_name`, `join_date`, `is_active`, `role_id`) VALUES
('S0001', 'Raguraj', 'Srirajeswaran', 777958841, '940613582V', '1990-01-01', 'raguraj94@hotmail.co.uk', 'Meesalai west, Meesalai.', 'High Court', '2005-02-26', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `warrants`
--

DROP TABLE IF EXISTS `warrants`;
CREATE TABLE IF NOT EXISTS `warrants` (
  `warrant_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `issued_for_party_id` varchar(10) DEFAULT NULL,
  `issued_by_staff_id` varchar(10) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `warrant_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`warrant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
