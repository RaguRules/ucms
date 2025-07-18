-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 05, 2025 at 06:26 AM
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
('C00009999', 'M/01/25', 'P0002', 'P0001', 'L0001', 'L0005', '2025-06-05', 1, 'Civil', 'Calling', 0, '2025-06-30', 'Calling', 'L0004', 'C02'),
('C0001', '110111', 'CID', 'SUREN', 'ARJUNA', 'SARNIYA', '2025-05-13', 1, 'Criminal', 'Trial', 1, '2025-12-31', 'Trial', 'L0005', 'Magistrate\'s Court'),
('C0002', 'D/934/15', 'RAJAN', 'KEETHA', 'SARNIYA', 'BANUSHA', '2025-05-13', 1, 'Civil', 'Calling', 0, '2025-08-29', 'Calling', 'L0005', 'District Court'),
('C9997', 'L/420/23', 'AMALA', 'COOLMAN', 'BUGCYF', 'JGHGC', '2025-05-13', 1, 'Civil', 'Calling', 1, '2025-12-31', 'Pre Trial Conference', 'L0005', 'District Court'),
('C9998', 'MIS/254/25', 'PARHA', 'MATHI, KOMATHI, SAREN', 'ARCHANA', 'SIVASANTHI', '2025-05-13', 1, 'Civil', 'Judgement', 1, '2027-12-31', 'Judgement', 'L0005', 'C02');

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
  `current_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Laid By','Appeal','Completed/ Closed','Appeal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `next_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Completed/ Closed','Appeal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_taken` tinyint(1) DEFAULT '0',
  `activity_date` date DEFAULT NULL,
  `staff_id` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dailycaseactivities`
--

INSERT INTO `dailycaseactivities` (`activity_id`, `case_name`, `summary`, `next_date`, `current_status`, `next_status`, `is_taken`, `activity_date`, `staff_id`) VALUES
('A00000005', 'C00009999', 'lol', '2025-12-30', 'Calling', 'Calling', 1, '2025-06-30', 'L0004'),
('A0002', 'C0002', 'Appeal outcome: Appeal Dismissed', '2025-03-05', 'Appeal', 'Trial', 1, '2025-07-01', 'L0004'),
('A0003', 'C0002', 'Appeal outcome: Appeal Dismissed', NULL, 'Appeal', 'Completed/ Closed', 1, '2025-07-01', 'L0004'),
('A0004', 'C0001', 'Appeal outcome: Appeal Dismissed', '2025-05-14', 'Appeal', 'Calling', 1, '2025-07-01', 'L0004');

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

--
-- Dumping data for table `judgements`
--

INSERT INTO `judgements` (`jud_id`, `case_id`, `is_contested`, `given_on`, `staff_id`) VALUES
('J00000001', 'C0001', 1, '2025-07-01', 'L00');

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

--
-- Dumping data for table `lawyer`
--

