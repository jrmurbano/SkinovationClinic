<?php
session_start();

// Prevent browser caching to ensure strict authentication
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../config.php';

// Fetch appointments for the logged-in patient
$patient_id = $_SESSION['patient_id'];
$sql = "SELECT * FROM appointments WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>My Appointments</h1>
    <?php if (count($appointments) > 0): ?>
        <ul>
            <?php foreach ($appointments as $appointment): ?>
                <li><?php echo htmlspecialchars($appointment['date']); ?> - <?php echo htmlspecialchars($appointment['time']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No appointments found.</p>
    <?php endif; ?>
</body>
</html>