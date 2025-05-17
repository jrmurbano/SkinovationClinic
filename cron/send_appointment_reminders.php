<?php
require_once __DIR__ . '/../config.php';

// Function to create reminder notifications
function createReminderNotifications($conn, $appointments, $type = 'appointment') {
    foreach ($appointments as $appointment) {
        $title = "Appointment Reminder";
        $message = sprintf(
            "Reminder: Your %s for %s is %s at %s.",
            $type === 'appointment' ? 'appointment' : 'package session',
            $type === 'appointment' ? $appointment['service_name'] : $appointment['package_name'],
            $appointment['days_until'] === 0 ? 'today' : 'tomorrow',
            date('g:i A', strtotime($appointment['appointment_time']))
        );

        // Create notification for patient
        createNotification(
            $conn,
            $type,
            $type === 'appointment' ? $appointment['appointment_id'] : $appointment['package_appointment_id'],
            $title,
            $message,
            $appointment['patient_id']
        );

        // Create notification for admin
        $adminTitle = "Upcoming Appointment";
        $adminMessage = sprintf(
            "%s has an %s for %s %s at %s.",
            $appointment['patient_name'],
            $type === 'appointment' ? 'appointment' : 'package session',
            $type === 'appointment' ? $appointment['service_name'] : $appointment['package_name'],
            $appointment['days_until'] === 0 ? 'today' : 'tomorrow',
            date('g:i A', strtotime($appointment['appointment_time']))
        );

        createNotification(
            $conn,
            $type,
            $type === 'appointment' ? $appointment['appointment_id'] : $appointment['package_appointment_id'],
            $adminTitle,
            $adminMessage
        );
    }
}

// Get regular appointments that are today or tomorrow
$stmt = $conn->prepare("
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.patient_id,
        s.service_name,
        CONCAT(p.first_name, ' ', p.last_name) as patient_name,
        DATEDIFF(a.appointment_date, CURDATE()) as days_until
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN patients p ON a.patient_id = p.patient_id
    WHERE a.status = 'confirmed'
    AND a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    AND NOT EXISTS (
        SELECT 1 FROM notifications n 
        WHERE n.appointment_id = a.appointment_id 
        AND n.type = 'appointment'
        AND DATE(n.created_at) = CURDATE()
    )
");
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create notifications for regular appointments
createReminderNotifications($conn, $appointments, 'appointment');

// Get package appointments that are today or tomorrow
$stmt = $conn->prepare("
    SELECT 
        pa.package_appointment_id,
        pa.appointment_date,
        pa.appointment_time,
        pb.patient_id,
        p.package_name,
        CONCAT(pt.first_name, ' ', pt.last_name) as patient_name,
        DATEDIFF(pa.appointment_date, CURDATE()) as days_until
    FROM package_appointments pa
    JOIN package_bookings pb ON pa.booking_id = pb.booking_id
    JOIN packages p ON pb.package_id = p.package_id
    JOIN patients pt ON pb.patient_id = pt.patient_id
    WHERE pa.status = 'confirmed'
    AND pa.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    AND NOT EXISTS (
        SELECT 1 FROM notifications n 
        WHERE n.appointment_id = pa.package_appointment_id 
        AND n.type = 'package'
        AND DATE(n.created_at) = CURDATE()
    )
");
$stmt->execute();
$package_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create notifications for package appointments
createReminderNotifications($conn, $package_appointments, 'package'); 