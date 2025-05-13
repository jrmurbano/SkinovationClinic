<?php
session_start();
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

$appointment_id = $_GET['id'] ?? null;
if (!$appointment_id) {
    die('Invalid appointment ID.');
}

// Updated to use PDO for database operations
$stmt = $conn->prepare('SELECT appointment_date FROM appointments WHERE appointment_id = :appointment_id AND patient_id = :patient_id');
$stmt->bindValue(':appointment_id', $appointment_id, PDO::PARAM_INT);
$stmt->bindValue(':patient_id', $_SESSION['patient_id'], PDO::PARAM_INT);
$stmt->execute();
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($appointment) {
    $appointment_date = strtotime($appointment['appointment_date']);
    $current_date = strtotime(date('Y-m-d'));

    if ($appointment_date - $current_date <= 0) { // Appointment is today or in the past
        die('Rescheduling is not allowed on the day of the appointment or for past appointments.');
    }
} else {
    die('Appointment not found.');
}

// Refactored to use PDO for all database operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = $_POST['appointment_date'] ?? null;
    $new_time = $_POST['appointment_time'] ?? null;

    if ($new_date && $new_time) {
        $stmt = $conn->prepare('UPDATE appointments SET appointment_date = :new_date, appointment_time = :new_time WHERE appointment_id = :appointment_id AND patient_id = :patient_id');
        $stmt->bindValue(':new_date', $new_date, PDO::PARAM_STR);
        $stmt->bindValue(':new_time', $new_time, PDO::PARAM_STR);
        $stmt->bindValue(':appointment_id', $appointment_id, PDO::PARAM_INT);
        $stmt->bindValue(':patient_id', $_SESSION['patient_id'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            header('Location: my-appointments.php?message=Appointment rescheduled successfully.');
            exit();
        } else {
            $error = 'Failed to reschedule appointment. Please try again.';
        }
    } else {
        $error = 'Please provide both date and time.';
    }
}

$stmt = $conn->prepare('SELECT appointment_date, appointment_time FROM appointments WHERE appointment_id = :appointment_id AND patient_id = :patient_id');
$stmt->bindValue(':appointment_id', $appointment_id, PDO::PARAM_INT);
$stmt->bindValue(':patient_id', $_SESSION['patient_id'], PDO::PARAM_INT);
$stmt->execute();
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    die('Appointment not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Reschedule Appointment</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"> <?php echo $error; ?> </div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="appointment_date" class="form-label">New Date</label>
            <input type="date" class="form-control" id="appointment_date" name="appointment_date" value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="appointment_time" class="form-label">New Time</label>
            <input type="time" class="form-control" id="appointment_time" name="appointment_time" value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Reschedule</button>
        <a href="my-appointments.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
