-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 13, 2025 at 01:56 PM
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
  `admin_last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `admin_first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `admin_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `admin_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_last_name`, `admin_first_name`, `admin_username`, `admin_password`) VALUES
(1, 'Urbano', 'Jean', 'jrmurbano', '$2y$10$e0NR1z1F5J1u1J1u1J1u1u1J1u1J1u1J1u1J1u1J1u1J1u1J1u1u');

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
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendants`
--

CREATE TABLE `attendants` (
  `attendant_id` int NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `shift_date` date NOT NULL,
  `shift_time` time NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(10, '3 + 1 Arms Cavitation', NULL, 1397.00, 4, 120, 180, '2025-05-13 14:45:06', '2025-05-13 14:45:06'),
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
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `last_name`, `first_name`, `middle_name`, `username`, `password`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Urbano', 'Ramona', 'Magbanua', 'ramzurbano', '$2y$10$1jzEodSG/t1G9DdCyK.AeONnukZuX1TW6tWCN7GqbSY3rZknngiRu', '09496269783', '2025-05-13 15:06:45', '2025-05-13 15:06:45');

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
(1, 'Yellow soap (acne)', NULL, 140.00, 0, '2025-05-13 14:39:04', '2025-05-13 14:43:21', 'assets/img/yellow_soap.png'),
(2, 'Pore Minimizer (Toner)', NULL, 380.00, 0, '2025-05-13 14:39:04', '2025-05-13 14:43:21', 'assets/img/pore_minimizer.png'),
(3, 'Sunscreen', NULL, 225.00, 0, '2025-05-13 14:39:04', '2025-05-13 14:43:21', 'assets/img/sunscreen.png'),
(4, 'Kojic Soap', NULL, 180.00, 0, '2025-05-13 14:39:04', '2025-05-13 14:43:21', 'assets/img/kojic_soap.png'),
(5, 'Lightening cream', NULL, 230.00, 0, '2025-05-13 14:39:04', '2025-05-13 14:43:21', 'assets/img/lightening_cream.png');

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
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `price`, `duration`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 'Primary Facial (Face)', NULL, 499.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(2, 'Chest/Back', NULL, 649.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(3, 'Neck', NULL, 449.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(4, 'Charcoal', 'Facial with Diamond Peel + Charcoal', 699.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(5, 'Collagen', 'Facial with Diamond Peel + Collagen Mask', 699.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(6, 'Snow White', 'Facial with Diamond Peel + Vitamin C serum & mask', 799.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(7, 'Casmara', 'Facial with Diamond Peel + Casmara Mask', 1000.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(8, 'Diamond Peel', NULL, 499.00, 60, 1, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(9, 'Radio Frequency', 'With Facial', 999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(10, 'Geneo Infusion', 'Rejuvenation & Brightening', 999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(11, 'Oxygeneo', 'Facial + RF + Infusion', 1999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(12, 'Skin Rejuvenation', 'Facial + Serum + PDT Light', 999.00, 60, 2, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(13, 'Galvanic Therapy', 'Facial + Serum Infusion', 799.00, 60, 2, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(14, 'PRP', 'Facial + PRP + PDT Light', 1899.00, 60, 2, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(15, 'Carbon Doll Laser', NULL, 999.00, 60, 3, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(16, 'Pico Glow', 'Melasma/Freckles', 999.00, 60, 3, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(17, 'Tattoo Removal', 'Depends on size', 999.00, 60, 3, '2025-05-13 06:03:46', '2025-05-13 14:30:47'),
(18, 'Vitamin C Infusion', 'Face', 349.00, 15, 4, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(19, 'Underarm Whitening', NULL, 549.00, 15, 4, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(20, 'Back Whitening', NULL, 649.00, 15, 4, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(21, 'Chest Whitening', NULL, 549.00, 15, 4, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(22, 'Butt Whitening', NULL, 549.00, 15, 4, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(23, 'Neck Whitening', NULL, 349.00, 15, 4, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(24, 'Pimple Injection', 'Per Pimple', 99.00, 60, 5, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(25, 'Anti-Acne Treatment', 'Facial + Serum + Light', 1599.00, 60, 5, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(26, 'Face Cavitation', NULL, 899.00, 60, 6, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(27, 'Waist Cavitation', NULL, 999.00, 60, 6, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(28, 'Thighs Cavitation', NULL, 999.00, 60, 6, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(29, 'Arms Cavitation', NULL, 999.00, 60, 6, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(30, 'IPL Face', NULL, 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(31, 'IPL Neck', NULL, 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(32, 'IPL Arm', NULL, 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(33, 'IPL Brazilian', NULL, 699.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(34, 'IPL Legs', 'Lower/Upper', 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(35, 'IPL Upperlip', NULL, 299.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(36, 'IPL Underarms', NULL, 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(37, 'IPL Bikini', NULL, 499.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(38, 'IPL Chest', NULL, 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(39, 'IPL Back', 'Lower/Upper', 999.00, 15, 7, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(40, 'Warts Removal', 'Minimum price per area', 1500.00, 60, 8, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(41, 'Korean Lash Lift with Tint', NULL, 699.00, 30, 8, '2025-05-13 06:03:46', '2025-05-13 06:03:46'),
(42, 'Korean Lash Lift without Tint', NULL, 499.00, 30, 8, '2025-05-13 06:03:46', '2025-05-13 06:03:46');

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
(7, 'IPL (Hair Removal)'),
(8, 'Other Services');

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
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `feedback_appointment_id_fk` (`appointment_id`),
  ADD KEY `feedback_patient_id_fk` (`patient_id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendants`
--
ALTER TABLE `attendants`
  MODIFY `attendant_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `package_appointments`
--
ALTER TABLE `package_appointments`
  MODIFY `package_appointment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_bookings`
--
ALTER TABLE `package_bookings`
  MODIFY `booking_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_appointment_id_fk` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `feedback_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

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
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
