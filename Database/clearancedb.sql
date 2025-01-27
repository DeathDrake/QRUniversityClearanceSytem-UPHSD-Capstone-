-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2024 at 06:12 PM
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
(29, 70, 'ell', 'discipline', 'tylerbeckmail@gmail.com', 'Signatory Admin', 15),
(30, 71, 'kyle', 'alumniaffairs', 'cp@gmail.com', 'Signatory Admin', 16),
(31, 72, 'amy', 'accounting', 'cc@gmail.com', 'Signatory Admin', 17),
(32, 73, 'amy', 'graduationfee', 'avocado@gmail.com', 'Signatory Admin', 18),
(33, 74, 'kent', 'officestudentsaffair', 'avocado@gmail.com', 'Signatory Admin', 19),
(34, 75, 'kent', 'campusministry', 'avocado@gmail.com', 'Signatory Admin', 20),
(35, 76, 'amy', 'sports', 'tylerbeckmail@gmail.com', 'Signatory Admin', 21),
(36, 77, 'kent', 'dean', 'abs@gmail.com', 'Signatory Admin', 22),
(37, 78, 'az', 'registrar', 'cp@gmail.com', 'Signatory Admin', 23),
(39, 82, 'school', 'Director1', 'tylerbeckmail@gmail.com', 'Signatory Admin', 25),
(40, 83, 'kyle', 'yagami', 'tylerbeckmail@gmail.com', 'Signatory Admin', 26);

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
(53, 29, 'Complete', NULL, 4),
(54, 30, 'Pending', NULL, 5),
(55, 29, 'Pending', NULL, 5),
(56, 30, 'Pending', NULL, 4),
(57, 32, 'Pending', NULL, 4),
(58, 41, 'Pending', NULL, 5);

-- --------------------------------------------------------

--
-- Table structure for table `clearance_details`
--

