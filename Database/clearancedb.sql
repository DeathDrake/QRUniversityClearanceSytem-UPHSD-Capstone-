-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2024 at 03:07 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clearancedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `signatory_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `user_id`, `first_name`, `last_name`, `email`, `type`, `signatory_id`) VALUES
(15, 47, 'kyle', 'Hainz', 'avocado@gmail.com', 'Super Admin', NULL),
(17, 49, 'ell', 'yagami', 'cc@gmail.com', 'Super Admin', NULL),
(26, 67, 'kyle', 'Library', 'cp@gmail.com', 'Signatory Admin', 12),
(27, 68, 'amy', 'guidance', 'avocado@gmail.com', 'Signatory Admin', 13),
(28, 69, 'amy', 'studentaffairs', 'cc@gmail.com', 'Signatory Admin', 14),
(29, 70, 'ell', 'discipline', 'tylerbeckmail@gmail.com', 'Signatory Admin', 15),
(30, 71, 'kyle', 'alumniaffairs', 'cp@gmail.com', 'Signatory Admin', 16),
(31, 72, 'amy', 'accounting', 'cc@gmail.com', 'Signatory Admin', 17),
(32, 73, 'amy', 'graduationfee', 'avocado@gmail.com', 'Signatory Admin', 18),
(33, 74, 'kent', 'officestudentsaffair', 'avocado@gmail.com', 'Signatory Admin', 19),
(34, 75, 'kent', 'campusministry', 'avocado@gmail.com', 'Signatory Admin', 20),
(35, 76, 'amy', 'sports', 'tylerbeckmail@gmail.com', 'Signatory Admin', 21),
(36, 77, 'kent', 'dean', 'abs@gmail.com', 'Signatory Admin', 22),
(37, 78, 'az', 'registrar', 'cp@gmail.com', 'Signatory Admin', 23),
(38, 79, 'kent', 'schooldirector', 'cc@gmail.com', 'Signatory Admin', 24);

-- --------------------------------------------------------

--
-- Table structure for table `clearance`
--

CREATE TABLE `clearance` (
  `clearance_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `date_submitted` date DEFAULT NULL,
  `Cpid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clearance`
--

