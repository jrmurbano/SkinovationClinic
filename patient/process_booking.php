<?php
session_start();
include '../db.php';
include '../config.php';

// Debug logging
$debug_log = fopen('booking_debug.log', 'a');
fwrite($debug_log, "\n\n" . date('Y-m-d H:i:s') . " - New booking attempt\n");
fwrite($debug_log, 'POST data: ' . print_r($_POST, true) . "\n");
fwrite($debug_log, 'SESSION data: ' . print_r($_SESSION, true) . "\n");

// Check if patient is logged in
if (!isset($_SESSION['patient_id'])) {
    fwrite($debug_log, "Error: No patient_id in session\n");
    fclose($debug_log);
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $patient_id = $_SESSION['patient_id'];
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $attendant_id = $_POST['attendant_id'] ?? '';
    $package_id = isset($_POST['package_id']) ? $_POST['package_id'] : null;
    $service_id = isset($_POST['service_id']) ? $_POST['service_id'] : null;
    $notes = $_POST['notes'] ?? '';
    $status = 'pending';

    fwrite($debug_log, "Processed form data:\n");
    fwrite($debug_log, "patient_id: $patient_id\n");
    fwrite($debug_log, "appointment_date: $appointment_date\n");
    fwrite($debug_log, "appointment_time: $appointment_time\n");
    fwrite($debug_log, "attendant_id: $attendant_id\n");
    fwrite($debug_log, "package_id: $package_id\n");
    fwrite($debug_log, "service_id: $service_id\n");

    // Validate required fields
    if (!$appointment_date || !$appointment_time || !$attendant_id) {
        fwrite($debug_log, "Error: Missing required fields\n");
        $_SESSION['error'] = 'Please fill in all required fields';
        fclose($debug_log);
        header('Location: ' . getRedirectUrl($package_id));
        exit();
    }

    // Start transaction
    $conn->beginTransaction();

    try {
        // Check if slot is still available
        $check_sql = "SELECT 1 FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND attendant_id = ? AND status != 'cancelled'
                     UNION ALL
                     SELECT 1 FROM package_appointments WHERE appointment_date = ? AND appointment_time = ? AND attendant_id = ? AND status != 'cancelled'";

        $stmt = $conn->prepare($check_sql);
        $stmt->execute([$appointment_date, $appointment_time, $attendant_id, $appointment_date, $appointment_time, $attendant_id]);
        
        if ($stmt->rowCount() > 0) {
            fwrite($debug_log, "Error: Time slot no longer available\n");
            throw new Exception('This time slot is no longer available. Please choose another time.');
        }

        if ($package_id) {
            // Handle package booking
            $stmt = $conn->prepare('SELECT * FROM packages WHERE package_id = ?');
            $stmt->execute([$package_id]);
            $package = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$package) {
                fwrite($debug_log, "Error: Package not found\n");
                throw new Exception('Selected package not found.');
            }

            // Create package booking
            $stmt = $conn->prepare("INSERT INTO package_bookings (patient_id, package_id, sessions_remaining, valid_until, grace_period_until) 
                                  VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), DATE_ADD(NOW(), INTERVAL ? DAY))");
            $sessions = $package['sessions'];
            $duration = $package['duration_days'];
            $grace_period = $package['grace_period_days'];
            $stmt->execute([$patient_id, $package_id, $sessions, $duration, $grace_period]);

            if (!$stmt->rowCount()) {
                fwrite($debug_log, 'Error: Failed to create package booking - ' . $stmt->errorInfo()[2] . "\n");
                throw new Exception('Failed to create package booking. Error: ' . $stmt->errorInfo()[2]);
            }

            $booking_id = $conn->lastInsertId();
            fwrite($debug_log, "Package booking created with ID: $booking_id\n");

            // Create first package appointment
            $stmt = $conn->prepare("INSERT INTO package_appointments (booking_id, appointment_date, appointment_time, attendant_id, status) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$booking_id, $appointment_date, $appointment_time, $attendant_id, $status]);

            if (!$stmt->rowCount()) {
                fwrite($debug_log, 'Error: Failed to create package appointment - ' . $stmt->errorInfo()[2] . "\n");
                throw new Exception('Failed to create package appointment. Error: ' . $stmt->errorInfo()[2]);
            }
            fwrite($debug_log, "Package appointment created successfully\n");

            // Create notification for admin
            $package_appointment_id = $conn->lastInsertId();
            $stmt = $conn->prepare("SELECT p.package_name, pt.first_name, pt.last_name 
                                   FROM package_appointments pa 
                                   JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
                                   JOIN packages p ON pb.package_id = p.package_id 
                                   JOIN patients pt ON pb.patient_id = pt.patient_id 
                                   WHERE pa.package_appointment_id = ?");
            $stmt->execute([$package_appointment_id]);
            $package_info = $stmt->fetch(PDO::FETCH_ASSOC);

            $title = "New Package Booking";
            $message = $package_info['first_name'] . " " . $package_info['last_name'] . 
                       " has booked the package: " . $package_info['package_name'];
            createNotification($conn, 'package', $package_appointment_id, $title, $message);
        } else {
            // Handle regular appointment
            if (!$service_id) {
                fwrite($debug_log, "Error: No service selected\n");
                throw new Exception('Please select a service.');
            }

            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, service_id, attendant_id, appointment_date, appointment_time, notes, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $service_id, $attendant_id, $appointment_date, $appointment_time, $notes, $status]);

            if (!$stmt->rowCount()) {
                fwrite($debug_log, 'Error: Failed to create appointment - ' . $stmt->errorInfo()[2] . "\n");
                throw new Exception('Failed to create appointment. Error: ' . $stmt->errorInfo()[2]);
            }
            fwrite($debug_log, "Regular appointment created successfully\n");

            // Create notification for admin
            $appointment_id = $conn->lastInsertId();
            $stmt = $conn->prepare("SELECT s.service_name, p.first_name, p.last_name 
                                   FROM appointments a 
                                   JOIN services s ON a.service_id = s.service_id 
                                   JOIN patients p ON a.patient_id = p.patient_id 
                                   WHERE a.appointment_id = ?");
            $stmt->execute([$appointment_id]);
            $appointment_info = $stmt->fetch(PDO::FETCH_ASSOC);

            $title = "New Appointment Booking";
            $message = $appointment_info['first_name'] . " " . $appointment_info['last_name'] . 
                       " has booked an appointment for: " . $appointment_info['service_name'];
            createNotification($conn, 'appointment', $appointment_id, $title, $message);
        }
        $conn->commit();
        fwrite($debug_log, "Transaction committed successfully\n");
        $_SESSION['success'] = 'Your appointment has been successfully booked! Please wait for confirmation.';
        fclose($debug_log);
        header('Location: my-appointments.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        fwrite($debug_log, 'Transaction rolled back. Error: ' . $e->getMessage() . "\n");
        $_SESSION['error'] = $e->getMessage();
        fclose($debug_log);
        header('Location: ' . getRedirectUrl($package_id));
        exit();
    }
} else {
    fwrite($debug_log, "Error: Not a POST request\n");
    fclose($debug_log);
    header('Location: calendar_view.php');
    exit();
}

function getRedirectUrl($package_id = null)
{
    $url = 'calendar_view.php';
    if ($package_id) {
        $url .= '?package_id=' . $package_id;
    }
    if (isset($_POST['date'])) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . 'date=' . $_POST['date'];
    }
    return $url;
}
?>
