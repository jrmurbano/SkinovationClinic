<?php
session_start();
include '../db.php';

// Debug logging
$debug_log = fopen('booking_debug.log', 'a');
fwrite($debug_log, "\n\n" . date('Y-m-d H:i:s') . " - New booking attempt\n");
fwrite($debug_log, 'POST data: ' . print_r($_POST, true) . "\n");
fwrite($debug_log, 'SESSION data: ' . print_r($_SESSION, true) . "\n");

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    $_SESSION['error'] = 'Please log in to book an appointment.';
    header('Location: ../login.php');
    exit();
}

// Define createNotification if not already defined (must be before any calls)
if (!function_exists('createNotification')) {
    function createNotification($conn, $type, $appointment_id, $title, $message, $patient_id = null) {
        try {
            // Validate patient_id exists
            if ($patient_id) {
                $check_stmt = $conn->prepare("SELECT 1 FROM patients WHERE patient_id = ?");
                $check_stmt->bind_param('i', $patient_id);
                $check_stmt->execute();
                if (!$check_stmt->fetch()) {
                    error_log("Invalid patient_id: $patient_id");
                    return false;
                }
            }

            $stmt = $conn->prepare("INSERT INTO notifications (type, appointment_id, title, message, patient_id, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return false;
            }
            $stmt->bind_param('sissi', $type, $appointment_id, $title, $message, $patient_id);
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
                return false;
            }
            return true;
        } catch (Exception $e) {
            error_log("Error in createNotification: " . $e->getMessage());
            return false;
        }
    }
}

// Use correct transaction handling for mysqli
$conn->autocommit(false);
$conn->begin_transaction(); // Use this for mysqli, not PDO

