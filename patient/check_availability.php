<?php
session_start();
include 'db.php';

// Prevent browser caching to ensure strict authentication
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get parameters
$attendant_id = isset($_GET['attendant_id']) ? intval($_GET['attendant_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

// Validate parameters
if ($attendant_id <= 0 || empty($date)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

// Generate all possible time slots from 9 AM to 6 PM
$all_slots = [];
$available_slots = [];
$booked_slots = [];

$start = 9; // 9 AM
$end = 18; // 6 PM

for ($hour = $start; $hour < $end; $hour++) {
    $time_slot = sprintf('%02d:00:00', $hour);
    $all_slots[] = $time_slot;

    // Add 30-minute intervals
    $time_slot = sprintf('%02d:30:00', $hour);
    $all_slots[] = $time_slot;
}

// Get booked slots for the selected date and attendant
$stmt = $conn->prepare("
    SELECT appointment_time
    FROM appointments
    WHERE attendant_id = ?
    AND appointment_date = ?    AND status != 'cancelled'
    AND appointment_id != ?
");
$stmt->bind_param('isi', $attendant_id, $date, $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

// Collect booked slots
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = $row['appointment_time'];
}

// Determine available slots
foreach ($all_slots as $slot) {
    if (!in_array($slot, $booked_slots)) {
        $available_slots[] = $slot;
    }
}

// Return available slots as JSON
header('Content-Type: application/json');
echo json_encode([
    'available_slots' => $available_slots,
    'booked_slots' => $booked_slots,
]);
?>
