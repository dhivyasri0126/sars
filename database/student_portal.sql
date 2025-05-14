-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 10:04 PM
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
-- Database: `student_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `register_no` varchar(20) DEFAULT NULL,
  `activity_type` varchar(50) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `college` varchar(100) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `event_name` varchar(100) DEFAULT NULL,
  `award` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `first_name`, `last_name`, `register_no`, `activity_type`, `date_from`, `date_to`, `college`, `event_type`, `event_name`, `award`, `created_at`, `status`) VALUES
(3, 'User', '1', '710724104042', 'co-curricular', '2025-04-30', '2025-04-30', 'PSG', 'technical', 'Hackathon', 'yes', '2025-05-07 09:47:38', NULL),
(4, 'gvhm', '7678', '34567', 'ex-curricular', '2025-05-07', '0666-06-06', 'ghbvcxcgn', 'technical', 'rthnvtgnvc', 'yes', '2025-05-14 03:16:40', NULL),
(5, 'gvhm', '7678', '34567', 'ex-curricular', '2025-05-17', '2025-05-08', 'ghbvcxcgn', 'technical', 'rthnvtgnvc', 'yes', '2025-05-14 04:02:03', NULL),
(6, 'gvhm', '7678', '34567', 'ex-curricular', '2025-05-17', '2025-05-08', 'ghbvcxcgn', 'technical', 'rthnvtgnvc', 'yes', '2025-05-14 04:03:25', NULL),
(7, 'gvhm', '7678', '34567', 'ex-curricular', '2025-05-17', '2025-05-08', 'ghbvcxcgn', 'technical', 'rthnvtgnvc', 'yes', '2025-05-14 04:08:39', 'Pending'),
(8, 'gvhm', '7678', '34567', 'ex-curricular', '2025-06-05', '2025-05-01', 'ghbvcxcgn', 'technical', 'rthnvtgnvc', 'yes', '2025-05-14 08:05:29', 'Pending');

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
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `reg_number`, `department`, `academic_year`, `section`, `dob`, `gender`, `mobile`, `hostel_day`, `address`, `email`, `password`) VALUES
(9, 'User1', NULL, 'CSE', '2024', 'A', '2007-05-30', 'female', '123456789', 'hosteller', 'xyz', 'user1@gmail.com', '$2y$10$T31briwVeJ09wS7GNwE35Oty4aNdDAAr14mFtPMZy8gNMvJ6s2Tpq'),
(10, 'VARCHAR', '98765433', 'CSE', '2025', 'B', '2025-05-16', 'male', '14142233', 'hosteller', 'eg', 'admin@example.com', '$2y$10$2A/B6CUZdcfeIA/xZhYuc.P7QSSvzu9B/BrHcgmiVCBOcRuGC94V2'),
(11, 'VARCHAR', '98765433', 'cse', '876543', 'B', '2025-05-16', 'male', '14142233', 'hosteller', 'eg', 'admin@example.com', '$2y$10$C41pHUYqW1F3tXQvDP/2kejl/dtgFcCYPNqCd17nR7Ky1rPYw6Ucy');

-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `upload_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
