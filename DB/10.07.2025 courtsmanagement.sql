-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 10, 2025 at 04:25 PM
-- Server version: 8.0.40
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

CREATE TABLE `appeals` (
  `appeal_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `appellant_party_id` varchar(10) DEFAULT NULL,
  `respondent_party_id` varchar(10) DEFAULT NULL,
  `appeal_date` date DEFAULT NULL,
  `appeal_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
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
  `court_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`case_id`, `case_name`, `plaintiff`, `defendant`, `plaintiff_lawyer`, `defendant_lawyer`, `registered_date`, `is_active`, `nature`, `status`, `is_warrant`, `next_date`, `for_what`, `staff_id`, `court_id`) VALUES
('C00000001', 'M/01/25', 'P00000001', 'P00000002', 'L00000001', 'L00000002', '2025-07-01', 1, 'Civil', 'Calling', 0, '2025-07-04', 'Calling', 'S00000001', 'C02'),
('C00000002', '55015', 'P00000001', 'P00000003', 'L00000003', 'L00000001', '2025-07-02', 1, 'Criminal', 'Calling', 1, '2025-07-05', 'Calling', 'S00000001', 'C01'),
('C00000003', 'D/1000/24', 'P00000003', 'P00000002', 'L00000002', 'L00000001', '2025-07-03', 1, 'Civil', 'Calling', 0, '2025-07-06', 'Calling', 'S00000001', 'C02');

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

CREATE TABLE `courts` (
  `court_id` varchar(5) NOT NULL,
  `court_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `court_status` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`court_id`, `court_name`, `court_status`) VALUES
