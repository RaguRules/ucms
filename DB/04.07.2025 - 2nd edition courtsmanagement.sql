-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 04, 2025 at 03:22 PM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Laid By','Appeal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_warrant` tinyint(1) DEFAULT '0',
  `next_date` date DEFAULT NULL,
  `for_what` varchar(100) DEFAULT NULL,
  `staff_id` varchar(10) DEFAULT NULL,
  `court_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`case_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

DROP TABLE IF EXISTS `courts`;
CREATE TABLE IF NOT EXISTS `courts` (
  `court_id` varchar(5) NOT NULL,
  `court_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `court_status` tinyint NOT NULL,
  PRIMARY KEY (`court_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dailycaseactivities`
--

DROP TABLE IF EXISTS `dailycaseactivities`;
CREATE TABLE IF NOT EXISTS `dailycaseactivities` (
  `activity_id` varchar(10) NOT NULL,
  `case_name` varchar(20) DEFAULT NULL,
  `summary` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `next_date` date DEFAULT NULL,
  `current_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Laid By','Appeal','Completed/ Closed','Appeal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `next_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Completed/ Closed','Appeal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_taken` tinyint(1) DEFAULT '0',
  `activity_date` date DEFAULT NULL,
  `staff_id` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer`
--

DROP TABLE IF EXISTS `lawyer`;
CREATE TABLE IF NOT EXISTS `lawyer` (
  `lawyer_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile` int DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `enrolment_number` varchar(20) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role_id` varchar(3) DEFAULT NULL,
  `station` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'ALL',
  `image_path` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `added_by` varchar(30) NOT NULL,
  `staff_id` varchar(30) NOT NULL,
  PRIMARY KEY (`lawyer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `role_id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `motions`
--

DROP TABLE IF EXISTS `motions`;
CREATE TABLE IF NOT EXISTS `motions` (
  `motion_id` varchar(10) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `filed_by` varchar(20) DEFAULT NULL,
  `filed_date` date DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `remarks` text,
  PRIMARY KEY (`motion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` varchar(10) NOT NULL,
  `record_id` varchar(10) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `court_id` varchar(12) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `message` text,
  `receiver_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

DROP TABLE IF EXISTS `parties`;
CREATE TABLE IF NOT EXISTS `parties` (
  `party_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile` int DEFAULT NULL,
  `nic_number` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `added_by` varchar(50) NOT NULL,
  `staff_id` varchar(20) NOT NULL,
  PRIMARY KEY (`party_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `police`
--

DROP TABLE IF EXISTS `police`;
CREATE TABLE IF NOT EXISTS `police` (
  `police_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile` int DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `badge_number` varchar(20) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role_id` varchar(3) DEFAULT NULL,
  `station` varchar(20) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `added_by` varchar(30) NOT NULL,
  `staff_id` varchar(30) NOT NULL,
  PRIMARY KEY (`police_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

DROP TABLE IF EXISTS `registration`;
CREATE TABLE IF NOT EXISTS `registration` (
  `reg_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `badge_number` varchar(20) DEFAULT NULL,
  `station` varchar(30) DEFAULT NULL,
  `enrolment_number` varchar(20) DEFAULT NULL,
  `role_id` varchar(5) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `status` varchar(10) DEFAULT 'Pending',
  `image_path` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`reg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `role_status` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` varchar(10) NOT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `mobile` int DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `court_id` varchar(10) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role_id` varchar(3) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `appointment` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
