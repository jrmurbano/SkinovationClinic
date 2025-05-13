<?php
session_start();
include '../db.php';

// Prevent browser caching to ensure strict authentication
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if service_id is provided
if (!isset($_GET['service_id'])) {
    header('Location: ../services.php');
    exit();
}

$service_id = $_GET['service_id'];

// Get service details
$stmt = $conn->prepare('SELECT * FROM services WHERE service_id = ?');
$stmt->bind_param('i', $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: ../services.php');
    exit();
}

$service = $result->fetch_assoc();

// Get all attendants
$stmt = $conn->prepare('SELECT * FROM attendants');
$stmt->execute();
$attendants_result = $stmt->get_result();

$all_attendants = [];
while ($row = $attendants_result->fetch_assoc()) {
    $all_attendants[] = $row;
}

// Process booking form
$booking_success = false;
$booking_error = '';

// Process pending booking if user is logged in and has a pending booking
if (isset($_SESSION['user_id']) && isset($_SESSION['pending_booking'])) {
    $pending_booking = $_SESSION['pending_booking']; // Validate inputs
    if (!empty($pending_booking['attendant_id']) && !empty($pending_booking['appointment_date']) && !empty($pending_booking['appointment_time'])) {
        // Insert booking into database        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, service_id, attendant_id, appointment_date, appointment_time, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param('iiiss', $_SESSION['user_id'], $pending_booking['service_id'], $pending_booking['attendant_id'], $pending_booking['appointment_date'], $pending_booking['appointment_time']);

        if ($stmt->execute()) {
            $booking_success = true;
            // Clear pending booking from session
            unset($_SESSION['pending_booking']);
            // Redirect to my-appointments page
            header('Location: my-appointments.php');
            exit();
        } else {
            $booking_error = 'Booking failed. Please try again.';
        }
    }

    // Clear pending booking if there was an error
    unset($_SESSION['pending_booking']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store booking details in session regardless of login status
    $_SESSION['pending_booking'] = [
        'service_id' => $service_id,
        'attendant_id' => $_POST['attendant_id'],
        'appointment_date' => $_POST['appointment_date'],
        'appointment_time' => $_POST['appointment_time'],
    ];

    // Redirect to login page
    header('Location: ../login.php?redirect=booking');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #f8f5ff;
        }

        .card {
            border: 1px solid rgba(111, 66, 193, 0.1);
            background-color: white;
        }

        .card-header {
            background-color: #6f42c1 !important;
            color: white;
        }

        .btn-primary {
            background-color: #6f42c1;
            border-color: #6f42c1;
        }

        .btn-primary:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
        }

        .btn-outline-secondary {
            color: #6f42c1;
            border-color: #6f42c1;
        }

        .btn-outline-secondary:hover {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }

        .service-details h4 {
            color: #6f42c1;
        }

        .service-details .fw-bold {
            color: #6f42c1;
        }

        .form-label {
            color: #6f42c1;
            font-weight: 500;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        }

        .alert-info {
            background-color: #f0e6ff;
            border-color: #6f42c1;
            color: #6f42c1;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if ($booking_success): ?>
                <div class="alert alert-success text-center">
                    <h4><i class="bi bi-check-circle"></i> Booking Successful!</h4>
                    <p>Your appointment has been booked successfully. You will receive a confirmation email shortly.</p>
                    <a href="my-appointments.php" class="btn btn-primary mt-3">View My Appointments</a>
                </div>
                <?php else: ?>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Book Appointment</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($booking_error): ?>
                        <div class="alert alert-danger"><?php echo $booking_error; ?></div>
                        <?php endif; ?>

                        <div class="service-details mb-4">
                            <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Price: ₱<?php echo number_format($service['price'], 2); ?></span>
                                <span>Duration: <?php echo $service['duration']; ?> minutes</span>
                            </div>
                        </div>

                        <?php if (!isset($_SESSION['user_id'])): ?>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="appointment_date" class="form-label">Select Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date"
                                    min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="appointment_time" class="form-label">Select Time</label>
                                <select class="form-select" id="appointment_time" name="appointment_time" required>
                                    <option value="">Choose a time slot</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Book Appointment</button>
                                <a href="services.php" class="btn btn-outline-secondary">Back to Services</a>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p>Please wait while we process your booking...</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // JavaScript to check availability of time slots based on selected date and attendant
        document.addEventListener('DOMContentLoaded', function() {
            const attendantSelect = document.getElementById('attendant_id');
            const dateInput = document.getElementById('appointment_date');
            const timeSelect = document.getElementById('appointment_time');

            function checkAvailability() {
                const attendantId = attendantSelect.value;
                const date = dateInput.value;

                if (!attendantId || !date) return;
                fetch(`check_availability.php?attendant_id=${attendantId}&date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        // Reset all options
                        Array.from(timeSelect.options).forEach(option => {
                            option.disabled = false;
                        });

                        // Disable booked slots
                        data.booked_slots.forEach(slot => {
                            Array.from(timeSelect.options).forEach(option => {
                                if (option.value === slot) {
                                    option.disabled = true;
                                }
                            });
                        });
                    })
                    .catch(error => console.error('Error checking availability:', error));
            }
            attendantSelect.addEventListener('change', checkAvailability);
            dateInput.addEventListener('change', checkAvailability);
        });
    </script>
</body>

</html>