('C01', 'Magistrate\'s Court', 1),
('C02', 'District Court', 1),
('C03', 'High Court', 1),
('C04', 'Juvenile Magistrate\'s Court', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dailycaseactivities`
--

CREATE TABLE `dailycaseactivities` (
  `activity_id` varchar(10) NOT NULL,
  `case_name` varchar(20) DEFAULT NULL,
  `summary` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `next_date` date DEFAULT NULL,
  `current_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Laid By','Appeal','Motion','Dismissed','Completed - Judgement Delivered','Completed - Order Made') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `next_status` enum('Calling','Pre Trial Conference','Trial','Inquiry','Order','Judgement','Post Judgement Calling','Completed/ Closed','Appeal','Motion','Laid By') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_taken` tinyint(1) DEFAULT '0',
  `activity_date` date DEFAULT NULL,
  `staff_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dailycaseactivities`
--

INSERT INTO `dailycaseactivities` (`activity_id`, `case_name`, `summary`, `next_date`, `current_status`, `next_status`, `is_taken`, `activity_date`, `staff_id`) VALUES
('A00000001', 'C00000001', 'possible for settlement', '2025-07-07', 'Calling', 'Judgement', 1, '2025-07-04', 'S00000001');

-- --------------------------------------------------------

--
-- Table structure for table `judgements`
--

CREATE TABLE `judgements` (
  `jud_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `is_contested` tinyint(1) DEFAULT '0',
  `given_on` date DEFAULT NULL,
  `staff_id` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer`
--

CREATE TABLE `lawyer` (
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
  `staff_id` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lawyer`
--

INSERT INTO `lawyer` (`lawyer_id`, `first_name`, `last_name`, `mobile`, `email`, `address`, `nic_number`, `date_of_birth`, `enrolment_number`, `joined_date`, `is_active`, `role_id`, `station`, `image_path`, `gender`, `added_by`, `staff_id`) VALUES
('L00000001', 'Arjuna', 'Thillainathan', 773333333, 'arjuna@aal.com', 'jaffna', '199033333333', '1990-11-28', 'Sup/2020/E2', '2025-07-06', 1, 'R06', 'Private', 'uploads/img_686a67a1aac6f4.92778807.png', 'Male', '', ''),
('L00000002', 'Sarniya', 'Rasuthan', 774444444, 'sarniya@aal.com', 'Poonakary Town', '199044444444', '1991-03-19', 'Sup/2015/L03', '2025-07-06', 1, 'R06', 'Legal Aid Commission', 'uploads/img_686a6916e48aa1.54443456.png', 'Female', 'GUEST', 'GUEST'),
('L00000003', 'Janarthan', 'Good', 770000000, 'janarthan@aal.com', 'Jaffna', '199000000000', '1989-12-30', 'Sup/ff/323', '2025-07-06', 1, 'R06', 'Attorney General Department', 'uploads/img_686ac3e7243238.79326398.png', 'Male', 'R01', 'S00000001');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `username` varchar(50) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `attempt` int DEFAULT '0',
  `otp` int DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `role_id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`username`, `password`, `attempt`, `otp`, `status`, `role_id`, `created_at`) VALUES
('arjuna@aal.com', '$2y$10$GTUheCzOAcEKx0ra9BaL7OE.StB4zdiWZzByXxilJvBFFk0N5HB0m', 0, 0, 'active', 'R06', '2025-07-06 14:28:11'),
('elamaran@police.com', '$2y$10$fa.A/RDQMEdie9xHsDiFnOd3E2TuGZESgz6P0xGhENtT7d0UGSdp2', 0, 0, 'active', 'R07', '2025-07-06 14:28:11'),
('interpreter@mc.com', '$2y$10$Zz7/fC3Ogh/BklyT9xE98Oq70QuKxSHcB8bW9WjwWdv3d5NLotm5e', 0, 0, 'active', 'R04', '2025-07-06 14:28:11'),
('janarthan@aal.com', '$2y$10$qbPq9KH3K4EfJkC6VXck3.v3sacHqDLW3MS4I311ap4M1014VLb6e', 0, 0, 'active', 'R06', '2025-07-06 18:43:51'),
('registrar@dc.com', '$2y$10$qhvgvHDvm2AdYIs0fMvd5.XKVx.c2T0i/FjrFWSKFYh79UuMGIYJS', 0, 0, 'active', 'R03', '2025-07-06 14:28:11'),
('sarniya@aal.com', '$2y$10$WJb2w0tDIRkTrYRVnssQouiOothQ/r.O7qSqLiz6d7MZI4ku9pzgK', 0, 20459, 'active', '0', '2025-07-06 14:28:11'),
('sriraguraj@gmail.com', '$2y$10$0WkvZIlKYlrp3A5ppz6ATOGFhtr5dFhAhdKudHMhDFCPfozDO/pQ.', 0, 0, 'active', 'R01', '2025-07-06 14:28:11'),
('thana@police.com', '$2y$10$4lxg.xtTz7H01UqSJYD3jeAS7T48w9DBGU3cqq6nqOHGZTcRNUTY6', 0, NULL, 'active', 'R07', '2025-07-06 17:25:20');

-- --------------------------------------------------------

--
-- Table structure for table `motions`
--

CREATE TABLE `motions` (
  `motion_id` varchar(10) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `filed_by` varchar(20) DEFAULT NULL,
  `filed_date` date DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `remarks` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `note_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `details` text,
  `created_date` date DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `role_id` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` varchar(10) NOT NULL,
  `record_id` varchar(10) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `court_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `message` text,
  `receiver_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `record_id`, `type`, `court_id`, `status`, `message`, `receiver_id`, `created_at`) VALUES
('N00000001', 'R00000001', 'lawyer', NULL, 'unread', 'Lawyer registration pending approval: ID R00000001', 'admin', '2025-07-06 17:46:22'),
('N00000002', 'R00000002', 'Self-Registration Approval', 'NULL', 'read', 'Policeself-registration pending approval', 'sriraguraj@gmail.com', '2025-07-06 19:58:37'),
('N00000003', 'R00000002', 'Self-Registration Approval', 'NULL', 'unread', 'Policeself-registration pending approval', 'registrar@dc.com', '2025-07-06 19:58:37'),
('N00000004', 'R00000002', 'Self-Registration Approval', 'NULL', 'read', 'Police self-registration is pending', 'sriraguraj@gmail.com', '2025-07-06 22:55:20'),
('N00000005', 'C00000001', 'next_date_updated', 'C02', 'unread', '\'M/01/25\' has been fixed for Judgement on 2025-07-07.', 'arjuna@aal.com', '2025-07-07 00:37:10'),
('N00000006', 'C00000001', 'next_date_updated', 'C02', 'unread', '\'M/01/25\' has been fixed for Judgement on 2025-07-07.', 'sarniya@aal.com', '2025-07-07 00:37:10');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `is_calculated` tinyint(1) DEFAULT '0',
  `given_on` date DEFAULT NULL,
  `staff_id` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

CREATE TABLE `parties` (
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
  `staff_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `parties`
--

INSERT INTO `parties` (`party_id`, `first_name`, `last_name`, `mobile`, `nic_number`, `email`, `joined_date`, `address`, `date_of_birth`, `gender`, `is_active`, `added_by`, `staff_id`) VALUES
('P00000001', 'sundaram', 'sundaram', 777777777, '199077777777', 'sundaram@party.com', '2025-07-06', 'palai', '1990-10-03', 'Male', 1, 'R01', 'S00000001'),
('P00000002', 'valarmathi', 'valarmathy', 778888888, '199088888888', 'valarmathy@party.com', '2025-07-06', 'kilinochchi', '1991-01-22', 'Female', 1, 'R01', 'S00000001'),
('P00000003', 'Theepan', 'Theepan', 779999999, '199099999999', 'theepan@party.com', '2025-07-06', 'Jaffna', '1991-05-13', 'Male', 1, 'R01', 'S00000001');

-- --------------------------------------------------------

--
-- Table structure for table `police`
--

CREATE TABLE `police` (
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
  `staff_id` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `police`
--

INSERT INTO `police` (`police_id`, `first_name`, `last_name`, `mobile`, `email`, `address`, `nic_number`, `date_of_birth`, `badge_number`, `joined_date`, `is_active`, `role_id`, `station`, `image_path`, `gender`, `added_by`, `staff_id`) VALUES
('P00000001', 'Elamaran', 'PC', 775555555, 'elamaran@police.com', 'Kili Police HQ', '199055555555', '1990-02-23', '6666', '2025-07-06', 1, 'R07', 'Kilinochchi HQ', 'uploads/img_686a6b3a9a9e57.05709854.png', 'Female', 'R01', 'S00000001'),
('P00000002', 'Thanangeyan', 'PC', 776666666, 'thana@police.com', 'Mullaithevi', '199066666666', '1990-06-14', '99999', '2025-07-07', 1, 'R07', 'S.C.I.B', 'uploads/img_686ab180d9a253.39987169.png', 'Male', 'R01', 'S00000001');

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
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
  `gender` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `role_status` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
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
('R07', 'POLICE', '1');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
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
  `appointment` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `mobile`, `nic_number`, `date_of_birth`, `email`, `address`, `court_id`, `joined_date`, `is_active`, `role_id`, `image_path`, `gender`, `appointment`) VALUES
('S00000001', 'Admin', 'Staff', 777958841, '199406103582', '1994-03-01', 'sriraguraj@gmail.com', 'Old Road, Aiya Kadaiyadi, Meesalai West, Jaffna', 'C02', '2025-07-06', 1, 'R01', 'uploads/img_686a638d44db09.20935733.heic', 'Male', 'Judicial Staff (JSC)'),
('S00000002', 'Selvamathy', 'Kulasingam', 771111111, '199011111111', '1990-04-20', 'registrar@dc.com', 'dc, kilinochchi', 'C02', '2025-07-06', 1, 'R03', 'uploads/img_686a65988c75d0.54133523.png', 'Male', 'Judicial Staff (JSC)'),
('S00000003', 'Interpreter', 'MC', 772222222, '199022222222', '1990-08-09', 'interpreter@mc.com', 'MC, Kilinochchi', 'C01', '2025-07-06', 1, 'R04', 'uploads/img_686a667424fb96.01676773.png', 'Male', 'Judicial Staff (JSC)');

-- --------------------------------------------------------

--
-- Table structure for table `warrants`
--

CREATE TABLE `warrants` (
  `warrant_id` varchar(10) NOT NULL,
  `case_id` varchar(10) DEFAULT NULL,
  `issued_for_party_id` varchar(10) DEFAULT NULL,
  `issued_by_staff_id` varchar(10) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `warrant_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appeals`
--
ALTER TABLE `appeals`
  ADD PRIMARY KEY (`appeal_id`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`case_id`);

--
-- Indexes for table `courts`
--
ALTER TABLE `courts`
  ADD PRIMARY KEY (`court_id`);

--
-- Indexes for table `dailycaseactivities`
--
ALTER TABLE `dailycaseactivities`
  ADD PRIMARY KEY (`activity_id`);

--
-- Indexes for table `judgements`
--
ALTER TABLE `judgements`
  ADD PRIMARY KEY (`jud_id`);

--
-- Indexes for table `lawyer`
--
ALTER TABLE `lawyer`
  ADD PRIMARY KEY (`lawyer_id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `motions`
--
ALTER TABLE `motions`
  ADD PRIMARY KEY (`motion_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`note_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`party_id`);

--
-- Indexes for table `police`
--
ALTER TABLE `police`
  ADD PRIMARY KEY (`police_id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`reg_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `warrants`
--
ALTER TABLE `warrants`
  ADD PRIMARY KEY (`warrant_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
