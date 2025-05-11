<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Check if client_id is provided
if (!isset($_GET['client_id'])) {
    header('Location: clients.php');
    exit;
}

$client_id = intval($_GET['client_id']);

// Get client information for logging
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ? AND is_admin = 0");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: clients.php');
    exit;
}

$client = $result->fetch_assoc();
$client_name = $client['name'];
$client_email = $client['email'];

// Start transaction
$conn->begin_transaction();

try {
    // Delete client's feedback
    $stmt = $conn->prepare("DELETE FROM feedback WHERE user_id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();

    // Delete client's appointments
    $stmt = $conn->prepare("DELETE FROM appointments WHERE user_id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();

    // Delete client's activity logs
    $stmt = $conn->prepare("DELETE FROM user_activity WHERE user_id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();

    // Finally, delete the client
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();

    // Log the action
    $admin_id = $_SESSION['user_id'];
    $action = "Deleted client account: $client_name ($client_email)";
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $admin_id, $action);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['success_message'] = "Client account has been successfully deleted.";

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Set error message
    $_SESSION['error_message'] = "An error occurred while deleting the client account: " . $e->getMessage();
}

// Redirect back to clients page
header('Location: clients.php');
exit;
?>
