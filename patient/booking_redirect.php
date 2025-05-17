<?php
session_start();
include '../db.php';

// Debugging log to trace redirection flow
error_log("Booking Redirect: Session data: " . print_r($_SESSION, true));
error_log("Booking Redirect: GET parameters: " . print_r($_GET, true));

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php?redirect=booking');
    exit();
}

// Fetch the clicked item details
$item_type = $_GET['type'] ?? '';
$item_id = $_GET['id'] ?? '';

if ($item_type && $item_id) {
    if ($item_type === 'service') {
        $stmt = $conn->prepare("SELECT service_name, price FROM services WHERE service_id = ?");
    } elseif ($item_type === 'package') {
        $stmt = $conn->prepare("SELECT package_name, price FROM packages WHERE package_id = ?");
    } elseif ($item_type === 'product') {
        $stmt = $conn->prepare("SELECT product_name, price FROM products WHERE product_id = ?");
    } else {
        die('Invalid item type.');
    }

    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('Item not found.');
    }

    $item = $result->fetch_assoc();
} else {
    die('Invalid request.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Booking Details</h1>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title">Item: <?php echo htmlspecialchars($item['service_name'] ?? $item['package_name'] ?? $item['product_name']); ?></h3>
                <p><strong>Price:</strong> ₱<?php echo number_format($item['price'], 2); ?></p>
            </div>
        </div>

        <form action="process_booking.php" method="POST">
            <input type="hidden" name="item_type" value="<?php echo htmlspecialchars($item_type); ?>">
            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item_id); ?>">

            <div class="mb-3">
                <label for="appointment_date" class="form-label">Preferred Date</label>
                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                <small class="form-text text-muted">View available dates on the <a href="calendar_view.php" target="_blank">calendar</a>.</small>
            </div>

            <div class="mb-3">
                <label for="appointment_time" class="form-label">Preferred Time</label>
                <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Check Availability</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
