-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 07:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!4-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6; SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `beauty_clinic2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'password123');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target_type` enum('user','appointment','service','attendant') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `attendant_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `service_id`, `attendant_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(23, 5, 30, 1, '2025-05-10', '00:00:00', 'completed', NULL, '2025-05-08 23:28:36', '2025-05-08 23:32:31'),
(24, 5, 31, 3, '2025-05-10', '00:00:00', 'cancelled', NULL, '2025-05-08 23:35:47', '2025-05-08 23:35:57'),
(25, 5, 29, 2, '2025-05-17', '00:00:00', 'completed', NULL, '2025-05-08 23:38:20', '2025-05-08 23:38:49'),
(26, 10, 18, 1, '2025-05-12', '00:00:00', 'cancelled', NULL, '2025-05-10 11:34:01', '2025-05-10 12:57:00'),
(27, 10, 18, 1, '2025-05-11', '00:00:00', 'pending', NULL, '2025-05-10 12:40:08', '2025-05-10 12:40:08'),
(28, 10, 29, 2, '2025-05-10', '14:00:00', '', NULL, '2025-05-10 12:56:52', '2025-05-10 13:32:29'),
(29, 10, 30, 1, '2025-05-10', '10:00:00', '', NULL, '2025-05-10 12:57:26', '2025-05-10 13:09:35'),
(30, 18, 26, 1, '2025-05-16', '10:00:00', 'pending', NULL, '2025-05-10 13:07:35', '2025-05-10 13:07:49'),
(31, 10, 30, 1, '2025-05-17', '10:00:00', 'pending', NULL, '2025-05-10 13:31:26', '2025-05-10 13:31:26'),
(32, 10, 29, 1, '2025-05-14', '14:00:00', 'pending', NULL, '2025-05-10 13:32:04', '2025-05-10 13:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `attendants`
--

CREATE TABLE `attendants` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendants`
--

INSERT INTO `attendants` (`id`, `name`, `bio`, `created_at`, `updated_at`) VALUES
(1, 'Kranchy Reyes Polvorido', '', '2025-05-07 21:28:51', '2025-05-08 18:37:33'),
(2, 'Melanie Cosas', '', '2025-05-07 21:28:51', '2025-05-08 18:38:12');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sessions` int(11) NOT NULL,
  `duration_days` int(11) NOT NULL COMMENT 'Duration in days',
  `grace_period_days` int(11) NOT NULL COMMENT 'Grace period in days',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `sessions`, `duration_days`, `grace_period_days`, `created_at`) VALUES
