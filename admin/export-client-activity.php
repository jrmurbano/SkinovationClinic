<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Check if client_id is provided
if (!isset($_GET['client_id'])) {
    header('Location: clients.php');
    exit;
}

$client_id = intval($_GET['client_id']);

// Get client information
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ? AND is_admin = 0");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: clients.php');
    exit;
}

$client = $result->fetch_assoc();

// Get client activity logs
$stmt = $conn->prepare("
    SELECT
        id,
        activity_type,
        ip_address,
        user_agent,
        login_time,
        logout_time,
        session_duration
    FROM
        user_activity
    WHERE
        user_id = ?
    ORDER BY
        login_time DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$activity_result = $stmt->get_result();

$activities = [];
while ($row = $activity_result->fetch_assoc()) {
    $activities[] = $row;
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="client_' . $client_id . '_activity_' . date('Y-m-d') . '.csv"');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM to fix Excel display issues with special characters
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Set column headers
fputcsv($output, ['Client Name', 'Client Email', 'Activity Type', 'Date & Time', 'IP Address', 'Browser', 'Session Duration', 'Logout Time']);

// Output each row of the data
foreach ($activities as $activity) {
    $session_duration = '';
    if ($activity['session_duration']) {
        $minutes = floor($activity['session_duration'] / 60);
        $seconds = $activity['session_duration'] % 60;
        $session_duration = $minutes . 'm ' . $seconds . 's';
    }

    fputcsv($output, [
        $client['name'],
        $client['email'],
        ucfirst($activity['activity_type']),
        date('Y-m-d H:i:s', strtotime($activity['login_time'])),
        $activity['ip_address'],
        $activity['user_agent'],
        $session_duration,
        $activity['logout_time'] ? date('Y-m-d H:i:s', strtotime($activity['logout_time'])) : 'N/A'
    ]);
}

fclose($output);
exit;
?>
