<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Check if required parameters are present
if (!isset($_POST['appointment_id']) || !isset($_POST['action'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: view_patient.php?id=' . $_GET['patient_id']);
    exit();
}

$appointment_id = clean($_POST['appointment_id']);
$action = clean($_POST['action']);

// Start transaction
$conn->beginTransaction();

try {
    // Get appointment details
    $stmt = $conn->prepare("
        SELECT a.*, p.patient_id, p.first_name, p.last_name, s.service_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        JOIN services s ON a.service_id = s.service_id
        WHERE a.appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        throw new Exception('Appointment not found');
    }

    // Update appointment status
    $status = ($action === 'confirm') ? 'confirmed' : 'cancelled';
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
    $stmt->execute([$status, $appointment_id]);

    // Create notification for patient
    $title = "Appointment " . ucfirst($status);
    $message = sprintf(
        "Your appointment for %s on %s at %s has been %s.",
        $appointment['service_name'],
        date('F j, Y', strtotime($appointment['appointment_date'])),
        date('g:i A', strtotime($appointment['appointment_time'])),
        $status
    );

    createNotification($conn, 'appointment', $appointment_id, $title, $message, $appointment['patient_id']);

    $conn->commit();
    $_SESSION['success'] = "Appointment has been " . $status . " successfully.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to patient view
header('Location: view_patient.php?id=' . $appointment['patient_id']);
exit();
?> 