try {
    // Get and validate input
    $patient_id = $_SESSION['patient_id'];
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $attendant_id = $_POST['attendant_id'] ?? '';
    // Only use POST data for booking type selection
    $service_id = isset($_POST['service_id']) && is_numeric($_POST['service_id']) && $_POST['service_id'] > 0 ? (int)$_POST['service_id'] : null;
    $product_id = isset($_POST['product_id']) && is_numeric($_POST['product_id']) && $_POST['product_id'] > 0 ? (int)$_POST['product_id'] : null;
    $package_id = isset($_POST['package_id']) && is_numeric($_POST['package_id']) && $_POST['package_id'] > 0 ? (int)$_POST['package_id'] : null;

    // Debug logging
    fwrite($debug_log, "Validating input:\n");
    fwrite($debug_log, "Patient ID: " . $patient_id . "\n");
    fwrite($debug_log, "Service ID: " . $service_id . "\n");
    fwrite($debug_log, "Attendant ID: " . $attendant_id . "\n");
    fwrite($debug_log, "Date: " . $appointment_date . "\n");
    fwrite($debug_log, "Time: " . $appointment_time . "\n");
    fwrite($debug_log, "POST data: " . print_r($_POST, true) . "\n");
    fwrite($debug_log, "SESSION data: " . print_r($_SESSION, true) . "\n");

    // Validate required fields
    if (!$appointment_date || !$appointment_time || !$attendant_id) {
        fwrite($debug_log, "Missing required fields\n");
        throw new Exception('Missing required booking information.');
    }

    // Validate service/product/package selection
    if (!$service_id && !$product_id && !$package_id) {
        fwrite($debug_log, "No valid service/product/package selected\n");
        throw new Exception('No valid service, product, or package selected.');
    }

    // Ensure only one booking type is selected
    $booking_types_selected = 0;
    if ($service_id) $booking_types_selected++;
    if ($product_id) $booking_types_selected++;
    if ($package_id) $booking_types_selected++;
    if ($booking_types_selected !== 1) {
        fwrite($debug_log, "Invalid booking type selection: more than one or none selected\n");
        throw new Exception('Please select only one booking type (service, product, or package).');
    }

    // Validate date and time
    $appointment_datetime = strtotime($appointment_date . ' ' . $appointment_time);
    if ($appointment_datetime < time()) {
        fwrite($debug_log, "Invalid date/time: " . date('Y-m-d H:i:s', $appointment_datetime) . "\n");
        throw new Exception('Cannot book appointments in the past.');
    }

    // Check if slot is available
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE appointment_date = ? 
        AND appointment_time = ? 
        AND attendant_id = ? 
        AND status != 'cancelled'
    ");
    $stmt->bind_param('ssi', $appointment_date, $appointment_time, $attendant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count >= 3) {
        throw new Exception('This time slot is fully booked. Please choose another time.');
    }

    // Check if patient already has an appointment at this time
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE patient_id = ? 
        AND appointment_date = ? 
        AND appointment_time = ? 
        AND status != 'cancelled'
    ");
    $stmt->bind_param('iss', $patient_id, $appointment_date, $appointment_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count > 0) {
        throw new Exception('You already have an appointment scheduled at this time.');
    }

    // Process based on booking type
    if ($service_id) {
        // Handle service booking
        $stmt = $conn->prepare("
            INSERT INTO appointments (
                patient_id, service_id, attendant_id, 
                appointment_date, appointment_time, status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        if (!$stmt) {
            fwrite($debug_log, "Prepare Error: " . $conn->error . "\n");
            throw new Exception('Database error: ' . $conn->error);
        }
        $stmt->bind_param('iiiss', $patient_id, $service_id, $attendant_id, $appointment_date, $appointment_time);
        if (!$stmt->execute()) {
            fwrite($debug_log, "SQL Error: " . $stmt->error . "\n");
            throw new Exception('Failed to create appointment: ' . $stmt->error);
        }
        $appointment_id = $conn->insert_id;
        fwrite($debug_log, "Created appointment with ID: " . $appointment_id . "\n");
        if (!$appointment_id) {
            fwrite($debug_log, "No appointment ID returned\n");
            throw new Exception('Failed to create appointment: No ID returned');
        }
        // Schedule reminders
        scheduleAppointmentReminders($conn, $appointment_id);
        // Create notifications
        $stmt = $conn->prepare("
            SELECT s.service_name, p.first_name, p.last_name 
            FROM appointments a 
            JOIN services s ON a.service_id = s.service_id 
            JOIN patients p ON a.patient_id = p.patient_id 
            WHERE a.appointment_id = ?
        ");
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $appointment_info = $stmt->get_result()->fetch_assoc();
        // Create notification for patient
        createNotification(
            $conn,
            'appointment',
            $appointment_id,
            "Your appointment for {$appointment_info['service_name']} on " . 
            date('F d, Y', strtotime($appointment_date)) . " at " . 
            date('h:i A', strtotime($appointment_time)) . " has been booked. Please wait for confirmation.",
            '',
            $patient_id
        );
        // Create notification for admin
        createNotification(
            $conn,
            'appointment',
            $appointment_id,
            "New appointment request from {$appointment_info['first_name']} {$appointment_info['last_name']} for {$appointment_info['service_name']} on " .
            date('F d, Y', strtotime($appointment_date)) . " at " .
            date('h:i A', strtotime($appointment_time)),
            '',
            1
        );
    } elseif ($package_id) {
        // Handle package booking
        $stmt = $conn->prepare("
            INSERT INTO package_bookings (patient_id, package_id, created_at)
            VALUES (?, ?, NOW())
        ");
        if (!$stmt) {
            fwrite($debug_log, "Prepare Error: " . $conn->error . "\n");
            throw new Exception('Database error: ' . $conn->error);
        }
        $stmt->bind_param('ii', $patient_id, $package_id);
        if (!$stmt->execute()) {
            fwrite($debug_log, "SQL Error: " . $stmt->error . "\n");
            throw new Exception('Failed to create package booking: ' . $stmt->error);
        }
        $booking_id = $conn->insert_id;
        fwrite($debug_log, "Created package booking with ID: " . $booking_id . "\n");

        // Create the first package appointment
        $stmt = $conn->prepare("
            INSERT INTO package_appointments (
                booking_id, appointment_date, appointment_time, 
                attendant_id, status, created_at
            ) VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        if (!$stmt) {
            fwrite($debug_log, "Prepare Error: " . $conn->error . "\n");
            throw new Exception('Database error: ' . $conn->error);
        }
        $stmt->bind_param('issi', $booking_id, $appointment_date, $appointment_time, $attendant_id);
        if (!$stmt->execute()) {
            fwrite($debug_log, "SQL Error: " . $stmt->error . "\n");
            throw new Exception('Failed to create package appointment: ' . $stmt->error);
        }
        $package_appointment_id = $conn->insert_id;
        fwrite($debug_log, "Created package appointment with ID: " . $package_appointment_id . "\n");

        // Get package and patient details for notifications
        $stmt = $conn->prepare("
            SELECT p.package_name, pt.first_name, pt.last_name 
            FROM package_bookings pb
            JOIN packages p ON pb.package_id = p.package_id
            JOIN patients pt ON pb.patient_id = pt.patient_id
            WHERE pb.booking_id = ?
        ");
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        $booking_info = $stmt->get_result()->fetch_assoc();

        // Create notification for patient
        createNotification(
            $conn,
            'package',
            $package_appointment_id,
            "Package Appointment Booked",
            sprintf(
                "Your package appointment for %s on %s at %s has been booked. Please wait for confirmation.",
                $booking_info['package_name'],
                date('F d, Y', strtotime($appointment_date)),
                date('g:i A', strtotime($appointment_time))
            ),
            $patient_id
        );

        // Create notification for admin
        createNotification(
            $conn,
            'package',
            $package_appointment_id,
            "New Package Appointment",
            sprintf(
                "New package appointment from %s %s for %s on %s at %s",
                $booking_info['first_name'],
                $booking_info['last_name'],
                $booking_info['package_name'],
                date('F d, Y', strtotime($appointment_date)),
                date('g:i A', strtotime($appointment_time))
            ),
            1 // Admin ID
        );
    } elseif ($product_id) {
        // Handle product pre-order as an appointment
        $stmt = $conn->prepare("
            INSERT INTO appointments (
                patient_id, product_id, attendant_id, 
                appointment_date, appointment_time, status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        if (!$stmt) {
            fwrite($debug_log, "Prepare Error: " . $conn->error . "\n");
            throw new Exception('Database error: ' . $conn->error);
        }
        $stmt->bind_param('iiiss', $patient_id, $product_id, $attendant_id, $appointment_date, $appointment_time);
        if (!$stmt->execute()) {
            fwrite($debug_log, "SQL Error: " . $stmt->error . "\n");
            throw new Exception('Failed to create product pre-order: ' . $stmt->error);
        }
        $appointment_id = $conn->insert_id;
        fwrite($debug_log, "Created product pre-order appointment with ID: " . $appointment_id . "\n");
        // Schedule reminders
        scheduleAppointmentReminders($conn, $appointment_id);
        // Create notifications
        $stmt = $conn->prepare("
            SELECT pr.product_name, p.first_name, p.last_name 
            FROM appointments a 
            JOIN products pr ON a.product_id = pr.product_id 
            JOIN patients p ON a.patient_id = p.patient_id 
            WHERE a.appointment_id = ?
        ");
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $appointment_info = $stmt->get_result()->fetch_assoc();
        createNotification(
            $conn,
            'appointment',
            $appointment_id,
            "Your product pre-order for {$appointment_info['product_name']} on " .
            date('F d, Y', strtotime($appointment_date)) . " at " .
            date('h:i A', strtotime($appointment_time)) . " has been booked. Please wait for confirmation.",
            '',
            $patient_id
        );
        createNotification(
            $conn,
            'appointment',
            $appointment_id,
            "New product pre-order from {$appointment_info['first_name']} {$appointment_info['last_name']} for {$appointment_info['product_name']} on " .
            date('F d, Y', strtotime($appointment_date)) . " at " .
            date('h:i A', strtotime($appointment_time)),
            '',
            1
        );
    }

    // Commit transaction
    $conn->commit();

    // Clear any session variables related to booking type
    unset($_SESSION['selected_service_id']);

    // Set success message
    $_SESSION['success'] = 'Your booking request has been submitted successfully! Please wait for confirmation.';

    // Redirect to appointments page
    header('Location: my-appointments.php');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message
    $_SESSION['error'] = $e->getMessage();
    
    // Redirect back to calendar view
    $redirect_url = 'calendar_view.php';
    if ($service_id) $redirect_url .= "?service_id=$service_id";
    elseif ($product_id) $redirect_url .= "?product_id=$product_id";
    elseif ($package_id) $redirect_url .= "?package_id=$package_id";
    
    header("Location: $redirect_url");
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

// Minimal scheduleAppointmentReminders implementation
function scheduleAppointmentReminders($conn, $appointment_id) {
    // Fetch appointment details
    $stmt = $conn->prepare("
        SELECT a.appointment_id, a.patient_id, a.appointment_date, a.appointment_time, s.service_name, p.first_name, p.last_name
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.appointment_id = ?
    ");
    $stmt->bind_param('i', $appointment_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    if (!$appointment) return;

    $title = "Appointment Reminder";
    $message = "Reminder: Your appointment for {$appointment['service_name']} is on " .
        date('F d, Y', strtotime($appointment['appointment_date'])) . " at " .
        date('h:i A', strtotime($appointment['appointment_time'])) . ".";
    // Patient notification
    createNotification($conn, 'appointment', $appointment_id, $title, $message, $appointment['patient_id']);
    // Admin notification
    $adminMessage = "Upcoming appointment for {$appointment['first_name']} {$appointment['last_name']} ({$appointment['service_name']}) on " .
        date('F d, Y', strtotime($appointment['appointment_date'])) . " at " .
        date('h:i A', strtotime($appointment['appointment_time'])) . ".";
    createNotification($conn, 'appointment', $appointment_id, $title, $adminMessage, 1);
}
?>
