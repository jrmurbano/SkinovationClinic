<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get the last check timestamp from session or set to current time
$lastCheck = isset($_SESSION['last_appointment_check']) ? $_SESSION['last_appointment_check'] : date('Y-m-d H:i:s');

// Check for new appointments since last check
$stmt = $conn->prepare("
    SELECT COUNT(*) as new_count 
    FROM appointments 
    WHERE created_at > ? AND status = 'pending'
");
$stmt->execute([$lastCheck]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Update last check timestamp
$_SESSION['last_appointment_check'] = date('Y-m-d H:i:s');

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'hasNew' => $result['new_count'] > 0,
    'count' => $result['new_count']
]); 