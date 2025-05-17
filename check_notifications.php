<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['patient_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Get the last notification check time from the session
$lastCheck = $_SESSION['last_notification_check'] ?? date('Y-m-d H:i:s', strtotime('-1 minute'));
$_SESSION['last_notification_check'] = date('Y-m-d H:i:s');

try {
    // Prepare the base query
    $query = "
        SELECT n.*, 
               CASE 
                   WHEN n.type = 'appointment' THEN a.appointment_date
                   WHEN n.type = 'package' THEN pa.appointment_date
               END as appointment_date,
               CASE 
                   WHEN n.type = 'appointment' THEN a.appointment_time
                   WHEN n.type = 'package' THEN pa.appointment_time
               END as appointment_time,
               CASE 
                   WHEN n.type = 'appointment' THEN s.service_name
                   WHEN n.type = 'package' THEN p.package_name
               END as service_name
        FROM notifications n
        LEFT JOIN appointments a ON n.appointment_id = a.appointment_id AND n.type = 'appointment'
        LEFT JOIN package_appointments pa ON n.appointment_id = pa.package_appointment_id AND n.type = 'package'
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN package_bookings pb ON pa.booking_id = pb.booking_id
        LEFT JOIN packages p ON pb.package_id = p.package_id
        WHERE n.created_at > ?
    ";

    // Add user-specific conditions
    if (isset($_SESSION['patient_id'])) {
        $query .= " AND n.patient_id = ?";
        $params = [$lastCheck, $_SESSION['patient_id']];
    } else {
        $query .= " AND n.patient_id IS NULL";
        $params = [$lastCheck];
    }

    $query .= " ORDER BY n.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $newNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $countQuery = "SELECT COUNT(*) FROM notifications WHERE is_read = 0";
    if (isset($_SESSION['patient_id'])) {
        $countQuery .= " AND patient_id = ?";
        $countParams = [$_SESSION['patient_id']];
    } else {
        $countQuery .= " AND patient_id IS NULL";
        $countParams = [];
    }
    
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($countParams);
    $unreadCount = $stmt->fetchColumn();

    // Prepare response
    $response = [
        'hasNew' => !empty($newNotifications),
        'unreadCount' => $unreadCount,
        'notifications' => $newNotifications
    ];

    if (!empty($newNotifications)) {
        $response['title'] = $newNotifications[0]['title'];
        $response['message'] = $newNotifications[0]['message'];
    }

    echo json_encode($response);
} catch (Exception $e) {
    error_log('Error checking notifications: ' . $e->getMessage());
    echo json_encode(['error' => 'Failed to check notifications']);
} 