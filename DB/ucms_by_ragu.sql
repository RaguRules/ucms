-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 13, 2025 at 12:13 PM
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

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`case_id`, `case_name`, `plaintiff`, `defendant`, `plaintiff_lawyer`, `defendant_lawyer`, `registered_date`, `is_active`, `nature`, `status`, `is_warrant`, `next_date`, `for_what`, `staff_id`, `court_id`) VALUES
('C0002', 'L/115/16', 'P0002', 'P0001', 'L0001', 'L0005', '2025-05-01', 1, 'Criminal', 'Appeal', 0, '2026-05-12', 'Appeal', 'L0005', 'C04'),
('C0003', '110111', 'CID', 'SUREN', 'ARJUNA', 'SARNIYA', '2025-05-13', 1, 'Criminal', 'Trial', 1, '2025-12-31', 'Trial', 'L0005', 'Magistrate&#039;s co'),
('C0004', 'D/934/15', 'RAJAN', 'KEETHA', 'SARNIYA', 'BANUSHA', '2025-05-13', 1, 'Civil', 'Calling', 0, '2025-08-29', 'Calling', 'L0005', 'District Court'),
('C0005', 'M/906', 'PO', 'IUY', 'IOHUGYFT', 'IOUGYFT', '2025-05-13', 1, 'Civil', 'Calling', 0, '2025-12-31', 'Calling', 'L0005', 'District Court'),
('C10000', 'MIS/254/25', 'PARHA', 'MATHI, KOMATHI, SAREN', 'ARCHANA', 'SIVASANTHI', '2025-05-13', 1, 'Civil', 'Judgement', 1, '2027-12-31', 'Judgement', 'L0005', 'C02'),
('C9999', 'L/420/23', 'AMALA', 'COOLMAN', 'BUGCYF', 'JGHGC', '2025-05-13', 1, 'Civil', 'Calling', 0, '2025-12-31', 'Pre Trial Conference', 'L0005', 'District Court');

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

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`court_id`, `court_name`, `court_status`) VALUES
('C01', 'Magistrate\'s Court', 1),
('C02', 'District Court', 1),
('C03', 'High Court', 1),
('C04', 'Juvenile Magistrate\'s Court', 0),
('C05', 'Commercial High Court', 0),
('C06', 'kj', 1);

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
  `current_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Laid By','Appeal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `next_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_taken` tinyint(1) DEFAULT '0',
  `activity_date` date DEFAULT NULL,
  `staff_id` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dailycaseactivities`
--

