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
-- Database: `staff_signup`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `date` date DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `student_id`, `title`, `status`, `date`, `event_type`, `created_at`) VALUES
(1, 1, 'Project Submission', 'approved', '2024-03-15', '', '2025-04-22 12:49:55'),
(2, 2, 'Internship Report', 'approved', '2024-03-14', '', '2025-04-22 12:49:55'),
(3, 3, 'Workshop Attendance', 'rejected', '2024-03-13', '', '2025-04-22 12:49:55'),
(4, 4, 'Research Paper', 'approved', '2024-03-12', '', '2025-04-22 12:49:55'),
(6, 6, 'Hackathon Participation', 'approved', '2024-03-10', '', '2025-04-22 12:49:55'),
(7, 7, 'Technical Workshop', 'rejected', '2024-03-09', '', '2025-04-22 12:49:55'),
(8, 8, 'Project Demo', 'approved', '2024-03-08', '', '2025-04-22 12:49:55'),
(9, 9, 'Internship Report', 'approved', '2024-03-07', '', '2025-04-22 12:49:55'),
(10, 10, 'Research Presentation', 'rejected', '2024-03-06', '', '2025-04-22 12:49:55');

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
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `email`, `password`, `department`, `designation`, `phone`, `gender`, `date_of_birth`, `created_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$bCF3jlEICavqF6V.YoOeG.s3/aPqZZr2ygP77EiewLPGKfgmsyElu', 'Administration', 'System Administrator', '1234567890', 'Male', '1990-01-01', '2025-04-22 12:49:55'),
(2, 'DHIVYASRI M', 'abc@gmail.com', 'Abc@123', NULL, 'professor', '8775798585', 'Female', '0000-00-00', '2025-04-22 14:20:17'),
(3, 'Snehan S', 'snehan0126@gmail.com', '$2y$10$pwaZAK.S./o4ATndOHVB1OHTUyutH85u52CWtt0S49rBjNzqD3owi', 'CSE', 'HOD', NULL, NULL, NULL, '2025-04-23 02:27:02');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `roll_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `activity_count` int(11) DEFAULT 0,
  `department` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `roll_number`, `name`, `grade`, `activity_count`, `department`, `year`, `created_at`) VALUES
(1, 'CS2020001', 'John Doe', 'A', 5, 'Computer Science', 2020, '2025-04-22 12:49:55'),
(2, 'CS2020002', 'Jane Smith', 'B+', 4, 'Computer Science', 2020, '2025-04-22 12:49:55'),
(3, 'EE2021001', 'Mike Johnson', 'A-', 4, 'BME', 2021, '2025-04-22 12:49:55'),
(6, 'EE2022001', 'Emily Davis', 'No', 4, 'Electrical Engineering', 2022, '2025-04-22 12:49:55'),
(7, 'ME2023001', 'Robert Wilson', 'B+', 3, 'Mechanical Engineering', 2023, '2025-04-22 12:49:55'),
(8, 'CS2023001', 'Lisa Anderson', 'A-', 5, 'Computer Science', 2023, '2025-04-22 12:49:55'),
(9, 'EE2024001', 'DHIVYASRI M', 'B', 4, 'Electrical Engineering', 2024, '2025-04-22 12:49:55'),
(10, 'ME2024001', 'DIVYA DARSHINI M', 'A', 4, 'Mechanical Engineering', 2024, '2025-04-22 12:49:55');

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roll_number` (`roll_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
