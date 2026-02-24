-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2026 at 06:51 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_eval`
--

-- --------------------------------------------------------

--
-- Table structure for table `atmpt_list`
--

CREATE TABLE `atmpt_list` (
  `id` int(100) NOT NULL,
  `exid` int(100) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `nq` int(100) NOT NULL,
  `cnq` int(100) NOT NULL,
  `ptg` int(100) NOT NULL,
  `status` int(10) NOT NULL,
  `subtime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exm_list`
--

CREATE TABLE `exm_list` (
  `exid` int(100) NOT NULL,
  `exname` varchar(100) NOT NULL,
  `nq` int(50) NOT NULL,
  `desp` varchar(100) NOT NULL,
  `subt` datetime NOT NULL,
  `extime` datetime NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subject` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `feedback` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `fname`, `date`, `feedback`) VALUES
(5, 'Teacher Rosey', '2021-12-12 13:01:00', 'Please kindly complete all the homework and submit tomorrow '),
(6, 'Teacher Rosey', '2021-12-13 06:23:18', 'Hello this is an annoucement');

-- --------------------------------------------------------

--
-- Table structure for table `qstn_list`
--

CREATE TABLE `qstn_list` (
  `exid` int(11) NOT NULL,
  `qid` int(11) NOT NULL,
  `qstn` varchar(200) NOT NULL,
  `qstn_o1` varchar(100) NOT NULL,
  `qstn_o2` varchar(100) NOT NULL,
  `qstn_o3` varchar(100) NOT NULL,
  `qstn_o4` varchar(100) NOT NULL,
  `qstn_ans` varchar(100) NOT NULL,
  `sno` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `pword` varchar(255) NOT NULL,
  `fname` char(100) NOT NULL,
  `dob` date NOT NULL,
  `gender` char(10) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `uname`, `pword`, `fname`, `dob`, `gender`, `email`) VALUES
(11, 'abraham', '1f9a884da469fdf263c098fc46891c04', 'Abraham Lincoln', '1998-02-12', 'M', 'ab@usa.com'),
(12, 'mariealx', 'f6fdffe48c908deb0f4c3bd36c032e72', 'Marie Alex', '1790-12-12', 'F', 'ma@aol.com'),
(15, 'swamy', '8d40a1580c500d15d3df48fce97c6c22', 'kiran', '2026-02-23', 'M', 'kiranswamy.cb@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `pword` varchar(255) NOT NULL,
  `fname` char(100) NOT NULL,
  `dob` date NOT NULL,
  `gender` char(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`id`, `uname`, `pword`, `fname`, `dob`, `gender`, `email`, `subject`) VALUES
(2, 'pranay.bochkar@finvedic.com', '482c811da5d5b4bc6d497ffa98491e38', 'Pranay', '1990-01-01', 'M', 'pranay.bochkar@finvedic.com', 'GENERAL');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `atmpt_list`
--
ALTER TABLE `atmpt_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exm_list`
--
ALTER TABLE `exm_list`
  ADD PRIMARY KEY (`exid`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qstn_list`
--
ALTER TABLE `qstn_list`
  ADD PRIMARY KEY (`qid`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `atmpt_list`
--
ALTER TABLE `atmpt_list`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `exm_list`
--
ALTER TABLE `exm_list`
  MODIFY `exid` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `qstn_list`
--
ALTER TABLE `qstn_list`
  MODIFY `qid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
