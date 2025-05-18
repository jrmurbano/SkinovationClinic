-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 18, 2025 at 05:48 AM
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
-- Database: `beauty_clinic2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int NOT NULL,
  `admin_last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `admin_first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `admin_username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `admin_password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_last_name`, `admin_first_name`, `admin_username`, `admin_password`) VALUES
(1, 'Urbano', 'Jean', 'jrmurbano', '$2y$10$e0NR1z1F5J1u1J1u1J1u1u1J1u1J1u1J1u1J1u1J1u1J1u1J1u1u'),
(2, 'Urbano', 'Roshan', 'roshannaaaa', '$2y$10$wwnBJK186HIzzbuQSDAZTukPzBSxMGa6p1RJOplk0Md163EVVcABC'),
(3, 'reyes', 'khi', 'khi', '$2y$10$D7ty7Gevr7NipuHg4cCR9uKGqiirMdLOsOFs8tuCs1HU8jLsr9pEO');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `service_id` int NOT NULL,
  `attendant_id` int NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `service_id`, `attendant_id`, `appointment_date`, `appointment_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 8, 1, '2025-05-19', '14:00:00', 'cancelled', '2025-05-17 19:11:47', '2025-05-17 19:12:16'),
(2, 4, 15, 1, '2025-05-20', '15:00:00', 'confirmed', '2025-05-18 08:22:46', '2025-05-18 08:25:43');

-- --------------------------------------------------------

--
-- Table structure for table `attendants`
--

CREATE TABLE `attendants` (
  `attendant_id` int NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `shift_date` date NOT NULL,
  `shift_time` time NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendants`
--

INSERT INTO `attendants` (`attendant_id`, `last_name`, `first_name`, `shift_date`, `shift_time`, `updated_at`, `created_at`) VALUES
(1, 'Reyes', 'Kranchy', '2025-05-18', '10:00:00', '2025-05-18 03:17:52', '2025-05-13 23:35:42'),
(2, 'Ynares', 'Jillian', '2025-05-18', '10:00:00', '2025-05-18 08:33:11', '2025-05-18 08:16:58'),
(3, 'Pendon', 'Nicole', '2025-05-18', '10:00:00', '2025-05-18 11:57:08', '2025-05-18 11:57:08');

-- --------------------------------------------------------

--
-- Table structure for table `cancellation_requests`
--

