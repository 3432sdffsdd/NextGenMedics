-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: sdb-70.hosting.stackcp.net
-- Generation Time: Jul 14, 2026 at 04:13 AM
-- Server version: 10.11.18-MariaDB-log
-- PHP Version: 8.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nextgenmedics-3530353392cf`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:28:55'),
(2, 4, 'create_user', 'user', 5, 'Created teacher: talhanazeer3@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:29:40'),
(3, 4, 'create_user', 'user', 6, 'Created teacher: sskhan.pk@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:30:03'),
(4, 4, 'create_user', 'user', 7, 'Created student: mobarra.asim25@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:32:02'),
(5, 4, 'create_user', 'user', 8, 'Created student: neelofar.iqbal.ni@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:32:37'),
(6, 4, 'create_user', 'user', 9, 'Created student: azwazubair@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:33:09'),
(7, 4, 'create_user', 'user', 10, 'Created student: Nainakhan521@gmail.Com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:34:06'),
(8, 4, 'create_user', 'user', 11, 'Created student: sahrish13062000@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:34:39'),
(9, 4, 'create_user', 'user', 12, 'Created student: ali.imran.khan95@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:35:18'),
(10, 4, 'create_user', 'user', 13, 'Created student: hafizaasmakanwal@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:35:47'),
(11, 4, 'create_user', 'user', 14, 'Created student: sidrahabdulhafeez2000@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:36:18'),
(12, 4, 'create_user', 'user', 15, 'Created student: palwashasaleem1999@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:36:55'),
(13, 4, 'create_user', 'user', 16, 'Created student: mahnoorasif1100@gmail.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:37:23'),
(14, 4, 'create_user', 'user', 17, 'Created student: sarah.aslam21@yahoo.com', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:37:59'),
(15, 12, 'login', 'user', 12, 'User logged in', '154.198.96.232', '2026-07-02 12:40:54'),
(16, 4, 'update_course', 'course', 1, 'Course updated', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:41:10'),
(17, 4, 'reset_password', 'user', 6, 'Password reset by admin', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:42:10'),
(18, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:43:50'),
(19, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:46:11'),
(20, 17, 'login', 'user', 17, 'User logged in', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:46:33'),
(21, 4, 'reset_password', 'user', 6, 'Password reset by admin', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 12:59:18'),
(22, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 13:00:07'),
(23, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 13:06:25'),
(24, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 13:06:45'),
(25, 17, 'login', 'user', 17, 'User logged in', '2400:adc1:134:bc00:b8c5:b8d3:4451:151e', '2026-07-02 13:07:12'),
(26, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:f931:d1a:84bb:262b', '2026-07-02 17:38:42'),
(27, 4, 'reset_password', 'user', 6, 'Password reset by admin', '2400:adc1:134:bc00:f931:d1a:84bb:262b', '2026-07-02 17:39:38'),
(28, 4, 'reset_password', 'user', 5, 'Password reset by admin', '2400:adc1:134:bc00:f931:d1a:84bb:262b', '2026-07-02 17:40:13'),
(29, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:f931:d1a:84bb:262b', '2026-07-02 17:41:09'),
(30, 9, 'login', 'user', 9, 'User logged in', '58.65.198.104', '2026-07-03 02:54:53'),
(31, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-03 04:33:30'),
(32, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-03 04:44:58'),
(33, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-03 05:44:21'),
(34, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-03 05:59:56'),
(35, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-03 06:07:35'),
(36, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-03 06:09:07'),
(37, 17, 'login', 'user', 17, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-03 06:26:29'),
(38, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-03 06:33:20'),
(39, 9, 'login', 'user', 9, 'User logged in', '58.65.198.104', '2026-07-03 06:38:45'),
(40, 12, 'login', 'user', 12, 'User logged in', '154.198.107.232', '2026-07-03 06:45:21'),
(41, 7, 'login', 'user', 7, 'User logged in', '118.103.230.36', '2026-07-03 18:42:25'),
(42, 16, 'login', 'user', 16, 'User logged in', '2402:ad80:11b:dd65:1:0:d47e:5204', '2026-07-03 20:38:43'),
(43, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 05:38:03'),
(44, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 05:40:19'),
(45, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 05:40:56'),
(46, 17, 'login', 'user', 17, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 05:41:14'),
(47, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 06:55:23'),
(48, 4, 'reset_password', 'user', 5, 'Password reset by admin', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 06:55:33'),
(49, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 10:01:49'),
(50, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:bd5d:a2c4:2425:7c4', '2026-07-04 12:47:14'),
(51, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:1ab:ab00:550d:1f:6c8e:d34', '2026-07-04 13:17:52'),
(52, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 19:08:37'),
(53, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 19:14:03'),
(54, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 19:16:28'),
(55, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 19:46:31'),
(56, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 19:50:27'),
(57, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 20:07:23'),
(58, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 20:09:55'),
(59, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 20:10:16'),
(60, 17, 'login', 'user', 17, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-04 20:10:57'),
(61, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-05 03:43:30'),
(62, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-05 03:57:51'),
(63, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:1ab:ab00:6ca0:a7b8:eec9:7e77', '2026-07-05 04:02:08'),
(64, 12, 'login', 'user', 12, 'User logged in', '202.47.36.224', '2026-07-05 06:41:07'),
(65, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:134:bc00:cc9e:9264:869d:ffd', '2026-07-05 07:39:33'),
(66, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:134:bc00:cc9e:9264:869d:ffd', '2026-07-05 08:14:05'),
(67, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:134:bc00:cc9e:9264:869d:ffd', '2026-07-05 08:28:15'),
(68, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:134:bc00:cc9e:9264:869d:ffd', '2026-07-05 08:45:57'),
(69, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:db0:4d3:2c90:483e', '2026-07-05 14:58:08'),
(70, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:db0:4d3:2c90:483e', '2026-07-05 15:40:23'),
(71, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:134:bc00:db0:4d3:2c90:483e', '2026-07-05 15:40:31'),
(72, 17, 'login', 'user', 17, 'User logged in', '2400:adc1:134:bc00:db0:4d3:2c90:483e', '2026-07-05 15:41:20'),
(73, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:db0:4d3:2c90:483e', '2026-07-05 15:58:33'),
(74, 4, 'reset_password', 'user', 17, 'Password reset by admin', '2400:adc1:134:bc00:db0:4d3:2c90:483e', '2026-07-05 15:58:40'),
(75, 7, 'login', 'user', 7, 'User logged in', '118.103.230.36', '2026-07-05 16:17:03'),
(76, 17, 'login', 'user', 17, 'User logged in', '2400:adc1:134:bc00:db0:4d3:2c90:483e', '2026-07-05 16:18:33'),
(77, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-06 06:09:43'),
(78, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-06 06:16:32'),
(79, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-06 07:18:29'),
(80, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-06 07:38:59'),
(81, 4, 'login', 'user', 4, 'User logged in', '175.107.39.7', '2026-07-06 07:59:53'),
(82, 6, 'login', 'user', 6, 'User logged in', '175.107.39.7', '2026-07-06 08:04:12'),
(83, 4, 'reset_password', 'user', 13, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:05:40'),
(84, 4, 'reset_password', 'user', 16, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:08:13'),
(85, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-06 08:08:22'),
(86, 4, 'reset_password', 'user', 7, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:09:23'),
(87, 4, 'reset_password', 'user', 17, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:11:31'),
(88, 4, 'reset_password', 'user', 15, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:12:23'),
(89, 4, 'reset_password', 'user', 14, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:14:08'),
(90, 4, 'reset_password', 'user', 12, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:15:44'),
(91, 4, 'reset_password', 'user', 8, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:17:33'),
(92, 4, 'reset_password', 'user', 9, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:17:57'),
(93, 4, 'reset_password', 'user', 10, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:18:27'),
(94, 4, 'reset_password', 'user', 11, 'Password reset by admin', '175.107.39.7', '2026-07-06 08:19:06'),
(95, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-06 08:20:02'),
(96, 9, 'login', 'user', 9, 'User logged in', '58.65.198.104', '2026-07-06 10:04:33'),
(97, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-06 10:13:38'),
(98, 4, 'login', 'user', 4, 'User logged in', '2404:3100:1477:d4d8:7818:12:7bd5:eede', '2026-07-06 10:19:07'),
(99, 6, 'login', 'user', 6, 'User logged in', '2404:3100:1477:d4d8:7818:12:7bd5:eede', '2026-07-06 10:20:23'),
(100, 8, 'login', 'user', 8, 'User logged in', '2400:adc1:44e:d600:6d14:76f5:9452:27a2', '2026-07-06 10:29:01'),
(101, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-06 10:54:28'),
(102, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-06 10:54:28'),
(103, 9, 'login', 'user', 9, 'User logged in', '2400:adc1:134:bc00:4fec:2e31:58a4:6dbc', '2026-07-06 11:06:09'),
(104, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:4fec:2e31:58a4:6dbc', '2026-07-06 11:07:04'),
(105, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:4fec:2e31:58a4:6dbc', '2026-07-06 12:13:28'),
(106, 13, 'login', 'user', 13, 'User logged in', '2400:adc1:134:bc00:4fec:2e31:58a4:6dbc', '2026-07-06 12:15:55'),
(107, 12, 'login', 'user', 12, 'User logged in', '202.47.36.224', '2026-07-06 12:18:17'),
(108, 6, 'login', 'user', 6, 'User logged in', '111.92.151.98', '2026-07-06 12:27:29'),
(109, 4, 'login', 'user', 4, 'User logged in', '111.92.151.98', '2026-07-06 12:35:58'),
(110, 4, 'reset_password', 'user', 3, 'Password reset by admin', '111.92.151.98', '2026-07-06 12:36:37'),
(111, 4, 'login', 'user', 4, 'User logged in', '111.92.151.98', '2026-07-06 12:37:42'),
(112, 3, 'login', 'user', 3, 'User logged in', '111.92.151.98', '2026-07-06 12:38:12'),
(113, 7, 'login', 'user', 7, 'User logged in', '118.103.230.36', '2026-07-06 15:49:49'),
(114, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 16:00:31'),
(115, 4, 'reset_password', 'user', 3, 'Password reset by admin', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 16:00:51'),
(116, 3, 'login', 'user', 3, 'User logged in', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 16:01:32'),
(117, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 18:37:15'),
(118, 4, 'reset_password', 'user', 3, 'Password reset by admin', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 18:37:41'),
(119, 3, 'login', 'user', 3, 'User logged in', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 18:38:39'),
(120, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 18:42:54'),
(121, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 18:44:56'),
(122, 4, 'reset_password', 'user', 3, 'Password reset by admin', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 18:45:19'),
(123, 3, 'login', 'user', 3, 'User logged in', '2400:adc1:134:bc00:d966:1f3f:ff41:94e9', '2026-07-06 18:45:54'),
(124, 17, 'login', 'user', 17, 'User logged in', '2407:aa80:15:cb09:74e5:747f:c1cf:59c6', '2026-07-06 19:34:48'),
(125, 3, 'login', 'user', 3, 'User logged in', '175.107.36.8', '2026-07-07 04:37:07'),
(126, 3, 'login', 'user', 3, 'User logged in', '175.107.37.15', '2026-07-07 05:25:18'),
(127, 6, 'login', 'user', 6, 'User logged in', '175.107.37.15', '2026-07-07 05:26:49'),
(128, 6, 'login', 'user', 6, 'User logged in', '175.107.37.15', '2026-07-07 06:10:21'),
(129, 9, 'login', 'user', 9, 'User logged in', '58.65.198.104', '2026-07-07 06:39:10'),
(130, 6, 'login', 'user', 6, 'User logged in', '175.107.37.15', '2026-07-07 06:46:49'),
(131, 6, 'login', 'user', 6, 'User logged in', '175.107.36.129', '2026-07-07 07:46:41'),
(132, 6, 'login', 'user', 6, 'User logged in', '175.107.37.36', '2026-07-07 08:56:04'),
(133, 3, 'login', 'user', 3, 'User logged in', '175.107.37.36', '2026-07-07 08:59:08'),
(134, 6, 'login', 'user', 6, 'User logged in', '175.107.37.36', '2026-07-07 09:14:45'),
(135, 6, 'login', 'user', 6, 'User logged in', '175.107.37.36', '2026-07-07 09:31:31'),
(136, 7, 'login', 'user', 7, 'User logged in', '2401:ba80:a350:32ee:f98c:5230:69a3:31d6', '2026-07-07 09:46:59'),
(137, 16, 'login', 'user', 16, 'User logged in', '223.123.108.103', '2026-07-07 10:10:01'),
(138, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:cd40:a13c:7604:5c8e', '2026-07-07 10:50:13'),
(139, 12, 'login', 'user', 12, 'User logged in', '202.47.36.224', '2026-07-07 11:00:18'),
(140, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:cd40:a13c:7604:5c8e', '2026-07-07 11:28:01'),
(141, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:cd40:a13c:7604:5c8e', '2026-07-07 11:54:38'),
(142, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:cd40:a13c:7604:5c8e', '2026-07-07 12:28:18'),
(143, 16, 'login', 'user', 16, 'User logged in', '2407:aa80:15:fa0e:a85d:8dcb:6f86:d1d7', '2026-07-07 13:05:11'),
(144, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-07 19:59:55'),
(145, 8, 'login', 'user', 8, 'User logged in', '2400:adc1:44e:d600:11a9:2881:5691:d1de', '2026-07-08 02:57:14'),
(146, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-08 05:09:40'),
(147, 4, 'login', 'user', 4, 'User logged in', '175.107.38.48', '2026-07-08 05:46:56'),
(148, 3, 'login', 'user', 3, 'User logged in', '175.107.37.96', '2026-07-08 06:28:41'),
(149, 6, 'login', 'user', 6, 'User logged in', '175.107.37.96', '2026-07-08 06:29:36'),
(150, 12, 'login', 'user', 12, 'User logged in', '175.107.37.96', '2026-07-08 06:57:32'),
(151, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-08 07:36:15'),
(152, 6, 'login', 'user', 6, 'User logged in', '175.107.38.1', '2026-07-08 07:39:14'),
(153, 12, 'login', 'user', 12, 'User logged in', '175.107.38.1', '2026-07-08 07:41:53'),
(154, 6, 'login', 'user', 6, 'User logged in', '175.107.38.178', '2026-07-08 08:06:04'),
(155, 13, 'login', 'user', 13, 'User logged in', '61.5.128.74', '2026-07-08 08:08:40'),
(156, 13, 'login', 'user', 13, 'User logged in', '61.5.128.66', '2026-07-08 09:44:58'),
(157, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-08 10:17:31'),
(158, 13, 'login', 'user', 13, 'User logged in', '61.5.128.66', '2026-07-08 10:48:04'),
(159, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-08 11:21:52'),
(160, 6, 'login', 'user', 6, 'User logged in', '111.92.151.98', '2026-07-08 11:22:25'),
(161, 5, 'login', 'user', 5, 'User logged in', '111.92.151.98', '2026-07-08 11:31:35'),
(162, 13, 'login', 'user', 13, 'User logged in', '111.92.151.98', '2026-07-08 11:43:15'),
(163, 5, 'login', 'user', 5, 'User logged in', '2400:adc1:134:bc00:da9:6fec:5850:109d', '2026-07-08 12:23:29'),
(164, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-08 12:30:06'),
(165, 5, 'login', 'user', 5, 'User logged in', '2401:ba80:a383:1f33:786c:f2ff:febd:e3a7', '2026-07-08 14:28:52'),
(166, 12, 'login', 'user', 12, 'User logged in', '202.47.36.224', '2026-07-08 15:19:35'),
(167, 6, 'login', 'user', 6, 'User logged in', '175.107.38.177', '2026-07-09 04:47:51'),
(168, 6, 'login', 'user', 6, 'User logged in', '175.107.37.139', '2026-07-09 05:05:04'),
(169, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-09 05:49:12'),
(170, 6, 'login', 'user', 6, 'User logged in', '175.107.37.139', '2026-07-09 06:08:02'),
(171, 6, 'login', 'user', 6, 'User logged in', '175.107.37.139', '2026-07-09 06:08:35'),
(172, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-09 06:15:25'),
(173, 4, 'login', 'user', 4, 'User logged in', '175.107.37.139', '2026-07-09 06:51:51'),
(174, 5, 'login', 'user', 5, 'User logged in', '175.107.37.139', '2026-07-09 06:53:45'),
(175, 4, 'login', 'user', 4, 'User logged in', '175.107.37.139', '2026-07-09 07:01:05'),
(176, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-09 07:22:53'),
(177, 6, 'login', 'user', 6, 'User logged in', '175.107.37.139', '2026-07-09 07:25:28'),
(178, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-09 07:28:30'),
(179, 4, 'login', 'user', 4, 'User logged in', '175.107.37.139', '2026-07-09 07:45:40'),
(180, 4, 'create_user', 'user', 18, 'Created student: Sumayyahsiddiquii@gmail.com', '175.107.37.139', '2026-07-09 07:46:16'),
(181, 4, 'reset_password', 'user', 18, 'Password reset by admin', '175.107.37.139', '2026-07-09 07:46:22'),
(182, 18, 'login', 'user', 18, 'User logged in', '175.107.37.139', '2026-07-09 07:46:57'),
(183, 4, 'login', 'user', 4, 'User logged in', '175.107.37.139', '2026-07-09 07:47:12'),
(184, 18, 'login', 'user', 18, 'User logged in', '175.107.37.139', '2026-07-09 07:48:00'),
(185, 18, 'login', 'user', 18, 'User logged in', '61.5.128.66', '2026-07-09 07:51:29'),
(186, 13, 'login', 'user', 13, 'User logged in', '61.5.128.74', '2026-07-09 07:57:50'),
(187, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-09 07:58:51'),
(188, 4, 'login', 'user', 4, 'User logged in', '175.107.37.139', '2026-07-09 08:24:59'),
(189, 4, 'create_user', 'user', 19, 'Created student: teststudent@abc.com', '175.107.37.139', '2026-07-09 08:25:27'),
(190, 6, 'login', 'user', 6, 'User logged in', '175.107.37.139', '2026-07-09 08:27:30'),
(191, 19, 'login', 'user', 19, 'User logged in', '61.5.128.74', '2026-07-09 09:28:43'),
(192, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-09 09:34:28'),
(193, 19, 'login', 'user', 19, 'User logged in', '61.5.128.74', '2026-07-09 09:59:47'),
(194, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:f97f:78da:9e01:565f', '2026-07-09 10:04:06'),
(195, 4, 'create_user', 'user', 20, 'Created student: mahnoor58784@gmail.com', '2400:adc1:134:bc00:f97f:78da:9e01:565f', '2026-07-09 10:04:41'),
(196, 4, 'reset_password', 'user', 20, 'Password reset by admin', '2400:adc1:134:bc00:f97f:78da:9e01:565f', '2026-07-09 10:04:47'),
(197, 19, 'login', 'user', 19, 'User logged in', '2400:adc1:134:bc00:f97f:78da:9e01:565f', '2026-07-09 10:06:33'),
(198, 19, 'login', 'user', 19, 'User logged in', '2400:adc1:134:bc00:f97f:78da:9e01:565f', '2026-07-09 10:11:36'),
(199, 20, 'login', 'user', 20, 'User logged in', '103.53.45.55', '2026-07-09 10:15:05'),
(200, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-09 10:56:36'),
(201, 19, 'login', 'user', 19, 'User logged in', '61.5.128.74', '2026-07-09 11:02:31'),
(202, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:f97f:78da:9e01:565f', '2026-07-09 13:03:02'),
(203, 4, 'create_user', 'user', 21, 'Created student: student1@test.com', '2400:adc1:134:bc00:f97f:78da:9e01:565f', '2026-07-09 13:03:57'),
(204, 19, 'login', 'user', 19, 'User logged in', '61.5.128.74', '2026-07-09 13:24:09'),
(205, 4, 'login', 'user', 4, 'User logged in', '2401:ba80:a391:7c0d:e84e:c1ff:fed5:66c5', '2026-07-09 15:02:57'),
(206, 7, 'login', 'user', 7, 'User logged in', '2401:ba80:ac03:2521:ec16:7331:5523:8a57', '2026-07-09 16:17:30'),
(207, 19, 'login', 'user', 19, 'User logged in', '202.47.34.26', '2026-07-09 18:39:50'),
(208, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-09 20:15:24'),
(209, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-10 04:40:01'),
(210, 21, 'login', 'user', 21, 'User logged in', '175.107.36.48', '2026-07-10 05:11:39'),
(211, 19, 'login', 'user', 19, 'User logged in', '61.5.128.74', '2026-07-10 07:37:42'),
(212, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-10 11:54:23'),
(213, 19, 'login', 'user', 19, 'User logged in', '61.5.128.74', '2026-07-10 14:06:22'),
(214, 4, 'login', 'user', 4, 'User logged in', '2401:ba80:a349:1b25:709b:34ff:feba:d763', '2026-07-10 16:04:52'),
(215, 9, 'login', 'user', 9, 'User logged in', '2401:ba80:a349:1b25:709b:34ff:feba:d763', '2026-07-10 16:05:43'),
(216, 11, 'login', 'user', 11, 'User logged in', '2401:ba80:a10d:50c3:678b:4d0b:5053:fb67', '2026-07-10 16:18:08'),
(217, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-10 18:24:35'),
(218, 9, 'login', 'user', 9, 'User logged in', '58.65.198.104', '2026-07-11 01:34:14'),
(219, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:c402:8cad:2b88:ab94', '2026-07-11 03:23:45'),
(220, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-11 03:51:14'),
(221, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-11 05:01:06'),
(222, 19, 'login', 'user', 19, 'User logged in', '61.5.128.74', '2026-07-11 05:41:44'),
(223, 11, 'login', 'user', 11, 'User logged in', '2401:ba80:a34e:1f1a:16e4:2421:d50b:2593', '2026-07-11 05:44:11'),
(224, 12, 'login', 'user', 12, 'User logged in', '154.198.97.48', '2026-07-11 06:00:37'),
(225, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-11 07:13:31'),
(226, 9, 'login', 'user', 9, 'User logged in', '58.65.198.104', '2026-07-11 07:52:48'),
(227, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:c402:8cad:2b88:ab94', '2026-07-11 08:24:24'),
(228, 20, 'login', 'user', 20, 'User logged in', '2400:adc1:134:bc00:c402:8cad:2b88:ab94', '2026-07-11 08:25:28'),
(229, 18, 'login', 'user', 18, 'User logged in', '111.92.143.60', '2026-07-11 09:34:43'),
(230, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-11 09:45:30'),
(231, 18, 'login', 'user', 18, 'User logged in', '111.92.143.60', '2026-07-11 11:15:32'),
(232, 12, 'login', 'user', 12, 'User logged in', '202.47.36.224', '2026-07-11 11:44:46'),
(233, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:c402:8cad:2b88:ab94', '2026-07-11 12:24:48'),
(234, 3, 'login', 'user', 3, 'User logged in', '2400:adc1:134:bc00:7b39:cce2:f5b0:73f8', '2026-07-11 12:32:04'),
(235, 6, 'login', 'user', 6, 'User logged in', '2400:adc1:134:bc00:c402:8cad:2b88:ab94', '2026-07-11 17:46:04'),
(236, 8, 'login', 'user', 8, 'User logged in', '39.34.186.241', '2026-07-11 19:33:33'),
(237, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-11 20:31:11'),
(238, 8, 'login', 'user', 8, 'User logged in', '39.34.186.241', '2026-07-11 20:37:54'),
(239, 18, 'login', 'user', 18, 'User logged in', '111.92.142.24', '2026-07-12 11:46:48'),
(240, 12, 'login', 'user', 12, 'User logged in', '202.47.36.224', '2026-07-12 16:13:14'),
(241, 18, 'login', 'user', 18, 'User logged in', '2401:ba80:a35f:dfa2:18c1:a9ac:4516:1da7', '2026-07-12 22:26:15'),
(242, 12, 'login', 'user', 12, 'User logged in', '149.40.195.48', '2026-07-13 04:53:21'),
(243, 12, 'login', 'user', 12, 'User logged in', '149.40.195.48', '2026-07-13 06:22:18'),
(244, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-13 06:39:48'),
(245, 12, 'login', 'user', 12, 'User logged in', '149.40.195.48', '2026-07-13 07:01:25'),
(246, 6, 'login', 'user', 6, 'User logged in', '61.5.128.66', '2026-07-13 07:14:54'),
(247, 18, 'login', 'user', 18, 'User logged in', '2401:ba80:a399:2d28:18c1:c7c5:57d:2e04', '2026-07-13 07:24:04'),
(248, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-13 08:04:25'),
(249, 16, 'login', 'user', 16, 'User logged in', '2402:ad80:80:a21d:1:0:88a0:63ac', '2026-07-13 08:21:53'),
(250, 7, 'login', 'user', 7, 'User logged in', '118.103.231.221', '2026-07-13 09:19:25'),
(251, 4, 'login', 'user', 4, 'User logged in', '175.107.39.22', '2026-07-13 09:26:59'),
(252, 4, 'login', 'user', 4, 'User logged in', '175.107.39.22', '2026-07-13 09:27:42'),
(253, 4, 'reset_password', 'user', 21, 'Password reset by admin', '175.107.39.22', '2026-07-13 09:28:07'),
(254, 21, 'login', 'user', 21, 'User logged in', '175.107.39.22', '2026-07-13 09:28:28'),
(255, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-13 09:37:35'),
(256, 5, 'login', 'user', 5, 'User logged in', '61.5.128.74', '2026-07-13 10:38:25'),
(257, 20, 'login', 'user', 20, 'User logged in', '103.53.45.55', '2026-07-13 13:12:22'),
(258, 12, 'login', 'user', 12, 'User logged in', '202.47.36.224', '2026-07-13 13:42:30'),
(259, 16, 'login', 'user', 16, 'User logged in', '2402:ad80:128:e987:1:0:70c:ffb7', '2026-07-13 15:07:03'),
(260, 16, 'login', 'user', 16, 'User logged in', '2402:ad80:128:e987:1:0:70c:ffb7', '2026-07-13 15:10:14'),
(261, 16, 'login', 'user', 16, 'User logged in', '2402:ad80:128:e987:1:0:70c:ffb7', '2026-07-13 15:11:39'),
(262, 16, 'login', 'user', 16, 'User logged in', '2402:ad80:128:e987:1:0:70c:ffb7', '2026-07-13 16:24:58'),
(263, 10, 'login', 'user', 10, 'User logged in', '202.47.41.48', '2026-07-13 18:12:30'),
(264, 10, 'login', 'user', 10, 'User logged in', '2400:adc1:134:bc00:4aec:db08:ab52:c840', '2026-07-13 18:19:22'),
(265, 10, 'login', 'user', 10, 'User logged in', '202.47.41.48', '2026-07-13 18:21:06'),
(266, 9, 'login', 'user', 9, 'User logged in', '58.65.198.104', '2026-07-14 02:55:02'),
(267, 4, 'login', 'user', 4, 'User logged in', '2400:adc1:134:bc00:21c2:499a:bf33:6917', '2026-07-14 03:06:54'),
(268, 4, 'create_user', 'user', 22, 'Created student: rameen.shaikh19@gmail.com', '2400:adc1:134:bc00:21c2:499a:bf33:6917', '2026-07-14 03:08:14');

-- --------------------------------------------------------

--
-- Table structure for table `ai_generation_jobs`
--

CREATE TABLE `ai_generation_jobs` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `requested_by` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `current_step` enum('extract','summary','notes','flashcards','mcqs','done') NOT NULL DEFAULT 'extract',
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `source_text` longtext DEFAULT NULL,
  `source_chars` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `flashcard_target` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `flashcard_done` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `mcq_target` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `mcq_done` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_lecture_content`
--

CREATE TABLE `ai_lecture_content` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `summary` longtext DEFAULT NULL,
  `revision_notes` longtext DEFAULT NULL,
  `high_yield_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`high_yield_points`)),
  `clinical_pearls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`clinical_pearls`)),
  `common_mistakes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`common_mistakes`)),
  `key_definitions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`key_definitions`)),
  `memory_tricks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`memory_tricks`)),
  `key_takeaways` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`key_takeaways`)),
  `status` enum('draft','approved','published') NOT NULL DEFAULT 'draft',
  `generated_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ai_lecture_content`
--

INSERT INTO `ai_lecture_content` (`id`, `lecture_id`, `course_id`, `summary`, `revision_notes`, `high_yield_points`, `clinical_pearls`, `common_mistakes`, `key_definitions`, `memory_tricks`, `key_takeaways`, `status`, `generated_by`, `approved_by`, `approved_at`, `published_at`, `created_at`, `updated_at`) VALUES
(5, 16, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', 5, 5, '2026-07-09 08:12:41', '2026-07-09 07:59:40', '2026-07-09 07:53:14', '2026-07-09 08:12:41'),
(6, 22, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'published', 5, 5, '2026-07-09 08:05:35', '2026-07-09 08:10:13', '2026-07-09 08:05:29', '2026-07-09 08:10:13'),
(7, 23, 1, 'Membranous Nephropathy (MN). MCD is the \"minimal\" disease, serving as the primary cause of nephrotic syndrome in children; it involves a T-cell mediated loss of the glomerular negative charge barrier, which results in massive albuminuria despite normal findings on light microscopy and immunofluorescence. In contrast, FSGS is the leading cause in African American adults—often linked to the APOL1 gene—and is characterized by a decrease in the actual number of podocytes leading to segmental scarring. This condition is notoriously steroid-resistant and associated with a \"triad\" of high-yield stressors: HIV (particularly the aggressive collapsing variant), Sickle Cell Disease, and heroin use. Membranous Nephropathy, most common in white adults, is an autoimmune or secondary process where immune complexes deposit in the subepithelial space, creating a pathognomonic \"spike and dome\" appearance. MN is uniquely associated with Hepatitis B, solid organ malignancies, and SLE, and it carries the highest clinical risk for renal vein thrombosis, a complication marked by sudden flank pain and an acute rise in creatinine.\n\nComplementing these primary podocytopathies is Renal Amyloidosis, an infiltrative disorder where misfolded proteins aggregate into insoluble (\\beta)-pleated sheet fibrils. This \"depositional nephropathy\" occurs as AA amyloidosis (secondary to chronic inflammation like Rheumatoid Arthritis) or AL amyloidosis (secondary to plasma cell dyscrasias like Multiple Myeloma), and it is one of the few causes of renal failure where kidneys appear enlarged or normal-sized on ultrasound. The gold standard for diagnosis is a renal biopsy showing apple-green birefringence under polarized light with Congo Red stain. Management protocols differentiate strictly by age and clinical context: children with classic signs of MCD typically receive empiric steroids without an initial biopsy, whereas adults require a biopsy to distinguish between these pathologies. Treatment focuses on controlling proteinuria with ACE inhibitors or ARBs and addressing the underlying systemic triggers—such as treating HIV with antiretrovirals or employing the Modified Ponticelli Regimen (steroids and cyclophosphamide) for primary Membranous Nephropathy.', 'The Filter’s Failure: Decoding the Hidden Logic of \"Leaky Kidneys\" and Nephrotic Syndromes\n\n\n\n1. Introduction: The Mystery of the Frothy Urine\n\nIt often begins with a subtle observation: urine that appears unusually \"foamy\" or \"frothy\" in the toilet bowl, or a morning ritual of waking up with \"puffy eyes\" that slowly dissipates throughout the day. These are not merely quirks of hydration; they are the hallmark signs of nephrotic syndrome. At the heart of this condition is a failure of the kidney\'s microscopic filtration units. Specifically, damage to the podocytes—specialized cells that act as the final gatekeepers of the blood-filtering barrier—causes the kidneys to become \"leaky.\" When these filters fail, vital proteins like albumin escape into the urine, triggering a cascade of systemic issues, from massive swelling (edema) to a dangerous hypercoagulable state.\n\n\n\n2. The \"Minimal\" Disease with Maximal Impact\n\nMinimal Change Disease (MCD) presents a fascinating paradox in clinical pathology. Its name is derived from the fact that under a standard light microscope, the kidney tissue appears completely normal. However, this \"minimal\" structural change hides a \"maximal\" physiological failure.\n\nThe core pathophysiology involves a T-cell mediated injury that leads to the production of a glomerular permeability factor. This factor causes podocyte effacement (the fusion of foot processes), resulting in a fascinatingly specific biological failure: the loss of a negative charge barrier. Because albumin is also negatively charged, it is normally repelled by the filter; without this charge barrier, albumin leaks into the urine in massive quantities.\n\nWhile MCD is primarily a pediatric condition—accounting for 85% of cases in children under ten—it is highly responsive to steroids. As a Medical Educator, I must highlight a classic \"Examiner Trap\": do not confuse MCD with Post-Streptococcal Glomerulonephritis (PSGN). While both may follow a sore throat or viral trigger, PSGN is a nephritic syndrome characterized by red urine, hypertension, and low C3 levels. In contrast, MCD presents with pure nephrotic features: frothy urine, normal blood pressure, and normal C3 levels.\n\nThe Overarching Clinical Rule: MCD is the \"minimal\" disease—minimal (or no) changes on light microscopy (LM) and immunofluorescence (IF), but maximal protein loss.\n\n\n\n3. The Genetic Shield That Became a Kidney Sword\n\nWhile MCD is the rule for children, Focal Segmental Glomerulosclerosis (FSGS) is the leading cause of nephrotic syndrome in African American adults. The terminology describes the nature of the damage: focal means only some glomeruli are involved, and segmental means only part of the glomerular tuft is scarred.\n\nRecent research highlights a compelling genetic link: the APOL1 gene variant. Common in those of sub-Saharan African descent, this variant likely evolved as a protective shield against certain tropical diseases, but it now acts as a \"sword\" that increases susceptibility to kidney scarring. Unlike the \"minimal\" podocyte fusion seen in MCD, FSGS involves an actual decrease in the number of podocytes.\n\nClinicians must also master the \"IF/THEN\" logic of secondary triggers. IF a patient uses heroin and presents with pure proteinuria, the diagnosis is FSGS; BUT IF that heroin user also presents with hematuria (blood in the urine), the diagnosis shifts to Membranoproliferative Glomerulonephritis (MPGN). Similarly, in Sickle Cell patients, red urine suggests renal papillary necrosis, while massive proteinuria without blood points toward FSGS.\n\n\n\n4. The \"Collapsing\" Crisis: When HIV Meets the Kidney\n\nWithin the spectrum of FSGS, there exists a particularly aggressive form known as the Collapsing Variant. This variant is characterized by the total collapse of the glomerular tuft and a rapid proliferation of parietal epithelial cells.\n\nThis specific pattern is a clinical red flag, as it is most strongly associated with HIV infection (HIVAN) and interferon therapy. For clinicians, the \"collapsing\" terminology is synonymous with a poor prognosis. It represents a crisis point where the kidney\'s architecture doesn\'t just scar—it essentially implodes. This explains why patients with HIV or those using intravenous heroin can face such a rapid, refractory decline in renal function.\n\n\n\n5. The Clot Twist: Why Kidney Damage Leads to Leg Pain\n\nMembranous Nephropathy (MN) is the most common cause of nephrotic syndrome in white adults. It is defined by the accumulation of immune complexes in the subepithelial space, triggering basement membrane thickening. On electron microscopy, this manifests as the classic Spike and Dome phenomenon. In 70% of primary cases, the diagnostic hallmark is the presence of the anti-PLA2R antibody (phospholipase A2 receptor).\n\nHowever, the most dangerous aspect of MN is its \"clot twist.\" Because the \"leaky\" kidney loses a specific protein called Antithrombin III (a natural blood thinner), patients enter a hypercoagulable state.\n\nClassic Complication: MN carries the highest risk of renal vein thrombosis (RVT) due to the loss of Antithrombin III in the urine. The sudden onset of bilateral flank pain, hematuria, and an acute rise in creatinine in a known MN patient is pathognomonic for this complication.\n\n\n\n6. The Amyloid Paradox: When Disease Makes Organs Grow\n\nRenal Amyloidosis represents a \"depositional\" disease where proteins misfold into insoluble beta-pleated sheets (a high-yield distinction from normal ?-helices). These fibrils deposit in the extracellular space, slowly choking out normal kidney function. Pathology offers a vivid diagnostic hallmark: when a tissue sample is treated with a Congo Red stain and viewed under polarized light, the deposits exhibit a striking apple-green birefringence.\n\nAmyloidosis presents a \"Paradox\" regarding organ size. The \"Small Kidney Rule\" dictates that most chronic kidney diseases cause the organs to shrink; however, Amyloidosis often results in normal-sized or enlarged kidneys due to the volume of protein infiltration.\n\nClinicians must distinguish between three primary types:\n\nAA Amyloidosis: Driven by chronic inflammation, such as Rheumatoid Arthritis (RA).\n\nAL Amyloidosis: Associated with plasma cell dyscrasias like Multiple Myeloma. Note the \"Dipstick Trap\": in AL amyloidosis, the dipstick is positive because the glomerulus is affected; in Myeloma Cast Nephropathy, the dipstick is often negative because light chains, rather than albumin, are leaking.\n\nDialysis-Associated Amyloidosis: Seen in long-term ESRD patients where ?-2 microglobulin accumulates, often presenting with carpal tunnel syndrome.\n\nSystemic clues often complete the picture, including Macroglossia (enlarged tongue) and \"raccoon eyes\" (periorbital bruising).\n\n\n\n7. Conclusion: The Filter’s Final Word\n\nThe fate of the kidney is dictated by the \"scenario\" of the damage. Whether it is a child with a post-viral trigger (MCD), an African American adult with the APOL1 variant (FSGS), a patient managing HIV (Collapsing FSGS), or an older adult with chronic inflammation (Amyloidosis), the theme remains the same: a compromise of the body’s essential filtration system.\n\nBecause these conditions are often \"silent\" until massive edema or a renal vein thrombosis occurs, early detection is vital. A simple urinalysis looking for 4+ protein or fatty casts is often the first step in uncovering a complex pathology. If the filters are failing, the body is quick to send signals—the question is whether we are trained to recognize the \"froth\" for the clinical crisis it represents.', NULL, NULL, NULL, NULL, NULL, NULL, 'published', 5, 5, '2026-07-09 09:59:28', '2026-07-09 09:59:30', '2026-07-09 09:54:29', '2026-07-09 09:59:30'),
(8, 24, 1, 'The nephritic spectrum is defined by a diverse group of inflammatory injuries to the glomerular filters, often differentiated by the timing of symptoms and specific laboratory markers like serum complement levels. IgA Nephropathy (Berger’s Disease), the most common primary glomerulonephritis worldwide, is characterised by recurrent macroscopic haematuria that occurs synpharyngitically—during or within 1–3 days of a respiratory infection. Unlike other conditions, IgA nephropathy maintains normal serum complement (C3/C4) levels, with diagnosis confirmed by granular IgA deposits in the mesangium. In contrast, Post-Streptococcal Glomerulonephritis (PSGN) typically affects children and presents with a \"waiting period\" of 1–3 weeks after pharyngitis or 3–6 weeks after skin infection. PSGN is a Type III hypersensitivity reaction where immune complexes clog the filters, leading to low C3 levels, \"cola-coloured\" urine, and pathognomonic \"subepithelial humps\" on electron microscopy. For adults, particularly those with diabetes, Infection-Related Glomerulonephritis (IRGN) represents a more dangerous variant that often occurs concurrently with an active staphylococcal infection, carrying a much poorer prognosis and frequently requiring immediate biopsy and dialysis.\n\nThe more aggressive proliferative disorders involve systemic autoimmune triggers or structural \"splitting\" of the glomerular basement membrane. Diffuse Proliferative Glomerulonephritis (DPGN) is the most severe form of Lupus nephritis, where subendothelial immune complexes create a \"fire\" of inflammation that manifests as thickened \"wire-loop\" capillaries. DPGN classically shows low C3/C4 levels, an active sediment with RBC casts, and a \"full house\" immunofluorescence pattern containing IgG, IgA, IgM, C3, and C1q. Similarly, Membranoproliferative Glomerulonephritis (MPGN) presents as a mixed nephritic-nephrotic syndrome with a unique \"tram-track\" splitting of the basement membrane as the kidney attempts to repair subendothelial damage. MPGN is strongly associated with Hepatitis C (Type 1) or the C3 Nephritic Factor (Type 2), the latter of which causes unstoppable complement activation. If any of these conditions progress violently, they can evolve into Rapidly Progressive Glomerulonephritis (RPGN), a clinical emergency defined by a 50% GFR decline in weeks and the presence of fibrin-filled crescents on biopsy. RPGN is categorized into Type 1 (Linear/Goodpasture Syndrome), Type 2 (Granular/Immune Complex), or Type 3 (Pauci-immune/ANCA-vasculitis), each requiring aggressive stabilization with high-dose pulse steroids to prevent permanent renal failure', 'The \"Kidney Crash\" and Other Medical Mysteries: 5 Surprising Truths About Glomerulonephritis\n\n1. Introduction: The Silent Siege of the Kidney’s Filters\n\nImagine waking up a few days after a common cold and noticing your urine has turned the startling color of tea or cola. This \"medical mystery\" is often the first sign of a \"fire\" in the kidneys. To understand what is happening, think of your kidney’s filters—the glomeruli—as incredibly fine, delicate silk sieves or coffee filters. Their job is to keep essential components like blood and protein in your body while straining out waste. In a group of conditions called glomerulonephritis, the body’s immune system goes haywire, dumping \"sticky trash\" on these filters. This causes them to swell, leak, and occasionally shut down entirely. The following list reveals the most surprising truths about how these delicate filters can fail.\n\n2. The Timing Trap: Why a Sore Throat Might Be a Clue or a Red Herring\n\nWhen a patient presents with blood in their urine following an infection, the most critical question a clinician asks is: \"When exactly did the illness start?\" This timing is the primary way doctors differentiate between a chronic, recurring condition and a one-time post-infectious reaction.\n\nThe \"Synpharyngitic\" Timeline (IgA Nephropathy): In this condition, the red urine appears almost immediately—just 1–3 days after the cold starts. This happens because misshapen IgA antibodies, usually meant to protect the throat and gut, clump together and quickly clog the kidney\'s structural middle (the mesangium).\n\nThe \"Honeymoon Period\" (PSGN): Post-Streptococcal Glomerulonephritis (PSGN) follows a slower rhythm. There is a lag time of 1–3 weeks after a sore throat, or a much longer 3–6 weeks after a skin infection (like impetigo), before the kidney symptoms appear.\n\nThis distinction is vital for prognosis. While PSGN is typically a one-time event that clears with supportive care, IgA Nephropathy is often a lifelong journey.\n\n\"IgA = I Get it Always\"\n\n3. Adult IRGN: The \"Immediate, Real, and Grave\" Emergency\n\nWhile many childhood kidney reactions are relatively benign, adult Infection-Related Glomerulonephritis (IRGN) is a \"lightning strike\" medical emergency. Unlike the childhood version, which has a clear \"honeymoon period,\" adult IRGN often strikes while the patient is still fighting an active, heavy infection—most commonly multi-drug resistant Staph.\n\nThis condition primarily targets \"sick adults,\" such as those with diabetes or those using chronic steroids. The pathology is driven by the alternative complement pathway; the body\'s immune \"demolition crew\" goes into overdrive, using up all its C3 complement to fuel the inflammation. This depletion of C3 is a hallmark laboratory finding that explains why the immune system is so exhausted. Because the inflammation is so aggressive and occurs in already compromised patients, the outlook is often severe.\n\n\"IRGN is Immediate, Real, and Grave... usually leads to dialysis.\"\n\n4. The \"Crescent Moon\" of Destruction: When Kidneys Fail in 72 Hours\n\nRapidly Progressive Glomerulonephritis (RPGN) is known as the \"kidney crash.\" It represents a true emergency where patients can go from normal function to requiring dialysis in just 48 to 72 hours. The hallmark of this destruction is the formation of \"crescents\" inside the kidney filters. When severe inflammation causes physical holes in the filter walls, fibrin and white blood cells leak out into the Bowman\'s space. This triggers the \"wallpaper\" cells of the filter—the parietal epithelial cells—to multiply wildly. These cells pile up into a crescent-moon shape that physically crushes the filter until it collapses.\n\nThe IF Patterns: The DNA of Diagnosis Clinicians identify the specific cause of this crash using Immunofluorescence (IF) patterns. A Linear pattern indicates Type 1 RPGN (Goodpasture Syndrome), where antibodies smoothly coat the filter floor. A Granular pattern signals Type 2, where \"clumpy\" immune complexes from diseases like Lupus or PSGN get stuck in the pipes. Finally, a Pauci-Immune pattern shows very little glowing (Pauci = poor) and is the signature of ANCA-associated vasculitis.\n\n5. The Hybrid Syndrome: When \"Bloody\" Meets \"Bubbly\"\n\nMost kidney diseases are either Nephritic (causing \"bloody\" urine and high blood pressure) or Nephrotic (causing \"bubbly,\" foamy urine due to massive protein leak). However, Diffuse Proliferative Glomerulonephritis (DPGN) and Membranoproliferative Glomerulonephritis (MPGN) act as \"Double Trouble\" hybrids. Patients present with a \"messy sediment\" where the bubbly urine signals a nephrotic-range protein leak and the bloody urine reveals nephritic RBC casts.\n\nIn DPGN, often linked to Lupus, the capillaries become so swollen they appear as \"Wire Loops\" on biopsy. In MPGN, often associated with Hepatitis C, the kidney tries to fix the damage by growing a second layer of basement membrane over the immune \"trash.\" This failed repair attempt results in \"Tram-track\" splitting of the basement membrane, a visual marker of the kidney’s desperate struggle to maintain its structure.\n\n\"DPGN is the Wire-Loop Lupus.\"\n\n6. The \"Full House\" and \"Starry Sky\": The Glowing Clues in the Dark\n\nTo reach a definitive diagnosis, doctors use Immunofluorescence (IF) to make specific immune components \"glow\" under a microscope. This reveals whether the damage is a Type II Hypersensitivity (a direct attack on the basement membrane) or a Type III Hypersensitivity (where clumps of immune complexes get stuck in the filter).\n\n\"Full House\" (DPGN): This is the classic signature of Lupus, where the biopsy \"glows for everything\"—specifically IgG, IgA, IgM, C3, and C1q.\n\n\"Starry Sky\" (PSGN/IRGN): This shows scattered, granular dots of IgG and C3 that look like a constellation in the dark.\n\n\"Linear\" (Goodpasture): A sharp, continuous line of light tracing the floor of the filter, indicating an attack on Type IV collagen.\n\n7. Conclusion: Beyond the Biopsy\n\nUnderstanding these \"immune fires\" is the first step toward aggressive intervention. Whether using high-dose steroids as a \"fire extinguisher\" to quench inflammation or employing plasmapheresis to literally wash dangerous antibodies out of the blood, modern nephrology focuses on stopping the immune system before the filters are permanently scarred.\n\nAs we refine our ability to diagnose these conditions through \"glowing\" patterns and microscopic \"crescents,\" we are left with a sobering thought: If our own antibodies can turn into \"sticky trash\" that shuts down our vital systems, how do we best support the delicate filters that keep us alive?', '[\"IF red urine occurs 1–3 days after a sore throat or viral infection (synpharyngitic) and is recurrent… THEN the diagnosis is IgA Nephropathy (Berger’s Disease).\",\"IF red urine occurs 1–3 weeks after pharyngitis or 3–6 weeks after a skin infection (impetigo\\/honey-crusted sores)… THEN the diagnosis is Post-Streptococcal Glomerulonephritis (PSGN).\",\"IF an adult (especially a diabetic) develops a rapid kidney \\\"crash\\\" during an active Staph infection (cellulitis or infected IV line)… THEN the diagnosis is Infection-Related Glomerulonephritis (IRGN).\",\"IF the patient has a history of Hepatitis C or heroin use and presents with mixed nephritic-nephrotic features… THEN the diagnosis is MPGN Type 1.\",\"IF the patient has SLE (Lupus), low complements, and red urine with RBC casts… THEN the diagnosis is Diffuse Proliferative Glomerulonephritis (DPGN).\",\"IF the patient has hematuria but the C3 and C4 complement levels are NORMAL… THEN think of IgA Nephropathy or Alport Syndrome.\",\"IF the C3 is LOW but the C4 is NORMAL… THEN think of PSGN or MPGN Type 2 (Dense Deposit Disease).\",\"IF both C3 and C4 are LOW… THEN think of DPGN (Lupus) or MPGN Type 1.\",\"IF a biopsy shows \\\"Wire-loop\\\" capillaries and subendothelial deposits… THEN it is DPGN.\",\"IF a biopsy shows \\\"Tram-track\\\" splitting of the basement membrane… THEN it is MPGN.\",\"IF a biopsy shows \\\"Subepithelial humps\\\" (Lumpy-Bumpy) on EM and a \\\"Starry Sky\\\" on IF… THEN it is PSGN or IRGN.\",\"IF a biopsy shows Fibrin Crescents crushing the glomerulus… THEN it is Rapidly Progressive Glomerulonephritis (RPGN).\",\"IF the patient has red urine plus purple leg rash (purpura), joint pain, and abdominal pain… THEN the diagnosis is Henoch-Schönlein Purpura (HSP).\",\"IF a young male smoker has hemoptysis (coughing blood) and red urine with a Linear IF pattern… THEN it is Goodpasture Syndrome (Type 1 RPGN).\",\"IF the patient has chronic sinusitis\\/ear infections, lung cavities, and a positive c-ANCA… THEN it is Granulomatosis with Polyangiitis (Wegener\\/Type 3 RPGN).\",\"IF the patient is losing fat from half of their face (partial lipodystrophy) and has bubbly urine… THEN it is MPGN Type 2 (Dense Deposit Disease).\",\"IF the patient has red urine along with Celiac disease or IBD… THEN it is likely IgA Nephropathy.\",\"IF the Creatinine jumps from 1.0 to 4.0 in just a few days (RPGN pattern)… THEN the best initial treatment is High-dose Pulse IV Methylprednisolone.\",\"IF a child has classic PSGN features (cola urine, low C3, post-strep history)… THEN the next step is Supportive Care (salt restriction\\/diuretics); biopsy is NOT needed.\",\"IF an adult with IgA Nephropathy has stable kidney function and normal BP… THEN the best step is Conservative management and monitoring.\",\"IF an IgA Nephropathy or DPGN patient has proteinuria or hypertension… THEN start ACE inhibitors or ARBs to lower intraglomerular pressure.\",\"IF the patient has Goodpasture Syndrome or severe RPGN… THEN consider Plasmapheresis to wash out the pathogenic antibodies\"]', '[]', '[]', '[]', '[]', '[]', 'published', 5, 5, '2026-07-09 11:20:28', '2026-07-09 11:20:30', '2026-07-09 11:07:12', '2026-07-09 11:31:26'),
(9, 25, 1, 'This comprehensive teaching summary synthesizes the four core lectures on carbohydrate metabolism, focusing on the intricate dance between Glycolysis, Gluconeogenesis, and the HMP Shunt. Biochemistry here is taught not as isolated facts, but as an integrated \"complete package\" connecting physiology, pathology, and pharmacology.\n\n1. Glycolysis: The Energy Investment and Pay-off\n\nGlycolysis is the process of breaking down a 6-carbon glucose molecule into two 3-carbon pyruvate molecules in the cytoplasm.\n\nThe \"Trapping\" Mechanism: Glucose is like a \"naughty child\" that wants to diffuse back out of the cell. To prevent this, the cell \"chains\" its leg by adding a heavy phosphate group to its 6th carbon, creating Glucose-6-Phosphate (G6P).\n\nThe Gatekeepers (Glucokinase vs. Hexokinase):\n\nHexokinase: Found in most tissues. It has a high affinity (Low Km) for glucose, ensuring cells get energy even when glucose levels are low. Hexokinase = High affinity.\n\nGlucokinase: Found in the Liver and Pancreatic beta cells. It has a low affinity (High Km) and acts as a glucose sensor, only working when glucose is abundant (high concentration). It is regulated by Insulin.\n\nThe Rate-Limiting Step (PFK-1): The conversion of Fructose-6-Phosphate to Fructose-1,6-Bisphosphate by Phosphofructokinase-1 (PFK-1) is the most important regulatory step.\n\nThe \"Big Brother\" Regulator: Fructose-2,6-Bisphosphate (F2,6BP) is the \"big brother\" that promotes the activity of the \"small brother\" (PFK-1) to speed up glycolysis.\n\nEnergy Regulators:\n\nInhibitors: High ATP and Citrate signal that the cell already has plenty of energy, so they shut down glycolysis via negative feedback.\n\nActivators: High AMP signals an energy deficit, triggering the pathway to produce more ATP.\n\n2. Anaerobic Glycolysis: Life Without Oxygen\n\nWhen oxygen is absent or in tissues lacking mitochondria (like RBCs), pyruvate is converted into Lactate by Lactate Dehydrogenase.\n\nTeaching Mnemonic: To remember which tissues rely on anaerobic glycolysis, use the phrase: \"Think Lactate When you can\'t Make Respiration\".\n\nT: Testis\n\nL: Lens (Eye)\n\nW: WBCs\n\nC: Cornea\n\nM: Medulla (Kidney)\n\nR: RBCs.\n\n3. Gluconeogenesis: The Reverse Route\n\nGluconeogenesis is the synthesis of glucose from non-carbohydrate precursors (like pyruvate or alanine) during fasting. It is essentially glycolysis in reverse, but it must bypass the three irreversible steps of glycolysis (the 1st, the last, and the rate-limiting step).\n\nThe First Bypass (Pyruvate to PEP):\n\nPyruvate ? Oxaloacetate (OAA) via Pyruvate Carboxylase.\n\nOAA ? Phosphoenolpyruvate (PEP) via PEPCK.\n\nThe Biotin/Raw Egg Correlation: Pyruvate Carboxylase requires Biotin (Vitamin B7) as a cofactor.\n\nExam Trap: A \"gym-goer\" consuming raw egg whites (which contain avidin) can develop a Biotin deficiency, leading to hair loss and impaired gluconeogenesis, manifesting as hypoglycemia.\n\nHormonal Control (The Phosphorylation Rule):\n\nInsulin (after a meal) triggers dephosphorylation, activating glycolytic enzymes.\n\nGlucagon (during fasting) triggers phosphorylation, activating gluconeogenic enzymes.\n\n4. The HMP Shunt: The \"Dandi\" Pathway\n\nThe Hexose Monophosphate (HMP) Shunt is a \"shunt\" because G6P \"skips work\" (deviates from glycolysis) to perform other vital tasks. It does not produce ATP; instead, it produces NADPH and Ribose-5-Phosphate (for DNA synthesis).\n\nThe Role of NADPH:\n\nGlutathione Reduction: NADPH keeps glutathione in a reduced state so it can neutralize oxygen free radicals (like H2\n?O2?) that would otherwise damage the cell.\n\nRespiratory Burst: In WBCs, NADPH is used to create free radicals specifically to kill bacteria.\n\nClinical Pathologies:\n\nG6PD Deficiency: If the key enzyme G6PD is missing, the cell cannot make NADPH. Without NADPH, free radicals destroy RBCs, leading to Heinz bodies (precipitated hemoglobin) and Bite cells (after the spleen \"bites\" out the Heinz body).\n\nChronic Granulomatous Disease (CGD): Caused by a deficiency in NADPH Oxidase. Because the \"respiratory burst\" fails, patients suffer from recurrent infections with catalase-positive bacteria like Staph aureus.\n\n5. Key Clinical Integrations\n\n2,3-BPG Shunt: An intermediate of glycolysis (1,3-BPG) can be diverted to 2,3-BPG. This molecule is crucial at high altitudes because it shifts the Oxygen Dissociation Curve to the right, helping hemoglobin release oxygen to tissues.\n\nDiabetic Ketoacidosis (DKA): A medical emergency where high glucose leads to ketone body production, resulting in a fruity odor on the breath and a high anion gap metabolic acidosis.\n\nHigh Anion Gap Acidosis Mnemonic: Remember \"MUDPILES\" to identify causes of metabolic acidosis, such as Lactic Acidosis (due to elevated lactate).', 'Cracking the Carbohydrate Code: 6 Surprising Insights Into How Your Body Manages Sugar\n\nBiochemistry is often taught as a dry collection of academic cycles, but in reality, it is a \"complete package\" of biological drama. It is the story of how your body translates food into the energy that sustains life. Understanding metabolism is not about rote memorization; it is about grasping the logic of a highly efficient traffic system that governs every cell. By looking at the technicalities of pathways like Glycolysis, Gluconeogenesis, and the HMP Shunt, we can uncover the elegant mechanisms that keep our internal chemistry in balance.\n\n\n\n1. The \"Naughty Child\" and the Molecular Padlock\n\nWhen glucose enters a cell, the body faces an immediate problem: glucose is \"naughty.\" Like a restless child, it tends to diffuse freely back out through the same Glucose Transporters (GLUT) it used to enter. To prevent this escape, the cell must \"trap\" the sugar the moment it arrives.\n\nThe body achieves this by adding a phosphate group to the sixth carbon of the glucose molecule. Think of this phosphate as a \"molecular padlock\" or a heavy chain attached to the child’s leg; the molecule becomes too polar and bulky to pass back through the cell membrane. This first irreversible step of glycolysis is managed by two enzymes: Hexokinase and Glucokinase.\n\n\"In the unfiltered words of renowned educator Conrad Fischer, these two enzymes are the \'same sh*t\'—mechanically, they perform the exact same function.\"\n\nWhile they both handle phosphorylation, where they work and how they respond to sugar levels defines how our organs prioritize energy.\n\n\n\n2. The Two-Way Street of GLUT2\n\nThe movement of glucose into cells is facilitated by Glucose Transporters (GLUT). However, not all transporters behave the same way. Most tissues require one-way entry for energy, but the Liver and Pancreas are unique \"two-way streets.\"\n\nGLUT1: Found in the Brain and Red Blood Cells (RBCs); these are high-priority consumers.\n\nGLUT2: Found in the Liver and Pancreas; these allow for two-way movement, permitting entry for storage and exit for blood sugar regulation.\n\nGLUT5: Primarily responsible for transporting Fructose.\n\nThe two-way movement in the liver and pancreas is the linchpin of systemic balance. When the body has excess glucose, these organs pull it in for storage; when the body is fasting, the liver must be able to release glucose back into the bloodstream to prevent a crash.\n\n\n\n3. Hexokinase vs. Glucokinase: An Affinity Stand-off\n\nA critical concept in biochemistry is the relationship between an enzyme\'s affinity for its substrate and the Michaelis Constant (Km).\n\n\n\nThe Inverse Relation\n\nThere is a fundamental inverse relationship between Km\n\n? and Affinity:\n\nHigh Affinity = Low Km\n\n?\n\nLow Affinity = High Km\n\n?\n\nHexokinase possesses a high affinity (Low Km). It works tirelessly even when glucose levels are scarce, ensuring that vital tissues \"tightly bind\" and utilize any available sugar for survival.\n\nGlucokinase, located in the liver and pancreatic beta cells, has a low affinity (High Km?). It is in a \"don\'t care\" state when sugar is low, only \"waking up\" to act as a Glucose Sensor when concentrations are high, such as after a heavy meal. Because Glucokinase is specifically tasked with managing sugar abundance, it is regulated by insulin, whereas Hexokinase is not.\n\n4. The Raw Egg Hazard and Biotin Deficiency\n\nA classic medical board scenario involves gym-goers who consume large quantities of raw eggs to boost protein intake. This habit can lead to metabolic failure due to a deficiency in Vitamin B7 (Biotin).\n\nRaw egg whites contain a protein that binds Biotin with incredible strength, preventing its absorption. Biotin serves as a critical cofactor for the enzyme Pyruvate Carboxylase, which manages the first step of Gluconeogenesis—the process by which the body creates new glucose from non-carbohydrate sources. When Biotin is deficient, the body cannot convert pyruvate into oxaloacetate, halting the production of new sugar. Clinically, this manifests as hair loss, skin rashes, and systemic energy failure.\n\n\n\n5. The HMP Shunt: The Body\'s \"Antioxidant Shorthand\"\n\nThe Hexose Monophosphate (HMP) Shunt, or Pentose Phosphate Pathway, is a \"shunt\" because it deviates from the normal path of Glycolysis. Instead of producing NADH for energy (ATP), its primary mission is to produce NADPH for biosynthesis and protection.\n\nTo remember where this pathway is most active, use the mnemonic H-M-P:\n\nHepatic (Liver)\n\nMammary glands\n\nPeriphery of the Adrenal Cortex\n\nWhile NADH is used to \"make\" energy, NADPH is used to \"build\" and \"protect.\" It has two critical roles:\n\nNeutralizing Free Radicals: It maintains reduced Glutathione, which protects cells from oxidative damage. This is vital for RBCs because they lack other organelles to neutralize free radicals.\n\nThe Respiratory Burst: In immune cells like Neutrophils, NADPH is used to create oxygen radicals to kill invading bacteria.\n\nA deficiency in the enzyme G6PD leads to a lack of NADPH. Without it, hemoglobin becomes damaged and precipitates into \"Heinz Bodies.\" When these cells pass through the spleen, macrophages \"bite\" out the damage, resulting in \"Bite Cells,\" a hallmark of hemolytic anemia.\n\n\n\n6. The Fate of Pyruvate: Choosing a Road\n\nAt the end of Glycolysis, the body reaches a metabolic crossroads at Pyruvate. Depending on oxygen availability and tissue type, Pyruvate has four possible fates: Lactic Acid, Alanine, Acetyl-CoA, or Oxaloacetate.\n\nIn tissues that lack mitochondria or have limited blood supply, the body relies on Anaerobic Glycolysis to produce Lactate. To remember these tissues, use the lecturer\'s mnemonic:\n\n\"Think Lactate When You Can\'t Make Respiration.\"\n\nTestis\n\nLens of the eye\n\nWBCs (Leukocytes)\n\nCornea\n\nMedulla of the kidney\n\nRBCs\n\nConclusion: Metabolism as an Integrated System\n\nMetabolism is not a series of isolated reactions but a complex, integrated traffic system. It features three irreversible \"checkpoints\" (Steps 1, 3, and 10 of Glycolysis) that act as one-way routes, ensuring that energy production and sugar storage move in the correct direction.\n\nWhile Glycolysis breaks sugar down for immediate use, Gluconeogenesis builds it up for rainy days, and the HMP Shunt \"borrows\" sugar to provide the antioxidant protection and building blocks necessary for life. The next time you head to the gym or sit down for a meal, consider the invisible drama occurring within you: How are your daily habits influencing the microscopic chains and shunts that power your existence at this very moment?', NULL, NULL, NULL, NULL, NULL, NULL, 'published', 5, 5, '2026-07-10 12:41:00', '2026-07-10 12:41:02', '2026-07-10 12:29:46', '2026-07-10 12:41:02'),
(10, 27, 1, 'This comprehensive teaching summary synthesizes the clinical pearls from the two lecture videos, focusing on two major endocrine pathologies: Pheochromocytoma and SIADH (Syndrome of Inappropriate Antidiuretic Hormone).\n\nLecture 1: Pheochromocytoma – The Adrenal Medulla \"Pressure Cooker\"\n\nPheochromocytoma is a tumor arising from the chromaffin cells (or enterchromaffin cells) of the adrenal medulla, which are derived from the neural crest.\n\nPathophysiology & Presentation: The tumor causes a massive release of catecholamines (epinephrine and norepinephrine), leading to a relapsing-remitting pattern of symptoms. It is a famous \"mimicker\" of panic attacks because it causes sudden episodes of tachycardia and tremors. It can also present with polycythemia as a paraneoplastic syndrome due to the release of Erythropoietin (EPO).\n\nThe \"5 Ps\" of Clinical Presentation:\n\n1. Pressure: Persistent or episodic hypertension due to vasoconstriction.\n2. Pain: Severe, pounding headaches.\n3. Perspiration: Excessive, drenching sweating.\n4. Palpitations: A racing heart or tachycardia.\n5. Pallor: Paleness of the skin during an episode.\n\nDiagnostic Markers & Genetics:\n\n• Diagnosis: Confirmed by elevated urinary VMA (Vanillylmandelic Acid) levels and metanephrines, which are metabolic breakdown products of catecholamines.\n\n• Tumor Markers: Immunohistochemistry typically shows positivity for Chromogranin A, Synaptophysin, and NSE (Neuron-Specific Enolase).\n\n• Genetic Links: Associated with MEN 2A, MEN 2B, Von Hippel-Lindau (VHL) syndrome (also linked to renal cell carcinoma), and Neurofibromatosis type 1 (NF1).\n\nThe Rule of 10s:\n\n• 10% occur in children.\n• 10% are malignant.\n• 10% calcify.\n• 10% are bilateral.\n• 10% are extra-adrenal (occurring as paragangliomas).\n\nCritical Management Strategy:\n\n• The Golden Rule: You must give an Alpha-blocker BEFORE a Beta-blocker.\n\n• Why? If you give a beta-blocker first, you block the vasodilatory beta-receptors, leaving alpha-receptors unopposed. This causes massive, \"unopposed alpha\" vasoconstriction, leading to a life-threatening hypertensive crisis.\n\n• Drugs of Choice: Phenoxybenzamine (irreversible alpha-blocker) is used for pre-operative management. Phentolamine (reversible alpha-blocker) is the drug of choice for treating acute hypertensive episodes or \"spells\". The ultimate treatment is surgical resection.\n\nLecture 2: SIADH – The Water-Log Syndrome\n\nSIADH occurs when there is excessive secretion of Antidiuretic Hormone (ADH), leading to inappropriate water retention in the kidneys.\n\nMechanism of Action: ADH acts on V2 receptors in the collecting duct, causing the incorporation of aquaporin channels into the apical membrane. This allows for the excessive absorption of free water back into the body.\n\nSecondary Pathophysiology (The Volume Response): As the body absorbs too much water, internal volume sensors trigger a compensatory response:\n\n1. Decrease in Aldosterone: To stop further water and salt retention.\n2. Increase in ANP and BNP: Atrial and Brain Natriuretic Peptides rise because the heart is stretched by the increased volume.\n3. Natriuresis: These changes cause the kidneys to excrete sodium (natriuresis) and water to normalize the volume. While this keeps the patient \"euvolemic,\" it worsens hyponatremia because sodium is being dumped while free water is being kept.\n\nCommon Causes:\n\n• Ectopic ADH: Most commonly caused by Small Cell Lung Cancer.\n• CNS Issues: Head trauma or severe brain injury.\n• Drugs: SSRIs (e.g., Paroxetine), Carbamazepine, and Cyclophosphamide.\n\nDiagnostic Laboratory Findings:\n\n• Serum Sodium: Low (Hyponatremia).\n• Urine Osmolality: High (>610 mOsm/kg) because the urine is highly concentrated as water is pulled out of it.\n• Potassium: Normal. This is a key finding that distinguishes SIADH from Addison\'s disease (where potassium is high).\n\nManagement Steps:\n\n1. Fluid Restriction: The first-line treatment is to stop the intake of free water.\n2. Salt Management: Use salt tablets or IV hypertonic saline for severe cases.\n3. Demeclocycline: An antibiotic that acts as an ADH antagonist by interfering with its action in the kidneys.\n4. Vaptans: Drugs like Conivaptan or Tolvaptan directly block ADH receptors and are a common \"high-yield\" answer in exams.\n', 'Beyond the Textbook: 5 Surprising Truths About the Body’s Chemical Command Center\n\n1. Introduction\n\nImagine sitting in a quiet room when, without warning, your system is hijacked. Your heart begins to race violently, a hammer-like pressure thumps against your skull, and you are suddenly drenched in sweat. This is the \"hormonal storm,\" a clinical crisis that is as terrifying for the patient as it is intellectually demanding for the physician. How can a microscopic cluster of cells in the adrenal gland or a rogue growth in the lung cause the entire human machine to spiral into a life-threatening emergency? By looking beyond the standard textbooks at Pheochromocytoma and the Syndrome of Inappropriate Antidiuretic Hormone (SIADH), we uncover a world where internal regulators maintain—or catastrophically lose—the delicate balance of survival.\n\n2. The \"5 P\" Storm: When Your Adrenals Go Rogue\n\nPheochromocytoma is a tumor of the adrenal medulla, specifically arising from the neural crest—the same embryonic tissue that gives rise to our nervous system. These \"chromaffin cells\" become rogue factories, producing a massive, episodic surge of catecholamines (epinephrine and norepinephrine).\n\nThis condition is notorious for its \"relapsing-remitting\" nature; symptoms hit in terrifying waves, often leading doctors to misdiagnose the crisis as a severe panic attack. It is, quite literally, a biological \"fight or flight\" response gone haywire. Clinicians identify this storm using the \"5 Ps\":\n\nPressure: Persistent or episodic hypertension (high blood pressure) driven by massive vasoconstriction.\nPain: A severe, throbbing headache.\nPallor: A ghostly paleness that washes over the skin during an episode.\nPalpitations: Tachycardia, where the heart feels like it is leaping out of the chest.\nPerspiration: Drenching, excessive sweating.\n\n\"The episode is so intense that a clinical observation might find a patient drenched in sweat, desperately holding their head in pain as their blood pressure reaches levels that threaten a stroke.\"\n\nThe \"surprising truth\" for many is that the smoking gun for this adrenal storm isn\'t found in the blood, but in the urine. We look for Urinary VMA (Vanillylmandelic acid) and metanephrines—the acidic breakdown products of catecholamines. These, along with \"chemical fingerprints\" like Chromogranin A, Synaptophysin, and Neuron-specific enolase (NSE), allow us to identify these neuroendocrine intruders.\n\n3. The Lethal Logic of \"Alpha Before Beta\"\n\nIn a standard hypertensive crisis, a doctor might reach for a beta-blocker to slow the heart. In Pheochromocytoma, that same medication becomes a poison. Because the tumor floods the body with catecholamines that hit both alpha (vasoconstriction) and beta (vasodilation/heart rate) receptors, blocking the beta receptors first leaves the alpha receptors \"unopposed.\" This causes the blood vessels to constrict even more violently, triggering a catastrophic hypertensive emergency.\n\nTo navigate this minefield, clinicians must follow a strict Order of Operations:\n\nAlpha-blockade (Acute): Administer Phentolamine to manage immediate hypertensive crises.\n\nAlpha-blockade (Pre-operative): Use an irreversible blocker like Phenoxybenzamine to neutralize the receptors long-term.\n\nBeta-blockade: Only after the alpha receptors are fully neutralized can beta-blockers be introduced to control the heart rate.\n\nSurgical Resection: The final step to remove the source of the storm.\n\n4. The Statistical Quirk: The \"Rule of Tens\"\n\nPheochromocytoma is often taught through the \"Rule of 10s,\" a piece of medical shorthand that highlights the tumor\'s unpredictable and diverse nature.\n\nStatistic	Clinical Feature\n\n10%	Occur in Children\n10%	Are Malignant (cancerous)\n10%	Are Calcified on imaging\n10%	Are Bilateral (found in both adrenal glands)\n10%	Are Extra-adrenal (called Paragangliomas)\n\nWhile these statistics provide a framework, the journalist and educator must also look at the genetic shadows behind the tumor. Many cases are linked to hereditary syndromes such as MEN 2A, MEN 2B, and Von Hippel-Lindau (VHL)—reminding us that a tumor in the adrenal gland may just be one piece of a larger genetic puzzle.\n\n5. The Dilution Paradox: How Too Much Water Mimics Too Little Salt\n\nTransitioning from the adrenals to the kidneys, we encounter SIADH (Syndrome of Inappropriate Antidiuretic Hormone). Here, the body secretes excessive ADH, forcing the kidneys to reabsorb water through aquaporins. \n\nThis creates a \"Dilution Paradox.\"\n\nThink of the Salty Soup Analogy: if you have a perfectly seasoned bowl of soup and pour in a glass of water, the soup will taste like it lacks salt. The salt hasn\'t disappeared; it has simply been diluted. In the body, this is called \"dilutional hyponatremia.\"\n\nThe body, sensing the flood, makes a desperate and misguided trade: the heart’s \"internal brain\"—sensing the stretch of the atria and ventricles—releases ANP and BNP. These hormones overrule the kidneys, suppressing aldosterone and triggering natriuresis (salt wasting). By dumping salt to try and lose water, the body worsens the sodium deficiency.\n\nThe Diagnostic Secret: While the blood becomes \"weak soup,\" the urine becomes incredibly \"thick,\" with an osmolality often rising above 610. Crucially, unlike other salt-wasting conditions like Addison’s disease, potassium levels remain normal in SIADH—a textbook-defying fact that helps clinicians solve the mystery.\n\n6. The Lung-Brain Connection: Unexpected Triggers of SIADH\nOne of the most striking truths of SIADH is that the \"Inappropriate\" hormone often doesn\'t come from the brain at all.\n\nThe Ectopic Source: Small Cell Lung Cancer can act as a rogue hormone factory, pumping out ADH independently of the pituitary gland.\n\nThe Mechanical Trigger: Head trauma or severe brain injury can disrupt the normal signaling wires, causing a sudden ADH leak.\n\nThe Pharmacological Culprits: Medications like Carbamazepine, Cyclophosphamide, and SSRIs such as Paroxetine are high-yield triggers.\n\nA surprising clinical paradox exists with SSRIs like Paroxetine; while SIADH usually causes decreased urine volume, some patients present with \"polyuria\" (excessive urination). This paradox highlights the body\'s complex attempt to balance a chemical command center that has gone off the rails.\n\n7. Conclusion: The Delicate Balance of Survival\n\nThe study of these conditions reveals a human body that is far more interconnected—and \"intelligent\"—than many realize. We see a world where the heart \"thinks\" and reacts to volume, where the lungs can bypass the brain to control our hydration, and where the order in which you take two pills can mean the difference between stability and a crisis.\n\nThese hormonal storms remind us of the hidden complexity of our internal regulators. They force us to wonder: how much of what we perceive as \"stress\" or \"mood\" is actually the result of a silent, microscopic chemical battle being fought within the deep command centers of our heart, lungs, and brain?', '[\"Pheochromocytoma arises from chromaffin cells of the adrenal medulla and is of neural crest origin.\",\"It is characterised by the \\\"5 Ps\\\": Pressure (HTN), Pain (Headache), Perspiration (Sweating), Palpitations, and Pallor.\",\"Symptoms follow a relapsing-remitting pattern and can mimic severe panic attacks.\",\"It may cause polycythemia as a paraneoplastic syndrome due to the release of Erythropoietin (EPO).\",\"Genetic associations include MEN 2A, MEN 2B, Von Hippel-Lindau (VHL), and Neurofibromatosis type 1 (NF1).\",\"Diagnosis is confirmed by elevated Urinary VMA (Vanillylmandelic Acid) and metanephrines.\",\"Tumour markers include Chromogranin A, Synaptophysin, and Neuron-Specific Enolase (NSE).\",\"It follows the \\\"Rule of 10s\\\": 10% are malignant, 10% bilateral, 10% extra-adrenal, 10% calcify, and 10% occur in children.\",\"Alpha-blockers (Phenoxybenzamine) must be given BEFORE beta-blockers to prevent a life-threatening unopposed alpha hypertensive crisis.\",\"Phentolamine is the drug of choice for managing acute hypertensive spells or \\\"spells\\\"\",\"SIADH involves excessive ADH acting on V2 receptors to incorporate aquaporin channels into the apical membrane of the collecting duct.\",\"Water retention triggers a compensatory volume response: decreased Aldosterone and increased ANP\\/BNP.\",\"This response causes natriuresis (sodium excretion), which normalises volume but worsens hyponatremia.\",\"Common triggers include Small Cell Lung Cancer (ectopic ADH), head trauma, and drugs like SSRIs, Carbamazepine, and Cyclophosphamide.\",\"Classic laboratory findings are low serum sodium, high urine osmolality (>610 mOsm\\/kg), and normal potassium.\",\"Normal potassium levels help distinguish SIADH from Addison\'s disease, where potassium is typically elevated.\",\"Fluid restriction is the first-line management strategy.\",\"Pharmacological treatments include Demeclocycline and Vaptans (Conivaptan\\/Tolvaptan), which directly block ADH receptors.\"]', '[]', '[]', '[]', '[]', '[]', 'published', 5, 5, '2026-07-11 05:46:07', '2026-07-11 05:46:10', '2026-07-11 05:01:21', '2026-07-11 05:46:10'),
(11, 26, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'published', 5, 5, '2026-07-13 09:39:39', '2026-07-13 09:39:41', '2026-07-13 09:38:23', '2026-07-13 09:39:41');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `is_pinned` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `course_id`, `author_id`, `title`, `content`, `priority`, `is_pinned`, `published_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 'Welcome to NextGen Medics LMS', 'Welcome to our new learning platform. Explore courses and start learning today!', 'high', 0, '2026-07-02 12:24:21', NULL, '2026-07-02 12:24:21', '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `max_marks` decimal(6,2) DEFAULT 100.00,
  `assignment_type` enum('file','interactive_test') NOT NULL DEFAULT 'file',
  `attachment_path` varchar(500) DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `status` enum('draft','published','closed') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `course_id`, `teacher_id`, `title`, `description`, `instructions`, `due_date`, `max_marks`, `assignment_type`, `attachment_path`, `external_url`, `status`, `created_at`, `updated_at`) VALUES
(13, 1, 5, 'Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait', 'Please Review the article ', '', '2026-09-30 23:59:00', 0.00, 'file', 'uploads/courses/1/assignments/a359ec40497fcf40e45a4d55c49254e0.pdf', NULL, 'published', '2026-07-09 06:46:20', '2026-07-09 07:24:24'),
(14, 1, 5, 'Nephro Pathology - Cryoglobulin Associated Kidney Disease', 'Review the article ', '', '2026-09-30 23:59:00', 0.00, 'file', 'uploads/courses/1/assignments/c4f0be5493a26aadf013fd58f1065997.pdf', NULL, 'published', '2026-07-09 07:23:56', '2026-07-09 07:23:56'),
(15, 1, 6, 'Anatomy UPPER LIMB', '', '', '2026-07-15 12:46:00', 0.00, 'file', 'uploads/courses/1/assignments/286208b09baf06d18e275ab3c179d1a1.pdf', NULL, 'published', '2026-07-09 07:44:45', '2026-07-09 07:44:45');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_attachments`
--

CREATE TABLE `assignment_attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `assignment_attachments`
--

INSERT INTO `assignment_attachments` (`id`, `assignment_id`, `title`, `file_path`, `original_filename`, `mime_type`, `file_size`, `sort_order`, `created_at`) VALUES
(3, 13, 'The _Silent_ Carrier Trap_ Why Your Kidneys May Be at Risk Despite a _Benign_ Genetic Label', 'uploads/courses/1/assignments/a359ec40497fcf40e45a4d55c49254e0.pdf', 'The _Silent_ Carrier Trap_ Why Your Kidneys May Be at Risk Despite a _Benign_ Genetic Label.pdf', 'application/pdf', 89504, 0, '2026-07-09 06:46:20'),
(4, 14, 'When Your Own Blood Reacts to the Cold_ The Hidden Reality of Cryoglobulin-Associated Kidney Disease', 'uploads/courses/1/assignments/c4f0be5493a26aadf013fd58f1065997.pdf', 'When Your Own Blood Reacts to the Cold_ The Hidden Reality of Cryoglobulin-Associated Kidney Disease.pdf', 'application/pdf', 87969, 0, '2026-07-09 07:23:56'),
(5, 15, 'FCPS Part1 Aantomy Worksheet Upper limb', 'uploads/courses/1/assignments/286208b09baf06d18e275ab3c179d1a1.pdf', 'FCPS Part1 Aantomy Worksheet Upper limb.pdf', 'application/pdf', 441817, 0, '2026-07-09 07:44:45');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_questions`
--

CREATE TABLE `assignment_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `explanation` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_question_options`
--

CREATE TABLE `assignment_question_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `option_text` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `submission_text` text DEFAULT NULL,
  `answers_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers_json`)),
  `marks` decimal(6,2) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `passed` tinyint(1) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('submitted','graded','returned','late') DEFAULT 'submitted',
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `graded_at` timestamp NULL DEFAULT NULL,
  `graded_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `student_id`, `file_path`, `original_filename`, `submission_text`, `answers_json`, `marks`, `percentage`, `passed`, `remarks`, `status`, `submitted_at`, `graded_at`, `graded_by`) VALUES
(6, 15, 7, 'uploads/submissions/7/564a74a7d3e92167f056505eb95eb2de.pdf', 'FCPS Part1 Aantomy Worksheet Upper limb.pdf', NULL, NULL, NULL, NULL, NULL, NULL, 'submitted', '2026-07-11 09:46:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submission_files`
--

CREATE TABLE `assignment_submission_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `assignment_submission_files`
--

INSERT INTO `assignment_submission_files` (`id`, `submission_id`, `title`, `file_path`, `original_filename`, `mime_type`, `file_size`, `sort_order`, `created_at`) VALUES
(1, 6, 'FCPS Part1 Aantomy Worksheet Upper limb', 'uploads/submissions/7/564a74a7d3e92167f056505eb95eb2de.pdf', 'FCPS Part1 Aantomy Worksheet Upper limb.pdf', 'application/pdf', 926799, 0, '2026-07-11 09:46:22');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `status` enum('present','absent','late','leave') NOT NULL DEFAULT 'absent',
  `remarks` varchar(255) DEFAULT NULL,
  `marked_by` int(10) UNSIGNED DEFAULT NULL,
  `marked_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `session_date` date NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `event` varchar(100) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `criteria_type` enum('streak','mcq','flashcard','lecture','revision') NOT NULL DEFAULT 'streak',
  `threshold` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `code`, `name`, `description`, `icon`, `criteria_type`, `threshold`, `created_at`) VALUES
(1, 'streak_7', '7-Day Streak', 'Studied every day for 7 days in a row', 'flame', 'streak', 7, '2026-07-04 05:29:43'),
(2, 'streak_30', '30-Day Streak', 'Studied every day for 30 days in a row', 'flame', 'streak', 30, '2026-07-04 05:29:43'),
(3, 'streak_100', '100-Day Streak', 'Studied every day for 100 days in a row', 'crown', 'streak', 100, '2026-07-04 05:29:43'),
(4, 'mcq_100', 'Century Maker', 'Answered 100 MCQs', 'target', 'mcq', 100, '2026-07-04 05:29:43'),
(5, 'mcq_1000', 'MCQ Master', 'Answered 1000 MCQs', 'trophy', 'mcq', 1000, '2026-07-04 05:29:43'),
(6, 'flash_100', 'Flash Learner', 'Reviewed 100 flashcards', 'zap', 'flashcard', 100, '2026-07-04 05:29:43');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batch_students`
--

CREATE TABLE `batch_students` (
  `batch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `template_id` int(10) UNSIGNED DEFAULT NULL,
  `certificate_number` varchar(100) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT current_timestamp(),
  `issued_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_templates`
--

CREATE TABLE `certificate_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `template_html` longtext NOT NULL,
  `background_image` varchar(500) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `certificate_templates`
--

INSERT INTO `certificate_templates` (`id`, `name`, `template_html`, `background_image`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'Default Certificate', '<div class=\"certificate\"><h1>Certificate of Completion</h1><p>This certifies that {{student_name}} has successfully completed {{course_title}}.</p><p>Issued: {{issue_date}}</p><p>Certificate No: {{certificate_number}}</p></div>', NULL, 1, '2026-07-02 12:24:21', '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` int(10) UNSIGNED NOT NULL,
  `module_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`id`, `module_id`, `title`, `description`, `sort_order`, `is_published`, `created_at`, `updated_at`) VALUES
(16, 16, 'Renal', NULL, 0, 0, '2026-07-08 11:08:34', '2026-07-08 11:08:34'),
(17, 17, 'Gram Positive Cocci', NULL, 0, 0, '2026-07-08 13:07:50', '2026-07-08 13:07:50'),
(18, 18, 'upper limb', NULL, 0, 0, '2026-07-09 04:52:39', '2026-07-09 04:52:39'),
(19, 19, 'Metabolism', NULL, 0, 0, '2026-07-10 11:55:24', '2026-07-10 11:55:24'),
(20, 20, 'Renal Pharmacology', NULL, 0, 0, '2026-07-11 03:51:46', '2026-07-11 03:51:46'),
(21, 16, 'Endocrinology', NULL, 0, 0, '2026-07-11 04:19:15', '2026-07-11 04:19:15'),
(22, 18, 'Lower Limb', NULL, 0, 0, '2026-07-11 12:49:22', '2026-07-11 12:49:22');

-- --------------------------------------------------------

--
-- Table structure for table `class_reminder_log`
--

CREATE TABLE `class_reminder_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `occurrence_date` date NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` enum('teacher','student') NOT NULL,
  `channel` enum('whatsapp','in_app','email') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_schedule`
--

CREATE TABLE `class_schedule` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `batch_id` int(10) UNSIGNED DEFAULT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `lecture_title` varchar(255) NOT NULL,
  `lecture_number` int(10) UNSIGNED DEFAULT NULL,
  `topic_covered` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `class_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT NULL,
  `meeting_link` varchar(500) DEFAULT NULL,
  `recording_link` varchar(500) DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `status` enum('upcoming','live','completed','cancelled','postponed','rescheduled') DEFAULT 'upcoming',
  `is_status_locked` tinyint(1) DEFAULT 0,
  `rescheduled_from_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_bookmarks`
--

CREATE TABLE `content_bookmarks` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `content_type` enum('note','flashcard','mcq','lecture') NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL,
  `note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `content_bookmarks`
--

INSERT INTO `content_bookmarks` (`id`, `student_id`, `content_type`, `content_id`, `note`, `created_at`) VALUES
(2, 19, 'lecture', 24, NULL, '2026-07-09 18:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `banner` varchar(500) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `level` enum('beginner','intermediate','advanced','professional') DEFAULT 'beginner',
  `prerequisites` text DEFAULT NULL,
  `learning_outcomes` text DEFAULT NULL,
  `status` enum('draft','published','archived','unpublished') DEFAULT 'draft',
  `max_students` int(10) UNSIGNED DEFAULT NULL,
  `certificate_available` tinyint(1) DEFAULT 0,
  `enrollment_status` enum('open','closed','waitlist') DEFAULT 'open',
  `sort_order` int(11) DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `category_id`, `teacher_id`, `title`, `slug`, `subtitle`, `short_description`, `description`, `thumbnail`, `banner`, `duration`, `start_date`, `end_date`, `fee`, `level`, `prerequisites`, `learning_outcomes`, `status`, `max_students`, `certificate_available`, `enrollment_status`, `sort_order`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 6, 'Featured Course FCPS 1 Preparation Course', 'featured-course-fcps-1-preparation-course', 'Complete foundation for  FCPS', 'Master FCPS Part 1 With Confidence', 'A comprehensive course covering all major Topics with clinical correlations.', NULL, NULL, '12 weeks', NULL, NULL, 0.00, 'beginner', NULL, NULL, 'published', 100, 0, 'open', 0, 1, '2026-07-02 12:24:21', '2026-07-02 12:41:10', NULL),
(2, 2, 2, 'Clinical Examination Skills', 'clinical-examination-skills', 'OSCE-ready clinical skills', 'Learn systematic clinical examination techniques.', 'Step-by-step guide to history taking and physical examination for OSCE success.', NULL, NULL, '8 weeks', NULL, NULL, 249.00, 'intermediate', NULL, NULL, 'published', NULL, 1, 'open', 0, 1, '2026-07-02 12:24:21', '2026-07-02 12:24:21', NULL),
(3, 3, 2, 'USMLE Step 1 Prep Intensive', 'usmle-step-1-prep', 'High-yield Step 1 preparation', 'Intensive USMLE Step 1 preparation program.', 'Cover high-yield topics, practice questions, and exam strategies.', NULL, NULL, '16 weeks', NULL, NULL, 499.00, 'advanced', NULL, NULL, 'published', NULL, 1, 'open', 0, 1, '2026-07-02 12:24:21', '2026-07-02 12:24:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_categories`
--

CREATE TABLE `course_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Medical Sciences', 'medical-sciences', 'Core medical education courses', NULL, 1, 1, '2026-07-02 12:24:21', '2026-07-02 12:24:21'),
(2, 'Clinical Skills', 'clinical-skills', 'Hands-on clinical training', NULL, 2, 1, '2026-07-02 12:24:21', '2026-07-02 12:24:21'),
(3, 'Exam Preparation', 'exam-preparation', 'USMLE, PLAB, and other exam prep', NULL, 3, 1, '2026-07-02 12:24:21', '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `course_class_schedule`
--

CREATE TABLE `course_class_schedule` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL COMMENT '0=Sunday, 1=Monday, ... 6=Saturday',
  `start_time` time NOT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT 60,
  `title` varchar(255) DEFAULT NULL,
  `meeting_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course_class_schedule`
--

INSERT INTO `course_class_schedule` (`id`, `course_id`, `day_of_week`, `start_time`, `duration_minutes`, `title`, `meeting_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '21:45:00', 60, 'Lecture 003', 'https://us05web.zoom.us/j/84497226954?pwd=aoVax5b2ky9oWg2uZvm2xFE5moCeXE.1\r\n', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(2, 1, 1, '19:30:00', 60, 'Lec 001: Upper Limb Bones, Joints,Surface Anatomy', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(3, 1, 1, '19:30:00', 60, 'Lec 002: Upper limb Nerves and clinical cases', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(4, 1, 1, '19:00:00', 60, 'Lec 003 : Upper Limb Vasculature and brachial plexus/ Axilla', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(5, 1, 1, '22:00:00', 60, 'Lecture 004', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(6, 1, 1, '22:00:00', 60, 'Lecture 005', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(7, 1, 1, '21:50:00', 60, 'Lecture 006', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(8, 1, 1, '20:30:00', 60, 'Lec 004: Lower limb bones, joints, surface anatomy', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(9, 1, 1, '20:30:00', 60, 'Lec 005: Femoral Triangle and vasculature of Lower limb', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(10, 1, 1, '20:30:00', 60, 'Lec 006:  Leg, Foot & Clinical Anatomy', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(11, 1, 1, '21:30:00', 60, 'Lecture 007', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(12, 1, 1, '21:30:00', 60, 'Lecture 008', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(13, 1, 1, '21:30:00', 60, 'Lecture 009', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(14, 1, 1, '20:30:00', 60, 'Lecture 007: Thorax anatomy', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(15, 1, 1, '20:30:00', 60, 'Lecture 008:  Respiratory Physiology I', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(16, 1, 1, '20:30:00', 60, 'Lecture 009:  Respiratory Physiology II', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(17, 1, 1, '21:30:00', 60, 'Lecture 010', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(18, 1, 1, '21:30:00', 60, 'Lecture 011', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(19, 1, 1, '21:30:00', 60, 'Lecture 012', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(20, 1, 1, '20:30:00', 60, 'LECTURE 010: Heart and mediastinum', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(21, 1, 1, '20:30:00', 60, 'Lecture 011:  Cardiovascular Physiology I', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(22, 1, 1, '20:30:00', 60, 'Lecture 012: Cardiovascular Physiology II', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(23, 1, 1, '21:30:00', 60, 'Lecture 013', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(24, 1, 1, '21:30:00', 60, 'Lecture 014', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(25, 1, 1, '21:30:00', 60, 'Lecture 015', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(26, 1, 1, '20:30:00', 60, 'LECTURE 013: Abdomen I', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45'),
(27, 1, 1, '20:30:00', 60, 'Lecture 014: Abdomen II', '', 1, '2026-07-09 07:05:45', '2026-07-09 07:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','completed','dropped','suspended') DEFAULT 'active',
  `progress` decimal(5,2) DEFAULT 0.00,
  `enrolled_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `course_id`, `student_id`, `status`, `progress`, `enrolled_at`, `completed_at`) VALUES
(2, 1, 17, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(3, 1, 16, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(4, 1, 15, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(5, 1, 14, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(6, 1, 13, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(7, 1, 12, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(8, 1, 11, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(9, 1, 10, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(10, 1, 9, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(11, 1, 8, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(12, 1, 7, 'active', 0.00, '2026-07-02 12:40:56', NULL),
(24, 1, 3, 'active', 0.00, '2026-07-06 12:36:30', NULL),
(25, 1, 18, 'active', 0.00, '2026-07-09 07:47:33', NULL),
(26, 1, 19, 'active', 0.00, '2026-07-09 08:26:21', NULL),
(27, 1, 20, 'active', 0.00, '2026-07-09 10:05:28', NULL),
(28, 1, 21, 'active', 0.00, '2026-07-09 15:03:59', NULL),
(30, 1, 22, 'active', 0.00, '2026-07-14 03:08:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_teachers`
--

CREATE TABLE `course_teachers` (
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course_teachers`
--

INSERT INTO `course_teachers` (`course_id`, `teacher_id`, `assigned_at`) VALUES
(1, 5, '2026-07-02 12:41:10'),
(1, 6, '2026-07-02 12:41:10'),
(2, 2, '2026-07-02 12:24:21'),
(3, 2, '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `daily_challenges`
--

CREATE TABLE `daily_challenges` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `mcqs_per_day` smallint(5) UNSIGNED NOT NULL DEFAULT 10,
  `start_date` date DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_challenge_sets`
--

CREATE TABLE `daily_challenge_sets` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `challenge_date` date NOT NULL,
  `mcq_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`mcq_ids`)),
  `attempt_id` int(10) UNSIGNED DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discussion_replies`
--

CREATE TABLE `discussion_replies` (
  `id` int(10) UNSIGNED NOT NULL,
  `thread_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `is_answer` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `is_teacher_approved` tinyint(1) NOT NULL DEFAULT 0,
  `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discussion_reply_likes`
--

CREATE TABLE `discussion_reply_likes` (
  `reply_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discussion_reports`
--

CREATE TABLE `discussion_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `thread_id` int(10) UNSIGNED DEFAULT NULL,
  `reply_id` int(10) UNSIGNED DEFAULT NULL,
  `reporter_id` int(10) UNSIGNED NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('open','resolved','dismissed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discussion_threads`
--

CREATE TABLE `discussion_threads` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED DEFAULT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `status` enum('open','closed','hidden') DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flashcards`
--

CREATE TABLE `flashcards` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `front` text NOT NULL,
  `back` text NOT NULL,
  `topic` varchar(255) DEFAULT NULL,
  `difficulty` enum('easy','moderate','difficult') NOT NULL DEFAULT 'moderate',
  `source` enum('ai','manual') NOT NULL DEFAULT 'ai',
  `status` enum('draft','approved') NOT NULL DEFAULT 'draft',
  `sort_order` int(11) DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `flashcards`
--

INSERT INTO `flashcards` (`id`, `lecture_id`, `course_id`, `front`, `back`, `topic`, `difficulty`, `source`, `status`, `sort_order`, `created_by`, `created_at`, `updated_at`) VALUES
(333, 22, 1, 'In patients with malabsorption (e.g. Crohn’s disease), why does unabsorbed fat increase the risk of Calcium Oxalate stones?', 'Unabsorbed fat binds intestinal calcium, preventing it from binding to and neutralising oxalate.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 1, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(334, 22, 1, 'The consumption of high-dose Vitamin C supplements increases the risk of which specific renal stone type?', 'Calcium Oxalate (Vitamin C is metabolised into oxalate).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 2, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(335, 22, 1, 'A high-sodium diet increases renal stone risk by forcing the excretion of which substance into the urine?', 'Calcium', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 3, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(336, 22, 1, 'Which urinary substance acts as a \'shield\' by preventing the clumping of calcium stones?', 'Citrate', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 4, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(338, 22, 1, 'In idiopathic calcium stone disease, what is the typical finding regarding serum and urinary calcium levels?', 'Normal serum calcium levels but high urinary calcium levels.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 6, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(339, 22, 1, 'The presence of red blood cells in the urine without the presence of _____ _____ suggests a renal stone rather than glomerulonephritis.', 'RBC casts', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 7, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(340, 22, 1, 'Microscopic \'envelope-shaped\' (octahedron) crystals in the urine are pathognomonic for which stone type?', 'Calcium Oxalate', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 8, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(341, 22, 1, 'Microscopic \'wedge-shaped\' crystals or rosettes occurring in alkaline urine suggest which stone type?', 'Calcium Phosphate', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 9, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(342, 22, 1, 'What is the gold-standard imaging modality for the initial diagnosis of most renal stones?', 'Non-contrast CT scan of the abdomen and pelvis.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 10, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(343, 22, 1, 'Which imaging modality is preferred for diagnosing renal stones in pregnant women or children to avoid radiation?', 'Renal Ultrasound', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 11, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(344, 22, 1, 'What class of analgesics is considered more effective than opioids for the pain of acute renal colic?', 'NSAIDs (e.g. Ketorolac, Ibuprofen).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 12, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(345, 22, 1, 'What is the mechanism of action of Tamsulosin in the medical expulsive therapy of ureteric stones?', 'It is an Alpha-blocker that relaxes the ureteric smooth muscle.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 13, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(346, 22, 1, 'For stones sized between 5 - 10 mm, which pharmacological treatment is indicated to aid passage?', 'Tamsulosin', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 14, 5, '2026-07-09 08:05:29', '2026-07-09 08:07:51'),
(347, 22, 1, 'How do Thiazide diuretics assist in preventing recurrent calcium stones?', 'They decrease urinary calcium excretion by increasing calcium reabsorption into the blood.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 15, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(348, 22, 1, 'Cystine stones are caused by an autosomal recessive defect in the transporter for which four amino acids?', 'Cystine, Ornithine, Lysine, and Arginine (COLA).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 16, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(349, 22, 1, 'In which part of the nephron is the \'COLA\' amino acid transporter defect located in Cystinuria?', 'Proximal Convoluted Tubule (PCT)', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 17, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(350, 22, 1, 'What is the characteristic microscopic appearance of crystals found in the urine of a patient with Cystinuria?', 'Hexagonal (six-sided) crystals.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 18, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(351, 22, 1, 'What colour change occurs in the urinary cyanide-nitroprusside test in a patient with Cystine stones?', 'Magenta (bright pink/purple).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 19, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(352, 22, 1, 'How does the radiopacity of a Cystine stone differ as compared to a Calcium stone on a plain X-ray?', 'Cystine stones are \'weakly radiopaque\' (faint), whereas Calcium stones are strongly radiopaque.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 20, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(353, 22, 1, 'What is the primary pharmacological goal in managing Cystine stones via urinary pH modification?', 'Alkalinisation of the urine (typically to PH>7.0)', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 21, 5, '2026-07-09 08:05:29', '2026-07-09 09:35:18'),
(354, 22, 1, 'Which thiol-containing drug can be used for refractory Cystine stones by \'cutting\' the molecules to increase solubility?', 'Tiopronin (or Penicillamine).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 22, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(355, 22, 1, 'Struvite stones (Magnesium Ammonium Phosphate) are primarily caused by infection with which type of bacteria?', 'Urease-producing bacteria (e.g. Proteus mirabilis, Klebsiella).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 23, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(356, 22, 1, 'What is the chemical mechanism by which Proteus mirabilis promotes the formation of Struvite stones?', 'Urease hydrolyses urea into ammonia, raising the urinary pH (making it alkaline).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 24, 5, '2026-07-09 08:05:29', '2026-07-09 09:35:33'),
(357, 22, 1, 'A \'Staghorn Calculus\' that fills the renal pelvis and calyces in an adult female with recurrent UTIs is most likely composed of _____.', 'Struvite (Ammonium Magnesium Phosphate)', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 25, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(358, 22, 1, 'What is the classic microscopic appearance of Struvite crystals?', 'Rectangular prisms or \'coffin lids\'.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 26, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(359, 22, 1, 'Why is surgical removal (e.g. PCNL) necessary for Struvite stones rather than antibiotic therapy alone?', 'The stone acts as a reservoir/shield for bacteria, leading to recurrence if not completely removed.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 27, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(360, 22, 1, 'Uric acid stones typically form when the urinary pH is consistently below what value?', '5.5', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 28, 5, '2026-07-09 08:05:29', '2026-07-09 09:36:15'),
(361, 22, 1, 'Which renal stone type is described as \'radiolucent\' (invisible) on plain X-ray but visible on CT scan?', 'Uric Acid stone', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 29, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(362, 22, 1, 'Under microscopy, what are the two common shapes of Uric Acid crystals?', 'Rhomboid (diamond-shaped) or rosette-shaped.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 30, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(363, 22, 1, 'Why do patients with chronic diarrhoea have an increased risk of forming Uric Acid stones?', 'Loss of bicarbonate leads to metabolic acidosis and highly acidic urine.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 31, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(364, 22, 1, 'Which genetic kidney disease is specifically associated with an increased tendency toward forming Uric Acid stones?', 'Autosomal Dominant Polycystic Kidney Disease (ADPKD).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 32, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(365, 22, 1, 'What is the best initial medical step to dissolve existing Uric Acid stones?', 'Alkalinisation of the urine (using Potassium Citrate or Potassium Bicarbonate).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 33, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(366, 22, 1, 'Why is Probenecid contraindicated in patients with a history of Uric Acid stones?', 'It is a uricosuric drug that increases the concentration of uric acid in the urine, worsening stone formation.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 34, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(367, 22, 1, 'What are the three most common anatomical locations for a stone to become stuck in the ureter?', 'Ureteropelvic Junction (UPJ), iliac vessel crossing, and Ureterovesical Junction (UVJ).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 35, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(368, 22, 1, 'Which clinical complication of a renal stone presents as a life-threatening emergency requiring immediate decompression?', 'Urosepsis (stone-induced obstruction + fever/infection).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 36, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(369, 22, 1, 'What are the two primary methods used to decompress a kidney obstructed by an infected stone?', 'Ureteral Stent placement or Percutaneous Nephrostomy tube.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 37, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(370, 22, 1, 'According to the \'5-10-20 Rule\', what is the management for a stone >20 mm or a complex Staghorn calculus?', 'Percutaneous Nephrolithotomy (PCNL).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 38, 5, '2026-07-09 08:05:29', '2026-07-09 08:09:16'),
(371, 22, 1, 'Shock Wave Lithotripsy (ESWL) is most appropriate for stones of what size and location?', 'Stones < 20 mm in the renal pelvis or upper ureter.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 39, 5, '2026-07-09 08:05:29', '2026-07-09 08:09:32'),
(372, 22, 1, 'What are the two major contraindications for Shock Wave Lithotripsy (ESWL)?', 'Pregnancy and active Urinary Tract Infection (UTI).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 40, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(373, 22, 1, 'Which surgical intervention is preferred for stones located in the distal (lower) ureter?', 'Ureterorenoscopy (URS) with stone retrieval or laser lithotripsy.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 41, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(374, 22, 1, 'How does the crystal shape of Uric Acid stones differ as compared to Cystine stones?', 'Uric Acid crystals are rhomboid/rosette-shaped, as compared to the hexagonal crystals of Cystine.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 42, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(375, 22, 1, 'How does the urinary pH typically differ in Struvite stones as compared to Uric Acid stones?', 'Struvite stones form in alkaline urine (pH > 7.0), as compared to Uric Acid stones which form in acidic urine (pH < 5.5).', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 43, 5, '2026-07-09 08:05:29', '2026-07-09 08:10:06'),
(376, 22, 1, 'In a patient with Sickle Cell trait and flank pain, what condition should be considered if the CT scan shows no stone?', 'Renal Papillary Necrosis', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 44, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(377, 22, 1, 'What is the first step in managing a post-operative patient with zero urine output and a positive bladder scan?', 'Check the Foley catheter for kinks or obstructions.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 45, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(378, 22, 1, 'Which stone type is associated with Tumor Lysis Syndrome following chemotherapy for Leukemia?', 'Uric Acid stones', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 46, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(379, 22, 1, 'What is the effect of increased Bowman capsule hydrostatic pressure caused by a chronic stone obstruction?', 'Hydronephrosis and potential renal atrophy.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 47, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(380, 22, 1, 'How does the management of a stone < 5mm differ from a stone > 10 mm', 'Stones < 5mm are managed conservatively with hydration, as compared to stones > 10 mm which usually require surgical intervention.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 48, 5, '2026-07-09 08:05:29', '2026-07-09 08:06:42'),
(381, 22, 1, 'For a pregnant patient with renal colic and a negative ultrasound, what is the clinical significance of a positive pregnancy test in the context of lower abdominal pain?', 'It raises suspicion for Ectopic Pregnancy as a differential diagnosis.', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 49, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:29'),
(382, 22, 1, 'What is the recommended daily urine output to prevent the recurrence of most kidney stones?', '>2.5 L', 'Nephrolithiasis Flashcards', 'moderate', 'manual', 'approved', 50, 5, '2026-07-09 08:05:29', '2026-07-09 08:05:56'),
(383, 23, 1, 'In the pathophysiology of Minimal Change Disease (MCD), what specific physiological barrier is lost to allow massive albuminuria?', 'The negative charge barrier of the glomerular basement membrane.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 1, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(384, 23, 1, 'A 45-year-old adult with Hodgkin lymphoma develops nephrotic syndrome; what is the most likely underlying renal diagnosis?', 'Minimal Change Disease (MCD).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 2, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(385, 23, 1, 'Under what specific condition is a renal biopsy indicated in a paediatric patient presenting with classic signs of Minimal Change Disease?', 'If the patient exhibits steroid-resistant nephrotic syndrome (fails to respond after 4–8 weeks of therapy).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 3, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(386, 23, 1, 'How does the podocyte pathology in Focal Segmental Glomerulosclerosis (FSGS) differ from that seen in Minimal Change Disease?', 'FSGS involves a decrease in the actual number of podocytes (podocyte loss), whereas MCD only shows foot process effacement.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 4, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(387, 23, 1, 'Which specific genetic variant, common in individuals of sub-Saharan African descent, predisposes patients to Focal Segmental Glomerulosclerosis?', 'The APOL1 gene variant.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 5, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:47'),
(388, 23, 1, 'In a patient with Sickle Cell Disease, how does the presentation of Focal Segmental Glomerulosclerosis differ from Renal Papillary Necrosis?', 'FSGS presents with massive proteinuria and oedema without blood, as compared to Renal Papillary Necrosis which presents with gross haematuria.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 6, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(389, 23, 1, 'A patient with a history of intravenous heroin use presents with nephrotic-range proteinuria and microscopic haematuria; which diagnosis is more likely than FSGS?', 'Membranoproliferative Glomerulonephritis (MPGN).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 7, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(390, 23, 1, 'Which histological variant of FSGS is characteristically associated with HIV infection (HIVAN) and carries a particularly poor prognosis?', 'The Collapsing Variant (characterised by glomerular tuft collapse).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 8, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(391, 23, 1, 'For a morbidly obese patient with secondary FSGS, what is the initial management strategy as compared to primary FSGS?', 'Weight loss and ACE inhibitors, as compared to high-dose corticosteroids used in primary FSGS.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 9, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(392, 23, 1, 'What is the most common primary (idiopathic) cause of nephrotic syndrome in Caucasian adults?', 'Membranous Nephropathy (MN).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 10, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(393, 23, 1, 'The primary (autoimmune) form of Membranous Nephropathy is most commonly associated with antibodies against which receptor?', 'The Phospholipase A-2 Receptor', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 11, 5, '2026-07-09 09:55:30', '2026-07-09 09:56:07'),
(394, 23, 1, 'How do the viral associations of Membranous Nephropathy differ from those of Membranoproliferative Glomerulonephritis?', 'Membranous Nephropathy is classically associated with Hepatitis B, as compared to MPGN which is linked to Hepatitis C.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 12, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(395, 23, 1, 'A 68-year-old smoker with nephrotic syndrome is suspected of having Membranous Nephropathy; what is the likely underlying trigger?', 'A paraneoplastic manifestation of a solid organ malignancy (e.g. lung, breast, or colon cancer).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 13, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(396, 23, 1, 'Which specific complication should be suspected in a patient with Membranous Nephropathy who develops sudden-onset flank pain and gross haematuria?', 'Renal Vein Thrombosis (due to loss of Antithrombin III).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 14, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(397, 23, 1, 'On Electron Microscopy, what is the pathognomonic finding for Membranous Nephropathy?', 'Subepithelial immune complex deposits showing a \'spike and dome\' appearance.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 15, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(398, 23, 1, 'What is the specific component of the \'Modified Ponticelli Regimen\' used to treat primary Membranous Nephropathy?', 'Alternating cycles of corticosteroids and alkylating agents (e.g. cyclophosphamide) over six months.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 16, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(399, 23, 1, 'How does the kidney size on ultrasound in Renal Amyloidosis differ from most other causes of Chronic Kidney Disease?', 'The kidneys are typically enlarged or normal-sized in Amyloidosis, as compared to the shrunken kidneys usually seen in CKD.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 17, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(400, 23, 1, 'Which type of amyloidosis is associated with chronic inflammatory conditions such as Rheumatoid Arthritis or Crohn\'s disease?', 'AA (Secondary) Amyloidosis, driven by Serum Amyloid A (SAA).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 18, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(401, 23, 1, 'In a patient with Multiple Myeloma, what does a positive urine dipstick for protein suggest as compared to a negative dipstick in the setting of renal failure?', 'A positive dipstick suggests AL Amyloidosis (glomerular involvement), as compared to a negative dipstick which suggests Myeloma Cast Nephropathy (light chains).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 19, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(402, 23, 1, 'What is the causative protein in Dialysis-Associated Amyloidosis presenting as carpal tunnel syndrome?', '$\\beta_2$-microglobulin.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 20, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(403, 23, 1, 'What characteristic finding is observed when a renal biopsy of an Amyloidosis patient is stained with Congo Red and viewed under polarised light?', 'Apple-green birefringence.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 21, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(404, 23, 1, 'Which protein secondary structure is characteristic of all amyloid deposits?', 'Beta Pleated Sheat', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 22, 5, '2026-07-09 09:55:30', '2026-07-09 09:56:27'),
(405, 23, 1, 'How do you distinguish Amyloidosis from Diabetic Nephropathy on Light Microscopy when both show pink (eosinophilic) deposits?', 'Amyloidosis shows apple-green birefringence with Congo Red, as compared to Diabetic Nephropathy which shows Kimmelstiel-Wilson nodules and is Congo Red negative.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 23, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(406, 23, 1, 'What is the next best step in management for a 5-year-old child with sudden-onset periorbital oedema and 4+ proteinuria?', 'Empiric corticosteroid therapy (no initial biopsy required).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 24, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(407, 23, 1, 'What is the definitive diagnostic test for Focal Segmental Glomerulosclerosis in an adult?', 'Renal biopsy (showing segmental sclerosis on Light Microscopy).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 25, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(408, 23, 1, 'Why are patients with nephrotic syndrome at increased risk for infections such as Streptococcus pneumoniae?', 'Due to the urinary loss of gamma globulins (immunoglobulins).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 26, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(409, 23, 1, 'In Membranous Nephropathy, where exactly are the immune complexes deposited in relation to the Glomerular Basement Membrane (GBM)?', 'In the subepithelial space (between the GBM and podocytes).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 27, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(410, 23, 1, 'Which nephrotic syndrome is classically associated with the use of Non-Steroidal Anti-Inflammatory Drugs (NSAIDs)?', 'Minimal Change Disease (and occasionally Membranous Nephropathy).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 28, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(411, 23, 1, 'A patient with long-standing Bronchiectasis develops nephrotic syndrome; what is the most likely protein depositing in the mesangium?', 'Serum Amyloid A (SAA) resulting in AA Amyloidosis.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 29, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(412, 23, 1, 'Which diagnostic marker is highly specific for primary Membranous Nephropathy and can sometimes obviate the need for biopsy?', 'Anti-PLA2R (Phospholipase A_2 Receptor) antibody titre.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 30, 5, '2026-07-09 09:55:30', '2026-07-09 09:56:45'),
(413, 23, 1, 'In the context of USMLE vignettes, what does \'frothy\' or \'foamy\' urine signify?', 'Severe proteinuria/albuminuria (nephrotic range).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 31, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(414, 23, 1, 'A 30-year-old female with SLE has thickened capillary loops on LM and 4+ proteinuria; which WHO class of Lupus Nephritis is this?', 'Class V Lupus Nephritis (Membranous Nephropathy).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 32, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(415, 23, 1, 'What is the primary treatment for AA Amyloidosis?', 'Management of the underlying chronic inflammatory condition (e.g. RA or IBD).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 33, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(416, 23, 1, 'Which nephrotic syndrome is most common in patients with a history of morbid obesity?', 'Focal Segmental Glomerulosclerosis (FSGS).', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 34, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(417, 23, 1, 'How does the prognosis of the \'Tip Variant\' of FSGS compare to the \'Collapsing Variant\'?', 'The Tip Variant has the best prognosis, as compared to the Collapsing Variant which has the poorest prognosis.', 'Nephrotic Syndrome Flashcards', 'moderate', 'manual', 'approved', 35, 5, '2026-07-09 09:55:30', '2026-07-09 09:55:30'),
(418, 24, 1, 'In Diffuse Proliferative Glomerulonephritis (DPGN), which specific histological finding is seen on light microscopy as a result of capillary wall thickening?', '\"Wire-loop\" capillaries.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 1, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(419, 24, 1, 'Where are the immune complexes located on electron microscopy in DPGN, as compared to Membranous Nephropathy?', 'Subendothelial space (DPGN), as compared to the subepithelial space (Membranous Nephropathy).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 2, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(420, 24, 1, 'What immunofluorescence pattern is described as a \"Full House\" in patients with Lupus Nephritis (DPGN)?', 'Granular deposits of IgG, IgA, IgM, C3, and C1q.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 3, 5, '2026-07-09 11:14:27', '2026-07-09 11:16:41'),
(421, 24, 1, 'What is the most likely renal diagnosis in a patient with systemic lupus erythematosus presenting with gross haematuria and a \"nephritic-nephrotic\" hybrid syndrome?', 'Diffuse Proliferative Glomerulonephritis (DPGN).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 4, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(422, 24, 1, 'IgA Nephropathy typically presents with gross haematuria _____ days after an upper respiratory tract infection.', '1–3 days.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 5, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(423, 24, 1, 'How do serum C3 levels differ in IgA Nephropathy as compared to Post-Streptococcal Glomerulonephritis (PSGN)?', 'Serum C3 levels are normal in IgA Nephropathy, as compared to low in PSGN.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 6, 5, '2026-07-09 11:14:27', '2026-07-09 11:16:59'),
(424, 24, 1, 'Which part of the glomerulus shows granular deposits and expansion in IgA Nephropathy?', 'The mesangium.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 7, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(425, 24, 1, 'In the context of the \"gut-kidney\" connection, which malabsorptive disorder is classically associated with IgA Nephropathy?', 'Coeliac disease.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 8, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(426, 24, 1, 'What is the initial pharmacological step to manage hypertension and proteinuria in stable IgA Nephropathy?', 'ACE inhibitors or Angiotensin II Receptor Blockers (ARBs).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 9, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(427, 24, 1, 'Which pathogen is the most common cause of adult-onset Infection-Related Glomerulonephritis (IRGN)?', 'Staph Aureus', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 10, 5, '2026-07-09 11:14:27', '2026-07-09 11:17:14'),
(428, 24, 1, 'How does the incubation period of adult IRGN differ from paediatric PSGN?', 'IRGN often occurs concurrently with the active infection, as compared to PSGN which has a 1–3 week latent period.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 11, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(429, 24, 1, 'What is the pathognomonic finding on electron microscopy for both PSGN and IRGN?', 'Subepithelial humps (Lumpy Bumpy appearance).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 12, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(430, 24, 1, 'In Membranoproliferative Glomerulonephritis (MPGN), what causes the \"tram-track\" appearance on light microscopy?', 'Splitting of the glomerular basement membrane due to new basement membrane synthesis.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 13, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(431, 24, 1, 'Which systemic viral infection is most strongly associated with Type 1 Membranoproliferative Glomerulonephritis?', 'Hepatitis C.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 14, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(432, 24, 1, 'What is the underlying immunological trigger for MPGN Type 2 (Dense Deposit Disease)?', 'C3 Nephritic Factor (an antibody that stabilizes C3 convertase).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 15, 5, '2026-07-09 11:14:27', '2026-07-09 11:18:40'),
(433, 24, 1, 'Which physical examination finding involving the face is associated with MPGN Type 2?', 'Partial lipodystrophy (loss of fat from the face).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 16, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(434, 24, 1, 'What is the expected latent period between a streptococcal skin infection (impetigo) and the onset of PSGN?', '3–6 weeks.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 17, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(435, 24, 1, 'In a paediatric patient with PSGN, what is the immunofluorescence pattern of IgG and C3 deposits?', '\"Starry Sky\" or granular pattern.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 18, 5, '2026-07-09 11:14:27', '2026-07-09 11:19:04'),
(436, 24, 1, 'What is the definitive histological hallmark required to diagnose Rapidly Progressive Glomerulonephritis (RPGN)?', 'Fibrin crescents in the Bowman\'s space.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 19, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(437, 24, 1, 'Which cell types proliferate to form the crescent shape in RPGN?', 'Parietal epithelial cells and macrophages.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 20, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(438, 24, 1, 'Type 1 RPGN (Anti-GBM disease) is characterised by antibodies directed against which specific structure?', 'The alpha 3 chain of Type IV Collagen', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 21, 5, '2026-07-09 11:14:27', '2026-07-09 11:20:14'),
(439, 24, 1, 'How does the immunofluorescence pattern of Type 1 RPGN compare to Type 2 RPGN?', 'Type 1 shows a linear pattern, as compared to the granular pattern seen in Type 2.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 22, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(440, 24, 1, 'Which clinical syndrome involves both pulmonary haemorrhage (haemoptysis) and Type 1 RPGN?', 'Goodpasture Syndrome.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 23, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(441, 24, 1, 'What is the most likely diagnosis for a patient with rapidly rising creatinine, haematuria, and positive c-ANCA with a history of chronic sinusitis?', 'Granulomatosis with Polyangiitis (Wegener\'s).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 24, 5, '2026-07-09 11:14:27', '2026-07-09 11:19:49'),
(442, 24, 1, 'Type 3 RPGN is also known as _____ because immunofluorescence shows little to no antibody deposition.', 'Pauci-immune glomerulonephritis', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 25, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(443, 24, 1, 'What is the immediate mainstay pharmacological treatment for a patient suspected of having RPGN?', 'High-dose intravenous methylprednisolone (Pulse steroids).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 26, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(444, 24, 1, 'In the management of Goodpasture Syndrome, what procedure is used to physically remove circulating anti-GBM antibodies?', 'Plasmapheresis (Plasma exchange).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 27, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(445, 24, 1, 'A patient presents with haematuria and sensorineural hearing loss; how does this differ from Goodpasture Syndrome?', 'This suggests Alport Syndrome (genetic Type IV mutation), as compared to Goodpasture (antibodies against Type IV).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 28, 5, '2026-07-09 11:14:27', '2026-07-09 11:16:14'),
(446, 24, 1, 'What is the typical prognosis of PSGN in children as compared to adult-onset IRGN?', 'Excellent/self-limiting in children, as compared to poor/frequently progressing to chronic kidney disease in adults.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 29, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(447, 24, 1, 'Which diagnostic lab result would differentiate Wegener’s (GPA) from Goodpasture Syndrome in a patient with pulmonary-renal syndrome?', 'Positive ANCA (GPA), as compared to positive anti-GBM antibodies (Goodpasture).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 30, 5, '2026-07-09 11:14:27', '2026-07-09 11:19:30'),
(448, 24, 1, 'In a patient with known SLE, how would you distinguish DPGN from Membranous Lupus Nephritis based on urinalysis?', 'DPGN shows an active sediment with RBC casts, as compared to Membranous which shows isolated heavy proteinuria without casts.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 31, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(449, 24, 1, 'What is the definitive diagnostic step for an adult presenting with acute nephritic syndrome to guide treatment intensity?', 'Renal biopsy.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 32, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(450, 24, 1, 'Henoch-Schönlein Purpura (HSP) is essentially the systemic version of which primary glomerulopathy?', 'IgA Nephropathy.', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 33, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(451, 24, 1, 'Which type of hypersensitivity reaction drives the pathophysiology of Post-Streptococcal Glomerulonephritis?', 'Type III hypersensitivity (Immune complex-mediated).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 34, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(452, 24, 1, 'On light microscopy, what is the primary differentiator between PSGN and RPGN?', 'Diffuse hypercellularity (PSGN), as compared to the presence of extracapillary crescents (RPGN).', 'Nephritic Syndrome Flashcards', 'moderate', 'manual', 'approved', 35, 5, '2026-07-09 11:14:27', '2026-07-09 11:14:27'),
(453, 25, 1, 'In which cellular compartment does the glycolysis pathway occur?', 'The cytoplasm.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 1, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(454, 25, 1, 'What is the primary physiological purpose of phosphorylating glucose to Glucose-6-phosphate during the first step of glycolysis?', 'To trap glucose inside the cell by preventing it from diffusing out through transporters.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 2, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(455, 25, 1, 'Which glucose transporter (GLUT) is responsible for the \'two-way\' movement of glucose in the liver and pancreas?', 'GLUT 2.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 3, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(456, 25, 1, 'Which GLUT transporters are primarily found in the brain and red blood cells (RBCs)?', 'GLUT 1.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 4, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(457, 25, 1, 'Which glucose transporter is specific for the uptake of fructose?', 'GLUT 5.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 5, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(458, 25, 1, 'How does the $K_{m}$ of Hexokinase compare to the $K_{m}$ of Glucokinase?', 'Hexokinase has a much lower $K_{m}$ (higher affinity) than Glucokinase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 6, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(459, 25, 1, 'Which enzyme acts as a \'glucose sensor\' in pancreatic $\\beta$ cells and the liver?', 'Glucokinase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 7, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(460, 25, 1, 'Between Hexokinase and Glucokinase, which enzyme has a higher $V_{max}$?', 'Glucokinase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 8, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(461, 25, 1, 'Which hormone specifically regulates the levels of Glucokinase in the body?', 'Insulin.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 9, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(462, 25, 1, 'What enzyme catalyzes the rate-limiting step of glycolysis?', 'Phosphofructokinase-1 (PFK-1).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 10, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(463, 25, 1, 'Which molecule is the most potent allosteric activator of Phosphofructokinase-1 (PFK-1)?', 'Fructose-2,6-bisphosphate.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 11, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(464, 25, 1, 'How do high levels of ATP and Citrate affect the activity of Phosphofructokinase-1 (PFK-1)?', 'They act as allosteric inhibitors (negative feedback).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 12, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(465, 25, 1, 'Identify the three irreversible enzymes of the glycolysis pathway.', 'Glucokinase/Hexokinase, Phosphofructokinase-1, and Pyruvate Kinase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 13, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(466, 25, 1, 'What are the four primary metabolic \'fates\' of pyruvate mentioned in the text?', 'Alanine, Lactate, Oxaloacetate, and Acetyl-CoA.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 14, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(467, 25, 1, 'Which enzyme converts pyruvate into lactate during anaerobic glycolysis?', 'Lactate Dehydrogenase (LDH).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 15, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(468, 25, 1, 'List three specific tissues that rely on anaerobic glycolysis for energy.', 'Red blood cells (RBCs), lens/cornea of the eye, and the kidney medulla.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 16, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(469, 25, 1, 'What is the clinical significance of 2,3-Bisphosphoglycerate (2,3-BPG) in RBCs?', 'It shifts the haemoglobin-oxygen dissociation curve to the right, promoting oxygen release to tissues.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 17, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(470, 25, 1, 'From which glycolytic intermediate is 2,3-Bisphosphoglycerate (2,3-BPG) synthesized?', '1,3-Bisphosphoglycerate.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 18, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(471, 25, 1, 'What is the term for the metabolic pathway that generates glucose from non-carbohydrate precursors like pyruvate?', 'Gluconeogenesis.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 19, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(472, 25, 1, 'Which enzyme catalyzes the first step of gluconeogenesis by converting pyruvate to oxaloacetate?', 'Pyruvate Carboxylase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 20, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(473, 25, 1, 'What essential cofactor is required by Pyruvate Carboxylase for its activity?', 'Biotin (Vitamin $B_{7}$).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 21, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(474, 25, 1, 'The consumption of raw egg whites can cause a deficiency in Biotin due to the presence of which protein?', 'Avidin.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 22, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(475, 25, 1, 'Which gluconeogenic enzyme converts Oxaloacetate into Phosphoenolpyruvate (PEP)?', 'Phosphoenolpyruvate carboxykinase (PEPCK).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 23, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(476, 25, 1, 'Which enzyme bypasses the PFK-1 step in gluconeogenesis by converting Fructose-1,6-bisphosphate back to Fructose-6-phosphate?', 'Fructose-1,6-bisphosphatase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 24, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(477, 25, 1, 'Where is the enzyme Glucose-6-phosphatase localized within the cell?', 'The Endoplasmic Reticulum (ER).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 25, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(478, 25, 1, 'Which hormone promotes gluconeogenesis and inhibits glycolysis during fasting states?', 'Glucagon.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 26, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(479, 25, 1, 'What are the two primary products generated by the Hexose Monophosphate (HMP) Shunt?', 'NADPH and Ribose-5-phosphate.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 27, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(480, 25, 1, 'What is the rate-limiting enzyme of the oxidative (irreversible) phase of the HMP Shunt?', 'Glucose-6-phosphate Dehydrogenase (G6PD).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 28, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(481, 25, 1, 'Which enzyme in the non-oxidative phase of the HMP Shunt requires Thiamine ($B_{1}$) as a cofactor?', 'Transketolase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 29, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(482, 25, 1, 'How is NADPH utilized to protect cells from oxidative stress?', 'It provides the reducing power to regenerate reduced glutathione, which neutralizes free radicals like $H_{2}O_{2}$.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 30, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(483, 25, 1, 'What are \'Heinz bodies\' in the context of G6PD deficiency?', 'Precipitated haemoglobin inclusions within RBCs caused by oxidative damage.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 31, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(484, 25, 1, 'Which clinical condition is characterized by a deficiency in the NADPH Oxidase enzyme in phagocytes?', 'Chronic Granulomatous Disease (CGD).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 32, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(485, 25, 1, 'Patients with Chronic Granulomatous Disease are particularly susceptible to infections by which category of bacteria?', 'Catalase-positive organisms (e.g., $Staphylococcus$ $aureus$).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 33, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(486, 25, 1, 'What is the role of the \'Respiratory Burst\' in white blood cells?', 'The production of reactive oxygen species (like superoxide and $H_{2}O_{2}$) to kill phagocytosed bacteria.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 34, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(487, 25, 1, 'Why does a deficiency in Pyruvate Dehydrogenase lead to lactic acidosis?', 'Pyruvate cannot enter the TCA cycle as Acetyl-CoA and is instead shunted toward lactate production.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 35, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(488, 25, 1, 'In the liver, how does Acetyl-CoA regulate Pyruvate Carboxylase?', 'It acts as a positive allosteric activator, promoting gluconeogenesis.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 36, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(489, 25, 1, 'Which pathway provides Ribose-5-phosphate for nucleotide and DNA synthesis?', 'The HMP Shunt (Pentose Phosphate Pathway).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 37, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(490, 25, 1, 'Explain the concept of \'Anion Gap\' in the context of metabolic acidosis.', 'It is the difference between measured cations (Sodium) and measured anions (Chloride + Bicarbonate).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 38, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(491, 25, 1, 'What clinical triad of symptoms is typically seen in Diabetic Ketoacidosis (DKA)?', 'Hyperglycaemia, metabolic acidosis (low pH), and elevated serum ketones.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 39, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(492, 25, 1, 'Which tissue lacks the enzyme Glucose-6-phosphatase, preventing it from releasing free glucose into the blood?', 'Muscle tissue.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 40, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(493, 25, 1, 'What is the mnemonic \'MUDPILES\' used for in medical biochemistry?', 'To remember the causes of high anion gap metabolic acidosis.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 41, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(494, 25, 1, 'How does glucagon affect the phosphorylation state of bifunctional enzyme PFK-2/FBPase-2?', 'Glucagon increases cAMP, activating Protein Kinase A which phosphorylates the enzyme.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 42, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(495, 25, 1, 'Does the phosphorylation of glycolytic enzymes generally activate or inactivate them?', 'It generally inactivates them (e.g., Pyruvate Kinase and PFK-2).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 43, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(496, 25, 1, 'Which vitamin deficiency would directly impair the activity of the Transketolase enzyme?', 'Vitamin $B_{1}$ (Thiamine).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 44, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(497, 25, 1, 'What is the specific effect of G6PD deficiency on the lifespan of Red Blood Cells?', 'It shortens it due to increased susceptibility to oxidative damage and subsequent haemolysis.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 45, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(498, 25, 1, 'Why is the HMP shunt particularly active in the adrenal cortex and mammary glands?', 'These tissues require high amounts of NADPH for the reductive biosynthesis of steroid hormones and fatty acids.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 46, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(499, 25, 1, 'What enzyme deficiency results in the inability to convert pyruvate to oxaloacetate, leading to hypoglycaemia and lactic acidosis?', 'Pyruvate Carboxylase deficiency.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 47, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(500, 25, 1, 'Which molecule shifts the oxygen dissociation curve to the left, increasing haemoglobin\'s affinity for oxygen?', 'Haemoglobin F (Foetal Haemoglobin).', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 48, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(501, 25, 1, 'In glycolysis, which enzyme is inhibited by its product, Glucose-6-phosphate?', 'Hexokinase.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 49, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51'),
(502, 25, 1, 'What is the primary function of the enzyme Glutathione Peroxidase?', 'To use reduced glutathione to convert hydrogen peroxide ($H_{2}O_{2}$) into water.', 'notebooklm flashcards', 'moderate', 'manual', 'approved', 50, 5, '2026-07-10 12:40:51', '2026-07-10 12:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `free_resources`
--

CREATE TABLE `free_resources` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('video','pdf','link','article') DEFAULT 'video',
  `file_path` varchar(500) DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `free_resources`
--

INSERT INTO `free_resources` (`id`, `title`, `description`, `type`, `file_path`, `external_url`, `thumbnail`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Introduction to Medical Terminology', 'Free introductory lecture on medical terminology.', 'video', NULL, 'https://example.com/video1', NULL, 1, 1, '2026-07-02 12:24:21'),
(2, 'Study Guide: Cardiovascular System', 'Downloadable PDF study guide.', 'pdf', NULL, NULL, NULL, 1, 2, '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `lectures`
--

CREATE TABLE `lectures` (
  `id` int(10) UNSIGNED NOT NULL,
  `chapter_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content_type` enum('video','document','mixed','live') DEFAULT 'mixed',
  `duration_minutes` int(10) UNSIGNED DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 0,
  `is_free_preview` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lectures`
--

INSERT INTO `lectures` (`id`, `chapter_id`, `title`, `description`, `content_type`, `duration_minutes`, `sort_order`, `is_published`, `is_free_preview`, `created_at`, `updated_at`) VALUES
(16, 16, 'All Renal Pathology Lecture Recordings - 29th June to 6th July', NULL, 'mixed', 0, 0, 0, 0, '2026-07-08 11:10:08', '2026-07-09 08:02:21'),
(17, 17, 'Staph Aureus, Staph Epidermidis + Saprophyticus and Streptococcus Pneumoniae', NULL, 'mixed', 0, 0, 0, 0, '2026-07-08 13:08:33', '2026-07-08 13:08:33'),
(18, 17, 'Group A and Group Streptococcus and Enterococcus', NULL, 'mixed', 0, 0, 0, 0, '2026-07-08 13:24:03', '2026-07-08 13:24:03'),
(19, 18, 'Lec#1 : Bones, Joints and Surface Anatomy of Upper Limb 2-7-2026', NULL, 'mixed', 0, 0, 0, 0, '2026-07-09 05:06:02', '2026-07-09 05:06:02'),
(20, 18, 'Lecture #2 Nerves of upper limb + clinical Cases 3-7-2026', NULL, 'mixed', 0, 0, 0, 0, '2026-07-09 05:11:40', '2026-07-09 05:38:06'),
(21, 18, 'Lec#3 Brachial plexus and vasculature of upper limb  4-7-2026', NULL, 'mixed', 0, 0, 0, 0, '2026-07-09 05:37:17', '2026-07-09 05:37:39'),
(22, 16, 'Renal Stone Slides', NULL, 'mixed', 0, 0, 0, 0, '2026-07-09 08:03:30', '2026-07-09 08:03:30'),
(23, 16, 'Nephrotic Syndrome Slides', NULL, 'mixed', 0, 0, 0, 0, '2026-07-09 09:51:55', '2026-07-09 09:51:55'),
(24, 16, 'Nephritic Syndromes', NULL, 'mixed', 0, 0, 0, 0, '2026-07-09 11:00:16', '2026-07-09 11:00:16'),
(25, 19, 'Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway', NULL, 'mixed', 0, 0, 0, 0, '2026-07-10 11:57:42', '2026-07-10 11:57:42'),
(26, 20, 'Diuretic - 8 July 2026', NULL, 'mixed', 0, 0, 0, 0, '2026-07-11 03:52:05', '2026-07-11 03:52:05'),
(27, 21, 'Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)', NULL, 'mixed', 0, 0, 0, 0, '2026-07-11 04:19:39', '2026-07-11 04:19:39'),
(28, 21, 'Diabetes Insipidus and Hypoparathyroidism - 10 July 2026 (Friday)', NULL, 'mixed', 0, 0, 0, 0, '2026-07-11 04:20:17', '2026-07-11 04:20:17'),
(29, 18, 'Vasculature and Lymphatics of Upper limb (10-7-2026)', NULL, 'mixed', 0, 0, 0, 0, '2026-07-11 12:30:24', '2026-07-11 12:30:24'),
(30, 22, 'Pelvis', NULL, 'mixed', 0, 0, 0, 0, '2026-07-11 12:50:14', '2026-07-11 12:50:14'),
(31, 19, 'Carbohydrate Metabolism - Kreb Cycle, ETC, Lactose + Fructose + Galactose Metabolisms', NULL, 'mixed', 0, 0, 0, 0, '2026-07-13 06:40:06', '2026-07-13 06:40:06');

-- --------------------------------------------------------

--
-- Table structure for table `lecture_progress`
--

CREATE TABLE `lecture_progress` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `watch_time_seconds` int(10) UNSIGNED DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lecture_resources`
--

CREATE TABLE `lecture_resources` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `type` enum('video','pdf','slides','download','link','reference') NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lecture_resources`
--

INSERT INTO `lecture_resources` (`id`, `lecture_id`, `type`, `title`, `file_path`, `external_url`, `mime_type`, `file_size`, `sort_order`, `uploaded_by`, `created_at`) VALUES
(69, 16, 'video', 'Video 001', 'uploads/courses/1/lectures/2a17d0c672209aaf3f6b8a9c27418f0a.mp4', NULL, 'video/mp4', 51456169, 0, 5, '2026-07-08 11:13:14'),
(71, 16, 'video', 'Video 002', 'uploads/courses/1/lectures/a49b13a1f9c47f95ae99923ffd57adee.mp4', NULL, 'video/mp4', 88609009, 0, 5, '2026-07-08 11:34:01'),
(73, 16, 'video', 'Video 003', 'uploads/courses/1/lectures/513ece0d551dfd4b7fa4704b9ba5fb0c.mp4', NULL, 'video/mp4', 81386635, 0, 5, '2026-07-08 11:59:06'),
(74, 16, 'video', 'Video 004', 'uploads/courses/1/lectures/63c022cbceea81d7d3b783b6c8f4a3a9.mp4', NULL, 'video/mp4', 102160005, 0, 5, '2026-07-08 12:05:13'),
(75, 16, 'video', 'Video 005', 'uploads/courses/1/lectures/6401b7f760a3da59f870a64b8eee07fd.mp4', NULL, 'video/mp4', 86519522, 0, 5, '2026-07-08 12:07:34'),
(76, 16, 'video', 'Video 006', 'uploads/courses/1/lectures/6f0043974dfc7d5ab5f75ad7f7707980.mp4', NULL, 'video/mp4', 75712778, 0, 5, '2026-07-08 12:09:03'),
(77, 16, 'video', 'Video 007', 'uploads/courses/1/lectures/15f30ab80fc0ad22c94e4b951f7dbeb5.mp4', NULL, 'video/mp4', 99925884, 0, 5, '2026-07-08 12:14:09'),
(78, 16, 'video', 'Video 008', 'uploads/courses/1/lectures/8b08ce9baacd302e5ca5627559f17382.mp4', NULL, 'video/mp4', 52570695, 0, 5, '2026-07-08 12:16:50'),
(79, 16, 'video', 'Video 009', 'uploads/courses/1/lectures/8926c5dec1942871819851a48abda39a.mp4', NULL, 'video/mp4', 87469623, 0, 5, '2026-07-08 12:19:16'),
(90, 17, 'video', 'Lecture 001', 'uploads/courses/1/lectures/b1b3f25bb105dfe9f3f60c653c2b1e1b.mp4', NULL, 'video/mp4', 17927942, 0, 5, '2026-07-08 13:09:21'),
(91, 17, 'video', 'Lecture 002', 'uploads/courses/1/lectures/194c0a51a8d431be48aaf156edcf7d76.mp4', NULL, 'video/mp4', 65287709, 0, 5, '2026-07-08 13:13:45'),
(92, 18, 'video', 'Micro - Strep Group A, Group B and Enterococcus', 'uploads/courses/1/lectures/45d5cc9f5eee668821cc2f4b87f4373e.mp4', NULL, 'video/mp4', 99085456, 0, 5, '2026-07-08 13:27:29'),
(93, 19, 'video', 'Lec#1 : Bones, Joints and Surface Anatomy of Upper Limb', NULL, 'https://www.loom.com/share/afa317491fb14963b53e7f34445927fb', NULL, NULL, 0, 6, '2026-07-09 05:07:01'),
(94, 19, 'slides', 'Upper_Limb_Anatomy_FCPS_Part1 (1)', 'uploads/courses/1/lectures/65b00d14ac852f622b1513b66f23770e.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 16212490, 0, 6, '2026-07-09 05:10:57'),
(95, 20, 'slides', 'Upper_Limb_Clinical_Blueprint', 'uploads/courses/1/lectures/f1c11e88f0a80e13102a4f938f3fb5dc.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 16496460, 0, 6, '2026-07-09 05:17:51'),
(96, 20, 'video', 'WhatsApp Video 2026-07-08 at 11.27.16 PM', 'uploads/courses/1/lectures/c1516d31e282603ae5d4cea83f1138a3.mp4', NULL, 'video/mp4', 92209639, 0, 6, '2026-07-09 05:35:19'),
(97, 21, 'pdf', 'Brachial_Plexus_Clinical_Schematic', 'uploads/courses/1/lectures/6e0f584b2fde9d4b18a18a02feb0b3bb.pdf', NULL, 'application/pdf', 16853580, 0, 6, '2026-07-09 06:12:58'),
(98, 21, 'slides', 'Upper_Limb_Clinical_Anatomy_Blueprint', 'uploads/courses/1/lectures/fc1a6a77c3609178819267aa577466a1.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 17648190, 0, 6, '2026-07-09 06:16:19'),
(99, 22, 'slides', 'Kidney Stones', 'uploads/courses/1/lectures/f5b8096067570265489d9f6a0cac82ea.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 29173861, 0, 5, '2026-07-09 08:05:03'),
(100, 23, 'slides', 'Nephrotic Syndromes', 'uploads/courses/1/lectures/98c66f1235fbcfed4bdd4f4e8979aee0.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 18497977, 0, 5, '2026-07-09 09:52:49'),
(101, 24, 'slides', 'Nephritic Syndromes', 'uploads/courses/1/lectures/439552ecd916ce4e869064e5de637ea9.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 28615106, 0, 5, '2026-07-09 11:01:53'),
(102, 24, 'slides', 'Proliferative Glomerulonephritis', 'uploads/courses/1/lectures/89e131ade94dc77fe44e512053298b36.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 24861990, 0, 5, '2026-07-09 11:03:20'),
(103, 25, 'video', 'Lecture 001', 'uploads/courses/1/lectures/cc5319e107e04e421ccdeba595a71f3a.mp4', NULL, 'video/mp4', 31729122, 0, 5, '2026-07-10 11:59:55'),
(104, 25, 'video', 'Lecture 002', 'uploads/courses/1/lectures/510bf67e5d843e14dcbc5e868391a6b5.mp4', NULL, 'video/mp4', 55428378, 0, 5, '2026-07-10 12:00:39'),
(105, 25, 'video', 'Lecture 003', 'uploads/courses/1/lectures/7cb789023847d756305689576b0e30be.mp4', NULL, 'video/mp4', 78240786, 0, 5, '2026-07-10 12:02:04'),
(106, 25, 'video', 'Lecture 004', 'uploads/courses/1/lectures/24251035cb8aa524691f89bf96d222a8.mp4', NULL, 'video/mp4', 40910209, 0, 5, '2026-07-10 12:02:38'),
(107, 25, 'pdf', 'Comprehensive Briefing on Carbohydrate Metabolism_ Glycolysis, Gluconeogenesis, and the HMP Shunt', 'uploads/courses/1/lectures/4dac9c0d7e22fb71c3cfa49ee1b7450c.pdf', NULL, 'application/pdf', 121513, 0, 5, '2026-07-10 12:25:58'),
(108, 26, 'video', 'Diuretic Pharmacology', 'uploads/courses/1/lectures/57531f32a6d186d9459b93c8de197112.mp4', NULL, 'video/mp4', 97499747, 0, 5, '2026-07-11 03:54:39'),
(109, 27, 'video', 'Video 001', 'uploads/courses/1/lectures/51978ed18beced29b990afc2a815bc6a.mp4', NULL, 'video/mp4', 48663923, 0, 5, '2026-07-11 04:22:03'),
(110, 27, 'video', 'Video 002', 'uploads/courses/1/lectures/a1880dbc6bdd131dd54079a4546d3b8c.mp4', NULL, 'video/mp4', 20447200, 0, 5, '2026-07-11 04:22:43'),
(111, 27, 'slides', 'PHEOCHROMOCYTOMA SLIDES', 'uploads/courses/1/lectures/058a937f7d5d9015cbc2bf8530e857cc.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 15137900, 0, 5, '2026-07-11 04:46:11'),
(112, 27, 'slides', 'SIADH SLIDES', 'uploads/courses/1/lectures/ef0b261463f8e791ea7e44b771ab0519.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 10567204, 0, 5, '2026-07-11 04:46:33'),
(113, 28, 'video', 'Video 001', 'uploads/courses/1/lectures/898504c412846c80da8e8ce4f69fd7aa.mp4', NULL, 'video/mp4', 30057204, 0, 5, '2026-07-11 05:47:39'),
(114, 28, 'video', 'Video 002', 'uploads/courses/1/lectures/a36b09bc27bf24af61e6f21186574a98.mp4', NULL, 'video/mp4', 75307257, 0, 5, '2026-07-11 05:48:39'),
(115, 28, 'slides', 'Diabetes Insipidus and Polydipsia', 'uploads/courses/1/lectures/84680a1e35b267822b6f585a327aaa12.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 18447526, 0, 5, '2026-07-11 05:50:40'),
(116, 28, 'slides', 'Hypoparathyroidism', 'uploads/courses/1/lectures/032a1c7071c9322e12a71e007a82a089.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 21364483, 0, 5, '2026-07-11 05:51:08'),
(117, 29, 'video', 'video1946540867', 'uploads/courses/1/lectures/8decaa568c0aed15dafa03fb4a8ef9ea.mp4', NULL, 'video/mp4', 85886678, 0, 6, '2026-07-11 12:33:44'),
(118, 29, 'pdf', 'Upper_Limb_Architecture', 'uploads/courses/1/lectures/5c1e458ba2fd2b241d9f886b65587c52.pdf', NULL, 'application/pdf', 13997970, 0, 6, '2026-07-11 12:34:30'),
(119, 30, 'pdf', 'Lower_Limb_Structural_Blueprint final', 'uploads/courses/1/lectures/ed43556e5cfa174d4fb3705103243873.pdf', NULL, 'application/pdf', 17248244, 0, 6, '2026-07-11 12:51:19'),
(120, 30, 'slides', 'Lower_Limb_Structural_Blueprint', 'uploads/courses/1/lectures/ba3cb5aa6846b6dc1bf009816ea363b3.pptx', NULL, 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 19949399, 0, 6, '2026-07-11 13:11:14'),
(121, 31, 'video', 'Video 001', 'uploads/courses/1/lectures/5e57e66fe04a9b995777a715e5c8e908.mp4', NULL, 'video/mp4', 45153575, 0, 5, '2026-07-13 06:41:23'),
(122, 31, 'video', 'Video 002', 'uploads/courses/1/lectures/7cda2ec7467ee4102bf6399226e19572.mp4', NULL, 'video/mp4', 73207203, 0, 5, '2026-07-13 06:44:58'),
(123, 31, 'video', 'Video 003', 'uploads/courses/1/lectures/b3c7c431f77edc31625b95ed8cfd69b1.mp4', NULL, 'video/mp4', 52795277, 0, 5, '2026-07-13 06:48:03'),
(125, 26, 'pdf', 'Mastering Diuretics_ A Mnemonic Guide to Side Effects and Physiology', 'uploads/courses/1/lectures/65d29350b6f533efd7622a600b3bf760.pdf', NULL, 'application/pdf', 139479, 0, 5, '2026-07-13 10:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `live_sessions`
--

CREATE TABLE `live_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meeting_url` varchar(500) DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT 60,
  `status` enum('scheduled','live','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `live_sessions`
--

INSERT INTO `live_sessions` (`id`, `course_id`, `teacher_id`, `title`, `description`, `meeting_url`, `scheduled_at`, `duration_minutes`, `status`, `created_at`) VALUES
(1, 1, 5, 'Live Class', NULL, 'https://us06web.zoom.us/j/82791342083?pwd=te2VyG5OYGszjjxwnmOWejQMwFSHIA.1', '2026-07-21 16:40:00', 120, 'scheduled', '2026-07-08 11:41:18');

-- --------------------------------------------------------

--
-- Table structure for table `mcqs`
--

CREATE TABLE `mcqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `question` text NOT NULL,
  `option_a` text NOT NULL,
  `option_b` text NOT NULL,
  `option_c` text DEFAULT NULL,
  `option_d` text DEFAULT NULL,
  `option_e` text DEFAULT NULL,
  `correct_option` enum('A','B','C','D','E') NOT NULL,
  `explanation` text DEFAULT NULL,
  `option_explanations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`option_explanations`)),
  `topic` varchar(255) DEFAULT NULL,
  `difficulty` enum('easy','moderate','difficult') NOT NULL DEFAULT 'moderate',
  `source` enum('ai','manual') NOT NULL DEFAULT 'ai',
  `status` enum('draft','approved','published') NOT NULL DEFAULT 'draft',
  `sort_order` int(11) DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mcqs`
--

INSERT INTO `mcqs` (`id`, `lecture_id`, `course_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `option_e`, `correct_option`, `explanation`, `option_explanations`, `topic`, `difficulty`, `source`, `status`, `sort_order`, `created_by`, `created_at`, `updated_at`) VALUES
(93, 23, 1, 'A 34-year-old African American man with a history of obesity (BMI ) presents with progressive swelling of his ankles and face. A 24-hour urine collection reveals of protein. Laboratory studies show a serum creatinine of and a serum albumin of . Which of the following genetic factors is most strongly associated with this patient\'s likely renal pathology?\n? Hint: Consider the specific risk factor mentioned in the source for populations of sub-Saharan African descent.', 'APOL1 gene variant', 'HLAB27 positivity', 'PKD1 mutation', 'PLA2R autoantibodies', 'CFH mutation', 'A', 'This gene variant is highly prevalent in individuals of sub-Saharan African descent and is a major risk factor for Focal Segmental Glomerulosclerosis.', NULL, NULL, 'moderate', 'manual', 'published', 1, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(94, 23, 1, 'A 5-year-old girl presents with sudden-onset periorbital oedema and \'frothy\' urine. Urinalysis shows protein and no blood. She is started on high-dose oral prednisone. After 10 weeks of therapy, her proteinuria remains at and oedema persists. What is the most appropriate next step in the management of this patient?\n? Hint: Think about the standard protocol for a paediatric patient who does not respond to initial first-line therapy.', 'Start intravenous Cyclophosphamide', 'Perform a renal biopsy', 'Check anti-PLA2R antibody titres', 'Increase the dose of Prednisone', 'Add an ACE inhibitor', 'B', 'In children who are steroid-resistant (failing to respond after 4-8 weeks), a biopsy is required to distinguish Minimal Change Disease from Focal Segmental Glomerulosclerosis.', NULL, NULL, 'moderate', 'manual', 'published', 2, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(95, 23, 1, 'A 48-year-old white man with Membranous Nephropathy presents with sudden-onset left-sided flank pain and gross haematuria. His serum creatinine has risen from to over 48 hours. Which of the following is the most likely physiological cause of this acute complication?\n? Hint: Focus on the specific protein loss that leads to a hypercoagulable state in nephrotic patients.', 'T-cell mediated podocyte effacement', 'Deposition of light chain casts', 'Formation of \'Spike and Dome\' patterns', 'Accumulation of subepithelial immune complexes', 'Urinary loss of Antithrombin III', 'E', 'Membranous Nephropathy causes a profound hypercoagulable state due to the loss of anticoagulant proteins, leading to high rates of renal vein thrombosis.', NULL, NULL, 'moderate', 'manual', 'published', 3, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(96, 23, 1, 'A 62-year-old woman with a 20-year history of Rheumatoid Arthritis presents with massive lower extremity oedema and ascites. Ultrasound reveals kidneys that are in length bilaterally. A renal biopsy is performed. Which of the following findings on light microscopy using Congo Red stain would confirm the diagnosis?\n? Hint: Recall the classic optical property associated with amyloid deposits when viewed under specific lighting conditions.', 'Segmental scarring of the glomerular tuft', 'Kimmelstiel-Wilson nodules', 'Subepithelial \'spikes\' along the basement membrane', 'Linear IgG deposits along the GBM', 'Apple-green birefringence under polarised light', 'E', 'This characteristic finding identifies the presence of -pleated sheet amyloid fibrils in the renal parenchyma.', NULL, NULL, 'moderate', 'manual', 'published', 4, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(97, 23, 1, 'A 29-year-old man with a history of intravenous heroin use presents with severe generalized oedema. Laboratory studies show of proteinuria and a creatinine of . If a biopsy reveals a \'collapsing\' variant of the glomerular tuft, which of the following is most likely to be found in his medical history?\n? Hint: The \'collapsing variant\' is the most aggressive form of this disease and is linked to specific viral stressors.', 'Hodgkin lymphoma', 'Hepatitis C infection', 'Type 1 Diabetes Mellitus', 'HIV infection', 'Chronic NSAID use', 'D', 'The collapsing variant of FSGS is highly aggressive and strongly associated with HIV-associated nephropathy (HIVAN).', NULL, NULL, 'moderate', 'manual', 'published', 5, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(98, 23, 1, 'A 32-year-old man with no significant medical history presents with sudden-onset facial swelling. Urinalysis shows protein and fatty casts. Electron microscopy reveals diffuse effacement of podocyte foot processes, but light microscopy and immunofluorescence are normal. What is the primary physiological defect leading to his condition?\n? Hint: This disease is characterized by \'minimal\' changes; consider why albumin specifically is lost if the structure looks normal on LM.', 'Reduced number of functional podocytes', 'Structural splitting of the basement membrane', 'Loss of the glomerular negative charge barrier', 'Mutation in the APOL1 gene', 'Deposition of subendothelial immune complexes', 'C', 'In Minimal Change Disease, the loss of negative charge on the basement membrane allows negatively charged albumin to leak into the bowman space.', NULL, NULL, 'moderate', 'manual', 'published', 6, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(99, 23, 1, 'A 70-year-old man with a 50 pack-year smoking history presents with new-onset nephrotic syndrome. Workup for primary causes is negative. Which of the following is the most likely underlying driver of his renal disease?\n? Hint: Consider the age and smoking history of the patient in the context of secondary causes for Membranous Nephropathy.', 'Abuse of intravenous heroin', 'Congenital podocyte defect', 'Long-standing Rheumatoid Arthritis', 'Chronic Hepatitis B infection', 'Paraneoplastic syndrome from a solid tumour', 'E', 'In elderly patients, Membranous Nephropathy is frequently a paraneoplastic manifestation of an underlying malignancy, such as lung cancer.', NULL, NULL, 'moderate', 'manual', 'published', 7, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(100, 23, 1, 'A patient with Multiple Myeloma presents with renal failure. The urine dipstick is positive for protein. A renal biopsy shows amorphous eosinophilic deposits. Which of the following proteins is the primary constituent of these deposits?\n? Hint: Think about the specific product of plasma cells that is overproduced in Multiple Myeloma.', 'Phospholipase A2 receptor', '-microglobulin', 'Transthyretin', 'Monoclonal light chains', 'Serum Amyloid A', 'D', 'In AL Amyloidosis (primary), immunoglobulin light chains fold into -pleated sheets and deposit in the glomerulus, causing a positive albumin dipstick.', NULL, NULL, 'moderate', 'manual', 'published', 8, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(101, 23, 1, 'A 25-year-old woman with a history of Systemic Lupus Erythematosus (SLE) and a butterfly rash develops massive proteinuria. A renal biopsy shows diffuse thickening of the glomerular capillary loops and granular IgG and C3 deposits on immunofluorescence. What is the most likely WHO classification for her lupus nephritis?\n? Hint: Identify which class of lupus nephritis specifically mimics Membranous Nephropathy.', 'Class II', 'Class V', 'Class I', 'Class VI', 'Class IV', 'B', 'WHO Class V Lupus Nephritis corresponds to Membranous Nephropathy, which presents with subepithelial deposits and nephrotic syndrome.', NULL, NULL, 'moderate', 'manual', 'published', 9, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(102, 23, 1, 'A patient with Sickle Cell Disease presents with periorbital oedema and of proteinuria. Urinalysis shows no red blood cells or casts. Which of the following is the most likely renal diagnosis?\n? Hint: Distinguish between the cause of \'red urine\' and the cause of \'frothy urine\' in a patient with Sickle Cell.', 'Post-streptococcal Glomerulonephritis', 'Renal Papillary Necrosis', 'Focal Segmental Glomerulosclerosis', 'Membranous Nephropathy', 'Minimal Change Disease', 'C', 'FSGS is the classic cause of nephrotic syndrome in patients with Sickle Cell Disease; it presents with proteinuria and no blood.', NULL, NULL, 'moderate', 'manual', 'published', 10, 5, '2026-07-09 09:59:14', '2026-07-09 09:59:14'),
(103, 24, 1, 'A 28-year-old woman with a history of systemic lupus erythematosus (SLE) presents with bilateral ankle oedema and dark urine. Blood pressure is . Laboratory studies show a serum creatinine of (baseline ), significantly decreased and levels, and positive anti-dsDNA antibodies. Urinalysis reveals red blood cell (RBC) casts and proteinuria. A renal biopsy is performed. Which of the following is the most likely finding on electron microscopy?\n? Hint: Consider the specific location of \'wire-loop\' lesions in the most severe form of lupus nephritis.', 'Intramembranous dense ribbon-like deposits', 'Linear deposition along the basement membrane', 'Subepithelial \'lumpy-bumpy\' humps', 'Subendothelial immune complex deposits', 'Thinning and splitting of the glomerular basement membrane', 'D', 'The clinical presentation of SLE, low complements, and nephritic sediment (RBC casts) strongly suggests Diffuse Proliferative Glomerulonephritis (DPGN), which is characterised by subendothelial deposits.', NULL, NULL, 'moderate', 'manual', 'published', 1, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(104, 24, 1, 'A 22-year-old man presents with visible blood in his urine that began yesterday. He currently has a sore throat and a low-grade fever. He recalls a similar episode of red urine six months ago during a bout of gastroenteritis. His blood pressure is and serum levels are within the normal range. Which of the following is the most likely pathological finding on renal biopsy?\n? Hint: The timing relative to the infection (synpharyngitic) and the normal complement level are the primary differentiators.', 'Subepithelial humps on electron microscopy', 'Diffuse effacement of podocyte foot processes', 'Subendothelial \'tram-track\' splitting', 'Fibrin crescents in Bowman\'s space', 'Mesangial deposition', 'E', 'Recurrent macroscopic haematuria occurring concurrently with an upper respiratory infection (synpharyngitic) and normal complement levels is classic for IgA nephropathy.', NULL, NULL, 'moderate', 'manual', 'published', 2, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(105, 24, 1, 'A 65-year-old man with poorly controlled Type 2 diabetes mellitus is hospitalised for a severe Staphylococcus aureus skin infection. Five days into his treatment, his urine output decreases, and his serum creatinine rises from to . Urinalysis shows RBC casts and protein. Serum is low. What is the most appropriate next step to confirm the diagnosis in this adult patient?\n? Hint: In adults with rapid renal failure and active infection, diagnosis must be definitive and immediate.', 'Start high-dose intravenous methylprednisolone', 'Renal biopsy', 'Antistreptolysin O () titre', 'Serum testing', 'Observe and provide supportive care', 'B', 'In adults, Infection-Related Glomerulonephritis (IRGN) often presents as a medical emergency; unlike in children, a biopsy is typically required to confirm the diagnosis and assess severity.', NULL, NULL, 'moderate', 'manual', 'published', 3, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(106, 24, 1, 'A 45-year-old man with chronic Hepatitis C infection presents with fatigue, lower extremity oedema, and \'foamy\' dark urine. Laboratory results show a serum creatinine of , blood and protein on urinalysis, and low serum . Light microscopy of a renal biopsy shows thickened capillary walls with a \'double-contour\' appearance. What is the most likely underlying mechanism?\n? Hint: Focus on the association between a specific virus and the \'tram-track\' appearance.', 'Autoantibodies against the chain of Type IV collagen', 'Persistent activation of the alternative complement pathway', 'Antibody-mediated podocyte injury with subepithelial spikes', 'Direct invasion of the renal parenchyma by viral particles', 'Subendothelial immune complex deposition leading to basement membrane splitting', 'E', 'Hepatitis C is strongly associated with Type 1 MPGN, which presents with low and the characteristic \'tram-track\' or double-contour appearance due to GBM splitting.', NULL, NULL, 'moderate', 'manual', 'published', 4, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(107, 24, 1, 'A 32-year-old male smoker presents to the emergency department with a 2-day history of coughing up blood and decreased urine output. His blood pressure is and creatinine is . Immunofluorescence of a renal biopsy shows a smooth, continuous linear pattern of along the glomerular capillaries. Which of the following is the most likely diagnosis?\n? Hint: The \'linear\' glow on the basement membrane is a specific marker for antibodies attacking a structural component.', 'Systemic lupus erythematosus', 'Goodpasture syndrome', 'Post-streptococcal glomerulonephritis', 'Granulomatosis with polyangiitis', 'Microscopic polyangiitis', 'B', 'The combination of pulmonary haemorrhage (hemoptysis) and rapidly progressive renal failure with a linear IF pattern is pathognomonic for Goodpasture syndrome (Type 1 RPGN).', NULL, NULL, 'moderate', 'manual', 'published', 5, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(108, 24, 1, 'An 8-year-old boy is brought to the clinic because his mother noticed his urine looks like \'cola\'. Three weeks ago, he had a sore throat that resolved without treatment. On examination, he has mild periorbital oedema and a blood pressure of . Laboratory studies show low levels and elevated titres. What is the most appropriate management for this patient?\n? Hint: This condition in children is usually self-limiting and requires only symptom management.', 'Long-term immunosuppression with mycophenolate mofetil', 'Intravenous methylprednisolone pulse therapy', 'Immediate renal biopsy to confirm crescents', 'Urgent plasmapheresis', 'Supportive care including salt restriction and diuretics', 'E', 'Paediatric PSGN is typically self-limiting; management focuses on controlling fluid overload and hypertension with salt restriction and diuretics.', NULL, NULL, 'moderate', 'manual', 'published', 6, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(109, 24, 1, 'Which of the following describes the hallmark histological feature found in all three types of Rapidly Progressive Glomerulonephritis (RPGN)?\n? Hint: Think of the \'Crescent Moon\' of destruction and what cells actually form that shape.', 'Extracapillary proliferation of parietal epithelial cells and macrophages', 'Mesangial expansion with hypercellularity', 'Subepithelial humps on electron microscopy', 'Granular \'starry sky\' deposition of and', 'Subendothelial \'wire-loop\' thickening of capillary walls', 'A', 'The \'crescent\' in RPGN is formed by the proliferation of parietal epithelial cells and the infiltration of inflammatory cells like macrophages into Bowman\'s space.', NULL, NULL, 'moderate', 'manual', 'published', 7, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(110, 24, 1, 'A 15-year-old girl is evaluated for worsening facial thinning (partial lipodystrophy) and dark urine. Laboratory testing reveals very low serum but normal . A renal biopsy shows dense, sausage-shaped deposits within the glomerular basement membrane. What is the most likely serum finding in this patient?\n? Hint: Consider the antibody that acts as a \'glitch\' in the complement system\'s off-switch.', 'Elevated titres', 'Positive (anti-proteinase 3)', 'Positive anti-HCV antibodies', 'nephritic factor', 'Anti-double stranded antibodies', 'D', 'Type 2 MPGN (Dense Deposit Disease) is associated with C3 nephritic factor, which stabilises C3 convertase, leading to persistent alternative complement pathway activation and lipodystrophy.', NULL, NULL, 'moderate', 'manual', 'published', 8, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(111, 24, 1, 'A 52-year-old woman with a history of chronic sinusitis and recurrent ear infections presents with dark urine and a cough productive of blood-tinged sputum. Her creatinine has risen from to over one week. A biopsy is planned. Which of the following immunofluorescence findings is most expected?\n? Hint: The \'Head-itis\' (sinusitis) plus \'Kidney Crash\' points to a specific vasculitis category.', 'Isolated staining in a \'starry sky\' pattern', 'Granular and staining', 'Granular staining in the mesangium', 'Negative or minimal staining (pauci-immune)', 'Linear staining', 'D', 'The combination of sinusitis, hemoptysis, and rapid renal failure suggests Granulomatosis with Polyangiitis (GPA), a Type 3 RPGN characterised by a pauci-immune pattern.', NULL, NULL, 'moderate', 'manual', 'published', 9, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(112, 24, 1, 'A 30-year-old woman with known systemic lupus erythematosus (SLE) develops foamy urine and significant peripheral oedema. Her urinalysis shows protein but no RBCs or RBC casts. Her creatinine remains normal at . Which of the following is the most likely renal diagnosis?\n? Hint: Distinguish between the \'Nephritic\' vs \'Nephrotic\' presentations of lupus in the kidney.', 'Minimal Change Disease', 'IgA Nephropathy', 'Diffuse Proliferative Glomerulonephritis (Class IV)', 'Membranous Nephropathy (Class V Lupus Nephritis)', 'Rapidly Progressive Glomerulonephritis', 'D', 'Purely nephrotic syndrome (massive proteinuria, no blood) in an SLE patient, without the \'active sediment\' of DPGN, points to Membranous Nephropathy.', NULL, NULL, 'moderate', 'manual', 'published', 10, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(113, 24, 1, 'In the context of Glomerulonephritis, what is the significance of \'sterile pyuria\' often seen in Post-Streptococcal Glomerulonephritis?\n? Hint: Look for the \'Neutrophil Histo\' clue mentioned in the source material.', 'It represents the massive infiltration of neutrophils into the inflamed glomeruli', 'It results from the breakdown of RBC casts in the renal tubules', 'It indicates a concurrent urinary tract infection that requires antibiotics', 'It suggests the patient has developed Henoch-Schönlein Purpura', 'It is a sign of chronic kidney scarring and tubular atrophy', 'A', 'PSGN is a proliferative disease where neutrophils (polymorphonuclear leukocytes) invade the glomeruli, which can then spill into the urine without an active bacterial infection.', NULL, NULL, 'moderate', 'manual', 'published', 11, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(114, 24, 1, 'A renal biopsy from a patient with rapidly declining kidney function shows \'lumpy-bumpy\' granular deposits of , , , , and on immunofluorescence. This \'Full House\' pattern is most specific for which condition?\n? Hint: This \'Full House\' of sticky trash is usually dumped by the body\'s \'haywire\' lupus defense system.', 'Anti-GBM Disease', 'Membranoproliferative Glomerulonephritis', 'Post-Streptococcal Glomerulonephritis', 'IgA Nephropathy', 'Diffuse Proliferative Glomerulonephritis', 'E', 'The \'Full House\' pattern on IF—staining positive for multiple immunoglobulins and complement components—is classic for DPGN in the setting of SLE.', NULL, NULL, 'moderate', 'manual', 'published', 12, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(115, 24, 1, 'Which of the following describes the pathophysiology of Type 1 Membranoproliferative Glomerulonephritis (MPGN)?\n? Hint: Think about the \'Tram-Track Repair\' and where the \'clumps\' get stuck.', 'Defective galactosylation of leads to mesangial trapping', 'Complement activation leads to massive subepithelial humps', 'Immune complexes deposit subendothelially, triggering basement membrane duplication', 'Pauci-immune necrotising vasculitis associated with', 'Antibodies attack the chain of Type IV collagen', 'C', 'In Type 1 MPGN, deposits under the inner lining (subendothelial) lead to the kidney growing more membrane over them, creating the \'tram-track\' appearance.', NULL, NULL, 'moderate', 'manual', 'published', 13, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(116, 24, 1, 'A 10-year-old boy with a history of Celiac disease is found to have microscopic haematuria on a routine school physical. He has no symptoms, a normal blood pressure, and a creatinine of . What is the best next step?\n? Hint: Do not rush to invasive procedures if the patient is \'stable and asymptomatic\'.', 'Perform an urgent renal biopsy', 'Order a streptozyme test and titre', 'Start high-dose oral prednisone', 'Conservative management and periodic monitoring', 'Administer intravenous cyclophosphamide', 'D', 'Stable, asymptomatic IgA nephropathy (common in Celiac patients) with normal BP and kidney function does not require immediate biopsy or aggressive treatment.', NULL, NULL, 'moderate', 'manual', 'published', 14, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(117, 24, 1, 'Which of the following statements best differentiates between paediatric Post-Streptococcal Glomerulonephritis (PSGN) and adult Infection-Related Glomerulonephritis (IRGN)?\n? Hint: Focus on the \'No Waiting\' trap mentioned in the adult-onset IRGN guide.', 'Paediatric PSGN requires lifelong dialysis, whereas adult IRGN is always self-limiting', 'Adult IRGN always has normal complement levels, while paediatric PSGN has low', 'Adult IRGN often occurs during an active infection, whereas paediatric PSGN typically follows a \'honeymoon period\'', 'Paediatric PSGN is associated with Hepatitis C, while adult IRGN is not', 'Adult IRGN is caused by Type IV collagen mutations', 'C', 'Paediatric PSGN has a delay of 1-6 weeks after infection; adult IRGN (especially Staph-related) often occurs while the infection is still active.', NULL, NULL, 'moderate', 'manual', 'published', 15, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(118, 24, 1, 'A 25-year-old man presents with a rapid decrease in urine output and a creatinine that spiked from to in two days. A renal biopsy reveals \'crescent-moon shape\' proliferation in the majority of glomeruli. What is the most appropriate initial therapy to halt the progression of this condition?\n? Hint: Think of the most aggressive \'fire extinguisher\' used to stop a lightning-strike attack on the filters.', 'Broad-spectrum antibiotics like vancomycin', 'Oral ibuprofen for inflammation control', 'Oral ACE inhibitors like lisinopril', 'Maintenance haemodialysis only', 'Intravenous methylprednisolone pulse therapy', 'E', 'RPGN is a \'kidney fire\' emergency; high-dose IV steroids are the immediate \'fire extinguisher\' to stop inflammatory damage.', NULL, NULL, 'moderate', 'manual', 'published', 16, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(119, 24, 1, 'In patients with Henoch-Schönlein Purpura (HSP), the renal involvement is identical in pathology to which of the following conditions?\n? Hint: Consider the \'Spectrum Case\' where skin, joint, and belly pain accompany kidney findings.', 'Membranoproliferative Glomerulonephritis', 'Post-Streptococcal Glomerulonephritis', 'Goodpasture Syndrome', 'Minimal Change Disease', 'IgA Nephropathy', 'E', 'HSP is a systemic vasculitis where the renal manifestation is essentially IgA nephropathy (mesangial IgA deposits) plus skin, joint, and GI symptoms.', NULL, NULL, 'moderate', 'manual', 'published', 17, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(120, 24, 1, 'A patient\'s renal biopsy shows \'wire loop\' capillaries on light microscopy and subendothelial deposits on electron microscopy. Which of the following lab findings would most likely be associated with this patient?\n? Hint: Identify the \'Lupus of the kidney\' and its signature markers.', 'Positive and normal', 'High levels and anti-HCV antibodies', 'Normal and normal with recurrent haematuria', 'Normal and positive ASO titre', 'Positive anti-dsDNA and low', 'E', 'Wire-loop lesions and subendothelial deposits are the classic hallmarks of Class IV Lupus Nephritis (DPGN), which is associated with these specific lab markers.', NULL, NULL, 'moderate', 'manual', 'published', 18, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(121, 24, 1, 'Which specific component of the glomerular filter is targeted by antibodies in Goodpasture Syndrome (Type 1 RPGN)?\n? Hint: The antibody attacks the \'floor\' of the filter shared by the lungs and kidneys.', 'Endothelial surface glycoproteins', 'The convertase enzyme', 'Alpha-3 chain of Type IV collagen', 'The podocyte slit diaphragm proteins', 'Mesangial matrix proteins', 'C', 'In Goodpasture Syndrome, the body produces autoantibodies that specifically attack the chain of Type IV collagen found in the basement membranes of the lungs and kidneys.', NULL, NULL, 'moderate', 'manual', 'published', 19, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(122, 24, 1, 'What is the primary immunological mechanism behind Post-Streptococcal Glomerulonephritis (PSGN)?\n? Hint: The \'Filter Clog\' is caused by wandering immune clumps, not a direct attack on a specific cell type.', 'Type III Hypersensitivity (Immune complex-mediated)', 'Type II Hypersensitivity (Cytotoxic antibody-mediated)', 'Type I Hypersensitivity (-mediated)', 'Type IV Hypersensitivity (T-cell mediated)', 'Direct cytopathic effect of the Streptococcus bacterium', 'A', 'PSGN occurs when antigen-antibody complexes (Strep antigens + antibodies) circulate and get physically trapped in the glomerular basement membrane.', NULL, NULL, 'moderate', 'manual', 'published', 20, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(123, 24, 1, 'A patient with suspected Membranoproliferative Glomerulonephritis (MPGN) shows \'Tram-track\' splitting of the glomerular basement membrane on light microscopy. If this is Type 1 MPGN, where would you expect to see the dense deposits on electron microscopy?\n? Hint: The deposits are located under the \'inner lining of the pipe\'.', 'Mesangium only', 'Subendothelial space', 'Intramembranous space', 'Subepithelial space', 'Podocyte foot processes', 'B', 'Type 1 MPGN is characterised by subendothelial deposits, which trigger the duplication of the basement membrane.', NULL, NULL, 'moderate', 'manual', 'published', 21, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(124, 24, 1, 'An 11-year-old girl presents with purple spots on her buttocks, abdominal pain, and painful ankles. Her urine dipstick shows blood. Which finding on renal biopsy would be most consistent with her diagnosis?\n? Hint: Remember that HSP and Berger\'s disease are on the same spectrum of immune protein deposition.', 'Granular deposits in the mesangium', 'Large subepithelial humps', 'Linear along the glomerular basement membrane', 'Diffuse \'wire-loop\' thickened capillaries', 'Splitting of the glomerular basement membrane', 'A', 'The patient has the classic tetrad of Henoch-Schönlein Purpura (HSP), and the kidney biopsy in HSP shows mesangial IgA deposition identical to Berger\'s disease.', NULL, NULL, 'moderate', 'manual', 'published', 22, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(125, 24, 1, 'A 35-year-old male with chronic sinus congestion and lung nodules presents with rapidly rising creatinine. His c-ANCA is positive. What is the most likely pathological finding on his renal biopsy?\n? Hint: The \'Pauci\' in Pauci-immune means there is very little \'glow\' on the microscope.', 'Full house granular immunofluorescence staining', 'Linear staining with pulmonary haemorrhage', 'Thickened \'tram-track\' basement membranes', 'Necrotising glomerulonephritis with crescents and no immune deposits', 'Subepithelial humps and starry sky pattern', 'D', 'Wegener\'s (GPA) is a pauci-immune RPGN (Type 3), which shows crescents and necrotising changes but lacks significant antibody staining on immunofluorescence.', NULL, NULL, 'moderate', 'manual', 'published', 23, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(126, 24, 1, 'Which lab finding most reliably distinguishes IgA Nephropathy from Post-Streptococcal Glomerulonephritis (PSGN) in a patient with post-infectious haematuria?\n? Hint: Think about which disease \'uses up\' the body\'s demolition crew and which one does not.', 'Degree of proteinuria', 'Presence of red blood cell (RBC) casts', 'Serum creatinine level', 'Blood pressure measurements', 'Serum complement level', 'E', 'is low in PSGN (it is used up) but is characteristically normal in IgA nephropathy.', NULL, NULL, 'moderate', 'manual', 'published', 24, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(127, 24, 1, 'What is the primary role of ACE inhibitors or ARBs in the management of chronic glomerulonephritis, such as IgA Nephropathy or DPGN?\n? Hint: The goal is to \'protect the filters\' from high pressure.', 'They directly remove immune complexes from the subendothelial space', 'They restore the missing sugar molecules on antibodies', 'They act as powerful immunosuppressants to stop antibody production', 'They lower intraglomerular pressure to reduce protein leak and prevent scarring', 'They are used as emergency agents to treat crescent formation', 'D', 'By dilating the efferent arteriole, these drugs lower the pressure inside the filters, protecting them from further mechanical damage and fibrosis.', NULL, NULL, 'moderate', 'manual', 'published', 25, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(128, 24, 1, 'A 10-year-old child presents with PSGN. The mother asks about the long-term outlook for her son\'s kidneys. Based on the provided material, what is the most accurate prognosis?\n? Hint: Paediatric cases of this post-infectious condition are described as \'simple and getting better\'.', 'The child will likely need immediate and lifelong dialysis', 'The prognosis is excellent, and the majority of children recover completely', 'The condition will recur every time the child gets a common cold', 'Most children will develop systemic lupus erythematosus within a decade', 'One-third of children will progress to end-stage renal disease within 20 years', 'B', 'PSGN in children is famously self-limiting with an excellent recovery rate, whereas the adult-onset version is much more concerning.', NULL, NULL, 'moderate', 'manual', 'published', 26, 5, '2026-07-09 11:14:29', '2026-07-09 11:14:29'),
(129, 25, 1, 'A 45-year-old male with a history of poorly controlled type 2 diabetes mellitus presents for a follow-up. Laboratory studies show a fasting blood glucose of (normal: ). In the liver, which of the following enzymes acts as a \'glucose sensor\' and is regulated by insulin to facilitate the phosphorylation of glucose specifically during high glucose concentrations?\n? Hint: Consider the enzyme found in the liver and pancreatic beta cells with a low affinity for its substrate.', 'Hexokinase', 'Glucose 6-phosphatase', 'Glucokinase', 'Phosphofructokinase-1', NULL, 'C', 'This enzyme has a high and high , allowing the liver to process large amounts of glucose after a meal; it is induced by insulin.', NULL, NULL, 'moderate', 'manual', 'published', 1, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(131, 25, 1, 'A 7-year-old boy is brought to the physician due to recurrent skin infections with . A dihydrorhodamine test confirms a diagnosis of Chronic Granulomatous Disease. Which of the following biochemical pathways provides the essential cofactor required by the enzyme deficient in this patient?\n? Hint: Think of the \'shunt\' pathway that produces reducing equivalents for biosynthetic and immune functions.', 'Pentose Phosphate Pathway', 'Glycolysis', 'Krebs Cycle', 'Gluconeogenesis', NULL, 'A', 'The HMP shunt/Pentose Phosphate Pathway is the primary source of , which is the necessary substrate for oxidase in neutrophils.', NULL, NULL, 'moderate', 'manual', 'published', 3, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(132, 25, 1, 'A 19-year-old male presents with dark urine and jaundice after being treated for a urinary tract infection with nitrofurantoin. A peripheral blood smear shows cells with small, dark inclusions and \'bite cells\'. A deficiency in which of the following enzymes is most likely responsible?\n? Hint: This enzyme is the rate-limiting step of the pathway that protects red blood cells from oxidative damage.', 'Pyruvate kinase', 'Glucose 6-phosphate dehydrogenase', 'Phosphofructokinase-1', 'Transketolase', NULL, 'B', 'Deficiency in leads to inadequate production, leaving haemoglobin vulnerable to oxidative damage and precipitation (Heinz bodies).', NULL, NULL, 'moderate', 'manual', 'published', 4, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(133, 25, 1, 'A researcher is studying the regulation of gluconeogenesis in liver cells. She observes that the addition of a specific molecule strongly inhibits Fructose 1,6-bisphosphatase. Which of the following molecules is the most likely inhibitor?\n? Hint: Identify the primary regulator that ensures glycolysis and gluconeogenesis do not occur at high rates simultaneously.', 'Fructose 2,6-bisphosphate', 'Biotin', 'Acetyl-CoA', 'Citrate', NULL, 'A', 'This molecule is a potent activator of glycolysis () and a reciprocal inhibitor of gluconeogenesis ().', NULL, NULL, 'moderate', 'manual', 'published', 5, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(134, 25, 1, 'A 32-year-old man who recently started a \'bodybuilding\' diet involving the consumption of 10 raw egg whites daily presents with hair loss and a skin rash. He is found to have a defect in the first step of gluconeogenesis. Which enzyme\'s function is most likely impaired in this patient?\n? Hint: Look for a Vitamin dependent enzyme that converts a 3-carbon molecule to a 4-carbon molecule.', 'Lactate dehydrogenase', 'Phosphoenolpyruvate carboxykinase', 'Pyruvate carboxylase', 'Pyruvate kinase', NULL, 'C', 'Raw egg whites contain avidin, which binds biotin (); biotin is the essential cofactor for pyruvate carboxylase.', NULL, NULL, 'moderate', 'manual', 'published', 6, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(135, 25, 1, 'During a state of starvation, the liver maintains blood glucose levels through gluconeogenesis. Which of the following enzymes is located specifically within the endoplasmic reticulum and is responsible for the final step of this process?\n? Hint: This enzyme is \'missing\' in most tissues, explaining why only the liver and kidneys can release glucose into the blood.', 'Pyruvate carboxylase', 'Glucose 6-phosphatase', 'Fructose 1,6-bisphosphatase', 'Glucokinase', NULL, 'B', 'This enzyme is anchored in the ER membrane; it removes the phosphate from to allow free glucose to exit the liver cell.', NULL, NULL, 'moderate', 'manual', 'published', 7, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(136, 25, 1, 'A newborn is evaluated for lethargy and poor feeding. Labs show significant lactic acidosis and hyperalaninaemia. The physician suspects a deficiency in an enzyme that bridges glycolysis and the citric acid cycle. Which enzyme is most likely deficient?\n? Hint: Identify the enzyme that converts pyruvate into Acetyl-CoA.', 'Glucokinase', 'Pyruvate kinase', 'Pyruvate dehydrogenase', 'Phosphofructokinase-2', NULL, 'C', 'Deficiency in prevents pyruvate from entering the cycle, shunting it toward lactate and alanine production.', NULL, NULL, 'moderate', 'manual', 'published', 8, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(137, 25, 1, 'A patient is found to have a genetic mutation that causes \'leakiness\' in the mitochondrial membrane, but his cytosolic glycolytic enzymes are normal. If glycolysis continues, why is the \'trapping\' of glucose as Glucose 6-phosphate still essential?\n? Hint: Glucose is described as a \'naughty child\' in the source material that needs to be \'chained\' inside the cell.', 'To prevent glucose from diffusing back out through GLUT transporters', 'To inhibit the HMP shunt', 'To activate the sodium-glucose co-transporter ()', 'To increase the affinity of glucose for GLUT-4', NULL, 'A', 'Phosphorylation adds a negative charge and increases the size of the molecule, preventing it from crossing the cell membrane.', NULL, NULL, 'moderate', 'manual', 'published', 9, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(138, 25, 1, 'Which of the following tissues relies exclusively on anaerobic glycolysis for its energy needs even under aerobic conditions due to a lack of mitochondria?\n? Hint: This cell type is essentially a bag of haemoglobin without organelles.', 'Brain', 'Mature Erythrocytes', 'Skeletal Muscle', 'Liver', NULL, 'B', 'RBCs lack mitochondria and thus cannot perform the cycle or oxidative phosphorylation; they must rely on lactate production.', NULL, NULL, 'moderate', 'manual', 'published', 10, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(139, 25, 1, 'A biochemist is examining the differences between Hexokinase and Glucokinase. He notes that one of the enzymes is not inhibited by its product, Glucose 6-phosphate. Which enzyme is this, and why is this property significant?\n? Hint: One enzyme is a \'sensor\' for the whole body, while the other is \'selfish\' for the individual cell.', 'Glucokinase; it prevents the pancreas from releasing too much insulin', 'Hexokinase; it allows for the rapid production of in the brain', 'Hexokinase; it ensures that all cells get a baseline level of glucose regardless of the concentration', 'Glucokinase; it allows the liver to continue storing glucose even when levels are high', NULL, 'D', 'Glucokinase is not product-inhibited, allowing the liver to clear high blood glucose levels postprandially.', NULL, NULL, 'moderate', 'manual', 'published', 11, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(140, 25, 1, 'During the HMP shunt, the enzyme Glucose 6-phosphate dehydrogenase () reduces . What is the specific role of in the context of the glutathione system?\n? Hint: Think of as the \'battery charger\' for the cell\'s antioxidant system.', 'It acts as a cofactor for glutathione reductase to reduce oxidised glutathione', 'It binds to Heinz bodies to facilitate their removal by the spleen', 'It directly neutralises hydrogen peroxide into water', 'It activates glutathione peroxidase', NULL, 'A', 'provides the electrons needed by glutathione reductase to convert back to , which then neutralises .', NULL, NULL, 'moderate', 'manual', 'published', 12, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(141, 25, 1, 'If a patient has a complete deficiency of the enzyme Phosphofructokinase-2 () in the liver, which of the following would be the most likely clinical consequence regarding glycolysis?\n? Hint: Consider the role of the product of in stimulating the \'rate-limiting\' step of glycolysis.', 'Excessive production of Pyruvate', 'Failure to initiate Gluconeogenesis', 'Impaired activation of Glycolysis during the well-fed state', 'Rapid development of hyperuricaemia', NULL, 'C', 'Without , the liver cannot produce Fructose 2,6-bisphosphate, which is the key allosteric activator of .', NULL, NULL, 'moderate', 'manual', 'published', 13, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(142, 25, 1, 'In the liver, high levels of Acetyl-CoA act as a metabolic signal. Which of the following best describes its regulatory effect?\n? Hint: Acetyl-CoA signals \'full energy\' and the need to build glucose rather than break it down.', 'Inhibits Fructose 1,6-bisphosphatase', 'Activates Phosphofructokinase-1 to accelerate Glycolysis', 'Activates Pyruvate carboxylase to promote Gluconeogenesis', 'Inhibits Glucokinase to prevent glucose trapping', NULL, 'C', 'High Acetyl-CoA signals that energy is sufficient and provides a substrate for gluconeogenesis by activating the first bypass enzyme.', NULL, NULL, 'moderate', 'manual', 'published', 14, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(143, 25, 1, 'A 10-month-old infant presents with hypoglycaemia, lactic acidosis, and hepatomegaly. The physician suspects Von Gierke disease (Type I Glycogen Storage Disease). This condition involves a deficiency in which enzyme?\n? Hint: This enzyme is located in the endoplasmic reticulum and is the final gatekeeper for hepatic glucose release.', 'Glucokinase', 'Fructose 1,6-bisphosphatase', 'Pyruvate carboxylase', 'Glucose 6-phosphatase', NULL, 'D', 'This enzyme is required for the final step of both glycogenolysis and gluconeogenesis; its absence prevents glucose release from the liver.', NULL, NULL, 'moderate', 'manual', 'published', 15, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(144, 25, 1, 'Why is it that during intense exercise, the rise in levels is a more sensitive signal for activating glycolysis than a decrease in ?\n? Hint: Think about the percentage change in concentrations when is consumed.', 'prevents the inhibitory effect of Citrate on', 'concentrations change proportionally more than concentrations during energy expenditure', 'directly binds to and activates Glucokinase', 'is required as a cofactor for Pyruvate Kinase', NULL, 'B', 'Small percentage drops in lead to much larger percentage increases in due to the adenylate kinase reaction, making a better \'energy sensor\'.', NULL, NULL, 'moderate', 'manual', 'published', 16, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(145, 25, 1, 'In deficiency, red blood cells are particularly susceptible to oxidative stress. Which of the following common exposures is most likely to trigger a haemolytic crisis in these patients?\n? Hint: This trigger is a type of legume often mentioned in medical board exams.', 'Heavy exercise', 'High altitude', 'Raw egg whites', 'Fava beans', NULL, 'D', 'Fava beans contain oxidants like vicine and covicine, which generate that cannot be neutralised in deficient cells.', NULL, NULL, 'moderate', 'manual', 'published', 17, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(146, 25, 1, 'A patient presents with a \'fruity\' breath odour and increased thirst. Laboratory results show hyperglycaemia and ketones in the urine. How does insulin administration resolve this hyperglycaemia in terms of liver enzyme regulation?\n? Hint: Insulin wants to \'burn\' glucose via glycolysis and \'trap\' it in the liver.', 'It induces Glucose 6-phosphatase expression', 'It inhibits Pyruvate Kinase to conserve glucose', 'It activates Glucokinase and Phosphofructokinase-2', 'It promotes the phosphorylation of glycolytic enzymes', NULL, 'C', 'Insulin promotes glucose uptake and glycolysis by inducing glucokinase and activating to produce Fructose 2,6-bisphosphate.', NULL, NULL, 'moderate', 'manual', 'published', 18, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(147, 25, 1, 'Which enzyme of gluconeogenesis is the reciprocal counterpart to the rate-limiting enzyme of glycolysis ()?\n? Hint: This enzyme removes a phosphate from the first and sixth carbons of a fructose molecule.', 'carboxykinase', 'Fructose 1,6-bisphosphatase', 'Glucose 6-phosphatase', 'Pyruvate carboxylase', NULL, 'B', 'This enzyme reverses the step performed by and is the rate-limiting step of gluconeogenesis.', NULL, NULL, 'moderate', 'manual', 'published', 19, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(148, 25, 1, 'Which of the following describes the effect of glucagon on the bifunctional enzyme complex in the liver?\n? Hint: Glucagon uses a second messenger system that leads to the addition of phosphate groups.', 'It induces the synthesis of more protein', 'It dephosphorylates the complex, activating and increasing glycolysis', 'It inhibits the complex directly via citrate', 'It phosphorylates the complex, activating and decreasing glycolysis', NULL, 'D', 'Glucagon increases , activating , which phosphorylates the complex; this lowers levels.', NULL, NULL, 'moderate', 'manual', 'published', 20, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(149, 25, 1, 'A research study finds that a certain population has a variant of Transketolase with a much lower affinity for thiamine pyrophosphate. Which clinical condition is this population likely predisposed to developing?\n? Hint: This syndrome is often seen in chronic alcoholics and involves confusion and gait disturbances.', 'Wernicke-Korsakoff Syndrome', 'Haemolytic Anaemia', 'Chronic Granulomatous Disease', 'Type 1 Diabetes', NULL, 'A', 'Genetic predisposition involving transketolase affinity contributes to the neurological manifestations of thiamine deficiency.', NULL, NULL, 'moderate', 'manual', 'published', 21, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(150, 25, 1, 'A patient with a deficiency in oxidase is unable to clear certain infections because their neutrophils cannot produce which of the following?\n? Hint: This product is the \'spark\' for the oxidative killing of bacteria.', 'Pyruvate', 'Lactic acid', 'Reduced glutathione', 'Superoxide radicals', NULL, 'D', 'oxidase converts oxygen to superoxide (), the first step in the respiratory burst.', NULL, NULL, 'moderate', 'manual', 'published', 22, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(151, 25, 1, 'Which enzyme acts as the rate-limiting step for the entire HMP shunt/Pentose Phosphate Pathway?\n? Hint: It is the first enzyme in the oxidative phase of the pathway.', '6-phosphogluconolactone hydrolase', 'Transketolase', 'Phosphofructokinase-1', 'Glucose 6-phosphate dehydrogenase', NULL, 'D', 'This enzyme is regulated by the ratio and determines the flow of glucose into the shunt.', NULL, NULL, 'moderate', 'manual', 'published', 23, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(152, 25, 1, 'A patient with chronic renal failure has high levels of urea. If urea cycle intermediates were to interfere with gluconeogenesis, which organ would be most affected in its ability to maintain blood glucose during long-term fasting?\n? Hint: This organ \'stores\' glucose as glycogen and \'makes\' it through gluconeogenesis.', 'Brain', 'Liver', 'Pancreas', 'Skeletal Muscle', NULL, 'B', 'The liver is the primary site of both the urea cycle and gluconeogenesis, accounting for roughly of glucose production during fasting.', NULL, NULL, 'moderate', 'manual', 'published', 24, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(153, 25, 1, 'Which of the following best explains why RBCs from a deficient patient are specifically prone to haemolysis, while their other body cells are relatively spared?\n? Hint: Consider that RBCs lack a nucleus and most organelles.', 'The spleen only destroys RBCs with Heinz bodies', 'RBCs have higher concentrations of Glucokinase', 'Other cells do not produce free radicals', 'Other cells have alternative pathways to generate', NULL, 'D', 'Nucleated cells can use enzymes like malic enzyme to produce , whereas RBCs rely solely on the HMP shunt.', NULL, NULL, 'moderate', 'manual', 'published', 25, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(154, 25, 1, 'In the \'energy investment phase\' of glycolysis, two molecules are used. Which two enzymes are responsible for these phosphorylation steps?\n? Hint: These enzymes are the first and third steps of the pathway.', 'Phosphoglycerate Kinase and Hexokinase', 'Phosphofructokinase-1 and Pyruvate Kinase', 'Glucokinase and Pyruvate Carboxylase', 'Hexokinase and Phosphofructokinase-1', NULL, 'D', 'Hexokinase/Glucokinase uses the first to trap glucose, and uses the second to form fructose 1,6-bisphosphate.', NULL, NULL, 'moderate', 'manual', 'published', 26, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(155, 25, 1, 'Which intermediate of the HMP shunt is essentially required for the synthesis of nucleotides (DNA and RNA)?\n? Hint: Look for a 5-carbon sugar whose name is found in the acronyms DNA and RNA.', 'Xylulose 5-phosphate', 'Ribulose 5-phosphate', 'Fructose 6-phosphate', 'Ribose 5-phosphate', NULL, 'D', 'Ribose 5-phosphate is the sugar backbone required for all nucleotide biosynthesis.', NULL, NULL, 'moderate', 'manual', 'published', 27, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(156, 25, 1, 'A patient is found to have \'Heinz bodies\' on blood film. These represent precipitates of which molecule?\n? Hint: This is the primary protein found within red blood cells.', 'Oxidised Haemoglobin', 'Crystallised Glucose', 'Lactate Dehydrogenase', 'Unconjugated Bilirubin', NULL, 'A', 'Oxidative stress leads to cross-linking of globin chains, causing haemoglobin to denature and precipitate.', NULL, NULL, 'moderate', 'manual', 'published', 28, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(157, 25, 1, 'Which of the following describes the \'feed-forward\' activation seen in glycolysis?\n? Hint: An early intermediate of glycolysis stimulates a later enzyme in the same pathway.', 'Citrate activating Acetyl-CoA carboxylase', 'Fructose 1,6-bisphosphate activating Pyruvate Kinase', 'Glucose activating Glucokinase', 'inhibiting', NULL, 'B', 'This ensures that the products of the rate-limiting step () are \'pulled\' through the end of the pathway.', NULL, NULL, 'moderate', 'manual', 'published', 29, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(158, 25, 1, 'A 60-year-old female with a history of heart failure is admitted with sepsis. She develops severe lactic acidosis. Which of the following is the most likely cause of her elevated lactate?\n? Hint: Lactate builds up when the \'normal\' aerobic pathway for pyruvate is blocked by lack of oxygen.', 'Inhibition of the HMP shunt', 'Deficiency of Lactate Dehydrogenase', 'Excessive activity of Glucokinase in the liver', 'Tissue hypoxia leading to impaired oxidative phosphorylation', NULL, 'D', 'Sepsis and heart failure reduce oxygen delivery, forcing cells to rely on anaerobic glycolysis and lactate production.', NULL, NULL, 'moderate', 'manual', 'published', 30, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12');
INSERT INTO `mcqs` (`id`, `lecture_id`, `course_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `option_e`, `correct_option`, `explanation`, `option_explanations`, `topic`, `difficulty`, `source`, `status`, `sort_order`, `created_by`, `created_at`, `updated_at`) VALUES
(159, 25, 1, 'What is the primary role of \'Bite cells\' found in the circulation of a deficient patient?\n? Hint: The \'bite\' is taken by an immune cell in the organ that \'filters\' the blood.', 'They are RBCs that have been \'bitten\' by bacteria', 'They are immature RBCs released early from the bone marrow', 'They are RBCs after splenic macrophages have removed Heinz bodies', 'They represent the site of viral entry into the erythrocyte', NULL, 'C', 'Macrophages in the splenic cords \'bite\' out the precipitated haemoglobin, leaving a characteristic semi-circular defect.', NULL, NULL, 'moderate', 'manual', 'published', 31, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(160, 25, 1, 'The conversion of Pyruvate to Oxaloacetate occurs in which cellular compartment?\n? Hint: This compartment is known as the \'powerhouse\' and also houses the Krebs cycle.', 'Mitochondria', 'Cytosol', 'Endoplasmic Reticulum', 'Nucleus', NULL, 'A', 'Pyruvate carboxylase is a mitochondrial enzyme, reflecting the link between the cycle and gluconeogenesis.', NULL, NULL, 'moderate', 'manual', 'published', 32, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(161, 25, 1, 'Which molecule is a common intermediate shared by Glycolysis, Gluconeogenesis, and the HMP Shunt?\n? Hint: It is the first molecule formed after glucose enters the cell.', 'Citrate', 'Lactic acid', 'Acetyl-CoA', 'Glucose 6-phosphate', NULL, 'D', 'is the \'branch point\' that can enter glycolysis, be produced by gluconeogenesis, or enter the HMP shunt.', NULL, NULL, 'moderate', 'manual', 'published', 33, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(162, 25, 1, 'In the context of biochemical thermodynamics, why must gluconeogenesis use different enzymes for the three bypass steps instead of simply reversing the glycolysis enzymes?\n? Hint: Think about the energy \'hill\' that would have to be climbed to reverse a reaction that releases a lot of heat.', 'The enzymes for gluconeogenesis are only found in the mitochondria', 'Insulin inhibits the reversal of any enzyme', 'The glycolytic steps are highly exergonic and essentially irreversible', 'The products of glycolysis are toxic if they accumulate', NULL, 'C', 'Steps 1, 3, and 10 of glycolysis have a large negative , making them energetically \'one-way streets\'.', NULL, NULL, 'moderate', 'manual', 'published', 34, 5, '2026-07-10 12:37:12', '2026-07-10 12:37:12'),
(163, 27, 1, 'A 45-year-old woman presents with a 3-month history of episodic pounding headaches, palpitations, and profuse sweating. During these episodes, her blood pressure is measured at 190/110 , but it returns to baseline between episodes. A CT scan of the abdomen reveals a mass in the left adrenal gland. Which of the following is the most appropriate initial pharmacotherapy before surgical intervention?', 'Propranolol', 'Amlodipine', 'Lisinopril', 'Phenoxybenzamine', NULL, 'D', 'An irreversible alpha-blocker must be administered first to prevent hypertensive surges during surgery and before any beta - blockade.', NULL, NULL, 'moderate', 'manual', 'published', 1, 5, '2026-07-11 05:10:23', '2026-07-11 05:15:02'),
(165, 27, 1, 'A 32-year-old patient is being evaluated for secondary hypertension. The physician suspects a tumour of the adrenal medulla derived from chromaffin cells. Which of the following urinary metabolites, if elevated, would most likely confirm the diagnosis?\n', '5-Hydroxyindoleacetic acid ', 'Vanillylmandelic acid ', 'Homovanillic acid ', '17-Hydroxycorticosteroids', NULL, 'B', 'is a breakdown product of metanephrines (epinephrine and norepinephrine) and is a classic urinary marker for Pheochromocytoma.', NULL, NULL, 'moderate', 'manual', 'published', 3, 5, '2026-07-11 05:10:23', '2026-07-11 05:15:29'),
(166, 27, 1, 'In a patient with the Syndrome of Inappropriate Antidiuretic Hormone , which of the following physiological responses occurs as a secondary measure to address the initial water retention?\n', 'Suppression of the Renin-Angiotensin-Aldosterone System ', 'Increased water intake due to thirst stimulation', 'Increased secretion of Aldosterone', 'Decreased levels of Atrial Natriuretic Peptide ', NULL, 'A', 'The body attempts to excrete excess volume by lowering aldosterone and increasing natriuretic peptides, leading to natriuresis.', NULL, NULL, 'moderate', 'manual', 'published', 4, 5, '2026-07-11 05:10:23', '2026-07-11 05:16:01'),
(167, 27, 1, 'A 24-year-old male presents with sudden onset palpitations and facial flushing after a hot shower. His medical history is significant for Neurofibromatosis. A diagnosis of Pheochromocytoma is suspected. Which of the following \'Rules of 10\' describes the possibility that this tumour arises outside the adrenal gland?\n', '10% occur in children', '10% are calcified', '10% are extra-adrenal', '10% are malignant', NULL, 'C', 'The Rule of 10s states that 10% of Pheochromocytomas are extra-adrenal, often presenting as paragangliomas.', NULL, NULL, 'moderate', 'manual', 'published', 5, 5, '2026-07-11 05:10:23', '2026-07-11 05:16:24'),
(168, 27, 1, 'A 55-year-old woman with a history of chronic depression was recently started on Paroxetine. She presents to the clinic with confusion and lethargy. Laboratory results show low sodium levels. Which of the following is the most likely cause of her hyponatraemia?\n', 'Psychogenic polydipsia', 'Drug-induced', 'Diabetes Insipidus', 'Addison\'s Disease', NULL, 'B', ', such as Paroxetine, are known pharmacological triggers for the Syndrome of Inappropriate Antidiuretic Hormone secretion.', NULL, NULL, 'moderate', 'manual', 'published', 6, 5, '2026-07-11 05:10:23', '2026-07-11 05:16:43'),
(169, 27, 1, 'During an acute hypertensive crisis in a patient with a confirmed Pheochromocytoma, which of the following is the most appropriate medication for immediate management?\n', 'Esmolol', 'Phenoxybenzamine', 'Phentolamine', 'Furosemide', NULL, 'C', 'Phentolamine is a reversible -blocker used specifically for the management of acute hypertensive crises or \'spells\' in these patients.', NULL, NULL, 'moderate', 'manual', 'published', 7, 5, '2026-07-11 05:10:23', '2026-07-11 05:16:57'),
(170, 27, 1, 'A patient with refractory who has not responded to fluid restriction is being considered for pharmacological treatment to block receptors directly. Which of the following agents is most appropriate?\n', 'Hydrochlorothiazide', 'Tolvaptan', 'Spironolactone', 'Desmopressin', NULL, 'B', 'Tolvaptan and Conivaptan (the \'vaptans\') are direct receptor antagonists used in the management of .', NULL, NULL, 'moderate', 'manual', 'published', 8, 5, '2026-07-11 05:10:23', '2026-07-11 05:17:08'),
(171, 27, 1, 'The adrenal medulla is embryologically derived from which of the following tissues?\n', 'Mesoderm', 'Surface ectoderm', 'Neural crest cells', 'Endoderm', NULL, 'C', 'The adrenal medulla originates from neural crest cells, which explains why Pheochromocytoma is classified as a neuroendocrine tumour.', NULL, NULL, 'moderate', 'manual', 'published', 9, 5, '2026-07-11 05:10:23', '2026-07-11 05:17:20'),
(172, 27, 1, 'A 40-year-old patient with Pheochromocytoma presents with an abnormally high haematocrit. Which of the following paraneoplastic secretions is most likely responsible for this finding?\n', 'Erythropoietin ', 'Renin', 'Norepinephrine', 'Cortisol', NULL, 'A', 'Pheochromocytoma can secrete as a paraneoplastic syndrome, leading to polycythaemia.', NULL, NULL, 'moderate', 'manual', 'published', 10, 5, '2026-07-11 05:10:23', '2026-07-11 05:17:36'),
(174, 27, 1, 'A patient with a known adrenal mass is found to have positive immunohistochemical staining for Chromogranin A and Synaptophysin. These markers are most characteristic of which type of tumour?\n', 'Adrenocortical Adenoma', 'Renal Cell Carcinoma', 'Pheochromocytoma', 'Myelolipoma', NULL, 'C', 'Chromogranin A, synaptophysin, and neuron-specific enolase are classic markers for neuroendocrine tumours like Pheochromocytoma.', NULL, NULL, 'moderate', 'manual', 'published', 12, 5, '2026-07-11 05:10:23', '2026-07-11 05:18:31'),
(175, 27, 1, 'A 50-year-old male is hospitalised following a severe head injury from a motor vehicle accident. On his third day of admission, he develops hyponatraemia. His clinical volume status appears normal (euvolaemic). Which of the following is the most likely diagnosis?\n', 'Cerebral Salt Wasting', 'Syndrome of Inappropriate ', 'Adrenal Crisis', 'Diabetes Insipidus', NULL, 'B', 'CNS disorders, including head trauma and brain injuries, are common triggers for excessive release.', NULL, NULL, 'moderate', 'manual', 'published', 13, 5, '2026-07-11 05:10:23', '2026-07-11 05:18:50'),
(177, 27, 1, 'A patient with is treated with Demeclocycline for SIADH. What is the primary mechanism of action of this drug in this context?\n', 'It acts as a potent loop diuretic', 'It inhibits release from the posterior pituitary', 'It induces a state of nephrogenic diabetes insipidus', 'It increases the sensitivity of receptors', NULL, 'C', 'Demeclocycline interferes with signalling in the renal tubules, reducing water reabsorption.', NULL, NULL, 'moderate', 'manual', 'published', 15, 5, '2026-07-11 05:10:23', '2026-07-11 05:19:27'),
(178, 27, 1, 'A 29-year-old woman is brought to the emergency department after a sudden \'attack\' of racing heart, sweating, and a severe throbbing headache that lasted 20 minutes. She describes several similar episodes over the past month. Between episodes, she feels normal but anxious. Her symptoms most closely mimic which of the following psychiatric conditions?\n', 'Obsessive-Compulsive Disorder', 'Major Depressive Disorder', 'Panic Attack', 'Generalized Anxiety Disorder', NULL, 'C', 'The relapsing-remitting nature of Pheochromocytoma symptoms (tachycardia, sweating, fear) significantly overlaps with the clinical presentation of panic attacks.', NULL, NULL, 'moderate', 'manual', 'published', 16, 5, '2026-07-11 05:10:23', '2026-07-11 05:44:58'),
(179, 27, 1, 'Regarding the \'Rule of 10s\' for Pheochromocytoma, what percentage of cases are traditionally thought to occur in the paediatric population?\n', '10%', '1%', '50%', '25%', NULL, 'A', 'The Rule of 10s specifies that 10% of cases occur in children.', NULL, NULL, 'moderate', 'manual', 'published', 17, 5, '2026-07-11 05:10:23', '2026-07-11 05:45:12'),
(180, 27, 1, 'A patient with presents with a serum sodium of and is experiencing seizures. In addition to fluid restriction, what is the most appropriate acute management for this severe hyponatraemia?\n', 'Intravenous dextrose in water', 'Normal saline bolus', 'Oral salt tablets', 'Intravenous hypertonic saline', NULL, 'D', 'Severe, symptomatic hyponatraemia (e.g., seizures) requires careful administration of hypertonic saline to raise sodium levels.', NULL, NULL, 'moderate', 'manual', 'published', 18, 5, '2026-07-11 05:10:23', '2026-07-11 05:45:40'),
(181, 27, 1, 'Which of the following describes the correct sequence of the \'5 Ps\' of Pheochromocytoma presentation as mentioned in the source material?\n', 'Pressure, Pain, Perspiration, Palpitations, Pallor', 'Pallor, Pitting oedema, Pain, Pressure, Perspiration', 'Paresthesia, Polyuria, Polydipsia, Pain, Pallor', 'Pressure, Purpura, Pruritus, Pain, Palpitations', NULL, 'A', 'These represent hypertension, headache, sweating, tachycardia, and skin changes during an episode.', NULL, NULL, 'moderate', 'manual', 'published', 19, 5, '2026-07-11 05:10:23', '2026-07-11 05:45:58'),
(182, 27, 1, 'In the context of pathophysiology of SIADH, what role do Atrial Natriuretic Peptide and Brain Natriuretic Peptide play?', 'They increase sodium reabsorption to balance water gain', 'They promote natriuresis, which helps normalise volume but worsens hyponatraemia', 'They inhibit the receptors in the collecting duct', 'They stimulate the release of more from the hypothalamus', NULL, 'B', 'and cause the kidneys to excrete sodium in an attempt to shed excess volume, which further lowers serum sodium levels.', NULL, NULL, 'moderate', 'manual', 'published', 20, 5, '2026-07-11 05:10:23', '2026-07-11 05:21:16'),
(183, 27, 1, 'Von Hippel-Lindau syndrome is a genetic association for Pheochromocytoma. What other condition was mentioned as being linked to the gene?\n', 'Small Cell Lung Carcinoma', 'Medullary Thyroid Carcinoma', 'Renal Cell Carcinoma', 'Neuroblastoma', NULL, 'C', 'The source notes that gene mutations are famously associated with Renal Cell Carcinoma.', NULL, NULL, 'moderate', 'manual', 'published', 21, 5, '2026-07-11 05:10:23', '2026-07-11 05:20:27'),
(184, 27, 1, 'A patient is diagnosed with SIADH . In addition to a low serum sodium, which of the following findings regarding urea is most likely?\n', 'High serum urea and high serum creatinine', 'Normal serum urea and low urinary urea', 'High serum urea and low urinary urea', 'Low serum urea and high urinary urea', NULL, 'D', 'The body\'s attempt to excrete water and the dilution of the ECF lead to low serum urea levels while urea is still present in the concentrated urine.', NULL, NULL, 'moderate', 'manual', 'published', 22, 5, '2026-07-11 05:10:23', '2026-07-11 05:19:54'),
(186, 27, 1, 'A 38-year-old woman is being prepared for the removal of a Pheochromocytoma. Two weeks before the surgery, she is started on Phenoxybenzamine. After several days of this therapy, a beta-blocker is added. What is the primary reason for adding the beta-blocker only *after* the alpha-blocker?', 'To increase the half-life of Phenoxybenzamine', 'To prevent reflex bradycardia', 'To stimulate the release of more catecholamines', 'To avoid unopposed alpha-mediated vasoconstriction', NULL, 'D', 'To avoid unopposed alpha mediated vasoconstriction, an alpha blocker is added before', NULL, NULL, 'moderate', 'manual', 'published', 24, 5, '2026-07-11 05:10:23', '2026-07-11 05:13:31'),
(187, 26, 1, 'A 68-year-old woman with a history of hypertension and osteoporosis is being evaluated for a change in her medication regimen. Which of the following diuretics would be most beneficial for her, considering her bone density concerns?\n? Hint: Consider which class of diuretics is known to decrease the excretion of calcium.', 'Furosemide', 'Acetazolamide', 'Bendroflumethiazide', 'Spironolactone', NULL, 'C', 'This drug reduces the renal excretion of calcium, which helps in preserving bone density in patients with osteoporosis.', NULL, NULL, 'moderate', 'manual', 'published', 1, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(188, 26, 1, 'A 72-year-old male is admitted to the emergency department with acute shortness of breath and coarse basal crackles. A chest X-ray confirms acute pulmonary oedema. Which treatment is the most appropriate immediate management to reduce cardiac preload?\n? Hint: Identify a potent agent used specifically for rapid volume depletion in emergency fluid overload.', 'IV Furosemide', 'Oral Bendroflumethiazide', 'Mannitol', 'Eplerenone', NULL, 'A', 'Intravenous loop diuretics are the standard for rapid fluid removal and preload reduction in acute heart failure scenarios.', NULL, NULL, 'moderate', 'manual', 'published', 2, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(189, 26, 1, 'A patient with primary hyperaldosteronism is prescribed spironolactone. After three months, he develops painful enlargement of his breast tissue. What is the most appropriate next step in pharmacological management?\n? Hint: Consider a more selective alternative within the same diuretic class that lacks anti-androgenic activity.', 'Switch to Eplerenone', 'Switch to Acetazolamide', 'Add Bendroflumethiazide', 'Increase the dose of Spironolactone', NULL, 'A', 'Eplerenone is a more selective aldosterone antagonist that does not block androgen receptors, thus avoiding gynaecomastia.', NULL, NULL, 'moderate', 'manual', 'published', 3, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(190, 26, 1, 'A mountain climber taking prophylactic medication for acute mountain sickness presents with lethargy and deep, rapid breathing. Arterial blood gas analysis reveals a low and low bicarbonate. Which mechanism explains this finding?\n? Hint: Think about the drug of choice for altitude sickness and its effect on bicarbonate handling.', 'Inhibition of carbonic anhydrase in the early proximal convoluted tubule', 'Antagonism of aldosterone receptors in the collecting duct', 'Blockade of reabsorption in the distal convoluted tubule', 'Inhibition of the symporter', NULL, 'A', 'Acetazolamide inhibits bicarbonate reabsorption, leading to a loss of base in the urine and resulting in metabolic acidosis.', NULL, NULL, 'moderate', 'manual', 'published', 4, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(191, 26, 1, 'A patient with chronic heart failure who is taking Digoxin is started on a new diuretic for worsening peripheral oedema. Two weeks later, the patient reports seeing yellow halos and having palpitations. Which electrolyte disturbance most likely precipitated this condition?\n? Hint: Identify the electrolyte abnormality common to loop diuretics that sensitises the heart to Digoxin.', 'Hypercalcaemia', 'Hyperkalaemia', 'Hyponatraemia', 'Hypokalaemia', NULL, 'D', 'Loop and thiazide diuretics cause potassium loss, and low potassium levels significantly increase the risk of digoxin toxicity.', NULL, NULL, 'moderate', 'manual', 'published', 5, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(192, 26, 1, 'An athlete with known Hypertrophic Obstructive Cardiomyopathy (HOCM) presents with mild hypertension. Which of the following should be avoided when managing his blood pressure?\n? Hint: Recall which class of drugs is contraindicated because reducing intravascular volume can worsen cardiac outflow obstruction.', 'Lifestyle modifications', 'Beta-blockers', 'Diuretic therapy', 'Calcium channel blockers', NULL, 'C', 'Diuretics are generally contraindicated in HOCM as they reduce preload, which can worsen the outflow tract obstruction.', NULL, NULL, 'moderate', 'manual', 'published', 6, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(193, 26, 1, 'A patient is prescribed a diuretic that acts specifically on the thick ascending limb of the loop of Henle. Which of the following is a potential adverse effect associated with this drug\'s mechanism of action?\n? Hint: Focus on the side effects of loop diuretics beyond just potassium loss.', 'Hypercalcaemia', 'Gynaecomastia', 'Metabolic acidosis', 'Hypomagnesaemia', NULL, 'D', 'Loop diuretics inhibit the symporter, which also disrupts the electrical gradient necessary for magnesium reabsorption.', NULL, NULL, 'moderate', 'manual', 'published', 7, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(194, 26, 1, 'A 50-year-old man with a history of gout and newly diagnosed hypertension is being considered for diuretic therapy. Which metabolic disturbance should the clinician be most concerned about if a thiazide is prescribed?\n? Hint: Think about which diuretic-induced metabolic change directly impacts the crystals involved in gout.', 'Hypokalaemia', 'Hyperuricaemia', 'Hyperlipidaemia', 'Hyperglycaemia', NULL, 'B', 'Thiazides compete with uric acid for secretion in the kidneys, leading to elevated serum uric acid levels which can trigger gout flares.', NULL, NULL, 'moderate', 'manual', 'published', 8, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(195, 26, 1, 'During a routine follow-up, a patient on spironolactone is found to have a serum potassium level of . What is the most appropriate clinical action?\n? Hint: Determine the necessary safety protocol when a patient develops significant hyperkalaemia on a potassium-sparing agent.', 'Reduce the dose by half', 'Advise the patient to increase water intake', 'Discontinue the drug immediately', 'Switch to Furosemide to balance the potassium', NULL, 'C', 'Hyperkalaemia is a critical and potentially life-threatening complication of potassium-sparing diuretics that necessitates immediate cessation.', NULL, NULL, 'moderate', 'manual', 'published', 9, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(196, 26, 1, 'A patient with End-Stage Renal Disease (ESRD) presents with severe fluid overload. Despite high doses of intravenous loop diuretics, there is no clinical improvement or increase in urine output. What is the next best step in management?\n? Hint: Consider the definitive treatment for fluid overload when the kidneys are no longer responsive to pharmacological agents.', 'Haemodialysis', 'Add a thiazide diuretic for sequential nephron blockade', 'Switch to Spironolactone', 'Administer IV Mannitol', NULL, 'A', 'When fluid overload is refractory to diuretics in the setting of advanced renal failure, mechanical fluid removal via dialysis is required.', NULL, NULL, 'moderate', 'manual', 'published', 10, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(197, 26, 1, 'An elderly patient with hypertension and Type 2 Diabetes is started on Bendroflumethiazide. What effect might this medication have on his glycaemic control?\n? Hint: Recall the term used to describe the metabolic impact of thiazides on blood sugar.', 'It may lead to new-onset hyperglycaemia or impaired glucose tolerance', 'It causes hypoglycaemia by increasing renal glucose excretion', 'It has no known effect on blood glucose levels', 'It will likely improve insulin sensitivity', NULL, 'A', 'Thiazides are known for their \'diabetogenic\' effect, which can worsen glucose management in diabetic or pre-diabetic patients.', NULL, NULL, 'moderate', 'manual', 'published', 11, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(198, 26, 1, 'Which of the following describes the mechanism of action of Spironolactone?\n? Hint: Identify the \'Salt and Water Guard\' mentioned in the text and the drug that blocks it.', 'Antagonism of aldosterone at the cortical collecting tubule', 'Inhibition of the symporter in the loop of Henle', 'Inhibition of carbonic anhydrase in the proximal tubule', 'Inhibition of the cotransporter in the early distal tubule', NULL, 'A', 'Spironolactone blocks the aldosterone receptor, preventing the reabsorption of sodium and the excretion of potassium and acid.', NULL, NULL, 'moderate', 'manual', 'published', 12, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(199, 26, 1, 'A patient presents with hypertension and hypokalaemia. They admit to consuming large quantities of a specific herbal tea. Which substance can mimic the effects of aldosterone, potentially complicating the diagnosis?\n? Hint: Look for a common root extract that can cause a pseudo-hyperaldosteronism state.', 'Licorice tea', 'Green tea', 'Chamomile tea', 'Peppermint tea', NULL, 'A', 'Excessive consumption of licorice can mimic aldosterone, leading to sodium retention (hypertension) and potassium loss (hypokalaemia).', NULL, NULL, 'moderate', 'manual', 'published', 13, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(200, 26, 1, 'Which diuretic is primarily indicated for the reduction of intraocular pressure in glaucoma or intracranial pressure in neurological emergencies?\n? Hint: Think about the class that uses osmotic pressure to move fluid from specific compartments.', 'Bendroflumethiazide', 'Mannitol', 'Bumetanide', 'Spironolactone', NULL, 'B', 'Mannitol is an osmotic diuretic that draws fluid out of tissues and into the vascular space, acting primarily on the PCT.', NULL, NULL, 'moderate', 'manual', 'published', 14, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(201, 26, 1, 'A patient reports feeling lightheaded and nearly fainting every time they stand up quickly after starting a new medication for heart failure. Which mechanism most likely explains this symptom?\n? Hint: Relate the patient\'s positional symptoms to the rapid removal of fluid by certain diuretics.', 'Hypoglycaemia from a diabetogenic effect', 'Direct central nervous system toxicity', 'Metabolic acidosis-induced dizziness', 'Orthostatic hypotension from rapid volume depletion', NULL, 'D', 'Potent diuretics like loop diuretics frequently cause orthostatic hypotension when moving from a supine to standing position.', NULL, NULL, 'moderate', 'manual', 'published', 15, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(202, 26, 1, 'A 45-year-old male is diagnosed with Conn syndrome (primary hyperaldosteronism). Which segment of the nephron is the primary target for the medication used to manage this condition?\n? Hint: Focus on where the \'Salt and Water Guard\' is located within the nephron.', 'Thick ascending limb of the loop of Henle', 'Early segment of the distal convoluted tubule', 'Cortical collecting tubule', 'Early proximal convoluted tubule', NULL, 'C', 'Potassium-sparing diuretics like spironolactone act on the distal portion of the nephron, specifically the collecting ducts.', NULL, NULL, 'moderate', 'manual', 'published', 16, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(203, 26, 1, 'Why is Acetazolamide specifically effective for acute mountain sickness?\n? Hint: Consider the specific acid-base disturbance this drug causes and how it might affect a climber\'s physiology.', 'It prevents the loss of potassium in the urine', 'It rapidly increases the volume of the intravascular space', 'It treats symptoms through the induction of metabolic acidosis', 'It acts as a potent vasodilator of the pulmonary arteries', NULL, 'C', 'The metabolic acidosis caused by carbonic anhydrase inhibition can help drive respiration and alleviate altitude symptoms.', NULL, NULL, 'moderate', 'manual', 'published', 17, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(204, 26, 1, 'A patient with hypertension is found to have elevated serum cholesterol and triglycerides after beginning a new medication. Which diuretic is most likely responsible for this metabolic disturbance?\n? Hint: Identify the class of diuretics associated with \'metabolic disturbances\' including hyperuricaemia and hyperlipidaemia.', 'Acetazolamide', 'Spironolactone', 'Furosemide', 'Bendroflumethiazide', NULL, 'D', 'Hyperlipidaemia is a documented metabolic adverse effect associated with thiazide diuretics.', NULL, NULL, 'moderate', 'manual', 'published', 18, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(205, 26, 1, 'In the context of diuretic therapy, which of the following is true regarding the mechanism of loop diuretics?\n? Hint: Recall the three-ion symporter found in the thick ascending limb of the loop of Henle.', 'They act as osmotic agents in the proximal tubule', 'They inhibit the reabsorption in the DCT', 'They block androgen receptors in males', 'They inhibit the symporter', NULL, 'D', 'This specific symporter is located in the thick ascending limb and is the primary target for agents like furosemide.', NULL, NULL, 'moderate', 'manual', 'published', 19, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(206, 26, 1, 'Which diuretic is considered the standard immediate management for life-threatening fluid overload and acute pulmonary oedema?\n? Hint: Choose the most potent and fastest-acting diuretic mentioned for emergency cardiac situations.', 'IV Mannitol', 'IV Furosemide', 'Bendroflumethiazide', 'Oral Spironolactone', NULL, 'B', 'Intravenous loop diuretics provide the rapid diuresis and preload reduction necessary in emergency pulmonary oedema.', NULL, NULL, 'moderate', 'manual', 'published', 20, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(207, 26, 1, 'A patient with hypertension and recurrent gout is started on a diuretic. Which medication would be least appropriate for this patient?\n? Hint: Consider which diuretic class is known to increase uric acid levels.', 'Eplerenone', 'Bendroflumethiazide', 'Acetazolamide', 'Mannitol', NULL, 'B', 'Thiazides can cause hyperuricaemia, which can trigger or worsen gout, making them less ideal for this patient.', NULL, NULL, 'moderate', 'manual', 'published', 21, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(208, 26, 1, 'What is the primary site of action for Carbonic Anhydrase Inhibitors like Acetazolamide?\n? Hint: Identify the earliest segment of the nephron mentioned in the source material.', 'Cortical collecting tubule', 'Early proximal convoluted tubule', 'Thick ascending limb', 'Early distal convoluted tubule', NULL, 'B', 'Carbonic anhydrase inhibitors work at the beginning of the nephron to inhibit bicarbonate and sodium reabsorption.', NULL, NULL, 'moderate', 'manual', 'published', 22, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(209, 26, 1, 'Which of the following describes a key clinical difference between spironolactone and eplerenone?\n? Hint: Think about why a clinician would switch a male patient from one to the other.', 'Spironolactone decreases calcium excretion, while eplerenone increases it', 'Eplerenone acts on the loop of Henle, while spironolactone acts on the collecting duct', 'Eplerenone is more selective and avoids androgen receptor blockade', 'Spironolactone is used for acute mountain sickness, while eplerenone is not', NULL, 'C', 'This selectivity prevents the side effect of gynaecomastia, which is common with spironolactone.', NULL, NULL, 'moderate', 'manual', 'published', 23, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(210, 26, 1, 'A patient with Congestive Heart Failure (CHF) is noted to have \'coarse basal crackles\' and shortness of breath. Which class of diuretics is primarily used to treat these symptomatic signs of fluid overload?\n? Hint: Identify the class described as \'potent agents used for rapid fluid removal\' in heart failure.', 'Carbonic Anhydrase Inhibitors', 'Thiazide Diuretics', 'Osmotic Diuretics', 'Loop Diuretics', NULL, 'D', 'Loop diuretics like furosemide and bumetanide are potent agents used to manage the symptoms of fluid overload in CHF.', NULL, NULL, 'moderate', 'manual', 'published', 24, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(211, 26, 1, 'Which electrolyte abnormality is the most clinically significant concern when using loop diuretics due to the risk of cardiac arrhythmias?\n? Hint: Focus on the potassium shift caused by loop diuretics.', 'Hypokalaemia', 'Hypernatraemia', 'Hyperkalaemia', 'Hyperglycaemia', NULL, 'A', 'Low potassium levels can lead to dangerous cardiac arrhythmias and are a major side effect of loop diuretics.', NULL, NULL, 'moderate', 'manual', 'published', 25, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(212, 26, 1, 'A diuretic is described as acting on the \'early segment of the distal convoluted tubule\'. What is its primary mechanism of action?\n? Hint: Associate the site (early DCT) with the specific electrolyte inhibition.', 'Osmotic draw of water into the proximal tubule', 'Antagonism of aldosterone receptors', 'Inhibition of sodium-chloride () reabsorption', 'Inhibition of the symporter', NULL, 'C', 'This is the primary mechanism of thiazide diuretics acting in the early DCT.', NULL, NULL, 'moderate', 'manual', 'published', 26, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(213, 26, 1, 'In a patient with severe renal failure where diuretics have failed to control fluid overload, what is the next logical step in management according to the clinical considerations provided?\n? Hint: Look for a non-pharmacological method of fluid removal.', 'Start a high-dose Spironolactone infusion', 'Administer IV Acetazolamide', 'Increase the dose of Bumetanide', 'Haemodialysis', NULL, 'D', 'The source material specifies that haemodialysis is the necessary next step when fluid overload is refractory to diuretics in renal failure.', NULL, NULL, 'moderate', 'manual', 'published', 27, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(214, 26, 1, 'A patient taking Spironolactone is warned about potential hyperkalaemia. Which part of the nephron is the drug acting on to cause this effect?\n? Hint: Identify the distal site where the final adjustments to potassium excretion are made.', 'Cortical collecting tubule', 'Thick ascending limb', 'Early distal convoluted tubule', 'Early proximal tubule', NULL, 'A', 'This distal portion of the nephron is where potassium-sparing diuretics block sodium reabsorption and potassium excretion.', NULL, NULL, 'moderate', 'manual', 'published', 28, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(215, 26, 1, 'Which of the following is a known metabolic disturbance associated with the \'diabetogenic\' effect of thiazide diuretics?\n? Hint: Focus on the term \'diabetogenic\' and its relation to blood sugar levels.', 'Hyperkalaemia', 'Metabolic acidosis', 'Hypouricaemia', 'Impaired glucose tolerance', NULL, 'D', 'Thiazides are associated with new-onset hyperglycaemia and impaired glucose handling.', NULL, NULL, 'moderate', 'manual', 'published', 29, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(216, 26, 1, 'A patient with bilateral adrenal hyperplasia presents with hypertension and is started on spironolactone. What is the pharmacological goal of this treatment?\n? Hint: Identify the hormone that is overproduced in Conn syndrome and the drug that antagonises it.', 'To decrease intracranial pressure', 'To block the \'Salt and Water Guard\' (aldosterone)', 'To inhibit the symporter', 'To induce metabolic acidosis for altitude adjustment', NULL, 'B', 'Spironolactone acts as an aldosterone antagonist to manage the effects of primary hyperaldosteronism.', NULL, NULL, 'moderate', 'manual', 'published', 30, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(217, 26, 1, 'A patient develops symptoms of primary hyperaldosteronism after consuming a large amount of a specific confectionary. Which condition or interaction is this mimicry most associated with?\n? Hint: Recall the non-drug substance that can cause hypertension and hypokalaemia.', 'Metabolic acidosis', 'Hypertrophic Obstructive Cardiomyopathy', 'Digoxin toxicity', 'Licorice tea consumption', NULL, 'D', 'Licorice mimics aldosterone effects, causing sodium retention and potassium loss, similar to Conn syndrome.', NULL, NULL, 'moderate', 'manual', 'published', 31, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(218, 26, 1, 'Which of the following describes the effect of loop diuretics on cardiac haemodynamics in the setting of fluid overload?\n? Hint: Think about how volume removal affects the amount of blood returning to the heart.', 'It effectively reduces cardiac preload', 'It increases bone density by decreasing calcium excretion', 'It increases cardiac afterload by peripheral vasoconstriction', 'It has no effect on cardiac volume but reduces intracranial pressure', NULL, 'A', 'Rapid diuresis decreases the volume of blood returning to the heart, which is critical in managing pulmonary oedema.', NULL, NULL, 'moderate', 'manual', 'published', 32, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(219, 26, 1, 'Bumetanide is classified as which type of diuretic?\n? Hint: Identify the class bumetanide shares with furosemide.', 'Loop Diuretic', 'Potassium-Sparing Diuretic', 'Thiazide Diuretic', 'Carbonic Anhydrase Inhibitor', NULL, 'A', 'Bumetanide, along with furosemide, acts on the thick ascending limb and is a potent loop diuretic.', NULL, NULL, 'moderate', 'manual', 'published', 33, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(220, 26, 1, 'A patient with hypertension and a history of osteoporosis is prescribed a thiazide. How does this drug help preserve her bone density?\n? Hint: Focus on the renal handling of calcium by this specific diuretic class.', 'By acting as a selective androgen receptor modulator', 'By decreasing calcium excretion in the urine', 'By increasing the absorption of calcium in the gut', 'By inhibiting the symporter', NULL, 'B', 'Thiazides promote calcium reabsorption in the distal tubule, which helps maintain bone minerals.', NULL, NULL, 'moderate', 'manual', 'published', 34, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23'),
(221, 26, 1, 'A diuretic that inhibits the cotransporter in the early distal convoluted tubule is likely to cause which of the following electrolyte patterns?\n? Hint: Review the metabolic side effects of thiazide diuretics.', 'Hypercalcaemia and Hyperkalaemia', 'Hypomagnesaemia and Hypocalcaemia', 'Hypokalaemia and Hyperuricaemia', 'Hyperkalaemia and Metabolic Acidosis', NULL, 'C', 'Thiazides cause potassium loss and can increase uric acid levels, potentially triggering gout.', NULL, NULL, 'moderate', 'manual', 'published', 35, 5, '2026-07-13 09:38:23', '2026-07-13 09:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `mcq_attempts`
--

CREATE TABLE `mcq_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `source` enum('challenge','practice','lecture','daily','mistakes','bank','revision') NOT NULL DEFAULT 'practice',
  `lecture_id` int(10) UNSIGNED DEFAULT NULL,
  `challenge_id` int(10) UNSIGNED DEFAULT NULL,
  `challenge_day` smallint(5) UNSIGNED DEFAULT NULL,
  `total_questions` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `correct_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `wrong_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `time_spent_seconds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mcq_attempts`
--

INSERT INTO `mcq_attempts` (`id`, `student_id`, `source`, `lecture_id`, `challenge_id`, `challenge_day`, `total_questions`, `correct_count`, `wrong_count`, `score`, `time_spent_seconds`, `started_at`, `submitted_at`, `created_at`) VALUES
(1, 17, 'practice', NULL, NULL, NULL, 50, 0, 50, 0.00, 7, '2026-07-05 15:47:40', '2026-07-05 15:47:40', '2026-07-05 15:47:40'),
(2, 13, 'practice', NULL, NULL, NULL, 10, 0, 10, 0.00, 4, '2026-07-08 10:38:27', '2026-07-08 10:38:27', '2026-07-08 10:38:27'),
(3, 19, 'practice', 23, NULL, NULL, 10, 2, 8, 20.00, 54, '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(4, 7, 'practice', 27, NULL, NULL, 20, 18, 2, 90.00, 469, '2026-07-11 07:29:41', '2026-07-11 07:29:41', '2026-07-11 07:29:41'),
(5, 16, 'practice', 26, NULL, NULL, 35, 9, 26, 25.71, 178, '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `mcq_attempt_answers`
--

CREATE TABLE `mcq_attempt_answers` (
  `id` int(10) UNSIGNED NOT NULL,
  `attempt_id` int(10) UNSIGNED NOT NULL,
  `mcq_id` int(10) UNSIGNED NOT NULL,
  `selected_option` enum('A','B','C','D','E') DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `time_spent_seconds` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mcq_attempt_answers`
--

INSERT INTO `mcq_attempt_answers` (`id`, `attempt_id`, `mcq_id`, `selected_option`, `is_correct`, `time_spent_seconds`) VALUES
(61, 3, 93, 'A', 1, 0),
(62, 3, 94, 'A', 0, 0),
(63, 3, 95, 'E', 1, 0),
(64, 3, 96, NULL, 0, 0),
(65, 3, 97, NULL, 0, 0),
(66, 3, 98, NULL, 0, 0),
(67, 3, 99, NULL, 0, 0),
(68, 3, 100, NULL, 0, 0),
(69, 3, 101, NULL, 0, 0),
(70, 3, 102, NULL, 0, 0),
(71, 4, 163, 'D', 1, 0),
(72, 4, 165, 'B', 1, 0),
(73, 4, 166, 'A', 1, 0),
(74, 4, 167, 'C', 1, 0),
(75, 4, 168, 'A', 0, 0),
(76, 4, 169, 'C', 1, 0),
(77, 4, 170, 'B', 1, 0),
(78, 4, 171, 'C', 1, 0),
(79, 4, 172, 'A', 1, 0),
(80, 4, 174, 'C', 1, 0),
(81, 4, 175, 'B', 1, 0),
(82, 4, 177, 'B', 0, 0),
(83, 4, 178, 'C', 1, 0),
(84, 4, 179, 'A', 1, 0),
(85, 4, 180, 'D', 1, 0),
(86, 4, 181, 'A', 1, 0),
(87, 4, 182, 'B', 1, 0),
(88, 4, 183, 'C', 1, 0),
(89, 4, 184, 'D', 1, 0),
(90, 4, 186, 'D', 1, 0),
(91, 5, 187, 'C', 1, 0),
(92, 5, 188, 'A', 1, 0),
(93, 5, 189, 'A', 1, 0),
(94, 5, 190, 'A', 1, 0),
(95, 5, 191, 'D', 1, 0),
(96, 5, 192, 'C', 1, 0),
(97, 5, 193, 'D', 1, 0),
(98, 5, 194, 'B', 1, 0),
(99, 5, 195, 'B', 0, 0),
(100, 5, 196, 'A', 1, 0),
(101, 5, 197, NULL, 0, 0),
(102, 5, 198, NULL, 0, 0),
(103, 5, 199, NULL, 0, 0),
(104, 5, 200, NULL, 0, 0),
(105, 5, 201, NULL, 0, 0),
(106, 5, 202, NULL, 0, 0),
(107, 5, 203, NULL, 0, 0),
(108, 5, 204, NULL, 0, 0),
(109, 5, 205, NULL, 0, 0),
(110, 5, 206, NULL, 0, 0),
(111, 5, 207, NULL, 0, 0),
(112, 5, 208, NULL, 0, 0),
(113, 5, 209, NULL, 0, 0),
(114, 5, 210, NULL, 0, 0),
(115, 5, 211, NULL, 0, 0),
(116, 5, 212, NULL, 0, 0),
(117, 5, 213, NULL, 0, 0),
(118, 5, 214, NULL, 0, 0),
(119, 5, 215, NULL, 0, 0),
(120, 5, 216, NULL, 0, 0),
(121, 5, 217, NULL, 0, 0),
(122, 5, 218, NULL, 0, 0),
(123, 5, 219, NULL, 0, 0),
(124, 5, 220, NULL, 0, 0),
(125, 5, 221, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `mentors`
--

CREATE TABLE `mentors` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mentors`
--

INSERT INTO `mentors` (`id`, `name`, `title`, `specialty`, `bio`, `avatar`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Dr. Sarah Ahmed', 'MD, FCPS', 'Internal Medicine', 'Board-certified internist with 15 years of teaching experience.', NULL, 1, 1, '2026-07-02 12:24:21'),
(2, 'Dr. Michael Chen', 'MD, PhD', 'Anatomy', 'Anatomy professor specializing in clinical correlations.', NULL, 2, 1, '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `sort_order`, `is_published`, `created_at`, `updated_at`) VALUES
(16, 1, 'Pathology', NULL, 0, 0, '2026-07-08 11:08:12', '2026-07-08 11:08:29'),
(17, 1, 'Micro', NULL, 0, 0, '2026-07-08 13:07:38', '2026-07-08 13:07:38'),
(18, 1, 'Anatomy', NULL, 0, 0, '2026-07-09 04:52:29', '2026-07-09 04:52:29'),
(19, 1, 'BIochemistry', NULL, 0, 0, '2026-07-10 11:55:14', '2026-07-10 11:55:14'),
(20, 1, 'Pharmacology', NULL, 0, 0, '2026-07-11 03:51:36', '2026-07-11 03:51:36');

-- --------------------------------------------------------

--
-- Table structure for table `note_highlights`
--

CREATE TABLE `note_highlights` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `lecture_id` int(10) UNSIGNED NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `highlighted_text` text NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT 'yellow',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `email_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `email_sent`, `created_at`, `read_at`) VALUES
(1, 5, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: %lZ0ER!lg29l', NULL, 1, 1, '2026-07-02 12:29:40', '2026-07-06 07:30:03'),
(2, 6, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: DjndM$tfSJ$S', NULL, 1, 1, '2026-07-02 12:30:03', '2026-07-02 17:46:39'),
(3, 7, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: Avxtbj#B6GzT', NULL, 1, 1, '2026-07-02 12:32:02', '2026-07-03 18:43:49'),
(4, 8, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: 84#RLo$oLAVB', NULL, 0, 1, '2026-07-02 12:32:37', NULL),
(5, 9, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: TXZsyhuPyt9H', NULL, 0, 1, '2026-07-02 12:33:09', NULL),
(6, 10, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: qfuWjchuYTH!', NULL, 0, 1, '2026-07-02 12:34:06', NULL),
(7, 11, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: npN6U9Q88ozG', NULL, 0, 1, '2026-07-02 12:34:39', NULL),
(8, 12, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: @!mOIPWq9xb8', NULL, 1, 1, '2026-07-02 12:35:18', '2026-07-05 06:41:43'),
(9, 13, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: D6tZEAz@$4FF', NULL, 0, 1, '2026-07-02 12:35:47', NULL),
(10, 14, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: aiHf!nk9@IfH', NULL, 0, 1, '2026-07-02 12:36:18', NULL),
(11, 15, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: RAqlQvhfTvoZ', NULL, 0, 1, '2026-07-02 12:36:55', NULL),
(12, 16, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: zfAH5yKp$mBF', NULL, 0, 1, '2026-07-02 12:37:23', NULL),
(13, 17, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: 7MQM2pms4oBZ', NULL, 0, 1, '2026-07-02 12:37:59', NULL),
(14, 14, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:05', NULL),
(15, 10, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:06', NULL),
(16, 16, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:06', NULL),
(17, 7, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 1, 1, '2026-07-03 06:10:06', '2026-07-03 18:43:49'),
(18, 17, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:06', NULL),
(19, 13, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:06', NULL),
(20, 12, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 1, 1, '2026-07-03 06:10:06', '2026-07-05 06:41:43'),
(21, 8, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:06', NULL),
(22, 11, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:06', NULL),
(23, 15, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:07', NULL),
(24, 9, 'new_assignment', 'New assignment posted', 'Assignment \"fsdfsd\" has been posted. Due: 2026-07-14 11:09:00.', '{\"assignment_id\":1}', 0, 1, '2026-07-03 06:10:07', NULL),
(25, 5, 'discussion_reply', 'New reply on your question', 'Someone replied to \"sfsdf\".', '{\"thread_id\":1,\"course_id\":1}', 1, 1, '2026-07-03 06:29:43', '2026-07-06 06:48:01'),
(26, 14, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 0, 1, '2026-07-04 20:01:30', NULL),
(27, 10, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 0, 1, '2026-07-04 20:01:30', NULL),
(28, 16, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-07 10:10:36'),
(29, 7, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-05 16:21:36'),
(30, 17, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-04 20:14:06'),
(31, 13, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-06 12:16:19'),
(32, 12, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-05 06:41:43'),
(33, 8, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-06 10:29:22'),
(34, 11, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-11 05:45:30'),
(35, 15, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 0, 1, '2026-07-04 20:01:30', NULL),
(36, 9, 'new_assignment', 'New assignment posted', 'Assignment \"Assignment 1\" has been posted. Due: 2026-07-08 01:00:00.', '{\"assignment_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:01:30', '2026-07-06 10:17:57'),
(37, 14, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 0, 1, '2026-07-04 20:06:08', NULL),
(38, 10, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 0, 1, '2026-07-04 20:06:08', NULL),
(39, 16, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:08', '2026-07-07 10:10:37'),
(40, 7, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:08', '2026-07-05 16:21:28'),
(41, 17, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:09', '2026-07-04 20:17:16'),
(42, 13, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:09', '2026-07-06 12:16:21'),
(43, 12, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:09', '2026-07-05 06:41:43'),
(44, 8, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:09', '2026-07-06 10:29:23'),
(45, 11, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:09', '2026-07-11 05:45:44'),
(46, 15, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 0, 1, '2026-07-04 20:06:09', NULL),
(47, 9, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Discussion 1\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:06:09', '2026-07-06 10:18:00'),
(48, 14, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 0, 1, '2026-07-04 20:14:32', NULL),
(49, 10, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 0, 1, '2026-07-04 20:14:32', NULL),
(50, 16, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:32', '2026-07-07 10:10:49'),
(51, 7, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:33', '2026-07-05 16:21:25'),
(52, 17, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:33', '2026-07-04 20:14:42'),
(53, 13, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:33', '2026-07-06 12:16:14'),
(54, 12, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:33', '2026-07-05 06:41:43'),
(55, 8, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:33', '2026-07-06 10:29:20'),
(56, 11, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:33', '2026-07-11 05:45:19'),
(57, 15, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 0, 1, '2026-07-04 20:14:33', NULL),
(58, 9, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 1\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":4,\"course_id\":1}', 1, 1, '2026-07-04 20:14:33', '2026-07-06 10:17:30'),
(59, 6, 'discussion_reply', 'New reply on your question', 'Someone replied to \"Discussion 1\".', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:17:30', '2026-07-05 15:32:03'),
(60, 6, 'discussion_reply', 'Your teacher replied', 'Your teacher replied to \"Discussion 1\".', '{\"thread_id\":2,\"course_id\":1}', 1, 1, '2026-07-04 20:18:09', '2026-07-05 15:32:03'),
(61, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(62, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(63, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(64, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(65, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(66, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(67, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 1, 1, '2026-07-05 07:42:03', '2026-07-06 12:19:41'),
(68, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(69, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(70, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(71, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:42:03', NULL),
(72, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:06', NULL),
(73, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:06', NULL),
(74, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(75, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(76, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(77, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(78, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 1, 1, '2026-07-05 07:50:07', '2026-07-06 12:19:41'),
(79, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(80, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(81, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(82, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 07:50:07', NULL),
(83, 5, 'ai_generation', 'Study resources ready for review', 'Generated study material for \"Bones, Joints and Surface Anatomy\" is ready. Review and approve it to publish.', '{\"lecture_id\":3}', 1, 0, '2026-07-05 08:29:04', '2026-07-06 07:30:03'),
(84, 5, 'ai_generation', 'Study resources ready for review', 'Generated study material for \"Bones, Joints and Surface Anatomy\" is ready. Review and approve it to publish.', '{\"lecture_id\":3}', 1, 0, '2026-07-05 08:30:34', '2026-07-06 07:30:03'),
(85, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:06', NULL),
(86, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:07', NULL),
(87, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:12', NULL),
(88, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:12', NULL),
(89, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:15', NULL),
(90, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:20', NULL),
(91, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 1, 1, '2026-07-05 08:31:20', '2026-07-06 12:19:41'),
(92, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:21', NULL),
(93, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:21', NULL),
(94, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:21', NULL),
(95, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:21', NULL),
(96, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:50', NULL),
(97, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:50', NULL),
(98, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:50', NULL),
(99, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:51', NULL),
(100, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:51', NULL),
(101, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:51', NULL),
(102, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 1, 1, '2026-07-05 08:31:51', '2026-07-06 12:19:41'),
(103, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:51', NULL),
(104, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:51', NULL),
(105, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:51', NULL),
(106, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Bones, Joints and Surface Anatomy\".', '{\"lecture_id\":3}', 0, 1, '2026-07-05 08:31:51', NULL),
(107, 14, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 0, 1, '2026-07-05 15:28:00', NULL),
(108, 10, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 0, 1, '2026-07-05 15:28:00', NULL),
(109, 16, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:00', '2026-07-07 10:10:49'),
(110, 7, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:00', '2026-07-05 16:21:25'),
(111, 17, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:00', '2026-07-05 15:42:32'),
(112, 13, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:00', '2026-07-06 12:16:14'),
(113, 12, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:00', '2026-07-06 12:19:00'),
(114, 8, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:00', '2026-07-06 10:29:20'),
(115, 11, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:01', '2026-07-11 05:45:19'),
(116, 15, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 0, 1, '2026-07-05 15:28:01', NULL),
(117, 9, 'new_quiz', 'New quiz available', 'Quiz \"Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":5,\"course_id\":1}', 1, 1, '2026-07-05 15:28:01', '2026-07-06 10:17:30'),
(118, 14, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 0, 1, '2026-07-05 15:29:23', NULL),
(119, 10, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 0, 1, '2026-07-05 15:29:23', NULL),
(120, 16, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-07 10:10:36'),
(121, 7, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-05 16:21:36'),
(122, 17, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-05 15:44:06'),
(123, 13, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-06 12:16:19'),
(124, 12, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-06 12:18:56'),
(125, 8, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-06 10:29:22'),
(126, 11, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-11 05:45:30'),
(127, 15, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 0, 1, '2026-07-05 15:29:23', NULL),
(128, 9, 'new_assignment', 'New assignment posted', 'Assignment \"1st Assignment\" has been posted. Due: 2026-07-08 20:28:00.', '{\"assignment_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:29:23', '2026-07-06 10:17:57'),
(129, 14, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 0, 1, '2026-07-05 15:32:24', NULL),
(130, 10, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 0, 1, '2026-07-05 15:32:24', NULL),
(131, 16, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:25', '2026-07-07 10:10:37'),
(132, 7, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:25', '2026-07-05 16:21:28'),
(133, 17, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:25', '2026-07-05 15:45:22'),
(134, 13, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:25', '2026-07-06 12:16:21'),
(135, 12, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:26', '2026-07-06 12:19:03'),
(136, 8, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:26', '2026-07-06 10:29:23'),
(137, 11, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:26', '2026-07-11 05:45:44'),
(138, 15, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 0, 1, '2026-07-05 15:32:26', NULL),
(139, 9, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Upper LIMB\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:32:26', '2026-07-06 10:18:00'),
(140, 6, 'ai_generation', 'Study resources ready for review', 'Generated study material for \"Brachial Plexus and Vasculature (4-7-2026)\" is ready. Review and approve it to publish.', '{\"lecture_id\":4}', 0, 0, '2026-07-05 15:35:52', NULL),
(141, 6, 'discussion_reply', 'New reply on your question', 'Someone replied to \"Upper LIMB\".', '{\"thread_id\":3,\"course_id\":1}', 1, 1, '2026-07-05 15:45:36', '2026-07-06 06:10:17'),
(142, 6, 'discussion_question', 'New student question', 'New question in Featured Course FCPS 1 Preparation Course: \"Uper Limb\".', '{\"thread_id\":4,\"course_id\":1}', 1, 1, '2026-07-05 16:27:45', '2026-07-06 10:55:05'),
(143, 14, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 0, 1, '2026-07-06 07:37:04', NULL),
(144, 10, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 0, 1, '2026-07-06 07:37:05', NULL),
(145, 16, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 1, 1, '2026-07-06 07:37:05', '2026-07-07 10:10:36'),
(146, 7, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 1, 1, '2026-07-06 07:37:05', '2026-07-07 09:47:24'),
(147, 17, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 0, 1, '2026-07-06 07:37:05', NULL),
(148, 13, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 1, 1, '2026-07-06 07:37:05', '2026-07-06 12:16:19'),
(149, 12, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 1, 1, '2026-07-06 07:37:05', '2026-07-06 12:18:56'),
(150, 8, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 1, 1, '2026-07-06 07:37:05', '2026-07-06 10:29:22'),
(151, 11, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 1, 1, '2026-07-06 07:37:06', '2026-07-11 05:45:30'),
(152, 15, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 0, 1, '2026-07-06 07:37:06', NULL),
(153, 9, 'new_assignment', 'New assignment posted', 'Assignment \"Solve the MCQs\" has been posted. Due: 2026-07-07 12:36:00.', '{\"assignment_id\":4,\"course_id\":1}', 1, 1, '2026-07-06 07:37:06', '2026-07-06 10:17:57'),
(154, 14, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 0, 1, '2026-07-06 07:38:48', NULL),
(155, 10, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 0, 1, '2026-07-06 07:38:48', NULL),
(156, 16, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 1, 1, '2026-07-06 07:38:48', '2026-07-07 10:10:36'),
(157, 7, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 1, 1, '2026-07-06 07:38:48', '2026-07-07 09:47:24'),
(158, 17, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 0, 1, '2026-07-06 07:38:48', NULL),
(159, 13, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 1, 1, '2026-07-06 07:38:48', '2026-07-06 12:16:19'),
(160, 12, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 1, 1, '2026-07-06 07:38:48', '2026-07-06 12:18:56'),
(161, 8, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 1, 1, '2026-07-06 07:38:48', '2026-07-06 10:29:22'),
(162, 11, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 1, 1, '2026-07-06 07:38:48', '2026-07-11 05:45:30'),
(163, 15, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 0, 1, '2026-07-06 07:38:48', NULL),
(164, 9, 'new_assignment', 'New assignment posted', 'Assignment \"Nephro Assignment\" has been posted. Due: 2026-07-06 12:38:00.', '{\"assignment_id\":5,\"course_id\":1}', 1, 1, '2026-07-06 07:38:48', '2026-07-06 10:17:57'),
(165, 14, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 0, 1, '2026-07-06 07:51:24', NULL),
(166, 10, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 0, 1, '2026-07-06 07:51:25', NULL),
(167, 16, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 1, 1, '2026-07-06 07:51:25', '2026-07-07 10:10:36'),
(168, 7, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 1, 1, '2026-07-06 07:51:25', '2026-07-07 09:47:24'),
(169, 17, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 0, 1, '2026-07-06 07:51:25', NULL),
(170, 13, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 1, 1, '2026-07-06 07:51:25', '2026-07-06 12:16:19'),
(171, 12, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 1, 1, '2026-07-06 07:51:25', '2026-07-06 12:18:56'),
(172, 8, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 1, 1, '2026-07-06 07:51:25', '2026-07-06 10:29:22'),
(173, 11, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 1, 1, '2026-07-06 07:51:25', '2026-07-11 05:45:30'),
(174, 15, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 0, 1, '2026-07-06 07:51:25', NULL),
(175, 9, 'new_assignment', 'New assignment posted', 'Assignment \"Polycystic Kidney Disease Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":6,\"course_id\":1}', 1, 1, '2026-07-06 07:51:25', '2026-07-06 10:17:57'),
(176, 14, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 0, 1, '2026-07-06 07:57:56', NULL),
(177, 10, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 0, 1, '2026-07-06 07:57:56', NULL),
(178, 16, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 1, 1, '2026-07-06 07:57:56', '2026-07-07 10:10:36'),
(179, 7, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 1, 1, '2026-07-06 07:57:56', '2026-07-07 09:47:24'),
(180, 17, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 0, 1, '2026-07-06 07:57:56', NULL),
(181, 13, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 1, 1, '2026-07-06 07:57:56', '2026-07-06 12:16:19'),
(182, 12, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 1, 1, '2026-07-06 07:57:56', '2026-07-06 12:18:56'),
(183, 8, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 1, 1, '2026-07-06 07:57:56', '2026-07-06 10:29:22'),
(184, 11, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 1, 1, '2026-07-06 07:57:56', '2026-07-11 05:45:30'),
(185, 15, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 0, 1, '2026-07-06 07:57:56', NULL),
(186, 9, 'new_assignment', 'New assignment posted', 'Assignment \"AKI Practice Questions\" has been posted. Due: 2026-07-06 23:59:00.', '{\"assignment_id\":7,\"course_id\":1}', 1, 1, '2026-07-06 07:57:56', '2026-07-06 10:17:57'),
(187, 14, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 0, 1, '2026-07-06 08:16:02', NULL),
(188, 10, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 0, 1, '2026-07-06 08:16:02', NULL),
(189, 16, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 1, 1, '2026-07-06 08:16:03', '2026-07-07 10:10:49'),
(190, 7, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 1, 1, '2026-07-06 08:16:03', '2026-07-06 15:50:02'),
(191, 17, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 0, 1, '2026-07-06 08:16:03', NULL),
(192, 13, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 1, 1, '2026-07-06 08:16:03', '2026-07-06 12:16:14'),
(193, 12, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 1, 1, '2026-07-06 08:16:08', '2026-07-06 12:19:00'),
(194, 8, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 1, 1, '2026-07-06 08:16:08', '2026-07-06 10:29:20'),
(195, 11, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 1, 1, '2026-07-06 08:16:08', '2026-07-11 05:45:19'),
(196, 15, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 0, 1, '2026-07-06 08:16:08', NULL),
(197, 9, 'new_quiz', 'New quiz available', 'Quiz \"Gomerulonephritis Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":8,\"course_id\":1}', 1, 1, '2026-07-06 08:16:08', '2026-07-06 10:17:30'),
(198, 14, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:21:04', NULL),
(199, 10, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:21:05', NULL),
(200, 16, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:21:05', '2026-07-07 10:10:49'),
(201, 7, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:21:05', '2026-07-06 15:50:02'),
(202, 17, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:21:06', NULL),
(203, 13, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:21:06', '2026-07-06 12:16:14'),
(204, 12, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:21:06', '2026-07-06 12:19:00'),
(205, 8, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:21:06', '2026-07-06 10:29:20'),
(206, 11, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:21:06', '2026-07-11 05:45:19'),
(207, 15, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:21:06', NULL),
(208, 9, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:21:06', '2026-07-06 10:17:30'),
(209, 14, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:22:06', NULL),
(210, 10, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:22:06', NULL),
(211, 16, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:22:06', '2026-07-07 10:10:49'),
(212, 7, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:22:07', '2026-07-06 15:50:02'),
(213, 17, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:22:07', NULL),
(214, 13, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:22:07', '2026-07-06 12:16:14'),
(215, 12, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:22:07', '2026-07-06 12:19:00'),
(216, 8, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:22:07', '2026-07-06 10:29:20'),
(217, 11, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:22:07', '2026-07-11 05:45:19'),
(218, 15, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 0, 1, '2026-07-06 08:22:07', NULL),
(219, 9, 'new_quiz', 'New quiz available', 'Quiz \"Polycystic Kidney Disease Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":9,\"course_id\":1}', 1, 1, '2026-07-06 08:22:08', '2026-07-06 10:17:30'),
(220, 14, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 0, 0, '2026-07-07 07:15:29', NULL),
(221, 10, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 0, 0, '2026-07-07 07:15:29', NULL),
(222, 16, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 1, 0, '2026-07-07 07:15:29', '2026-07-07 10:10:49'),
(223, 7, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 1, 0, '2026-07-07 07:15:29', '2026-07-07 09:47:20'),
(224, 17, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 0, 0, '2026-07-07 07:15:29', NULL),
(225, 13, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 1, 0, '2026-07-07 07:15:29', '2026-07-08 08:09:41'),
(226, 3, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 0, 0, '2026-07-07 07:15:29', NULL),
(227, 12, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 1, 0, '2026-07-07 07:15:29', '2026-07-07 11:00:50'),
(228, 8, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 1, 0, '2026-07-07 07:15:29', '2026-07-08 02:57:35'),
(229, 11, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 1, 0, '2026-07-07 07:15:29', '2026-07-11 05:45:19'),
(230, 15, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 0, 0, '2026-07-07 07:15:29', NULL),
(231, 9, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":10,\"course_id\":1}', 1, 0, '2026-07-07 07:15:29', '2026-07-10 16:05:59'),
(232, 14, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 0, 0, '2026-07-07 07:18:35', NULL),
(233, 10, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 0, 0, '2026-07-07 07:18:35', NULL),
(234, 16, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 1, 0, '2026-07-07 07:18:35', '2026-07-07 10:10:37'),
(235, 7, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 1, 0, '2026-07-07 07:18:35', '2026-07-07 09:47:31'),
(236, 17, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 0, 0, '2026-07-07 07:18:35', NULL);
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `email_sent`, `created_at`, `read_at`) VALUES
(237, 13, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 1, 0, '2026-07-07 07:18:35', '2026-07-08 08:09:08'),
(238, 3, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 0, 0, '2026-07-07 07:18:35', NULL),
(239, 12, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 1, 0, '2026-07-07 07:18:35', '2026-07-07 11:01:01'),
(240, 8, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 1, 0, '2026-07-07 07:18:35', '2026-07-08 02:58:27'),
(241, 11, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 1, 0, '2026-07-07 07:18:35', '2026-07-11 05:45:44'),
(242, 15, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 0, 0, '2026-07-07 07:18:35', NULL),
(243, 9, 'new_discussion', 'New discussion posted', 'Your teacher posted \"test dis\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":5,\"course_id\":1}', 1, 0, '2026-07-07 07:18:35', '2026-07-10 16:06:08'),
(244, 5, 'quiz_submitted', 'Quiz submitted', 'Ali  Imran Khan completed quiz \"Polycystic Kidney Disease Quiz\".', '{\"course_id\":1,\"quiz_id\":9,\"attempt_id\":14}', 1, 0, '2026-07-07 11:10:58', '2026-07-08 07:36:27'),
(245, 6, 'quiz_submitted', 'Quiz submitted', 'Ali  Imran Khan completed quiz \"Polycystic Kidney Disease Quiz\".', '{\"course_id\":1,\"quiz_id\":9,\"attempt_id\":14}', 1, 0, '2026-07-07 11:10:58', '2026-07-07 11:32:20'),
(246, 14, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 0, 0, '2026-07-07 12:00:40', NULL),
(247, 10, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 0, 0, '2026-07-07 12:00:40', NULL),
(248, 16, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 1, 0, '2026-07-07 12:00:40', '2026-07-07 13:07:00'),
(249, 7, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 1, 0, '2026-07-07 12:00:40', '2026-07-08 05:10:00'),
(250, 17, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 0, 0, '2026-07-07 12:00:40', NULL),
(251, 13, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 1, 0, '2026-07-07 12:00:40', '2026-07-08 08:09:41'),
(252, 3, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 0, 0, '2026-07-07 12:00:40', NULL),
(253, 12, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 1, 0, '2026-07-07 12:00:40', '2026-07-08 06:57:56'),
(254, 8, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 1, 0, '2026-07-07 12:00:40', '2026-07-08 02:57:35'),
(255, 11, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 1, 0, '2026-07-07 12:00:40', '2026-07-11 05:45:19'),
(256, 15, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 0, 0, '2026-07-07 12:00:40', NULL),
(257, 9, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":13,\"course_id\":1}', 1, 0, '2026-07-07 12:00:40', '2026-07-10 16:05:59'),
(258, 14, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 0, 0, '2026-07-07 12:33:07', NULL),
(259, 10, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 0, 0, '2026-07-07 12:33:07', NULL),
(260, 16, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 1, 0, '2026-07-07 12:33:07', '2026-07-07 13:06:00'),
(261, 7, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 1, 0, '2026-07-07 12:33:07', '2026-07-08 05:10:00'),
(262, 17, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 0, 0, '2026-07-07 12:33:07', NULL),
(263, 13, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 1, 0, '2026-07-07 12:33:07', '2026-07-08 08:09:41'),
(264, 3, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 0, 0, '2026-07-07 12:33:07', NULL),
(265, 12, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 1, 0, '2026-07-07 12:33:07', '2026-07-08 06:57:56'),
(266, 8, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 1, 0, '2026-07-07 12:33:07', '2026-07-08 02:57:35'),
(267, 11, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 1, 0, '2026-07-07 12:33:07', '2026-07-11 05:45:19'),
(268, 15, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 0, 0, '2026-07-07 12:33:07', NULL),
(269, 9, 'new_quiz', 'New quiz available', 'Quiz \"test quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":14,\"course_id\":1}', 1, 0, '2026-07-07 12:33:07', '2026-07-10 16:05:59'),
(270, 14, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 0, 0, '2026-07-07 12:34:59', NULL),
(271, 10, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 0, 0, '2026-07-07 12:34:59', NULL),
(272, 16, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 1, 0, '2026-07-07 12:34:59', '2026-07-07 13:06:56'),
(273, 7, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 1, 0, '2026-07-07 12:34:59', '2026-07-08 05:10:51'),
(274, 17, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 0, 0, '2026-07-07 12:34:59', NULL),
(275, 13, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 1, 0, '2026-07-07 12:34:59', '2026-07-08 08:09:08'),
(276, 3, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 0, 0, '2026-07-07 12:34:59', NULL),
(277, 12, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 1, 0, '2026-07-07 12:34:59', '2026-07-08 06:58:07'),
(278, 8, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 1, 0, '2026-07-07 12:34:59', '2026-07-08 02:58:27'),
(279, 11, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 1, 0, '2026-07-07 12:34:59', '2026-07-11 05:45:44'),
(280, 15, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 0, 0, '2026-07-07 12:34:59', NULL),
(281, 9, 'new_discussion', 'New discussion posted', 'Your teacher posted \"adsdad\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":6,\"course_id\":1}', 1, 0, '2026-07-07 12:34:59', '2026-07-10 16:06:08'),
(282, 5, 'quiz_submitted', 'Quiz submitted', 'Mahnoor  Asif completed quiz \"Gomerulonephritis Quiz\".', '{\"course_id\":1,\"quiz_id\":8,\"attempt_id\":15}', 1, 0, '2026-07-07 13:26:42', '2026-07-08 07:36:27'),
(283, 6, 'quiz_submitted', 'Quiz submitted', 'Mahnoor  Asif completed quiz \"Gomerulonephritis Quiz\".', '{\"course_id\":1,\"quiz_id\":8,\"attempt_id\":15}', 1, 0, '2026-07-07 13:26:42', '2026-07-08 06:41:43'),
(284, 5, 'discussion_question', 'New student question', 'New question in Featured Course FCPS 1 Preparation Course: \"A question from quiz\".', '{\"course_id\":1,\"thread_id\":7}', 1, 0, '2026-07-07 13:34:37', '2026-07-08 08:00:48'),
(285, 6, 'discussion_question', 'New student question', 'New question in Featured Course FCPS 1 Preparation Course: \"A question from quiz\".', '{\"course_id\":1,\"thread_id\":7}', 1, 0, '2026-07-07 13:34:37', '2026-07-08 11:23:34'),
(286, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:22', NULL),
(287, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:22', NULL),
(288, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(289, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(290, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(291, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(292, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(293, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 1, 1, '2026-07-08 07:40:23', '2026-07-08 15:20:21'),
(294, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(295, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(296, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(297, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"lecture 1\".', '{\"lecture_id\":14,\"course_id\":1}', 0, 1, '2026-07-08 07:40:23', NULL),
(298, 14, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 0, 0, '2026-07-08 07:51:54', NULL),
(299, 10, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 0, 0, '2026-07-08 07:51:54', NULL),
(300, 16, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 1, 0, '2026-07-08 07:51:54', '2026-07-13 08:22:03'),
(301, 7, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 1, 0, '2026-07-08 07:51:54', '2026-07-09 16:18:15'),
(302, 17, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 0, 0, '2026-07-08 07:51:54', NULL),
(303, 13, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 1, 0, '2026-07-08 07:51:54', '2026-07-08 08:09:41'),
(304, 3, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 0, 0, '2026-07-08 07:51:54', NULL),
(305, 12, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 1, 0, '2026-07-08 07:51:54', '2026-07-08 15:20:09'),
(306, 8, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 0, 0, '2026-07-08 07:51:54', NULL),
(307, 11, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 1, 0, '2026-07-08 07:51:54', '2026-07-11 05:45:19'),
(308, 15, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 0, 0, '2026-07-08 07:51:54', NULL),
(309, 9, 'new_quiz', 'New quiz available', 'Quiz \"Nephro Pathology Quiz - April 2026\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":7,\"course_id\":1}', 1, 0, '2026-07-08 07:51:54', '2026-07-10 16:05:59'),
(310, 14, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 0, 0, '2026-07-08 08:01:16', NULL),
(311, 10, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 0, 0, '2026-07-08 08:01:16', NULL),
(312, 16, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 1, 0, '2026-07-08 08:01:16', '2026-07-13 08:22:25'),
(313, 7, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 1, 0, '2026-07-08 08:01:16', '2026-07-10 04:58:44'),
(314, 17, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 0, 0, '2026-07-08 08:01:16', NULL),
(315, 13, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 1, 0, '2026-07-08 08:01:16', '2026-07-08 08:09:08'),
(316, 3, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 0, 0, '2026-07-08 08:01:16', NULL),
(317, 12, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 1, 0, '2026-07-08 08:01:16', '2026-07-08 15:20:12'),
(318, 8, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 0, 0, '2026-07-08 08:01:16', NULL),
(319, 11, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 1, 0, '2026-07-08 08:01:16', '2026-07-11 05:45:44'),
(320, 15, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 0, 0, '2026-07-08 08:01:16', NULL),
(321, 9, 'new_discussion', 'New discussion posted', 'Your teacher posted \"Loop Diuretics\" in Featured Course FCPS 1 Preparation Course.', '{\"thread_id\":8,\"course_id\":1}', 1, 0, '2026-07-08 08:01:16', '2026-07-10 16:06:08'),
(322, 14, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 0, 1, '2026-07-08 08:03:25', NULL),
(323, 10, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 0, 1, '2026-07-08 08:03:25', NULL),
(324, 16, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 1, 1, '2026-07-08 08:03:25', '2026-07-13 08:22:04'),
(325, 7, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 1, 1, '2026-07-08 08:03:25', '2026-07-09 20:15:32'),
(326, 17, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 0, 1, '2026-07-08 08:03:25', NULL),
(327, 13, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 1, 1, '2026-07-08 08:03:25', '2026-07-08 08:09:25'),
(328, 3, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 0, 1, '2026-07-08 08:03:25', NULL),
(329, 12, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 1, 1, '2026-07-08 08:03:26', '2026-07-08 15:20:11'),
(330, 8, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 0, 1, '2026-07-08 08:03:26', NULL),
(331, 11, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 1, 1, '2026-07-08 08:03:26', '2026-07-11 05:45:30'),
(332, 15, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 0, 1, '2026-07-08 08:03:26', NULL),
(333, 9, 'new_assignment', 'New Assignment posted', 'Assignment \"Notes\" has been posted. Due: 2026-07-10 15:03:00.', '{\"assignment_id\":10,\"course_id\":1}', 1, 1, '2026-07-08 08:03:26', '2026-07-10 16:06:02'),
(334, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:10', NULL),
(335, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:10', NULL),
(336, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(337, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(338, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(339, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(340, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(341, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 1, 1, '2026-07-08 08:04:11', '2026-07-08 15:20:21'),
(342, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(343, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(344, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(345, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:11', NULL),
(346, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:41', NULL),
(347, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:42', NULL),
(348, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(349, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(350, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(351, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(352, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(353, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 1, 1, '2026-07-08 08:04:43', '2026-07-08 15:20:21'),
(354, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(355, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(356, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(357, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:43', NULL),
(358, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:59', NULL),
(359, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:59', NULL),
(360, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:04:59', NULL),
(361, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(362, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(363, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(364, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(365, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 1, 1, '2026-07-08 08:05:00', '2026-07-08 15:20:21'),
(366, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(367, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(368, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(369, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 08:05:00', NULL),
(370, 5, 'quiz_submitted', 'Quiz submitted', 'Dr. Hafiza  Asma kanwal completed quiz \"Polycystic Kidney Disease Quiz\".', '{\"course_id\":1,\"quiz_id\":9,\"attempt_id\":20}', 1, 0, '2026-07-08 09:45:31', '2026-07-08 10:17:46'),
(371, 6, 'quiz_submitted', 'Quiz submitted', 'Dr. Hafiza  Asma kanwal completed quiz \"Polycystic Kidney Disease Quiz\".', '{\"course_id\":1,\"quiz_id\":9,\"attempt_id\":20}', 1, 0, '2026-07-08 09:45:31', '2026-07-08 11:23:22'),
(372, 5, 'quiz_submitted', 'Quiz submitted', 'Dr. Hafiza  Asma kanwal completed quiz \"Nephro Pathology Quiz - April 2026\".', '{\"course_id\":1,\"quiz_id\":7,\"attempt_id\":21}', 1, 0, '2026-07-08 09:46:07', '2026-07-08 10:17:46'),
(373, 6, 'quiz_submitted', 'Quiz submitted', 'Dr. Hafiza  Asma kanwal completed quiz \"Nephro Pathology Quiz - April 2026\".', '{\"course_id\":1,\"quiz_id\":7,\"attempt_id\":21}', 1, 0, '2026-07-08 09:46:07', '2026-07-08 11:23:22'),
(374, 14, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 0, 0, '2026-07-08 10:21:32', NULL),
(375, 10, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 0, 0, '2026-07-08 10:21:32', NULL),
(376, 16, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 1, 0, '2026-07-08 10:21:32', '2026-07-13 08:22:03'),
(377, 7, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 1, 0, '2026-07-08 10:21:32', '2026-07-09 16:18:15'),
(378, 17, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 0, 0, '2026-07-08 10:21:32', NULL),
(379, 13, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 1, 0, '2026-07-08 10:21:32', '2026-07-08 10:27:28'),
(380, 3, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 0, 0, '2026-07-08 10:21:32', NULL),
(381, 12, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 1, 0, '2026-07-08 10:21:32', '2026-07-08 15:20:09'),
(382, 8, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 0, 0, '2026-07-08 10:21:32', NULL),
(383, 11, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 1, 0, '2026-07-08 10:21:32', '2026-07-11 05:45:19'),
(384, 15, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 0, 0, '2026-07-08 10:21:32', NULL),
(385, 9, 'new_quiz', 'New quiz available', 'Quiz \"Renal Quiz\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":15,\"course_id\":1}', 1, 0, '2026-07-08 10:21:32', '2026-07-10 16:05:59'),
(386, 14, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 0, 1, '2026-07-08 10:26:22', NULL),
(387, 10, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 0, 1, '2026-07-08 10:26:22', NULL),
(388, 16, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 1, 1, '2026-07-08 10:26:22', '2026-07-13 08:22:04'),
(389, 7, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 1, 1, '2026-07-08 10:26:22', '2026-07-09 20:15:32'),
(390, 17, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 0, 1, '2026-07-08 10:26:22', NULL),
(391, 13, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 1, 1, '2026-07-08 10:26:22', '2026-07-08 10:27:48'),
(392, 3, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 0, 1, '2026-07-08 10:26:22', NULL),
(393, 12, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 1, 1, '2026-07-08 10:26:23', '2026-07-08 15:20:11'),
(394, 8, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 0, 1, '2026-07-08 10:26:23', NULL),
(395, 11, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 1, 1, '2026-07-08 10:26:23', '2026-07-11 05:45:30'),
(396, 15, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 0, 1, '2026-07-08 10:26:23', NULL),
(397, 9, 'new_assignment', 'New Assignment posted', 'Assignment \"Answer Key of Renal Quiz\" has been posted. Due: 2026-07-08 15:25:00.', '{\"assignment_id\":11,\"course_id\":1}', 1, 1, '2026-07-08 10:26:23', '2026-07-10 16:06:02'),
(398, 5, 'quiz_submitted', 'Quiz submitted', 'Dr. Hafiza  Asma kanwal completed quiz \"Renal Quiz\".', '{\"course_id\":1,\"quiz_id\":15,\"attempt_id\":22}', 1, 0, '2026-07-08 10:27:39', '2026-07-08 10:34:50'),
(399, 6, 'quiz_submitted', 'Quiz submitted', 'Dr. Hafiza  Asma kanwal completed quiz \"Renal Quiz\".', '{\"course_id\":1,\"quiz_id\":15,\"attempt_id\":22}', 1, 0, '2026-07-08 10:27:39', '2026-07-08 11:23:22'),
(400, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:41', NULL),
(401, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:41', NULL),
(402, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:41', NULL),
(403, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(404, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(405, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(406, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(407, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 1, 1, '2026-07-08 10:37:42', '2026-07-08 15:20:21'),
(408, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(409, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(410, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(411, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:37:42', NULL),
(412, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:56', NULL),
(413, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:56', NULL),
(414, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:56', NULL),
(415, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:56', NULL),
(416, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:56', NULL),
(417, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:57', NULL),
(418, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:57', NULL),
(419, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 1, 1, '2026-07-08 10:42:57', '2026-07-08 15:20:21'),
(420, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:57', NULL),
(421, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:57', NULL),
(422, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:57', NULL),
(423, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:42:57', NULL),
(424, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(425, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(426, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(427, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(428, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(429, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(430, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(431, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 1, 1, '2026-07-08 10:43:15', '2026-07-08 15:20:21'),
(432, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(433, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(434, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(435, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:15', NULL),
(436, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:21', NULL),
(437, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:21', NULL),
(438, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:21', NULL),
(439, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(440, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(441, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(442, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(443, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 1, 1, '2026-07-08 10:43:22', '2026-07-08 15:20:21'),
(444, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(445, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(446, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(447, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Group A Streptococcus, Group B Streptococcus and Enterococci\".', '{\"lecture_id\":15,\"course_id\":1}', 0, 1, '2026-07-08 10:43:22', NULL),
(448, 17, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:09', NULL),
(449, 16, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:09', NULL),
(450, 15, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:09', NULL),
(451, 14, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:09', NULL),
(452, 13, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:09', NULL);
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `email_sent`, `created_at`, `read_at`) VALUES
(453, 12, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 1, 1, '2026-07-08 10:51:11', '2026-07-08 15:20:21'),
(454, 11, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:11', NULL),
(455, 10, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:11', NULL),
(456, 9, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:11', NULL),
(457, 8, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:11', NULL),
(458, 7, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:11', NULL),
(459, 3, 'class_scheduled', 'New class scheduled', '\"Endocrinology\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 21:45.', '{\"course_id\":1,\"schedule_id\":1}', 0, 1, '2026-07-08 10:51:11', NULL),
(460, 17, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:21', NULL),
(461, 16, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:21', NULL),
(462, 15, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:21', NULL),
(463, 14, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:21', NULL),
(464, 13, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:21', NULL),
(465, 12, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 1, 1, '2026-07-08 10:54:22', '2026-07-08 15:20:21'),
(466, 11, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:22', NULL),
(467, 10, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:22', NULL),
(468, 9, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:22', NULL),
(469, 8, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:22', NULL),
(470, 7, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:22', NULL),
(471, 3, 'class_scheduled', 'New class scheduled', '\"Endo\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 8, 2026 09:00.', '{\"course_id\":1,\"schedule_id\":2}', 0, 1, '2026-07-08 10:54:23', NULL),
(472, 17, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:18', NULL),
(473, 16, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:18', NULL),
(474, 15, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:18', NULL),
(475, 14, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:18', NULL),
(476, 13, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:19', NULL),
(477, 12, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 1, 1, '2026-07-08 11:41:19', '2026-07-08 15:20:21'),
(478, 11, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:19', NULL),
(479, 10, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:19', NULL),
(480, 9, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:19', NULL),
(481, 8, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:19', NULL),
(482, 7, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:19', NULL),
(483, 3, 'class_scheduled', 'New class scheduled', 'Class \"Live Class\" for Featured Course FCPS 1 Preparation Course is scheduled on Jul 21, 2026 4:40 PM.', '{\"session_id\":1,\"course_id\":1}', 0, 1, '2026-07-08 11:41:19', NULL),
(484, 14, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 0, 1, '2026-07-09 05:50:58', NULL),
(485, 10, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 0, 1, '2026-07-09 05:50:58', NULL),
(486, 16, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 1, 1, '2026-07-09 05:50:58', '2026-07-13 08:22:04'),
(487, 7, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 1, 1, '2026-07-09 05:50:58', '2026-07-09 20:15:32'),
(488, 17, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 0, 1, '2026-07-09 05:50:59', NULL),
(489, 13, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 1, 1, '2026-07-09 05:50:59', '2026-07-09 08:00:31'),
(490, 3, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 0, 1, '2026-07-09 05:50:59', NULL),
(491, 12, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 1, 1, '2026-07-09 05:50:59', '2026-07-11 06:04:57'),
(492, 8, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 0, 1, '2026-07-09 05:50:59', NULL),
(493, 11, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 1, 1, '2026-07-09 05:50:59', '2026-07-11 05:45:30'),
(494, 15, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 0, 1, '2026-07-09 05:50:59', NULL),
(495, 9, 'new_assignment', 'New Assignment posted', 'Assignment \"BIOCHEMISTRY LECTURES\" has been posted. Due: 2026-07-19 23:59:00.', '{\"assignment_id\":12,\"course_id\":1}', 1, 1, '2026-07-09 05:50:59', '2026-07-10 16:06:02'),
(496, 14, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 0, 1, '2026-07-09 06:46:20', NULL),
(497, 10, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 0, 1, '2026-07-09 06:46:20', NULL),
(498, 16, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 1, 1, '2026-07-09 06:46:22', '2026-07-13 08:22:04'),
(499, 7, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 1, 1, '2026-07-09 06:46:22', '2026-07-09 20:15:32'),
(500, 17, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 0, 1, '2026-07-09 06:46:22', NULL),
(501, 13, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 1, 1, '2026-07-09 06:46:22', '2026-07-09 08:00:31'),
(502, 3, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 0, 1, '2026-07-09 06:46:22', NULL),
(503, 12, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 1, 1, '2026-07-09 06:46:22', '2026-07-11 06:04:57'),
(504, 8, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 0, 1, '2026-07-09 06:46:22', NULL),
(505, 11, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 1, 1, '2026-07-09 06:46:22', '2026-07-11 05:45:30'),
(506, 15, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 0, 1, '2026-07-09 06:46:22', NULL),
(507, 9, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Renal Papillary Necrosis and Sickle Cell Trait\" has been posted. Due: 2026-07-10 23:59:00.', '{\"assignment_id\":13,\"course_id\":1}', 1, 1, '2026-07-09 06:46:22', '2026-07-10 16:06:02'),
(508, 14, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 0, 0, '2026-07-09 06:54:20', NULL),
(509, 10, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 0, 0, '2026-07-09 06:54:20', NULL),
(510, 16, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 1, 0, '2026-07-09 06:54:20', '2026-07-13 08:22:03'),
(511, 7, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 1, 0, '2026-07-09 06:54:20', '2026-07-09 16:18:15'),
(512, 17, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 0, 0, '2026-07-09 06:54:20', NULL),
(513, 13, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 0, 0, '2026-07-09 06:54:20', NULL),
(514, 3, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 0, 0, '2026-07-09 06:54:20', NULL),
(515, 12, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 1, 0, '2026-07-09 06:54:20', '2026-07-11 06:04:55'),
(516, 8, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 0, 0, '2026-07-09 06:54:20', NULL),
(517, 11, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 1, 0, '2026-07-09 06:54:20', '2026-07-11 05:45:19'),
(518, 15, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 0, 0, '2026-07-09 06:54:20', NULL),
(519, 9, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy: Upper Limb\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":16,\"course_id\":1}', 1, 0, '2026-07-09 06:54:20', '2026-07-10 16:05:59'),
(520, 14, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 0, 1, '2026-07-09 07:23:56', NULL),
(521, 10, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 0, 1, '2026-07-09 07:23:56', NULL),
(522, 16, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 1, 1, '2026-07-09 07:23:56', '2026-07-13 08:22:04'),
(523, 7, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 1, 1, '2026-07-09 07:23:56', '2026-07-09 20:15:32'),
(524, 17, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 0, 1, '2026-07-09 07:23:57', NULL),
(525, 13, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 1, 1, '2026-07-09 07:23:57', '2026-07-09 08:00:31'),
(526, 3, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 0, 1, '2026-07-09 07:23:57', NULL),
(527, 12, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 1, 1, '2026-07-09 07:23:57', '2026-07-11 06:04:57'),
(528, 8, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 0, 1, '2026-07-09 07:23:57', NULL),
(529, 11, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 1, 1, '2026-07-09 07:23:57', '2026-07-11 05:45:30'),
(530, 15, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 0, 1, '2026-07-09 07:23:57', NULL),
(531, 9, 'new_assignment', 'New Assignment posted', 'Assignment \"Nephro Pathology - Cryoglobulin Associated Kidney Disease\" has been posted. Due: 2026-09-30 23:59:00.', '{\"assignment_id\":14,\"course_id\":1}', 1, 1, '2026-07-09 07:23:58', '2026-07-10 16:06:02'),
(532, 14, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 0, 0, '2026-07-09 07:30:48', NULL),
(533, 10, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 0, 0, '2026-07-09 07:30:48', NULL),
(534, 16, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 1, 0, '2026-07-09 07:30:48', '2026-07-13 08:22:03'),
(535, 7, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 1, 0, '2026-07-09 07:30:48', '2026-07-09 16:18:15'),
(536, 17, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 0, 0, '2026-07-09 07:30:48', NULL),
(537, 13, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 0, 0, '2026-07-09 07:30:48', NULL),
(538, 3, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 0, 0, '2026-07-09 07:30:48', NULL),
(539, 12, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 1, 0, '2026-07-09 07:30:48', '2026-07-11 06:04:55'),
(540, 8, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 0, 0, '2026-07-09 07:30:48', NULL),
(541, 11, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 1, 0, '2026-07-09 07:30:48', '2026-07-11 05:45:19'),
(542, 15, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 0, 0, '2026-07-09 07:30:48', NULL),
(543, 9, 'new_quiz', 'New quiz available', 'Quiz \"Anatomy Upper limb Quiz 2\" is now available. Check the Quizzes tab in your course.', '{\"quiz_id\":17,\"course_id\":1}', 1, 0, '2026-07-09 07:30:48', '2026-07-10 16:05:59'),
(544, 14, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 0, 1, '2026-07-09 07:44:45', NULL),
(545, 10, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 0, 1, '2026-07-09 07:44:45', NULL),
(546, 16, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 1, 1, '2026-07-09 07:44:45', '2026-07-13 08:22:04'),
(547, 7, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 1, 1, '2026-07-09 07:44:45', '2026-07-09 20:15:32'),
(548, 17, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 0, 1, '2026-07-09 07:44:45', NULL),
(549, 13, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 1, 1, '2026-07-09 07:44:45', '2026-07-09 08:00:31'),
(550, 3, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 0, 1, '2026-07-09 07:44:45', NULL),
(551, 12, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 1, 1, '2026-07-09 07:44:45', '2026-07-11 06:04:57'),
(552, 8, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 0, 1, '2026-07-09 07:44:45', NULL),
(553, 11, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 1, 1, '2026-07-09 07:44:45', '2026-07-11 05:45:30'),
(554, 15, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 0, 1, '2026-07-09 07:44:45', NULL),
(555, 9, 'new_assignment', 'New Assignment posted', 'Assignment \"Anatomy UPPER LIMB\" has been posted. Due: 2026-07-15 12:46:00.', '{\"assignment_id\":15,\"course_id\":1}', 1, 1, '2026-07-09 07:44:45', '2026-07-10 16:06:02'),
(556, 18, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: $TpAVcBZRf23', NULL, 0, 1, '2026-07-09 07:46:16', NULL),
(557, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:14', NULL),
(558, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:15', NULL),
(559, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:15', NULL),
(560, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:15', NULL),
(561, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:15', NULL),
(562, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:15', NULL),
(563, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:15', NULL),
(564, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:16', NULL),
(565, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:16', NULL),
(566, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:16', NULL),
(567, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:16', NULL),
(568, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:16', NULL),
(569, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:53:16', NULL),
(570, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:44', NULL),
(571, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:44', NULL),
(572, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(573, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(574, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(575, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(576, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(577, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(578, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(579, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(580, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(581, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(582, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:55:45', NULL),
(583, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(584, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(585, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(586, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(587, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(588, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(589, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(590, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(591, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(592, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(593, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(594, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:03', NULL),
(595, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:56:04', NULL),
(596, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:30', NULL),
(597, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:30', NULL),
(598, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(599, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(600, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(601, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(602, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(603, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(604, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(605, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(606, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(607, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(608, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:31', NULL),
(609, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:40', NULL),
(610, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:40', NULL),
(611, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:40', NULL),
(612, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:40', NULL),
(613, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:40', NULL),
(614, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(615, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(616, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(617, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(618, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(619, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(620, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(621, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"All Lectures - 29th June to 6th July\".', '{\"lecture_id\":16,\"course_id\":1}', 0, 1, '2026-07-09 07:59:41', NULL),
(622, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(623, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(624, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(625, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(626, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(627, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(628, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(629, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(630, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(631, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(632, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(633, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:29', NULL),
(634, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:05:30', NULL),
(635, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:13', NULL),
(636, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(637, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(638, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(639, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(640, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(641, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(642, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(643, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(644, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(645, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(646, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(647, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Renal Stone Slides\".', '{\"lecture_id\":22,\"course_id\":1}', 0, 1, '2026-07-09 08:10:14', NULL),
(648, 19, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: Yl%OCkr$6vdi', NULL, 1, 1, '2026-07-09 08:25:27', '2026-07-09 10:03:59'),
(649, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:29', NULL),
(650, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(651, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(652, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(653, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(654, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(655, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(656, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(657, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:30', NULL),
(658, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:31', NULL),
(659, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:31', NULL),
(660, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:31', NULL),
(661, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 1, 1, '2026-07-09 09:54:31', '2026-07-09 10:03:59'),
(662, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:54:31', NULL),
(663, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:18', NULL),
(664, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:18', NULL),
(665, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(666, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(667, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(668, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(669, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(670, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(671, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL);
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `email_sent`, `created_at`, `read_at`) VALUES
(672, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(673, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(674, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(675, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 1, 1, '2026-07-09 09:55:19', '2026-07-09 10:03:59'),
(676, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:19', NULL),
(677, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(678, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(679, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(680, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(681, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(682, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(683, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(684, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(685, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(686, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(687, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:30', NULL),
(688, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:31', NULL),
(689, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 1, 1, '2026-07-09 09:55:31', '2026-07-09 10:03:59'),
(690, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:55:31', NULL),
(691, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(692, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(693, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(694, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(695, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(696, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(697, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(698, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(699, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(700, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(701, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(702, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(703, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 1, 1, '2026-07-09 09:56:55', '2026-07-09 10:03:59'),
(704, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:56:55', NULL),
(705, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:14', NULL),
(706, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:14', NULL),
(707, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:14', NULL),
(708, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:14', NULL),
(709, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(710, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(711, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(712, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(713, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(714, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(715, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(716, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(717, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 1, 1, '2026-07-09 09:59:15', '2026-07-09 10:03:59'),
(718, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:15', NULL),
(719, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(720, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(721, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(722, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(723, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(724, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(725, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(726, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(727, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(728, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(729, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(730, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:30', NULL),
(731, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 1, 1, '2026-07-09 09:59:30', '2026-07-09 10:03:59'),
(732, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephrotic Syndrome Slides\".', '{\"lecture_id\":23,\"course_id\":1}', 0, 1, '2026-07-09 09:59:31', NULL),
(733, 20, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: dELMixszJ4S7', NULL, 0, 1, '2026-07-09 10:04:41', NULL),
(734, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:13', NULL),
(735, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:13', NULL),
(736, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:13', NULL),
(737, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:13', NULL),
(738, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:14', NULL),
(739, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:14', NULL),
(740, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:14', NULL),
(741, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:14', NULL),
(742, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:14', NULL),
(743, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:14', NULL),
(744, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:14', NULL),
(745, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:15', NULL),
(746, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:15', NULL),
(747, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 1, 1, '2026-07-09 11:07:15', '2026-07-09 11:25:28'),
(748, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:07:15', NULL),
(749, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(750, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(751, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(752, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(753, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(754, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(755, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(756, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(757, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(758, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(759, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:14', NULL),
(760, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:15', NULL),
(761, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:15', NULL),
(762, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 1, 1, '2026-07-09 11:13:15', '2026-07-09 11:25:28'),
(763, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:13:15', NULL),
(764, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(765, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(766, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(767, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(768, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(769, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(770, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(771, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(772, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(773, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(774, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(775, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(776, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:27', NULL),
(777, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 1, 1, '2026-07-09 11:14:28', '2026-07-09 11:25:28'),
(778, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:28', NULL),
(779, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(780, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(781, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(782, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(783, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(784, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(785, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(786, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(787, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(788, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(789, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(790, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(791, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(792, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 1, 1, '2026-07-09 11:14:29', '2026-07-09 11:25:28'),
(793, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:14:29', NULL),
(794, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:23', NULL),
(795, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(796, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(797, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(798, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(799, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(800, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(801, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(802, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(803, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(804, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(805, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(806, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(807, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 1, 1, '2026-07-09 11:20:24', '2026-07-09 11:25:28'),
(808, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:24', NULL),
(809, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(810, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(811, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(812, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(813, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(814, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(815, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(816, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(817, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(818, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(819, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(820, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(821, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:30', NULL),
(822, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 1, 1, '2026-07-09 11:20:30', '2026-07-09 11:25:28'),
(823, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Nephritic Syndromes\".', '{\"lecture_id\":24,\"course_id\":1}', 0, 1, '2026-07-09 11:20:31', NULL),
(824, 21, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: DDD$xisPSYb4', NULL, 0, 1, '2026-07-09 13:03:57', NULL),
(825, 5, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy: Upper Limb\".', '{\"course_id\":1,\"quiz_id\":16,\"attempt_id\":23}', 1, 0, '2026-07-09 16:25:14', '2026-07-10 11:54:31'),
(826, 6, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy: Upper Limb\".', '{\"course_id\":1,\"quiz_id\":16,\"attempt_id\":23}', 1, 0, '2026-07-09 16:25:14', '2026-07-11 12:24:57'),
(827, 5, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":25}', 1, 0, '2026-07-10 04:48:16', '2026-07-10 11:54:31'),
(828, 6, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":25}', 1, 0, '2026-07-10 04:48:16', '2026-07-11 12:24:57'),
(829, 5, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":25}', 1, 0, '2026-07-10 04:48:16', '2026-07-10 11:54:31'),
(830, 6, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":25}', 1, 0, '2026-07-10 04:48:16', '2026-07-11 12:24:57'),
(831, 5, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":25}', 1, 0, '2026-07-10 04:48:16', '2026-07-10 11:54:31'),
(832, 6, 'quiz_submitted', 'Quiz submitted', 'MOBARRA  ASIM completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":25}', 1, 0, '2026-07-10 04:48:16', '2026-07-11 12:24:57'),
(833, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(834, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(835, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(836, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(837, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(838, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(839, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(840, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(841, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(842, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(843, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(844, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(845, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(846, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(847, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(848, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:29:47', NULL),
(849, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:29', NULL),
(850, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(851, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(852, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(853, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(854, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(855, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(856, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(857, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(858, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(859, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(860, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(861, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(862, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:30', NULL),
(863, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:31', NULL),
(864, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:36:31', NULL),
(865, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:12', NULL),
(866, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:12', NULL),
(867, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:12', NULL),
(868, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(869, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(870, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(871, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(872, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(873, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(874, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(875, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(876, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(877, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(878, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(879, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(880, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:13', NULL),
(881, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:52', NULL),
(882, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:52', NULL),
(883, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:52', NULL),
(884, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:52', NULL),
(885, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:52', NULL),
(886, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:52', NULL);
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `email_sent`, `created_at`, `read_at`) VALUES
(887, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(888, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(889, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(890, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(891, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(892, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(893, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(894, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(895, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(896, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:37:53', NULL),
(897, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:51', NULL),
(898, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:51', NULL),
(899, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:51', NULL),
(900, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:51', NULL),
(901, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(902, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(903, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(904, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(905, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(906, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(907, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(908, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(909, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(910, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(911, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(912, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:40:52', NULL),
(913, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:02', NULL),
(914, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:02', NULL),
(915, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:02', NULL),
(916, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:02', NULL),
(917, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:02', NULL),
(918, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:02', NULL),
(919, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:02', NULL),
(920, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(921, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(922, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(923, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(924, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(925, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(926, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(927, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(928, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Carbohydrate Metabolism - Glycolysis, Gluconeogenesis and HMP Shunt Pathway\".', '{\"lecture_id\":25,\"course_id\":1}', 0, 1, '2026-07-10 12:41:03', NULL),
(929, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:21', NULL),
(930, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(931, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(932, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(933, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(934, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(935, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(936, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(937, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(938, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(939, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(940, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(941, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(942, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(943, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(944, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:01:22', NULL),
(945, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:38', NULL),
(946, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:39', NULL),
(947, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:39', NULL),
(948, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:39', NULL),
(949, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:39', NULL),
(950, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:39', NULL),
(951, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:40', NULL),
(952, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:40', NULL),
(953, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:40', NULL),
(954, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:40', NULL),
(955, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:40', NULL),
(956, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:40', NULL),
(957, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:40', NULL),
(958, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:41', NULL),
(959, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:41', NULL),
(960, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:07:41', NULL),
(961, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:23', NULL),
(962, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(963, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(964, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(965, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(966, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(967, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(968, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(969, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(970, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(971, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(972, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(973, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(974, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(975, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:24', NULL),
(976, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:10:25', NULL),
(977, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:10', NULL),
(978, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:10', NULL),
(979, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(980, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(981, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(982, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(983, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(984, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(985, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(986, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(987, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(988, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(989, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(990, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(991, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(992, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Pheochromocytoma and SIADH - 9 July 2026 (Wednesday)\".', '{\"lecture_id\":27,\"course_id\":1}', 0, 1, '2026-07-11 05:46:11', NULL),
(993, 7, 'badge_unlocked', 'Achievement unlocked: 7-Day Streak', 'Studied every day for 7 days in a row', '{\"badge_code\":\"streak_7\"}', 0, 0, '2026-07-11 07:13:32', NULL),
(994, 5, 'assignment_submitted', 'New assignment submission', 'MOBARRA  ASIM submitted \"Anatomy UPPER LIMB\".', '{\"course_id\":1,\"assignment_id\":15}', 1, 0, '2026-07-11 09:46:22', '2026-07-13 07:00:28'),
(995, 6, 'assignment_submitted', 'New assignment submission', 'MOBARRA  ASIM submitted \"Anatomy UPPER LIMB\".', '{\"course_id\":1,\"assignment_id\":15}', 1, 0, '2026-07-11 09:46:22', '2026-07-11 12:28:00'),
(996, 5, 'quiz_submitted', 'Quiz submitted', 'Ali  Imran Khan completed quiz \"Anatomy: Upper Limb\".', '{\"course_id\":1,\"quiz_id\":16,\"attempt_id\":26}', 1, 0, '2026-07-13 06:27:55', '2026-07-13 07:00:32'),
(997, 6, 'quiz_submitted', 'Quiz submitted', 'Ali  Imran Khan completed quiz \"Anatomy: Upper Limb\".', '{\"course_id\":1,\"quiz_id\":16,\"attempt_id\":26}', 1, 0, '2026-07-13 06:27:55', '2026-07-13 07:31:02'),
(998, 5, 'quiz_submitted', 'Quiz submitted', 'Ali  Imran Khan completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":27}', 1, 0, '2026-07-13 06:46:20', '2026-07-13 07:00:32'),
(999, 6, 'quiz_submitted', 'Quiz submitted', 'Ali  Imran Khan completed quiz \"Anatomy Upper limb Quiz 2\".', '{\"course_id\":1,\"quiz_id\":17,\"attempt_id\":27}', 1, 0, '2026-07-13 06:46:20', '2026-07-13 07:31:02'),
(1000, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:23', NULL),
(1001, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:23', NULL),
(1002, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:23', NULL),
(1003, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:23', NULL),
(1004, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1005, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1006, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1007, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1008, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1009, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1010, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1011, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1012, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1013, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:24', NULL),
(1014, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:25', NULL),
(1015, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:38:25', NULL),
(1016, 14, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1017, 10, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1018, 16, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1019, 7, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1020, 17, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1021, 13, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1022, 3, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1023, 12, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1024, 8, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:41', NULL),
(1025, 20, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:42', NULL),
(1026, 11, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:42', NULL),
(1027, 15, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:42', NULL),
(1028, 18, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:42', NULL),
(1029, 21, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:42', NULL),
(1030, 19, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:42', NULL),
(1031, 9, 'ai_content_published', 'New revision material available', 'New notes, flashcards and MCQs are ready for \"Diuretic - 8 July 2026\".', '{\"lecture_id\":26,\"course_id\":1}', 0, 1, '2026-07-13 09:39:42', NULL),
(1032, 22, 'welcome', 'Welcome to NextGen Medics', 'Your account has been created. Your temporary password is: rK$!EkoerT@z', NULL, 0, 1, '2026-07-14 03:08:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quiz_type` enum('mcq','descriptive','mixed') DEFAULT 'mcq',
  `duration_minutes` int(10) UNSIGNED DEFAULT 30,
  `passing_marks` decimal(6,2) DEFAULT 50.00,
  `total_marks` decimal(6,2) DEFAULT 100.00,
  `random_questions` tinyint(1) DEFAULT 0,
  `question_pool_size` int(10) UNSIGNED DEFAULT NULL,
  `negative_marking` tinyint(1) DEFAULT 0,
  `negative_mark_value` decimal(4,2) DEFAULT 0.00,
  `shuffle_questions` tinyint(1) DEFAULT 0,
  `shuffle_options` tinyint(1) DEFAULT 0,
  `max_attempts` int(10) UNSIGNED DEFAULT 1,
  `show_leaderboard` tinyint(1) DEFAULT 0,
  `auto_evaluate` tinyint(1) DEFAULT 1,
  `show_review` tinyint(1) NOT NULL DEFAULT 1,
  `available_from` datetime DEFAULT NULL,
  `available_until` datetime DEFAULT NULL,
  `status` enum('draft','published','closed') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `teacher_id`, `title`, `description`, `quiz_type`, `duration_minutes`, `passing_marks`, `total_marks`, `random_questions`, `question_pool_size`, `negative_marking`, `negative_mark_value`, `shuffle_questions`, `shuffle_options`, `max_attempts`, `show_leaderboard`, `auto_evaluate`, `show_review`, `available_from`, `available_until`, `status`, `created_at`, `updated_at`) VALUES
(16, 1, 6, 'Anatomy: Upper Limb', '', 'mcq', 15, 85.00, 12.00, 0, NULL, 0, 0.00, 1, 0, 1, 1, 1, 1, NULL, NULL, 'published', '2026-07-09 06:53:39', '2026-07-09 06:54:20'),
(17, 1, 6, 'Anatomy Upper limb Quiz 2', '', 'mcq', 30, 80.00, 100.00, 0, NULL, 0, 0.00, 1, 0, 1, 1, 1, 1, NULL, NULL, 'published', '2026-07-09 07:30:25', '2026-07-09 07:30:48');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `attempt_number` int(10) UNSIGNED DEFAULT 1,
  `score` decimal(6,2) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `passed` tinyint(1) DEFAULT NULL,
  `status` enum('in_progress','submitted','evaluated') DEFAULT 'in_progress',
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `evaluated_at` timestamp NULL DEFAULT NULL,
  `evaluated_by` int(10) UNSIGNED DEFAULT NULL,
  `time_taken_seconds` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `quiz_id`, `student_id`, `attempt_number`, `score`, `percentage`, `passed`, `status`, `started_at`, `submitted_at`, `evaluated_at`, `evaluated_by`, `time_taken_seconds`) VALUES
(23, 16, 7, 1, 8.00, 66.67, 0, 'evaluated', '2026-07-09 16:18:28', '2026-07-09 16:25:14', '2026-07-09 16:25:14', NULL, 405),
(24, 17, 7, 1, NULL, NULL, NULL, 'in_progress', '2026-07-09 16:26:52', NULL, NULL, NULL, NULL),
(25, 17, 7, 2, 25.00, 96.15, 1, 'evaluated', '2026-07-10 04:40:09', '2026-07-10 04:48:16', '2026-07-10 04:48:16', NULL, 471),
(26, 16, 12, 1, 8.00, 66.67, 0, 'evaluated', '2026-07-13 06:22:31', '2026-07-13 06:27:55', '2026-07-13 06:27:55', NULL, 323),
(27, 17, 12, 1, 20.00, 76.92, 0, 'evaluated', '2026-07-13 06:29:52', '2026-07-13 06:46:20', '2026-07-13 06:46:20', NULL, 988);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempt_answers`
--

CREATE TABLE `quiz_attempt_answers` (
  `id` int(10) UNSIGNED NOT NULL,
  `attempt_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `selected_option_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_option_ids`)),
  `text_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `marks_awarded` decimal(6,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quiz_attempt_answers`
--

INSERT INTO `quiz_attempt_answers` (`id`, `attempt_id`, `question_id`, `selected_option_ids`, `text_answer`, `is_correct`, `marks_awarded`, `feedback`) VALUES
(159, 23, 195, '[779]', NULL, 1, 1.00, NULL),
(160, 23, 196, '[784]', NULL, 0, 0.00, NULL),
(161, 23, 197, '[785]', NULL, 1, 1.00, NULL),
(162, 23, 198, '[789]', NULL, 1, 1.00, NULL),
(163, 23, 199, '[795]', NULL, 1, 1.00, NULL),
(164, 23, 200, '[797]', NULL, 0, 0.00, NULL),
(165, 23, 201, '[803]', NULL, 0, 0.00, NULL),
(166, 23, 202, '[805]', NULL, 1, 1.00, NULL),
(167, 23, 203, '[810]', NULL, 1, 1.00, NULL),
(168, 23, 204, '[816]', NULL, 1, 1.00, NULL),
(169, 23, 205, '[819]', NULL, 0, 0.00, NULL),
(170, 23, 206, '[824]', NULL, 1, 1.00, NULL),
(171, 25, 207, '[825]', NULL, 1, 1.00, NULL),
(172, 25, 208, '[829]', NULL, 1, 1.00, NULL),
(173, 25, 209, '[836]', NULL, 1, 1.00, NULL),
(174, 25, 210, '[837]', NULL, 1, 1.00, NULL),
(175, 25, 211, '[841]', NULL, 1, 1.00, NULL),
(176, 25, 212, '[848]', NULL, 1, 1.00, NULL),
(177, 25, 213, '[852]', NULL, 1, 1.00, NULL),
(178, 25, 214, '[853]', NULL, 1, 1.00, NULL),
(179, 25, 215, '[859]', NULL, 1, 1.00, NULL),
(180, 25, 216, '[862]', NULL, 1, 1.00, NULL),
(181, 25, 217, '[865]', NULL, 1, 1.00, NULL),
(182, 25, 218, '[869]', NULL, 1, 1.00, NULL),
(183, 25, 219, '[876]', NULL, 1, 1.00, NULL),
(184, 25, 207, '[825]', NULL, 1, 1.00, NULL),
(185, 25, 220, '[878]', NULL, 1, 1.00, NULL),
(186, 25, 207, '[825]', NULL, 1, 1.00, NULL),
(187, 25, 208, '[829]', NULL, 1, 1.00, NULL),
(188, 25, 221, '[884]', NULL, 1, 1.00, NULL),
(189, 25, 208, '[829]', NULL, 1, 1.00, NULL),
(190, 25, 209, '[836]', NULL, 1, 1.00, NULL),
(191, 25, 222, '[886]', NULL, 1, 1.00, NULL),
(192, 25, 209, '[836]', NULL, 1, 1.00, NULL),
(193, 25, 210, '[837]', NULL, 1, 1.00, NULL),
(194, 25, 223, '[890]', NULL, 1, 1.00, NULL),
(195, 25, 210, '[837]', NULL, 1, 1.00, NULL),
(196, 25, 211, '[841]', NULL, 1, 1.00, NULL),
(197, 25, 224, '[896]', NULL, 1, 1.00, NULL),
(198, 25, 211, '[841]', NULL, 1, 1.00, NULL),
(199, 25, 212, '[848]', NULL, 1, 1.00, NULL),
(200, 25, 225, '[898]', NULL, 1, 1.00, NULL),
(201, 25, 212, '[848]', NULL, 1, 1.00, NULL),
(202, 25, 213, '[852]', NULL, 1, 1.00, NULL),
(203, 25, 226, '[904]', NULL, 1, 1.00, NULL),
(204, 25, 213, '[852]', NULL, 1, 1.00, NULL),
(205, 25, 214, '[853]', NULL, 1, 1.00, NULL),
(206, 25, 227, '[906]', NULL, 0, 0.00, NULL),
(207, 25, 214, '[853]', NULL, 1, 1.00, NULL),
(208, 25, 215, '[859]', NULL, 1, 1.00, NULL),
(209, 25, 228, '[911]', NULL, 1, 1.00, NULL),
(210, 25, 215, '[859]', NULL, 1, 1.00, NULL),
(211, 25, 216, '[862]', NULL, 1, 1.00, NULL),
(212, 25, 229, '[914]', NULL, 1, 1.00, NULL),
(213, 25, 216, '[862]', NULL, 1, 1.00, NULL),
(214, 25, 217, '[865]', NULL, 1, 1.00, NULL),
(215, 25, 230, '[918]', NULL, 1, 1.00, NULL),
(216, 25, 217, '[865]', NULL, 1, 1.00, NULL),
(217, 25, 218, '[869]', NULL, 1, 1.00, NULL),
(218, 25, 231, '[921]', NULL, 1, 1.00, NULL),
(219, 25, 218, '[869]', NULL, 1, 1.00, NULL),
(220, 25, 219, '[876]', NULL, 1, 1.00, NULL),
(221, 25, 219, '[876]', NULL, 1, 1.00, NULL),
(222, 25, 232, '[926]', NULL, 1, 1.00, NULL),
(223, 25, 220, '[878]', NULL, 1, 1.00, NULL),
(224, 25, 220, '[878]', NULL, 1, 1.00, NULL),
(225, 25, 221, '[884]', NULL, 1, 1.00, NULL),
(226, 25, 221, '[884]', NULL, 1, 1.00, NULL),
(227, 25, 222, '[886]', NULL, 1, 1.00, NULL),
(228, 25, 222, '[886]', NULL, 1, 1.00, NULL),
(229, 25, 223, '[890]', NULL, 1, 1.00, NULL),
(230, 25, 223, '[890]', NULL, 1, 1.00, NULL),
(231, 25, 224, '[896]', NULL, 1, 1.00, NULL),
(232, 25, 224, '[896]', NULL, 1, 1.00, NULL),
(233, 25, 225, '[898]', NULL, 1, 1.00, NULL),
(234, 25, 225, '[898]', NULL, 1, 1.00, NULL),
(235, 25, 226, '[904]', NULL, 1, 1.00, NULL),
(236, 25, 226, '[904]', NULL, 1, 1.00, NULL),
(237, 25, 227, '[906]', NULL, 0, 0.00, NULL),
(238, 25, 227, '[906]', NULL, 0, 0.00, NULL),
(239, 25, 228, '[911]', NULL, 1, 1.00, NULL),
(240, 25, 228, '[911]', NULL, 1, 1.00, NULL),
(241, 25, 229, '[914]', NULL, 1, 1.00, NULL),
(242, 25, 229, '[914]', NULL, 1, 1.00, NULL),
(243, 25, 230, '[918]', NULL, 1, 1.00, NULL),
(244, 25, 230, '[918]', NULL, 1, 1.00, NULL),
(245, 25, 231, '[921]', NULL, 1, 1.00, NULL),
(246, 25, 231, '[921]', NULL, 1, 1.00, NULL),
(247, 25, 232, '[926]', NULL, 1, 1.00, NULL),
(248, 25, 232, '[926]', NULL, 1, 1.00, NULL),
(249, 26, 195, '[779]', NULL, 1, 1.00, NULL),
(250, 26, 196, '[784]', NULL, 0, 0.00, NULL),
(251, 26, 197, '[785]', NULL, 1, 1.00, NULL),
(252, 26, 198, '[789]', NULL, 1, 1.00, NULL),
(253, 26, 199, '[793]', NULL, 0, 0.00, NULL),
(254, 26, 200, '[800]', NULL, 1, 1.00, NULL),
(255, 26, 201, '[801]', NULL, 0, 0.00, NULL),
(256, 26, 202, '[808]', NULL, 0, 0.00, NULL),
(257, 26, 203, '[810]', NULL, 1, 1.00, NULL),
(258, 26, 204, '[816]', NULL, 1, 1.00, NULL),
(259, 26, 205, '[817]', NULL, 1, 1.00, NULL),
(260, 26, 206, '[824]', NULL, 1, 1.00, NULL),
(261, 27, 207, '[826]', NULL, 0, 0.00, NULL),
(262, 27, 208, '[829]', NULL, 1, 1.00, NULL),
(263, 27, 209, '[833]', NULL, 0, 0.00, NULL),
(264, 27, 210, '[837]', NULL, 1, 1.00, NULL),
(265, 27, 211, '[841]', NULL, 1, 1.00, NULL),
(266, 27, 212, '[848]', NULL, 1, 1.00, NULL),
(267, 27, 213, '[852]', NULL, 1, 1.00, NULL),
(268, 27, 214, '[853]', NULL, 1, 1.00, NULL),
(269, 27, 215, '[859]', NULL, 1, 1.00, NULL),
(270, 27, 216, '[862]', NULL, 1, 1.00, NULL),
(271, 27, 217, '[868]', NULL, 0, 0.00, NULL),
(272, 27, 218, '[869]', NULL, 1, 1.00, NULL),
(273, 27, 219, '[876]', NULL, 1, 1.00, NULL),
(274, 27, 220, '[878]', NULL, 1, 1.00, NULL),
(275, 27, 221, '[884]', NULL, 1, 1.00, NULL),
(276, 27, 222, '[885]', NULL, 0, 0.00, NULL),
(277, 27, 223, '[890]', NULL, 1, 1.00, NULL),
(278, 27, 224, '[895]', NULL, 0, 0.00, NULL),
(279, 27, 225, '[898]', NULL, 1, 1.00, NULL),
(280, 27, 226, '[904]', NULL, 1, 1.00, NULL),
(281, 27, 227, '[905]', NULL, 1, 1.00, NULL),
(282, 27, 228, '[911]', NULL, 1, 1.00, NULL),
(283, 27, 229, '[914]', NULL, 1, 1.00, NULL),
(284, 27, 230, '[917]', NULL, 0, 0.00, NULL),
(285, 27, 231, '[921]', NULL, 1, 1.00, NULL),
(286, 27, 232, '[926]', NULL, 1, 1.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `question_type` enum('single_choice','multiple_choice','true_false','fill_blank','matching','essay') NOT NULL,
  `question_text` text NOT NULL,
  `marks` decimal(6,2) DEFAULT 1.00,
  `explanation` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question_type`, `question_text`, `marks`, `explanation`, `sort_order`, `created_at`) VALUES
(195, 16, 'single_choice', 'A patient is unable to initiate the first 15° of arm abduction after a shoulder injury. Which muscle is most likely affected?', 1.00, NULL, 1, '2026-07-09 06:54:20'),
(196, 16, 'single_choice', 'The cords of the brachial plexus are named based on their spatial relationship to which specific anatomical structure?', 1.00, NULL, 2, '2026-07-09 06:54:20'),
(197, 16, 'single_choice', 'Following a fracture of the middle third of the clavicle, the medial fragment is typically elevated. Which muscle is responsible for this displacement?', 1.00, NULL, 3, '2026-07-09 06:54:20'),
(198, 16, 'single_choice', 'A patient presents with \'scapular winging\' and is unable to elevate their arm above the horizontal plane. Which nerve has most likely been damaged?', 1.00, NULL, 4, '2026-07-09 06:54:20'),
(199, 16, 'single_choice', 'In the \'Ulnar Paradox,\' why does a distal lesion at the wrist result in a more pronounced clawing of the fingers than a proximal lesion at the elbow?', 1.00, NULL, 5, '2026-07-09 06:54:20'),
(200, 16, 'single_choice', 'An 8-year-old child sustains a supracondylar fracture of the humerus. During clinical examination, the patient is asked to make a fist but is unable to flex the thumb, index, and middle fingers. What is this clinical sign called?', 1.00, NULL, 6, '2026-07-09 06:54:20'),
(201, 16, 'single_choice', 'Which vascular structure is most at risk during a midshaft fracture of the humerus, given its close proximity to the radial nerve in the spiral groove?', 1.00, NULL, 7, '2026-07-09 06:54:20'),
(202, 16, 'single_choice', 'A newborn delivered after a difficult labor involving shoulder dystocia presents with the right arm adducted, medially rotated, and the forearm extended. Which part of the brachial plexus was injured?', 1.00, NULL, 8, '2026-07-09 06:54:20'),
(203, 16, 'single_choice', 'During a mastectomy with axillary lymph node dissection, a surgeon identifies a nerve descending on the medial wall of the axilla atop the serratus anterior muscle. Which nerve is this?', 1.00, NULL, 9, '2026-07-09 06:54:20'),
(204, 16, 'single_choice', 'Which carpal bone is most commonly fractured and is particularly prone to avascular necrosis due to its retrograde blood supply?', 1.00, NULL, 10, '2026-07-09 06:54:20'),
(205, 16, 'single_choice', 'A patient exhibits a positive \'Froment\'s Sign\' when attempting to hold a piece of paper between their thumb and index finger. This indicates weakness of which muscle?', 1.00, NULL, 11, '2026-07-09 06:54:20'),
(206, 16, 'single_choice', 'The \'regimental badge area\' corresponds to the skin over the lower part of the deltoid muscle. Anesthesia in this area indicates damage to which nerve?', 1.00, NULL, 12, '2026-07-09 06:54:20'),
(207, 17, 'single_choice', 'A surgeon performs a ligation of the axillary artery between the thyrocervical trunk and the subscapular artery. Which vessel is most likely to reverse its flow to maintain perfusion to the distal limb via the scapular anastomosis?', 1.00, NULL, 1, '2026-07-09 07:30:48'),
(208, 17, 'single_choice', 'During a physical examination, a patient is asked to hold a piece of paper between their thumb and index finger. As the examiner pulls the paper away, the patient involuntarily flexes the interphalangeal (IP) joint of the thumb. Which underlying anatomical compensation explains this \'Positive Froment Sign\'?', 1.00, NULL, 2, '2026-07-09 07:30:48'),
(209, 17, 'single_choice', 'A 22-year-old male sustains a midshaft fracture of the humerus. While he exhibits a classic \'wrist drop,\' the examiner notes that his ability to extend the elbow (Triceps function) remains largely intact. What anatomical detail explains this finding?', 1.00, NULL, 3, '2026-07-09 07:30:48'),
(210, 17, 'single_choice', 'In the \'Ulnar Paradox,\' why does a distal lesion of the ulnar nerve at the wrist result in a more pronounced clawing of the 4?? and 5?? digits compared to a proximal lesion at the elbow?', 1.00, NULL, 4, '2026-07-09 07:30:48'),
(211, 17, 'single_choice', 'A patient presents with a \'Galeazzi Fracture.\' Which combination of injuries must the radiologist confirm on imaging?', 1.00, NULL, 5, '2026-07-09 07:30:48'),
(212, 17, 'single_choice', 'Which specific carpal bone is most likely to cause compression of the median nerve within the carpal tunnel if it undergoes an anterior (volar) dislocation?', 1.00, NULL, 6, '2026-07-09 07:30:48'),
(213, 17, 'single_choice', 'The naming of the \'Lateral,\' \'Medial,\' and \'Posterior\' cords of the brachial plexus is strictly based on their spatial relationship to which anatomical structure?', 1.00, NULL, 7, '2026-07-09 07:30:48'),
(214, 17, 'single_choice', 'A 10-year-old child falls on an outstretched hand and sustains a supracondylar fracture of the humerus. The clinician is concerned about the \'Hand of Benediction.\' This posture is only evident when:', 1.00, NULL, 8, '2026-07-09 07:30:48'),
(215, 17, 'single_choice', 'What is the primary mechanical reason the middle third of the clavicle is the most common site for fractures?', 1.00, NULL, 9, '2026-07-09 07:30:48'),
(216, 17, 'single_choice', 'A patient following a radical mastectomy presents with a \'winged scapula.\' To confirm a paralysis of the Serratus Anterior, the clinician should look for failure in which movement?', 1.00, NULL, 10, '2026-07-09 07:30:48'),
(217, 17, 'single_choice', 'In a patient with Carpal Tunnel Syndrome, which area of palmar sensation is typically preserved despite severe numbness in the digits?', 1.00, NULL, 11, '2026-07-09 07:30:48'),
(218, 17, 'single_choice', 'A \'Waiter\'s Tip\' posture (arm adducted, medially rotated, forearm extended, and pronated) is the classic presentation of which brachial plexus syndrome?', 1.00, NULL, 12, '2026-07-09 07:30:48'),
(219, 17, 'single_choice', 'Which muscle \'jump-starts\' abduction of the arm from 0° to 15° before the deltoid takes over?', 1.00, NULL, 13, '2026-07-09 07:30:48'),
(220, 17, 'single_choice', 'A fracture of the \'surgical neck\' of the humerus endangers which two neurovascular structures?', 1.00, NULL, 14, '2026-07-09 07:30:48'),
(221, 17, 'single_choice', 'Which specific action of the intrinsic hand muscles is lost if the \'DAB\' component of the \'PAD & DAB\' map is paralyzed?', 1.00, NULL, 15, '2026-07-09 07:30:48'),
(222, 17, 'single_choice', 'A patient sustain a \'Monteggia Fracture.\' What is the classic neurological risk associated with this injury due to the displacement of the radial head?', 1.00, NULL, 16, '2026-07-09 07:30:48'),
(223, 17, 'single_choice', 'Following a fall on a flexed wrist, a patient\'s X-ray shows a distal radius fracture with volar (anterior) displacement. What is the correct eponym for this \'Garden Spade\' deformity?', 1.00, NULL, 17, '2026-07-09 07:30:48'),
(224, 17, 'single_choice', 'Why does a fracture of the scaphoid at the waist frequently lead to avascular necrosis (AVN) of the proximal fragment?', 1.00, NULL, 18, '2026-07-09 07:30:48'),
(225, 17, 'single_choice', 'A patient presents with \'Ape Hand\' deformity. Which specific muscular loss is the most defining characteristic of this median nerve lesion?', 1.00, NULL, 19, '2026-07-09 07:30:48'),
(226, 17, 'single_choice', 'The \'Regimental Badge\' area of the lateral shoulder is the clinical sensory map for which nerve?', 1.00, NULL, 20, '2026-07-09 07:30:48'),
(227, 17, 'single_choice', 'What is the primary anatomical \'fix\' for a child with a \'Pulled Elbow\' (subluxation of the radial head)?', 1.00, NULL, 21, '2026-07-09 07:30:48'),
(228, 17, 'single_choice', 'Which nerve is found directly behind the medial epicondyle of the humerus, making it vulnerable in fractures of that region?', 1.00, NULL, 22, '2026-07-09 07:30:48'),
(229, 17, 'single_choice', 'A patient exhibits Klumpke\'s Palsy following an injury where they grabbed a tree branch to break a fall. Why might they also present with Horner Syndrome (miosis, ptosis, anhidrosis)?', 1.00, NULL, 23, '2026-07-09 07:30:48'),
(230, 17, 'single_choice', 'In the \'Master Schematic\' of the brachial plexus, which section is found directly behind the clavicle?', 1.00, NULL, 24, '2026-07-09 07:30:48'),
(231, 17, 'single_choice', 'A patient has \'Tennis Elbow.\' Which specific anatomical structure is most commonly inflamed due to overuse of the wrist extensors?', 1.00, NULL, 25, '2026-07-09 07:30:48'),
(232, 17, 'single_choice', 'Which muscle acts as the primary driver for arm abduction between 15° and 90°?', 1.00, NULL, 26, '2026-07-09 07:30:48');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_question_options`
--

CREATE TABLE `quiz_question_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `match_pair` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quiz_question_options`
--

INSERT INTO `quiz_question_options` (`id`, `question_id`, `option_text`, `is_correct`, `match_pair`, `sort_order`) VALUES
(777, 195, 'Teres minor', 0, NULL, 0),
(778, 195, 'Subscapularis', 0, NULL, 1),
(779, 195, 'Supraspinatus', 1, NULL, 2),
(780, 195, 'Deltoid', 0, NULL, 3),
(781, 196, 'The Surgical Neck of the Humerus', 0, NULL, 0),
(782, 196, 'The second part of the Axillary Artery', 1, NULL, 1),
(783, 196, 'The first part of the Axillary Artery', 0, NULL, 2),
(784, 196, 'The Clavicle', 0, NULL, 3),
(785, 197, 'Sternocleidomastoid', 1, NULL, 0),
(786, 197, 'Subclavius', 0, NULL, 1),
(787, 197, 'Trapezius', 0, NULL, 2),
(788, 197, 'Pectoralis major', 0, NULL, 3),
(789, 198, 'Long Thoracic Nerve', 1, NULL, 0),
(790, 198, 'Thoracodorsal Nerve', 0, NULL, 1),
(791, 198, 'Axillary Nerve', 0, NULL, 2),
(792, 198, 'Suprascapular Nerve', 0, NULL, 3),
(793, 199, 'The Median nerve compensates for distal ulnar injuries', 0, NULL, 0),
(794, 199, 'Proximal lesions lead to more severe muscle atrophy', 0, NULL, 1),
(795, 199, 'Distal lesions spare the Flexor Digitorum Profundus', 1, NULL, 2),
(796, 199, 'Sensory loss is greater in distal lesions', 0, NULL, 3),
(797, 200, 'Ape Hand', 0, NULL, 0),
(798, 200, 'Waiter\'s Tip', 0, NULL, 1),
(799, 200, 'Ulnar Claw', 0, NULL, 2),
(800, 200, 'Hand of Benediction', 1, NULL, 3),
(801, 201, 'Brachial Artery', 0, NULL, 0),
(802, 201, 'Profunda Brachii Artery', 1, NULL, 1),
(803, 201, 'Posterior Circumflex Humeral Artery', 0, NULL, 2),
(804, 201, 'Ulnar Artery', 0, NULL, 3),
(805, 202, 'Upper Trunk (C5-C6)', 1, NULL, 0),
(806, 202, 'Posterior Cord', 0, NULL, 1),
(807, 202, 'Lower Trunk (C8-T1)', 0, NULL, 2),
(808, 202, 'Medial Cord', 0, NULL, 3),
(809, 203, 'Intercostobrachial Nerve', 0, NULL, 0),
(810, 203, 'Long Thoracic Nerve', 1, NULL, 1),
(811, 203, 'Thoracodorsal Nerve', 0, NULL, 2),
(812, 203, 'Medial Cutaneous Nerve of the Arm', 0, NULL, 3),
(813, 204, 'Lunate', 0, NULL, 0),
(814, 204, 'Hamate', 0, NULL, 1),
(815, 204, 'Pisiform', 0, NULL, 2),
(816, 204, 'Scaphoid', 1, NULL, 3),
(817, 205, 'Adductor pollicis', 1, NULL, 0),
(818, 205, 'Abductor pollicis brevis', 0, NULL, 1),
(819, 205, 'Opponens pollicis', 0, NULL, 2),
(820, 205, 'Flexor pollicis brevis', 0, NULL, 3),
(821, 206, 'Musculocutaneous Nerve', 0, NULL, 0),
(822, 206, 'Radial Nerve', 0, NULL, 1),
(823, 206, 'Suprascapular Nerve', 0, NULL, 2),
(824, 206, 'Axillary Nerve', 1, NULL, 3),
(825, 207, 'Circumflex scapular artery', 1, NULL, 0),
(826, 207, 'Posterior circumflex humeral artery', 0, NULL, 1),
(827, 207, 'Profunda brachii artery', 0, NULL, 2),
(828, 207, 'Thoracoacromial trunk', 0, NULL, 3),
(829, 208, 'Recruitment of the Flexor Pollicis Longus by the Median Nerve', 1, NULL, 0),
(830, 208, 'Paralysis of the First Dorsal Interosseus leading to Radial Nerve dominance', 0, NULL, 1),
(831, 208, 'Over-activation of the Adductor Pollicis by the Median Nerve', 0, NULL, 2),
(832, 208, 'Hyperactivity of the Opponens Pollicis due to Ulnar Nerve irritation', 0, NULL, 3),
(833, 209, 'The long head of the triceps is supplied by the Musculocutaneous nerve', 0, NULL, 0),
(834, 209, 'The triceps is dually innervated by the axillary and radial nerves', 0, NULL, 1),
(835, 209, 'The midshaft is distal to the Deep Brachial Artery\'s origin', 0, NULL, 2),
(836, 209, 'Branches to the triceps arise proximal to the radial groove', 1, NULL, 3),
(837, 210, 'The Flexor Digitorum Profundus is spared in distal lesions', 1, NULL, 0),
(838, 210, 'The lumbricals receive a compensatory supply from the median nerve', 0, NULL, 1),
(839, 210, 'Distal lesions cause a secondary compression in Guyon\'s canal', 0, NULL, 2),
(840, 210, 'The interossei are only paralyzed in distal injuries', 0, NULL, 3),
(841, 211, 'Distal radius fracture and distal radio-ulnar joint (DRUJ) dislocation', 1, NULL, 0),
(842, 211, 'Proximal ulna fracture and radial head dislocation', 0, NULL, 1),
(843, 211, 'Isolated ulnar shaft fracture with interosseous membrane rupture', 0, NULL, 2),
(844, 211, 'Radial styloid fracture and lunate dislocation', 0, NULL, 3),
(845, 212, 'Hamate', 0, NULL, 0),
(846, 212, 'Pisiform', 0, NULL, 1),
(847, 212, 'Scaphoid', 0, NULL, 2),
(848, 212, 'Lunate', 1, NULL, 3),
(849, 213, 'The surgical neck of the humerus', 0, NULL, 0),
(850, 213, 'The first rib', 0, NULL, 1),
(851, 213, 'The clavicle', 0, NULL, 2),
(852, 213, 'The second part of the Axillary Artery', 1, NULL, 3),
(853, 214, 'The patient is asked to actively make a fist', 1, NULL, 0),
(854, 214, 'The patient is asked to extend their wrist', 0, NULL, 1),
(855, 214, 'The hand is resting on a flat surface', 0, NULL, 2),
(856, 214, 'The patient attempts to abduct their fingers', 0, NULL, 3),
(857, 215, 'It lacks an internal medullary cavity', 0, NULL, 0),
(858, 215, 'The subclavius muscle provides excessive compressive force there', 0, NULL, 1),
(859, 215, 'It is the junction where the anterior convexity meets the anterior concavity', 1, NULL, 2),
(860, 215, 'The Sternocleidomastoid attaches solely to this region', 0, NULL, 3),
(861, 216, 'Internal rotation of the humerus', 0, NULL, 0),
(862, 216, 'Rotating the scapula to elevate the arm above 90°', 1, NULL, 1),
(863, 216, 'Initiating the first 15° of arm abduction', 0, NULL, 2),
(864, 216, 'Adducting the arm against resistance', 0, NULL, 3),
(865, 217, 'The skin over the thenar eminence', 1, NULL, 0),
(866, 217, 'The dorsal surface of the thumb', 0, NULL, 1),
(867, 217, 'The tip of the index finger', 0, NULL, 2),
(868, 217, 'The medial half of the ring finger', 0, NULL, 3),
(869, 218, 'Erb-Duchenne Palsy (Upper Trunk)', 1, NULL, 0),
(870, 218, 'Klumpke\'s Palsy (Lower Trunk)', 0, NULL, 1),
(871, 218, 'Thoracic Outlet Syndrome', 0, NULL, 2),
(872, 218, 'Radial Nerve Palsy (Posterior Cord)', 0, NULL, 3),
(873, 219, 'Subscapularis', 0, NULL, 0),
(874, 219, 'Infraspinatus', 0, NULL, 1),
(875, 219, 'Teres Minor', 0, NULL, 2),
(876, 219, 'Supraspinatus', 1, NULL, 3),
(877, 220, 'Radial nerve and Profunda brachii artery', 0, NULL, 0),
(878, 220, 'Axillary nerve and Posterior circumflex humeral artery', 1, NULL, 1),
(879, 220, 'Ulnar nerve and Superior ulnar collateral artery', 0, NULL, 2),
(880, 220, 'Median nerve and Brachial artery', 0, NULL, 3),
(881, 221, 'Finger Adduction', 0, NULL, 0),
(882, 221, 'Thumb Flexion', 0, NULL, 1),
(883, 221, 'Thumb Opposition', 0, NULL, 2),
(884, 221, 'Finger Abduction', 1, NULL, 3),
(885, 222, 'Ulnar Nerve at the elbow', 0, NULL, 0),
(886, 222, 'Posterior Interosseous Nerve (Radial branch)', 1, NULL, 1),
(887, 222, 'Axillary Nerve in the axilla', 0, NULL, 2),
(888, 222, 'Median Nerve at the cubital fossa', 0, NULL, 3),
(889, 223, 'Galeazzi Fracture', 0, NULL, 0),
(890, 223, 'Smith\'s Fracture', 1, NULL, 1),
(891, 223, 'Monteggia Fracture', 0, NULL, 2),
(892, 223, 'Colles\' Fracture', 0, NULL, 3),
(893, 224, 'The scaphoid is encapsulated and lacks any periosteal blood supply', 0, NULL, 0),
(894, 224, 'The lunate compresses the scaphoid\'s nutrient foramen after injury', 0, NULL, 1),
(895, 224, 'The fracture triggers a compartment syndrome in the snuffbox', 0, NULL, 2),
(896, 224, 'The blood supply enters distally and flows in a retrograde direction', 1, NULL, 3),
(897, 225, 'Failure of finger abduction and hypothenar atrophy', 0, NULL, 0),
(898, 225, 'Loss of thumb opposition and thenar wasting', 1, NULL, 1),
(899, 225, 'Inability to flex the distal interphalangeal joints of digits 4 and 5', 0, NULL, 2),
(900, 225, 'Complete loss of sensation over the medial side of the hand', 0, NULL, 3),
(901, 226, 'Musculocutaneous Nerve', 0, NULL, 0),
(902, 226, 'Suprascapular Nerve', 0, NULL, 1),
(903, 226, 'Radial Nerve', 0, NULL, 2),
(904, 226, 'Axillary Nerve', 1, NULL, 3),
(905, 227, 'Supination and flexion of the forearm', 1, NULL, 0),
(906, 227, 'Direct posterior pressure on the olecranon', 0, NULL, 1),
(907, 227, 'Pronation and extension of the forearm', 0, NULL, 2),
(908, 227, 'Surgical pinning of the radio-ulnar joint', 0, NULL, 3),
(909, 228, 'Median Nerve', 0, NULL, 0),
(910, 228, 'Radial Nerve', 0, NULL, 1),
(911, 228, 'Ulnar Nerve', 1, NULL, 2),
(912, 228, 'Musculocutaneous Nerve', 0, NULL, 3),
(913, 229, 'Secondary compression of the carotid artery in the neck', 0, NULL, 0),
(914, 229, 'Traction on the T1 root involves sympathetic fibers to the head', 1, NULL, 1),
(915, 229, 'Associated injury to the superior cervical ganglion', 0, NULL, 2),
(916, 229, 'Ischemia to the brainstem due to subclavian steal', 0, NULL, 3),
(917, 230, 'Cords', 0, NULL, 0),
(918, 230, 'Divisions', 1, NULL, 1),
(919, 230, 'Trunks', 0, NULL, 2),
(920, 230, 'Roots', 0, NULL, 3),
(921, 231, 'Lateral Epicondyle', 1, NULL, 0),
(922, 231, 'Medial Epicondyle', 0, NULL, 1),
(923, 231, 'Radial Tuberosity', 0, NULL, 2),
(924, 231, 'Olecranon Bursa', 0, NULL, 3),
(925, 232, 'Serratus Anterior', 0, NULL, 0),
(926, 232, 'Deltoid', 1, NULL, 1),
(927, 232, 'Trapezius', 0, NULL, 2),
(928, 232, 'Pectoralis Major', 0, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `revoked_at`, `created_at`) VALUES
(1, 4, '64268f50e439b1509896ec435f2398226f09eb143b2e39858b8a575226fbfb20', '2026-07-09 12:28:55', NULL, '2026-07-02 12:28:55'),
(2, 12, 'c6702ac3b7f5e6c3e24d7f8cda35644242874bbe6ef7a246d6b791adc379d125', '2026-07-09 12:40:54', NULL, '2026-07-02 12:40:54'),
(3, 6, '266e315526083e197d85950b56a15ca69a9b5d3877f9419b447d4f1e45bc5e9a', '2026-07-09 12:43:50', NULL, '2026-07-02 12:43:50'),
(4, 17, '94848b20560f9a8aa9f6c5e9b4f552cfeabd858371e9f5e95c2f81de1903137e', '2026-07-09 12:46:33', NULL, '2026-07-02 12:46:33'),
(5, 6, '774347d93bcd4a23f2ef39b8d84effc6f374c47fd73eacb0f2c82dfef62f4ecc', '2026-07-09 13:00:07', NULL, '2026-07-02 13:00:07'),
(6, 17, '08bff4f59f9621203ee3071a8fd0740d6148b4633fa4fa066198738fca4c159e', '2026-07-09 13:07:12', NULL, '2026-07-02 13:07:12'),
(7, 4, 'b8719206b8d73f42a7370d9a8188dc73a389e1f64a9523808f5bf1021c474504', '2026-07-09 17:38:42', NULL, '2026-07-02 17:38:42'),
(8, 6, '89f8dec66e5d7ca576c8c00d8476e2bdb0bd939e22273e79f63c1ec679ff1ab2', '2026-07-09 17:41:09', NULL, '2026-07-02 17:41:09'),
(9, 9, 'e3f21fad4f131fb47ff6b308b440d6aa8318a8e9501c18b5605c8c8d0cd1fdb3', '2026-07-10 02:54:53', NULL, '2026-07-03 02:54:53'),
(10, 6, '3f2ca4fd0834640d87ae90ea2b8614827b6b2b82aeda5f24feaeb6854a1ed6e0', '2026-07-10 04:33:30', NULL, '2026-07-03 04:33:30'),
(11, 6, '79549ac19d9be9fa656ba84827039d58e2ed67741ff6c51b5e4cefc73da85f66', '2026-07-10 04:44:58', NULL, '2026-07-03 04:44:58'),
(12, 6, '4a235aa433b3e5eb2741d48ffb88bdf12b8d0bb4226d3d8b3778bcdcc146f25d', '2026-07-10 05:44:21', NULL, '2026-07-03 05:44:21'),
(13, 4, 'b6f4d93b89e126a0f22cd598a0534f06ba35584a460000edc5806fef898c1f05', '2026-07-10 05:59:56', NULL, '2026-07-03 05:59:56'),
(14, 5, 'eedf608770c0e4dee3696425a2d45960cc49965a9809bb33366e2b2f616ab986', '2026-07-10 06:07:35', NULL, '2026-07-03 06:07:35'),
(15, 17, 'b081f9259376a7dbba9725c76cddf465e04056839df1fb2fc80dbdf9596527d9', '2026-07-10 06:26:29', NULL, '2026-07-03 06:26:29'),
(16, 6, '98ff9425a192a0fd7287e41eabdde4932c51c1c15a2f45c30092243b91f06af8', '2026-07-10 06:33:20', NULL, '2026-07-03 06:33:20'),
(17, 9, '723a26dd87d2e88efad13a4d324ad46e5407bced4c914527eb92711dbbf55808', '2026-07-10 06:38:45', NULL, '2026-07-03 06:38:45'),
(18, 12, '859af49632fb8b1f6bb987ae6b9ee983aeb2ef80c8a72500b5b8bb06115744c9', '2026-07-10 06:45:21', NULL, '2026-07-03 06:45:21'),
(19, 7, '5c2a39a0609591386d2ed32f9b2201a64ac6295d70f3df22a806ae9581baa917', '2026-07-10 18:42:25', NULL, '2026-07-03 18:42:25'),
(20, 16, 'a71d30f3e231ab55768f193c75253a4d9a1b7b755cd4a14b13590e408bfdef5b', '2026-07-10 20:38:43', NULL, '2026-07-03 20:38:43'),
(21, 4, '7495b4e0a899e740dfd9725f10fdd4c27e7b53c5d0d84a84fda49aa7daf04f0b', '2026-07-11 05:38:03', NULL, '2026-07-04 05:38:03'),
(22, 6, 'd0569280cebd8005f4cf9b8e06676da20c2c5c7d31dd5584691af5c248c62c24', '2026-07-11 05:40:19', NULL, '2026-07-04 05:40:19'),
(23, 17, '34c2c633de4e1676ccc95c8bc82b03b945f28ee2653d3012543d7baf91008dc3', '2026-07-11 05:41:14', NULL, '2026-07-04 05:41:14'),
(24, 4, '2dadf2ebf7e30ff40115d73970dab1c24df996c5b15c57abea6905dc6f954b60', '2026-07-11 06:55:23', NULL, '2026-07-04 06:55:23'),
(25, 6, '3b2e59d0a86c2b0ead4e677a8750a708ae4331ccdfb37ef14c87bbb3cfb99f2c', '2026-07-11 10:01:49', NULL, '2026-07-04 10:01:49'),
(26, 4, '39505988e74b89169a6f45da5b82129e58f62d206ebf57a0add85439eb08307a', '2026-07-11 12:47:14', NULL, '2026-07-04 12:47:14'),
(27, 6, 'c02a554dbef2e93b19b63e1255942b0ed9217e80a65dd7375c2a174f5edf21cc', '2026-07-11 13:17:52', NULL, '2026-07-04 13:17:52'),
(28, 4, '559e14355f30f53ccef1b897f5f1dd5cea90a4ad6e35531a9e85e9f057f39779', '2026-07-11 19:08:37', NULL, '2026-07-04 19:08:37'),
(29, 5, '2697a25bcf729fb5c49cd5b11f005ae5f64063e873248e50a220c41df29143cb', '2026-07-11 19:14:03', NULL, '2026-07-04 19:14:03'),
(30, 6, 'f90ad08bef4717f45b4bb99e05ca147348fd2d8876106cc3be64950c05ccbca7', '2026-07-11 19:16:28', NULL, '2026-07-04 19:16:28'),
(31, 6, 'c79c8d04e32b601ef17852a6d1625119ab49fd9857b97f82cdd10b8a04cae14d', '2026-07-11 19:46:31', NULL, '2026-07-04 19:46:31'),
(32, 6, '1df32d64c53988f416b1a27ef86c9ac06fe2fd8975ec7788594f4cc18db08cad', '2026-07-11 19:50:27', NULL, '2026-07-04 19:50:27'),
(33, 5, '6167fdfa453930a2feceaa541cddc5d1554ad6e88e4a1ca5f61c3c8b5d45d2bd', '2026-07-11 20:07:23', NULL, '2026-07-04 20:07:23'),
(34, 4, '91dc0f177c24ec38225a2b5e2440c54359b49c90a1aac9a28a57dbf3607041ac', '2026-07-11 20:09:55', NULL, '2026-07-04 20:09:55'),
(35, 17, '0a4b2e6933216a778ec399a09071850f35001c0eb5deaf9b09b15cb9108e2159', '2026-07-11 20:10:57', NULL, '2026-07-04 20:10:57'),
(36, 5, 'f70eb05524ae1c79b8557c303dbecfde4b46c97ba17141ed3360982872061aa9', '2026-07-12 03:43:30', NULL, '2026-07-05 03:43:30'),
(37, 5, '53dbe0bfe8d693817cf0dac7e868cf7faacb4694b2ffa0b4ecd7fe824d489289', '2026-07-12 03:57:51', NULL, '2026-07-05 03:57:51'),
(38, 5, '3fdb0a0c4dd8703cdb6b2f1514c9e4bdc1a648fba9b21a867c2c84d3c784860e', '2026-07-12 04:02:08', NULL, '2026-07-05 04:02:08'),
(39, 12, '3281ce22c0068c2536273a8936efe374910242967e85090f0545ea85c24f3965', '2026-07-12 06:41:07', NULL, '2026-07-05 06:41:07'),
(40, 5, '7696786b03546db6d4fc2db648497bb54d8534a17b0e2e91467121dde3669f97', '2026-07-12 07:39:33', NULL, '2026-07-05 07:39:33'),
(41, 5, '43c935ec551fa1397aa76dd15f7793c936eb7666adf6d0da6dd92354d6bf3eaa', '2026-07-12 08:14:05', NULL, '2026-07-05 08:14:05'),
(42, 5, '79c59928315617606780261c90147e8cc279ee34d7155d1c04fd43c5a6e349b0', '2026-07-12 08:28:15', NULL, '2026-07-05 08:28:15'),
(43, 5, 'ad4e4fd55db7d64a2f2851876ec3fd8693484b4d9618df47de55e7bb38acc704', '2026-07-12 08:45:57', NULL, '2026-07-05 08:45:57'),
(44, 6, '47d16ad1d233ed6ecd17f32ef61a2af8dc9982689dcba858842216aaded2b5fe', '2026-07-12 14:58:08', NULL, '2026-07-05 14:58:08'),
(45, 4, '5f35ed0e5d9377107eddb997499d7ad60a493ba281dc5a554ffe3f8c0db81693', '2026-07-12 15:40:23', NULL, '2026-07-05 15:40:23'),
(46, 17, '8ac5b8aa20dadd36c2131eddef5772d5752f9b03fb6f4f895f3382a8ebda597c', '2026-07-12 15:41:20', NULL, '2026-07-05 15:41:20'),
(47, 4, '30534913aae5c669e6194869790cb97c24fc9ab29e31ee5af03968dfa2af2fbe', '2026-07-12 15:58:33', NULL, '2026-07-05 15:58:33'),
(48, 7, '5d644678266595cf16ff833ca82b63bc9a31382a1eb1abd78374dcd54d435e77', '2026-07-12 16:17:03', NULL, '2026-07-05 16:17:03'),
(49, 17, '15dae30d3432e4b62db1361f8f75e8fe6cf0dbd35b0859f2661349d17ce5e750', '2026-07-12 16:18:33', NULL, '2026-07-05 16:18:33'),
(50, 6, 'd49f19335a6899246886c3a9f00d0221743d7c80871d6e24a45f1b13002ee250', '2026-07-13 06:09:43', NULL, '2026-07-06 06:09:43'),
(51, 5, '6c24f11546e2b5ed9480007aa9cab72c5b01ff334bf88e08ffabc061ffeabfdd', '2026-07-13 06:16:32', NULL, '2026-07-06 06:16:32'),
(52, 5, '8112477d699ee2834d7c0eb2d60d3da4d08204929be39dfdee6bb37843dd8593', '2026-07-13 07:18:29', NULL, '2026-07-06 07:18:29'),
(53, 6, '536079f6644ff3fa959fa9e5f16817bcf96520f178343ad4459cc08f51246322', '2026-07-13 07:38:59', NULL, '2026-07-06 07:38:59'),
(54, 4, '48ec0ce4a52d02a8542d66723518ad15e7b7b599a5dfcd193b19bd6145e7b425', '2026-07-13 07:59:53', NULL, '2026-07-06 07:59:53'),
(55, 6, '0b094cef84c95bfbd28288571b882ea2e8c3a8fd75ddc6faac014d3ca373f4a0', '2026-07-13 08:04:12', NULL, '2026-07-06 08:04:12'),
(56, 6, 'a61435db18da12856625987d7ebf65c4d74706097ca9adefce84a63d7d6b8783', '2026-07-13 08:08:22', NULL, '2026-07-06 08:08:22'),
(57, 5, '448dcc26f8390c19e66b778640d3701ef051bdba0aa067babcb500be5700d0f9', '2026-07-13 08:20:02', NULL, '2026-07-06 08:20:02'),
(58, 9, '0374112d557d7310dce1169d65d9f040170b0800f43322ddc33a0c639153cf4b', '2026-07-13 10:04:33', NULL, '2026-07-06 10:04:33'),
(59, 5, '9201ec05e1767bfc0a8f9db7adeb48bc0f6f6d25a0af70fce8abc45877210538', '2026-07-13 10:13:38', NULL, '2026-07-06 10:13:38'),
(60, 4, '9de2393be249f8c933f38bac3f81f939ac17f8dc740e2f7fc09508dd92b934d1', '2026-07-13 10:19:07', NULL, '2026-07-06 10:19:07'),
(61, 6, '2e4466864d69359ed58b455383fff9b77774f76e764588e0c985a0d0ec3b29bd', '2026-07-13 10:20:23', NULL, '2026-07-06 10:20:23'),
(62, 8, '36d1590f6b33111e7647b61173611979fb48fed76eae6dc6f8af4532f4f53d44', '2026-07-13 10:29:01', NULL, '2026-07-06 10:29:01'),
(63, 5, 'a0ccc70591ef52471b300b2a5241782361b2aeb13fa217d3dc276d3289ffbd72', '2026-07-13 10:54:28', NULL, '2026-07-06 10:54:28'),
(64, 6, 'd3644700140162554e3b75f7972fb214f26c4634bb1d307f1de9b8b41f07e082', '2026-07-13 10:54:28', NULL, '2026-07-06 10:54:28'),
(65, 9, '78a42d0f8df178eee7361ad487407544b787bdfbc7a0a7383e5884f249e30b8f', '2026-07-13 11:06:09', NULL, '2026-07-06 11:06:09'),
(66, 6, 'e1c6bac3d7c8081f13902e5928bf60bacd23dd87eb45a01343420e5a9b3796ce', '2026-07-13 11:07:04', NULL, '2026-07-06 11:07:04'),
(67, 4, 'fdbdfbf7c5bec3bbec0793e6cdebd520a6eb831c49fc54178ec6952978bfe036', '2026-07-13 12:13:28', NULL, '2026-07-06 12:13:28'),
(68, 13, '456c67ac26ed4ef029987afe1d3d6d8584f9d484ced4f7a387c4bb4657edfdc8', '2026-07-13 12:15:55', NULL, '2026-07-06 12:15:55'),
(69, 12, 'cc17664e246466f62780b5aa88bdd988d83f1f2dc973fbf5782e351427c9c87e', '2026-07-13 12:18:17', NULL, '2026-07-06 12:18:17'),
(70, 6, '439731ed86be47b11f8312d02b7bffcb456771321412719e18f70113b4f8e001', '2026-07-13 12:27:29', NULL, '2026-07-06 12:27:29'),
(71, 4, '2558717048e42e04836df139c2fb2935b86fa509b4c21bea276e89c7b2b706fd', '2026-07-13 12:35:58', NULL, '2026-07-06 12:35:58'),
(72, 4, '36f80743698677e019054b0df646c2c6d4fb3324378c493c03148f3264975f02', '2026-07-13 12:37:42', NULL, '2026-07-06 12:37:42'),
(73, 3, '3054fe85e79a4d63f59bd40b63b31e4905a6eba07c4b4bd7679db101e31b9780', '2026-07-13 12:38:12', NULL, '2026-07-06 12:38:12'),
(74, 7, '0934f0febb742c0c6b24959f087ef4e25bc4dde113d6ee5c9d5d788cac16c2aa', '2026-07-13 15:49:49', NULL, '2026-07-06 15:49:49'),
(75, 4, 'bfaad8b4fc4e7a947fa189dca0ab97f6f91d9d69fbd292d5a230213546151002', '2026-07-13 16:00:31', NULL, '2026-07-06 16:00:31'),
(76, 3, '42d61a6b3aad7122a8ce51325e73f5e966cd519affe0a7f7a3b1a7e0fb688f8f', '2026-07-13 16:01:32', NULL, '2026-07-06 16:01:32'),
(77, 4, '6dbc7241bd9f1410af081edbd6662e4d81f463410740342b25dbb683d340263f', '2026-07-13 18:37:15', NULL, '2026-07-06 18:37:15'),
(78, 3, 'ce98b8912ea06ccfa5d929fcd905842d12e2bc4135a8f4859ea645e6a16f5d9a', '2026-07-13 18:38:39', NULL, '2026-07-06 18:38:39'),
(79, 6, '840392a3eb35cc946cbc0ef34ff9e5f594fcbaf9feb7122a854468ce5739d52a', '2026-07-13 18:42:54', NULL, '2026-07-06 18:42:54'),
(80, 4, 'c8ea702630cc4cb3e60f711edaaa4879bd47c58d87c9daf4215e89fbfe380a82', '2026-07-13 18:44:56', NULL, '2026-07-06 18:44:56'),
(81, 3, '7d0324edf6efb1ad017b748539a98c92e42ebec55230239a2147214799ddc0a6', '2026-07-13 18:45:54', NULL, '2026-07-06 18:45:54'),
(82, 17, '2281fe9128b34ad55fce544bdc8cd7a0775230b618e987c898343476660d69ce', '2026-07-13 19:34:48', NULL, '2026-07-06 19:34:48'),
(83, 3, '6aebc95ae8081dff2b5b307c876e1dbcef38de2ceed63fd98aa92e9f062b1e08', '2026-07-14 04:37:07', NULL, '2026-07-07 04:37:07'),
(84, 3, 'cc0bc102845a5a275b6f7309340c3add531bc1182169a2ff2b9b4b03cc27464a', '2026-07-14 05:25:18', NULL, '2026-07-07 05:25:18'),
(85, 6, 'a29d28c864d2e1b77e6cf72a55a13c04293d414a04f65722ac42bee46d08a047', '2026-07-14 05:26:49', NULL, '2026-07-07 05:26:49'),
(86, 6, '606d1e9506840ec4cc8666e20e9777c946023f24b04c0823397eb66883c80064', '2026-07-14 06:10:21', NULL, '2026-07-07 06:10:21'),
(87, 9, 'd2f951acfc5d7e76736861b79f8f395b09e79b34924d37ad5a55157786689430', '2026-07-14 06:39:10', NULL, '2026-07-07 06:39:10'),
(88, 6, '3ca89e754a04eedb65e33a4b9edc82b31993dddd531ddea6266a6dc223d5b6f8', '2026-07-14 06:46:49', NULL, '2026-07-07 06:46:49'),
(89, 6, '76a68a444dee9e47c1cfe5a564bd81fd4ceec30f6cb0b73cea4106aa7544b8fe', '2026-07-14 07:46:41', NULL, '2026-07-07 07:46:41'),
(90, 6, 'c9378b879e899b6217aa6b4cde090ba344236238923d0f6c25b264869b9e2bc2', '2026-07-14 08:56:04', NULL, '2026-07-07 08:56:04'),
(91, 3, '05a28b72fb1031e2236e168228b21bd5b20866893c5ded1c0cee1fe73001ec15', '2026-07-14 08:59:08', NULL, '2026-07-07 08:59:08'),
(92, 6, '82869e47e8649eed8002b9a05b7ac8dba7d51cda8b16a36d1cf12addd8f8a242', '2026-07-14 09:14:45', NULL, '2026-07-07 09:14:45'),
(93, 6, '29aa9da3487542e1eff808f39d2a5cb9c4e1701be77e6e25508ee7f3c0590eed', '2026-07-14 09:31:31', NULL, '2026-07-07 09:31:31'),
(94, 7, '1ad22d0c0d2c504cf1c8ac14eb0454c2eed59461054dbf2a65cfd2c84562b79e', '2026-07-14 09:46:59', NULL, '2026-07-07 09:46:59'),
(95, 16, 'f741077b9ed5c99bd84c2e48220238ff28cd1abff6af244a7e8ae95cf43659b8', '2026-07-14 10:10:01', NULL, '2026-07-07 10:10:01'),
(96, 6, '395c697dd4a9551b637416e222f989e8c0a811505dd0fbd27676f52d1dc9ce84', '2026-07-14 10:50:13', NULL, '2026-07-07 10:50:13'),
(97, 12, 'b330ec5dcabd4a7021ba4da3004404f58572c537c786d2a0e20511f05c7a6b1d', '2026-07-14 11:00:18', NULL, '2026-07-07 11:00:18'),
(98, 6, '0be36278c9873d7c10f62a37819cd478a8930518d2411adc3625dea61abb8b64', '2026-07-14 11:28:01', NULL, '2026-07-07 11:28:01'),
(99, 6, '7d0a3fdfc7d002d356b0d254396d7f998d3559421bc954116bc7d3c8a07ccae8', '2026-07-14 11:54:38', NULL, '2026-07-07 11:54:38'),
(100, 6, 'd494129c55aca7236ef2f9c3031302268bbd2c0707d1224ae9429a6fdaf59f09', '2026-07-14 12:28:18', NULL, '2026-07-07 12:28:18'),
(101, 16, '557540f35b1f24b404b485cd5a6472752ee2b2bf64722d45f857ab88d73b6a65', '2026-07-14 13:05:11', NULL, '2026-07-07 13:05:11'),
(102, 7, '79aee513c199012a677085fd550425b1ef1c4b0da90f58c9162392e55109c6c5', '2026-07-14 19:59:55', NULL, '2026-07-07 19:59:55'),
(103, 8, 'a1287c4581930236eaf1cfb29b09a1d5f897a18a565a1bc2d07ca3d1794473b6', '2026-07-15 02:57:14', NULL, '2026-07-08 02:57:14'),
(104, 7, 'd552efe287ed03dbd6f9814375ba36592a834ba32de5100665820bf1567a4733', '2026-07-15 05:09:40', NULL, '2026-07-08 05:09:40'),
(105, 4, 'e40109cc0340a74b35e118df30bdebb383de759f8dca867c900f370ac89657d3', '2026-07-15 05:46:56', NULL, '2026-07-08 05:46:56'),
(106, 3, '43f828ed4683b332a744a5113dc29c699d318e1f880c94c43bf68c4ae6476e3b', '2026-07-15 06:28:41', NULL, '2026-07-08 06:28:41'),
(107, 6, 'f5362f4b7ac3e58f6d76833ed620a8a1770616d91b88e710943ddd5610f9b7d1', '2026-07-15 06:29:36', NULL, '2026-07-08 06:29:36'),
(108, 12, '06b4e372e512e298f28ee4942efdfda4665b11fd0e56c9444951004f861f5c02', '2026-07-15 06:57:32', NULL, '2026-07-08 06:57:32'),
(109, 5, 'ccd15a20db245125dc2802611dcf635f6e8519deb1ceed52f605eb8a9518b5cd', '2026-07-15 07:36:15', NULL, '2026-07-08 07:36:15'),
(110, 6, 'ee254df6b465705e372ce21a73ba6f2b5ad79bdb1bb117645ac812940cc98bf6', '2026-07-15 07:39:14', NULL, '2026-07-08 07:39:14'),
(111, 12, '9f453235bc74d9e7b0bab2f53aae8fce78a6723133d6457d49da869e52b6a953', '2026-07-15 07:41:53', NULL, '2026-07-08 07:41:53'),
(112, 6, '27bb68b97602fc8cc4879ec5bf69624073ed568a448f286c322e75099c0ec2f1', '2026-07-15 08:06:04', NULL, '2026-07-08 08:06:04'),
(113, 13, '628fa312d140864b201f3a2429facc4c88154cdbef860745a86ddc7a0f1c43d9', '2026-07-15 08:08:40', NULL, '2026-07-08 08:08:40'),
(114, 13, '9193726c76e7b2b199568d071cde1ca9d62c6f790365731adab42aedcf3704c5', '2026-07-15 09:44:58', NULL, '2026-07-08 09:44:58'),
(115, 5, 'c18a99439c8c7b3bd4524aa7f8fb01c02caf019eab0fb6bc794206cdd0270a48', '2026-07-15 10:17:31', NULL, '2026-07-08 10:17:31'),
(116, 13, '987010b38da9a1321804d6ef41ecda550bb01f945b6b414efb4737612de76f44', '2026-07-15 10:48:04', NULL, '2026-07-08 10:48:04'),
(117, 5, '779bfdce0afb5976cc085092d97f57308487cdfc41f69fd7d4f24f94d5aa9c95', '2026-07-15 11:21:52', NULL, '2026-07-08 11:21:52'),
(118, 6, 'cd1d8e9d82a90521678fbbdea6ff122992e11b303a86fea5cc567f956ad76203', '2026-07-15 11:22:25', NULL, '2026-07-08 11:22:25'),
(119, 5, '3d9faef6854297c6a89c6244232e154ac4441fa3eb3b13c9ef6c37913626affb', '2026-07-15 11:31:35', NULL, '2026-07-08 11:31:35'),
(120, 13, '863b4e168b7649f910384d47222e1648f5df8597591f35a10ea59e45520d634a', '2026-07-15 11:43:15', NULL, '2026-07-08 11:43:15'),
(121, 5, '6e8e574edfe9ae4a531db475faaad18bb3afc85a088d2490e3bddce79f69ca38', '2026-07-15 12:23:29', NULL, '2026-07-08 12:23:29'),
(122, 5, '16b40ee9c8e03d41b6be3eee6604328511d8da37f579655f648dfd90058137f0', '2026-07-15 12:30:06', NULL, '2026-07-08 12:30:06'),
(123, 5, 'f760b3dd95ec23ba7b3333c70831a890edce2e6c66f476c3602d0f2b4b443235', '2026-07-15 14:28:52', NULL, '2026-07-08 14:28:52'),
(124, 12, 'a59a535052eb4803622ca7b9e0d050d69ddd379fcb5db016daf15e667a76f116', '2026-07-15 15:19:35', NULL, '2026-07-08 15:19:35'),
(125, 6, 'd979ae189651ad82592c92acc1641a7ad0f29025760219681aff6105a70be331', '2026-07-16 04:47:51', NULL, '2026-07-09 04:47:51'),
(126, 6, '4885e81f22ac058ad4cce44fe99d176af6b9cb4b061519ed80e949f5aae07b16', '2026-07-16 05:05:04', NULL, '2026-07-09 05:05:04'),
(127, 5, '74b46b6c0a6708883d53085a7639edc72d83b1342e2598bf395a7d5b0d319a62', '2026-07-16 05:49:12', NULL, '2026-07-09 05:49:12'),
(128, 6, '75ca58d5d858feff98a25b86a90398596bf77fc88a9653a7e0b4b630cfdad886', '2026-07-16 06:08:02', NULL, '2026-07-09 06:08:02'),
(129, 6, 'b4f2a7ae64cb28bf6d440d3cede11a9c2e87c0e98a17a55e4b7693ea341c015a', '2026-07-16 06:08:35', NULL, '2026-07-09 06:08:35'),
(130, 6, 'c4769ed68686074a45cca30ea72a9fdb83cae8ab86abf053c6c3154016acd0d7', '2026-07-16 06:15:25', NULL, '2026-07-09 06:15:25'),
(131, 4, '8d252895a30f193eee40c4c7082fa4edccfed838be67d77cae76891b8d9c7c3f', '2026-07-16 06:51:51', NULL, '2026-07-09 06:51:51'),
(132, 5, 'ee47acd49086c900fe9b069c70ab2ced31b785850f60339d773d8fcf333c2b9c', '2026-07-16 06:53:45', NULL, '2026-07-09 06:53:45'),
(133, 4, '95990727db4001df7e8ca1f3986973a0ae905ddc054026aeabf952bc0f9a9ec0', '2026-07-16 07:01:05', NULL, '2026-07-09 07:01:05'),
(134, 5, '0c799d18b91c35589a76d2ac127e2101ed179fc7be89f22f0f3cdabb65f0483d', '2026-07-16 07:22:53', NULL, '2026-07-09 07:22:53'),
(135, 6, '4956fdf0655f637dae3d56bccccad91e14820046da3a7b0c91dee22e82e4dd20', '2026-07-16 07:25:28', NULL, '2026-07-09 07:25:28'),
(136, 6, '3501dedcf7de97e352027922bfaa23acde0a2077bfdc85e306cceb79b1561613', '2026-07-16 07:28:30', NULL, '2026-07-09 07:28:30'),
(137, 4, '2295782c344227a9577488b70837d6dd61383cdf93648e66ebcee3909e180fee', '2026-07-16 07:45:40', NULL, '2026-07-09 07:45:40'),
(138, 18, '3e635f5a4c8eb6d31f313d6a73a0b844980795a9a532dba543f041e6bf6987b0', '2026-07-16 07:46:57', NULL, '2026-07-09 07:46:57'),
(139, 4, '2c40a5f43e6d73c7bc49b17c922197911380f6410a1c1502de669ee7d41e3f1e', '2026-07-16 07:47:12', NULL, '2026-07-09 07:47:12'),
(140, 18, '4ac745d098d751179ba3c87992b151dbb759a3bc8646d7dc0d548bedaa59b7e8', '2026-07-16 07:48:00', NULL, '2026-07-09 07:48:00'),
(141, 18, 'f31ede7b4382204cc3f77932f45ca95db54e0ad673af55a62a9ccc64c97147cc', '2026-07-16 07:51:29', NULL, '2026-07-09 07:51:29'),
(142, 13, 'bfae17282792bec86d2e4737f5c231656e68493a08dca2f6eb862c3442b5f0dc', '2026-07-16 07:57:50', NULL, '2026-07-09 07:57:50'),
(143, 5, 'bce466e264e24c1084d06e095d5812c2fd6ddd1524394229cd65ded2c387b1a8', '2026-07-16 07:58:51', NULL, '2026-07-09 07:58:51'),
(144, 4, '36bc98ae96e762ca53793740979a3e0bef7595ccc132f752f06cad28119f9149', '2026-07-16 08:24:59', NULL, '2026-07-09 08:24:59'),
(145, 6, '744f7a7edb8df2b9b3759a341d665c3be909575e7d8939817443bee2f78d4e20', '2026-07-16 08:27:30', NULL, '2026-07-09 08:27:30'),
(146, 19, '7c0a32ce8969d4bc9aaf71d7a168df4ade32a7c87d689ae6425e91f272594259', '2026-07-16 09:28:43', NULL, '2026-07-09 09:28:43'),
(147, 5, 'b2a002dcdea7f826eff7569ff36145c7dcc2960eafa51c0ec0bc14698344a7b3', '2026-07-16 09:34:28', NULL, '2026-07-09 09:34:28'),
(148, 19, '28b2d97efaf571a708f154ea45ecd222bcfae72beb760992ddc9a08dacd666b9', '2026-07-16 09:59:47', NULL, '2026-07-09 09:59:47'),
(149, 4, '3148e688514ec2ccf1bfd2c41571d545f70d29137797917ea8f7bab50aff2741', '2026-07-16 10:04:06', NULL, '2026-07-09 10:04:06'),
(150, 19, '07e5d08f4bfe7a9d4e706b4218520049da28fbf17fb22d94f29d50b810ce0fc0', '2026-07-16 10:06:33', NULL, '2026-07-09 10:06:33'),
(151, 19, '7376a1e9719277deee63438611dc7a5d90e6d73b3d492b97aea0ac6e52e2e216', '2026-07-16 10:11:36', NULL, '2026-07-09 10:11:36'),
(152, 20, '04def11ebd93d03c876a9a505c9ee1bea673060f13d4b9a387bf6caa5725612f', '2026-07-16 10:15:05', NULL, '2026-07-09 10:15:05'),
(153, 5, 'ddade94b97019af0403278fcbdba551c1b7adc20695eac09520cd9eaf66f9147', '2026-07-16 10:56:36', NULL, '2026-07-09 10:56:36'),
(154, 19, 'b9d116b284ea42c2d789429bf387cca33f9159e37a4a07ba7fc0552887536264', '2026-07-16 11:02:31', NULL, '2026-07-09 11:02:31'),
(155, 4, '3d348e535f679c7b60c57c8b6d4afb7535d1580a08000203412373f71b5c3a84', '2026-07-16 13:03:02', NULL, '2026-07-09 13:03:02'),
(156, 19, '96454d0ad02d96ec6bed656b39a35fe628227c820da233d940711337e0093bd5', '2026-07-16 13:24:09', NULL, '2026-07-09 13:24:09'),
(157, 4, 'a989ad3bd16d55101a65669e4b82e7a068d68acca8203622f0fac3f90c862902', '2026-07-16 15:02:57', NULL, '2026-07-09 15:02:57'),
(158, 7, '68506d6ba06d18a5a6d8d2243f55d88e70763b9e864c84746a14b4706789d567', '2026-07-16 16:17:30', NULL, '2026-07-09 16:17:30'),
(159, 19, 'bbfe0536727634dfa514b3c6158ad04c324b3eb89b82a195849218862aec9d88', '2026-07-16 18:39:50', NULL, '2026-07-09 18:39:50'),
(160, 7, 'b3b56815ef5f219afa2b116074280ca3c5d1e408a69234687b213439f7e575f5', '2026-07-16 20:15:24', NULL, '2026-07-09 20:15:24'),
(161, 7, '4ac65f9e7805df7d488fb9db82618367b8386a3be713f51a6bafd5b8b5433a12', '2026-07-17 04:40:01', NULL, '2026-07-10 04:40:01'),
(162, 21, '6838b3e1f220f7a041ddf2fbb6bfbee53c875d94f17ff8817bd68ae2e0b4de41', '2026-07-17 05:11:39', NULL, '2026-07-10 05:11:39'),
(163, 19, '218af3e5db13a08af7e511ee9b4af8026f18ed9aa8fd309d1a8f4d27d16153a8', '2026-07-17 07:37:42', NULL, '2026-07-10 07:37:42'),
(164, 5, 'e852e61a82a233b5349aec5b36ecfdb9d16c51546072fd82606fb289a4eef196', '2026-07-17 11:54:23', NULL, '2026-07-10 11:54:23'),
(165, 19, 'bf0c3a0900e4057e4c17d9df1fb62b9c5ff6e9fb8b3f698015ee447e809fbebc', '2026-07-17 14:06:22', NULL, '2026-07-10 14:06:22'),
(166, 4, 'adc19d57ffce0ca59088dfcd6b0d6b5325d20a8f7963cda99240c196b33d0b31', '2026-07-17 16:04:52', NULL, '2026-07-10 16:04:52'),
(167, 9, 'c4e9ca6ad31a09b49177594b7977f28bff793ae0d01421bc0e95ad8cddb1dcb0', '2026-07-17 16:05:43', NULL, '2026-07-10 16:05:43'),
(168, 11, 'e08897b874aeda2bd3a9b15cfd587de83f57919648f4ce90f95e13689b5858b8', '2026-07-17 16:18:08', NULL, '2026-07-10 16:18:08'),
(169, 7, '5089a25b1bc1b576d762f2c0d59c42c07e6223d241c8170e512c0816ebfa333b', '2026-07-17 18:24:35', NULL, '2026-07-10 18:24:35'),
(170, 9, '1313d71f7688651bdae82c42318d31a24ac5cdd75e3ec9adb64eb83d6c0ecf66', '2026-07-18 01:34:14', NULL, '2026-07-11 01:34:14'),
(171, 4, '2769136d8a6c9404d76942e9215dbce970f8054c5e88e0efb8749f99ede4d37a', '2026-07-18 03:23:45', NULL, '2026-07-11 03:23:45'),
(172, 5, 'de6be70ca3e7c00711e57508d5a95708d94b30110b1fd6ff73960dea512af232', '2026-07-18 03:51:14', NULL, '2026-07-11 03:51:14'),
(173, 5, '2ded81ed4e70113b7b634723d22d910294c5838be2bab6b6f170bc12accc6312', '2026-07-18 05:01:06', NULL, '2026-07-11 05:01:06'),
(174, 19, '1820b907501353d7f68f92a4b7e90b4797382fb9af552f5f985453fc218d35f1', '2026-07-18 05:41:44', NULL, '2026-07-11 05:41:44'),
(175, 11, 'f576efc2a9dc0a895f850f0e635e8bfb3b1159d0bf4242ec7f1a5fa5c826c7c6', '2026-07-18 05:44:11', NULL, '2026-07-11 05:44:11'),
(176, 12, '5ee15769b553120fa2b40c61af2613e53a1202857fe3938cd517788943be17fb', '2026-07-18 06:00:37', NULL, '2026-07-11 06:00:37'),
(177, 7, '2018ad29e4320ff6a3986ac14698af327851ad1ea7dce8cb6fe96a054254fc1f', '2026-07-18 07:13:31', NULL, '2026-07-11 07:13:31'),
(178, 9, 'c30c195e228e4e7c0af39c068e2b9d32b9224e17719a5a81a601b30f8fb5fb01', '2026-07-18 07:52:48', NULL, '2026-07-11 07:52:48'),
(179, 4, '83fe85fa526a87dfb35b9f1c0b975179a7f9a11b1e77ffd41b9ffbd37a3205bb', '2026-07-18 08:24:24', NULL, '2026-07-11 08:24:24'),
(180, 20, '0badb59b68287a836d7081a05ed59c38fbdaba1b40642249ca30b350080a0507', '2026-07-18 08:25:28', NULL, '2026-07-11 08:25:28'),
(181, 18, '39effc529166c52daae46a156fd8630b3c23c49e5a08907cb78675dab856ee8a', '2026-07-18 09:34:43', NULL, '2026-07-11 09:34:43'),
(182, 7, 'dcc876e251941c15d93a1d5ec6208be9dc4ce3179c7376b533457dc6827f6c3f', '2026-07-18 09:45:30', NULL, '2026-07-11 09:45:30'),
(183, 18, 'fc000d992411ab518edd30b388775b674308471a495efe60f480cbc5b9317e36', '2026-07-18 11:15:32', NULL, '2026-07-11 11:15:32'),
(184, 12, '5546217cd0aa97e0ad059e8fc4aac5c2b68d35c15e2ea8587336e81162bf7995', '2026-07-18 11:44:46', NULL, '2026-07-11 11:44:46'),
(185, 6, 'dc420020f7075a09008084de6706e2749fa6ad6deaf64ba69378f90fa8f44609', '2026-07-18 12:24:48', NULL, '2026-07-11 12:24:48'),
(186, 3, '4d44f9cc824d3b6198821696cbf790cbdb6f8d1d8b0df0d872c0ed8b04808e83', '2026-07-18 12:32:04', NULL, '2026-07-11 12:32:04'),
(187, 6, 'af2c595fe65a4d08e14627cce89ed70fb7144874f0afac650d85041be12a9cd1', '2026-07-18 17:46:04', NULL, '2026-07-11 17:46:04'),
(188, 8, '37a032864a79a75a40543bef0fd8cc75c9f393505474479fae69d68610eeedb2', '2026-07-18 19:33:33', NULL, '2026-07-11 19:33:33'),
(189, 7, '77b4b23be32031394b165a4f4c594beab5d13a2f441ba0d787b945eabd026603', '2026-07-18 20:31:11', NULL, '2026-07-11 20:31:11'),
(190, 8, '05b3b62f7a7bcfd14b5b09971eb564429b87c2dbc51a6083cd3c67a598fa1696', '2026-07-18 20:37:54', NULL, '2026-07-11 20:37:54'),
(191, 18, '9480e4e15c6fe05808a36b8cbe347c2fc915770bd019ae3633f64c619441cafd', '2026-07-19 11:46:48', NULL, '2026-07-12 11:46:48'),
(192, 12, '38653769e94831a9402e85240ddfbfe9807c32258613b685e728ac2778e17b41', '2026-07-19 16:13:14', NULL, '2026-07-12 16:13:14'),
(193, 18, '67ff3a3a448d95e2ae5abb6e5b6390152b1b9ee55ab58105808bc51f422537b3', '2026-07-19 22:26:15', NULL, '2026-07-12 22:26:15'),
(194, 12, '40d77d94402376f6790709671c6d08362670328448fd3bfeea506939a10b8b00', '2026-07-20 04:53:21', NULL, '2026-07-13 04:53:21'),
(195, 12, 'a4a0247e54641565cc3fdd7cd4190f41ab9eaabc046118d8bccaf188949e5a68', '2026-07-20 06:22:18', NULL, '2026-07-13 06:22:18'),
(196, 5, '7e32c9192fc53697fca6c3892af3080ffc3a93eb40d83579f6d80829cf0fd7b1', '2026-07-20 06:39:48', NULL, '2026-07-13 06:39:48'),
(197, 12, '6c158149787379e3dafc9a0f537c1de25449c7350bda0c6c201c53d8fd4ac16c', '2026-07-20 07:01:25', NULL, '2026-07-13 07:01:25'),
(198, 6, 'd20a8268b2501c912768a63049e291bb564d65ae1e0fcf0c0745bec091672495', '2026-07-20 07:14:54', NULL, '2026-07-13 07:14:54'),
(199, 18, 'c974d1766f6bf2e89a0c45eade6df5406bc9a50740eae5951acaa686fff4ff55', '2026-07-20 07:24:04', NULL, '2026-07-13 07:24:04'),
(200, 7, 'a3bd7ca12243f2c6c0ccfa667d2470fbb2bdfad944a6bc83d91408003c296af0', '2026-07-20 08:04:25', NULL, '2026-07-13 08:04:25'),
(201, 16, '73283921aff718a6443ad0a35dcc3703346b875f8ec64974a7bf71fd3c86cfd7', '2026-07-20 08:21:53', NULL, '2026-07-13 08:21:53'),
(202, 7, '5164096b3f56a78ef12940aba2afbe5e777b6ca66cc70d0c111d6310c858b294', '2026-07-20 09:19:25', NULL, '2026-07-13 09:19:25'),
(203, 4, '19f6d4bab555f8e67d1a45a71c2f553ebffc77bf14b5c4df2e8ae7d4055def70', '2026-07-20 09:26:59', NULL, '2026-07-13 09:26:59'),
(204, 4, '0f42976920a236d0d5dcc07d13d13984a46034da84bb2f39454df5922a19aea3', '2026-07-20 09:27:42', NULL, '2026-07-13 09:27:42'),
(205, 21, '984f0de65cc2239083e11da7904b29a9dcb2d2fef04ee4aa3894e4723ecb732a', '2026-07-20 09:28:28', NULL, '2026-07-13 09:28:28'),
(206, 5, 'f31c58ffd269da31e000cc860f323df81097a45e1799f3365f28904475d633fb', '2026-07-20 09:37:35', NULL, '2026-07-13 09:37:35'),
(207, 5, '236e2feed716e0f099f4c297c671fcdab45acd8a6951f07a3bf0b1ea29b64170', '2026-07-20 10:38:25', NULL, '2026-07-13 10:38:25'),
(208, 20, 'dd61e6ec2ec6512b1c46ca5485301baf7adbb3d93a2c9cd9ea5fa19b0f417700', '2026-07-20 13:12:22', NULL, '2026-07-13 13:12:22'),
(209, 12, 'afee5efdff3ad79e8a3fd22f17590c6e6a529ae505454f74011abb8f93d09a27', '2026-07-20 13:42:30', NULL, '2026-07-13 13:42:30'),
(210, 16, 'd22b703c9d03d8f364cc118f85485c1cb72686a1d38a47612f6a73b315153f1b', '2026-07-20 15:07:03', NULL, '2026-07-13 15:07:03'),
(211, 16, '1fd3fb98aefdec78d852d779a93ebbbfd59198d0ccc7ae001e37038c70dc9fa0', '2026-07-20 15:10:14', NULL, '2026-07-13 15:10:14'),
(212, 16, '1e7478e4affabe08275f0c7bfd29a58e8375542e9e47bde880e389a39d495790', '2026-07-20 15:11:39', NULL, '2026-07-13 15:11:39'),
(213, 16, '7a01a37745ed1b6ce1322c82221ab376f9d3306930626a466fe5754e789bb2fb', '2026-07-20 16:24:58', NULL, '2026-07-13 16:24:58'),
(214, 10, 'c64efa3cddfa9ee4bc7b76909a83d1db6a189ffd4f41b620524f13e22f0de05e', '2026-07-20 18:12:30', NULL, '2026-07-13 18:12:30'),
(215, 10, '7d297ed216a6b7af6cd3bb82de1d2ba8aeb201f44bf94ea50af1e6415a65a319', '2026-07-20 18:19:22', NULL, '2026-07-13 18:19:22'),
(216, 10, '46f46efd67a5792ef7c28f2da95cb201d662c402d00ac183e96b3c0ed97ac29d', '2026-07-20 18:21:06', NULL, '2026-07-13 18:21:06'),
(217, 9, '64b78a7f97125bb248f81f37b7a1f6caa188b2c16a02ba309ac0ed354c1ade55', '2026-07-21 02:55:02', NULL, '2026-07-14 02:55:02'),
(218, 4, '26836d039308ce2495a47dd6eef49690122606c7da31d600784e5a3323fc004e', '2026-07-21 03:06:54', NULL, '2026-07-14 03:06:54');

-- --------------------------------------------------------

--
-- Table structure for table `revision_sessions`
--

CREATE TABLE `revision_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int(10) UNSIGNED DEFAULT NULL,
  `topics_revised` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`topics_revised`)),
  `mcqs_solved` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `mcqs_correct` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `accuracy` decimal(5,2) DEFAULT NULL,
  `summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`summary`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revision_session_items`
--

CREATE TABLE `revision_session_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `item_type` enum('mcq','flashcard','note','lecture') NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `is_correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Administrator', 'admin', 'Full system access', '2026-07-02 12:24:21'),
(2, 'Teacher', 'teacher', 'Course instructor access', '2026-07-02 12:24:21'),
(3, 'Student', 'student', 'Student access', '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `permission_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_month_uploads`
--

CREATE TABLE `schedule_month_uploads` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `month_year` char(7) NOT NULL COMMENT 'YYYY-MM',
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `row_count` int(10) UNSIGNED DEFAULT 0,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `applied_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schema_migrations`
--

INSERT INTO `schema_migrations` (`id`, `migration`, `applied_at`) VALUES
(1, '001_course_class_schedule.sql', '2026-07-04 05:29:43'),
(2, '002_schedule_and_batches.sql', '2026-07-04 05:29:43'),
(3, '003_ai_learning_module.sql', '2026-07-04 05:29:43'),
(4, '004_material_uploader.sql', '2026-07-04 05:29:44'),
(5, '005_schedule_month_uploads.sql', '2026-07-04 05:29:44'),
(6, '006_quiz_show_review.sql', '2026-07-04 05:29:44'),
(16, '007_premium_study_features.sql', '2026-07-04 18:56:52'),
(17, '009_assignment_multi_files.sql', '2026-07-07 05:02:53'),
(18, '010_assignment_files_backfill.sql', '2026-07-07 05:02:53'),
(21, '008_interactive_assignments.sql', '2026-07-07 12:23:13');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `updated_at`) VALUES
(1, 'site_name', 'NextGen Medics', 'general', '2026-07-02 12:24:21'),
(2, 'site_email', 'info@nextgenmedics.com', 'general', '2026-07-02 12:24:21'),
(3, 'certificate_prefix', 'NGM', 'certificates', '2026-07-02 12:24:21'),
(4, 'max_login_attempts', '5', 'security', '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `student_badges`
--

CREATE TABLE `student_badges` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `badge_id` int(10) UNSIGNED NOT NULL,
  `earned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_badges`
--

INSERT INTO `student_badges` (`id`, `student_id`, `badge_id`, `earned_at`) VALUES
(1, 7, 1, '2026-07-11 07:13:32');

-- --------------------------------------------------------

--
-- Table structure for table `student_flashcard_progress`
--

CREATE TABLE `student_flashcard_progress` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `flashcard_id` int(10) UNSIGNED NOT NULL,
  `status` enum('new','learning','mastered') NOT NULL DEFAULT 'new',
  `is_favorite` tinyint(1) NOT NULL DEFAULT 0,
  `is_difficult` tinyint(1) NOT NULL DEFAULT 0,
  `review_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_flashcard_progress`
--

INSERT INTO `student_flashcard_progress` (`id`, `student_id`, `flashcard_id`, `status`, `is_favorite`, `is_difficult`, `review_count`, `last_reviewed_at`, `created_at`, `updated_at`) VALUES
(21, 19, 333, 'mastered', 0, 0, 1, '2026-07-09 09:29:26', '2026-07-09 09:29:26', '2026-07-09 09:29:26'),
(22, 19, 334, 'mastered', 0, 0, 1, '2026-07-09 09:29:32', '2026-07-09 09:29:32', '2026-07-09 09:29:32'),
(23, 19, 335, 'mastered', 0, 0, 1, '2026-07-09 09:29:38', '2026-07-09 09:29:38', '2026-07-09 09:29:38'),
(24, 19, 336, 'mastered', 0, 0, 1, '2026-07-09 09:29:43', '2026-07-09 09:29:43', '2026-07-09 09:29:43'),
(25, 19, 338, 'mastered', 0, 0, 2, '2026-07-09 09:30:25', '2026-07-09 09:29:55', '2026-07-09 09:30:25'),
(26, 19, 339, 'mastered', 0, 0, 2, '2026-07-09 09:30:31', '2026-07-09 09:30:00', '2026-07-09 09:30:31'),
(29, 19, 340, 'mastered', 0, 0, 1, '2026-07-09 09:30:36', '2026-07-09 09:30:36', '2026-07-09 09:30:36'),
(30, 19, 341, 'mastered', 0, 0, 1, '2026-07-09 09:30:41', '2026-07-09 09:30:41', '2026-07-09 09:30:41'),
(31, 19, 342, 'mastered', 0, 0, 1, '2026-07-09 09:30:46', '2026-07-09 09:30:46', '2026-07-09 09:30:46'),
(32, 19, 343, 'mastered', 0, 0, 1, '2026-07-09 09:30:51', '2026-07-09 09:30:51', '2026-07-09 09:30:51'),
(33, 19, 344, 'learning', 0, 0, 1, '2026-07-09 09:31:22', '2026-07-09 09:31:22', '2026-07-09 09:31:22'),
(34, 19, 345, 'mastered', 0, 0, 1, '2026-07-09 09:32:06', '2026-07-09 09:32:06', '2026-07-09 09:32:06'),
(35, 19, 346, 'mastered', 0, 0, 1, '2026-07-09 09:32:09', '2026-07-09 09:32:09', '2026-07-09 09:32:09'),
(36, 19, 347, 'mastered', 0, 0, 1, '2026-07-09 09:32:15', '2026-07-09 09:32:15', '2026-07-09 09:32:15'),
(37, 19, 348, 'mastered', 0, 0, 1, '2026-07-09 09:32:21', '2026-07-09 09:32:21', '2026-07-09 09:32:21'),
(38, 19, 349, 'mastered', 0, 0, 1, '2026-07-09 09:32:25', '2026-07-09 09:32:25', '2026-07-09 09:32:25'),
(39, 19, 351, 'mastered', 0, 0, 1, '2026-07-09 09:32:51', '2026-07-09 09:32:51', '2026-07-09 09:32:51'),
(40, 19, 352, 'mastered', 0, 0, 1, '2026-07-09 09:32:58', '2026-07-09 09:32:58', '2026-07-09 09:32:58'),
(41, 19, 354, 'mastered', 0, 0, 1, '2026-07-09 09:33:42', '2026-07-09 09:33:42', '2026-07-09 09:33:42'),
(42, 19, 355, 'mastered', 0, 0, 1, '2026-07-09 09:33:51', '2026-07-09 09:33:51', '2026-07-09 09:33:51'),
(43, 19, 384, 'mastered', 0, 0, 1, '2026-07-09 10:00:04', '2026-07-09 10:00:04', '2026-07-09 10:00:04'),
(44, 19, 386, 'mastered', 0, 0, 1, '2026-07-09 10:00:17', '2026-07-09 10:00:17', '2026-07-09 10:00:17'),
(45, 19, 387, 'mastered', 0, 0, 1, '2026-07-09 10:00:23', '2026-07-09 10:00:23', '2026-07-09 10:00:23'),
(46, 19, 388, 'mastered', 0, 0, 1, '2026-07-09 10:00:30', '2026-07-09 10:00:30', '2026-07-09 10:00:30'),
(47, 19, 389, 'mastered', 0, 0, 1, '2026-07-09 10:00:36', '2026-07-09 10:00:36', '2026-07-09 10:00:36'),
(48, 19, 390, 'mastered', 0, 0, 1, '2026-07-09 10:00:42', '2026-07-09 10:00:42', '2026-07-09 10:00:42'),
(49, 19, 391, 'mastered', 0, 0, 1, '2026-07-09 10:00:49', '2026-07-09 10:00:49', '2026-07-09 10:00:49'),
(50, 19, 418, 'mastered', 0, 0, 2, '2026-07-09 18:40:27', '2026-07-09 11:21:12', '2026-07-09 18:40:27'),
(51, 19, 419, 'mastered', 0, 0, 1, '2026-07-09 11:21:17', '2026-07-09 11:21:17', '2026-07-09 11:21:17'),
(52, 19, 420, 'mastered', 0, 0, 1, '2026-07-09 11:21:35', '2026-07-09 11:21:35', '2026-07-09 11:21:35'),
(53, 19, 421, 'mastered', 0, 0, 1, '2026-07-09 11:21:40', '2026-07-09 11:21:40', '2026-07-09 11:21:40'),
(54, 19, 422, 'mastered', 0, 0, 1, '2026-07-09 11:21:56', '2026-07-09 11:21:56', '2026-07-09 11:21:56'),
(55, 19, 423, 'mastered', 0, 0, 1, '2026-07-09 11:22:02', '2026-07-09 11:22:02', '2026-07-09 11:22:02'),
(56, 19, 424, 'mastered', 0, 0, 1, '2026-07-09 11:22:05', '2026-07-09 11:22:05', '2026-07-09 11:22:05'),
(58, 19, 453, 'mastered', 0, 0, 1, '2026-07-10 14:06:38', '2026-07-10 14:06:38', '2026-07-10 14:06:38'),
(59, 19, 454, 'mastered', 0, 0, 1, '2026-07-10 14:06:45', '2026-07-10 14:06:45', '2026-07-10 14:06:45'),
(60, 19, 455, 'mastered', 0, 0, 1, '2026-07-10 14:06:51', '2026-07-10 14:06:51', '2026-07-10 14:06:51'),
(61, 19, 456, 'mastered', 0, 0, 1, '2026-07-10 14:06:56', '2026-07-10 14:06:56', '2026-07-10 14:06:56'),
(62, 19, 457, 'mastered', 0, 0, 1, '2026-07-10 14:07:00', '2026-07-10 14:07:00', '2026-07-10 14:07:00'),
(63, 19, 458, 'mastered', 0, 0, 1, '2026-07-10 14:07:09', '2026-07-10 14:07:09', '2026-07-10 14:07:09'),
(64, 19, 459, 'mastered', 0, 0, 1, '2026-07-10 14:07:13', '2026-07-10 14:07:13', '2026-07-10 14:07:13'),
(65, 19, 460, 'mastered', 0, 0, 1, '2026-07-10 14:07:18', '2026-07-10 14:07:18', '2026-07-10 14:07:18'),
(66, 11, 334, 'mastered', 0, 0, 1, '2026-07-10 16:21:17', '2026-07-10 16:21:17', '2026-07-10 16:21:17'),
(67, 7, 383, 'mastered', 0, 0, 1, '2026-07-11 07:33:14', '2026-07-11 07:33:14', '2026-07-11 07:33:14'),
(68, 7, 384, 'learning', 0, 0, 1, '2026-07-11 07:33:33', '2026-07-11 07:33:33', '2026-07-11 07:33:33'),
(69, 18, 334, 'mastered', 0, 0, 1, '2026-07-12 22:31:30', '2026-07-12 22:31:30', '2026-07-12 22:31:30'),
(70, 18, 335, 'mastered', 0, 0, 1, '2026-07-12 22:33:12', '2026-07-12 22:33:12', '2026-07-12 22:33:12'),
(71, 18, 336, 'mastered', 0, 0, 1, '2026-07-12 22:34:03', '2026-07-12 22:34:03', '2026-07-12 22:34:03'),
(72, 18, 338, 'learning', 0, 0, 1, '2026-07-12 22:34:21', '2026-07-12 22:34:21', '2026-07-12 22:34:21'),
(73, 18, 340, 'mastered', 0, 0, 1, '2026-07-12 22:35:40', '2026-07-12 22:35:40', '2026-07-12 22:35:40'),
(74, 18, 341, 'mastered', 0, 0, 1, '2026-07-12 22:35:49', '2026-07-12 22:35:49', '2026-07-12 22:35:49'),
(75, 18, 342, 'mastered', 0, 0, 1, '2026-07-12 22:36:10', '2026-07-12 22:36:10', '2026-07-12 22:36:10'),
(76, 18, 343, 'mastered', 0, 0, 1, '2026-07-12 22:36:23', '2026-07-12 22:36:23', '2026-07-12 22:36:23'),
(77, 12, 333, 'learning', 0, 0, 1, '2026-07-13 06:52:01', '2026-07-13 06:52:01', '2026-07-13 06:52:01'),
(78, 16, 336, 'learning', 0, 0, 1, '2026-07-13 15:13:04', '2026-07-13 15:13:04', '2026-07-13 15:13:04'),
(79, 16, 338, 'learning', 0, 0, 1, '2026-07-13 15:13:04', '2026-07-13 15:13:04', '2026-07-13 15:13:04'),
(80, 16, 334, 'mastered', 0, 0, 1, '2026-07-13 15:13:04', '2026-07-13 15:13:04', '2026-07-13 15:13:04'),
(81, 16, 333, 'mastered', 0, 0, 1, '2026-07-13 15:13:04', '2026-07-13 15:13:04', '2026-07-13 15:13:04'),
(82, 16, 335, 'learning', 0, 0, 1, '2026-07-13 15:13:04', '2026-07-13 15:13:04', '2026-07-13 15:13:04'),
(83, 16, 339, 'mastered', 0, 0, 1, '2026-07-13 15:13:07', '2026-07-13 15:13:07', '2026-07-13 15:13:07'),
(84, 16, 340, 'mastered', 0, 0, 1, '2026-07-13 15:13:13', '2026-07-13 15:13:13', '2026-07-13 15:13:13'),
(85, 16, 341, 'mastered', 0, 0, 1, '2026-07-13 15:13:37', '2026-07-13 15:13:37', '2026-07-13 15:13:37'),
(86, 16, 342, 'mastered', 0, 0, 1, '2026-07-13 15:13:42', '2026-07-13 15:13:42', '2026-07-13 15:13:42'),
(87, 16, 343, 'mastered', 0, 0, 1, '2026-07-13 15:13:47', '2026-07-13 15:13:47', '2026-07-13 15:13:47'),
(88, 16, 344, 'mastered', 0, 0, 1, '2026-07-13 15:13:51', '2026-07-13 15:13:51', '2026-07-13 15:13:51');

-- --------------------------------------------------------

--
-- Table structure for table `student_mistakes`
--

CREATE TABLE `student_mistakes` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `mcq_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `chapter` varchar(150) DEFAULT NULL,
  `topic` varchar(255) DEFAULT NULL,
  `wrong_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `consecutive_correct` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','mastered') NOT NULL DEFAULT 'active',
  `last_wrong_at` timestamp NULL DEFAULT current_timestamp(),
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_mistakes`
--

INSERT INTO `student_mistakes` (`id`, `student_id`, `mcq_id`, `subject`, `chapter`, `topic`, `wrong_count`, `consecutive_correct`, `status`, `last_wrong_at`, `last_attempt_at`, `created_at`, `updated_at`) VALUES
(61, 19, 94, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(62, 19, 96, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(63, 19, 97, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(64, 19, 98, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(65, 19, 99, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(66, 19, 100, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(67, 19, 101, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(68, 19, 102, 'Pathology', 'Renal', NULL, 1, 0, 'active', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09', '2026-07-09 10:05:09'),
(69, 7, 168, 'Pathology', 'Endocrinology', NULL, 1, 0, 'active', '2026-07-11 07:29:41', '2026-07-11 07:29:41', '2026-07-11 07:29:41', '2026-07-11 07:29:41'),
(70, 7, 177, 'Pathology', 'Endocrinology', NULL, 1, 0, 'active', '2026-07-11 07:29:41', '2026-07-11 07:29:41', '2026-07-11 07:29:41', '2026-07-11 07:29:41'),
(71, 16, 195, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(72, 16, 197, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(73, 16, 198, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(74, 16, 199, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(75, 16, 200, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(76, 16, 201, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(77, 16, 202, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(78, 16, 203, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(79, 16, 204, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(80, 16, 205, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(81, 16, 206, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(82, 16, 207, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(83, 16, 208, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(84, 16, 209, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(85, 16, 210, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(86, 16, 211, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(87, 16, 212, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(88, 16, 213, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(89, 16, 214, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(90, 16, 215, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(91, 16, 216, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(92, 16, 217, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(93, 16, 218, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(94, 16, 219, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(95, 16, 220, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05'),
(96, 16, 221, 'Pharmacology', 'Renal Pharmacology', NULL, 1, 0, 'active', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05', '2026-07-13 15:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `study_activity_log`
--

CREATE TABLE `study_activity_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `activity_date` date NOT NULL,
  `activity_type` enum('login','lecture','mcq','revision','flashcard') NOT NULL,
  `count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `study_activity_log`
--

INSERT INTO `study_activity_log` (`id`, `student_id`, `activity_date`, `activity_type`, `count`, `created_at`) VALUES
(1, 17, '2026-07-04', 'login', 11, '2026-07-04 05:41:14'),
(12, 12, '2026-07-05', 'login', 1, '2026-07-05 06:41:07'),
(13, 17, '2026-07-05', 'login', 10, '2026-07-05 15:41:21'),
(15, 17, '2026-07-05', 'revision', 3, '2026-07-05 15:47:14'),
(16, 17, '2026-07-05', 'mcq', 1, '2026-07-05 15:47:40'),
(24, 7, '2026-07-05', 'login', 2, '2026-07-05 16:17:03'),
(28, 7, '2026-07-05', 'revision', 1, '2026-07-05 16:32:52'),
(30, 9, '2026-07-06', 'login', 5, '2026-07-06 10:04:37'),
(34, 9, '2026-07-06', 'flashcard', 6, '2026-07-06 10:13:52'),
(40, 9, '2026-07-06', 'revision', 1, '2026-07-06 10:24:09'),
(41, 8, '2026-07-06', 'login', 1, '2026-07-06 10:29:02'),
(43, 13, '2026-07-06', 'login', 2, '2026-07-06 12:15:57'),
(45, 12, '2026-07-06', 'login', 3, '2026-07-06 12:18:18'),
(48, 3, '2026-07-06', 'login', 4, '2026-07-06 12:38:13'),
(49, 7, '2026-07-06', 'login', 1, '2026-07-06 15:49:49'),
(53, 17, '2026-07-06', 'login', 2, '2026-07-06 19:34:48'),
(55, 3, '2026-07-07', 'login', 4, '2026-07-07 04:37:07'),
(57, 9, '2026-07-07', 'login', 1, '2026-07-07 06:39:11'),
(60, 7, '2026-07-07', 'login', 5, '2026-07-07 09:47:00'),
(63, 16, '2026-07-07', 'login', 6, '2026-07-07 10:10:01'),
(64, 12, '2026-07-07', 'login', 1, '2026-07-07 11:00:18'),
(72, 8, '2026-07-08', 'login', 1, '2026-07-08 02:57:14'),
(73, 7, '2026-07-08', 'login', 3, '2026-07-08 05:09:40'),
(76, 3, '2026-07-08', 'login', 1, '2026-07-08 06:28:42'),
(77, 12, '2026-07-08', 'login', 8, '2026-07-08 06:57:32'),
(80, 13, '2026-07-08', 'login', 17, '2026-07-08 08:08:40'),
(81, 13, '2026-07-08', 'revision', 13, '2026-07-08 08:10:43'),
(97, 13, '2026-07-08', 'mcq', 1, '2026-07-08 10:38:27'),
(116, 18, '2026-07-09', 'login', 4, '2026-07-09 07:47:02'),
(119, 18, '2026-07-09', 'revision', 1, '2026-07-09 07:54:20'),
(121, 13, '2026-07-09', 'login', 1, '2026-07-09 07:57:50'),
(122, 13, '2026-07-09', 'revision', 3, '2026-07-09 08:00:04'),
(125, 19, '2026-07-09', 'login', 10, '2026-07-09 09:28:43'),
(126, 19, '2026-07-09', 'revision', 30, '2026-07-09 09:29:04'),
(127, 19, '2026-07-09', 'flashcard', 37, '2026-07-09 09:29:26'),
(160, 19, '2026-07-09', 'mcq', 1, '2026-07-09 10:05:09'),
(170, 20, '2026-07-09', 'login', 3, '2026-07-09 10:15:05'),
(201, 7, '2026-07-09', 'login', 2, '2026-07-09 16:17:31'),
(208, 7, '2026-07-10', 'login', 2, '2026-07-10 04:40:02'),
(209, 21, '2026-07-10', 'login', 2, '2026-07-10 05:11:39'),
(210, 21, '2026-07-10', 'revision', 1, '2026-07-10 05:12:15'),
(211, 7, '2026-07-10', 'revision', 9, '2026-07-10 05:14:00'),
(215, 19, '2026-07-10', 'login', 3, '2026-07-10 07:37:42'),
(216, 19, '2026-07-10', 'revision', 6, '2026-07-10 07:39:12'),
(223, 19, '2026-07-10', 'flashcard', 8, '2026-07-10 14:06:38'),
(232, 9, '2026-07-10', 'login', 1, '2026-07-10 16:05:43'),
(233, 11, '2026-07-10', 'login', 6, '2026-07-10 16:18:09'),
(239, 11, '2026-07-10', 'flashcard', 1, '2026-07-10 16:21:17'),
(247, 9, '2026-07-11', 'login', 3, '2026-07-11 01:34:24'),
(248, 9, '2026-07-11', 'revision', 1, '2026-07-11 01:36:31'),
(250, 19, '2026-07-11', 'login', 1, '2026-07-11 05:41:44'),
(251, 19, '2026-07-11', 'revision', 4, '2026-07-11 05:41:49'),
(254, 11, '2026-07-11', 'login', 1, '2026-07-11 05:44:12'),
(255, 11, '2026-07-11', 'revision', 3, '2026-07-11 05:46:37'),
(258, 12, '2026-07-11', 'login', 2, '2026-07-11 06:00:38'),
(260, 7, '2026-07-11', 'login', 9, '2026-07-11 07:13:32'),
(261, 7, '2026-07-11', 'revision', 10, '2026-07-11 07:13:35'),
(262, 7, '2026-07-11', 'mcq', 1, '2026-07-11 07:29:41'),
(267, 7, '2026-07-11', 'flashcard', 2, '2026-07-11 07:33:14'),
(275, 20, '2026-07-11', 'login', 1, '2026-07-11 08:25:29'),
(276, 18, '2026-07-11', 'login', 2, '2026-07-11 09:34:43'),
(284, 3, '2026-07-11', 'login', 1, '2026-07-11 12:32:05'),
(285, 8, '2026-07-11', 'login', 2, '2026-07-11 19:33:33'),
(290, 18, '2026-07-12', 'login', 4, '2026-07-12 11:46:50'),
(291, 12, '2026-07-12', 'login', 1, '2026-07-12 16:13:14'),
(295, 18, '2026-07-12', 'flashcard', 8, '2026-07-12 22:31:30'),
(303, 12, '2026-07-13', 'login', 5, '2026-07-13 04:53:21'),
(306, 12, '2026-07-13', 'flashcard', 1, '2026-07-13 06:52:01'),
(308, 18, '2026-07-13', 'login', 1, '2026-07-13 07:24:04'),
(309, 7, '2026-07-13', 'login', 3, '2026-07-13 08:04:25'),
(311, 16, '2026-07-13', 'login', 4, '2026-07-13 08:21:54'),
(312, 16, '2026-07-13', 'revision', 4, '2026-07-13 08:23:26'),
(314, 7, '2026-07-13', 'revision', 2, '2026-07-13 08:36:37'),
(317, 21, '2026-07-13', 'login', 3, '2026-07-13 09:28:29'),
(320, 20, '2026-07-13', 'login', 3, '2026-07-13 13:12:23'),
(326, 16, '2026-07-13', 'flashcard', 11, '2026-07-13 15:13:04'),
(338, 16, '2026-07-13', 'mcq', 1, '2026-07-13 15:17:05'),
(341, 10, '2026-07-13', 'login', 3, '2026-07-13 18:12:31'),
(344, 9, '2026-07-14', 'login', 1, '2026-07-14 02:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `study_plans`
--

CREATE TABLE `study_plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `exam_date` date NOT NULL,
  `hours_per_day` decimal(4,1) NOT NULL DEFAULT 2.0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `study_plan_tasks`
--

CREATE TABLE `study_plan_tasks` (
  `id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `task_date` date NOT NULL,
  `task_type` enum('lecture','mcq','flashcard','revision','review') NOT NULL,
  `title` varchar(255) NOT NULL,
  `lecture_id` int(10) UNSIGNED DEFAULT NULL,
  `target_count` smallint(5) UNSIGNED DEFAULT NULL,
  `status` enum('pending','completed','skipped') NOT NULL DEFAULT 'pending',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `study_streaks`
--

CREATE TABLE `study_streaks` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `current_streak` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `longest_streak` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_activity_date` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `study_streaks`
--

INSERT INTO `study_streaks` (`id`, `student_id`, `current_streak`, `longest_streak`, `last_activity_date`, `updated_at`) VALUES
(1, 17, 3, 3, '2026-07-06', '2026-07-06 19:34:48'),
(2, 12, 3, 4, '2026-07-13', '2026-07-13 04:53:21'),
(4, 7, 1, 7, '2026-07-13', '2026-07-13 08:04:25'),
(5, 9, 1, 2, '2026-07-14', '2026-07-14 02:55:04'),
(6, 8, 1, 1, '2026-07-11', '2026-07-11 19:33:33'),
(7, 13, 2, 2, '2026-07-09', '2026-07-09 07:57:50'),
(9, 3, 1, 3, '2026-07-11', '2026-07-11 12:32:05'),
(15, 16, 1, 1, '2026-07-13', '2026-07-13 08:21:54'),
(22, 18, 3, 3, '2026-07-13', '2026-07-13 07:24:04'),
(24, 19, 3, 3, '2026-07-11', '2026-07-11 05:41:44'),
(25, 20, 1, 1, '2026-07-13', '2026-07-13 13:12:23'),
(28, 21, 1, 1, '2026-07-13', '2026-07-13 09:28:29'),
(31, 11, 2, 2, '2026-07-11', '2026-07-11 05:44:12'),
(49, 10, 1, 1, '2026-07-13', '2026-07-13 18:12:31');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_name` varchar(150) NOT NULL,
  `course_name` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT 5,
  `avatar` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `student_name`, `course_name`, `content`, `rating`, `avatar`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Ahmed Khan', 'Anatomy & Physiology Mastery', 'This course transformed my understanding of anatomy. Highly recommended!', 5, NULL, 1, 1, '2026-07-02 12:24:21'),
(2, 'Fatima Ali', 'USMLE Step 1 Prep Intensive', 'Excellent high-yield content. Passed Step 1 on first attempt!', 5, NULL, 1, 2, '2026-07-02 12:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','suspended','pending') DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `avatar`, `bio`, `status`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'admin', 'admin@nextgenmedics.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', NULL, NULL, NULL, 'active', NULL, NULL, '2026-07-02 12:24:21', '2026-07-02 12:24:21', NULL),
(2, 2, 'dr.smith', 'teacher@nextgenmedics.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', NULL, NULL, NULL, 'active', NULL, NULL, '2026-07-02 12:24:21', '2026-07-04 05:38:51', '2026-07-04 05:38:51'),
(3, 3, 'student1', 'student@nextgenmedics.com', '$2y$12$7eqjQRgRgF8AGdLNPkquI.nlBnWId4v8aYt9oLfDqTcKuODhdPjrq', 'Jane', 'Doe', NULL, NULL, NULL, 'active', NULL, '2026-07-11 12:32:04', '2026-07-02 12:24:21', '2026-07-11 12:32:04', NULL),
(4, 1, 'Ammad', 'admin3@nextgenmedics.info', '$2y$12$3OxzyXP4O1bNKFiz0AGPjOiQwwvwdP6tOd5TTFLGWEvEUnJI2clJG', 'Ammad', 'arif', NULL, NULL, NULL, 'active', NULL, '2026-07-14 03:06:54', '2026-07-02 12:27:39', '2026-07-14 03:06:54', NULL),
(5, 2, 'Talha ', 'talhanazeer3@gmail.com', '$2y$12$0jIE8SI0qTJEexO/MLgZ4OZs7Jn.B72Xmaqxw0kih4sog7g64HfZm', 'Talha', 'Nazeer', '', NULL, NULL, 'active', NULL, '2026-07-13 10:38:25', '2026-07-02 12:29:40', '2026-07-13 10:38:25', NULL),
(6, 2, 'Sidrah ', 'sskhan.pk@gmail.com', '$2y$12$JbZrzu.U6OPe82tyryek.e9OlNTpWLm1nm9jur5aeq6CQ/z3qgybu', 'sidrah ', 'khan', '', NULL, NULL, 'active', NULL, '2026-07-13 07:14:54', '2026-07-02 12:30:03', '2026-07-13 07:14:54', NULL),
(7, 3, 'MOBARRA ASIM', 'mobarra.asim25@gmail.com', '$2y$12$V5flysc/8k0dogcKNHxqsOXiZrv.PGRelo9vQ.YwFjuaH8F5G3Pjm', 'MOBARRA ', 'ASIM', '', NULL, NULL, 'active', NULL, '2026-07-13 09:19:25', '2026-07-02 12:32:02', '2026-07-13 09:19:25', NULL),
(8, 3, 'Neelofar Iqbal ', 'neelofar.iqbal.ni@gmail.com', '$2y$12$Ac0.MH3ZIrsn0c0RFdVgDOvppJuLeFzmmDN3piPAgHAJEHOkO16Dq', 'Neelofar  ', 'Iqbal', '', NULL, NULL, 'active', NULL, '2026-07-11 20:37:54', '2026-07-02 12:32:37', '2026-07-11 20:37:54', NULL),
(9, 3, 'Azwa Zubair', 'azwazubair@gmail.com', '$2y$12$L89YbabQqegVHT.wKXcoBOjHfzDJcmXTSB5fkosnrWFL97JJkkH5C', 'Azwa ', 'Zubair', '', NULL, NULL, 'active', NULL, '2026-07-14 02:55:02', '2026-07-02 12:33:09', '2026-07-14 02:55:02', NULL),
(10, 3, 'Naina Abdullah ', 'Nainakhan521@gmail.Com', '$2y$12$sPK7R7psbOZ9Y4kD.VBzX.qtxjQEmVhnBys.T7v67G1AA4at93Y6W', 'Naina  ', 'Abdullah', '', NULL, NULL, 'active', NULL, '2026-07-13 18:21:06', '2026-07-02 12:34:06', '2026-07-13 18:21:06', NULL),
(11, 3, 'Sahrish Nasim ', 'sahrish13062000@gmail.com', '$2y$12$7lJnPBlgbN4zvrpcqsZlIehg4UsqCrnoIhIwEgIVx3oQxHeNKdQ2m', 'Sahrish  ', 'Nasim', '', NULL, NULL, 'active', NULL, '2026-07-11 05:44:11', '2026-07-02 12:34:39', '2026-07-11 05:44:11', NULL),
(12, 3, 'Dr Ali Imran Khan', 'ali.imran.khan95@gmail.com', '$2y$12$1QwUvSHUeftvt0SWA2IqM.GXicU3vcpedfJ.gj8ksrmQjAQ84cRgO', 'Ali ', 'Imran Khan', '', NULL, NULL, 'active', NULL, '2026-07-13 13:42:30', '2026-07-02 12:35:18', '2026-07-13 13:42:30', NULL),
(13, 3, 'Dr. Hafiza Asma kanwal ', 'hafizaasmakanwal@gmail.com', '$2y$12$eParJw4Lor2Y0qMhyGlia.eGKeiospzA30zLu2I.WilZ0/PEAPMjK', 'Dr. Hafiza ', 'Asma kanwal ', '', NULL, NULL, 'active', NULL, '2026-07-09 07:57:50', '2026-07-02 12:35:47', '2026-07-09 07:57:50', NULL),
(14, 3, 'Sidrah abdul hafeez', 'sidrahabdulhafeez2000@gmail.com', '$2y$12$r5Q5VhDV.lXEt7kJOY0vT.M0Q.O2whJPn6fqKeikhgjD/8X1d4fVS', 'Sidrah ', 'abdul hafeez', '', NULL, NULL, 'active', NULL, NULL, '2026-07-02 12:36:18', '2026-07-06 08:14:08', NULL),
(15, 3, 'Palwasha Saleem', 'palwashasaleem1999@gmail.com', '$2y$12$m/NVn8yGYixR5TKrzVjGLeaPr7IiX8d5SDhhM/Gd9nkNWRZdwSftS', 'Palwasha ', 'Saleem', '', NULL, NULL, 'active', NULL, NULL, '2026-07-02 12:36:55', '2026-07-06 08:12:23', NULL),
(16, 3, 'Mahnoor Asif', 'mahnoorasif1100@gmail.com', '$2y$12$fmz0GaxxJ0CP5.LTKEGsSuuPVDVvFwRfSCcA8mMNtG/IS06PI/asu', 'Mahnoor ', 'Asif', '', NULL, NULL, 'active', NULL, '2026-07-13 16:24:58', '2026-07-02 12:37:23', '2026-07-13 16:24:58', NULL),
(17, 3, 'Sarah Aslam', 'sarah.aslam21@yahoo.com', '$2y$12$h.cLhPN5MYBFq77jigHt9u7dWF.w/S8EzP9MKnnm1KavDicdzPP5u', 'Sarah ', 'Aslam', '', NULL, NULL, 'active', NULL, '2026-07-06 19:34:48', '2026-07-02 12:37:59', '2026-07-06 19:34:48', NULL),
(18, 3, 'Sumayyah Siddiquii', 'Sumayyahsiddiquii@gmail.com', '$2y$12$UYE3LZMtDY7lDOtCPlkP..66YKjXgpCCFmPSnTVMDuWG2IH8nxbv2', 'Sumayyah ', 'Siddiquii', '', NULL, NULL, 'active', NULL, '2026-07-13 07:24:04', '2026-07-09 07:46:16', '2026-07-13 07:24:04', NULL),
(19, 3, 'test', 'teststudent@abc.com', '$2y$12$Y4sInCFAAhtPj2KIDKT4dum6x5SFx7G1LZzafd0n71EeQBrT1Ax16', 'test', 'student', '', NULL, NULL, 'active', NULL, '2026-07-11 05:41:44', '2026-07-09 08:25:27', '2026-07-11 05:41:44', NULL),
(20, 3, 'Mahnoor Jahangir', 'mahnoor58784@gmail.com', '$2y$12$LgVyA0DaR2lFjjlyF73bnu0GXFsIRttIodZ8FN2VAI6rPYp3HZbRe', 'Mahnoor ', 'Jahangir', '', NULL, NULL, 'active', NULL, '2026-07-13 13:12:22', '2026-07-09 10:04:41', '2026-07-13 13:12:22', NULL),
(21, 3, 'student12', 'student1@test.com', '$2y$12$4wwmtFYoKgxh46BX6sc8UOxuBfF..ZOnTq8.391CW2FrUQg7DybEe', 'student', 'student', '', NULL, NULL, 'active', NULL, '2026-07-13 09:28:28', '2026-07-09 13:03:57', '2026-07-13 09:28:28', NULL),
(22, 3, 'Rameen Iqbal shaikh', 'rameen.shaikh19@gmail.com', '$2y$12$ZAy7dbHq15CaIj/4QjotDuP.ReQSPizay7PDkgahpYogfdNHRrc1S', 'Rameen ', 'Iqbal shaikh', '', NULL, NULL, 'active', NULL, NULL, '2026-07-14 03:08:14', '2026-07-14 03:08:14', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_user` (`user_id`),
  ADD KEY `idx_activity_created` (`created_at`);

--
-- Indexes for table `ai_generation_jobs`
--
ALTER TABLE `ai_generation_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `idx_ai_jobs_status` (`status`),
  ADD KEY `idx_ai_jobs_lecture` (`lecture_id`);

--
-- Indexes for table `ai_lecture_content`
--
ALTER TABLE `ai_lecture_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ai_content_lecture` (`lecture_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `generated_by` (`generated_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_ai_content_status` (`status`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_announcements_course` (`course_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `idx_assignments_course` (`course_id`),
  ADD KEY `idx_assignments_due` (`due_date`);

--
-- Indexes for table `assignment_attachments`
--
ALTER TABLE `assignment_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assignment_attachments_assignment` (`assignment_id`);

--
-- Indexes for table `assignment_questions`
--
ALTER TABLE `assignment_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assignment_questions_assignment` (`assignment_id`);

--
-- Indexes for table `assignment_question_options`
--
ALTER TABLE `assignment_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assignment_options_question` (`question_id`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_assignment_submission` (`assignment_id`,`student_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `graded_by` (`graded_by`),
  ADD KEY `idx_submissions_status` (`status`);

--
-- Indexes for table `assignment_submission_files`
--
ALTER TABLE `assignment_submission_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_submission_files_submission` (`submission_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_attendance` (`session_id`,`student_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_attendance_status` (`status`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `idx_attendance_sessions_course` (`course_id`),
  ADD KEY `idx_attendance_sessions_date` (`session_date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_event` (`event`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_batches_course` (`course_id`);

--
-- Indexes for table `batch_students`
--
ALTER TABLE `batch_students`
  ADD PRIMARY KEY (`batch_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD UNIQUE KEY `uk_student_course_cert` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chapters_module` (`module_id`);

--
-- Indexes for table `class_reminder_log`
--
ALTER TABLE `class_reminder_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_class_reminder` (`schedule_id`,`occurrence_date`,`user_id`,`role`,`channel`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_cs_course` (`course_id`),
  ADD KEY `idx_cs_date` (`class_date`),
  ADD KEY `idx_cs_teacher` (`teacher_id`),
  ADD KEY `idx_cs_batch` (`batch_id`),
  ADD KEY `idx_cs_status` (`status`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `content_bookmarks`
--
ALTER TABLE `content_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_bookmark` (`student_id`,`content_type`,`content_id`),
  ADD KEY `idx_bookmark_student` (`student_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_courses_status` (`status`),
  ADD KEY `idx_courses_teacher` (`teacher_id`),
  ADD KEY `idx_courses_category` (`category_id`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `course_class_schedule`
--
ALTER TABLE `course_class_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_course` (`course_id`),
  ADD KEY `idx_schedule_day` (`day_of_week`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_enrollment` (`course_id`,`student_id`),
  ADD KEY `idx_enrollments_student` (`student_id`);

--
-- Indexes for table `course_teachers`
--
ALTER TABLE `course_teachers`
  ADD PRIMARY KEY (`course_id`,`teacher_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_daily_challenge_lecture` (`lecture_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `daily_challenge_sets`
--
ALTER TABLE `daily_challenge_sets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_daily_challenge_student_date` (`student_id`,`challenge_date`),
  ADD KEY `idx_dcs_date` (`challenge_date`);

--
-- Indexes for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_discussion_replies_thread` (`thread_id`);

--
-- Indexes for table `discussion_reply_likes`
--
ALTER TABLE `discussion_reply_likes`
  ADD PRIMARY KEY (`reply_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `discussion_reports`
--
ALTER TABLE `discussion_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `reply_id` (`reply_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `idx_reports_status` (`status`);

--
-- Indexes for table `discussion_threads`
--
ALTER TABLE `discussion_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_discussion_course` (`course_id`),
  ADD KEY `fk_thread_lecture` (`lecture_id`);

--
-- Indexes for table `flashcards`
--
ALTER TABLE `flashcards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_flashcards_lecture` (`lecture_id`),
  ADD KEY `idx_flashcards_course` (`course_id`),
  ADD KEY `idx_flashcards_status` (`status`);

--
-- Indexes for table `free_resources`
--
ALTER TABLE `free_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lectures`
--
ALTER TABLE `lectures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lectures_chapter` (`chapter_id`);

--
-- Indexes for table `lecture_progress`
--
ALTER TABLE `lecture_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_lecture_progress` (`student_id`,`lecture_id`),
  ADD KEY `lecture_id` (`lecture_id`);

--
-- Indexes for table `lecture_resources`
--
ALTER TABLE `lecture_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lecture_resources_lecture` (`lecture_id`),
  ADD KEY `fk_resource_uploader` (`uploaded_by`);

--
-- Indexes for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `idx_live_sessions_course` (`course_id`);

--
-- Indexes for table `mcqs`
--
ALTER TABLE `mcqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_mcqs_lecture` (`lecture_id`),
  ADD KEY `idx_mcqs_course` (`course_id`),
  ADD KEY `idx_mcqs_status` (`status`),
  ADD KEY `idx_mcqs_difficulty` (`difficulty`),
  ADD KEY `idx_mcqs_topic_status` (`topic`,`status`,`difficulty`);

--
-- Indexes for table `mcq_attempts`
--
ALTER TABLE `mcq_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `challenge_id` (`challenge_id`),
  ADD KEY `idx_mcq_attempts_student` (`student_id`),
  ADD KEY `idx_mcq_attempts_lecture` (`lecture_id`);

--
-- Indexes for table `mcq_attempt_answers`
--
ALTER TABLE `mcq_attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mcq_answers_attempt` (`attempt_id`),
  ADD KEY `idx_mcq_answers_mcq` (`mcq_id`),
  ADD KEY `idx_mcq_attempt_answers_mcq_correct` (`mcq_id`,`is_correct`);

--
-- Indexes for table `mentors`
--
ALTER TABLE `mentors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_modules_course` (`course_id`);

--
-- Indexes for table `note_highlights`
--
ALTER TABLE `note_highlights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecture_id` (`lecture_id`),
  ADD KEY `idx_highlight_student_lecture` (`student_id`,`lecture_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_read` (`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_email` (`email`),
  ADD KEY `idx_password_resets_token` (`token`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `idx_quizzes_course` (`course_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluated_by` (`evaluated_by`),
  ADD KEY `idx_quiz_attempts_student` (`student_id`),
  ADD KEY `idx_quiz_attempts_quiz` (`quiz_id`);

--
-- Indexes for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `idx_attempt_answers_attempt` (`attempt_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_questions_quiz` (`quiz_id`);

--
-- Indexes for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_options_question` (`question_id`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refresh_tokens_user` (`user_id`);

--
-- Indexes for table `revision_sessions`
--
ALTER TABLE `revision_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_revision_student` (`student_id`),
  ADD KEY `idx_revision_completed` (`completed_at`);

--
-- Indexes for table `revision_session_items`
--
ALTER TABLE `revision_session_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rsi_session` (`session_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `schedule_month_uploads`
--
ALTER TABLE `schedule_month_uploads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_schedule_month` (`course_id`,`month_year`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_smu_course` (`course_id`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `migration` (`migration`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `student_badges`
--
ALTER TABLE `student_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_badge` (`student_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `student_flashcard_progress`
--
ALTER TABLE `student_flashcard_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_flashcard_progress` (`student_id`,`flashcard_id`),
  ADD KEY `flashcard_id` (`flashcard_id`),
  ADD KEY `idx_fc_progress_student` (`student_id`);

--
-- Indexes for table `student_mistakes`
--
ALTER TABLE `student_mistakes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_student_mistake` (`student_id`,`mcq_id`),
  ADD KEY `mcq_id` (`mcq_id`),
  ADD KEY `idx_mistakes_student_status` (`student_id`,`status`),
  ADD KEY `idx_mistakes_topic` (`topic`);

--
-- Indexes for table `study_activity_log`
--
ALTER TABLE `study_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_activity` (`student_id`,`activity_date`,`activity_type`),
  ADD KEY `idx_activity_student_date` (`student_id`,`activity_date`);

--
-- Indexes for table `study_plans`
--
ALTER TABLE `study_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_study_plan_student` (`student_id`);

--
-- Indexes for table `study_plan_tasks`
--
ALTER TABLE `study_plan_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecture_id` (`lecture_id`),
  ADD KEY `idx_spt_plan_date` (`plan_id`,`task_date`),
  ADD KEY `idx_spt_status` (`status`);

--
-- Indexes for table `study_streaks`
--
ALTER TABLE `study_streaks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_study_streak_student` (`student_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=269;

--
-- AUTO_INCREMENT for table `ai_generation_jobs`
--
ALTER TABLE `ai_generation_jobs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `ai_lecture_content`
--
ALTER TABLE `ai_lecture_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `assignment_attachments`
--
ALTER TABLE `assignment_attachments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assignment_questions`
--
ALTER TABLE `assignment_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignment_question_options`
--
ALTER TABLE `assignment_question_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assignment_submission_files`
--
ALTER TABLE `assignment_submission_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `class_reminder_log`
--
ALTER TABLE `class_reminder_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_schedule`
--
ALTER TABLE `class_schedule`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_bookmarks`
--
ALTER TABLE `content_bookmarks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_class_schedule`
--
ALTER TABLE `course_class_schedule`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_challenge_sets`
--
ALTER TABLE `daily_challenge_sets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `discussion_reports`
--
ALTER TABLE `discussion_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discussion_threads`
--
ALTER TABLE `discussion_threads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `flashcards`
--
ALTER TABLE `flashcards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;

--
-- AUTO_INCREMENT for table `free_resources`
--
ALTER TABLE `free_resources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lectures`
--
ALTER TABLE `lectures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `lecture_progress`
--
ALTER TABLE `lecture_progress`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lecture_resources`
--
ALTER TABLE `lecture_resources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `live_sessions`
--
ALTER TABLE `live_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mcqs`
--
ALTER TABLE `mcqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `mcq_attempts`
--
ALTER TABLE `mcq_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mcq_attempt_answers`
--
ALTER TABLE `mcq_attempt_answers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `mentors`
--
ALTER TABLE `mentors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `note_highlights`
--
ALTER TABLE `note_highlights`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1033;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=929;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `revision_sessions`
--
ALTER TABLE `revision_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revision_session_items`
--
ALTER TABLE `revision_session_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedule_month_uploads`
--
ALTER TABLE `schedule_month_uploads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_badges`
--
ALTER TABLE `student_badges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_flashcard_progress`
--
ALTER TABLE `student_flashcard_progress`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `student_mistakes`
--
ALTER TABLE `student_mistakes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `study_activity_log`
--
ALTER TABLE `study_activity_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=345;

--
-- AUTO_INCREMENT for table `study_plans`
--
ALTER TABLE `study_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `study_plan_tasks`
--
ALTER TABLE `study_plan_tasks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `study_streaks`
--
ALTER TABLE `study_streaks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_generation_jobs`
--
ALTER TABLE `ai_generation_jobs`
  ADD CONSTRAINT `ai_generation_jobs_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_generation_jobs_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_generation_jobs_ibfk_3` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_lecture_content`
--
ALTER TABLE `ai_lecture_content`
  ADD CONSTRAINT `ai_lecture_content_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_lecture_content_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_lecture_content_ibfk_3` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_lecture_content_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_attachments`
--
ALTER TABLE `assignment_attachments`
  ADD CONSTRAINT `assignment_attachments_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_questions`
--
ALTER TABLE `assignment_questions`
  ADD CONSTRAINT `assignment_questions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_question_options`
--
ALTER TABLE `assignment_question_options`
  ADD CONSTRAINT `assignment_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `assignment_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assignment_submission_files`
--
ALTER TABLE `assignment_submission_files`
  ADD CONSTRAINT `assignment_submission_files_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `assignment_submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_ibfk_3` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendance_records_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `batches_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batches_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batch_students`
--
ALTER TABLE `batch_students`
  ADD CONSTRAINT `batch_students_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `certificate_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `certificates_ibfk_4` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_reminder_log`
--
ALTER TABLE `class_reminder_log`
  ADD CONSTRAINT `class_reminder_log_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `course_class_schedule` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_reminder_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD CONSTRAINT `class_schedule_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_schedule_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `class_schedule_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `class_schedule_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `content_bookmarks`
--
ALTER TABLE `content_bookmarks`
  ADD CONSTRAINT `content_bookmarks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_class_schedule`
--
ALTER TABLE `course_class_schedule`
  ADD CONSTRAINT `course_class_schedule_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_teachers`
--
ALTER TABLE `course_teachers`
  ADD CONSTRAINT `course_teachers_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_teachers_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD CONSTRAINT `daily_challenges_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_challenges_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_challenges_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_challenge_sets`
--
ALTER TABLE `daily_challenge_sets`
  ADD CONSTRAINT `daily_challenge_sets_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD CONSTRAINT `discussion_replies_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `discussion_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_replies_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_reply_likes`
--
ALTER TABLE `discussion_reply_likes`
  ADD CONSTRAINT `discussion_reply_likes_ibfk_1` FOREIGN KEY (`reply_id`) REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_reply_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_reports`
--
ALTER TABLE `discussion_reports`
  ADD CONSTRAINT `discussion_reports_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `discussion_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_reports_ibfk_2` FOREIGN KEY (`reply_id`) REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_reports_ibfk_3` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_threads`
--
ALTER TABLE `discussion_threads`
  ADD CONSTRAINT `discussion_threads_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_threads_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thread_lecture` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `flashcards`
--
ALTER TABLE `flashcards`
  ADD CONSTRAINT `flashcards_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `flashcards_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `flashcards_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lectures`
--
ALTER TABLE `lectures`
  ADD CONSTRAINT `lectures_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecture_progress`
--
ALTER TABLE `lecture_progress`
  ADD CONSTRAINT `lecture_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecture_progress_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecture_resources`
--
ALTER TABLE `lecture_resources`
  ADD CONSTRAINT `fk_resource_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lecture_resources_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD CONSTRAINT `live_sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_sessions_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mcqs`
--
ALTER TABLE `mcqs`
  ADD CONSTRAINT `mcqs_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mcqs_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mcqs_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mcq_attempts`
--
ALTER TABLE `mcq_attempts`
  ADD CONSTRAINT `mcq_attempts_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mcq_attempts_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mcq_attempts_ibfk_3` FOREIGN KEY (`challenge_id`) REFERENCES `daily_challenges` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mcq_attempt_answers`
--
ALTER TABLE `mcq_attempt_answers`
  ADD CONSTRAINT `mcq_attempt_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `mcq_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mcq_attempt_answers_ibfk_2` FOREIGN KEY (`mcq_id`) REFERENCES `mcqs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_highlights`
--
ALTER TABLE `note_highlights`
  ADD CONSTRAINT `note_highlights_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_highlights_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_3` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD CONSTRAINT `quiz_attempt_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempt_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD CONSTRAINT `quiz_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `refresh_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `revision_sessions`
--
ALTER TABLE `revision_sessions`
  ADD CONSTRAINT `revision_sessions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `revision_session_items`
--
ALTER TABLE `revision_session_items`
  ADD CONSTRAINT `revision_session_items_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `revision_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_month_uploads`
--
ALTER TABLE `schedule_month_uploads`
  ADD CONSTRAINT `schedule_month_uploads_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_month_uploads_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_badges`
--
ALTER TABLE `student_badges`
  ADD CONSTRAINT `student_badges_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_flashcard_progress`
--
ALTER TABLE `student_flashcard_progress`
  ADD CONSTRAINT `student_flashcard_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_flashcard_progress_ibfk_2` FOREIGN KEY (`flashcard_id`) REFERENCES `flashcards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_mistakes`
--
ALTER TABLE `student_mistakes`
  ADD CONSTRAINT `student_mistakes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_mistakes_ibfk_2` FOREIGN KEY (`mcq_id`) REFERENCES `mcqs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `study_activity_log`
--
ALTER TABLE `study_activity_log`
  ADD CONSTRAINT `study_activity_log_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `study_plans`
--
ALTER TABLE `study_plans`
  ADD CONSTRAINT `study_plans_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `study_plan_tasks`
--
ALTER TABLE `study_plan_tasks`
  ADD CONSTRAINT `study_plan_tasks_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `study_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `study_plan_tasks_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `study_streaks`
--
ALTER TABLE `study_streaks`
  ADD CONSTRAINT `study_streaks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
