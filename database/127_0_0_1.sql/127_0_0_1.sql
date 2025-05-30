-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 09:38 PM
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
-- Database: `staff_signup`
--
DROP DATABASE IF EXISTS `staff_signup`;
CREATE DATABASE IF NOT EXISTS `staff_signup` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `staff_signup`;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `tutor_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `advisor_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `hod_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `role` varchar(10) DEFAULT 'none',
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `email`, `password`, `department`, `designation`, `role`, `phone`, `gender`, `date_of_birth`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$bCF3jlEICavqF6V.YoOeG.s3/aPqZZr2ygP77EiewLPGKfgmsyElu', 'Administration', 'System Administrator', 'advisor', '1234567890', 'Male', '1990-01-01', '2025-04-22 12:49:55', '2025-05-15 18:49:13'),
(2, 'DHIVYASRI M', 'abc@gmail.com', 'Abc@123', NULL, 'professor', NULL, '8775798585', 'Female', '0000-00-00', '2025-04-22 14:20:17', '2025-05-15 16:45:27'),
(3, 'Snehan S', 'snehan0126@gmail.com', '$2y$10$pwaZAK.S./o4ATndOHVB1OHTUyutH85u52CWtt0S49rBjNzqD3owi', 'CSE', 'HOD', NULL, NULL, NULL, NULL, '2025-04-23 02:27:02', '2025-05-15 16:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `grade` varchar(2) DEFAULT NULL,
  `activity_count` int(11) DEFAULT 0,
  `department` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);
--
-- Database: `student_portal`
--
DROP DATABASE IF EXISTS `student_portal`;
CREATE DATABASE IF NOT EXISTS `student_portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `student_portal`;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `reg_number` varchar(20) DEFAULT NULL,
  `activity_type` varchar(50) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `college` varchar(100) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `event_name` varchar(100) DEFAULT NULL,
  `award` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pending',
  `file_path` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `student_id`, `reg_number`, `activity_type`, `date_from`, `date_to`, `college`, `event_type`, `event_name`, `award`, `created_at`, `status`, `file_path`) VALUES
(3, NULL, '710724104042', 'co-curricular', '2025-04-30', '2025-04-30', 'PSG', 'technical', 'Hackathon', 'yes', '2025-05-07 09:47:38', NULL, ''),
(8, NULL, '34567', 'ex-curricular', '2025-06-05', '2025-05-01', 'ghbvcxcgn', 'technical', 'rthnvtgnvc', 'yes', '2025-05-14 08:05:29', 'Pending', ''),
(9, 10, NULL, 'Technical', '2006-11-01', '2006-11-26', NULL, NULL, 'Demo', NULL, '2025-05-15 10:56:52', 'rejected', 'uploads/98765433/6825cf12a79c4.jpg'),
(10, 13, NULL, 'Technical', '2025-05-15', '2025-05-15', NULL, NULL, 'Demo', NULL, '2025-05-15 17:40:59', 'approved', 'uploads/40/6826288804420.jpg'),
(11, 13, NULL, 'Academic', '2025-05-15', '2025-05-15', NULL, NULL, 'Demo', NULL, '2025-05-15 17:44:22', 'advisor_approved', 'uploads/40/682628991ff5c.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `reg_number` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `academic_year` varchar(10) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `hostel_day` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `activity_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `reg_number`, `department`, `academic_year`, `section`, `dob`, `gender`, `mobile`, `hostel_day`, `address`, `email`, `password`, `activity_count`) VALUES
(9, 'User1', NULL, 'CSE', '2024', 'A', '2007-05-30', 'female', '123456789', 'hosteller', 'xyz', 'user1@gmail.com', '$2y$10$T31briwVeJ09wS7GNwE35Oty4aNdDAAr14mFtPMZy8gNMvJ6s2Tpq', 0),
(10, 'VARCHAR', '98765433', 'CSE', '2025', 'B', '2025-05-16', 'male', '14142233', 'hosteller', 'eg', 'admin@example.com', '$2y$10$2A/B6CUZdcfeIA/xZhYuc.P7QSSvzu9B/BrHcgmiVCBOcRuGC94V2', 0),
(11, 'VARCHAR', '98765433', 'CSE', '2025', 'B', '2025-05-16', 'male', '14142233', 'hosteller', 'eg', 'admin@example.com', '$2y$10$C41pHUYqW1F3tXQvDP/2kejl/dtgFcCYPNqCd17nR7Ky1rPYw6Ucy', 0),
(12, 'VARCHAR', '98765433', 'CSE', '2025', 'B', '2025-05-16', 'male', '14142233', 'hosteller', 'eg', 'admin@example.com', '$2y$10$HXBI6aVOsKSuchB3n8KPFOiUrZDej5iE1yxFByYRs4iZkly/ecWwG', 0),
(13, 'DHIVYASRI M', '40', 'cse', '2024', 'A', '2006-11-26', 'female', '9876543210', 'hosteller', '', 'dhivyasri0126@gmail.com', '$2y$10$I/7f7jR4TJeJRpzblwVN1eOUYkH/DyL0iDXmmUQOqQ4xrbygy1h6e', 1),
(14, 'DHIVYASRI M', '40', 'cse', '2024', 'A', '2006-11-26', 'female', '9876543210', 'hosteller', '', 'dhivyasri0126@gmail.com', '$2y$10$aQlImg/9KEXeVT2NSaRjjO9A3hqzxR0sVVHXVQOQh1nBzk12yyOHK', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