CREATE TABLE `cancellation_requests` (
  `request_id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `appointment_type` enum('regular','package') COLLATE utf8mb4_general_ci NOT NULL,
  `patient_id` int NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `closed_dates`
--

CREATE TABLE `closed_dates` (
  `id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `closed_dates`
--

INSERT INTO `closed_dates` (`id`, `start_date`, `end_date`, `reason`, `created_at`) VALUES
(1, '2025-06-12', '2025-06-13', 'Independence Day', '2025-05-17 18:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history_log`
--

CREATE TABLE `history_log` (
  `id` int NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('Service','Product','Package') NOT NULL,
  `name` varchar(255) NOT NULL,
  `action` enum('Added','Edited','Deleted','Availed') NOT NULL,
  `performed_by` varchar(255) NOT NULL,
  `details` text,
  `related_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `history_log`
--

INSERT INTO `history_log` (`id`, `datetime`, `type`, `name`, `action`, `performed_by`, `details`, `related_id`) VALUES
(1, '2025-05-18 08:06:05', 'Product', 'Sunscreen', 'Edited', 'Admin', 'Product updated.', NULL),
(2, '2025-05-18 11:14:38', 'Service', 'Casmara', 'Edited', 'Admin', 'Service updated.', NULL),
(3, '2025-05-18 11:22:49', 'Product', 'Derm Options Kojic Soap', 'Edited', 'Admin', 'Product updated.', NULL),
(4, '2025-05-18 11:24:38', 'Product', 'Lightening Cream', 'Edited', 'Admin', 'Product updated.', NULL),
(5, '2025-05-18 11:25:04', 'Product', 'Derm Options Pore Minimizer (Toner)', 'Edited', 'Admin', 'Product updated.', NULL),
(6, '2025-05-18 11:25:22', 'Product', 'Sunscreen Cream', 'Edited', 'Admin', 'Product updated.', NULL),
(7, '2025-05-18 11:26:11', 'Product', 'Derm Options Yellow Soap (Anti-Acne)', 'Edited', 'Admin', 'Product updated.', NULL),
(8, '2025-05-18 11:31:13', 'Service', 'Diamond Peel', 'Edited', 'Admin', 'Service updated.', NULL),
(9, '2025-05-18 11:36:32', 'Service', 'Chest/Back', 'Edited', 'Admin', 'Service updated.', NULL),
(10, '2025-05-18 11:36:46', 'Service', 'Neck', 'Edited', 'Admin', 'Service updated.', NULL),
(11, '2025-05-18 11:37:03', 'Service', 'Charcoal', 'Edited', 'Admin', 'Service updated.', NULL),
(12, '2025-05-18 11:37:16', 'Service', 'Collagen', 'Edited', 'Admin', 'Service updated.', NULL),
(13, '2025-05-18 11:37:32', 'Service', 'Snow White', 'Edited', 'Admin', 'Service updated.', NULL),
(14, '2025-05-18 11:37:43', 'Service', 'Casmara', 'Edited', 'Admin', 'Service updated.', NULL),
(15, '2025-05-18 11:38:07', 'Service', 'Radio Frequency', 'Edited', 'Admin', 'Service updated.', NULL),
(16, '2025-05-18 11:38:17', 'Service', 'Geneo Infusion', 'Edited', 'Admin', 'Service updated.', NULL),
(17, '2025-05-18 11:38:33', 'Service', 'Oxygeneo', 'Edited', 'Admin', 'Service updated.', NULL),
(18, '2025-05-18 11:39:08', 'Service', 'Skin Rejuvenation', 'Edited', 'Admin', 'Service updated.', NULL),
(19, '2025-05-18 11:39:27', 'Service', 'Galvanic Therapy', 'Edited', 'Admin', 'Service updated.', NULL),
(20, '2025-05-18 11:39:48', 'Service', 'Platelet-Rich Plasma (PRP) therapy', 'Edited', 'Admin', 'Service updated.', NULL),
(21, '2025-05-18 11:40:01', 'Service', 'Carbon Doll Laser', 'Edited', 'Admin', 'Service updated.', NULL),
(22, '2025-05-18 11:40:13', 'Service', 'Pico Glow', 'Edited', 'Admin', 'Service updated.', NULL),
(23, '2025-05-18 11:40:39', 'Service', 'Tattoo Removal', 'Edited', 'Admin', 'Service updated.', NULL),
(24, '2025-05-18 11:40:51', 'Service', 'Vitamin C Infusion', 'Edited', 'Admin', 'Service updated.', NULL),
(25, '2025-05-18 11:41:03', 'Service', 'Underarm Whitening', 'Edited', 'Admin', 'Service updated.', NULL),
(26, '2025-05-18 11:41:13', 'Service', 'Back Whitening', 'Edited', 'Admin', 'Service updated.', NULL),
(27, '2025-05-18 11:41:23', 'Service', 'Chest Whitening', 'Edited', 'Admin', 'Service updated.', NULL),
(28, '2025-05-18 11:41:32', 'Service', 'Butt Whitening', 'Edited', 'Admin', 'Service updated.', NULL),
(29, '2025-05-18 11:41:46', 'Service', 'Neck Whitening', 'Edited', 'Admin', 'Service updated.', NULL),
(30, '2025-05-18 11:43:40', 'Service', 'Pimple Injection', 'Edited', 'Admin', 'Service updated.', NULL),
(31, '2025-05-18 11:43:47', 'Service', 'Anti-Acne Treatment', 'Edited', 'Admin', 'Service updated.', NULL),
(32, '2025-05-18 11:44:01', 'Service', 'Face Cavitation', 'Edited', 'Admin', 'Service updated.', NULL),
(33, '2025-05-18 11:44:11', 'Service', 'Waist Cavitation', 'Edited', 'Admin', 'Service updated.', NULL),
(34, '2025-05-18 11:44:22', 'Service', 'Thighs Cavitation', 'Edited', 'Admin', 'Service updated.', NULL),
(35, '2025-05-18 11:44:32', 'Service', 'Arms Cavitation', 'Edited', 'Admin', 'Service updated.', NULL),
(36, '2025-05-18 11:45:22', 'Service', 'IPL Face', 'Edited', 'Admin', 'Service updated.', NULL),
(37, '2025-05-18 11:45:38', 'Service', 'IPL Arm', 'Edited', 'Admin', 'Service updated.', NULL),
(38, '2025-05-18 11:46:22', 'Service', 'IPL Back', 'Edited', 'Admin', 'Service updated.', NULL),
(39, '2025-05-18 11:46:48', 'Service', 'IPL Upperlip', 'Edited', 'Admin', 'Service updated.', NULL),
(40, '2025-05-18 11:47:16', 'Service', 'IPL Underarms', 'Edited', 'Admin', 'Service updated.', NULL),
(41, '2025-05-18 11:47:43', 'Service', 'IPL Bikini', 'Edited', 'Admin', 'Service updated.', NULL),
(42, '2025-05-18 11:49:19', 'Service', 'IPL Brazilian', 'Edited', 'Admin', 'Service updated.', NULL),
(43, '2025-05-18 11:49:58', 'Service', 'IPL Legs', 'Edited', 'Admin', 'Service updated.', NULL),
(44, '2025-05-18 11:50:36', 'Service', 'IPL Chest', 'Edited', 'Admin', 'Service updated.', NULL),
(45, '2025-05-18 11:50:48', 'Service', 'Warts Removal', 'Edited', 'Admin', 'Service updated.', NULL),
(46, '2025-05-18 11:51:03', 'Service', 'Korean Lash Lift with Tint', 'Edited', 'Admin', 'Service updated.', NULL),
(47, '2025-05-18 11:51:12', 'Service', 'Korean Lash Lift without Tint', 'Edited', 'Admin', 'Service updated.', NULL),
(48, '2025-05-18 13:04:38', 'Service', 'Anti-Acne Treatment', 'Edited', 'Admin', 'Service updated.', NULL),
(49, '2025-05-18 13:05:27', 'Service', 'Arms Cavitation', 'Edited', 'Admin', 'Service updated.', NULL),
(50, '2025-05-18 13:18:26', 'Service', 'Galvanic Therapy', 'Edited', 'Admin', 'Service updated.', NULL),
(51, '2025-05-18 13:19:59', 'Service', 'Back Whitening', 'Edited', 'Admin', 'Service updated.', NULL),
(52, '2025-05-18 13:23:23', 'Service', 'Carbon Doll Laser', 'Edited', 'Admin', 'Service updated.', NULL),
(53, '2025-05-18 13:24:05', 'Service', 'Charcoal', 'Edited', 'Admin', 'Service updated.', NULL),
(54, '2025-05-18 13:26:30', 'Service', 'Collagen', 'Edited', 'Admin', 'Service updated.', NULL),
(55, '2025-05-18 13:27:36', 'Service', 'Warts Removal', 'Edited', 'Admin', 'Service updated.', NULL),
(56, '2025-05-18 13:28:25', 'Service', 'Geneo Infusion', 'Edited', 'Admin', 'Service updated.', NULL),
(57, '2025-05-18 13:29:07', 'Service', 'Oxygeneo', 'Edited', 'Admin', 'Service updated.', NULL),
(58, '2025-05-18 13:30:25', 'Service', 'Platelet-Rich Plasma (PRP) therapy', 'Edited', 'Admin', 'Service updated.', NULL),
(59, '2025-05-18 13:31:18', 'Service', 'Face Cavitation', 'Edited', 'Admin', 'Service updated.', NULL),
(60, '2025-05-18 13:32:12', 'Service', 'Chest/Back', 'Edited', 'Admin', 'Service updated.', NULL),
(61, '2025-05-18 13:34:55', 'Service', 'IPL Underarms', 'Edited', 'Admin', 'Service updated.', NULL),
(62, '2025-05-18 13:35:26', 'Service', 'IPL Back', 'Edited', 'Admin', 'Service updated.', NULL),
(63, '2025-05-18 13:36:03', 'Service', 'IPL Upperlip', 'Edited', 'Admin', 'Service updated.', NULL),
(64, '2025-05-18 13:37:02', 'Service', 'Waist Cavitation', 'Edited', 'Admin', 'Service updated.', NULL),
(65, '2025-05-18 13:39:21', 'Service', 'IPL Brazilian', 'Edited', 'Admin', 'Service updated.', NULL),
(66, '2025-05-18 13:40:36', 'Service', 'IPL Bikini', 'Edited', 'Admin', 'Service updated.', NULL),
(67, '2025-05-18 13:41:05', 'Service', 'Vitamin C Infusion', 'Edited', 'Admin', 'Service updated.', NULL),
(68, '2025-05-18 13:44:08', 'Service', 'Radio Frequency', 'Edited', 'Admin', 'Service updated.', NULL),
(69, '2025-05-18 13:45:02', 'Service', 'Skin Rejuvenation', 'Edited', 'Admin', 'Service updated.', NULL),
(70, '2025-05-18 13:45:41', 'Service', 'Primary Facial (Face)', 'Edited', 'Admin', 'Service updated.', NULL),
(71, '2025-05-18 13:46:30', 'Service', 'IPL Legs', 'Edited', 'Admin', 'Service updated.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int NOT NULL,
  `type` enum('appointment','confirmation','cancellation','reschedule') COLLATE utf8mb4_general_ci NOT NULL,
  `appointment_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE `owner` (
  `owner_id` int NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `owner_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`owner_id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'khi', '$2y$10$FaJhIFXf.x.qlP3zBaLQvOnTyuyBOeuHUAIB28mX77En1a8E4p2KS', 'ksreyes.chmsu@gmail.com', '2025-05-15 13:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `package_id` int NOT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `sessions` int NOT NULL,
  `duration_days` int NOT NULL COMMENT 'Duration in days',
  `grace_period_days` int NOT NULL COMMENT 'Grace period in days',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`package_id`, `package_name`, `description`, `price`, `sessions`, `duration_days`, `grace_period_days`, `created_at`, `updated_at`) VALUES
(1, '3 + 1 Underarm Whitening', NULL, 1847.00, 4, 90, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(2, '3 + 1 Back Whitening', NULL, 1847.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(3, '3 + 1 Bikini Whitening', NULL, 1847.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(4, '3 + 1 Butt Whitening', NULL, 1847.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(5, '3 + 1 Carbon Doll Laser', NULL, 3197.00, 4, 120, 240, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(6, '3 + 1 Casmara Facial', NULL, 3200.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(7, '3 + 1 Face Cavitation', NULL, 1397.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(8, '3 + 1 Waist Cavitation', NULL, 1397.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(9, '3 + 1 Thighs Cavitation', NULL, 1397.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(11, '3 + 1 Charcoal', NULL, 2297.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(12, '3 + 1 Chest Infusion', NULL, 1847.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(13, '3 + 1 Chest/Back Facial', NULL, 1997.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(14, '3 + 1 Collagen', NULL, 2297.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(15, '3 + 1 Diamond Peel', NULL, 1697.00, 4, 90, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(16, '3 + 1 Galvanic Therapy', NULL, 2597.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(17, '3 + 1 Geneo Infusion', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(18, '3 + 1 IPL Arms', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(19, '3 + 1 IPL Back', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(20, '3 + 1 IPL Bikini', NULL, 2297.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(21, '3 + 1 IPL Brazilian', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(22, '3 + 1 IPL Chest', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(23, '3 + 1 IPL Face', NULL, 1697.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(24, '3 + 1 IPL Legs', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(25, '3 + 1 IPL Neck', NULL, 1697.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(26, '3 + 1 IPL Thighs', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(27, '3 + 1 IPL Thighs (Front)', NULL, 1697.00, 4, 90, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(28, '3 + 1 IPL Underarms', NULL, 1697.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(29, '3 + 1 IPL Upperlip', NULL, 1097.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(30, '3 + 1 Pico Glow (Melasma/Freckles)', NULL, 3197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(31, '3 + 1 Neck Cleaning', NULL, 1397.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(32, '3 + 1 Neck Infusion', NULL, 1247.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(33, '3 + 1 Oxygeneo', NULL, 6197.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(34, '3 + 1 Pico Laser (Underarm)', NULL, 3197.00, 4, 120, 240, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
(35, '3 + 1 Primary Facial', NULL, 1697.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `package_appointments`
--

CREATE TABLE `package_appointments` (
  `package_appointment_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `attendant_id` int NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_appointments`
--

INSERT INTO `package_appointments` (`package_appointment_id`, `booking_id`, `attendant_id`, `appointment_date`, `appointment_time`, `status`, `created_at`, `updated_at`) VALUES
(3, 5, 1, '2025-05-12', '10:00:00', 'pending', '2025-05-13 23:37:13', '2025-05-13 23:37:13'),
(4, 6, 1, '2025-05-21', '11:00:00', 'cancelled', '2025-05-13 23:41:01', '2025-05-13 23:42:23'),
(6, 8, 1, '2025-05-14', '09:00:00', 'pending', '2025-05-15 19:18:52', '2025-05-15 19:18:52'),
(7, 9, 1, '2025-05-19', '10:00:00', 'cancelled', '2025-05-15 20:13:39', '2025-05-15 20:22:17'),
(8, 10, 1, '2025-05-15', '10:00:00', 'cancelled', '2025-05-15 20:33:29', '2025-05-15 22:03:47'),
(9, 11, 1, '2025-05-21', '12:00:00', 'cancelled', '2025-05-15 23:25:48', '2025-05-15 23:25:55');

-- --------------------------------------------------------

--
-- Table structure for table `package_bookings`
--

CREATE TABLE `package_bookings` (
  `booking_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `package_id` int NOT NULL,
  `sessions_remaining` int NOT NULL,
  `valid_until` date NOT NULL,
  `grace_period_until` date NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_bookings`
--

INSERT INTO `package_bookings` (`booking_id`, `patient_id`, `package_id`, `sessions_remaining`, `valid_until`, `grace_period_until`, `created_at`, `updated_at`) VALUES
(5, 2, 1, 4, '2025-08-11', '2025-11-09', '2025-05-13 23:37:13', '2025-05-13 23:37:13'),
(6, 2, 1, 5, '2025-08-11', '2025-11-09', '2025-05-13 23:41:01', '2025-05-13 23:42:23'),
(8, 3, 2, 4, '2025-09-12', '2025-11-11', '2025-05-15 19:18:52', '2025-05-15 19:18:52'),
(9, 3, 7, 5, '2025-09-12', '2025-11-11', '2025-05-15 20:13:39', '2025-05-15 20:22:17'),
(10, 3, 1, 5, '2025-08-13', '2025-11-11', '2025-05-15 20:33:29', '2025-05-15 22:03:48'),
(11, 3, 7, 5, '2025-09-12', '2025-11-11', '2025-05-15 23:25:48', '2025-05-15 23:25:55');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `last_name`, `first_name`, `middle_name`, `username`, `password`, `phone`, `created_at`, `updated_at`, `archived`) VALUES
(2, 'Diaz', 'Emil Joaquin', 'Hinolan', 'emil123', '$2y$10$h8bfGeF7ziBPJaD7TlKQ0e3Q66r5Y/YGPwXfCe7Cs.FRZvwmqzVPu', '09695282766', '2025-05-13 22:22:40', '2025-05-13 22:22:40', 0),
(3, 'Baylosis', 'Kurt', 'Iris', 'kurt', '$2y$10$vvWHCqxO3mh7VDW7G0LNm.HkO5we/28emq/.mJPFG1iGMt6Se1rIS', '12312312123', '2025-05-15 19:17:40', '2025-05-15 19:17:40', 0),
(4, 'Rodrigo', 'Olivia', 'Isabel', 'oliviarodrigo', '$2y$10$.hSmvd1s62e3uF6zfOlqROBmIXDt2Ab481jY5seLSgzxPB9LxITo6', '09011282021', '2025-05-18 08:19:19', '2025-05-18 12:29:04', 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `product_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `stock`, `created_at`, `updated_at`, `product_image`) VALUES
(1, 'Derm Options Yellow Soap (Anti-Acne)', 'Anti-Acne Soap', 140.00, 100, '2025-05-13 14:39:04', '2025-05-18 11:26:11', 'assets/img/product_68295353a24516.52836979.jpg'),
(2, 'Derm Options Pore Minimizer (Toner)', 'AB Astringent', 380.00, 100, '2025-05-13 14:39:04', '2025-05-18 11:25:04', 'assets/img/product_6829531078cf74.08364219.jpg'),
(3, 'Sunscreen Cream', 'Apply to help skin fight UV rays.', 225.00, 100, '2025-05-13 14:39:04', '2025-05-18 11:25:22', 'assets/img/product_682953229ab053.06402532.jpg'),
(4, 'Derm Options Kojic Soap', 'Soap to whiten skin effectively', 180.00, 100, '2025-05-13 14:39:04', '2025-05-18 11:22:49', 'assets/img/product_68295289ea2f16.96311967.jpg'),
(5, 'Lightening Cream', 'For night use.', 230.00, 100, '2025-05-13 14:39:04', '2025-05-18 11:24:38', 'assets/img/product_682952f691b149.18795681.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `type` enum('reschedule','cancellation') COLLATE utf8mb4_general_ci NOT NULL,
  `requested_date` date DEFAULT NULL,
  `requested_time` time DEFAULT NULL,
  `status` enum('pending','approved','denied') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_response_at` timestamp NULL DEFAULT NULL,
  `admin_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int NOT NULL,
  `service_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `duration` int NOT NULL COMMENT 'Duration in minutes',
  `category_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `price`, `duration`, `category_id`, `created_at`, `updated_at`, `image`) VALUES
(1, 'Primary Facial (Face)', '', 499.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 13:45:41', 'assets/img/service_682974058dd1e3.98457666.jpg'),
(2, 'Chest/Back', 'A facial treatment specifically for the chest or back area.', 649.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 13:32:12', 'assets/img/service_682970dc9d4552.80214440.jpg'),
(3, 'Neck', 'A facial treatment targeting the neck area. ', 449.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 11:36:46', NULL),
(4, 'Charcoal', 'A facial that includes a Diamond Peel and a Charcoal mask for deep cleansing and purification. ', 699.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 13:24:05', 'assets/img/service_68296ef5dc8551.89673092.jpg'),
(5, 'Collagen', 'This facial combines a Diamond Peel with a Collagen mask to help improve skin elasticity and firmness. ', 699.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 13:26:30', 'assets/img/service_68296f86202a35.75352874.jpg'),
(6, 'Snow White', 'Features a Diamond Peel along with a Vitamin C serum and mask to brighten and even out skin tone. ', 799.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 11:37:32', NULL),
(7, 'Casmara', 'A premium facial that includes a Diamond Peel and a specialized Casmara mask for targeted skin concerns. ', 1000.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 11:37:43', 'assets/img/service_6829509e8684b3.32845245.jpg'),
(8, 'Diamond Peel', 'An exfoliating treatment to remove dead skin cells and reveal smoother skin. ', 499.00, 60, 1, '2025-05-13 06:03:46', '2025-05-18 11:31:13', 'assets/img/service_68295481ef5d45.56517721.jpg'),
(9, 'Radio Frequency', 'A treatment that uses radiofrequency energy to tighten skin and reduce the appearance of wrinkles, combined with a facial. ', 999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-18 13:44:08', 'assets/img/service_682973a8541c62.46097733.png'),
(10, 'Geneo Infusion', 'A treatment that infuses the skin with active ingredients for rejuvenation and brightening effects. ', 999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-18 13:28:25', 'assets/img/service_68296ff8f3f665.74137706.png'),
(11, 'Oxygeneo', 'A comprehensive treatment combining a facial, radiofrequency, and infusion for enhanced oxygenation, skin tightening, and nourishment.', 1999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-18 13:29:07', 'assets/img/service_682970238ee6a6.30687018.jpg'),
(12, 'Skin Rejuvenation', 'This treatment combines a facial, serum application, and Photodynamic therapy (PDT) light therapy to promote skin renewal. ', 999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-18 13:45:02', 'assets/img/service_682973de853147.02259351.webp'),
(13, 'Galvanic Therapy', 'Uses gentle electrical currents to enhance the absorption of serums into the skin, paired with a facial.', 799.00, 60, 2, '2025-05-13 06:03:46', '2025-05-18 13:18:26', 'assets/img/service_68296da2147359.39605132.jpg'),
(14, 'Platelet-Rich Plasma (PRP) therapy', 'Platelet-Rich Plasma (PRP) therapy combined with a facial and PDT light to stimulate collagen production and skin regeneration.', 1899.00, 60, 2, '2025-05-13 06:03:46', '2025-05-18 13:30:25', 'assets/img/service_68297071cbe637.07989830.jpg'),
(15, 'Carbon Doll Laser', 'A laser treatment that uses a carbon mask to exfoliate, minimize pores, and improve skin texture. ', 999.00, 60, 3, '2025-05-13 06:03:46', '2025-05-18 13:23:23', 'assets/img/service_68296ecb6e42c3.25325927.jpg'),
(16, 'Pico Glow', 'A laser treatment designed to target and reduce the appearance of melasma and freckles. ', 999.00, 60, 3, '2025-05-13 06:03:46', '2025-05-18 11:40:13', NULL),
(17, 'Tattoo Removal', 'Laser treatment to remove unwanted tattoos (price depends on size).', 999.00, 60, 3, '2025-05-13 06:03:46', '2025-05-18 11:40:39', NULL),
(18, 'Vitamin C Infusion', 'A treatment that infuses Vitamin C into the facial skin for a brighter complexion. ', 349.00, 15, 4, '2025-05-13 06:03:46', '2025-05-18 13:41:05', 'assets/img/service_682972f1b47736.15464392.webp'),
(19, 'Underarm Whitening', 'A treatment to lighten the skin in the underarm area. ', 549.00, 15, 4, '2025-05-13 06:03:46', '2025-05-18 11:41:03', NULL),
(20, 'Back Whitening', 'A treatment focused on lightening the skin on the back. ', 649.00, 15, 4, '2025-05-13 06:03:46', '2025-05-18 13:19:59', 'assets/img/service_68296dffed12e2.89401796.jpg'),
(21, 'Chest Whitening', 'A treatment to lighten the skin on the chest area. ', 549.00, 15, 4, '2025-05-13 06:03:46', '2025-05-18 11:41:23', NULL),
(22, 'Butt Whitening', 'A treatment aimed at lightening the skin on the buttocks. ', 549.00, 15, 4, '2025-05-13 06:03:46', '2025-05-18 11:41:32', NULL),
(23, 'Neck Whitening', 'A treatment to lighten the skin on the neck. ', 349.00, 15, 4, '2025-05-13 06:03:46', '2025-05-18 11:41:46', NULL),
(24, 'Pimple Injection', 'A direct injection to reduce inflammation and size of individual pimples. ', 99.00, 60, 5, '2025-05-13 06:03:46', '2025-05-18 11:43:40', NULL),
(25, 'Anti-Acne Treatment', 'A comprehensive treatment involving a facial, anti-acne serum, and light therapy to combat acne. ', 1599.00, 60, 5, '2025-05-13 06:03:46', '2025-05-18 13:04:38', 'assets/img/service_68296a663424c6.44351006.webp'),
(26, 'Face Cavitation', 'Cavitation treatment to help slim and contour the face. ', 899.00, 60, 6, '2025-05-13 06:03:46', '2025-05-18 13:31:18', 'assets/img/service_682970a638d196.48295171.jpg'),
(27, 'Waist Cavitation', 'Cavitation treatment targeting fat reduction and contouring of the waist area. ', 999.00, 60, 6, '2025-05-13 06:03:46', '2025-05-18 13:37:02', 'assets/img/service_682971fecdeb09.17194132.jpg'),
(28, 'Thighs Cavitation', 'Cavitation treatment aimed at slimming and contouring the thighs. ', 999.00, 60, 6, '2025-05-13 06:03:46', '2025-05-18 11:44:22', NULL),
(29, 'Arms Cavitation', 'Cavitation treatment to help reduce fat and contour the arms. ', 999.00, 60, 6, '2025-05-13 06:03:46', '2025-05-18 13:05:27', 'assets/img/service_68296a9719a224.45641582.webp'),
(30, 'IPL Face', 'Intense Pulsed Light (IPL) treatment for hair removal on the face. ', 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 11:45:22', NULL),
(31, 'IPL Neck', NULL, 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46', NULL),
(32, 'IPL Arm', 'Intense Pulsed Light (IPL) hair removal for the arms. ', 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 11:45:38', NULL),
(33, 'IPL Brazilian', 'Intense Pulsed Light (IPL) Brazilian hair removal on the bikini area. ', 699.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 13:39:21', 'assets/img/service_682972899df284.28425790.jpg'),
(34, 'IPL Legs', 'Intense Pulsed Light (IPL) hair removal for either the lower or upper legs. ', 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 13:46:30', 'assets/img/service_68297436d4b9c0.74762067.jpg'),
(35, 'IPL Upperlip', 'Intense Pulsed Light (IPL) removal for the upper lip area. ', 299.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 13:36:03', 'assets/img/service_682971c3bf7dc4.13615735.webp'),
(36, 'IPL Underarms', 'Intense Pulsed Light (IPL) hair removal for the underarms.', 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 13:34:55', 'assets/img/service_6829717fc05b75.51679548.jpg'),
(37, 'IPL Bikini', 'Intense Pulsed Light (IPL) hair removal for the bikini line. ', 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 13:40:36', 'assets/img/service_682972d4249411.25368167.png'),
(38, 'IPL Chest', 'Intense Pulsed Light (IPL) hair removal for the chest area. ', 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 11:50:36', NULL),
(39, 'IPL Back', 'Intense Pulsed Light (IPL) hair removal for either the lower or upper back.', 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-18 13:35:26', 'assets/img/service_6829719e9ba5d2.02481982.webp'),
(40, 'Warts Removal', 'Treatment for removing warts (minimum price per area applies). ', 1500.00, 60, 8, '2025-05-13 06:03:46', '2025-05-18 13:27:36', 'assets/img/service_68296fc81aad77.47065882.jpg'),
(41, 'Korean Lash Lift with Tint', 'A treatment to curl and lift lashes, with an option for tinting.', 699.00, 30, 8, '2025-05-13 06:03:46', '2025-05-18 11:51:03', NULL),
(42, 'Korean Lash Lift without Tint', 'A treatment to curl and lift lashes.', 499.00, 30, 8, '2025-05-13 06:03:46', '2025-05-18 11:51:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `category_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`category_id`, `name`) VALUES
(1, 'Facials'),
(2, 'Anti-Aging & Face Lift'),
(3, 'Pico Laser'),
(4, 'Lightening Treatments'),
(5, 'Pimple Treatments'),
(6, 'Body Slimming with Cavitation'),
(7, 'Intense Pulsed Light (IPL) Hair Removal'),
(8, 'Other Services');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hire_date` date NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_hours`
--

CREATE TABLE `store_hours` (
  `id` int NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') COLLATE utf8mb4_general_ci NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `is_closed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_hours`
--

INSERT INTO `store_hours` (`id`, `day_of_week`, `open_time`, `close_time`, `is_closed`, `created_at`, `updated_at`) VALUES
(8, 'Monday', '09:00:00', '17:00:00', 0, '2025-05-15 12:09:33', '2025-05-15 12:09:33'),
(9, 'Tuesday', '09:00:00', '17:00:00', 0, '2025-05-15 12:09:33', '2025-05-15 12:09:33'),
(10, 'Wednesday', '09:00:00', '17:00:00', 0, '2025-05-15 12:09:33', '2025-05-15 12:09:33'),
(11, 'Thursday', '09:00:00', '17:00:00', 0, '2025-05-15 12:09:33', '2025-05-15 12:09:33'),
(12, 'Friday', '09:00:00', '17:00:00', 0, '2025-05-15 12:09:33', '2025-05-15 12:09:33'),
(13, 'Saturday', '09:00:00', '17:00:00', 0, '2025-05-15 12:09:33', '2025-05-15 12:09:33'),
(14, 'Sunday', '09:00:00', '17:00:00', 0, '2025-05-15 12:09:33', '2025-05-15 12:09:33'),
(15, 'Monday', '09:00:00', '18:00:00', 0, '2025-05-15 12:31:22', '2025-05-15 12:31:22'),
(16, 'Tuesday', '09:00:00', '18:00:00', 0, '2025-05-15 12:31:22', '2025-05-15 12:31:22'),
(17, 'Wednesday', '09:00:00', '18:00:00', 0, '2025-05-15 12:31:22', '2025-05-15 12:31:22'),
(18, 'Thursday', '09:00:00', '18:00:00', 0, '2025-05-15 12:31:22', '2025-05-15 12:31:22'),
(19, 'Friday', '09:00:00', '18:00:00', 0, '2025-05-15 12:31:22', '2025-05-15 12:31:22'),
(20, 'Saturday', '09:00:00', '17:00:00', 0, '2025-05-15 12:31:22', '2025-05-15 12:31:22'),
(21, 'Sunday', '09:00:00', '17:00:00', 1, '2025-05-15 12:31:22', '2025-05-15 12:31:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `appointments_patient_id_fk` (`patient_id`),
  ADD KEY `appointments_service_id_fk` (`service_id`),
  ADD KEY `appointments_attendant_id_fk` (`attendant_id`);

--
-- Indexes for table `attendants`
--
ALTER TABLE `attendants`
  ADD PRIMARY KEY (`attendant_id`);

--
-- Indexes for table `cancellation_requests`
--
ALTER TABLE `cancellation_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `closed_dates`
--
ALTER TABLE `closed_dates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `feedback_appointment_id_fk` (`appointment_id`),
  ADD KEY `feedback_patient_id_fk` (`patient_id`);

--
-- Indexes for table `history_log`
--
ALTER TABLE `history_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `owner`
--
ALTER TABLE `owner`
  ADD PRIMARY KEY (`owner_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`owner_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `package_appointments`
--
ALTER TABLE `package_appointments`
  ADD PRIMARY KEY (`package_appointment_id`),
  ADD KEY `package_appointments_booking_id_fk` (`booking_id`),
  ADD KEY `package_appointments_attendant_id_fk` (`attendant_id`);

--
-- Indexes for table `package_bookings`
--
ALTER TABLE `package_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `package_bookings_patient_id_fk` (`patient_id`),
  ADD KEY `package_bookings_package_id_fk` (`package_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `services_category_id_fk` (`category_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_staff_email` (`email`),
  ADD KEY `idx_staff_status` (`status`);

--
-- Indexes for table `store_hours`
--
ALTER TABLE `store_hours`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendants`
--
ALTER TABLE `attendants`
  MODIFY `attendant_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cancellation_requests`
--
ALTER TABLE `cancellation_requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `closed_dates`
--
ALTER TABLE `closed_dates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history_log`
--
ALTER TABLE `history_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owner`
--
ALTER TABLE `owner`
  MODIFY `owner_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `owner_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `package_appointments`
--
ALTER TABLE `package_appointments`
  MODIFY `package_appointment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `package_bookings`
--
ALTER TABLE `package_bookings`
  MODIFY `booking_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_hours`
--
ALTER TABLE `store_hours`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_attendant_id_fk` FOREIGN KEY (`attendant_id`) REFERENCES `attendants` (`attendant_id`),
  ADD CONSTRAINT `appointments_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_service_id_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `cancellation_requests`
--
ALTER TABLE `cancellation_requests`
  ADD CONSTRAINT `cancellation_requests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_appointment_id_fk` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `feedback_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL;

--
-- Constraints for table `package_appointments`
--
ALTER TABLE `package_appointments`
  ADD CONSTRAINT `package_appointments_attendant_id_fk` FOREIGN KEY (`attendant_id`) REFERENCES `attendants` (`attendant_id`),
  ADD CONSTRAINT `package_appointments_booking_id_fk` FOREIGN KEY (`booking_id`) REFERENCES `package_bookings` (`booking_id`);

--
-- Constraints for table `package_bookings`
--
ALTER TABLE `package_bookings`
  ADD CONSTRAINT `package_bookings_package_id_fk` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`),
  ADD CONSTRAINT `package_bookings_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
