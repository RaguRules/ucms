-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 10, 2025 at 05:06 PM
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
  `court_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`case_id`, `case_name`, `plaintiff`, `defendant`, `plaintiff_lawyer`, `defendant_lawyer`, `registered_date`, `is_active`, `nature`, `status`, `is_warrant`, `next_date`, `for_what`, `staff_id`, `court_id`) VALUES
('C0001', '54666', 'Amuthalingam', 'Sakkarapani', 'L0001', 'L0002', '2025-05-01', 1, 'CRIMINAL', 'Calling', 1, '2025-06-06', 'for replication', 'S0001', 'C01'),
('C0002', 'L/115/16', 'Maruthanayakam', 'Ananthy', 'L0001', 'L0003', '2025-05-01', 1, 'CIVIL', 'Pre Trial Conference', 0, '2026-05-12', 'For Consideration', 'S0001', 'C02');

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

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`court_id`, `court_name`) VALUES
('C01', 'Magistrate\'s Court'),
('C02', 'District Court'),
('C03', 'High Court');

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
  `mobile` int DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `nic_number` varchar(12) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `enrolment_number` varchar(20) DEFAULT NULL,
  `joined_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role_id` varchar(3) DEFAULT NULL,
  `station` varchar(20) DEFAULT 'ALL',
  `image_path` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`lawyer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lawyer`
--

INSERT INTO `lawyer` (`lawyer_id`, `first_name`, `last_name`, `mobile`, `email`, `address`, `nic_number`, `date_of_birth`, `enrolment_number`, `joined_date`, `is_active`, `role_id`, `station`, `image_path`, `gender`) VALUES
('L0001', 'Ragu', 'AAL', 777958842, 'sri@gmail.com', 'Jaffna', '199406106666', '0000-00-00', '66666666666', '2025-04-27', 1, 'R06', 'Private', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`username`, `password`, `attempt`, `otp`, `status`, `role_id`) VALUES
('dummy@rolechange.comg', '$2y$10$9OZTe7MMUoxiD2t6aHw70eZZ3H65teyDm.z7ElB5kLNfXDR25/vHS', 0, 0, 'active', 'R01'),
('sri@gmail.com', '$2y$10$YLuMNln5/7L2LGMQ.D9kcOKZWx5TJl1FeBHJDxHeSLd.Wmx2qND.O', 0, 1329, 'active', 'R06');

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

--
-- Dumping data for table `parties`
--

INSERT INTO `parties` (`party_id`, `first_name`, `last_name`, `mobile_number`, `nic_no`, `email_address`, `joined_date`, `address`, `date_of_birth`, `is_deleted`) VALUES
('P0001', 'party_fname', 'party_lname', 712345678, '198065456788', 'party1@gmail.com', '2025-05-01', 'Paampan, Poonakari', '1985-05-10', 0);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` varchar(3) NOT NULL,
  `role_name` varchar(20) DEFAULT NULL,
  `role_status` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_status`) VALUES
('R01', 'ADMIN', '1'),
('R02', 'JUDGE', '1'),
('R03', 'REGISTRAR', '1'),
('R04', 'INTERPRETER', '1'),
('R05', 'COMMON_STAFF', '1'),
('R06', 'LAWYER', '1'),
('R07', 'POLICE', '1'),
('R08', 'Other Station VIPs', '1'),
('R09', 'lol', '1'),
('R10', 'a', '0'),
('R11', 'g', '0'),
('R12', 'Outer staff', '0');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `mobile`, `nic_number`, `date_of_birth`, `email`, `address`, `court_id`, `joined_date`, `is_active`, `role_id`, `image_path`, `gender`, `appointment`) VALUES
('S0001', 'Selvamathy', 'Kulasingam', 777777777, '197406103583', '2025-04-17', 'reg@reg.reg', 'PP', 'C01', '2025-04-18', 1, 'R03', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0002', 'Raguraj', 'Srirajeswaran', 777958841, '199406103585', '1994-03-01', 'sriraguraj@gmail.com', 'Meesalai', 'C03', '2025-04-19', 1, 'R03', 'uploads/img_680d251812b3a2.88472394.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0003', 'firstname', 'lastname', 777958840, '199406103580', '1956-05-02', 'email@gmail.com', 'address', 'C01', '2025-04-19', 0, 'R05', 'uploads/img_680d23cbd50d20.02873809.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0004', 'Ragu', 'Raj', 773563868, '199406103583', '1994-03-01', 'raguraj94@hotmail.co.uk', 'Chavakachcheri', 'C03', '2025-04-26', 1, 'R01', 'uploads/img_680cf677d2d3a3.98806943.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0005', 'oes', 'oes', 765555555, '199406103511', '2025-04-30', 'dfv@edfv.com', 'ugf786', 'C01', '2025-04-26', 0, 'R05', 'uploads/img_680d09e9ae7ee4.16129921.jpeg', 'Male', 'O.E.S/ Peon/ Security'),
('S0006', 'staff', 'role', 776543322, '198076543333', '1980-09-20', 'dummy@rolechange.com', 'Nallur', 'C01', '2025-05-04', 0, 'R03', 'uploads/img_68176471af2782.25215949.jpeg', 'Male', 'Judicial Staff (JSC)');

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
