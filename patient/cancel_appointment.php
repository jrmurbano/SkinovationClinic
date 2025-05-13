<?php
session_start();
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No appointment specified';
    header('Location: my-appointments.php');
    exit();
}

$appointment_id = $_GET['id'];
$patient_id = $_SESSION['patient_id'];

// Start transaction
$conn->begin_transaction();

try {
    // First check if it's a regular appointment
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND patient_id = ? AND status = 'pending'");
    $stmt->bind_param('ii', $appointment_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // It's a regular appointment
        $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND patient_id = ?");
        $stmt->bind_param('ii', $appointment_id, $patient_id);
        $stmt->execute();
    } else {
        // Check if it's a package appointment
        $stmt = $conn->prepare("SELECT pa.*, pb.patient_id 
                              FROM package_appointments pa 
                              JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
                              WHERE pa.package_appointment_id = ? AND pb.patient_id = ? AND pa.status = 'pending'");
        $stmt->bind_param('ii', $appointment_id, $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Appointment not found or already cancelled');
        }

        // Update package appointment status
        $stmt = $conn->prepare("UPDATE package_appointments SET status = 'cancelled' WHERE package_appointment_id = ?");
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();

        // Get the booking details to refund the session
        $appointment = $result->fetch_assoc();
        $booking_id = $appointment['booking_id'];

        // Add the session back to the package booking
        $stmt = $conn->prepare('UPDATE package_bookings SET sessions_remaining = sessions_remaining + 1 WHERE booking_id = ?');
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
    }

    $conn->commit();
    $_SESSION['success'] = 'Appointment cancelled successfully';
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

header('Location: my-appointments.php');
exit();
?>
