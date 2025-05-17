<?php
if (defined('CONFIG_INCLUDED')) {
    return;
}
define('CONFIG_INCLUDED', true);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Default XAMPP MySQL username
define('DB_PASS', ''); // Default XAMPP MySQL password
define('DB_NAME', 'beauty_clinic2');

// Establish database connection
try {
    $conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log('Connection Error: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Base URL (adjust this according to your setup)
$base_url = 'http://localhost/final';

// Common functions
if (!function_exists('redirect')) {
    function redirect($path) {
        header("Location: $path");
        exit();
    }
}

// Security function to prevent XSS
if (!function_exists('clean')) {
    function clean($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// Check if user is logged in
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['patient_id']);
    }
}

// Function to check if admin is logged in
if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']);
    }
}

// Function to check if owner is logged in
if (!function_exists('isOwnerLoggedIn')) {
    function isOwnerLoggedIn() {
        return isset($_SESSION['owner_id']);
    }
}

if (!function_exists('createNotification')) {
    function createNotification($conn, $type, $appointment_id, $title, $message, $patient_id = null) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO notifications (type, appointment_id, title, message, patient_id, is_read, created_at) 
                VALUES (?, ?, ?, ?, ?, 0, NOW())
            ");
            return $stmt->execute([$type, $appointment_id, $title, $message, $patient_id]);
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }
}

// Function to check if a date is valid
if (!function_exists('isValidDate')) {
    function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

// Function to check if a time is valid
if (!function_exists('isValidTime')) {
    function isValidTime($time) {
        $t = DateTime::createFromFormat('H:i:s', $time);
        return $t && $t->format('H:i:s') === $time;
    }
}

// Function to format currency
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '₱' . number_format($amount, 2);
    }
}
