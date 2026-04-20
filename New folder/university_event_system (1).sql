-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 09:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `university_event_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `profile_image`) VALUES
(1, 'Al Rafi Ahmed', 'admin@iubat.edu', 'admin123', 'uploads/profiles/admin_1776453141_66cb74bb.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL DEFAULT 'General',
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `last_registration_date` date NOT NULL DEFAULT '2099-12-31',
  `fee_type` enum('free','paid') NOT NULL DEFAULT 'free',
  `event_banner` varchar(255) DEFAULT NULL,
  `assigned_supervisor_id` int(11) DEFAULT NULL,
  `payment_option` varchar(100) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_by_admin` int(11) NOT NULL,
  `approved_by_supervisor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `event_type`, `description`, `date`, `last_registration_date`, `fee_type`, `event_banner`, `assigned_supervisor_id`, `payment_option`, `location`, `capacity`, `status`, `created_by_admin`, `approved_by_supervisor`) VALUES
(1, 'Pohela boisakh', 'Cultural Program', '', '2026-04-18', '2026-04-17', 'free', NULL, 5, NULL, 'IUBAT Campus', 1000000000, 'pending', 1, NULL),
(2, 'Pohela Boisakh', 'Cultural Program', 'dfdf', '2026-04-18', '2026-04-18', 'free', 'uploads/event_banners/banner_1776448243_87358e10.png', NULL, NULL, 'IUBAT campus', 100000000, 'pending', 1, NULL),
(3, 'CSE Cultural fest', 'Cultural Program', 'u', '2026-04-30', '2026-04-25', 'free', 'uploads/event_banners/banner_1776448904_8b9a21eb.jpg', 7, NULL, 'IUBAT campus', 400, 'pending', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `serial_no` int(11) NOT NULL,
  `participant_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `event_id`, `program_name`, `serial_no`, `participant_name`, `description`, `start_time`, `end_time`) VALUES
(1, 3, 'kuran', 1, 'Rafi', 'sds', '10:10:00', '10:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `name`, `email`, `password`, `department`, `is_active`, `profile_image`) VALUES
(2, '22203177', 'Epti Ara Era', '22203177@gmail.com', 'era123', 'CSE', 1, 'uploads/profiles/student_1776453171_59471d72.jpg'),
(3, '22203175', 'Gazi Faizul Islam', '22203175@gmail.com', 'gazi123', 'CSE', 1, 'uploads/profiles/student_1776454139_a7984497.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `supervisors`
--

CREATE TABLE `supervisors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supervisors`
--

INSERT INTO `supervisors` (`id`, `name`, `email`, `password`, `designation`, `department`, `status`, `is_active`, `profile_image`) VALUES
(5, 'Sakib Al Hasan', 'sakib@gmail.com', 'sakib123', 'Captain', 'Cricket', 'approved', 1, 'uploads/profiles/supervisor_1776452747_1ccb2ead.jpeg'),
(6, 'Default Supervisor', 'supervisor@iubat.edu', 'super123', 'Lecturer', 'CSE', 'approved', 1, NULL),
(7, 'S M Rifatur Rana', 'rana@iubat.edu', 'rana123', 'Lecturer', 'CSE', 'approved', 1, NULL),
(8, 'Shahinur Alam1', 'sahin@iubat.edu', 'sahin123', 'Coordinator', 'CSE', 'approved', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admins_email` (`email`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_created_by_admin` (`created_by_admin`),
  ADD KEY `idx_events_approved_by_supervisor` (`approved_by_supervisor`),
  ADD KEY `idx_events_assigned_supervisor_id` (`assigned_supervisor_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_programs_event_id` (`event_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_registrations_student_event` (`student_id`,`event_id`),
  ADD KEY `idx_registrations_student_id` (`student_id`),
  ADD KEY `idx_registrations_event_id` (`event_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_students_student_id` (`student_id`),
  ADD UNIQUE KEY `uq_students_email` (`email`);

--
-- Indexes for table `supervisors`
--
ALTER TABLE `supervisors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_supervisors_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supervisors`
--
ALTER TABLE `supervisors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_approved_by_supervisor` FOREIGN KEY (`approved_by_supervisor`) REFERENCES `supervisors` (`id`),
  ADD CONSTRAINT `fk_events_assigned_supervisor_id` FOREIGN KEY (`assigned_supervisor_id`) REFERENCES `supervisors` (`id`),
  ADD CONSTRAINT `fk_events_created_by_admin` FOREIGN KEY (`created_by_admin`) REFERENCES `admins` (`id`);

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `fk_programs_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `fk_registrations_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `fk_registrations_student_id` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