INSERT INTO `dailycaseactivities` (`activity_id`, `case_name`, `summary`, `next_date`, `current_status`, `next_status`, `is_taken`, `activity_date`, `staff_id`) VALUES
('A0001', 'C0002', 'Plaintiff counsel moves date. Defendant has no objection. Thus, Trial is postponed.', '2025-05-12', '', 'Trial', 0, '2025-05-11', 'S0001'),
('A0002', 'C0002', 'Evidence of witness-03 of plaintiff is recorded. Plaintiff trial is concluded. Defendant says he no need to lead for evidence. Defendant trial also concluded. Fixed for Judgement. File W/S at registry both parties.', '2025-11-06', 'Trial', 'Judgement', 1, '2025-05-12', 'S0001');

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
  `station` varchar(20) DEFAULT 'ALL',
  `image_path` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `added_by` varchar(30) NOT NULL,
  `staff_id` varchar(30) NOT NULL,
  PRIMARY KEY (`lawyer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lawyer`
--

INSERT INTO `lawyer` (`lawyer_id`, `first_name`, `last_name`, `mobile`, `email`, `address`, `nic_number`, `date_of_birth`, `enrolment_number`, `joined_date`, `is_active`, `role_id`, `station`, `image_path`, `gender`, `added_by`, `staff_id`) VALUES
('L0001', 'Ragu', 'AAL', 777958842, 'sri@gmail.com', 'Jaffna', '199406106666', '0000-00-00', '66666666666', '2025-04-27', 1, 'R06', 'Private', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male', '', ''),
('L0002', 'ooo', 'lll', 779999999, '9878t@gmail.com', 'kiug7f86ocl hk.;vypfotfy', '199409876543', '1994-04-07', 'jhvyuct', '2025-05-14', 1, 'R06', 'Legal Aid Commission', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male', 'R06', 'L0005'),
('L0003', 'new bee', 'law', 777958888, 'bee@charmy.com', 'galle', '199876545678', '1998-09-21', 'edfgh', '2025-05-12', 0, 'R06', 'Legal Aid Commission', 'uploads/img_68219abe476644.45439479.jpeg', 'Female', '', ''),
('L0004', 'ican', 'lab', 754497253, 'sriraguraj@yahoo.com', 'Meesalai', '940613581V', '1994-03-01', 'ihut', '2025-05-12', 0, 'R06', 'Legal Aid Commission', 'uploads/img_6821b0534a4ab5.39256854.jpeg', 'Male', 'R03', 'L0004'),
('L0005', 'staff', 'lawyer', 754497452, 'ragu@ragu.com', 'mee', '940613582v', '1994-03-01', 'r7ctvyibuon', '2025-05-12', 1, 'R06', 'Legal Aid Commission', 'uploads/img_6821da3516f099.01811946.jpeg', 'Male', 'R03', 'L0005'),
('L0006', 'dam', 'dam', 777654432, 'dfgb@gmail.com', 'ugyf', '765456789876', '7654-03-07', 'rrr', '2025-05-12', 0, 'R06', 'Legal Aid Commission', 'uploads/img_6821f95435df41.34396599.jpeg', 'Female', 'R06', 'L0005');

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

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`username`, `password`, `attempt`, `otp`, `status`, `role_id`) VALUES
('bee@charmy.com', '$2y$10$F4ZVuX.x9mX4wVHoRoJsEuNWgy2qgNejcPyuu2ojNkxBoDeM/vFy2', 0, 0, 'deleted', 'R06'),
('dfgb@gmail.com', '$2y$10$ce1iwT5PclVgsqZcggI.aOUCYhKVURGOgam5J9iH/CiItJC15Ib.W', 0, 0, 'active', 'R06'),
('dummy@rolechange2.com', '$2y$10$9OZTe7MMUoxiD2t6aHw70eZZ3H65teyDm.z7ElB5kLNfXDR25/vHS', 0, 0, 'active', 'R05'),
('igp@police.lk', '$2y$10$Nq6RE3f/h2XpAlZEx6Mw8uKEGt3L63QRD4aFeX7HrtMaBw.ikJyhq', 0, 0, 'active', 'R06'),
('phpoop@php.net', '$2y$10$EwLIr4qTBLfr.BuutbBASOZSo2KF.y5gYofjn4Dqp5nZI8mRr9/x2', 0, 0, 'active', 'R05'),
('ragu@police.lk', '$2y$10$zS9ispxgnnq6jIxrDC.QheP0wtH89.3xA69Wpck.HfwMwxIE9kZQW', 0, 0, 'active', 'R06'),
('ragu@ragu.com', '$2y$10$OmvclNHtv06YK8qMDVGmY.etdVoiLI75vuhwG984hAzd.vs5DsEi.', 0, 0, 'active', 'R06'),
('rrr@gmail.com', '$2y$10$LMUakQhvB3Q2dzgtOvJdjOExq0NxIHPvdxddCG6iLXSEux5hM21TK', 0, 0, 'active', 'R03'),
('sri@gmail.com', '$2y$10$YLuMNln5/7L2LGMQ.D9kcOKZWx5TJl1FeBHJDxHeSLd.Wmx2qND.O', 0, 1329, 'active', 'R06'),
('sriraguraj@gmail.co.uk', '$2y$10$Hm88LaV.RZbaXPT.q94HiuTwrt9NQ6njdGB3blBIBegB6i6TCoUtq', 0, 0, 'active', 'R06'),
('sriraguraj@yahoo.com', '$2y$10$8Zd8.DTnkuEEs2cQa6lczuf2AB9qqxtG2TF7z.Rh6ZAAnMT2tc7/u', 0, 0, 'active', 'R06');

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
  `status` varchar(50) DEFAULT NULL,
  `message` text,
  `receiver_id` varchar(10) DEFAULT NULL,
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

--
-- Dumping data for table `parties`
--

INSERT INTO `parties` (`party_id`, `first_name`, `last_name`, `mobile`, `nic_number`, `email`, `joined_date`, `address`, `date_of_birth`, `gender`, `is_active`, `added_by`, `staff_id`) VALUES
('P0001', 'party_fname', 'party_lname', 712345678, '198065456788', 'party1@gmail.com', '2025-05-01', 'Paampan, Poonakari', '1980-06-01', 'Female', 0, 'R03', 'S0001'),
('P0002', 'lol2', 'par2', 777958841, '199406103582', 'fake@gmail.com', '2025-05-27', 'Chv', '1994-03-01', 'Male', 1, 'R03', 'S0001');

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

--
-- Dumping data for table `police`
--

INSERT INTO `police` (`police_id`, `first_name`, `last_name`, `mobile`, `email`, `address`, `nic_number`, `date_of_birth`, `badge_number`, `joined_date`, `is_active`, `role_id`, `station`, `image_path`, `gender`, `added_by`, `staff_id`) VALUES
('P0001', 'IGP', 'Police', 777123456, 'igp@police.lk', 'Colombo', '199087654321', '1991-01-10', '1122', '2025-05-12', 0, 'R06', '', 'uploads/img_682220b7dfbef1.77589695.jpeg', 'Female', 'R06', 'L0005'),
('P0002', 'Ragu', 'snr.DIG', 777999999, 'ragu@police.lk', 'Jaffna', '188798765432', '1888-04-30', '1329', '2025-05-12', 1, 'R06', 'C.I.D', 'uploads/img_6822215f1a34c8.38131899.jpeg', 'Female', 'R06', 'L0005');

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
  `role_id` varchar(3) NOT NULL,
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `role_status` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
('R12', 'Outer staff', '0'),
('R13', 'a', '1'),
('R14', 'Commercial High Court Fiscal', '0'),
('R15', 'k', '1');

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

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `mobile`, `nic_number`, `date_of_birth`, `email`, `address`, `court_id`, `joined_date`, `is_active`, `role_id`, `image_path`, `gender`, `appointment`) VALUES
('S0001', 'Selvamathy', 'Kulasingam', 777777777, '197406103583', '2025-04-17', 'reg@reg.reg', 'PP', 'C01', '2025-04-18', 1, 'R03', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0002', 'Raguraj', 'Srirajeswaran', 777958841, '199406103585', '1994-03-01', 'sriraguraj@gmail.com', 'Meesalai', 'C03', '2025-04-19', 1, 'R03', 'uploads/img_680d251812b3a2.88472394.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0003', 'firstname', 'lastname', 777958840, '199406103580', '1956-05-02', 'email@gmail.com', 'address', 'C01', '2025-04-19', 0, 'R05', 'uploads/img_680d23cbd50d20.02873809.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0004', 'Ragu', 'Raj', 773563868, '199406103583', '1994-03-01', 'raguraj94@hotmail.co.uk', 'Chavakachcheri', 'C03', '2025-04-26', 1, 'R01', 'uploads/img_680cf677d2d3a3.98806943.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0005', 'oes', 'oes', 765555555, '199406103511', '2025-04-30', 'dfv@edfv.com', 'ugf786', 'C01', '2025-04-26', 0, 'R05', 'uploads/img_680d09e9ae7ee4.16129921.jpeg', 'Male', 'O.E.S/ Peon/ Security'),
('S0006', 'staff', 'role', 776543322, '198076543333', '1980-09-20', 'dummy2@rolechange.com', 'Nallur', 'C01', '2025-05-04', 0, 'R05', 'uploads/img_68176471af2782.25215949.jpeg', 'Male', 'Judicial Staff (JSC)'),
('S0007', 'php', 'oop', 777654321, '145678654321', '1456-10-11', 'phpoop@php.net', 'phpo', 'C01', '2025-05-10', 1, 'R05', 'uploads/img_681f9d0cdf4958.54817722.jpeg', 'Female', 'Ministry Staff'),
('S0008', 'rrr', 'rrr', 777000000, '000000000000', '1899-12-30', 'rrr@gmail.com', 'addr', 'C03', '2025-05-12', 1, 'R03', 'uploads/img_6822167e1799a0.63392347.jpeg', 'Male', 'Judicial Staff (JSC)');

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
