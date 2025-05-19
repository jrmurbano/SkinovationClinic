<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['patient_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['patient_id'];
    
    // Get unread notifications count
    $count_sql = "SELECT COUNT(*) FROM notifications WHERE is_read = 0";
    if (!$is_admin) {
        $count_sql .= " AND patient_id = ?";
    }
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($is_admin ? [] : [$user_id]);
    $unread_count = $count_stmt->fetchColumn();
    
    // Get recent notifications
    $sql = "SELECT 
                n.*,
                CASE 
                    WHEN n.type = 'appointment' THEN a.appointment_date
                    WHEN n.type = 'package' THEN pa.appointment_date
                END as appointment_date
            FROM notifications n
            LEFT JOIN appointments a ON n.appointment_id = a.appointment_id AND n.type = 'appointment'
            LEFT JOIN package_appointments pa ON n.appointment_id = pa.package_appointment_id AND n.type = 'package'
            WHERE 1=1";
            
    if (!$is_admin) {
        $sql .= " AND n.patient_id = ?";
    }
    $sql .= " ORDER BY n.created_at DESC LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($is_admin ? [] : [$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format notifications
    $formatted_notifications = array_map(function($n) {
        $n['created_at_formatted'] = date('M j, Y g:i A', strtotime($n['created_at']));
        if (isset($n['appointment_date'])) {
            $n['appointment_date_formatted'] = date('M j, Y', strtotime($n['appointment_date']));
        }
        return $n;
    }, $notifications);
    
    echo json_encode([
        'success' => true,
        'unread_count' => $unread_count,
        'notifications' => $formatted_notifications
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notifications'
    ]);
} 