<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php?redirect=booking');
    exit();
}

// Get package details
if (isset($_GET['package_id'])) {
    $package_id = $_GET['package_id'];
    // Redirect to calendar view with package ID
    header('Location: patient/calendar_view.php?package_id=' . $package_id);
    exit();

    if (!$package) {
        header('Location: packages.php');
        exit();
    }
} else {
    header('Location: packages.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_SESSION['patient_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $attendant_id = $_POST['attendant_id']; // Create the package booking
    $stmt = $conn->prepare('INSERT INTO package_bookings (patient_id, package_id, sessions_remaining, valid_until, grace_period_until, created_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), DATE_ADD(NOW(), INTERVAL ? DAY), NOW())');
    $sessions = $package['sessions'];
    $duration = $package['duration_days'];
    $grace_period = $package['grace_period_days'];
    $stmt->bind_param('iiiii', $patient_id, $package_id, $sessions, $duration, $grace_period);

    if ($stmt->execute()) {
        // Book the first appointment
        $booking_id = $conn->insert_id;
        $stmt = $conn->prepare("INSERT INTO package_appointments (booking_id, appointment_date, appointment_time, attendant_id, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param('issi', $booking_id, $appointment_date, $appointment_time, $attendant_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Package booked successfully! Your first appointment has been scheduled.';
            header('Location: patient/index.php');
            exit();
        }
    }

    $_SESSION['error'] = 'Error booking package. Please try again.';
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Package</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-5">Book Package</h1>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $package['name']; ?></h5>
                        <p class="card-text"><?php echo $package['description']; ?></p>
                        <div class="package-details mb-4">
                            <p><strong>Price:</strong> ₱<?php echo number_format($package['price'], 2); ?></p>
                            <p><strong>Sessions:</strong> <?php echo $package['sessions']; ?></p>
                            <p><strong>Valid for:</strong> <?php echo $package['duration_days']; ?> days</p>
                            <p><strong>Grace Period:</strong> <?php echo $package['grace_period_days']; ?> days</p>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="appointment_date" class="form-label">First Appointment Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="appointment_time" class="form-label">Preferred Time</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time"
                                    required>
                            </div>

                            <div class="mb-3"> <label for="attendant_id" class="form-label">Select Attendant</label>
                                <select class="form-select" id="attendant_id" name="attendant_id" required>
                                    <option value="">Choose an attendant</option>
                                    <?php $result = $conn->query('SELECT attendant_id, first_name, last_name FROM attendants');
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['attendant_id'] . "'>Dr. " . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-purple">Book Package</button>
                                <a href="packages.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
