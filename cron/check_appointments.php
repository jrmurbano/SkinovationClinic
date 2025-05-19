<?php
require_once '../config.php';

// Function to create notification
function createReminderNotification($conn, $type, $appointment_id, $title, $message, $patient_id) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (type, appointment_id, title, message, patient_id, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        return $stmt->execute([$type, $appointment_id, $title, $message, $patient_id]);
    } catch (Exception $e) {
        error_log("Error creating reminder notification: " . $e->getMessage());
        return false;
    }
}

try {
    // Get tomorrow's date
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $today = date('Y-m-d');
    
    // Check regular appointments
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.patient_id,
            COALESCE(s.service_name, p.product_name) as item_name,
            'appointment' as type
        FROM appointments a
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN products p ON a.product_id = p.product_id
        WHERE a.status = 'confirmed'
        AND a.appointment_date IN (?, ?)
        AND NOT EXISTS (
            SELECT 1 FROM notifications n 
            WHERE n.appointment_id = a.appointment_id 
            AND n.type = 'reminder'
            AND DATE(n.created_at) = CURRENT_DATE
        )
    ");
    $stmt->execute([$tomorrow, $today]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check package appointments
    $stmt = $conn->prepare("
        SELECT 
            pa.package_appointment_id as appointment_id,
            pa.appointment_date,
            pa.appointment_time,
            pb.patient_id,
            p.package_name as item_name,
            'package' as type
        FROM package_appointments pa
        JOIN package_bookings pb ON pa.booking_id = pb.booking_id
        JOIN packages p ON pb.package_id = p.package_id
        WHERE pa.status = 'confirmed'
        AND pa.appointment_date IN (?, ?)
        AND NOT EXISTS (
            SELECT 1 FROM notifications n 
            WHERE n.appointment_id = pa.package_appointment_id 
            AND n.type = 'reminder'
            AND DATE(n.created_at) = CURRENT_DATE
        )
    ");
    $stmt->execute([$tomorrow, $today]);
    $package_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine all appointments
    $all_appointments = array_merge($appointments, $package_appointments);
    
    // Process each appointment
    foreach ($all_appointments as $appointment) {
        $is_tomorrow = $appointment['appointment_date'] === $tomorrow;
        $title = $is_tomorrow ? "Appointment Tomorrow" : "Appointment Today";
        $when = $is_tomorrow ? "tomorrow" : "today";
        
        $message = sprintf(
            "Reminder: Your %s for %s is scheduled for %s at %s",
            $appointment['type'],
            $appointment['item_name'],
            $when,
            date('g:i A', strtotime($appointment['appointment_time']))
        );
        
        // Create notification for patient
        createReminderNotification(
            $conn,
            'reminder',
            $appointment['appointment_id'],
            $title,
            $message,
            $appointment['patient_id']
        );
        
        // Create notification for admin
        $admin_message = sprintf(
            "Reminder: %s appointment for %s is scheduled for %s at %s",
            ucfirst($appointment['type']),
            $appointment['item_name'],
            $when,
            date('g:i A', strtotime($appointment['appointment_time']))
        );
        
        createReminderNotification(
            $conn,
            'reminder',
            $appointment['appointment_id'],
            $title,
            $admin_message,
            1 // Admin ID
        );
    }
    
    echo "Reminder notifications sent successfully\n";
    
} catch (Exception $e) {
    error_log("Error in appointment reminder script: " . $e->getMessage());
    echo "Error processing reminders: " . $e->getMessage() . "\n";
} 