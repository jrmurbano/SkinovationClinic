-- MySQL dump for Skinovation Beauty Clinic
-- Version: 2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `beauty_clinic2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target_type` enum('user','appointment','service','attendant') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `admin_logs_admin_id` (`admin_id`),
  CONSTRAINT `admin_logs_admin_id_fk` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `attendants`
--

CREATE TABLE `attendants` (
  `attendant_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attendant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Drop existing tables if they exist
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_categories;

-- Create the `service_categories` table
CREATE TABLE `service_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create the `services` table
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `category_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`service_id`),
  KEY `services_category_id_fk` (`category_id`),
  CONSTRAINT `services_category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `service_categories`
INSERT INTO service_categories (id, name) VALUES
(1, 'Facials'),
(2, 'Anti-Aging & Face Lift'),
(3, 'Pico Laser'),
(4, 'Lightening Treatments'),
(5, 'Pimple Treatments'),
(6, 'Body Slimming with Cavitation'),
(7, 'IPL (Hair Removal)'),
(8, 'Other Services');

-- Insert data into `services`
INSERT INTO services (name, description, price, duration, category_id) VALUES
('Primary Facial (Face)', NULL, 499.00, 60, 1),
('Chest/Back', NULL, 649.00, 60, 1),
('Neck', NULL, 449.00, 60, 1),
('Charcoal', 'Facial with Diamond Peel + Charcoal', 699.00, 60, 1),
('Collagen', 'Facial with Diamond Peel + Collagen Mask', 699.00, 60, 1),
('Snow White', 'Facial with Diamond Peel + Vitamin C serum & mask', 799.00, 60, 1),
('Casmara', 'Facial with Diamond Peel + Casmara Mask', 1000.00, 60, 1),
('Diamond Peel', NULL, 499.00, 60, 1),
('Radio Frequency', 'With Facial', 999.00, 60, 2),
('Geneo Infusion', 'Rejuvenation & Brightening', 999.00, 60, 2),
('Oxygeneo', 'Facial + RF + Infusion', 1999.00, 60, 2),
('Skin Rejuvenation', 'Facial + Serum + PDT Light', 999.00, 60, 2),
('Galvanic Therapy', 'Facial + Serum Infusion', 799.00, 60, 2),
('PRP', 'Facial + PRP + PDT Light', 1899.00, 60, 2),
('Carbon Doll Laser', NULL, 999.00, 60, 3),
('Pico Glow', 'Melasma/Freckles', 999.00, 60, 3),
('Tattoo Removal', 'Depends on size', NULL, 60, 3),
('Vitamin C Infusion', 'Face', 349.00, 15, 4),
('Underarm Whitening', NULL, 549.00, 15, 4),
('Back Whitening', NULL, 649.00, 15, 4),
('Chest Whitening', NULL, 549.00, 15, 4),
('Butt Whitening', NULL, 549.00, 15, 4),
('Neck Whitening', NULL, 349.00, 15, 4),
('Pimple Injection', 'Per Pimple', 99.00, 60, 5),
('Anti-Acne Treatment', 'Facial + Serum + Light', 1599.00, 60, 5),
('Face Cavitation', NULL, 899.00, 60, 6),
('Waist Cavitation', NULL, 999.00, 60, 6),
('Thighs Cavitation', NULL, 999.00, 60, 6),
('Arms Cavitation', NULL, 999.00, 60, 6),
('IPL Face', NULL, 499.00, 15, 7),
('IPL Neck', NULL, 499.00, 15, 7),
('IPL Arm', NULL, 999.00, 15, 7),
('IPL Brazilian', NULL, 699.00, 15, 7),
('IPL Legs', 'Lower/Upper', 999.00, 15, 7),
('IPL Upperlip', NULL, 299.00, 15, 7),
('IPL Underarms', NULL, 499.00, 15, 7),
('IPL Bikini', NULL, 499.00, 15, 7),
('IPL Chest', NULL, 999.00, 15, 7),
('IPL Back', 'Lower/Upper', 999.00, 15, 7),
('Warts Removal', 'Minimum price per area', 1500.00, 60, 8),
('Korean Lash Lift with Tint', NULL, 699.00, 30, 8),
('Korean Lash Lift without Tint', NULL, 499.00, 30, 8);

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `attendant_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `appointments_patient_id_fk` (`patient_id`),
  KEY `appointments_service_id_fk` (`service_id`),
  KEY `appointments_attendant_id_fk` (`attendant_id`),
  CONSTRAINT `appointments_attendant_id_fk` FOREIGN KEY (`attendant_id`) REFERENCES `attendants` (`attendant_id`),
  CONSTRAINT `appointments_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `appointments_service_id_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`feedback_id`),
  KEY `feedback_appointment_id_fk` (`appointment_id`),
  KEY `feedback_patient_id_fk` (`patient_id`),
  CONSTRAINT `feedback_appointment_id_fk` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  CONSTRAINT `feedback_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `package_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sessions` int(11) NOT NULL,
  `duration_days` int(11) NOT NULL COMMENT 'Duration in days',
  `grace_period_days` int(11) NOT NULL COMMENT 'Grace period in days',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `package_bookings`
--

CREATE TABLE `package_bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `sessions_remaining` int(11) NOT NULL,
  `valid_until` date NOT NULL,
  `grace_period_until` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`booking_id`),
  KEY `package_bookings_patient_id_fk` (`patient_id`),
  KEY `package_bookings_package_id_fk` (`package_id`),
  CONSTRAINT `package_bookings_package_id_fk` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`),
  CONSTRAINT `package_bookings_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `package_appointments`
--

CREATE TABLE `package_appointments` (
  `package_appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `attendant_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`package_appointment_id`),
  KEY `package_appointments_booking_id_fk` (`booking_id`),
  KEY `package_appointments_attendant_id_fk` (`attendant_id`),
  CONSTRAINT `package_appointments_attendant_id_fk` FOREIGN KEY (`attendant_id`) REFERENCES `attendants` (`attendant_id`),
  CONSTRAINT `package_appointments_booking_id_fk` FOREIGN KEY (`booking_id`) REFERENCES `package_bookings` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
