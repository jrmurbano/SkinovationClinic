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
$appointment = $stmt->fetch();

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
        $stmt = $conn->prepare('UPDATE appointments SET appointment_date = :new_date, appointment_time = :new_time, status = :status WHERE appointment_id = :appointment_id AND patient_id = :patient_id');
        $stmt->bindValue(':new_date', $new_date, PDO::PARAM_STR);
        $stmt->bindValue(':new_time', $new_time, PDO::PARAM_STR);
        $stmt->bindValue(':status', 'pending', PDO::PARAM_STR); // Set status to pending for admin confirmation
        $stmt->bindValue(':appointment_id', $appointment_id, PDO::PARAM_INT);
        $stmt->bindValue(':patient_id', $_SESSION['patient_id'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            header('Location: my-appointments.php?message=Appointment reschedule request sent. Please wait for admin confirmation.');
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
$appointment = $stmt->fetch();

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
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6f42c1;
        }

        .hero-cover {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            padding: 2rem;
        }

        .service-card {
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 2rem;
            background: white;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .service-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .feature-box {
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            text-align: center;
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .testimonial-item {
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 1rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .testimonial-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .testimonial-item::before {
            content: '"';
            position: absolute;
            top: 1rem;
            left: 1rem;
            font-size: 4rem;
            font-family: Georgia, serif;
            color: var(--primary-color);
            opacity: 0.1;
            line-height: 1;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        .footer a:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
    </style>

</head>
<body>
<div class="container mt-5">
    <?php include '../header.php'; ?>
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
    <?php include '../footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
