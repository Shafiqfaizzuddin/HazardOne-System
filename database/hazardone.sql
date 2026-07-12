-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 12, 2026 at 11:59 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hazardone`
--

-- --------------------------------------------------------

--
-- Table structure for table `hazards`
--

CREATE TABLE `hazards` (
  `id` int NOT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reported_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `category` enum('Road','Environmental','Building') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hazards`
--

INSERT INTO `hazards` (`id`, `user_name`, `reported_at`, `user_agent`, `location_name`, `latitude`, `longitude`, `category`, `description`) VALUES
(1, 'Wan', '2026-07-12 17:53:50', 'Google sdk_gphone16k_x86_64; Android 17; SDK 37', 'Gurun', '37.4219983', '-122.0840000', 'Road', 'test'),
(2, 'Syazrin', '2026-07-12 18:06:01', 'Google sdk_gphone16k_x86_64; Android 17; SDK 37', 'Kodiang', '37.4219983', '-122.0840000', 'Building', 'Collapsed'),
(3, 'Syazwi', '2026-07-12 18:12:02', 'Google sdk_gphone16k_x86_64; Android 17; SDK 37', 'Arau', '37.4219983', '-122.0840000', 'Environmental', 'Fog'),
(4, 'One', '2026-07-12 19:33:14', 'Google sdk_gphone16k_x86_64; Android 17; SDK 37', 'Sydney', '37.4219983', '-122.0840000', 'Road', 'Hole'),
(5, 'Linda', '2026-07-12 19:53:57', 'Google sdk_gphone16k_x86_64; Android 17; SDK 37', 'Mecca', '37.4219983', '-122.0840000', 'Environmental', 'Tree on the road');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hazards`
--
ALTER TABLE `hazards`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hazards`
--
ALTER TABLE `hazards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