INSERT INTO `lawyer` (`lawyer_id`, `first_name`, `last_name`, `mobile`, `email`, `address`, `nic_number`, `date_of_birth`, `enrolment_number`, `joined_date`, `is_active`, `role_id`, `station`, `image_path`, `gender`, `added_by`, `staff_id`) VALUES
('L00000008', 'name', 'iouyt', 776765532, 'gcfghj@gmail.com', 'ihougyftdr', '199890987656', '0000-00-00', 'Sup.001', '2025-06-28', 1, 'R06', 'Legal Aid Commission', 'uploads/img_68601504683593.85865001.png', 'Female', '', ''),
('L00000009', 'aaaa', 'aaaa', 777876655, 'aaaw@gmail.com', 'ihugfy', '198765432123', '0000-00-00', '', '2025-07-04', 1, 'R06', 'Attorney General Department', 'uploads/img_6867cd80ab4225.63421535.png', 'Male', '', ''),
('L0001', 'Ragu', 'AAL', 777958842, 'sri@gmail.com', 'Jaffna', '199406106666', '0000-00-00', '66666666666', '2025-04-27', 1, 'R06', 'Private', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male', '', ''),
('L0002', 'ooo', 'lll', 779999999, '9878t@gmail.com', 'kiug7f86ocl hk.;vypfotfy', '199409876543', '1994-04-07', 'jhvyuct', '2025-05-14', 1, 'R06', 'Legal Aid Commission', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male', 'R06', 'L0005'),
('L0003', 'new bee', 'law', 777958888, 'bee@charmy.com', 'galle', '199876545678', '1998-09-21', 'edfgh', '2025-05-12', 0, 'R06', 'Legal Aid Commission', 'uploads/img_68219abe476644.45439479.jpeg', 'Female', '', ''),
('L0004', 'ican', 'lab', 754497253, 'sriraguraj@yahoo.com', 'Meesalai', '940613581V', '1994-03-01', 'ihut', '2025-05-12', 0, 'R06', 'Attorney General Dept', 'uploads/img_6821b0534a4ab5.39256854.jpeg', 'Male', 'R06', 'L0005'),
('L0005', 'staff', 'lawyer', 754497452, 'ragu@ragu.com', 'mee', '940613582v', '1994-03-01', 'r7ctvyibuon', '2025-05-12', 1, 'R06', 'Legal Aid Commission', 'uploads/img_6821da3516f099.01811946.jpeg', 'Male', 'R03', 'L0005'),
('L0006', 'dam', 'dam', 777654432, 'dfgb@gmail.com', 'ugyf', '765456789876', '7654-03-07', 'rrr', '2025-05-12', 0, 'R06', 'Legal Aid Commission', 'uploads/img_6821f95435df41.34396599.jpeg', 'Female', '', ''),
('L0007', 'wrg', 'hougiy', 777876654, 'kbhv@gmail.com', 'vyuf75e6', '765445678987', '7655-03-31', 'iho8g7', '2025-05-14', 1, 'R06', 'Legal Aid Commission', 'uploads/img_68245c2114b8d3.10499994.jpeg', 'Male', 'R06', 'L0005');

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
('aaaw@gmail.com', '$2y$10$ZaIyeQJ.T2UQY3cnwkg4HOfePCSj8LbOoeUq5ryatCpHh/cMsgMO6', 0, 46240, 'active', '0'),
('bee@charmy.com', '$2y$10$F4ZVuX.x9mX4wVHoRoJsEuNWgy2qgNejcPyuu2ojNkxBoDeM/vFy2', 0, 0, 'deleted', 'R06'),
('dfgb@gmail.com', '$2y$10$ce1iwT5PclVgsqZcggI.aOUCYhKVURGOgam5J9iH/CiItJC15Ib.W', 0, 0, 'active', 'R06'),
('dummy@rolechange2.com', '$2y$10$9OZTe7MMUoxiD2t6aHw70eZZ3H65teyDm.z7ElB5kLNfXDR25/vHS', 0, 0, 'active', 'R05'),
('gcfghj@gmail.com', '$2y$10$GMtQG/z5c.ltWY4YDDNR..0oYV/CwMbf83KSUYIQQ5983DE52/4Fi', 0, 56909, 'active', '0'),
('hiugyft@gmail.com', '$2y$10$S8Mhgqxec/hSfzHyL3ShdOyilW90qyoa.bHmJ/iWpEjnVrszRVi9C', 0, 48320, 'pending', '0'),
('igp@police.lk', '$2y$10$Nq6RE3f/h2XpAlZEx6Mw8uKEGt3L63QRD4aFeX7HrtMaBw.ikJyhq', 0, 0, 'active', 'R06'),
('jvhgcfxt@gmail.com', '$2y$10$hWu9fM/job2Z74uf51WJbuYYvfVjDnrUsGefRKQbLcf8nR9klxzgG', 0, 0, 'active', 'R01'),
('kbhv@gmail.com', '$2y$10$cwNSLD1wZ7wlbdV0h6ouFuNl2nTxnpQj4pbVRXZ6bDrtZl/0g0LGe', 0, 0, 'active', 'R06'),
('phpoop@php.net', '$2y$10$EwLIr4qTBLfr.BuutbBASOZSo2KF.y5gYofjn4Dqp5nZI8mRr9/x2', 0, 0, 'active', 'R05'),
('ragu@police.lk', '$2y$10$zS9ispxgnnq6jIxrDC.QheP0wtH89.3xA69Wpck.HfwMwxIE9kZQW', 0, 0, 'active', 'R06'),
('ragu@ragu.com', '$2y$10$hTp3JGS8QTbFj26VrevO8OGr0u4H7a9Vh5.E9douecqCFCJn5pcoO', 1, 423587, 'active', 'R06'),
('rrr@gmail.com', '$2y$10$LMUakQhvB3Q2dzgtOvJdjOExq0NxIHPvdxddCG6iLXSEux5hM21TK', 0, 0, 'active', 'R03'),
('sri@gmail.com', '$2y$10$YLuMNln5/7L2LGMQ.D9kcOKZWx5TJl1FeBHJDxHeSLd.Wmx2qND.O', 0, 1329, 'active', 'R06'),
('sriraguraj@gmail.co.uk', '$2y$10$Hm88LaV.RZbaXPT.q94HiuTwrt9NQ6njdGB3blBIBegB6i6TCoUtq', 0, 0, 'active', 'R06'),
('sriraguraj@gmail.com', '$2y$10$KtQ/DI.IsOKU.7M.F/uqE.krhCAM5jP86obtcNtb0.DXk1Rqdf2Xa', 0, 0, 'active', 'R01'),
('sriraguraj@yahoo.com', '$2y$10$CTh2pbpjqrtNPoW5JeDF/eHuZx8Xv/e3aH5cAHEOh6nEGfcToxjZK', 0, 668476, 'active', 'R06'),
('uctyxfhg@gmail.com', '$2y$10$9PtYpAraxlGgMZWqsXgYneCePlW4e40bMH7VpC.Junsol2rl4PNSu', 0, 47232, 'deleted', '0'),
('yfudtrs@sdgf.com', '$2y$10$90ilczT6lT7VNJc.MWOGfuufRoVSnFDvVcmD3VtUuK9PbvrxbJj8m', 0, 86225, 'pending', '0');

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

--
-- Dumping data for table `motions`
--

INSERT INTO `motions` (`motion_id`, `type`, `case_id`, `filed_by`, `filed_date`, `status`, `remarks`) VALUES
('O00000003', 'Call Early', 'C0002', 'L0001', '2025-07-02', 'Approved', 'next date');

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

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`note_id`, `case_id`, `title`, `details`, `created_date`, `updated_date`, `is_deleted`, `role_id`) VALUES
('1', '0', '02.07.2025', 'defendant says they ejected the plaintiff forcefully', '2025-07-03', '2025-07-03', 0, '0'),
('2', 'C0001', 'lol', 'ihguify', '2025-07-03', '2025-07-03', 1, '0'),
('N00000001', 'C9997', '03.07', 'updated details', '2025-07-03', '2025-07-03', 1, '0');

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `record_id`, `type`, `court_id`, `status`, `message`, `receiver_id`, `created_at`) VALUES
('N00000001', 'C0002', 'next_date_updated', 'District Cou', 'read', 'Next hearing date has been updated for case \'D/934/15\'. Please check the new schedule.', 'sriraguraj@yahoo.com', '2025-07-05 08:47:52'),
('N00000002', 'C0002', 'next_date_updated', 'District Cou', 'unread', 'Next hearing date has been updated for case \'D/934/15\'. Please check the new schedule.', NULL, '2025-07-05 08:47:52'),
('N00000003', 'C00009999', 'next_date_updated', 'C02', 'unread', 'Next hearing date has been updated for case \'M/01/25\'. Please check the new schedule.', 'sri@gmail.com', '2025-07-05 09:15:46'),
('N00000004', 'C00009999', 'next_date_updated', 'C02', 'unread', 'Next hearing date has been updated for case \'M/01/25\'. Please check the new schedule.', 'ragu@ragu.com', '2025-07-05 09:15:46');

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `case_id`, `is_calculated`, `given_on`, `staff_id`) VALUES
('O00000001', 'C0002', 0, '2025-07-02', 'L00'),
('O00000002', 'C9997', 1, '2025-06-02', 'L00');

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
('P0002', 'Ragu', 'snr.DIG', 777999999, 'ragu@police.lk', 'Jaffna', '188798765432', '1888-04-30', '1329', '2025-05-12', 1, 'R06', 'Tharmapuram', 'uploads/img_6822215f1a34c8.38131899.jpeg', 'Male', '', '');

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

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`reg_id`, `first_name`, `last_name`, `mobile`, `email`, `password`, `address`, `nic_number`, `date_of_birth`, `badge_number`, `station`, `enrolment_number`, `role_id`, `joined_date`, `status`, `image_path`, `gender`) VALUES
('R00000001', 'jbviyfutd', 'ihougifyu', '0777876658', 'hiugyft@gmail.com', '$2y$10$S8Mhgqxec/hSfzHyL3ShdOyilW90qyoa.bHmJ/iWpEjnVrszRVi9C', 'hioguifyud', '199876545677', '1998-09-21', '98756', 'Kilinochchi HQ', '', 'R07', '2025-07-04', 'Pending', 'uploads/img_6867d6bc888373.46297651.png', 'Female');

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
('S00000009', 'iyfutd', 'buigyfutd', 776567783, '978654543V', '1997-12-30', 'jvhgcfxt@gmail.com', 'uigyf867d5', 'C01', '2025-06-29', 1, 'R01', 'uploads/', 'Female', 'Judicial Staff (JSC)'),
('S00000010', 'Ragu', 'Ragu', 777958841, '199406103582', '1994-03-01', 'sriraguraj@gmail.com', 'Old road, Aiya kadaiyadi, Meesalai west, Meesalai.', 'C03', '2025-06-29', 1, 'R01', 'uploads/img_68617b4301c8e0.24441485.png', 'Male', 'Judicial Staff (JSC)'),
('S0001', 'Selvamathy', 'Kulasingam', 777777777, '197406103583', '2025-04-17', 'reg@reg.reg', 'PP', 'C01', '2025-04-18', 1, 'R03', 'uploads/img_6817604aca3819.20225241.jpeg', 'Male', 'Judicial Staff (JSC)'),
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
