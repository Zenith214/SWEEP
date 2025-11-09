-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 12:46 PM
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
-- Database: `sweep`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notes`
--

CREATE TABLE `admin_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `collection_log_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `truck_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `route_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_date` date NOT NULL,
  `status` enum('active','cancelled') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `truck_id`, `user_id`, `route_id`, `assignment_date`, `status`, `notes`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 10, 2, 4, '2025-10-31', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(2, 3, 4, 7, '2025-10-31', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(3, 4, 3, 1, '2025-10-31', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(4, 2, 9, 3, '2025-10-31', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(5, 3, 2, 4, '2025-11-01', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(6, 5, 5, 2, '2025-11-01', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(7, 2, 9, 3, '2025-11-01', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(8, 4, 4, 7, '2025-11-01', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(9, 5, 5, 4, '2025-11-02', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(10, 10, 2, 7, '2025-11-02', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(11, 4, 9, 6, '2025-11-02', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(12, 3, 4, 3, '2025-11-02', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(13, 4, 7, 3, '2025-11-03', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(14, 10, 6, 2, '2025-11-03', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(15, 5, 9, 6, '2025-11-03', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(16, 1, 2, 7, '2025-11-03', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(17, 5, 5, 7, '2025-11-04', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(18, 6, 4, 1, '2025-11-04', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(19, 3, 7, 4, '2025-11-04', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(20, 1, 3, 2, '2025-11-04', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(21, 5, 9, 4, '2025-11-05', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(22, 1, 6, 7, '2025-11-05', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(23, 3, 2, 6, '2025-11-05', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(24, 2, 7, 3, '2025-11-05', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(25, 10, 3, 3, '2025-11-06', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(26, 5, 6, 2, '2025-11-06', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(27, 6, 4, 7, '2025-11-06', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(28, 2, 5, 8, '2025-11-06', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(29, 3, 7, 8, '2025-11-07', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(30, 2, 6, 2, '2025-11-07', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(31, 5, 8, 6, '2025-11-07', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(32, 4, 9, 4, '2025-11-07', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(33, 10, 5, 1, '2025-11-07', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(34, 5, 9, 1, '2025-11-08', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(35, 10, 6, 4, '2025-11-08', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(36, 2, 2, 3, '2025-11-08', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(37, 5, 2, 2, '2025-11-09', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(38, 4, 9, 7, '2025-11-09', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(39, 1, 8, 3, '2025-11-09', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(40, 3, 6, 6, '2025-11-09', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(41, 6, 9, 7, '2025-11-10', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(42, 3, 8, 6, '2025-11-10', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(43, 2, 7, 2, '2025-11-10', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(44, 1, 2, 1, '2025-11-11', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(45, 2, 9, 8, '2025-11-11', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(46, 10, 4, 7, '2025-11-11', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(47, 1, 9, 4, '2025-11-12', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(48, 5, 5, 3, '2025-11-12', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(49, 6, 6, 2, '2025-11-12', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(50, 1, 3, 6, '2025-11-13', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(51, 3, 5, 4, '2025-11-13', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(52, 4, 7, 2, '2025-11-13', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(53, 10, 2, 1, '2025-11-13', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(54, 5, 9, 7, '2025-11-13', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(55, 10, 8, 3, '2025-11-14', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(56, 3, 4, 8, '2025-11-14', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(57, 6, 9, 2, '2025-11-14', 'active', 'Regular scheduled collection', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(58, 1, 6, 1, '2025-11-14', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(59, 5, 5, 6, '2025-11-15', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(60, 10, 4, 1, '2025-11-15', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(61, 4, 6, 4, '2025-11-15', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(62, 6, 7, 4, '2025-11-16', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(63, 1, 6, 8, '2025-11-16', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(64, 2, 3, 6, '2025-11-16', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(65, 3, 7, 3, '2025-11-17', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(66, 10, 6, 6, '2025-11-17', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(67, 2, 8, 7, '2025-11-17', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(68, 5, 5, 2, '2025-11-17', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(69, 1, 9, 1, '2025-11-18', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(70, 2, 7, 3, '2025-11-18', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(71, 10, 8, 7, '2025-11-18', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(72, 6, 8, 6, '2025-11-19', 'active', 'Check for bulk items', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(73, 1, 6, 7, '2025-11-19', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(74, 10, 4, 8, '2025-11-19', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(75, 2, 3, 3, '2025-11-19', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(76, 3, 5, 2, '2025-11-19', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(77, 5, 7, 3, '2025-11-20', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(78, 10, 6, 2, '2025-11-20', 'active', 'Extra pickup requested', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(79, 1, 3, 4, '2025-11-20', 'active', 'Covering for another crew', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(80, 4, 8, 8, '2025-11-20', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(81, 3, 9, 4, '2025-11-21', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(82, 4, 3, 6, '2025-11-21', 'active', NULL, NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(83, 5, 7, 3, '2025-11-21', 'active', 'New route assignment', NULL, '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(84, 4, 8, 6, '2025-10-30', 'cancelled', 'Original assignment', 'Holiday adjustment', '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(85, 10, 6, 1, '2025-11-04', 'cancelled', 'Original assignment', 'Holiday adjustment', '2025-11-06 22:55:39', '2025-11-06 22:55:39'),
(86, 4, 7, 2, '2025-10-31', 'cancelled', 'Original assignment', 'Crew member called in sick', '2025-11-06 22:55:40', '2025-11-06 22:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('sweep-cache-analytics:collection_metrics:9ab6cd5966365f92511f8fb5d3aa1e2c:fallback', 'a:14:{s:17:\"total_collections\";i:0;s:9:\"completed\";i:0;s:10:\"incomplete\";i:0;s:15:\"issues_reported\";i:0;s:15:\"completion_rate\";i:0;s:15:\"scheduled_today\";i:4;s:15:\"completed_today\";i:0;s:12:\"issues_today\";i:0;s:21:\"completion_rate_today\";d:0;s:22:\"completion_rate_change\";d:0;s:16:\"completion_trend\";s:6:\"stable\";s:10:\"trend_data\";a:2:{s:6:\"labels\";a:31:{i:0;s:6:\"Oct 10\";i:1;s:6:\"Oct 11\";i:2;s:6:\"Oct 12\";i:3;s:6:\"Oct 13\";i:4;s:6:\"Oct 14\";i:5;s:6:\"Oct 15\";i:6;s:6:\"Oct 16\";i:7;s:6:\"Oct 17\";i:8;s:6:\"Oct 18\";i:9;s:6:\"Oct 19\";i:10;s:6:\"Oct 20\";i:11;s:6:\"Oct 21\";i:12;s:6:\"Oct 22\";i:13;s:6:\"Oct 23\";i:14;s:6:\"Oct 24\";i:15;s:6:\"Oct 25\";i:16;s:6:\"Oct 26\";i:17;s:6:\"Oct 27\";i:18;s:6:\"Oct 28\";i:19;s:6:\"Oct 29\";i:20;s:6:\"Oct 30\";i:21;s:6:\"Oct 31\";i:22;s:6:\"Nov 01\";i:23;s:6:\"Nov 02\";i:24;s:6:\"Nov 03\";i:25;s:6:\"Nov 04\";i:26;s:6:\"Nov 05\";i:27;s:6:\"Nov 06\";i:28;s:6:\"Nov 07\";i:29;s:6:\"Nov 08\";i:30;s:6:\"Nov 09\";}s:6:\"values\";a:31:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;i:12;i:0;i:13;i:0;i:14;i:0;i:15;i:0;i:16;i:0;i:17;i:0;i:18;i:0;i:19;i:0;i:20;i:0;i:21;d:0;i:22;d:0;i:23;d:0;i:24;d:0;i:25;d:0;i:26;d:0;i:27;d:0;i:28;d:0;i:29;d:0;i:30;d:0;}}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}', 1762772388),
('sweep-cache-analytics:collection_metrics:ea1edd9cd4498f1103bdb14372920fce:fallback', 'a:14:{s:17:\"total_collections\";i:0;s:9:\"completed\";i:0;s:10:\"incomplete\";i:0;s:15:\"issues_reported\";i:0;s:15:\"completion_rate\";i:0;s:15:\"scheduled_today\";i:4;s:15:\"completed_today\";i:0;s:12:\"issues_today\";i:0;s:21:\"completion_rate_today\";d:0;s:22:\"completion_rate_change\";d:0;s:16:\"completion_trend\";s:6:\"stable\";s:10:\"trend_data\";a:2:{s:6:\"labels\";a:31:{i:0;s:6:\"Oct 10\";i:1;s:6:\"Oct 11\";i:2;s:6:\"Oct 12\";i:3;s:6:\"Oct 13\";i:4;s:6:\"Oct 14\";i:5;s:6:\"Oct 15\";i:6;s:6:\"Oct 16\";i:7;s:6:\"Oct 17\";i:8;s:6:\"Oct 18\";i:9;s:6:\"Oct 19\";i:10;s:6:\"Oct 20\";i:11;s:6:\"Oct 21\";i:12;s:6:\"Oct 22\";i:13;s:6:\"Oct 23\";i:14;s:6:\"Oct 24\";i:15;s:6:\"Oct 25\";i:16;s:6:\"Oct 26\";i:17;s:6:\"Oct 27\";i:18;s:6:\"Oct 28\";i:19;s:6:\"Oct 29\";i:20;s:6:\"Oct 30\";i:21;s:6:\"Oct 31\";i:22;s:6:\"Nov 01\";i:23;s:6:\"Nov 02\";i:24;s:6:\"Nov 03\";i:25;s:6:\"Nov 04\";i:26;s:6:\"Nov 05\";i:27;s:6:\"Nov 06\";i:28;s:6:\"Nov 07\";i:29;s:6:\"Nov 08\";i:30;s:6:\"Nov 09\";}s:6:\"values\";a:31:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;i:12;i:0;i:13;i:0;i:14;i:0;i:15;i:0;i:16;i:0;i:17;i:0;i:18;i:0;i:19;i:0;i:20;i:0;i:21;d:0;i:22;d:0;i:23;d:0;i:24;d:0;i:25;d:0;i:26;d:0;i:27;d:0;i:28;d:0;i:29;d:0;i:30;d:0;}}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}', 1762771790),
('sweep-cache-dashboard:0a262856b7bc1cedaee0bbfc03086deb', 'a:5:{s:16:\"today_assignment\";a:5:{s:2:\"id\";i:37;s:10:\"route_name\";s:22:\"North District Route B\";s:10:\"route_zone\";s:4:\"ND-B\";s:12:\"truck_number\";s:5:\"T-005\";s:15:\"assignment_date\";s:10:\"2025-11-09\";}s:20:\"upcoming_assignments\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;a:4:{s:2:\"id\";i:44;s:10:\"route_name\";s:22:\"North District Route A\";s:12:\"truck_number\";s:5:\"T-001\";s:15:\"assignment_date\";s:10:\"2025-11-11\";}i:1;a:4:{s:2:\"id\";i:53;s:10:\"route_name\";s:22:\"North District Route A\";s:12:\"truck_number\";s:5:\"T-010\";s:15:\"assignment_date\";s:10:\"2025-11-13\";}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:11:\"performance\";a:3:{s:17:\"total_collections\";i:0;s:9:\"completed\";i:0;s:15:\"completion_rate\";i:0;}s:11:\"recent_logs\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:8:\"metadata\";a:3:{s:12:\"generated_at\";s:25:\"2025-11-09T11:29:37+00:00\";s:14:\"crew_member_id\";i:2;s:16:\"crew_member_name\";s:10:\"John Smith\";}}', 1762688077),
('sweep-cache-dashboard:25aba73b8e30de4d4a1cb0817e8bd024', 'a:12:{s:18:\"collection_metrics\";a:14:{s:17:\"total_collections\";i:0;s:9:\"completed\";i:0;s:10:\"incomplete\";i:0;s:15:\"issues_reported\";i:0;s:15:\"completion_rate\";i:0;s:15:\"scheduled_today\";i:4;s:15:\"completed_today\";i:0;s:12:\"issues_today\";i:0;s:21:\"completion_rate_today\";d:0;s:22:\"completion_rate_change\";d:0;s:16:\"completion_trend\";s:6:\"stable\";s:10:\"trend_data\";a:2:{s:6:\"labels\";a:31:{i:0;s:6:\"Oct 10\";i:1;s:6:\"Oct 11\";i:2;s:6:\"Oct 12\";i:3;s:6:\"Oct 13\";i:4;s:6:\"Oct 14\";i:5;s:6:\"Oct 15\";i:6;s:6:\"Oct 16\";i:7;s:6:\"Oct 17\";i:8;s:6:\"Oct 18\";i:9;s:6:\"Oct 19\";i:10;s:6:\"Oct 20\";i:11;s:6:\"Oct 21\";i:12;s:6:\"Oct 22\";i:13;s:6:\"Oct 23\";i:14;s:6:\"Oct 24\";i:15;s:6:\"Oct 25\";i:16;s:6:\"Oct 26\";i:17;s:6:\"Oct 27\";i:18;s:6:\"Oct 28\";i:19;s:6:\"Oct 29\";i:20;s:6:\"Oct 30\";i:21;s:6:\"Oct 31\";i:22;s:6:\"Nov 01\";i:23;s:6:\"Nov 02\";i:24;s:6:\"Nov 03\";i:25;s:6:\"Nov 04\";i:26;s:6:\"Nov 05\";i:27;s:6:\"Nov 06\";i:28;s:6:\"Nov 07\";i:29;s:6:\"Nov 08\";i:30;s:6:\"Nov 09\";}s:6:\"values\";a:31:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;i:12;i:0;i:13;i:0;i:14;i:0;i:15;i:0;i:16;i:0;i:17;i:0;i:18;i:0;i:19;i:0;i:20;i:0;i:21;d:0;i:22;d:0;i:23;d:0;i:24;d:0;i:25;d:0;i:26;d:0;i:27;d:0;i:28;d:0;i:29;d:0;i:30;d:0;}}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"recycling_metrics\";a:7:{s:12:\"total_weight\";d:12472.35;s:10:\"total_logs\";i:77;s:18:\"material_breakdown\";a:6:{i:0;a:3:{s:13:\"material_type\";s:7:\"organic\";s:6:\"weight\";d:3609.69;s:10:\"percentage\";d:28.94;}i:1;a:3:{s:13:\"material_type\";s:5:\"glass\";s:6:\"weight\";d:2481.22;s:10:\"percentage\";d:19.89;}i:2;a:3:{s:13:\"material_type\";s:9:\"cardboard\";s:6:\"weight\";d:2182.41;s:10:\"percentage\";d:17.5;}i:3;a:3:{s:13:\"material_type\";s:5:\"paper\";s:6:\"weight\";d:1854.86;s:10:\"percentage\";d:14.87;}i:4;a:3:{s:13:\"material_type\";s:5:\"metal\";s:6:\"weight\";d:1299.92;s:10:\"percentage\";d:10.42;}i:5;a:3:{s:13:\"material_type\";s:7:\"plastic\";s:6:\"weight\";d:1044.25;s:10:\"percentage\";d:8.37;}}s:14:\"recycling_rate\";i:0;s:24:\"logs_with_quality_issues\";i:11;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:13:\"fleet_metrics\";a:10:{s:12:\"total_trucks\";i:10;s:11:\"operational\";i:7;s:11:\"maintenance\";i:2;s:14:\"out_of_service\";i:1;s:19:\"average_utilization\";d:18.43;s:23:\"trucks_with_assignments\";i:7;s:20:\"underutilized_trucks\";a:7:{i:0;a:3:{s:8:\"truck_id\";i:1;s:12:\"truck_number\";s:5:\"T-001\";s:16:\"utilization_rate\";d:12.9;}i:1;a:3:{s:8:\"truck_id\";i:2;s:12:\"truck_number\";s:5:\"T-002\";s:16:\"utilization_rate\";d:19.35;}i:2;a:3:{s:8:\"truck_id\";i:3;s:12:\"truck_number\";s:5:\"T-003\";s:16:\"utilization_rate\";d:22.58;}i:3;a:3:{s:8:\"truck_id\";i:4;s:12:\"truck_number\";s:5:\"T-004\";s:16:\"utilization_rate\";d:19.35;}i:4;a:3:{s:8:\"truck_id\";i:5;s:12:\"truck_number\";s:5:\"T-005\";s:16:\"utilization_rate\";d:29.03;}i:5;a:3:{s:8:\"truck_id\";i:6;s:12:\"truck_number\";s:5:\"T-006\";s:16:\"utilization_rate\";d:6.45;}i:6;a:3:{s:8:\"truck_id\";i:10;s:12:\"truck_number\";s:5:\"T-010\";s:16:\"utilization_rate\";d:19.35;}}s:20:\"utilization_by_truck\";a:7:{i:0;a:3:{s:8:\"truck_id\";i:1;s:12:\"truck_number\";s:5:\"T-001\";s:16:\"utilization_rate\";d:12.9;}i:1;a:3:{s:8:\"truck_id\";i:2;s:12:\"truck_number\";s:5:\"T-002\";s:16:\"utilization_rate\";d:19.35;}i:2;a:3:{s:8:\"truck_id\";i:3;s:12:\"truck_number\";s:5:\"T-003\";s:16:\"utilization_rate\";d:22.58;}i:3;a:3:{s:8:\"truck_id\";i:4;s:12:\"truck_number\";s:5:\"T-004\";s:16:\"utilization_rate\";d:19.35;}i:4;a:3:{s:8:\"truck_id\";i:5;s:12:\"truck_number\";s:5:\"T-005\";s:16:\"utilization_rate\";d:29.03;}i:5;a:3:{s:8:\"truck_id\";i:6;s:12:\"truck_number\";s:5:\"T-006\";s:16:\"utilization_rate\";d:6.45;}i:6;a:3:{s:8:\"truck_id\";i:10;s:12:\"truck_number\";s:5:\"T-010\";s:16:\"utilization_rate\";d:19.35;}}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:16:\"crew_performance\";a:8:{s:17:\"active_crew_count\";i:8;s:17:\"total_collections\";i:0;s:24:\"avg_collections_per_crew\";d:0;s:14:\"top_performers\";a:0:{}s:21:\"crew_with_most_issues\";a:0:{}s:20:\"all_crew_performance\";a:0:{}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"report_statistics\";a:9:{s:13:\"total_reports\";i:9;s:9:\"by_status\";a:4:{s:7:\"pending\";i:3;s:11:\"in_progress\";i:3;s:8:\"resolved\";i:2;s:6:\"closed\";i:1;}s:7:\"by_type\";a:4:{s:13:\"missed_pickup\";a:2:{s:5:\"label\";s:13:\"Missed Pickup\";s:5:\"count\";i:4;}s:17:\"uncollected_waste\";a:2:{s:5:\"label\";s:17:\"Uncollected Waste\";s:5:\"count\";i:2;}s:15:\"illegal_dumping\";a:2:{s:5:\"label\";s:15:\"Illegal Dumping\";s:5:\"count\";i:2;}s:5:\"other\";a:2:{s:5:\"label\";s:5:\"Other\";s:5:\"count\";i:1;}}s:16:\"most_common_type\";a:2:{s:5:\"label\";s:13:\"Missed Pickup\";s:5:\"count\";i:4;}s:25:\"avg_resolution_time_hours\";d:34;s:27:\"locations_with_most_reports\";a:0:{}s:14:\"resolved_count\";i:3;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"route_performance\";a:6:{s:10:\"all_routes\";a:0:{}s:29:\"routes_with_lowest_completion\";a:0:{}s:23:\"routes_with_most_issues\";a:0:{}s:20:\"total_routes_tracked\";i:0;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:16:\"usage_statistics\";a:8:{s:20:\"active_users_by_role\";a:4:{s:14:\"administrators\";i:0;s:12:\"crew_members\";i:0;s:9:\"residents\";i:0;s:5:\"total\";i:0;}s:26:\"new_resident_registrations\";i:0;s:16:\"active_residents\";i:0;s:27:\"reports_per_active_resident\";i:0;s:22:\"inactive_users_30_days\";i:0;s:13:\"recent_logins\";i:0;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:23:\"geographic_distribution\";a:6:{s:19:\"collections_by_zone\";a:0:{}s:15:\"reports_by_zone\";a:1:{i:0;a:3:{s:4:\"zone\";s:7:\"Unknown\";s:13:\"total_reports\";i:9;s:15:\"pending_reports\";i:3;}}s:25:\"zones_without_collections\";a:10:{i:0;s:4:\"CD-A\";i:1;s:4:\"CD-B\";i:2;s:4:\"ED-A\";i:3;s:4:\"ED-B\";i:4;s:4:\"ND-A\";i:5;s:4:\"ND-B\";i:6;s:4:\"RR-A\";i:7;s:4:\"SD-A\";i:8;s:4:\"SE-1\";i:9;s:4:\"WD-A\";}s:11:\"total_zones\";i:10;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"operational_costs\";a:9:{s:9:\"available\";b:0;s:7:\"message\";s:88:\"Cost tracking is not yet implemented. Enable cost tracking to view operational expenses.\";s:11:\"total_costs\";i:0;s:14:\"cost_breakdown\";a:3:{s:4:\"fuel\";i:0;s:11:\"maintenance\";i:0;s:5:\"labor\";i:0;}s:19:\"cost_per_collection\";i:0;s:10:\"trend_data\";a:0:{}s:10:\"comparison\";a:0:{}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:13:\"pending_items\";a:4:{s:17:\"unassigned_routes\";i:0;s:15:\"pending_reports\";i:3;s:21:\"trucks_in_maintenance\";i:3;s:15:\"overdue_reports\";i:2;}s:6:\"alerts\";a:0:{}s:8:\"metadata\";a:4:{s:12:\"generated_at\";s:25:\"2025-11-09T10:59:48+00:00\";s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";s:15:\"filters_applied\";a:0:{}}}', 1762686288),
('sweep-cache-dashboard:25aba73b8e30de4d4a1cb0817e8bd024:fallback', 'a:12:{s:18:\"collection_metrics\";a:14:{s:17:\"total_collections\";i:0;s:9:\"completed\";i:0;s:10:\"incomplete\";i:0;s:15:\"issues_reported\";i:0;s:15:\"completion_rate\";i:0;s:15:\"scheduled_today\";i:4;s:15:\"completed_today\";i:0;s:12:\"issues_today\";i:0;s:21:\"completion_rate_today\";d:0;s:22:\"completion_rate_change\";d:0;s:16:\"completion_trend\";s:6:\"stable\";s:10:\"trend_data\";a:2:{s:6:\"labels\";a:31:{i:0;s:6:\"Oct 10\";i:1;s:6:\"Oct 11\";i:2;s:6:\"Oct 12\";i:3;s:6:\"Oct 13\";i:4;s:6:\"Oct 14\";i:5;s:6:\"Oct 15\";i:6;s:6:\"Oct 16\";i:7;s:6:\"Oct 17\";i:8;s:6:\"Oct 18\";i:9;s:6:\"Oct 19\";i:10;s:6:\"Oct 20\";i:11;s:6:\"Oct 21\";i:12;s:6:\"Oct 22\";i:13;s:6:\"Oct 23\";i:14;s:6:\"Oct 24\";i:15;s:6:\"Oct 25\";i:16;s:6:\"Oct 26\";i:17;s:6:\"Oct 27\";i:18;s:6:\"Oct 28\";i:19;s:6:\"Oct 29\";i:20;s:6:\"Oct 30\";i:21;s:6:\"Oct 31\";i:22;s:6:\"Nov 01\";i:23;s:6:\"Nov 02\";i:24;s:6:\"Nov 03\";i:25;s:6:\"Nov 04\";i:26;s:6:\"Nov 05\";i:27;s:6:\"Nov 06\";i:28;s:6:\"Nov 07\";i:29;s:6:\"Nov 08\";i:30;s:6:\"Nov 09\";}s:6:\"values\";a:31:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;i:12;i:0;i:13;i:0;i:14;i:0;i:15;i:0;i:16;i:0;i:17;i:0;i:18;i:0;i:19;i:0;i:20;i:0;i:21;d:0;i:22;d:0;i:23;d:0;i:24;d:0;i:25;d:0;i:26;d:0;i:27;d:0;i:28;d:0;i:29;d:0;i:30;d:0;}}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"recycling_metrics\";a:7:{s:12:\"total_weight\";d:12472.35;s:10:\"total_logs\";i:77;s:18:\"material_breakdown\";a:6:{i:0;a:3:{s:13:\"material_type\";s:7:\"organic\";s:6:\"weight\";d:3609.69;s:10:\"percentage\";d:28.94;}i:1;a:3:{s:13:\"material_type\";s:5:\"glass\";s:6:\"weight\";d:2481.22;s:10:\"percentage\";d:19.89;}i:2;a:3:{s:13:\"material_type\";s:9:\"cardboard\";s:6:\"weight\";d:2182.41;s:10:\"percentage\";d:17.5;}i:3;a:3:{s:13:\"material_type\";s:5:\"paper\";s:6:\"weight\";d:1854.86;s:10:\"percentage\";d:14.87;}i:4;a:3:{s:13:\"material_type\";s:5:\"metal\";s:6:\"weight\";d:1299.92;s:10:\"percentage\";d:10.42;}i:5;a:3:{s:13:\"material_type\";s:7:\"plastic\";s:6:\"weight\";d:1044.25;s:10:\"percentage\";d:8.37;}}s:14:\"recycling_rate\";i:0;s:24:\"logs_with_quality_issues\";i:11;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:13:\"fleet_metrics\";a:10:{s:12:\"total_trucks\";i:10;s:11:\"operational\";i:7;s:11:\"maintenance\";i:2;s:14:\"out_of_service\";i:1;s:19:\"average_utilization\";d:18.43;s:23:\"trucks_with_assignments\";i:7;s:20:\"underutilized_trucks\";a:7:{i:0;a:3:{s:8:\"truck_id\";i:1;s:12:\"truck_number\";s:5:\"T-001\";s:16:\"utilization_rate\";d:12.9;}i:1;a:3:{s:8:\"truck_id\";i:2;s:12:\"truck_number\";s:5:\"T-002\";s:16:\"utilization_rate\";d:19.35;}i:2;a:3:{s:8:\"truck_id\";i:3;s:12:\"truck_number\";s:5:\"T-003\";s:16:\"utilization_rate\";d:22.58;}i:3;a:3:{s:8:\"truck_id\";i:4;s:12:\"truck_number\";s:5:\"T-004\";s:16:\"utilization_rate\";d:19.35;}i:4;a:3:{s:8:\"truck_id\";i:5;s:12:\"truck_number\";s:5:\"T-005\";s:16:\"utilization_rate\";d:29.03;}i:5;a:3:{s:8:\"truck_id\";i:6;s:12:\"truck_number\";s:5:\"T-006\";s:16:\"utilization_rate\";d:6.45;}i:6;a:3:{s:8:\"truck_id\";i:10;s:12:\"truck_number\";s:5:\"T-010\";s:16:\"utilization_rate\";d:19.35;}}s:20:\"utilization_by_truck\";a:7:{i:0;a:3:{s:8:\"truck_id\";i:1;s:12:\"truck_number\";s:5:\"T-001\";s:16:\"utilization_rate\";d:12.9;}i:1;a:3:{s:8:\"truck_id\";i:2;s:12:\"truck_number\";s:5:\"T-002\";s:16:\"utilization_rate\";d:19.35;}i:2;a:3:{s:8:\"truck_id\";i:3;s:12:\"truck_number\";s:5:\"T-003\";s:16:\"utilization_rate\";d:22.58;}i:3;a:3:{s:8:\"truck_id\";i:4;s:12:\"truck_number\";s:5:\"T-004\";s:16:\"utilization_rate\";d:19.35;}i:4;a:3:{s:8:\"truck_id\";i:5;s:12:\"truck_number\";s:5:\"T-005\";s:16:\"utilization_rate\";d:29.03;}i:5;a:3:{s:8:\"truck_id\";i:6;s:12:\"truck_number\";s:5:\"T-006\";s:16:\"utilization_rate\";d:6.45;}i:6;a:3:{s:8:\"truck_id\";i:10;s:12:\"truck_number\";s:5:\"T-010\";s:16:\"utilization_rate\";d:19.35;}}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:16:\"crew_performance\";a:8:{s:17:\"active_crew_count\";i:8;s:17:\"total_collections\";i:0;s:24:\"avg_collections_per_crew\";d:0;s:14:\"top_performers\";a:0:{}s:21:\"crew_with_most_issues\";a:0:{}s:20:\"all_crew_performance\";a:0:{}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"report_statistics\";a:9:{s:13:\"total_reports\";i:9;s:9:\"by_status\";a:4:{s:7:\"pending\";i:3;s:11:\"in_progress\";i:3;s:8:\"resolved\";i:2;s:6:\"closed\";i:1;}s:7:\"by_type\";a:4:{s:13:\"missed_pickup\";a:2:{s:5:\"label\";s:13:\"Missed Pickup\";s:5:\"count\";i:4;}s:17:\"uncollected_waste\";a:2:{s:5:\"label\";s:17:\"Uncollected Waste\";s:5:\"count\";i:2;}s:15:\"illegal_dumping\";a:2:{s:5:\"label\";s:15:\"Illegal Dumping\";s:5:\"count\";i:2;}s:5:\"other\";a:2:{s:5:\"label\";s:5:\"Other\";s:5:\"count\";i:1;}}s:16:\"most_common_type\";a:2:{s:5:\"label\";s:13:\"Missed Pickup\";s:5:\"count\";i:4;}s:25:\"avg_resolution_time_hours\";d:34;s:27:\"locations_with_most_reports\";a:0:{}s:14:\"resolved_count\";i:3;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"route_performance\";a:6:{s:10:\"all_routes\";a:0:{}s:29:\"routes_with_lowest_completion\";a:0:{}s:23:\"routes_with_most_issues\";a:0:{}s:20:\"total_routes_tracked\";i:0;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:16:\"usage_statistics\";a:8:{s:20:\"active_users_by_role\";a:4:{s:14:\"administrators\";i:0;s:12:\"crew_members\";i:0;s:9:\"residents\";i:0;s:5:\"total\";i:0;}s:26:\"new_resident_registrations\";i:0;s:16:\"active_residents\";i:0;s:27:\"reports_per_active_resident\";i:0;s:22:\"inactive_users_30_days\";i:0;s:13:\"recent_logins\";i:0;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:23:\"geographic_distribution\";a:6:{s:19:\"collections_by_zone\";a:0:{}s:15:\"reports_by_zone\";a:1:{i:0;a:3:{s:4:\"zone\";s:7:\"Unknown\";s:13:\"total_reports\";i:9;s:15:\"pending_reports\";i:3;}}s:25:\"zones_without_collections\";a:10:{i:0;s:4:\"CD-A\";i:1;s:4:\"CD-B\";i:2;s:4:\"ED-A\";i:3;s:4:\"ED-B\";i:4;s:4:\"ND-A\";i:5;s:4:\"ND-B\";i:6;s:4:\"RR-A\";i:7;s:4:\"SD-A\";i:8;s:4:\"SE-1\";i:9;s:4:\"WD-A\";}s:11:\"total_zones\";i:10;s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:17:\"operational_costs\";a:9:{s:9:\"available\";b:0;s:7:\"message\";s:88:\"Cost tracking is not yet implemented. Enable cost tracking to view operational expenses.\";s:11:\"total_costs\";i:0;s:14:\"cost_breakdown\";a:3:{s:4:\"fuel\";i:0;s:11:\"maintenance\";i:0;s:5:\"labor\";i:0;}s:19:\"cost_per_collection\";i:0;s:10:\"trend_data\";a:0:{}s:10:\"comparison\";a:0:{}s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";}s:13:\"pending_items\";a:4:{s:17:\"unassigned_routes\";i:0;s:15:\"pending_reports\";i:3;s:21:\"trucks_in_maintenance\";i:3;s:15:\"overdue_reports\";i:2;}s:6:\"alerts\";a:0:{}s:8:\"metadata\";a:4:{s:12:\"generated_at\";s:25:\"2025-11-09T10:59:48+00:00\";s:12:\"period_start\";s:10:\"2025-10-10\";s:10:\"period_end\";s:10:\"2025-11-09\";s:15:\"filters_applied\";a:0:{}}}', 1762772657),
('sweep-cache-dashboard:8157676fb2d567221b4eeca757fca53b', 'a:6:{s:4:\"zone\";N;s:15:\"next_collection\";N;s:14:\"recent_reports\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:19:\"collection_schedule\";a:0:{}s:17:\"report_statistics\";a:3:{s:13:\"total_reports\";i:0;s:15:\"pending_reports\";i:0;s:16:\"resolved_reports\";i:0;}s:8:\"metadata\";a:3:{s:12:\"generated_at\";s:25:\"2025-11-09T11:33:54+00:00\";s:11:\"resident_id\";i:10;s:13:\"resident_name\";s:13:\"john resident\";}}', 1762688334),
('sweep-cache-recycling_analytics:material_totals:2025-10-10:2025-11-09', 'a:6:{i:0;a:4:{s:13:\"material_type\";s:7:\"organic\";s:12:\"total_weight\";d:3609.69;s:9:\"log_count\";i:45;s:10:\"percentage\";d:28.94;}i:1;a:4:{s:13:\"material_type\";s:5:\"glass\";s:12:\"total_weight\";d:2481.22;s:9:\"log_count\";i:42;s:10:\"percentage\";d:19.89;}i:2;a:4:{s:13:\"material_type\";s:9:\"cardboard\";s:12:\"total_weight\";d:2182.41;s:9:\"log_count\";i:45;s:10:\"percentage\";d:17.5;}i:3;a:4:{s:13:\"material_type\";s:5:\"paper\";s:12:\"total_weight\";d:1854.86;s:9:\"log_count\";i:43;s:10:\"percentage\";d:14.87;}i:4;a:4:{s:13:\"material_type\";s:5:\"metal\";s:12:\"total_weight\";d:1299.92;s:9:\"log_count\";i:41;s:10:\"percentage\";d:10.42;}i:5;a:4:{s:13:\"material_type\";s:7:\"plastic\";s:12:\"total_weight\";d:1044.25;s:9:\"log_count\";i:40;s:10:\"percentage\";d:8.37;}}', 1762688618),
('sweep-cache-recycling_analytics:recycling_rate:2025-09-09:2025-10-09', 'a:5:{s:12:\"total_weight\";d:750.31;s:9:\"log_count\";i:5;s:10:\"zone_count\";i:4;s:14:\"weight_per_log\";d:150.06;s:15:\"weight_per_zone\";d:187.58;}', 1762688619),
('sweep-cache-recycling_analytics:recycling_rate:2025-10-10:2025-11-09', 'a:5:{s:12:\"total_weight\";d:12472.35;s:9:\"log_count\";i:77;s:10:\"zone_count\";i:7;s:14:\"weight_per_log\";d:161.98;s:15:\"weight_per_zone\";d:1781.76;}', 1762688618),
('sweep-cache-recycling_analytics:target_progress:2025-11-01:2025-11-30', 'a:7:{i:0;a:7:{s:9:\"target_id\";i:1;s:13:\"material_type\";s:7:\"plastic\";s:13:\"target_weight\";d:744;s:13:\"actual_weight\";d:1753.19;s:19:\"progress_percentage\";d:235.64;s:11:\"is_achieved\";b:1;s:5:\"month\";O:25:\"Illuminate\\Support\\Carbon\":3:{s:4:\"date\";s:26:\"2025-11-01 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}}i:1;a:7:{s:9:\"target_id\";i:2;s:13:\"material_type\";s:5:\"paper\";s:13:\"target_weight\";d:1455;s:13:\"actual_weight\";d:2983.72;s:19:\"progress_percentage\";d:205.07;s:11:\"is_achieved\";b:1;s:5:\"month\";O:25:\"Illuminate\\Support\\Carbon\":3:{s:4:\"date\";s:26:\"2025-11-01 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}}i:2;a:7:{s:9:\"target_id\";i:3;s:13:\"material_type\";s:5:\"glass\";s:13:\"target_weight\";d:1260;s:13:\"actual_weight\";d:3994.23;s:19:\"progress_percentage\";d:317;s:11:\"is_achieved\";b:1;s:5:\"month\";O:25:\"Illuminate\\Support\\Carbon\":3:{s:4:\"date\";s:26:\"2025-11-01 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}}i:3;a:7:{s:9:\"target_id\";i:4;s:13:\"material_type\";s:5:\"metal\";s:13:\"target_weight\";d:648;s:13:\"actual_weight\";d:2201.63;s:19:\"progress_percentage\";d:339.76;s:11:\"is_achieved\";b:1;s:5:\"month\";O:25:\"Illuminate\\Support\\Carbon\":3:{s:4:\"date\";s:26:\"2025-11-01 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}}i:4;a:7:{s:9:\"target_id\";i:5;s:13:\"material_type\";s:9:\"cardboard\";s:13:\"target_weight\";d:1836;s:13:\"actual_weight\";d:3907.88;s:19:\"progress_percentage\";d:212.85;s:11:\"is_achieved\";b:1;s:5:\"month\";O:25:\"Illuminate\\Support\\Carbon\":3:{s:4:\"date\";s:26:\"2025-11-01 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}}i:5;a:7:{s:9:\"target_id\";i:6;s:13:\"material_type\";s:7:\"organic\";s:13:\"target_weight\";d:2040;s:13:\"actual_weight\";d:6131.41;s:19:\"progress_percentage\";d:300.56;s:11:\"is_achieved\";b:1;s:5:\"month\";O:25:\"Illuminate\\Support\\Carbon\":3:{s:4:\"date\";s:26:\"2025-11-01 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}}i:6;a:7:{s:9:\"target_id\";i:7;s:13:\"material_type\";s:3:\"all\";s:13:\"target_weight\";d:7983;s:13:\"actual_weight\";d:20972.06;s:19:\"progress_percentage\";d:262.71;s:11:\"is_achieved\";b:1;s:5:\"month\";O:25:\"Illuminate\\Support\\Carbon\":3:{s:4:\"date\";s:26:\"2025-11-01 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}}}', 1762688619),
('sweep-cache-recycling_analytics:trend_data:2025-08-17:2025-11-09:weekly', 'a:9:{i:0;a:6:{s:6:\"period\";s:7:\"2025-34\";s:12:\"period_start\";s:10:\"2025-08-18\";s:12:\"period_label\";s:20:\"Week of Aug 18, 2025\";s:12:\"total_weight\";d:146.61;s:9:\"log_count\";i:2;s:17:\"percentage_change\";N;}i:1;a:6:{s:6:\"period\";s:7:\"2025-36\";s:12:\"period_start\";s:10:\"2025-09-02\";s:12:\"period_label\";s:20:\"Week of Sep 01, 2025\";s:12:\"total_weight\";d:350.79;s:9:\"log_count\";i:4;s:17:\"percentage_change\";d:139.27;}i:2;a:6:{s:6:\"period\";s:7:\"2025-37\";s:12:\"period_start\";s:10:\"2025-09-09\";s:12:\"period_label\";s:20:\"Week of Sep 08, 2025\";s:12:\"total_weight\";d:415.2;s:9:\"log_count\";i:3;s:17:\"percentage_change\";d:18.36;}i:3;a:6:{s:6:\"period\";s:7:\"2025-38\";s:12:\"period_start\";s:10:\"2025-09-21\";s:12:\"period_label\";s:20:\"Week of Sep 15, 2025\";s:12:\"total_weight\";d:301.64;s:9:\"log_count\";i:1;s:17:\"percentage_change\";d:-27.35;}i:4;a:6:{s:6:\"period\";s:7:\"2025-40\";s:12:\"period_start\";s:10:\"2025-10-01\";s:12:\"period_label\";s:20:\"Week of Sep 29, 2025\";s:12:\"total_weight\";d:166.32;s:9:\"log_count\";i:2;s:17:\"percentage_change\";d:-44.86;}i:5;a:6:{s:6:\"period\";s:7:\"2025-42\";s:12:\"period_start\";s:10:\"2025-10-13\";s:12:\"period_label\";s:20:\"Week of Oct 13, 2025\";s:12:\"total_weight\";d:1194.88;s:9:\"log_count\";i:5;s:17:\"percentage_change\";d:618.42;}i:6;a:6:{s:6:\"period\";s:7:\"2025-43\";s:12:\"period_start\";s:10:\"2025-10-21\";s:12:\"period_label\";s:20:\"Week of Oct 20, 2025\";s:12:\"total_weight\";d:894.73;s:9:\"log_count\";i:5;s:17:\"percentage_change\";d:-25.12;}i:7;a:6:{s:6:\"period\";s:7:\"2025-44\";s:12:\"period_start\";s:10:\"2025-10-28\";s:12:\"period_label\";s:20:\"Week of Oct 27, 2025\";s:12:\"total_weight\";d:3151.91;s:9:\"log_count\";i:23;s:17:\"percentage_change\";d:252.27;}i:8;a:6:{s:6:\"period\";s:7:\"2025-45\";s:12:\"period_start\";s:10:\"2025-11-03\";s:12:\"period_label\";s:20:\"Week of Nov 03, 2025\";s:12:\"total_weight\";d:7230.83;s:9:\"log_count\";i:44;s:17:\"percentage_change\";d:129.41;}}', 1762688619),
('sweep-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:26:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"users.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"users.read\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"users.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"users.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:18:\"users.manage_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:13:\"routes.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:11:\"routes.read\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:13:\"routes.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"routes.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:16:\"schedules.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:14:\"schedules.read\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:16:\"schedules.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:16:\"schedules.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:18:\"collections.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:16:\"collections.read\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:18:\"collections.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"reports.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:12:\"reports.read\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:14:\"reports.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:14:\"reports.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"recycling.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:14:\"recycling.read\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:16:\"recycling.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:15:\"dashboard.admin\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:14:\"dashboard.crew\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:18:\"dashboard.resident\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"administrator\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"collection_crew\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:8:\"resident\";s:1:\"c\";s:3:\"web\";}}}', 1762750235);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `route_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `collection_date` date NOT NULL,
  `status` enum('scheduled','in_progress','completed','incomplete','cancelled') NOT NULL DEFAULT 'scheduled',
  `completion_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection_logs`
--

CREATE TABLE `collection_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `collection_id` bigint(20) UNSIGNED DEFAULT NULL,
  `completion_time` datetime DEFAULT NULL,
  `status` enum('pending','completed','incomplete','issue_reported') NOT NULL DEFAULT 'pending',
  `issue_type` varchar(100) DEFAULT NULL,
  `issue_description` text DEFAULT NULL,
  `completion_percentage` tinyint(4) DEFAULT NULL,
  `crew_notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection_photos`
--

CREATE TABLE `collection_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `collection_log_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_preferences`
--

CREATE TABLE `dashboard_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `widget_visibility` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`widget_visibility`)),
  `widget_order` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`widget_order`)),
  `default_filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`default_filters`)),
  `default_view` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dismissed_alerts`
--

CREATE TABLE `dismissed_alerts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `alert_category` varchar(255) NOT NULL,
  `alert_identifier` varchar(255) NOT NULL,
  `dismissed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `generated_reports`
--

CREATE TABLE `generated_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `scheduled_report_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `is_collection_skipped` tinyint(1) NOT NULL DEFAULT 1,
  `reschedule_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `name`, `date`, `is_collection_skipped`, `reschedule_date`, `created_at`, `updated_at`) VALUES
(1, 'New Year\'s Day', '2025-01-01', 1, NULL, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(2, 'Martin Luther King Jr. Day', '2025-01-15', 0, '2025-01-16', '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(3, 'Memorial Day', '2025-05-27', 0, '2025-05-28', '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(4, 'Independence Day', '2025-07-04', 1, NULL, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(5, 'Labor Day', '2025-09-02', 0, '2025-09-03', '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(6, 'Thanksgiving Day', '2025-11-28', 1, NULL, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(7, 'Day After Thanksgiving', '2025-11-29', 0, '2025-11-30', '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(8, 'Christmas Day', '2025-12-25', 1, NULL, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(9, 'New Year\'s Eve', '2025-12-31', 0, '2025-12-30', '2025-11-06 22:55:37', '2025-11-06 22:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_06_022822_create_permission_tables', 1),
(5, '2025_11_06_022904_create_role_change_logs_table', 1),
(6, '2025_11_06_023308_add_soft_deletes_to_users_table', 1),
(7, '2025_11_06_100000_create_routes_table', 1),
(8, '2025_11_06_100001_create_schedules_table', 1),
(9, '2025_11_06_100002_create_schedule_days_table', 1),
(10, '2025_11_06_100003_create_holidays_table', 1),
(11, '2025_11_07_025633_create_trucks_table', 1),
(12, '2025_11_07_030140_create_assignments_table', 1),
(13, '2025_11_07_030215_create_truck_status_history_table', 1),
(14, '2025_11_07_050548_add_cancellation_reason_to_assignments_table', 1),
(15, '2025_11_07_140404_create_collection_logs_table', 2),
(16, '2025_11_07_140536_create_collection_photos_table', 2),
(17, '2025_11_07_140614_create_admin_notes_table', 2),
(18, '2025_11_08_040549_create_reports_table', 3),
(19, '2025_11_08_040651_create_report_photos_table', 3),
(20, '2025_11_08_040715_create_report_responses_table', 3),
(21, '2025_11_08_040812_create_report_status_history_table', 3),
(22, '2025_11_08_064408_create_recycling_logs_table', 4),
(23, '2025_11_08_064932_create_recycling_log_materials_table', 4),
(24, '2025_11_08_065242_create_recycling_targets_table', 4),
(25, '2025_11_09_050031_create_collections_table', 5),
(26, '2025_11_09_050045_create_dashboard_preferences_table', 5),
(27, '2025_11_09_050118_create_scheduled_reports_table', 5),
(28, '2025_11_09_050133_create_generated_reports_table', 5),
(29, '2025_11_09_050252_add_zone_to_reports_table', 5),
(30, '2025_11_09_050320_add_unit_to_recycling_log_materials_table', 5),
(31, '2025_11_09_050340_add_collection_id_to_collection_logs_table', 5),
(33, '2025_11_09_071252_create_dismissed_alerts_table', 6),
(34, '2025_11_09_094450_add_performance_indexes_to_analytics_tables', 7);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 10),
(3, 'App\\Models\\User', 11),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(3, 'App\\Models\\User', 14);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'users.create', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(2, 'users.read', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(3, 'users.update', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(4, 'users.delete', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(5, 'users.manage_roles', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(6, 'routes.create', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(7, 'routes.read', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(8, 'routes.update', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(9, 'routes.delete', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(10, 'schedules.create', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(11, 'schedules.read', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(12, 'schedules.update', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(13, 'schedules.delete', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(14, 'collections.create', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(15, 'collections.read', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(16, 'collections.update', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(17, 'reports.create', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(18, 'reports.read', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(19, 'reports.update', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(20, 'reports.delete', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(21, 'recycling.create', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(22, 'recycling.read', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(23, 'recycling.update', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(24, 'dashboard.admin', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(25, 'dashboard.crew', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(26, 'dashboard.resident', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `recycling_logs`
--

CREATE TABLE `recycling_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `route_id` bigint(20) UNSIGNED DEFAULT NULL,
  `collection_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `quality_issue` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycling_logs`
--

INSERT INTO `recycling_logs` (`id`, `user_id`, `assignment_id`, `route_id`, `collection_date`, `notes`, `quality_issue`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 1, 4, '2025-10-31', NULL, 0, '2025-10-31 05:52:00', '2025-10-31 05:52:00', NULL),
(2, 4, 2, 7, '2025-10-31', 'Large volume collection today', 0, '2025-10-31 00:27:00', '2025-10-31 00:27:00', NULL),
(3, 3, 3, 1, '2025-10-31', NULL, 0, '2025-10-31 05:33:00', '2025-10-31 05:33:00', NULL),
(4, 9, 4, 3, '2025-10-31', 'Holiday collection - increased volume', 0, '2025-10-31 08:43:00', '2025-10-31 08:43:00', NULL),
(5, 5, 6, 2, '2025-11-01', 'Well-sorted materials', 0, '2025-11-01 02:06:00', '2025-11-01 02:06:00', NULL),
(6, 9, 7, 3, '2025-11-01', 'New recycling bins in use', 0, '2025-11-01 04:43:00', '2025-11-01 04:43:00', NULL),
(7, 4, 8, 7, '2025-11-01', 'Commercial area - mostly cardboard', 0, '2025-11-01 06:15:00', '2025-11-01 06:15:00', NULL),
(8, 2, 10, 7, '2025-11-02', 'Large volume collection today', 0, '2025-11-02 07:00:00', '2025-11-02 07:00:00', NULL),
(9, 9, 11, 6, '2025-11-02', 'Commercial area - mostly cardboard', 0, '2025-11-02 05:47:00', '2025-11-02 05:47:00', NULL),
(10, 4, 12, 3, '2025-11-02', 'Materials not properly sorted', 1, '2025-11-02 02:31:00', '2025-11-02 02:31:00', NULL),
(11, 7, 13, 3, '2025-11-03', 'Wet cardboard - reduced quality', 1, '2025-11-03 02:54:00', '2025-11-03 02:54:00', NULL),
(12, 9, 15, 6, '2025-11-03', 'Resident requested extra pickup', 0, '2025-11-03 05:02:00', '2025-11-03 05:02:00', NULL),
(13, 2, 16, 7, '2025-11-03', NULL, 0, '2025-11-03 06:58:00', '2025-11-03 06:58:00', NULL),
(14, 5, 17, 7, '2025-11-04', 'Plastic bags mixed with paper products', 1, '2025-11-04 00:57:00', '2025-11-04 00:57:00', NULL),
(15, 4, 18, 1, '2025-11-04', 'Resident requested extra pickup', 0, '2025-11-04 04:17:00', '2025-11-04 04:17:00', NULL),
(16, 7, 19, 4, '2025-11-04', NULL, 0, '2025-11-04 05:03:00', '2025-11-04 05:03:00', NULL),
(17, 3, 20, 2, '2025-11-04', 'Commercial area - mostly cardboard', 0, '2025-11-04 00:48:00', '2025-11-04 00:48:00', NULL),
(18, 9, 21, 4, '2025-11-05', 'Well-sorted materials', 0, '2025-11-05 03:54:00', '2025-11-05 03:54:00', NULL),
(19, 6, 22, 7, '2025-11-05', 'Contamination found - food waste mixed with recyclables', 1, '2025-11-05 01:30:00', '2025-11-05 01:30:00', NULL),
(20, 3, 25, 3, '2025-11-06', NULL, 0, '2025-11-06 05:21:00', '2025-11-06 05:21:00', NULL),
(21, 6, 26, 2, '2025-11-06', 'Materials not properly sorted', 1, '2025-11-06 06:33:00', '2025-11-06 06:33:00', NULL),
(22, 4, 27, 7, '2025-11-06', 'Well-sorted materials', 0, '2025-11-06 01:13:00', '2025-11-06 01:13:00', NULL),
(23, 5, 28, 8, '2025-11-06', NULL, 0, '2025-11-06 04:00:00', '2025-11-06 04:00:00', NULL),
(24, 6, 30, 2, '2025-11-07', 'Holiday collection - increased volume', 0, '2025-11-07 06:35:00', '2025-11-07 06:35:00', NULL),
(25, 8, 31, 6, '2025-11-07', NULL, 0, '2025-11-07 07:33:00', '2025-11-07 07:33:00', NULL),
(26, 9, 32, 4, '2025-11-07', 'Holiday collection - increased volume', 0, '2025-11-07 01:09:00', '2025-11-07 01:09:00', NULL),
(27, 5, 33, 1, '2025-11-07', NULL, 0, '2025-11-07 02:36:00', '2025-11-07 02:36:00', NULL),
(28, 9, 34, 1, '2025-11-08', 'Residential area - mixed materials', 0, '2025-11-08 02:09:00', '2025-11-08 02:09:00', NULL),
(29, 2, 36, 3, '2025-11-08', 'Well-sorted materials', 0, '2025-11-08 07:48:00', '2025-11-08 07:48:00', NULL),
(30, 2, 37, 2, '2025-11-09', 'Excessive contamination - needs resident education', 1, '2025-11-09 07:17:00', '2025-11-09 07:17:00', NULL),
(31, 8, 39, 3, '2025-11-09', 'Large volume collection today', 0, '2025-11-09 06:22:00', '2025-11-09 06:22:00', NULL),
(32, 6, 40, 6, '2025-11-09', NULL, 0, '2025-11-09 03:08:00', '2025-11-09 03:08:00', NULL),
(33, 9, 41, 7, '2025-11-10', 'Large volume collection today', 0, '2025-11-10 03:10:00', '2025-11-10 03:10:00', NULL),
(34, 8, 42, 6, '2025-11-10', 'Residential area - mixed materials', 0, '2025-11-10 03:31:00', '2025-11-10 03:31:00', NULL),
(35, 7, 43, 2, '2025-11-10', NULL, 0, '2025-11-10 03:28:00', '2025-11-10 03:28:00', NULL),
(36, 9, 45, 8, '2025-11-11', 'Good quality materials collected', 0, '2025-11-11 00:43:00', '2025-11-11 00:43:00', NULL),
(37, 4, 46, 7, '2025-11-11', 'Good quality materials collected', 0, '2025-11-11 03:21:00', '2025-11-11 03:21:00', NULL),
(38, 9, 47, 4, '2025-11-12', NULL, 0, '2025-11-12 04:39:00', '2025-11-12 04:39:00', NULL),
(39, 5, 48, 3, '2025-11-12', 'Holiday collection - increased volume', 0, '2025-11-12 05:45:00', '2025-11-12 05:45:00', NULL),
(40, 6, 49, 2, '2025-11-12', 'Well-sorted materials', 0, '2025-11-12 06:13:00', '2025-11-12 06:13:00', NULL),
(41, 3, 50, 6, '2025-11-13', 'Wet cardboard - reduced quality', 1, '2025-11-13 06:24:00', '2025-11-13 06:24:00', NULL),
(42, 5, 51, 4, '2025-11-13', 'Residential area - mixed materials', 0, '2025-11-13 00:56:00', '2025-11-13 00:56:00', NULL),
(43, 7, 52, 2, '2025-11-13', NULL, 0, '2025-11-13 04:26:00', '2025-11-13 04:26:00', NULL),
(44, 2, 53, 1, '2025-11-13', 'Materials not properly sorted', 1, '2025-11-13 07:41:00', '2025-11-13 07:41:00', NULL),
(45, 9, 54, 7, '2025-11-13', 'Excessive contamination - needs resident education', 1, '2025-11-13 00:01:00', '2025-11-13 00:01:00', NULL),
(46, 8, 55, 3, '2025-11-14', NULL, 0, '2025-11-14 08:19:00', '2025-11-14 08:19:00', NULL),
(47, 4, 56, 8, '2025-11-14', 'Good quality materials collected', 0, '2025-11-14 05:07:00', '2025-11-14 05:07:00', NULL),
(48, 9, 57, 2, '2025-11-14', 'New recycling bins in use', 0, '2025-11-14 05:39:00', '2025-11-14 05:39:00', NULL),
(49, 6, 58, 1, '2025-11-14', NULL, 0, '2025-11-14 08:36:00', '2025-11-14 08:36:00', NULL),
(50, 5, 59, 6, '2025-11-15', 'Commercial area - mostly cardboard', 0, '2025-11-15 07:21:00', '2025-11-15 07:21:00', NULL),
(51, 6, 61, 4, '2025-11-15', 'Good quality materials collected', 0, '2025-11-15 05:54:00', '2025-11-15 05:54:00', NULL),
(52, 7, 62, 4, '2025-11-16', 'New recycling bins in use', 0, '2025-11-16 03:04:00', '2025-11-16 03:04:00', NULL),
(53, 3, 64, 6, '2025-11-16', NULL, 0, '2025-11-16 08:59:00', '2025-11-16 08:59:00', NULL),
(54, 7, 65, 3, '2025-11-17', 'Large volume collection today', 0, '2025-11-17 07:38:00', '2025-11-17 07:38:00', NULL),
(55, 6, 66, 6, '2025-11-17', 'Good quality materials collected', 0, '2025-11-17 01:17:00', '2025-11-17 01:17:00', NULL),
(56, 9, 69, 1, '2025-11-18', 'Good quality materials collected', 0, '2025-11-18 01:19:00', '2025-11-18 01:19:00', NULL),
(57, 8, 71, 7, '2025-11-18', NULL, 0, '2025-11-18 06:29:00', '2025-11-18 06:29:00', NULL),
(58, 8, 72, 6, '2025-11-19', 'Large volume collection today', 0, '2025-11-19 04:24:00', '2025-11-19 04:24:00', NULL),
(59, 4, 74, 8, '2025-11-19', 'New recycling bins in use', 0, '2025-11-19 01:15:00', '2025-11-19 01:15:00', NULL),
(60, 3, 75, 3, '2025-11-19', NULL, 0, '2025-11-19 01:27:00', '2025-11-19 01:27:00', NULL),
(61, 5, 76, 2, '2025-11-19', 'Residential area - mixed materials', 0, '2025-11-19 04:53:00', '2025-11-19 04:53:00', NULL),
(62, 7, 77, 3, '2025-11-20', NULL, 0, '2025-11-20 02:12:00', '2025-11-20 02:12:00', NULL),
(63, 3, 79, 4, '2025-11-20', 'Residential area - mixed materials', 0, '2025-11-20 02:32:00', '2025-11-20 02:32:00', NULL),
(64, 8, 80, 8, '2025-11-20', 'New recycling bins in use', 0, '2025-11-20 00:46:00', '2025-11-20 00:46:00', NULL),
(65, 9, 81, 4, '2025-11-21', 'Commercial area - mostly cardboard', 0, '2025-11-21 03:46:00', '2025-11-21 03:46:00', NULL),
(66, 7, 83, 3, '2025-11-21', NULL, 0, '2025-11-21 06:37:00', '2025-11-21 06:37:00', NULL),
(67, 4, NULL, 1, '2025-09-04', NULL, 0, '2025-09-04 07:01:00', '2025-09-04 07:01:00', NULL),
(68, 3, NULL, 1, '2025-10-25', NULL, 0, '2025-10-25 05:47:00', '2025-10-25 05:47:00', NULL),
(69, 5, NULL, 6, '2025-10-17', 'Holiday collection - increased volume', 0, '2025-10-17 05:43:00', '2025-10-17 05:43:00', NULL),
(70, 9, NULL, 8, '2025-10-04', 'Excessive contamination - needs resident education', 1, '2025-10-04 03:02:00', '2025-10-04 03:02:00', NULL),
(71, 5, NULL, 8, '2025-10-19', 'Excessive contamination - needs resident education', 1, '2025-10-19 05:35:00', '2025-10-19 05:35:00', NULL),
(72, 6, NULL, 3, '2025-08-24', NULL, 0, '2025-08-24 02:59:00', '2025-08-24 02:59:00', NULL),
(73, 9, NULL, 1, '2025-10-23', 'Residential area - mixed materials', 0, '2025-10-23 01:49:00', '2025-10-23 01:49:00', NULL),
(74, 2, NULL, 1, '2025-10-22', NULL, 0, '2025-10-22 06:15:00', '2025-10-22 06:15:00', NULL),
(75, 9, NULL, 1, '2025-10-18', 'New recycling bins in use', 0, '2025-10-18 03:58:00', '2025-10-18 03:58:00', NULL),
(76, 2, NULL, 6, '2025-10-14', 'Commercial area - mostly cardboard', 0, '2025-10-14 01:22:00', '2025-10-14 01:22:00', NULL),
(77, 4, NULL, 4, '2025-10-13', 'Large volume collection today', 0, '2025-10-13 05:12:00', '2025-10-13 05:12:00', NULL),
(78, 5, NULL, 6, '2025-11-06', 'Residential area - mixed materials', 0, '2025-11-06 04:22:00', '2025-11-06 04:22:00', NULL),
(79, 2, NULL, 1, '2025-11-08', 'Resident requested extra pickup', 0, '2025-11-08 07:52:00', '2025-11-08 07:52:00', NULL),
(80, 5, NULL, 1, '2025-08-18', NULL, 0, '2025-08-18 02:35:00', '2025-08-18 02:35:00', NULL),
(81, 9, NULL, 6, '2025-09-04', NULL, 0, '2025-09-04 02:58:00', '2025-09-04 02:58:00', NULL),
(82, 4, NULL, 6, '2025-10-21', NULL, 0, '2025-10-21 08:37:00', '2025-10-21 08:37:00', NULL),
(83, 8, NULL, 7, '2025-10-01', 'Glass broken and mixed with other materials', 1, '2025-10-01 08:30:00', '2025-10-01 08:30:00', NULL),
(84, 2, 1, 4, '2025-10-31', 'Large volume collection today', 0, '2025-10-31 05:42:00', '2025-10-31 05:42:00', NULL),
(85, 4, 2, 7, '2025-10-31', 'Commercial area - mostly cardboard', 0, '2025-10-31 07:38:00', '2025-10-31 07:38:00', NULL),
(86, 3, 3, 1, '2025-10-31', 'Commercial area - mostly cardboard', 0, '2025-10-31 05:31:00', '2025-10-31 05:31:00', NULL),
(87, 9, 4, 3, '2025-10-31', 'Resident requested extra pickup', 0, '2025-10-31 08:47:00', '2025-10-31 08:47:00', NULL),
(88, 2, 5, 4, '2025-11-01', NULL, 0, '2025-11-01 07:09:00', '2025-11-01 07:09:00', NULL),
(89, 5, 6, 2, '2025-11-01', NULL, 0, '2025-11-01 05:20:00', '2025-11-01 05:20:00', NULL),
(90, 9, 7, 3, '2025-11-01', 'Commercial area - mostly cardboard', 0, '2025-11-01 01:33:00', '2025-11-01 01:33:00', NULL),
(91, 5, 9, 4, '2025-11-02', 'New recycling bins in use', 0, '2025-11-02 07:04:00', '2025-11-02 07:04:00', NULL),
(92, 2, 10, 7, '2025-11-02', 'Large volume collection today', 0, '2025-11-02 04:36:00', '2025-11-02 04:36:00', NULL),
(93, 9, 11, 6, '2025-11-02', 'Holiday collection - increased volume', 0, '2025-11-02 05:53:00', '2025-11-02 05:53:00', NULL),
(94, 4, 12, 3, '2025-11-02', 'Non-recyclable items included in collection', 1, '2025-11-02 06:24:00', '2025-11-02 06:24:00', NULL),
(95, 7, 13, 3, '2025-11-03', 'Plastic bags mixed with paper products', 1, '2025-11-03 03:07:00', '2025-11-03 03:07:00', NULL),
(96, 6, 14, 2, '2025-11-03', 'Resident requested extra pickup', 0, '2025-11-03 06:11:00', '2025-11-03 06:11:00', NULL),
(97, 9, 15, 6, '2025-11-03', 'Residential area - mixed materials', 0, '2025-11-03 02:53:00', '2025-11-03 02:53:00', NULL),
(98, 2, 16, 7, '2025-11-03', 'Plastic bags mixed with paper products', 1, '2025-11-03 01:13:00', '2025-11-03 01:13:00', NULL),
(99, 5, 17, 7, '2025-11-04', 'Holiday collection - increased volume', 0, '2025-11-04 05:08:00', '2025-11-04 05:08:00', NULL),
(100, 4, 18, 1, '2025-11-04', 'Good quality materials collected', 0, '2025-11-04 02:29:00', '2025-11-04 02:29:00', NULL),
(101, 7, 19, 4, '2025-11-04', 'New recycling bins in use', 0, '2025-11-04 05:18:00', '2025-11-04 05:18:00', NULL),
(102, 9, 21, 4, '2025-11-05', 'Residential area - mixed materials', 0, '2025-11-05 03:51:00', '2025-11-05 03:51:00', NULL),
(103, 6, 22, 7, '2025-11-05', NULL, 0, '2025-11-05 02:58:00', '2025-11-05 02:58:00', NULL),
(104, 3, 25, 3, '2025-11-06', 'Commercial area - mostly cardboard', 0, '2025-11-06 02:59:00', '2025-11-06 02:59:00', NULL),
(105, 6, 26, 2, '2025-11-06', 'Large volume collection today', 0, '2025-11-06 02:36:00', '2025-11-06 02:36:00', NULL),
(106, 4, 27, 7, '2025-11-06', 'New recycling bins in use', 0, '2025-11-06 06:07:00', '2025-11-06 06:07:00', NULL),
(107, 7, 29, 8, '2025-11-07', 'Large volume collection today', 0, '2025-11-07 00:24:00', '2025-11-07 00:24:00', NULL),
(108, 6, 30, 2, '2025-11-07', NULL, 0, '2025-11-07 03:47:00', '2025-11-07 03:47:00', NULL),
(109, 9, 32, 4, '2025-11-07', 'Good quality materials collected', 0, '2025-11-07 03:50:00', '2025-11-07 03:50:00', NULL),
(110, 5, 33, 1, '2025-11-07', 'New recycling bins in use', 0, '2025-11-07 06:09:00', '2025-11-07 06:09:00', NULL),
(111, 2, 37, 2, '2025-11-09', NULL, 0, '2025-11-09 06:11:00', '2025-11-09 06:11:00', NULL),
(112, 9, 38, 7, '2025-11-09', 'New recycling bins in use', 0, '2025-11-09 02:37:00', '2025-11-09 02:37:00', NULL),
(113, 6, 40, 6, '2025-11-09', 'Holiday collection - increased volume', 0, '2025-11-09 00:49:00', '2025-11-09 00:49:00', NULL),
(114, 9, 41, 7, '2025-11-10', 'Resident requested extra pickup', 0, '2025-11-10 00:07:00', '2025-11-10 00:07:00', NULL),
(115, 8, 42, 6, '2025-11-10', 'New recycling bins in use', 0, '2025-11-10 08:12:00', '2025-11-10 08:12:00', NULL),
(116, 7, 43, 2, '2025-11-10', NULL, 0, '2025-11-10 00:53:00', '2025-11-10 00:53:00', NULL),
(117, 2, 44, 1, '2025-11-11', 'Holiday collection - increased volume', 0, '2025-11-11 08:41:00', '2025-11-11 08:41:00', NULL),
(118, 9, 45, 8, '2025-11-11', 'Residential area - mixed materials', 0, '2025-11-11 04:28:00', '2025-11-11 04:28:00', NULL),
(119, 4, 46, 7, '2025-11-11', NULL, 0, '2025-11-11 06:14:00', '2025-11-11 06:14:00', NULL),
(120, 9, 47, 4, '2025-11-12', 'Well-sorted materials', 0, '2025-11-12 02:55:00', '2025-11-12 02:55:00', NULL),
(121, 5, 48, 3, '2025-11-12', 'Non-recyclable items included in collection', 1, '2025-11-12 00:07:00', '2025-11-12 00:07:00', NULL),
(122, 6, 49, 2, '2025-11-12', NULL, 0, '2025-11-12 05:11:00', '2025-11-12 05:11:00', NULL),
(123, 3, 50, 6, '2025-11-13', 'Holiday collection - increased volume', 0, '2025-11-13 08:25:00', '2025-11-13 08:25:00', NULL),
(124, 5, 51, 4, '2025-11-13', 'Non-recyclable items included in collection', 1, '2025-11-13 04:20:00', '2025-11-13 04:20:00', NULL),
(125, 7, 52, 2, '2025-11-13', NULL, 0, '2025-11-13 08:35:00', '2025-11-13 08:35:00', NULL),
(126, 2, 53, 1, '2025-11-13', 'Well-sorted materials', 0, '2025-11-13 01:22:00', '2025-11-13 01:22:00', NULL),
(127, 9, 54, 7, '2025-11-13', NULL, 0, '2025-11-13 06:10:00', '2025-11-13 06:10:00', NULL),
(128, 8, 55, 3, '2025-11-14', NULL, 0, '2025-11-14 01:00:00', '2025-11-14 01:00:00', NULL),
(129, 4, 56, 8, '2025-11-14', 'Residential area - mixed materials', 0, '2025-11-14 08:44:00', '2025-11-14 08:44:00', NULL),
(130, 9, 57, 2, '2025-11-14', 'Good quality materials collected', 0, '2025-11-14 03:50:00', '2025-11-14 03:50:00', NULL),
(131, 6, 58, 1, '2025-11-14', 'Contamination found - food waste mixed with recyclables', 1, '2025-11-14 02:47:00', '2025-11-14 02:47:00', NULL),
(132, 5, 59, 6, '2025-11-15', 'Glass broken and mixed with other materials', 1, '2025-11-15 07:31:00', '2025-11-15 07:31:00', NULL),
(133, 4, 60, 1, '2025-11-15', NULL, 0, '2025-11-15 02:52:00', '2025-11-15 02:52:00', NULL),
(134, 7, 62, 4, '2025-11-16', 'Residential area - mixed materials', 0, '2025-11-16 06:54:00', '2025-11-16 06:54:00', NULL),
(135, 6, 63, 8, '2025-11-16', 'Resident requested extra pickup', 0, '2025-11-16 08:32:00', '2025-11-16 08:32:00', NULL),
(136, 3, 64, 6, '2025-11-16', 'Holiday collection - increased volume', 0, '2025-11-16 00:04:00', '2025-11-16 00:04:00', NULL),
(137, 7, 65, 3, '2025-11-17', 'Residential area - mixed materials', 0, '2025-11-17 03:58:00', '2025-11-17 03:58:00', NULL),
(138, 6, 66, 6, '2025-11-17', 'Good quality materials collected', 0, '2025-11-17 03:16:00', '2025-11-17 03:16:00', NULL),
(139, 8, 67, 7, '2025-11-17', 'Residential area - mixed materials', 0, '2025-11-17 07:13:00', '2025-11-17 07:13:00', NULL),
(140, 5, 68, 2, '2025-11-17', 'Resident requested extra pickup', 0, '2025-11-17 07:31:00', '2025-11-17 07:31:00', NULL),
(141, 9, 69, 1, '2025-11-18', 'Well-sorted materials', 0, '2025-11-18 06:33:00', '2025-11-18 06:33:00', NULL),
(142, 7, 70, 3, '2025-11-18', NULL, 0, '2025-11-18 03:51:00', '2025-11-18 03:51:00', NULL),
(143, 8, 71, 7, '2025-11-18', NULL, 0, '2025-11-18 05:52:00', '2025-11-18 05:52:00', NULL),
(144, 8, 72, 6, '2025-11-19', 'Non-recyclable items included in collection', 1, '2025-11-19 02:25:00', '2025-11-19 02:25:00', NULL),
(145, 6, 73, 7, '2025-11-19', 'Well-sorted materials', 0, '2025-11-19 02:37:00', '2025-11-19 02:37:00', NULL),
(146, 4, 74, 8, '2025-11-19', NULL, 0, '2025-11-19 05:03:00', '2025-11-19 05:03:00', NULL),
(147, 3, 75, 3, '2025-11-19', 'Good quality materials collected', 0, '2025-11-19 02:06:00', '2025-11-19 02:06:00', NULL),
(148, 5, 76, 2, '2025-11-19', 'New recycling bins in use', 0, '2025-11-19 03:53:00', '2025-11-19 03:53:00', NULL),
(149, 7, 77, 3, '2025-11-20', NULL, 0, '2025-11-20 04:54:00', '2025-11-20 04:54:00', NULL),
(150, 6, 78, 2, '2025-11-20', 'Large volume collection today', 0, '2025-11-20 04:58:00', '2025-11-20 04:58:00', NULL),
(151, 3, 79, 4, '2025-11-20', NULL, 0, '2025-11-20 08:28:00', '2025-11-20 08:28:00', NULL),
(152, 8, 80, 8, '2025-11-20', 'Large volume collection today', 0, '2025-11-20 07:30:00', '2025-11-20 07:30:00', NULL),
(153, 9, 81, 4, '2025-11-21', 'Excessive contamination - needs resident education', 1, '2025-11-21 08:52:00', '2025-11-21 08:52:00', NULL),
(154, 3, 82, 6, '2025-11-21', 'Large volume collection today', 0, '2025-11-21 00:15:00', '2025-11-21 00:15:00', NULL),
(155, 7, 83, 3, '2025-11-21', 'Materials not properly sorted', 1, '2025-11-21 00:50:00', '2025-11-21 00:50:00', NULL),
(156, 2, NULL, 1, '2025-09-07', 'Good quality materials collected', 0, '2025-09-07 04:04:00', '2025-09-07 04:04:00', NULL),
(157, 3, NULL, 7, '2025-09-10', NULL, 0, '2025-09-10 05:23:00', '2025-09-10 05:23:00', NULL),
(158, 9, NULL, 1, '2025-08-13', NULL, 0, '2025-08-13 01:54:00', '2025-08-13 01:54:00', NULL),
(159, 4, NULL, 2, '2025-10-28', 'Well-sorted materials', 0, '2025-10-28 01:01:00', '2025-10-28 01:01:00', NULL),
(160, 9, NULL, 1, '2025-08-15', 'Materials not properly sorted', 1, '2025-08-15 06:45:00', '2025-08-15 06:45:00', NULL),
(161, 9, NULL, 2, '2025-09-09', 'Glass broken and mixed with other materials', 1, '2025-09-09 00:23:00', '2025-09-09 00:23:00', NULL),
(162, 3, NULL, 6, '2025-09-21', 'Contamination found - food waste mixed with recyclables', 1, '2025-09-21 03:13:00', '2025-09-21 03:13:00', NULL),
(163, 8, NULL, 1, '2025-11-01', NULL, 0, '2025-11-01 02:08:00', '2025-11-01 02:08:00', NULL),
(164, 4, NULL, 6, '2025-09-02', 'Holiday collection - increased volume', 0, '2025-09-02 07:34:00', '2025-09-02 07:34:00', NULL),
(165, 2, NULL, 7, '2025-10-22', 'Non-recyclable items included in collection', 1, '2025-10-22 07:38:00', '2025-10-22 07:38:00', NULL),
(166, 5, NULL, 3, '2025-09-14', 'Non-recyclable items included in collection', 1, '2025-09-14 07:20:00', '2025-09-14 07:20:00', NULL),
(167, 2, 37, 2, '2025-11-09', 'school supplies and shi', 0, '2025-11-08 20:50:26', '2025-11-08 20:50:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `recycling_log_materials`
--

CREATE TABLE `recycling_log_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `recycling_log_id` bigint(20) UNSIGNED NOT NULL,
  `material_type` enum('plastic','paper','glass','metal','cardboard','organic') NOT NULL,
  `weight` decimal(8,2) NOT NULL,
  `unit` enum('kg','lbs','tons') NOT NULL DEFAULT 'kg',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycling_log_materials`
--

INSERT INTO `recycling_log_materials` (`id`, `recycling_log_id`, `material_type`, `weight`, `unit`, `created_at`, `updated_at`) VALUES
(1, 1, 'plastic', 15.18, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(2, 1, 'paper', 38.43, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(3, 1, 'glass', 25.10, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(4, 1, 'metal', 32.78, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(5, 1, 'cardboard', 72.40, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(6, 1, 'organic', 53.73, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(7, 2, 'plastic', 35.22, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(8, 2, 'paper', 29.28, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(9, 2, 'glass', 72.19, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(10, 2, 'metal', 51.23, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(11, 2, 'cardboard', 17.97, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(12, 2, 'organic', 114.31, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(13, 3, 'paper', 24.30, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(14, 3, 'glass', 84.97, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(15, 3, 'metal', 17.50, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(16, 3, 'organic', 96.34, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(17, 4, 'plastic', 9.47, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(18, 4, 'cardboard', 22.33, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(19, 4, 'organic', 25.00, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(20, 5, 'plastic', 21.78, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(21, 6, 'plastic', 17.02, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(22, 6, 'glass', 41.35, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(23, 6, 'metal', 18.86, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(24, 7, 'paper', 49.32, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(25, 7, 'metal', 48.94, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(26, 7, 'organic', 105.52, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(27, 8, 'plastic', 5.16, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(28, 8, 'cardboard', 34.13, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(29, 9, 'plastic', 26.82, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(30, 10, 'organic', 144.88, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(31, 11, 'glass', 39.63, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(32, 11, 'cardboard', 50.65, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(33, 11, 'organic', 79.42, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(34, 12, 'plastic', 6.06, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(35, 12, 'paper', 70.60, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(36, 12, 'glass', 67.21, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(37, 12, 'cardboard', 62.48, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(38, 12, 'organic', 22.85, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(39, 13, 'plastic', 24.66, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(40, 13, 'metal', 19.36, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(41, 14, 'paper', 60.10, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(42, 14, 'glass', 52.45, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(43, 14, 'metal', 45.28, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(44, 14, 'cardboard', 26.53, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(45, 14, 'organic', 143.46, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(46, 15, 'plastic', 22.27, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(47, 15, 'paper', 21.92, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(48, 15, 'glass', 32.51, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(49, 16, 'paper', 45.82, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(50, 17, 'glass', 76.00, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(51, 17, 'metal', 24.64, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(52, 18, 'paper', 22.05, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(53, 18, 'cardboard', 28.64, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(54, 19, 'plastic', 34.28, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(55, 19, 'paper', 75.49, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(56, 19, 'glass', 93.08, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(57, 19, 'metal', 15.76, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(58, 19, 'cardboard', 27.34, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(59, 19, 'organic', 20.21, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(60, 20, 'glass', 24.74, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(61, 20, 'metal', 34.36, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(62, 20, 'cardboard', 74.69, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(63, 20, 'organic', 21.63, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(64, 21, 'plastic', 16.09, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(65, 21, 'glass', 98.58, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(66, 22, 'paper', 33.27, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(67, 22, 'glass', 31.87, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(68, 22, 'metal', 11.23, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(69, 22, 'cardboard', 48.96, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(70, 22, 'organic', 107.94, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(71, 23, 'plastic', 49.21, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(72, 23, 'paper', 74.40, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(73, 24, 'metal', 20.63, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(74, 25, 'paper', 26.19, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(75, 25, 'glass', 64.61, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(76, 25, 'cardboard', 63.56, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(77, 25, 'organic', 81.54, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(78, 26, 'plastic', 7.42, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(79, 26, 'paper', 70.87, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(80, 26, 'metal', 42.45, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(81, 26, 'cardboard', 54.05, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(82, 26, 'organic', 81.74, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(83, 27, 'plastic', 37.25, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(84, 27, 'paper', 55.15, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(85, 27, 'glass', 54.15, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(86, 27, 'metal', 36.18, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(87, 27, 'cardboard', 52.17, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(88, 27, 'organic', 73.37, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(89, 28, 'glass', 99.07, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(90, 28, 'metal', 17.35, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(91, 28, 'organic', 144.88, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(92, 29, 'metal', 50.97, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(93, 29, 'organic', 65.58, 'kg', '2025-11-08 18:52:20', '2025-11-08 18:52:20'),
(94, 30, 'paper', 14.89, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(95, 30, 'glass', 50.04, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(96, 30, 'metal', 58.12, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(97, 30, 'cardboard', 69.41, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(98, 30, 'organic', 102.63, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(99, 31, 'plastic', 16.99, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(100, 31, 'paper', 24.94, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(101, 31, 'glass', 31.60, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(102, 31, 'metal', 30.09, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(103, 31, 'organic', 60.63, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(104, 32, 'plastic', 11.63, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(105, 32, 'paper', 40.85, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(106, 32, 'glass', 72.32, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(107, 32, 'metal', 32.52, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(108, 32, 'cardboard', 37.17, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(109, 32, 'organic', 56.70, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(110, 33, 'plastic', 11.95, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(111, 33, 'metal', 45.64, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(112, 33, 'cardboard', 73.52, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(113, 33, 'organic', 41.93, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(114, 34, 'plastic', 46.66, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(115, 34, 'paper', 66.78, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(116, 34, 'metal', 36.35, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(117, 34, 'cardboard', 87.91, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(118, 34, 'organic', 101.08, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(119, 35, 'glass', 69.63, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(120, 35, 'organic', 77.75, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(121, 36, 'paper', 39.80, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(122, 37, 'plastic', 19.00, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(123, 37, 'paper', 39.24, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(124, 37, 'cardboard', 57.03, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(125, 38, 'paper', 49.16, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(126, 39, 'plastic', 6.51, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(127, 39, 'paper', 78.43, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(128, 39, 'metal', 55.49, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(129, 40, 'paper', 48.98, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(130, 40, 'glass', 24.69, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(131, 40, 'metal', 34.41, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(132, 40, 'cardboard', 46.74, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(133, 40, 'organic', 139.88, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(134, 41, 'glass', 81.21, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(135, 41, 'metal', 9.18, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(136, 42, 'plastic', 8.32, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(137, 42, 'paper', 18.05, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(138, 42, 'glass', 77.66, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(139, 42, 'metal', 45.16, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(140, 42, 'organic', 74.79, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(141, 43, 'plastic', 43.61, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(142, 43, 'cardboard', 36.89, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(143, 43, 'organic', 93.08, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(144, 44, 'plastic', 19.47, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(145, 44, 'organic', 36.91, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(146, 45, 'plastic', 15.90, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(147, 45, 'metal', 19.69, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(148, 45, 'cardboard', 17.09, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(149, 46, 'metal', 27.89, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(150, 46, 'cardboard', 69.08, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(151, 47, 'metal', 17.00, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(152, 47, 'cardboard', 49.59, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(153, 48, 'plastic', 6.52, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(154, 48, 'glass', 53.47, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(155, 49, 'paper', 31.82, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(156, 49, 'glass', 98.88, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(157, 49, 'cardboard', 14.79, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(158, 50, 'plastic', 45.58, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(159, 50, 'paper', 46.18, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(160, 50, 'glass', 65.47, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(161, 50, 'metal', 15.50, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(162, 50, 'cardboard', 24.85, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(163, 50, 'organic', 57.87, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(164, 51, 'paper', 76.57, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(165, 52, 'plastic', 19.37, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(166, 52, 'paper', 19.51, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(167, 52, 'glass', 19.27, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(168, 52, 'metal', 11.67, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(169, 52, 'cardboard', 62.95, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(170, 52, 'organic', 125.73, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(171, 53, 'plastic', 49.42, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(172, 53, 'paper', 68.97, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(173, 53, 'glass', 54.29, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(174, 53, 'metal', 18.86, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(175, 53, 'cardboard', 52.48, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(176, 53, 'organic', 57.69, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(177, 54, 'plastic', 35.42, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(178, 54, 'paper', 76.53, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(179, 55, 'plastic', 31.07, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(180, 55, 'metal', 49.15, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(181, 55, 'cardboard', 67.39, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(182, 56, 'metal', 52.20, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(183, 56, 'cardboard', 62.19, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(184, 56, 'organic', 77.31, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(185, 57, 'organic', 92.78, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(186, 58, 'plastic', 27.15, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(187, 58, 'glass', 81.44, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(188, 58, 'cardboard', 35.54, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(189, 58, 'organic', 31.40, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(190, 59, 'cardboard', 71.87, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(191, 59, 'organic', 96.81, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(192, 60, 'glass', 23.07, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(193, 60, 'metal', 38.69, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(194, 60, 'cardboard', 84.97, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(195, 60, 'organic', 60.55, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(196, 61, 'plastic', 49.76, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(197, 61, 'glass', 67.22, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(198, 61, 'metal', 11.93, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(199, 62, 'plastic', 28.81, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(200, 62, 'glass', 80.43, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(201, 62, 'metal', 19.40, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(202, 63, 'metal', 33.59, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(203, 63, 'organic', 102.61, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(204, 64, 'cardboard', 73.00, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(205, 65, 'glass', 97.91, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(206, 66, 'plastic', 46.62, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(207, 66, 'paper', 53.02, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(208, 66, 'metal', 12.66, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(209, 66, 'cardboard', 76.88, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(210, 67, 'glass', 80.51, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(211, 68, 'paper', 62.65, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(212, 68, 'glass', 74.63, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(213, 68, 'metal', 9.10, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(214, 68, 'cardboard', 38.87, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(215, 69, 'paper', 50.31, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(216, 69, 'metal', 37.11, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(217, 69, 'cardboard', 54.62, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(218, 69, 'organic', 70.09, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(219, 70, 'organic', 88.67, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(220, 71, 'plastic', 30.64, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(221, 71, 'paper', 22.98, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(222, 71, 'glass', 65.29, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(223, 71, 'cardboard', 21.39, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(224, 72, 'plastic', 15.76, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(225, 72, 'metal', 17.18, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(226, 73, 'paper', 21.87, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(227, 73, 'metal', 48.64, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(228, 73, 'cardboard', 29.20, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(229, 73, 'organic', 26.70, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(230, 74, 'glass', 83.26, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(231, 75, 'plastic', 9.89, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(232, 75, 'paper', 52.80, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(233, 75, 'glass', 61.43, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(234, 75, 'metal', 41.03, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(235, 75, 'cardboard', 52.65, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(236, 75, 'organic', 46.23, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(237, 76, 'plastic', 30.02, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(238, 76, 'paper', 62.51, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(239, 76, 'glass', 71.84, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(240, 76, 'metal', 12.95, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(241, 76, 'cardboard', 74.02, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(242, 76, 'organic', 106.34, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(243, 77, 'plastic', 44.80, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(244, 77, 'paper', 17.34, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(245, 77, 'glass', 75.53, 'kg', '2025-11-08 18:52:21', '2025-11-08 18:52:21'),
(246, 77, 'metal', 24.44, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(247, 77, 'cardboard', 58.63, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(248, 78, 'metal', 42.45, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(249, 78, 'cardboard', 69.79, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(250, 79, 'plastic', 25.64, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(251, 79, 'paper', 27.67, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(252, 79, 'glass', 63.78, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(253, 79, 'metal', 51.98, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(254, 79, 'organic', 60.74, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(255, 80, 'glass', 33.60, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(256, 80, 'metal', 48.60, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(257, 80, 'cardboard', 31.47, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(258, 81, 'plastic', 23.22, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(259, 81, 'glass', 27.00, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(260, 82, 'paper', 34.05, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(261, 82, 'glass', 37.30, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(262, 82, 'metal', 20.58, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(263, 82, 'cardboard', 75.97, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(264, 82, 'organic', 93.61, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(265, 83, 'plastic', 34.11, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(266, 83, 'cardboard', 43.54, 'kg', '2025-11-08 18:52:22', '2025-11-08 18:52:22'),
(267, 84, 'plastic', 36.38, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(268, 84, 'paper', 21.59, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(269, 85, 'cardboard', 60.17, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(270, 85, 'organic', 47.81, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(271, 86, 'plastic', 25.38, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(272, 86, 'paper', 70.22, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(273, 86, 'glass', 62.11, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(274, 86, 'organic', 134.60, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(275, 87, 'plastic', 39.32, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(276, 88, 'plastic', 15.18, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(277, 88, 'paper', 44.90, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(278, 88, 'cardboard', 53.11, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(279, 88, 'organic', 65.18, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(280, 89, 'paper', 34.70, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(281, 89, 'glass', 92.93, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(282, 89, 'cardboard', 24.23, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(283, 90, 'paper', 40.93, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(284, 90, 'metal', 43.09, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(285, 90, 'cardboard', 78.41, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(286, 90, 'organic', 120.84, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(287, 91, 'paper', 37.47, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(288, 92, 'metal', 19.09, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(289, 92, 'cardboard', 53.34, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(290, 92, 'organic', 23.74, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(291, 93, 'paper', 38.71, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(292, 93, 'glass', 44.45, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(293, 93, 'cardboard', 16.83, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(294, 94, 'plastic', 26.72, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(295, 94, 'cardboard', 28.59, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(296, 95, 'plastic', 46.82, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(297, 95, 'paper', 58.38, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(298, 95, 'glass', 42.82, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(299, 95, 'metal', 31.93, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(300, 95, 'cardboard', 45.87, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(301, 95, 'organic', 65.98, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(302, 96, 'metal', 46.24, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(303, 97, 'plastic', 32.65, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(304, 97, 'organic', 145.21, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(305, 98, 'plastic', 40.68, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(306, 98, 'paper', 63.33, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(307, 98, 'metal', 35.67, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(308, 98, 'organic', 48.36, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(309, 99, 'glass', 85.69, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(310, 99, 'cardboard', 15.92, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(311, 100, 'plastic', 8.93, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(312, 100, 'paper', 25.64, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(313, 100, 'glass', 89.85, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(314, 100, 'metal', 36.38, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(315, 100, 'cardboard', 83.47, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(316, 100, 'organic', 32.39, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(317, 101, 'plastic', 21.33, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(318, 101, 'glass', 51.76, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(319, 101, 'cardboard', 27.95, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(320, 101, 'organic', 35.99, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(321, 102, 'organic', 104.50, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(322, 103, 'plastic', 16.00, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(323, 103, 'paper', 48.25, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(324, 103, 'glass', 48.82, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(325, 103, 'cardboard', 46.67, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(326, 103, 'organic', 109.05, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(327, 104, 'plastic', 14.68, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(328, 104, 'cardboard', 33.49, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(329, 104, 'organic', 122.20, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(330, 105, 'plastic', 44.52, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(331, 105, 'metal', 44.24, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(332, 105, 'cardboard', 56.86, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(333, 106, 'glass', 41.39, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(334, 106, 'metal', 48.02, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(335, 107, 'paper', 76.32, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(336, 107, 'organic', 123.66, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(337, 108, 'plastic', 46.42, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(338, 108, 'cardboard', 42.07, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(339, 109, 'paper', 10.20, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(340, 109, 'metal', 33.12, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(341, 109, 'organic', 110.89, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(342, 110, 'glass', 21.70, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(343, 110, 'cardboard', 84.90, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(344, 111, 'plastic', 45.78, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(345, 111, 'glass', 45.15, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(346, 111, 'metal', 22.45, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(347, 111, 'cardboard', 63.56, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(348, 111, 'organic', 125.61, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(349, 112, 'plastic', 27.20, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(350, 112, 'glass', 19.67, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(351, 112, 'cardboard', 27.61, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(352, 112, 'organic', 75.94, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(353, 113, 'plastic', 26.17, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(354, 114, 'plastic', 25.50, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(355, 114, 'paper', 36.20, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(356, 114, 'organic', 100.27, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(357, 115, 'plastic', 44.41, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(358, 115, 'paper', 14.12, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(359, 115, 'glass', 74.25, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(360, 115, 'metal', 23.92, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(361, 115, 'cardboard', 27.48, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(362, 115, 'organic', 22.69, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(363, 116, 'plastic', 42.23, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(364, 116, 'paper', 16.59, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(365, 116, 'glass', 17.62, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(366, 116, 'cardboard', 77.14, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(367, 116, 'organic', 65.65, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(368, 117, 'plastic', 22.86, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(369, 117, 'paper', 27.27, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(370, 117, 'glass', 73.14, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(371, 117, 'metal', 41.96, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(372, 117, 'cardboard', 82.97, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(373, 117, 'organic', 20.69, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(374, 118, 'glass', 94.13, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(375, 118, 'cardboard', 81.19, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(376, 119, 'plastic', 47.82, 'kg', '2025-11-08 19:26:37', '2025-11-08 19:26:37'),
(377, 119, 'paper', 50.14, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(378, 119, 'glass', 68.57, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(379, 119, 'metal', 37.59, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(380, 119, 'cardboard', 15.24, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(381, 120, 'plastic', 42.26, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(382, 120, 'paper', 44.39, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(383, 120, 'metal', 17.69, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(384, 120, 'cardboard', 21.69, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(385, 120, 'organic', 69.81, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(386, 121, 'plastic', 44.68, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(387, 121, 'cardboard', 75.59, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(388, 122, 'paper', 71.49, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(389, 123, 'plastic', 24.82, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(390, 123, 'paper', 14.98, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(391, 124, 'plastic', 5.28, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(392, 124, 'paper', 50.79, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(393, 124, 'glass', 61.39, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(394, 124, 'organic', 52.21, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(395, 125, 'plastic', 8.32, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(396, 126, 'organic', 41.58, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(397, 127, 'glass', 23.84, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(398, 127, 'metal', 50.51, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(399, 127, 'cardboard', 52.14, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(400, 128, 'glass', 42.70, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(401, 129, 'plastic', 15.60, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(402, 129, 'paper', 31.14, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(403, 129, 'glass', 50.99, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(404, 129, 'cardboard', 49.15, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(405, 129, 'organic', 66.85, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(406, 130, 'cardboard', 33.18, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(407, 131, 'paper', 30.41, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(408, 131, 'glass', 16.02, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(409, 131, 'metal', 11.66, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(410, 131, 'organic', 96.50, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(411, 132, 'plastic', 20.10, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(412, 132, 'paper', 52.18, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(413, 132, 'glass', 77.40, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(414, 132, 'cardboard', 72.53, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(415, 132, 'organic', 28.57, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(416, 133, 'metal', 50.77, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(417, 133, 'cardboard', 77.64, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(418, 133, 'organic', 96.55, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(419, 134, 'paper', 59.66, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(420, 134, 'glass', 60.09, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(421, 134, 'cardboard', 74.02, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(422, 134, 'organic', 120.22, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(423, 135, 'glass', 31.04, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(424, 135, 'metal', 52.16, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(425, 135, 'cardboard', 57.70, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(426, 135, 'organic', 137.66, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(427, 136, 'paper', 17.25, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(428, 136, 'glass', 28.13, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(429, 137, 'cardboard', 16.89, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(430, 137, 'organic', 80.68, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(431, 138, 'metal', 49.62, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(432, 138, 'cardboard', 72.63, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(433, 139, 'metal', 22.29, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(434, 140, 'paper', 16.11, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(435, 140, 'glass', 83.19, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(436, 140, 'cardboard', 16.29, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(437, 140, 'organic', 62.71, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(438, 141, 'paper', 71.83, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(439, 141, 'glass', 73.70, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(440, 141, 'organic', 93.81, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(441, 142, 'plastic', 37.24, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(442, 142, 'paper', 79.71, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(443, 142, 'glass', 41.75, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(444, 142, 'metal', 35.99, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(445, 142, 'cardboard', 13.17, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(446, 142, 'organic', 102.78, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(447, 143, 'plastic', 17.17, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(448, 143, 'paper', 58.00, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(449, 143, 'glass', 42.35, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(450, 143, 'metal', 42.99, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(451, 143, 'organic', 20.69, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(452, 144, 'paper', 16.32, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(453, 144, 'glass', 54.34, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(454, 144, 'organic', 98.31, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(455, 145, 'plastic', 11.10, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(456, 145, 'paper', 78.69, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(457, 145, 'glass', 63.92, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(458, 145, 'metal', 24.07, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(459, 145, 'cardboard', 76.96, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(460, 145, 'organic', 86.35, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(461, 146, 'plastic', 14.71, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(462, 147, 'organic', 147.47, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(463, 148, 'paper', 10.64, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(464, 148, 'glass', 43.13, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(465, 149, 'paper', 11.18, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(466, 149, 'glass', 93.35, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(467, 149, 'metal', 44.30, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(468, 149, 'cardboard', 81.64, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(469, 149, 'organic', 69.80, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(470, 150, 'plastic', 13.12, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(471, 150, 'paper', 60.41, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(472, 150, 'glass', 42.21, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(473, 150, 'metal', 55.76, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(474, 150, 'cardboard', 47.93, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(475, 150, 'organic', 28.22, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(476, 151, 'paper', 12.73, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(477, 151, 'glass', 77.77, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(478, 151, 'cardboard', 61.03, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(479, 151, 'organic', 123.05, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(480, 152, 'metal', 34.85, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(481, 152, 'organic', 29.63, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(482, 153, 'paper', 24.43, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(483, 153, 'cardboard', 56.55, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(484, 153, 'organic', 66.61, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(485, 154, 'glass', 58.77, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(486, 154, 'metal', 45.91, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(487, 154, 'cardboard', 52.11, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(488, 154, 'organic', 81.75, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(489, 155, 'plastic', 36.88, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(490, 155, 'paper', 34.89, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(491, 155, 'glass', 62.61, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(492, 155, 'metal', 15.53, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(493, 155, 'organic', 98.39, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(494, 156, 'plastic', 20.50, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(495, 156, 'paper', 14.77, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(496, 157, 'paper', 12.06, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(497, 158, 'metal', 20.10, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(498, 159, 'glass', 63.56, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(499, 160, 'plastic', 36.35, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(500, 160, 'metal', 58.09, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(501, 160, 'cardboard', 27.92, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(502, 161, 'organic', 132.85, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(503, 162, 'plastic', 38.95, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(504, 162, 'paper', 45.12, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(505, 162, 'glass', 70.85, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(506, 162, 'metal', 49.72, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(507, 162, 'cardboard', 51.66, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(508, 162, 'organic', 45.34, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(509, 163, 'plastic', 32.59, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(510, 163, 'paper', 76.77, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(511, 163, 'glass', 65.97, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(512, 163, 'metal', 23.20, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(513, 163, 'cardboard', 67.81, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(514, 163, 'organic', 70.48, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(515, 164, 'plastic', 42.69, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(516, 164, 'paper', 40.28, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(517, 164, 'metal', 57.46, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(518, 164, 'cardboard', 44.36, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(519, 165, 'paper', 67.40, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(520, 165, 'glass', 60.82, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(521, 165, 'metal', 14.96, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(522, 165, 'cardboard', 53.93, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(523, 165, 'organic', 41.19, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(524, 166, 'plastic', 24.09, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(525, 166, 'paper', 67.50, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(526, 166, 'glass', 61.83, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(527, 166, 'metal', 55.02, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(528, 166, 'cardboard', 13.78, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(529, 166, 'organic', 48.07, 'kg', '2025-11-08 19:26:38', '2025-11-08 19:26:38'),
(530, 167, 'paper', 10.00, 'kg', '2025-11-08 20:50:26', '2025-11-08 20:50:26'),
(531, 167, 'metal', 5.00, 'kg', '2025-11-08 20:50:26', '2025-11-08 20:50:26');

-- --------------------------------------------------------

--
-- Table structure for table `recycling_targets`
--

CREATE TABLE `recycling_targets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `material_type` enum('plastic','paper','glass','metal','cardboard','organic','all') DEFAULT NULL,
  `target_weight` decimal(10,2) NOT NULL,
  `month` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycling_targets`
--

INSERT INTO `recycling_targets` (`id`, `material_type`, `target_weight`, `month`, `created_at`, `updated_at`) VALUES
(1, 'plastic', 744.00, '2025-11-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(2, 'paper', 1455.00, '2025-11-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(3, 'glass', 1260.00, '2025-11-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(4, 'metal', 648.00, '2025-11-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(5, 'cardboard', 1836.00, '2025-11-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(6, 'organic', 2040.00, '2025-11-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(7, 'all', 7983.00, '2025-11-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(8, 'plastic', 720.00, '2025-10-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(9, 'paper', 1350.00, '2025-10-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(10, 'glass', 1116.00, '2025-10-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(11, 'metal', 654.00, '2025-10-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(12, 'cardboard', 1926.00, '2025-10-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(13, 'organic', 1960.00, '2025-10-01', '2025-11-08 18:52:03', '2025-11-08 18:52:03'),
(14, 'all', 7726.00, '2025-10-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04'),
(15, 'plastic', 816.00, '2025-09-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04'),
(16, 'paper', 1500.00, '2025-09-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04'),
(17, 'glass', 1212.00, '2025-09-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04'),
(18, 'metal', 600.00, '2025-09-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04'),
(19, 'cardboard', 1764.00, '2025-09-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04'),
(20, 'organic', 2120.00, '2025-09-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04'),
(21, 'all', 8012.00, '2025-09-01', '2025-11-08 18:52:04', '2025-11-08 18:52:04');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_number` varchar(20) NOT NULL,
  `resident_id` bigint(20) UNSIGNED NOT NULL,
  `report_type` enum('missed_pickup','uncollected_waste','illegal_dumping','other') NOT NULL,
  `location` varchar(255) NOT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `route_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `reference_number`, `resident_id`, `report_type`, `location`, `zone`, `description`, `status`, `route_id`, `assigned_to`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 'REP-20251107-0001', 11, 'missed_pickup', '123 Main Street, Zone A', NULL, 'My scheduled pickup on Monday was missed. The truck did not come to my street.', 'pending', NULL, NULL, NULL, '2025-11-06 21:38:02', '2025-11-06 21:38:02'),
(2, 'REP-20251107-0002', 11, 'missed_pickup', '123 Main Street, Zone A', NULL, 'My scheduled pickup on Monday was missed. The truck did not come to my street.', 'pending', NULL, NULL, NULL, '2025-11-06 21:41:15', '2025-11-06 21:41:15'),
(3, 'REP-20251105-0001', 12, 'uncollected_waste', '456 Oak Avenue, Zone B', NULL, 'Several bags of waste were left behind after collection. The crew only took half of the waste.', 'in_progress', 8, 5, NULL, '2025-11-04 21:41:15', '2025-11-04 21:41:15'),
(4, 'REP-20251101-0001', 11, 'illegal_dumping', '789 Pine Road, Zone A', NULL, 'Someone dumped construction debris on the corner of Pine Road and 5th Street.', 'resolved', 8, NULL, '2025-11-02 12:41:15', '2025-10-31 21:41:15', '2025-10-31 21:41:15'),
(5, 'REP-20251025-0001', 13, 'missed_pickup', '321 Elm Street, Zone C', NULL, 'Missed pickup on Thursday. No truck came by.', 'closed', NULL, NULL, '2025-10-26 00:41:15', '2025-10-24 21:41:15', '2025-10-24 21:41:15'),
(6, 'REP-20251108-0001', 14, 'uncollected_waste', '555 Maple Drive, Zone D', NULL, 'Recycling bins were not emptied during last collection.', 'pending', NULL, NULL, NULL, '2025-11-07 21:41:15', '2025-11-07 21:41:15'),
(7, 'REP-20251103-0001', 12, 'other', '888 Cedar Lane, Zone B', NULL, 'Damaged waste bin needs replacement. The lid is broken and cannot close properly.', 'in_progress', NULL, NULL, NULL, '2025-11-02 21:41:16', '2025-11-02 21:41:16'),
(8, 'REP-20251018-0001', 13, 'illegal_dumping', '999 Birch Court, Zone C', NULL, 'Large furniture items dumped near the park entrance.', 'resolved', 6, NULL, '2025-10-19 09:41:16', '2025-10-17 21:41:16', '2025-10-17 21:41:16'),
(9, 'REP-20251106-0001', 14, 'missed_pickup', '147 Willow Way, Zone D', NULL, 'Scheduled collection did not occur. Bins are still full.', 'in_progress', 1, 6, NULL, '2025-11-05 21:41:16', '2025-11-05 21:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `report_photos`
--

CREATE TABLE `report_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_photos`
--

INSERT INTO `report_photos` (`id`, `report_id`, `file_path`, `file_name`, `file_size`, `uploaded_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'reports/1/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-11-06 21:38:02', '2025-11-06 21:38:02', '2025-11-06 21:38:02'),
(2, 1, 'reports/1/photo_2.jpg', 'missed_pickup_1.jpg', 1856432, '2025-11-06 21:40:02', '2025-11-06 21:40:02', '2025-11-06 21:40:02'),
(3, 2, 'reports/2/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-11-06 21:41:15', '2025-11-06 21:41:15', '2025-11-06 21:41:15'),
(4, 2, 'reports/2/photo_2.jpg', 'missed_pickup_1.jpg', 1856432, '2025-11-06 21:43:15', '2025-11-06 21:43:15', '2025-11-06 21:43:15'),
(5, 3, 'reports/3/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-11-04 21:41:15', '2025-11-04 21:41:15', '2025-11-04 21:41:15'),
(6, 3, 'reports/3/photo_2.jpg', 'missed_pickup_1.jpg', 1856432, '2025-11-04 21:43:15', '2025-11-04 21:43:15', '2025-11-04 21:43:15'),
(7, 3, 'reports/3/photo_3.jpg', 'illegal_dump_1.jpg', 3245678, '2025-11-04 21:45:15', '2025-11-04 21:45:15', '2025-11-04 21:45:15'),
(8, 4, 'reports/4/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-10-31 21:41:15', '2025-10-31 21:41:15', '2025-10-31 21:41:15'),
(9, 4, 'reports/4/photo_2.jpg', 'missed_pickup_1.jpg', 1856432, '2025-10-31 21:43:15', '2025-10-31 21:43:15', '2025-10-31 21:43:15'),
(10, 4, 'reports/4/photo_3.jpg', 'illegal_dump_1.jpg', 3245678, '2025-10-31 21:45:15', '2025-10-31 21:45:15', '2025-10-31 21:45:15'),
(11, 6, 'reports/6/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-11-07 21:41:15', '2025-11-07 21:41:15', '2025-11-07 21:41:15'),
(12, 7, 'reports/7/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-11-02 21:41:16', '2025-11-02 21:41:16', '2025-11-02 21:41:16'),
(13, 7, 'reports/7/photo_2.jpg', 'missed_pickup_1.jpg', 1856432, '2025-11-02 21:43:16', '2025-11-02 21:43:16', '2025-11-02 21:43:16'),
(14, 8, 'reports/8/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-10-17 21:41:16', '2025-10-17 21:41:16', '2025-10-17 21:41:16'),
(15, 8, 'reports/8/photo_2.jpg', 'missed_pickup_1.jpg', 1856432, '2025-10-17 21:43:16', '2025-10-17 21:43:16', '2025-10-17 21:43:16'),
(16, 9, 'reports/9/photo_1.jpg', 'waste_pile_1.jpg', 2458624, '2025-11-05 21:41:16', '2025-11-05 21:41:16', '2025-11-05 21:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `report_responses`
--

CREATE TABLE `report_responses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_responses`
--

INSERT INTO `report_responses` (`id`, `report_id`, `admin_id`, `response`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Thank you for reporting this issue. We are investigating and will update you soon.', '2025-11-05 18:41:15', '2025-11-05 09:41:15'),
(2, 3, 1, 'Our collection crew has been notified and will address this issue on their next route.', '2025-11-05 13:41:15', '2025-11-06 13:41:15'),
(3, 4, 1, 'Thank you for reporting this issue. We are investigating and will update you soon.', '2025-11-01 03:41:15', '2025-11-01 14:41:15'),
(4, 4, 1, 'Our collection crew has been notified and will address this issue on their next route.', '2025-11-01 15:41:15', '2025-11-01 11:41:15'),
(5, 5, 1, 'Thank you for reporting this issue. We are investigating and will update you soon.', '2025-10-25 19:41:15', '2025-10-25 11:41:15'),
(6, 7, 1, 'Thank you for reporting this issue. We are investigating and will update you soon.', '2025-11-03 12:41:16', '2025-11-03 19:41:16'),
(7, 7, 1, 'Our collection crew has been notified and will address this issue on their next route.', '2025-11-03 19:41:16', '2025-11-04 07:41:16'),
(8, 8, 1, 'Thank you for reporting this issue. We are investigating and will update you soon.', '2025-10-18 04:41:16', '2025-10-18 08:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `report_status_history`
--

CREATE TABLE `report_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` enum('pending','in_progress','resolved','closed') DEFAULT NULL,
  `new_status` enum('pending','in_progress','resolved','closed') NOT NULL,
  `changed_by` bigint(20) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_status_history`
--

INSERT INTO `report_status_history` (`id`, `report_id`, `old_status`, `new_status`, `changed_by`, `note`, `created_at`) VALUES
(1, 2, NULL, 'pending', 11, 'Report submitted by resident', '2025-11-06 21:41:15'),
(2, 3, NULL, 'pending', 12, 'Report submitted by resident', '2025-11-04 21:41:15'),
(3, 3, 'pending', 'in_progress', 1, 'Status updated by administrator', '2025-11-05 05:41:15'),
(4, 4, NULL, 'pending', 11, 'Report submitted by resident', '2025-10-31 21:41:15'),
(5, 4, 'pending', 'in_progress', 1, 'Report assigned to collection crew for investigation', '2025-11-01 01:41:15'),
(6, 4, 'in_progress', 'resolved', 1, 'Issue has been resolved. Collection completed.', '2025-11-02 05:41:15'),
(7, 5, NULL, 'pending', 13, 'Report submitted by resident', '2025-10-24 21:41:15'),
(8, 5, 'pending', 'in_progress', 1, 'Report assigned to collection crew for investigation', '2025-10-25 09:41:15'),
(9, 5, 'in_progress', 'resolved', 1, 'Issue has been resolved. Collection completed.', '2025-10-26 10:41:15'),
(10, 5, 'resolved', 'closed', 1, 'Report closed after verification', '2025-10-28 02:41:15'),
(11, 6, NULL, 'pending', 14, 'Report submitted by resident', '2025-11-07 21:41:15'),
(12, 7, NULL, 'pending', 12, 'Report submitted by resident', '2025-11-02 21:41:16'),
(13, 7, 'pending', 'in_progress', 1, 'Status updated by administrator', '2025-11-03 07:41:16'),
(14, 8, NULL, 'pending', 13, 'Report submitted by resident', '2025-10-17 21:41:16'),
(15, 8, 'pending', 'in_progress', 1, 'Report assigned to collection crew for investigation', '2025-10-18 01:41:16'),
(16, 8, 'in_progress', 'resolved', 1, 'Issue has been resolved. Collection completed.', '2025-10-19 16:41:16'),
(17, 9, NULL, 'pending', 14, 'Report submitted by resident', '2025-11-05 21:41:16'),
(18, 9, 'pending', 'in_progress', 1, 'Status updated by administrator', '2025-11-06 09:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'administrator', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(2, 'collection_crew', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(3, 'resident', 'web', '2025-11-06 22:55:36', '2025-11-06 22:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_logs`
--

CREATE TABLE `role_change_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `changed_by` bigint(20) UNSIGNED NOT NULL,
  `old_role` varchar(255) NOT NULL,
  `new_role` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_change_logs`
--

INSERT INTO `role_change_logs` (`id`, `user_id`, `changed_by`, `old_role`, `new_role`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 'collection_crew', 'collection_crew', '2025-11-07 19:29:49', '2025-11-07 19:29:49');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(7, 2),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(11, 2),
(11, 3),
(12, 1),
(13, 1),
(14, 1),
(14, 2),
(15, 1),
(15, 2),
(16, 1),
(16, 2),
(17, 1),
(17, 3),
(18, 1),
(18, 3),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 2),
(26, 3);

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `zone` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `name`, `zone`, `description`, `notes`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'North District Route A', 'ND-A', 'Covers residential areas in the northern district, including Maple Street and Oak Avenue.', 'Heavy traffic area - start early', 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(2, 'North District Route B', 'ND-B', 'Northern commercial district and apartment complexes.', 'Multiple large dumpsters', 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(3, 'South District Route A', 'SD-A', 'Southern residential neighborhoods including Pine Hills and Cedar Grove.', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(4, 'East District Route A', 'ED-A', 'Eastern industrial and commercial zones.', 'Requires large capacity truck', 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(5, 'East District Route B', 'ED-B', 'Eastern residential areas near the river.', NULL, 0, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(6, 'West District Route A', 'WD-A', 'Western suburbs and new developments.', 'Narrow streets - use smaller truck', 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(7, 'Central District Route A', 'CD-A', 'Downtown core and business district.', 'Early morning collection required', 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(8, 'Central District Route B', 'CD-B', 'Central residential areas and parks.', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(9, 'Rural Route A', 'RR-A', 'Rural areas and farmland on the outskirts.', 'Long distances between stops', 0, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(10, 'Special Events Route', 'SE-1', 'Temporary route for special events and festivals.', 'Only active during events', 0, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_reports`
--

CREATE TABLE `scheduled_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL,
  `metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metrics`)),
  `format` enum('pdf','csv') NOT NULL DEFAULT 'pdf',
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `next_generation_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `route_id` bigint(20) UNSIGNED NOT NULL,
  `collection_time` time NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `route_id`, `collection_time`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '07:00:00', '2025-11-01', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(2, 2, '08:00:00', '2025-11-01', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(3, 3, '09:00:00', '2025-11-01', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(4, 4, '06:30:00', '2025-11-01', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(5, 6, '08:30:00', '2025-11-01', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(6, 7, '05:30:00', '2025-11-01', NULL, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36', NULL),
(7, 5, '10:00:00', '2025-09-01', '2025-10-31', 0, '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(8, 10, '14:00:00', '2025-11-14', '2025-11-21', 1, '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schedule_days`
--

CREATE TABLE `schedule_days` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED NOT NULL,
  `day_of_week` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedule_days`
--

INSERT INTO `schedule_days` (`id`, `schedule_id`, `day_of_week`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(2, 1, 4, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(3, 2, 2, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(4, 2, 5, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(5, 3, 3, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(6, 4, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(7, 4, 3, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(8, 4, 5, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(9, 5, 2, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(10, 5, 4, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(11, 6, 1, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(12, 6, 2, '2025-11-06 22:55:36', '2025-11-06 22:55:36'),
(13, 6, 3, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(14, 6, 4, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(15, 6, 5, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(16, 7, 2, '2025-11-06 22:55:37', '2025-11-06 22:55:37'),
(17, 8, 6, '2025-11-06 22:55:37', '2025-11-06 22:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('QeTTLFIa7N49OWrh5veNAnLoqRewgw8WBWhFkQGP', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRmE0Y0cyNVdiT216WUlMZVpIeHdOeVVoTjZ4RldCVEhLSU9xRXVWeSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJsYW5kaW5nIjt9fQ==', 1762688685);

-- --------------------------------------------------------

--
-- Table structure for table `trucks`
--

CREATE TABLE `trucks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `truck_number` varchar(50) NOT NULL,
  `license_plate` varchar(50) NOT NULL,
  `capacity` decimal(8,2) NOT NULL,
  `operational_status` enum('operational','maintenance','out_of_service') NOT NULL DEFAULT 'operational',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trucks`
--

INSERT INTO `trucks` (`id`, `truck_number`, `license_plate`, `capacity`, `operational_status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'T-001', 'ABC-1234', 5.50, 'operational', 'Primary collection truck for Zone A', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(2, 'T-002', 'ABC-1235', 6.00, 'operational', 'Primary collection truck for Zone B', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(3, 'T-003', 'ABC-1236', 5.75, 'operational', 'Primary collection truck for Zone C', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(4, 'T-004', 'ABC-1237', 7.00, 'operational', 'Large capacity truck for commercial routes', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(5, 'T-005', 'ABC-1238', 5.25, 'operational', 'Backup truck for residential routes', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(6, 'T-006', 'ABC-1239', 6.50, 'operational', 'Recently serviced and ready for operation', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(7, 'T-007', 'ABC-1240', 5.00, 'maintenance', 'Scheduled maintenance - hydraulic system repair', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(8, 'T-008', 'ABC-1241', 6.25, 'maintenance', 'Routine maintenance - oil change and inspection', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(9, 'T-009', 'ABC-1242', 5.50, 'out_of_service', 'Major engine repair required - awaiting parts', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL),
(10, 'T-010', 'ABC-1243', 7.50, 'operational', 'Newest truck in fleet - high capacity', '2025-11-06 22:55:37', '2025-11-06 22:55:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `truck_status_history`
--

CREATE TABLE `truck_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `truck_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` enum('operational','maintenance','out_of_service') DEFAULT NULL,
  `new_status` enum('operational','maintenance','out_of_service') NOT NULL,
  `changed_by` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `truck_status_history`
--

INSERT INTO `truck_status_history` (`id`, `truck_id`, `old_status`, `new_status`, `changed_by`, `notes`, `created_at`) VALUES
(1, 7, NULL, 'operational', 1, 'Truck added to fleet', '2025-09-09 22:55:37'),
(2, 7, 'operational', 'maintenance', 1, 'Engine diagnostic check', '2025-11-04 22:55:37'),
(3, 8, NULL, 'operational', 1, 'Truck added to fleet', '2025-09-14 22:55:37'),
(4, 8, 'operational', 'maintenance', 1, 'Tire replacement required', '2025-11-02 22:55:37'),
(5, 9, NULL, 'operational', 1, 'Truck added to fleet', '2025-08-18 22:55:37'),
(6, 9, 'operational', 'maintenance', 1, 'Routine maintenance check revealed issues', '2025-10-16 22:55:37'),
(7, 9, 'maintenance', 'out_of_service', 1, 'Electrical system issues - awaiting specialist', '2025-10-27 22:55:37'),
(8, 3, NULL, 'operational', 1, 'Truck added to fleet', '2025-06-25 22:55:37'),
(9, 3, 'operational', 'maintenance', 1, 'Routine inspection and service', '2025-10-24 22:55:37'),
(10, 3, 'maintenance', 'operational', 1, 'Service completed - ready for operation', '2025-10-23 22:55:37'),
(11, 4, NULL, 'operational', 1, 'Truck added to fleet', '2025-05-21 22:55:37'),
(12, 4, 'operational', 'maintenance', 1, 'Scheduled maintenance', '2025-10-05 22:55:37'),
(13, 4, 'maintenance', 'operational', 1, 'Maintenance completed - all systems operational', '2025-10-05 22:55:37'),
(14, 4, 'operational', 'maintenance', 1, 'Routine inspection and service', '2025-10-25 22:55:37'),
(15, 4, 'maintenance', 'operational', 1, 'Service completed - ready for operation', '2025-10-27 22:55:37'),
(16, 6, NULL, 'operational', 1, 'Truck added to fleet', '2025-06-22 22:55:37'),
(17, 10, NULL, 'operational', 1, 'Truck added to fleet', '2025-06-18 22:55:37'),
(18, 10, 'operational', 'maintenance', 1, 'Scheduled maintenance', '2025-09-11 22:55:37'),
(19, 10, 'maintenance', 'operational', 1, 'Maintenance completed - all systems operational', '2025-09-24 22:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'System Administrator', 'admin@sweep.local', '2025-11-06 22:55:36', '$2y$12$HWclLrJDpKNByAV1wK3fCuK7KH7MrWiEgkL5/j.ATyUIFVt89otcK', NULL, '2025-11-06 22:55:36', '2025-11-07 00:16:47', NULL),
(2, 'John Smith', 'john.smith@sweep.local', '2025-11-06 22:55:37', '$2y$12$RQ2VcZvMSwhRj3fQswlkme6deBmC62C06n4yffFtgOHnITfwh1PA2', NULL, '2025-11-06 22:55:37', '2025-11-07 00:16:47', NULL),
(3, 'Maria Garcia', 'maria.garcia@sweep.local', '2025-11-06 22:55:37', '$2y$12$yaV.AiWQnPeTyL95Sz/Qu.UjKxu/nCI8Q.ZilqV6uSyaKh8QC03fq', NULL, '2025-11-06 22:55:37', '2025-11-07 00:16:48', NULL),
(4, 'David Johnson', 'david.johnson@sweep.local', '2025-11-06 22:55:38', '$2y$12$HI0.uP71hojChNcxky9jO.8eoPoquqOU0OuZKUHXyYm6sK39xn.5C', NULL, '2025-11-06 22:55:38', '2025-11-07 00:16:48', NULL),
(5, 'Sarah Williams', 'sarah.williams@sweep.local', '2025-11-06 22:55:38', '$2y$12$lawuR0EE5TSWv80vQH456ua.wijWSiSh6Lbx1DUNRnde9mZ8kbtiy', NULL, '2025-11-06 22:55:38', '2025-11-07 00:16:48', NULL),
(6, 'Michael Brown', 'michael.brown@sweep.local', '2025-11-06 22:55:38', '$2y$12$kvG8qcvFUNl45fB0y4dcSeQ02gCwt/ivYRZW5ukJxhc77LO9Xg.hS', NULL, '2025-11-06 22:55:38', '2025-11-07 00:16:48', NULL),
(7, 'Jennifer Davis', 'jennifer.davis@sweep.local', '2025-11-06 22:55:39', '$2y$12$4DwBNoyIKBYsgnGxbHsyTO0fxjpar84bjyHSOIfeMA8rWQPPuaooi', NULL, '2025-11-06 22:55:39', '2025-11-07 00:16:49', NULL),
(8, 'Robert Miller', 'robert.miller@sweep.local', '2025-11-06 22:55:39', '$2y$12$2WWIH5kHhj/MVqwTfyM3b.4zsMzEGIoyb4253pR2BP1qfWLH88hAi', NULL, '2025-11-06 22:55:39', '2025-11-07 00:16:49', NULL),
(9, 'Lisa Wilson', 'lisa.wilson@sweep.local', '2025-11-06 22:55:39', '$2y$12$qkWK67piY4vnbScBddkXsOl1Fr0Me/2Wucyb1Obae0ceUPnURTuDO', NULL, '2025-11-06 22:55:39', '2025-11-07 00:16:49', NULL),
(10, 'john resident', 'test.resident@sweep.local', NULL, '$2y$12$FVjTI2BupKd.xEQo8hm0x.P5c1kEmBizryjOc6efeXlaaHaJHxYE.', NULL, '2025-11-07 01:16:11', '2025-11-07 01:16:11', NULL),
(11, 'John Doe', 'john.doe@example.com', '2025-11-07 21:38:01', '$2y$12$TpJ8LRqirX/rZRbly/Ph5uBC0HPBJizGOLBucOTjuQLSwdfC3m0OW', NULL, '2025-11-07 21:38:01', '2025-11-07 21:38:01', NULL),
(12, 'Jane Smith', 'jane.smith@example.com', '2025-11-07 21:38:01', '$2y$12$0eCRZZUT1z1dAw3yQo42cO9ukmNKhYmbKlPu/BgbeUUArSoX3XUxm', NULL, '2025-11-07 21:38:01', '2025-11-07 21:38:01', NULL),
(13, 'Michael Johnson', 'michael.johnson@example.com', '2025-11-07 21:38:02', '$2y$12$lQgA0koFAx/IPrXqg3vsUevMC3aNIIe1vl3Gn4p6x6s9SqwRElqWe', NULL, '2025-11-07 21:38:02', '2025-11-07 21:38:02', NULL),
(14, 'Sarah Williams', 'sarah.williams@example.com', '2025-11-07 21:38:02', '$2y$12$XLmNgVVkUmXQRGCi2/Ib6OIU2nlyFLYIwq3FwFf6wIq4fTNDLkjIy', NULL, '2025-11-07 21:38:02', '2025-11-07 21:38:02', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notes`
--
ALTER TABLE `admin_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_notes_collection_log_id_index` (`collection_log_id`),
  ADD KEY `admin_notes_admin_id_index` (`admin_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_truck_assignment` (`truck_id`,`assignment_date`,`status`),
  ADD UNIQUE KEY `unique_user_assignment` (`user_id`,`assignment_date`,`status`),
  ADD KEY `assignments_assignment_date_index` (`assignment_date`),
  ADD KEY `assignments_truck_id_assignment_date_index` (`truck_id`,`assignment_date`),
  ADD KEY `assignments_user_id_assignment_date_index` (`user_id`,`assignment_date`),
  ADD KEY `assignments_route_id_assignment_date_index` (`route_id`,`assignment_date`),
  ADD KEY `idx_assignments_date_crew` (`assignment_date`,`user_id`),
  ADD KEY `idx_assignments_date_status` (`assignment_date`,`status`),
  ADD KEY `idx_assignments_route_date` (`route_id`,`assignment_date`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collections_collection_date_status_index` (`collection_date`,`status`),
  ADD KEY `collections_route_id_collection_date_index` (`route_id`,`collection_date`),
  ADD KEY `collections_assignment_id_index` (`assignment_id`);

--
-- Indexes for table `collection_logs`
--
ALTER TABLE `collection_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `collection_logs_assignment_id_unique` (`assignment_id`),
  ADD KEY `collection_logs_assignment_id_index` (`assignment_id`),
  ADD KEY `collection_logs_status_index` (`status`),
  ADD KEY `collection_logs_created_by_index` (`created_by`),
  ADD KEY `collection_logs_created_at_index` (`created_at`),
  ADD KEY `collection_logs_collection_id_index` (`collection_id`),
  ADD KEY `idx_collection_logs_created_at` (`created_at`),
  ADD KEY `idx_collection_logs_status_created` (`status`,`created_at`);

--
-- Indexes for table `collection_photos`
--
ALTER TABLE `collection_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collection_photos_collection_log_id_index` (`collection_log_id`);

--
-- Indexes for table `dashboard_preferences`
--
ALTER TABLE `dashboard_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dashboard_preferences_user_id_unique` (`user_id`),
  ADD KEY `dashboard_preferences_user_id_index` (`user_id`);

--
-- Indexes for table `dismissed_alerts`
--
ALTER TABLE `dismissed_alerts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dismissed_alerts_user_id_alert_category_alert_identifier_unique` (`user_id`,`alert_category`,`alert_identifier`),
  ADD KEY `dismissed_alerts_user_id_alert_category_index` (`user_id`,`alert_category`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `generated_reports_scheduled_report_id_index` (`scheduled_report_id`),
  ADD KEY `generated_reports_generated_at_index` (`generated_at`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `holidays_date_unique` (`date`),
  ADD KEY `holidays_date_index` (`date`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `recycling_logs`
--
ALTER TABLE `recycling_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recycling_logs_user_id_index` (`user_id`),
  ADD KEY `recycling_logs_collection_date_index` (`collection_date`),
  ADD KEY `recycling_logs_route_id_index` (`route_id`),
  ADD KEY `recycling_logs_assignment_id_index` (`assignment_id`),
  ADD KEY `idx_recycling_logs_created_at` (`created_at`),
  ADD KEY `idx_recycling_logs_assignment_created` (`assignment_id`,`created_at`);

--
-- Indexes for table `recycling_log_materials`
--
ALTER TABLE `recycling_log_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recycling_log_materials_recycling_log_id_index` (`recycling_log_id`),
  ADD KEY `recycling_log_materials_material_type_index` (`material_type`);

--
-- Indexes for table `recycling_targets`
--
ALTER TABLE `recycling_targets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recycling_targets_month_material_type_unique` (`month`,`material_type`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reports_reference_number_unique` (`reference_number`),
  ADD KEY `reports_reference_number_index` (`reference_number`),
  ADD KEY `reports_resident_id_index` (`resident_id`),
  ADD KEY `reports_status_index` (`status`),
  ADD KEY `reports_report_type_index` (`report_type`),
  ADD KEY `reports_created_at_index` (`created_at`),
  ADD KEY `reports_route_id_index` (`route_id`),
  ADD KEY `reports_assigned_to_index` (`assigned_to`),
  ADD KEY `reports_zone_index` (`zone`),
  ADD KEY `idx_reports_created_status` (`created_at`,`status`),
  ADD KEY `idx_reports_route_created` (`route_id`,`created_at`),
  ADD KEY `idx_reports_status_created` (`status`,`created_at`);

--
-- Indexes for table `report_photos`
--
ALTER TABLE `report_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_photos_report_id_index` (`report_id`);

--
-- Indexes for table `report_responses`
--
ALTER TABLE `report_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_responses_report_id_index` (`report_id`),
  ADD KEY `report_responses_admin_id_index` (`admin_id`);

--
-- Indexes for table `report_status_history`
--
ALTER TABLE `report_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_status_history_report_id_index` (`report_id`),
  ADD KEY `report_status_history_changed_by_index` (`changed_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_change_logs`
--
ALTER TABLE `role_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_change_logs_user_id_foreign` (`user_id`),
  ADD KEY `role_change_logs_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `routes_name_unique` (`name`),
  ADD KEY `routes_name_index` (`name`),
  ADD KEY `routes_zone_index` (`zone`),
  ADD KEY `idx_routes_is_active` (`is_active`);

--
-- Indexes for table `scheduled_reports`
--
ALTER TABLE `scheduled_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scheduled_reports_user_id_index` (`user_id`),
  ADD KEY `scheduled_reports_is_active_next_generation_at_index` (`is_active`,`next_generation_at`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedules_route_id_index` (`route_id`);

--
-- Indexes for table `schedule_days`
--
ALTER TABLE `schedule_days`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `schedule_days_schedule_id_day_of_week_unique` (`schedule_id`,`day_of_week`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `trucks`
--
ALTER TABLE `trucks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trucks_truck_number_unique` (`truck_number`),
  ADD KEY `trucks_truck_number_index` (`truck_number`),
  ADD KEY `idx_trucks_operational_status` (`operational_status`);

--
-- Indexes for table `truck_status_history`
--
ALTER TABLE `truck_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `truck_status_history_changed_by_foreign` (`changed_by`),
  ADD KEY `truck_status_history_truck_id_index` (`truck_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notes`
--
ALTER TABLE `admin_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collection_logs`
--
ALTER TABLE `collection_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collection_photos`
--
ALTER TABLE `collection_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dashboard_preferences`
--
ALTER TABLE `dashboard_preferences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dismissed_alerts`
--
ALTER TABLE `dismissed_alerts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `recycling_logs`
--
ALTER TABLE `recycling_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `recycling_log_materials`
--
ALTER TABLE `recycling_log_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=532;

--
-- AUTO_INCREMENT for table `recycling_targets`
--
ALTER TABLE `recycling_targets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `report_photos`
--
ALTER TABLE `report_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `report_responses`
--
ALTER TABLE `report_responses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `report_status_history`
--
ALTER TABLE `report_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role_change_logs`
--
ALTER TABLE `role_change_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `scheduled_reports`
--
ALTER TABLE `scheduled_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `schedule_days`
--
ALTER TABLE `schedule_days`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `trucks`
--
ALTER TABLE `trucks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `truck_status_history`
--
ALTER TABLE `truck_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_notes`
--
ALTER TABLE `admin_notes`
  ADD CONSTRAINT `admin_notes_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_notes_collection_log_id_foreign` FOREIGN KEY (`collection_log_id`) REFERENCES `collection_logs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_truck_id_foreign` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collections`
--
ALTER TABLE `collections`
  ADD CONSTRAINT `collections_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collections_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collection_logs`
--
ALTER TABLE `collection_logs`
  ADD CONSTRAINT `collection_logs_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_logs_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collection_photos`
--
ALTER TABLE `collection_photos`
  ADD CONSTRAINT `collection_photos_collection_log_id_foreign` FOREIGN KEY (`collection_log_id`) REFERENCES `collection_logs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dashboard_preferences`
--
ALTER TABLE `dashboard_preferences`
  ADD CONSTRAINT `dashboard_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dismissed_alerts`
--
ALTER TABLE `dismissed_alerts`
  ADD CONSTRAINT `dismissed_alerts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD CONSTRAINT `generated_reports_scheduled_report_id_foreign` FOREIGN KEY (`scheduled_report_id`) REFERENCES `scheduled_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recycling_logs`
--
ALTER TABLE `recycling_logs`
  ADD CONSTRAINT `recycling_logs_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recycling_logs_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recycling_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `recycling_log_materials`
--
ALTER TABLE `recycling_log_materials`
  ADD CONSTRAINT `recycling_log_materials_recycling_log_id_foreign` FOREIGN KEY (`recycling_log_id`) REFERENCES `recycling_logs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `report_photos`
--
ALTER TABLE `report_photos`
  ADD CONSTRAINT `report_photos_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_responses`
--
ALTER TABLE `report_responses`
  ADD CONSTRAINT `report_responses_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_responses_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_status_history`
--
ALTER TABLE `report_status_history`
  ADD CONSTRAINT `report_status_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_status_history_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_change_logs`
--
ALTER TABLE `role_change_logs`
  ADD CONSTRAINT `role_change_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_change_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scheduled_reports`
--
ALTER TABLE `scheduled_reports`
  ADD CONSTRAINT `scheduled_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_days`
--
ALTER TABLE `schedule_days`
  ADD CONSTRAINT `schedule_days_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `truck_status_history`
--
ALTER TABLE `truck_status_history`
  ADD CONSTRAINT `truck_status_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `truck_status_history_truck_id_foreign` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