INSERT INTO `clearance` (`clearance_id`, `student_id`, `status`, `date_submitted`, `Cpid`) VALUES
(7, 29, 'Pending', NULL, 1),
(8, 30, 'Pending', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `clearance_details`
--

CREATE TABLE `clearance_details` (
  `clearance_detail_id` int(11) NOT NULL,
  `deptstatus` varchar(50) DEFAULT NULL,
  `lackingreq` varchar(255) DEFAULT NULL,
  `clearance_id` int(11) DEFAULT NULL,
  `signatory_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clearance_period`
--

CREATE TABLE `clearance_period` (
  `Cpid` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clearance_period`
--

INSERT INTO `clearance_period` (`Cpid`, `school_year`, `semester`, `startdate`, `enddate`) VALUES
(1, '2024-2025', '2nd Semester', '2024-11-01', '2024-11-30');

-- --------------------------------------------------------

--
-- Table structure for table `qrcode`
--

CREATE TABLE `qrcode` (
  `qr_code` int(11) NOT NULL,
  `qr_id` int(11) DEFAULT NULL,
  `qr_code_data` varchar(255) DEFAULT NULL,
  `clearance_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `signatory`
--

CREATE TABLE `signatory` (
  `signatory_id` int(11) NOT NULL,
  `signatory_department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signatory`
--

INSERT INTO `signatory` (`signatory_id`, `signatory_department`) VALUES
(12, 'LIBRARY'),
(13, 'GUIDANCE'),
(14, 'STUDENT AFFAIRS'),
(15, 'PREFECT OF DISCIPLINE'),
(16, 'ALUMNI AFFAIRS'),
(17, 'ACCOUNTING'),
(18, 'GRADUATION FEE'),
(19, 'OFFICE OF INT\'L STUDENT AFFAIRS'),
(20, 'CAMPUS MINISTRY'),
(21, 'SPORTS/ATHLETICS'),
(22, 'DEAN'),
(23, 'UNIVERSITY REGISTRAR'),
(24, 'SCHOOL DIRECTOR');

-- --------------------------------------------------------

--
-- Table structure for table `studentlackingrequirements`
--

CREATE TABLE `studentlackingrequirements` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `signatory_id` int(11) DEFAULT NULL,
  `lackingrequirement` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `StudNo` varchar(50) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `mname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `year_level` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `StudNo`, `fname`, `mname`, `lname`, `email`, `course`, `year_level`) VALUES
(29, 64, '210758434', 'ell', 'light', 'ayy', 'cp@gmail.com', 'BSIT', '3RD YEAR'),
(30, 65, '215432676', 'kent', 'edfas', 'ayy', 'cp@gmail.com', 'BSIT', '3RD YEAR'),
(32, 80, '210987565', 'amy', 'els', 'Hainz', 'cp@gmail.com', 'BSIT', '4th Year');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `role`) VALUES
(47, 'Admin12', '$2y$10$3USoMDhTYNdF9iCvx5E.VOLJG5/EyuzpyhwYBIGMfZCny.pMVd.v6', 'admin'),
(49, 'Admin1', '$2y$10$PkLFlC5uKqpw/ikVDWs5U.337T2ADHSw0vRLWDZ4wvkMY/wOD6Z2.', 'admin'),
(64, 'Student4', '$2y$10$YnUz9Upk06odLc6RKz0riO9L.PS7OkefezHpTVe8ToDifL4reUQw2', 'student'),
(65, 'Student5', '$2y$10$M4LAnztWwlJfxn0mdb5egOql8/HS.w6PTYrp.yw/1Ih8EWopMxQzi', 'student'),
(67, 'library1', '$2y$10$Au/2iJhCi5wEdOHXEmUka.yqtLIqCtRBHvwfShKh53.oQjIyJLW.S', 'admin'),
(68, 'guidance1', '$2y$10$9V7cJwy/bK47rbHQo2FFQO.lC01fIkY/1AiwY3pBLKfXYAUMLshTi', 'admin'),
(69, 'studentaffairs1\r\n', '$2y$10$wEumVxvxvYiK19Tmyv2X/eodzOkHZvqmbgLHOcNZbSZayQRdN2fV.', 'admin'),
(70, 'prefectofdiscipline1', '$2y$10$Td8gbKlXuY5c89jiaUD9Fu/UZSgvBWkpJH34nPu7jCzd79.3d6ju6', 'admin'),
(71, 'alumniaffairs1', '$2y$10$NCp8Q.InqZfsLu4clCUFg.KGX1g6snlbHc.j/3R2AqAd/6WZRWP9e', 'admin'),
(72, 'accounting1', '$2y$10$c/SrApPQV63DKVY6OC7ZO.LsKJF4AvkkQFRWfpfrTsSqUsQ22.QVq', 'admin'),
(73, 'graduationfee1', '$2y$10$lNehAjPI1VslLWXiCr.iMeunR7KT.gFGr6VuDrhGo32J1sGHqwrza', 'admin'),
(74, 'officestudentaffairs1', '$2y$10$mK6dw0nknDYQQ0QM8ogEmeerxfJXuCywgE597DaBsSGvaJN6o5./m', 'admin'),
(75, 'campusministry1', '$2y$10$00dAmXzNNtZUeOuiZxrfBeEeBvWsC4lTEgT96lZ2TX7rP4Q.i1o7K', 'admin'),
(76, 'sports1', '$2y$10$17Cd3CB/Nc/iTkldaXYmlu7NKmUJP3LpWbAsoQBldy9unofEpJPkS', 'admin'),
(77, 'dean1', '$2y$10$uRQHhMILsPyqOx9qmIsCeuQo/jFUS9AaQJK.gI/IOXPd3jjIrxOXG', 'admin'),
(78, 'universityregistrar1', '$2y$10$rISNNbIkTWjaqdX.whNJ/ewEzOVMDGyqlbgWP/pSAIV2CfBv2/m1W', 'admin'),
(79, 'schooldirector1', '$2y$10$n7Gei91yoKHeE/TKQYx8veeKmzI2YXMzbStLDV7W95RZCY84SUSrS', 'admin'),
(80, 'student1', '$2y$10$RS/ppsizEhbK2hjgMok/MOAmaawy8Mp7srkTqsAF7KW9hs15uLIei', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `signatory_id` (`signatory_id`);

--
-- Indexes for table `clearance`
--
ALTER TABLE `clearance`
  ADD PRIMARY KEY (`clearance_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_clearance_period` (`Cpid`);

--
-- Indexes for table `clearance_details`
--
ALTER TABLE `clearance_details`
  ADD PRIMARY KEY (`clearance_detail_id`),
  ADD KEY `clearance_id` (`clearance_id`),
  ADD KEY `signatory_id` (`signatory_id`);

--
-- Indexes for table `clearance_period`
--
ALTER TABLE `clearance_period`
  ADD PRIMARY KEY (`Cpid`);

--
-- Indexes for table `qrcode`
--
ALTER TABLE `qrcode`
  ADD PRIMARY KEY (`qr_code`),
  ADD KEY `clearance_id` (`clearance_id`);

--
-- Indexes for table `signatory`
--
ALTER TABLE `signatory`
  ADD PRIMARY KEY (`signatory_id`);

--
-- Indexes for table `studentlackingrequirements`
--
ALTER TABLE `studentlackingrequirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `signatory_id` (`signatory_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `clearance`
--
ALTER TABLE `clearance`
  MODIFY `clearance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `clearance_details`
--
ALTER TABLE `clearance_details`
  MODIFY `clearance_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clearance_period`
--
ALTER TABLE `clearance_period`
  MODIFY `Cpid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `qrcode`
--
ALTER TABLE `qrcode`
  MODIFY `qr_code` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signatory`
--
ALTER TABLE `signatory`
  MODIFY `signatory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `studentlackingrequirements`
--
ALTER TABLE `studentlackingrequirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`signatory_id`) REFERENCES `signatory` (`signatory_id`);

--
-- Constraints for table `clearance`
--
ALTER TABLE `clearance`
  ADD CONSTRAINT `clearance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `fk_clearance_period` FOREIGN KEY (`Cpid`) REFERENCES `clearance_period` (`Cpid`);

--
-- Constraints for table `clearance_details`
--
ALTER TABLE `clearance_details`
  ADD CONSTRAINT `clearance_details_ibfk_1` FOREIGN KEY (`clearance_id`) REFERENCES `clearance` (`clearance_id`),
  ADD CONSTRAINT `clearance_details_ibfk_2` FOREIGN KEY (`signatory_id`) REFERENCES `signatory` (`signatory_id`);

--
-- Constraints for table `qrcode`
--
ALTER TABLE `qrcode`
  ADD CONSTRAINT `qrcode_ibfk_1` FOREIGN KEY (`clearance_id`) REFERENCES `clearance` (`clearance_id`);

--
-- Constraints for table `studentlackingrequirements`
--
ALTER TABLE `studentlackingrequirements`
  ADD CONSTRAINT `studentlackingrequirements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `studentlackingrequirements_ibfk_2` FOREIGN KEY (`signatory_id`) REFERENCES `signatory` (`signatory_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
