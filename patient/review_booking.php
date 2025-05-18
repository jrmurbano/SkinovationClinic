<?php
session_start();
include '../db.php';

// Get booking details from POST
$appointment_date = $_POST['appointment_date'] ?? '';
$appointment_time = $_POST['appointment_time'] ?? '';
$attendant_id = $_POST['attendant_id'] ?? '';
$service_id = isset($_POST['service_id']) && is_numeric($_POST['service_id']) && $_POST['service_id'] > 0 ? (int)$_POST['service_id'] : 
             (isset($_SESSION['selected_service_id']) && is_numeric($_SESSION['selected_service_id']) && $_SESSION['selected_service_id'] > 0 ? (int)$_SESSION['selected_service_id'] : null);
$product_id = isset($_POST['product_id']) && is_numeric($_POST['product_id']) && $_POST['product_id'] > 0 ? (int)$_POST['product_id'] : null;
$package_id = isset($_POST['package_id']) && is_numeric($_POST['package_id']) && $_POST['package_id'] > 0 ? (int)$_POST['package_id'] : null;

// Debug logging
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));
error_log("Service ID: " . $service_id);

// Fetch selected item details
$selected_item = null;
$amount = null;
$label = '';
// Debug: Dump POST data if nothing is found
if (
    (empty($service_id) || !is_numeric($service_id)) &&
    (empty($product_id) || !is_numeric($product_id)) &&
    (empty($package_id) || !is_numeric($package_id))
) {
    echo '<div class="alert alert-danger">No valid service, product, or package ID was sent. Please go back and try again.<br>Debug POST: <pre>';
    print_r($_POST);
    echo '</pre><br>Debug SESSION: <pre>';
    print_r($_SESSION);
    echo '</pre></div>';
    exit();
}
if (!empty($service_id) && is_numeric($service_id)) {
    $stmt = $conn->prepare('SELECT * FROM services WHERE service_id = ?');
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    $selected_item = $stmt->get_result()->fetch_assoc();
    $amount = $selected_item ? $selected_item['price'] : null;
    $label = 'Selected Service:';
    $product_id = $package_id = null;
} elseif (!empty($product_id) && is_numeric($product_id)) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $selected_item = $stmt->get_result()->fetch_assoc();
    $amount = $selected_item ? $selected_item['price'] : null;
    $label = 'Selected Product:';
    $service_id = $package_id = null;
} elseif (!empty($package_id) && is_numeric($package_id)) {
    $stmt = $conn->prepare('SELECT * FROM packages WHERE package_id = ?');
    $stmt->bind_param('i', $package_id);
    $stmt->execute();
    $selected_item = $stmt->get_result()->fetch_assoc();
    $amount = $selected_item ? $selected_item['price'] : null;
    $label = 'Selected Package:';
    $service_id = $product_id = null;
}

// For Go Back, reconstruct the query string
$back_url = 'calendar_view.php';
if ($service_id) $back_url .= '?service_id=' . urlencode($service_id);
if ($product_id) $back_url .= '?product_id=' . urlencode($product_id);
if ($package_id) $back_url .= '?package_id=' . urlencode($package_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .review-card { max-width: 500px; margin: 40px auto; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="review-card card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Review Your Booking</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Selected Date:</label>
                    <p class="form-control-plaintext">
                        <?php echo htmlspecialchars(date('l, F j, Y', strtotime($appointment_date))); ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-clock"></i> Selected Time:</label>
                    <p class="form-control-plaintext">
                        <?php echo htmlspecialchars(date('g:i A', strtotime($appointment_time))); ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-tag"></i> <?php echo $label; ?></label>
                    <p class="form-control-plaintext">
                        <?php 
                        if ($selected_item) {
                            if (isset($selected_item['service_name'])) {
                                echo htmlspecialchars($selected_item['service_name']);
                            } elseif (isset($selected_item['product_name'])) {
                                echo htmlspecialchars($selected_item['product_name']);
                            } elseif (isset($selected_item['package_name'])) {
                                echo htmlspecialchars($selected_item['package_name']);
                            } else {
                                echo '<span class="text-danger">Item name not found.</span>';
                            }
                        } else {
                            echo '<span class="text-danger">Selected item not found. Please go back and try again.</span>';
                        }
                        ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-money-bill-wave"></i> Amount to be paid on clinic premises (₱):</label>
                    <p class="form-control-plaintext">
                        <?php 
                        if ($selected_item && isset($amount)) {
                            echo number_format($amount, 2);
                        } else {
                            echo '<span class="text-danger">Amount not available.</span>';
                        }
                        ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-info-circle"></i> Clinic Appointment Policy:</label>
                    <ul>
                        <li>Appointments can only rescheduled or cancelled at least 1 day before the scheduled date.</li>
                        <li>Failure to attend without prior notice may result in forfeiture of your slot.</li>
                        <li>Please arrive at least 10 minutes before your scheduled appointment.</li>
                        <li>Late arrivals may result in reduced service time or rescheduling.</li>
                    </ul>
                </div>
                <form method="POST" action="process_booking.php">
                    <input type="hidden" name="appointment_date" value="<?php echo htmlspecialchars($appointment_date); ?>">
                    <input type="hidden" name="appointment_time" value="<?php echo htmlspecialchars($appointment_time); ?>">
                    <input type="hidden" name="attendant_id" value="<?php echo htmlspecialchars($attendant_id); ?>">
                    <?php if ($service_id): ?>
                        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id); ?>">
                    <?php elseif ($product_id): ?>
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                    <?php elseif ($package_id): ?>
                        <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($package_id); ?>">
                    <?php endif; ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="policyAgree" name="policyAgree" required>
                        <label class="form-check-label" for="policyAgree">
                            I agree with the clinic's policy in appointments.
                        </label>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo $back_url; ?>" class="btn btn-secondary">Go Back</a>
                        <button type="submit" class="btn btn-success" id="confirmBookingBtn">
                            <?php if ($product_id): ?>
                                Confirm Pre-order
                            <?php elseif ($package_id): ?>
                                Confirm Package Booking
                            <?php else: ?>
                                Confirm Booking
                            <?php endif; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
