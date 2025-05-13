<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Default Laragon MySQL username
define('DB_PASS', ''); // Default Laragon MySQL password
define('DB_NAME', 'beauty_clinic2');

// Establish database connection
try {
    $conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection Error: ' . $e->getMessage();
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Base URL (adjust this according to your setup)
$base_url = 'http://skinovationclinic.test';

// Common functions
function redirect($path)
{
    header("Location: $path");
    exit();
}

// Security function to prevent XSS
function clean($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['patient_id']);
}

// Function to check if admin is logged in
function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}