(1, '3 + 1 Underarm Whitening', 'Buy 3 Get 1 Free Underarm Whitening Package', 1847.00, 4, 90, 180, NOW()),
(2, '3 + 1 Back Whitening', 'Buy 3 Get 1 Free Back Whitening Package', 1847.00, 4, 120, 180, NOW()),
(3, '3 + 1 Bikini Whitening', 'Buy 3 Get 1 Free Bikini Whitening Package', 1847.00, 4, 120, 180, NOW()),
(4, '3 + 1 Butt Whitening', 'Buy 3 Get 1 Free Butt Whitening Package', 1847.00, 4, 120, 180, NOW()),
(5, '3 + 1 Carbon Doll Laser', 'Buy 3 Get 1 Free Carbon Doll Laser Package', 3197.00, 4, 120, 240, NOW()),
(6, '3 + 1 Casmara Facial', 'Buy 3 Get 1 Free Casmara Facial Package', 3200.00, 4, 120, 180, NOW()),
(7, '3 + 1 Face Cavitation', 'Buy 3 Get 1 Free Face Cavitation Package', 1397.00, 4, 120, 180, NOW()),
(8, '3 + 1 Waist Cavitation', 'Buy 3 Get 1 Free Waist Cavitation Package', 1397.00, 4, 120, 180, NOW()),
(9, '3 + 1 Thighs Cavitation', 'Buy 3 Get 1 Free Thighs Cavitation Package', 1397.00, 4, 120, 180, NOW()),
(10, '3 + 1 Arms Cavitation', 'Buy 3 Get 1 Free Arms Cavitation Package', 1397.00, 4, 120, 180, NOW()),
(11, '3 + 1 Charcoal', 'Buy 3 Get 1 Free Charcoal Facial Package', 2297.00, 4, 120, 180, NOW()),
(12, '3 + 1 Chest Infusion', 'Buy 3 Get 1 Free Chest Infusion Package', 1847.00, 4, 120, 180, NOW()),
(13, '3 + 1 Chest/Back Facial', 'Buy 3 Get 1 Free Chest/Back Facial Package', 1997.00, 4, 120, 180, NOW()),
(14, '3 + 1 Collagen', 'Buy 3 Get 1 Free Collagen Facial Package', 2297.00, 4, 120, 180, NOW()),
(15, '3 + 1 Diamond Peel', 'Buy 3 Get 1 Free Diamond Peel Package', 1697.00, 4, 90, 180, NOW()),
(16, '3 + 1 Galvanic Therapy', 'Buy 3 Get 1 Free Galvanic Therapy Package', 2597.00, 4, 120, 180, NOW()),
(17, '3 + 1 Geneo Infusion', 'Buy 3 Get 1 Free Geneo Infusion Package', 3197.00, 4, 120, 180, NOW()),
(18, '3 + 1 IPL Arms', 'Buy 3 Get 1 Free IPL Arms Package', 3197.00, 4, 120, 180, NOW()),
(19, '3 + 1 IPL Back', 'Buy 3 Get 1 Free IPL Back Package', 3197.00, 4, 120, 180, NOW()),
(20, '3 + 1 IPL Bikini', 'Buy 3 Get 1 Free IPL Bikini Package', 2297.00, 4, 120, 180, NOW()),
(21, '3 + 1 IPL Brazilian', 'Buy 3 Get 1 Free IPL Brazilian Package', 3197.00, 4, 120, 180, NOW()),
(22, '3 + 1 IPL Chest', 'Buy 3 Get 1 Free IPL Chest Package', 3197.00, 4, 120, 180, NOW()),
(23, '3 + 1 IPL Face', 'Buy 3 Get 1 Free IPL Face Package', 1697.00, 4, 120, 180, NOW()),
(24, '3 + 1 IPL Legs', 'Buy 3 Get 1 Free IPL Legs Package', 3197.00, 4, 120, 180, NOW()),
(25, '3 + 1 IPL Neck', 'Buy 3 Get 1 Free IPL Neck Package', 1697.00, 4, 120, 180, NOW()),
(26, '3 + 1 IPL Thighs', 'Buy 3 Get 1 Free IPL Thighs Package', 3197.00, 4, 120, 180, NOW()),
(27, '3 + 1 IPL Underarms', 'Buy 3 Get 1 Free IPL Underarms Package', 1697.00, 4, 120, 180, NOW()),
(28, '3 + 1 IPL Upperlip', 'Buy 3 Get 1 Free IPL Upperlip Package', 1097.00, 4, 120, 180, NOW()),
(29, '3 + 1 Pico Glow', 'Buy 3 Get 1 Free Pico Glow Package', 3197.00, 4, 120, 180, NOW()),
(30, '3 + 1 Neck Cleaning', 'Buy 3 Get 1 Free Neck Cleaning Package', 1397.00, 4, 120, 180, NOW()),
(31, '3 + 1 Neck Infusion', 'Buy 3 Get 1 Free Neck Infusion Package', 1247.00, 4, 120, 180, NOW()),
(32, '3 + 1 Oxygeneo', 'Buy 3 Get 1 Free Oxygeneo Package', 6197.00, 4, 120, 180, NOW()),
(33, '3 + 1 Pico Laser Underarm', 'Buy 3 Get 1 Free Pico Laser Underarm Package', 3197.00, 4, 120, 240, NOW()),
(34, '3 + 1 Primary Facial', 'Buy 3 Get 1 Free Primary Facial Package', 1697.00, 4, 120, 180, NOW());

ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `category`) VALUES
(1, 'Yellow Soap (Acne)', 140.00, NULL),
(2, 'Pore Minimizer (Toner)', 380.00, NULL),
(3, 'Sunscreen', 225.00, NULL),
(4, 'Kojic Soap', 180.00, NULL),
(5, 'Lightening Cream', 230.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `category`, `price`, `duration`, `created_at`, `updated_at`) VALUES
(1, 'PRIMARY FACIAL (Face)', 'Basic facial treatment for cleansing and rejuvenation', 'facials', 499.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(2, 'CHEST/BACK', 'Facial treatment for chest and back areas', 'facials', 649.00, 75, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(3, 'NECK', 'Specialized facial treatment for the neck area', 'facials', 449.00, 45, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(4, 'COLLAGEN (Facial w/ Diamond Peel + Charcoal)', 'Advanced facial with diamond peel and charcoal for deep cleansing', 'facials', 699.00, 90, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(5, 'SNOW WHITE (Facial w/ Diamond Peel + Collagen Mask)', 'Brightening facial with diamond peel and collagen mask', 'facials', 699.00, 90, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(6, 'CASAMARA (Facial w/ Diamond Peel + Vit. C Serum & Mask)', 'Premium facial with diamond peel and vitamin C treatment', 'facials', 1000.00, 120, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(7, 'DIAMOND PEEL', 'Microdermabrasion treatment for skin exfoliation', 'facials', 499.00, 45, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(8, 'RADIO FREQUENCY (Face)', 'Non-invasive treatment that uses radio frequency energy to tighten skin', 'facials', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(9, 'GENEO INFUSION (Rejuvenation + Brightening)', 'Advanced facial treatment for skin rejuvenation and brightening', 'facials', 999.00, 90, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(10, 'OXYGENEO (Facial + Rejuvenation)', 'Innovative 3-in-1 facial treatment that exfoliates, infuses, and oxygenates', 'facials', 1999.00, 120, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(11, 'SKIN REJUVENATION (Facial + Serum + PDT Light)', 'Comprehensive skin rejuvenation treatment with light therapy', 'facials', 999.00, 90, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(12, 'GALVANIC THERAPY (Facial + Serum Infusion)', 'Uses galvanic current to improve product absorption into the skin', 'facials', 799.00, 75, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(13, 'PRP (Facial + PRP + PDT Light)', 'Platelet-rich plasma therapy combined with light treatment', 'facials', 1899.00, 120, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(14, 'PICO LASER CARBON DOLL', 'Advanced laser treatment for skin rejuvenation', 'facials', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(15, 'PICO GLOW (Melasma/Freckles)', 'Targeted treatment for pigmentation issues', 'facials', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(16, 'VIT. C INFUSION (Face)', 'Vitamin C infusion for brightening and anti-aging', 'lightening', 349.00, 45, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(17, 'UNDERARM WHITENING', 'Specialized treatment for underarm whitening', 'lightening', 549.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(18, 'BACK WHITENING', 'Whitening treatment for the back area', 'lightening', 649.00, 75, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(19, 'CHEST WHITENING', 'Whitening treatment for the chest area', 'lightening', 549.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(20, 'BUTT WHITENING', 'Whitening treatment for the buttocks area', 'lightening', 549.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(21, 'NECK WHITENING', 'Specialized whitening treatment for the neck', 'lightening', 349.00, 45, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(22, 'BIKINI WHITENING', 'Whitening treatment for the bikini area', 'lightening', 549.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(23, 'SKIN WHITE GLUTA (60mg) + VIT C IV PUSH', 'Glutathione and Vitamin C IV treatment for skin whitening', 'lightening', 649.00, 30, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(24, 'SNOW WHITE GLUTA (5000mg) IV PUSH', 'High-dose glutathione IV treatment for intensive whitening', 'lightening', 999.00, 45, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(25, 'PLATINUM GLUTA (500,000mgs) IV DRIP', 'Premium glutathione IV drip for maximum whitening effect', 'lightening', 1299.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(26, 'LUXURY DRIP (500,000mgs) IV DRIP', 'Comprehensive IV drip with multiple skin-enhancing ingredients', 'lightening', 2000.00, 75, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(27, 'BB GLOW (Facial w/ Diamond Peel + Mask + PDT Light)', 'Semi-permanent BB cream treatment with additional therapies', 'lightening', 2199.00, 120, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(28, 'BB BLUSH', 'Semi-permanent blush treatment for a natural flush', 'lightening', 799.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(29, 'PIMPLE INJECTION (Per Pimple)', 'Targeted injection for individual pimples', 'pimple', 99.00, 15, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(30, 'ANTI ACNE TREATMENT (Facial + Serum + Light)', 'Comprehensive acne treatment with facial, serum, and light therapy', 'pimple', 1599.00, 120, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(31, 'BODY SLIMMING w/ CAVITATION FACE', 'Facial slimming treatment with ultrasonic cavitation', 'slimming', 899.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(32, 'BODY SLIMMING w/ CAVITATION WAIST', 'Waist slimming treatment with ultrasonic cavitation', 'slimming', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(33, 'BODY SLIMMING w/ CAVITATION THIGHS', 'Thigh slimming treatment with ultrasonic cavitation', 'slimming', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(34, 'BODY SLIMMING w/ CAVITATION ARMS', 'Arm slimming treatment with ultrasonic cavitation', 'slimming', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(35, 'IPL HAIR REMOVAL FACE', 'IPL hair removal for the face', 'hair-removal', 499.00, 30, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(36, 'IPL HAIR REMOVAL NECK', 'IPL hair removal for the neck', 'hair-removal', 499.00, 30, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(37, 'IPL HAIR REMOVAL ARM', 'IPL hair removal for the arms', 'hair-removal', 999.00, 45, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(38, 'IPL HAIR REMOVAL BRAZILIAN', 'IPL hair removal for the Brazilian area', 'hair-removal', 699.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(39, 'IPL HAIR REMOVAL LEGS (lower/upper)', 'IPL hair removal for the legs', 'hair-removal', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(40, 'IPL HAIR REMOVAL UPPERLIP', 'IPL hair removal for the upper lip', 'hair-removal', 299.00, 15, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(41, 'IPL HAIR REMOVAL UNDERARMS', 'IPL hair removal for the underarms', 'hair-removal', 499.00, 30, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(42, 'IPL HAIR REMOVAL BIKINI', 'IPL hair removal for the bikini line', 'hair-removal', 499.00, 30, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(43, 'IPL HAIR REMOVAL CHEST', 'IPL hair removal for the chest', 'hair-removal', 999.00, 45, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(44, 'IPL HAIR REMOVAL BACK (lower/upper)', 'IPL hair removal for the back', 'hair-removal', 999.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(45, 'WARTS REMOVAL (1500 Minimum / Area)', 'Professional removal of warts', 'other', 1500.00, 60, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(46, 'KOREAN LASH LIFT WITH TINT', 'Korean-style lash lift with tinting', 'other', 699.00, 90, '2025-05-07 21:28:51', '2025-05-07 21:28:51'),
(47, 'KOREAN LASH LIFT WITHOUT TINT', 'Korean-style lash lift without tinting', 'other', 499.00, 75, '2025-05-07 21:28:51', '2025-05-07 21:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `phone`, `password`, `is_admin`, `is_active`, `last_login`, `created_at`, `updated_at`, `username`) VALUES
(10, 'Kurt Baylosis', '12321312321', '$2y$10$7Yif8nS0U0PQsnWyoLNRQ.pzjBUr.n3sNEmVdZ72VqhUPFAEB3cqK', 0, 1, '2025-05-10 12:43:41', '2025-05-10 11:33:36', '2025-05-10 12:43:41', 'Kurt'),
(18, 'Kenai', '12312311231', '$2y$10$jl2Qs1G83msstUv3Okv7ueFHLiM1LrqsKQdqG2LCR4.5CYYPyrf1.', 0, 1, NULL, '2025-05-10 13:07:13', '2025-05-10 13:07:13', 'Khi');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('login','logout','password_reset','profile_update','appointment_booking','appointment_cancellation') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `session_duration` int(11) DEFAULT NULL COMMENT 'Duration in seconds',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity`
--

INSERT INTO `user_activity` (`id`, `user_id`, `activity_type`, `ip_address`, `user_agent`, `login_time`, `logout_time`, `session_duration`, `created_at`) VALUES
(1, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 18:41:00', '2025-05-08 18:41:36', 36, '2025-05-08 10:41:00'),
(2, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 18:41:36', NULL, NULL, '2025-05-08 10:41:36'),
(8, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:04:25', '2025-05-08 23:06:39', 134, '2025-05-08 15:04:25'),
(9, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:06:39', NULL, NULL, '2025-05-08 15:06:39'),
(12, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:09:29', '2025-05-08 23:10:11', 42, '2025-05-08 15:09:29'),
(13, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:10:11', NULL, NULL, '2025-05-08 15:10:11'),
(16, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:28:24', '2025-05-08 23:28:51', 27, '2025-05-08 15:28:24'),
(17, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:28:51', NULL, NULL, '2025-05-08 15:28:51'),
(20, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:33:28', '2025-05-08 23:36:04', 156, '2025-05-08 15:33:28'),
(21, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:36:04', NULL, NULL, '2025-05-08 15:36:04'),
(24, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:38:06', '2025-05-08 23:38:24', 18, '2025-05-08 15:38:06'),
(25, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-08 23:38:24', NULL, NULL, '2025-05-08 15:38:24'),
(27, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 10:48:39', '2025-05-10 10:48:41', 2, '2025-05-10 02:48:39'),
(28, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 10:48:41', NULL, NULL, '2025-05-10 02:48:41'),
(29, 5, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 10:57:33', '2025-05-10 10:57:35', 2, '2025-05-10 02:57:33'),
(30, 5, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 10:57:35', NULL, NULL, '2025-05-10 02:57:35'),
(33, 9, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 11:22:06', '2025-05-10 11:22:11', 5, '2025-05-10 03:22:06'),
(34, 9, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 11:22:11', NULL, NULL, '2025-05-10 03:22:11'),
(35, 9, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 11:30:21', '2025-05-10 11:30:30', 9, '2025-05-10 03:30:21'),
(36, 9, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 11:30:30', NULL, NULL, '2025-05-10 03:30:30'),
(37, 15, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 11:42:15', '2025-05-10 11:42:17', 2, '2025-05-10 03:42:15'),
(38, 15, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 11:42:17', NULL, NULL, '2025-05-10 03:42:17'),
(39, 10, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 12:36:02', '2025-05-10 12:40:14', 252, '2025-05-10 04:36:02'),
(40, 10, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 12:40:14', NULL, NULL, '2025-05-10 04:40:14'),
(41, 10, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 12:43:41', '2025-05-10 12:44:18', 37, '2025-05-10 04:43:41'),
(42, 10, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-10 12:44:18', NULL, NULL, '2025-05-10 04:44:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_logs_admin_id` (`admin_id`),
  ADD KEY `idx_admin_logs_target` (`target_id`,`target_type`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appointments_user` (`user_id`),
  ADD KEY `idx_appointments_service` (`service_id`),
  ADD KEY `idx_appointments_attendant` (`attendant_id`),
  ADD KEY `idx_appointments_date` (`appointment_date`),
  ADD KEY `idx_appointments_status` (`status`);

--
-- Indexes for table `attendants`
--
ALTER TABLE `attendants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_feedback_appointment` (`appointment_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_category` (`category`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_activity_user_id` (`user_id`),
  ADD KEY `idx_user_activity_login_time` (`login_time`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `dermatologists`
--
ALTER TABLE `dermatologists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Table structure for table `package_bookings`
--

CREATE TABLE `package_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `sessions_remaining` int(11) NOT NULL,
  `valid_until` datetime NOT NULL,
  `grace_period_until` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `package_appointments`
--

CREATE TABLE `package_appointments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `dermatologist_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `package_bookings`
--
ALTER TABLE `package_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `package_appointments`
--
ALTER TABLE `package_appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `dermatologist_id` (`dermatologist_id`);

--
-- AUTO_INCREMENT for table `package_bookings`
--
ALTER TABLE `package_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_appointments`
--
ALTER TABLE `package_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `package_bookings`
--
ALTER TABLE `package_bookings`
  ADD CONSTRAINT `package_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `package_bookings_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`);

--
-- Constraints for table `package_appointments`
--
ALTER TABLE `package_appointments`
  ADD CONSTRAINT `package_appointments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `package_bookings` (`id`),
  ADD CONSTRAINT `package_appointments_ibfk_2` FOREIGN KEY (`dermatologist_id`) REFERENCES `dermatologists` (`id`);

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`dermatologist_id`) REFERENCES `dermatologists` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `patient_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