CREATE TABLE `clearance_details` (
  `clearance_detail_id` int(11) NOT NULL,
  `deptstatus` varchar(50) DEFAULT NULL,
  `lackingreq` varchar(255) DEFAULT NULL,
  `clearance_id` int(11) DEFAULT NULL,
  `signatory_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `Cpid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clearance_details`
--

INSERT INTO `clearance_details` (`clearance_detail_id`, `deptstatus`, `lackingreq`, `clearance_id`, `signatory_id`, `student_id`, `Cpid`) VALUES
(435, 'Cleared', NULL, 53, 12, 29, 4),
(436, 'Cleared', NULL, 53, 13, 29, 4),
(437, 'Cleared', NULL, 53, 15, 29, 4),
(438, 'Cleared', NULL, 53, 16, 29, 4),
(439, 'Cleared', NULL, 53, 17, 29, 4),
(440, 'Cleared', NULL, 53, 18, 29, 4),
(441, 'Cleared', NULL, 53, 19, 29, 4),
(442, 'Cleared', NULL, 53, 20, 29, 4),
(443, 'Cleared', NULL, 53, 21, 29, 4),
(444, 'Cleared', NULL, 53, 22, 29, 4),
(445, 'Cleared', NULL, 53, 23, 29, 4),
(446, 'Cleared', NULL, 53, 25, 29, 4),
(447, 'Cleared', NULL, 53, 26, 29, 4),
(448, 'Not Set', NULL, 54, 12, 30, 5),
(449, 'Not Set', NULL, 54, 13, 30, 5),
(450, 'Not Set', NULL, 54, 15, 30, 5),
(451, 'Not Set', NULL, 54, 16, 30, 5),
(452, 'Not Set', NULL, 54, 17, 30, 5),
(453, 'Not Set', NULL, 54, 18, 30, 5),
(454, 'Not Set', NULL, 54, 19, 30, 5),
(455, 'Not Set', NULL, 54, 20, 30, 5),
(456, 'Not Set', NULL, 54, 21, 30, 5),
(457, 'Not Set', NULL, 54, 22, 30, 5),
(458, 'Not Set', NULL, 54, 23, 30, 5),
(459, 'Not Set', NULL, 54, 25, 30, 5),
(460, 'Not Set', NULL, 54, 26, 30, 5),
(461, 'Not Set', NULL, 55, 12, 29, 5),
(462, 'Not Set', NULL, 55, 13, 29, 5),
(463, 'Not Set', NULL, 55, 15, 29, 5),
(464, 'Not Set', NULL, 55, 16, 29, 5),
(465, 'Not Set', NULL, 55, 17, 29, 5),
(466, 'Not Set', NULL, 55, 18, 29, 5),
(467, 'Not Set', NULL, 55, 19, 29, 5),
(468, 'Not Set', NULL, 55, 20, 29, 5),
(469, 'Not Set', NULL, 55, 21, 29, 5),
(470, 'Not Set', NULL, 55, 22, 29, 5),
(471, 'Not Set', NULL, 55, 23, 29, 5),
(472, 'Not Set', NULL, 55, 25, 29, 5),
(473, 'Not Set', NULL, 55, 26, 29, 5),
(474, 'Pending', '1234', 56, 12, 30, 4),
(475, 'Not Set', NULL, 56, 13, 30, 4),
(476, 'Not Set', NULL, 56, 15, 30, 4),
(477, 'Not Set', NULL, 56, 16, 30, 4),
(478, 'Not Set', NULL, 56, 17, 30, 4),
(479, 'Not Set', NULL, 56, 18, 30, 4),
(480, 'Not Set', NULL, 56, 19, 30, 4),
(481, 'Not Set', NULL, 56, 20, 30, 4),
(482, 'Not Set', NULL, 56, 21, 30, 4),
(483, 'Not Set', NULL, 56, 22, 30, 4),
(484, 'Not Set', NULL, 56, 23, 30, 4),
(485, 'Not Set', NULL, 56, 25, 30, 4),
(486, 'Not Set', NULL, 56, 26, 30, 4),
(487, 'Not Set', '', 57, 12, 32, 4),
(488, 'Not Set', NULL, 57, 13, 32, 4),
(489, 'Not Set', NULL, 57, 15, 32, 4),
(490, 'Not Set', NULL, 57, 16, 32, 4),
(491, 'Not Set', NULL, 57, 17, 32, 4),
(492, 'Not Set', NULL, 57, 18, 32, 4),
(493, 'Not Set', NULL, 57, 19, 32, 4),
(494, 'Not Set', NULL, 57, 20, 32, 4),
(495, 'Not Set', NULL, 57, 21, 32, 4),
(496, 'Not Set', NULL, 57, 22, 32, 4),
(497, 'Not Set', NULL, 57, 23, 32, 4),
(498, 'Not Set', NULL, 57, 25, 32, 4),
(499, 'Not Set', NULL, 57, 26, 32, 4),
(500, 'Cleared', NULL, 58, 12, 41, 5),
(501, 'Not Set', NULL, 58, 13, 41, 5),
(502, 'Not Set', NULL, 58, 15, 41, 5),
(503, 'Not Set', NULL, 58, 16, 41, 5),
(504, 'Not Set', NULL, 58, 17, 41, 5),
(505, 'Not Set', NULL, 58, 18, 41, 5),
(506, 'Not Set', NULL, 58, 19, 41, 5),
(507, 'Not Set', NULL, 58, 20, 41, 5),
(508, 'Not Set', NULL, 58, 21, 41, 5),
(509, 'Not Set', NULL, 58, 22, 41, 5),
(510, 'Not Set', NULL, 58, 23, 41, 5),
(511, 'Not Set', NULL, 58, 25, 41, 5),
(512, 'Not Set', NULL, 58, 26, 41, 5);

-- --------------------------------------------------------

--
-- Table structure for table `clearance_period`
--

CREATE TABLE `clearance_period` (
  `Cpid` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `clearancetype` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clearance_period`
--

INSERT INTO `clearance_period` (`Cpid`, `school_year`, `semester`, `startdate`, `enddate`, `clearancetype`) VALUES
(4, '2024-2025', '1st Semester', '2024-11-04', '2024-11-29', 'Non-Graduating'),
(5, '2024-2025', 'Summer', '2024-12-01', '2024-12-26', 'Non-Graduating');

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
(15, 'PREFECT OF DISCIPLINE'),
(16, 'ALUMNI AFFAIRS'),
(17, 'ACCOUNTING'),
(18, 'GRADUATION FEE'),
(19, 'OFFICE OF INT\'L STUDENT AFFAIRS'),
(20, 'CAMPUS MINISTRY'),
(21, 'SPORTS/ATHLETICS'),
(22, 'DEAN'),
(23, 'UNIVERSITY REGISTRAR'),
(25, 'SCHOOL DIRECTOR'),
(26, 'STUDENT AFFAIRS');

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
  `username` varchar(255) DEFAULT NULL,
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

INSERT INTO `students` (`student_id`, `user_id`, `username`, `StudNo`, `fname`, `mname`, `lname`, `email`, `course`, `year_level`) VALUES
(29, 64, 'Student4', '210758434', 'Angelo', 'L.', 'Labastida', 'cp@gmail.com', 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', '4th Year'),
(30, 65, 'Student5', '215432676', 'James', 'R.', 'Correa', 'cp@gmail.com', 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', '3rd Year'),
(32, 80, 'student1', '210987565', 'Christian', 'L.', 'Palma', 'cp@gmail.com', 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', '4th Year'),
(41, 91, 'Student7', '3431111', 'Aaron', 'L.', 'Salagubang', 'cp@gmail.com', 'BACHELOR OF ARTS', '1st Year'),
(44, 94, 'Stundent11', '355555', 'Renzo', 'K.', 'Baes', 'tylerbeckmail@gmail.com', 'BACHELOR OF ARTS IN PSYCHOLOGY', '3rd Year'),
(45, 95, 'StudentKyle', '2108735234', 'Kyle', 'P.', 'Moreno', 'Moreno@gmail.com', 'BACHELOR OF ELEMENTARY EDUCATION', '1st Year');

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
(70, 'prefectofdiscipline1', '$2y$10$Td8gbKlXuY5c89jiaUD9Fu/UZSgvBWkpJH34nPu7jCzd79.3d6ju6', 'admin'),
(71, 'alumniaffairs1', '$2y$10$NCp8Q.InqZfsLu4clCUFg.KGX1g6snlbHc.j/3R2AqAd/6WZRWP9e', 'admin'),
(72, 'accounting1', '$2y$10$c/SrApPQV63DKVY6OC7ZO.LsKJF4AvkkQFRWfpfrTsSqUsQ22.QVq', 'admin'),
(73, 'graduationfee1', '$2y$10$lNehAjPI1VslLWXiCr.iMeunR7KT.gFGr6VuDrhGo32J1sGHqwrza', 'admin'),
(74, 'officestudentaffairs1', '$2y$10$mK6dw0nknDYQQ0QM8ogEmeerxfJXuCywgE597DaBsSGvaJN6o5./m', 'admin'),
(75, 'campusministry1', '$2y$10$00dAmXzNNtZUeOuiZxrfBeEeBvWsC4lTEgT96lZ2TX7rP4Q.i1o7K', 'admin'),
(76, 'sports1', '$2y$10$17Cd3CB/Nc/iTkldaXYmlu7NKmUJP3LpWbAsoQBldy9unofEpJPkS', 'admin'),
(77, 'dean1', '$2y$10$uRQHhMILsPyqOx9qmIsCeuQo/jFUS9AaQJK.gI/IOXPd3jjIrxOXG', 'admin'),
(78, 'universityregistrar1', '$2y$10$rISNNbIkTWjaqdX.whNJ/ewEzOVMDGyqlbgWP/pSAIV2CfBv2/m1W', 'admin'),
(80, 'student1', '$2y$10$RS/ppsizEhbK2hjgMok/MOAmaawy8Mp7srkTqsAF7KW9hs15uLIei', 'student'),
(82, 'schooldirector1', '$2y$10$beo2UadtU32oqtlIygsjb.3/G34tkOC6uFUADfSwJsr8UCgyL3gl2', 'admin'),
(83, 'studentaffairs1', '$2y$10$2UiNoE/3sfG8RKhBmVmTJOepC5YTYaYJkCCy2pvdcKHCU.gA.dUpi', 'admin'),
(91, 'Student7', '$2y$10$a39HM/3XLZAdaOWIKVweW.7EQzol9VuX2ndElYS/3eSAGiY3vDoGq', 'student'),
(94, 'Stundent11', '$2y$10$JxmxtkTusU2c9Pa1dGqcqOX0xhUjVmtvR.G9UR/0WHN8aqmeBAPeu', 'student'),
(95, 'StudentKyle', '$2y$10$WxO2nmz5qphrLjSz/L6qq.HcOuKMjhMRA/5cpRJtxnn5IfPzYJMFC', 'student');

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
  ADD KEY `signatory_id` (`signatory_id`),
  ADD KEY `fk_student_id` (`student_id`),
  ADD KEY `fk_clearance_details_cpid` (`Cpid`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_students_username` (`username`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `clearance`
--
ALTER TABLE `clearance`
  MODIFY `clearance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `clearance_details`
--
ALTER TABLE `clearance_details`
  MODIFY `clearance_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=525;

--
-- AUTO_INCREMENT for table `clearance_period`
--
ALTER TABLE `clearance_period`
  MODIFY `Cpid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qrcode`
--
ALTER TABLE `qrcode`
  MODIFY `qr_code` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signatory`
--
ALTER TABLE `signatory`
  MODIFY `signatory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `studentlackingrequirements`
--
ALTER TABLE `studentlackingrequirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

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
  ADD CONSTRAINT `clearance_details_ibfk_2` FOREIGN KEY (`signatory_id`) REFERENCES `signatory` (`signatory_id`),
  ADD CONSTRAINT `fk_clearance_details_cpid` FOREIGN KEY (`Cpid`) REFERENCES `clearance_period` (`Cpid`),
  ADD CONSTRAINT `fk_student_id` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
