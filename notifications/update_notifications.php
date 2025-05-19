<?php
session_start();
require_once '../config.php';

// Function to mark notifications as read
function markAsRead($conn, $notification_ids, $user_id, $is_admin = false) {
    try {
        $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
        $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id IN ($placeholders)";
        if (!$is_admin) {
            $sql .= " AND patient_id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $params = array_merge($notification_ids, [$user_id]);
        return $stmt->execute($params);
    } catch (Exception $e) {
        return false;
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                if (isset($_POST['notification_id'])) {
                    $notification_id = [(int)$_POST['notification_id']];
                    $user_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : $_SESSION['patient_id'];
                    $is_admin = isset($_SESSION['admin_id']);
                    
                    if (markAsRead($conn, $notification_id, $user_id, $is_admin)) {
                        $response['success'] = true;
                        $response['message'] = 'Notification marked as read';
                    }
                }
                break;
                
            case 'mark_all_read':
                $user_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : $_SESSION['patient_id'];
                $is_admin = isset($_SESSION['admin_id']);
                
                // Get all unread notification IDs for the user
                $sql = "SELECT notification_id FROM notifications WHERE is_read = 0";
                if (!$is_admin) {
                    $sql .= " AND patient_id = ?";
                }
                $stmt = $conn->prepare($sql);
                $stmt->execute($is_admin ? [] : [$user_id]);
                $notifications = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($notifications)) {
                    if (markAsRead($conn, $notifications, $user_id, $is_admin)) {
                        $response['success'] = true;
                        $response['message'] = 'All notifications marked as read';
                    }
                }
                break;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} 