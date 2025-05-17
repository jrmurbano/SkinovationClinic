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

// Start transaction
$conn->beginTransaction();

try {
    // First check if it's a regular appointment
    if ($appointment_type === 'regular') {
        $stmt = $conn->prepare("
            SELECT a.*, s.service_name, CONCAT(p.first_name, ' ', p.last_name) as patient_name
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE a.appointment_id = ? AND a.patient_id = ? AND a.status = 'pending'
        ");
        $stmt->execute([$appointment_id, $patient_id]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            throw new Exception('Appointment not found or already cancelled');
        }

        // Check if appointment is at least 24 hours away
        $appointment_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
        if ($appointment_datetime <= strtotime('+1 day')) {
            throw new Exception('Appointments can only be cancelled at least 24 hours in advance');
        }
    } else {
        // Check if it's a package appointment
        $stmt = $conn->prepare("
            SELECT pa.*, pb.patient_id, p.package_name, CONCAT(pt.first_name, ' ', pt.last_name) as patient_name
            FROM package_appointments pa 
            JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
            JOIN packages p ON pb.package_id = p.package_id
            JOIN patients pt ON pb.patient_id = pt.patient_id
            WHERE pa.package_appointment_id = ? AND pb.patient_id = ? AND pa.status = 'pending'
        ");
        $stmt->execute([$appointment_id, $patient_id]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            throw new Exception('Appointment not found or already cancelled');
        }

        // Check if appointment is at least 24 hours away
        $appointment_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
        if ($appointment_datetime <= strtotime('+1 day')) {
            throw new Exception('Appointments can only be cancelled at least 24 hours in advance');
        }
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
        $appointment_type === 'regular' ? $appointment['service_name'] : $appointment['package_name'],
        $appointment_type === 'regular' ? $appointment['service_name'] : $appointment['package_name'],
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
