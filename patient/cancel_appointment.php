<?php
session_start();
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if appointment ID and type are provided
if (!isset($_POST['appointment_id']) || !isset($_POST['appointment_type'])) {
    $_SESSION['error'] = 'No appointment specified';
    header('Location: my-appointments.php');
    exit();
}

$appointment_id = $_POST['appointment_id'];
$appointment_type = $_POST['appointment_type'];
$reason = $_POST['reason'] ?? '';
$patient_id = $_SESSION['patient_id'];

// Debug values
error_log("Appointment ID: " . $appointment_id);
error_log("Patient ID: " . $patient_id);
error_log("Appointment Type: " . $appointment_type);

// Start transaction
$conn->beginTransaction();

try {
    // First, let's check if the appointment exists at all
    $check_stmt = $conn->prepare("
        SELECT a.*, s.service_name, CONCAT(p.first_name, ' ', p.last_name) as patient_name
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.appointment_id = ? AND a.patient_id = ?
    ");
    $check_stmt->execute([$appointment_id, $patient_id]);
    $check_appointment = $check_stmt->fetch();
    
    error_log("Checking appointment - ID: " . $appointment_id . ", Patient: " . $patient_id);
    error_log("Appointment found: " . ($check_appointment ? "Yes" : "No"));
    if ($check_appointment) {
        error_log("Current status: " . $check_appointment['status']);
    }

    // Check if it's a regular appointment or package appointment
    if ($appointment_type === 'regular') {
        $stmt = $conn->prepare("
            SELECT a.*, s.service_name as name, CONCAT(p.first_name, ' ', p.last_name) as patient_name
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE a.appointment_id = ? AND a.patient_id = ? AND a.status IN ('pending', 'confirmed')
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT pa.*, p.package_name as name, CONCAT(pt.first_name, ' ', pt.last_name) as patient_name
            FROM package_appointments pa 
            JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
            JOIN packages p ON pb.package_id = p.package_id
            JOIN patients pt ON pb.patient_id = pt.patient_id
            WHERE pa.package_appointment_id = ? AND pb.patient_id = ? AND pa.status IN ('pending', 'confirmed')
        ");
    }
    
    $stmt->execute([$appointment_id, $patient_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        throw new Exception('Appointment not found or already cancelled');
    }

    // Check if appointment is at least 24 hours away
    $appointment_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
    if ($appointment_datetime <= strtotime('+1 day')) {
        throw new Exception('Appointments can only be cancelled at least 24 hours in advance');
    }

    // Check if there's already a pending cancellation request
    $stmt = $conn->prepare("
        SELECT 1 FROM cancellation_requests 
        WHERE appointment_id = ? AND appointment_type = ? AND status = 'pending'
    ");
    $stmt->execute([$appointment_id, $appointment_type]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('You already have a pending cancellation request for this appointment');
    }

    // Create cancellation request
    $stmt = $conn->prepare("
        INSERT INTO cancellation_requests (appointment_id, appointment_type, patient_id, reason) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$appointment_id, $appointment_type, $patient_id, $reason]);

    // Create notification for admin
    $title = "New Cancellation Request";
    $message = sprintf(
        "%s has requested to cancel their %s appointment for %s on %s at %s.",
        $appointment['patient_name'],
        $appointment_type === 'regular' ? 'service' : 'package',
        $appointment['name'],
        date('F j, Y', strtotime($appointment['appointment_date'])),
        date('g:i A', strtotime($appointment['appointment_time']))
    );
    
    if (!empty($reason)) {
        $message .= " Reason: " . $reason;
    }

    createNotification($conn, 'cancellation', $appointment_id, $title, $message, $patient_id);

    $conn->commit();
    $_SESSION['success'] = 'Cancellation request submitted successfully. Please wait for admin approval.';
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

header('Location: my-appointments.php');
exit();
?>
