-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 07, 2023 at 11:44 PM
-- Server version: 5.7.23-23
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `neelneel_ems`
--

-- --------------------------------------------------------

--
-- Table structure for table `holidaylist`
--

CREATE TABLE `holidaylist` (
  `id` int(11) NOT NULL,
  `holiay_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `holidaylist`
--

INSERT INTO `holidaylist` (`id`, `holiay_name`, `date`) VALUES
(1, 'Holi', '2023-03-08');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2023-01-06-095347', 'App\\Database\\Migrations\\Projects', 'default', 'App', 1672999287, 1),
(2, '2023-01-09-085036', 'App\\Database\\Migrations\\Time', 'default', 'App', 1673254555, 2),
(3, '2023-01-09-090840', 'App\\Database\\Migrations\\Time', 'default', 'App', 1673255373, 3);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `project_status` enum('Completed','Running') DEFAULT 'Running',
  `project_stage` enum('0','20','40','60','80','100') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `created_at`, `updated_at`, `project_status`, `project_stage`) VALUES
(41, 'Tata Motor', '2023-01-30 06:45:34', '2023-02-22 00:35:19', 'Running', '20'),
(42, 'Google', '2023-01-30 06:45:45', '2023-02-22 06:38:02', 'Running', '80'),
(44, 'Ferndale', '2023-01-30 06:46:18', '2023-02-22 00:33:02', 'Completed', '100'),
(48, 'Push-teacher', '2023-02-01 22:57:16', '2023-02-21 07:26:02', 'Running', '60'),
(49, 'Big Binary', '2023-02-02 23:00:27', '2023-02-21 07:24:47', 'Running', '40'),
(58, 'Pepsi Co. Ltd', '2023-02-22 00:35:46', '2023-02-24 01:52:43', 'Completed', '100'),
(60, 'Alphabet Corp.', '2023-02-23 06:34:41', '2023-02-24 01:52:06', 'Running', '40'),
(61, 'Zapak.com', '2023-02-23 06:34:53', '2023-02-24 01:52:14', 'Running', '80'),
(63, 'Samsung India', '2023-02-23 06:35:27', '2023-03-02 01:12:25', 'Running', '20'),
(65, 'Miniclip', '2023-02-23 07:01:09', '2023-02-23 07:01:09', 'Running', '0'),
(66, 'Aero Space', '2023-02-23 07:24:20', '2023-02-23 07:24:20', 'Running', '0');

-- --------------------------------------------------------

--
-- Table structure for table `project_assign`
--

CREATE TABLE `project_assign` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assign_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `project_assign`
--

INSERT INTO `project_assign` (`id`, `project_id`, `user_id`, `assign_by`, `created_at`) VALUES
(1, 25, 9, 1, '2023-01-25 06:35:50'),
(2, 31, 9, 1, '2023-01-25 06:40:25'),
(3, 25, 10, 10, '2023-01-27 04:02:30'),
(4, 31, 14, 10, '2023-01-27 04:27:19'),
(5, 25, 1, 10, '2023-01-27 04:29:29'),
(6, 25, 14, 10, '2023-01-27 04:37:48'),
(7, 25, 11, 10, '2023-01-29 23:22:10'),
(8, 40, 14, 10, '2023-01-30 04:24:19'),
(9, 42, 11, 10, '2023-01-30 07:12:20'),
(10, 46, 9, 10, '2023-01-30 23:50:15'),
(11, 45, 13, 10, '2023-01-30 23:50:27'),
(12, 41, 9, 15, '2023-01-31 01:21:09'),
(13, 44, 16, 15, '2023-01-31 01:21:23'),
(14, 41, 1, 10, '2023-02-01 23:01:55'),
(15, 42, 13, 10, '2023-02-03 03:02:33'),
(16, 49, 15, 10, '2023-02-03 03:02:54'),
(17, 42, 9, 10, '2023-02-03 03:03:24'),
(18, 42, 1, 10, '2023-02-03 03:03:43'),
(19, 49, 9, 10, '2023-02-03 03:04:14'),
(20, 46, 16, 10, '2023-02-03 03:07:09'),
(21, 49, 1, 10, '2023-02-03 03:13:54'),
(22, 50, 1, 10, '2023-02-03 03:14:30'),
(23, 48, 17, 10, '2023-02-03 03:45:34'),
(24, 42, 12, 10, '2023-02-05 23:04:34'),
(25, 46, 12, 10, '2023-02-06 03:49:20'),
(26, 49, 12, 10, '2023-02-07 03:45:09'),
(27, 43, 12, 10, '2023-02-07 03:46:04'),
(28, 44, 12, 10, '2023-02-07 03:46:09'),
(29, 48, 15, 10, '2023-02-07 06:25:12'),
(30, 45, 15, 10, '2023-02-07 06:25:22'),
(31, 45, 1, 1, '2023-02-13 00:05:38'),
(32, 45, 9, 1, '2023-02-13 00:05:50'),
(33, 44, 10, 10, '2023-02-13 04:04:49'),
(34, 49, 21, 10, '2023-02-19 23:39:23'),
(35, 49, 10, 10, '2023-02-20 23:47:02'),
(36, 41, 35, 10, '2023-02-21 04:29:45'),
(37, 41, 12, 10, '2023-02-22 05:44:24'),
(38, 41, 45, 10, '2023-02-24 05:16:24'),
(39, 42, 44, 10, '2023-02-24 05:16:29'),
(40, 49, 43, 10, '2023-02-24 05:16:37'),
(41, 42, 43, 10, '2023-02-24 05:16:43'),
(42, 61, 40, 10, '2023-02-24 05:16:50'),
(43, 66, 40, 10, '2023-02-24 05:16:53'),
(44, 66, 11, 10, '2023-03-03 03:58:42');

-- --------------------------------------------------------

--
-- Table structure for table `time_entries`
--

CREATE TABLE `time_entries` (
  `id` int(11) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `time_entries`
--

INSERT INTO `time_entries` (`id`, `project_id`, `user_id`, `date`, `time`, `description`) VALUES
(1, '41', '12', '2023-02-03', '01:30:00', 'working on design'),
(2, '43', '12', '2023-02-03', '01:30:00', 'working on deployment'),
(3, '44', '12', '2023-02-03', '01:30:00', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nobis aut dolorem eius quasi id illo iste aliquam tempora sapiente deleniti possimus eum voluptatum exercitationem aliquid, accusantium assumenda sunt corporis magnam esse. Hic, dignissimos placeat '),
(4, '42', '12', '2023-02-06', '01:30:00', 'Working on the inhouse project - Project Management System'),
(5, '46', '12', '2023-02-07', '01:30:00', 'Working on functionality'),
(6, '49', '12', '2023-02-05', '01:30:00', 'working on design'),
(7, '44', '12', '2023-02-06', '02:30:00', 'Need to test all the bugs and console errors'),
(8, '44', '12', '2023-02-07', '01:18:00', 'working time'),
(9, '43', '12', '2023-02-07', '00:28:00', 'dsosdjodsdjosjoojjo'),
(10, '46', '12', '2023-02-08', '01:30:00', ''),
(11, '49', '12', '2023-02-07', '01:00:00', ''),
(12, '49', '12', '2023-02-07', '00:18:00', ''),
(13, '46', '12', '2023-02-07', '00:15:00', ''),
(14, '46', '12', '2023-02-07', '01:15:00', 'testinginnini'),
(15, '43', '12', '2023-02-07', '01:11:00', ''),
(16, '49', '15', '2023-02-07', '01:30:00', 'have made some changes on the functionality'),
(17, '49', '15', '2023-02-07', '02:30:00', 'all the endpoints are done, now working on the designing part'),
(18, '48', '15', '2023-02-07', '01:00:00', 'have starter creating the layout'),
(19, '45', '15', '2023-02-07', '01:26:00', 'how should i work on the design part ?'),
(20, '49', '15', '2023-02-07', '01:30:00', 'done'),
(21, '46', '12', '2023-02-09', '00:30:00', 'Giving finishing touch'),
(22, '46', '12', '2023-02-09', '02:00:00', 'website added on live server'),
(23, '49', '12', '2023-02-10', '00:15:00', ''),
(24, '44', '12', '2023-02-10', '02:27:00', 'checking all the edge cases'),
(25, '46', '12', '2023-02-13', '01:15:00', 'Have made some changes, updated the design and improved functionality'),
(26, '46', '12', '2023-02-15', '01:30:00', 'designinsdisdfngi'),
(27, '49', '12', '2023-02-21', '01:30:00', 'working on design part'),
(28, '42', '12', '2023-02-22', '02:30:00', 'Working on Designing part'),
(29, '41', '12', '2023-02-22', '01:30:00', 'molestiae ut, sit asperiores? Natus totam eum magni asperiores enim dignissimos repudiandae, sit beatae iure, similique, velit perferendis.\nNecessitatibus, officia suscipit. Amet quidem accusantium iste doloremque ipsa ea, quam at, nulla ullam quasi cumqu'),
(30, '41', '12', '2023-02-23', '02:17:00', 'molestiae ut, sit asperiores? Natus totam eum magni asperiores enim dignissimos repudiandae, sit beatae iure, similique, velit perferendis.\nNecessitatibus, officia suscipit. Amet quidem accusantium iste doloremque ipsa ea, quam at, nulla ullam quasi cumqu'),
(31, '41', '12', '2023-02-24', '03:30:00', 'molestiae ut, sit asperiores? Natus totam eum magni asperiores enim dignissimos repudiandae, sit beatae iure, similique, velit perferendis.\nNecessitatibus, officia suscipit. Amet quidem accusantium iste doloremque ipsa ea, quam at, nulla ullam quasi cumqu'),
(32, '41', '45', '2023-02-27', '01:11:00', 'implementing new features and debugging errors'),
(33, '41', '45', '2023-02-24', '02:15:00', 'Pending to improvise the design, currently working on the functionalities'),
(34, '41', '45', '2023-02-23', '01:23:00', 'dossdmofdomgmo'),
(35, '41', '45', '2023-02-24', '01:18:00', 'discussing about new features and functions'),
(36, '41', '45', '2023-02-24', '03:23:00', 'refactoring code and updating styling'),
(37, '49', '12', '2023-02-24', '02:30:00', 'Have made some changes on the frontend part.'),
(38, '42', '11', '2023-02-27', '02:21:00', 'Updated styling and designing, also checked for any flaws or bugs'),
(39, '42', '11', '2023-02-26', '01:26:00', 'testing to find out the bugs and errors'),
(40, '42', '11', '2023-02-28', '01:30:00', 'hello deven here testing PMS'),
(41, '42', '11', '2023-02-23', '01:30:00', 'think so there\'s a flaw, checking again\"'),
(42, '42', '11', '2023-02-18', '01:30:00', 'testing okokokokok'),
(43, '42', '11', '2023-02-28', '02:21:00', 'testingisndfisdffijwejiiwejg'),
(44, '42', '11', '2023-02-13', '01:30:00', 'why going on 13th'),
(45, '42', '11', '2023-02-28', '02:30:00', 'figuring out what is going wrong'),
(46, '42', '11', '2023-02-04', '01:30:00', 'lets test on 5th feb now'),
(47, '42', '11', '2023-02-25', '01:30:00', 'checking on 26th feb now'),
(48, '41', '12', '2023-02-16', '01:30:00', 'I hope so now its working fine, lets give it a try'),
(49, '49', '12', '2023-02-27', '01:30:00', 'Testing on 28th feb, by temp111'),
(50, '41', '12', '2023-02-28', '01:30:00', 'testing again, 28th feb'),
(51, '42', '12', '2023-02-11', '01:30:00', 'hello... okookook'),
(52, '41', '12', '2023-02-01', '02:17:00', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Temporibus quisquam vero sapiente eligendi reprehenderit quod numquam optio labore illum ipsam.'),
(53, '41', '12', '2023-02-28', '02:32:00', 'at last, finally its not working..'),
(54, '42', '12', '2023-02-03', '02:17:00', 'the app is working fine, give it a shot'),
(55, '49', '12', '2023-03-01', '02:06:00', 'How should i design the dashboard ? should i remove the charts ? '),
(56, '66', '11', '2023-03-05', '01:30:00', 'Working on the functionalities instructed by Sir.'),
(57, '66', '11', '2023-03-06', '02:21:00', 'Added couple of functionalities, and testing them out.'),
(58, '66', '11', '2023-03-01', '01:30:00', 'okoko'),
(59, '42', '11', '2023-03-05', '01:30:00', 'Now you can browse privately, and other people who use this device won’t see your activity. However, downloads, bookmarks and reading list items will be saved.\nNow you can browse privately, and other people who use this device won’t see your activity. How'),
(60, '66', '11', '2023-03-05', '01:30:00', 'Now you can browse privately, and other people who use this device won’t see your activity. However, downloads, bookmarks and reading list items will be saved.\nNow you can browse privately, and other people who use this device won’t see your activity. How'),
(61, '66', '11', '2023-03-06', '00:15:00', '15 mins worked'),
(62, '66', '11', '2023-03-06', '01:30:00', 'testing out the time range.'),
(63, '66', '11', '2023-03-06', '09:30:00', 'trying out with 9 hours 30 mins'),
(64, '66', '11', '2023-03-05', '03:30:00', 'Why i am getting warnings in console. need to resolve them '),
(65, '66', '11', '2023-03-05', '00:15:00', 'after submit the data will be cleared.'),
(66, '42', '11', '2023-03-05', '01:30:00', 'if there error then dont clear form');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_active` enum('0','1') DEFAULT '0',
  `authorized_to` set('edit','view','delete','create') DEFAULT 'view',
  `roles` enum('employee','admin') NOT NULL,
  `verification_link` varchar(255) DEFAULT NULL,
  `reset_link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `is_active`, `authorized_to`, `roles`, `verification_link`, `reset_link`) VALUES
(1, 'hemant', 'hemant@neelnetworks.com', '$2y$10$FJNfMtLHwC51sE/BUg06pObbyNXYx4YmQnpD6SxdOXOev8AxChXYa', '1', 'edit,view,delete,create', 'admin', '2Qdz5OcDZKsgWmxq', ''),
(10, 'deven', 'deven@neelnetworks.com', '$2y$10$sdxAgtXjXUeyZYwAxb6sEOz1B6eygk1AWt9h3wXNGZFxNHIqjrL/a', '1', 'edit,view,delete,create', 'admin', '', 'HadGnNU2PpCEXo7J'),
(11, 'temp123', 'nikohih348@minterp.com', '$2y$10$UU4DSr9E1KHQwzdXiQoPq.ixCfgSserqnkq2nHLjf5iby9Qaq3ttu', '1', 'view', 'employee', '', ''),
(12, 'temp111', 'pesiga@ema-sofia.eu', '$2y$10$pu6IrhTFfAG8iiBL2PeRmOPtYh6tW/AA7ZUqLVZMaIAEjgWeZgGSe', '1', 'edit,view,delete,create', 'employee', '', ''),
(17, 'fakeuser', 'pipsayaltu@gufum.com', '$2y$10$ZKjYAkaTGSDP6I7nO7.fBeM78PK67A.HvHWrXvQp9jkAZk6fFlxVi', '1', 'view', 'employee', '', ''),
(42, 'Emma', 'jstn1mt90d@dishcatfish.com', '$2y$10$cn2LEWWoeUsrssfMCAhnDu.lQ.6emtXr/hTd07FmyTkYWaV/1GRzy', '0', 'view', 'employee', '7HEoIlfeFc0NOPAC', ''),
(46, 'Tony Stark', 'nifaso3833@wireps.com', '$2y$10$XE5cJqxjQ.CCRD8QF.13Kut9oWDowfk6TwuH2AWx6sJ9BKO84vlmy', '0', 'view', 'employee', 'pE56cmut3rRXFCjd', ''),
(47, 'Peter', 'peter@neelnetworks.com', '$2y$10$gTTYx34GoWwEEUqwqpKlRuGEI/MCNz11I.8j0YNyTnoRM4L.j6GDa', '1', 'view', 'employee', '', ''),
(48, 'Alexis', 'alexis@trial.com', '$2y$10$6Gbokuwr5dgR2DGRnHPdvOZ3U5gZqyZTeLKoLe9mlB7PZ2fFK0i9m', '0', 'view', 'employee', 'UvLtaN0X8KnlmVix', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `holidaylist`
--
ALTER TABLE `holidaylist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_assign`
--
ALTER TABLE `project_assign`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `holidaylist`
--
ALTER TABLE `holidaylist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `project_assign`
--
ALTER TABLE `project_assign`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `time_entries`
--
ALTER TABLE `time_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
