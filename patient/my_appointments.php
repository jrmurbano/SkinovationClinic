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

$GLOBALS['is_admin'] = false; // Set this flag for proper path resolution
$patient_id = $_SESSION['patient_id'];

// Process appointment cancellation
$cancel_success = false;
$cancel_error = '';

if (isset($_POST['cancel_appointment']) && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    // Check if the appointment belongs to the user
    $stmt = $conn->prepare('SELECT * FROM appointments WHERE appointment_id = ? AND patient_id = ?');
    $stmt->bind_param('ii', $appointment_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $appointment = $result->fetch_assoc();

        // Check if appointment is today and not already cancelled
        $appointment_date = $appointment['appointment_date'];
        $current_date = date('Y-m-d');

        if ($appointment_date == $current_date && $appointment['status'] != 'cancelled' && $appointment['status'] != 'cancellation_pending') {
            // Update appointment status to cancellation_pending            $stmt = $conn->prepare("UPDATE appointments SET status = 'cancellation_pending', updated_at = NOW() WHERE appointment_id = ?");
            $stmt->bind_param('i', $appointment_id);

            if ($stmt->execute()) {
                $cancel_success = true;
            } else {
                $cancel_error = 'An error occurred while requesting cancellation. Please try again.';
            }
        } else {
            $cancel_error = 'You can only request cancellation on the day of your appointment.';
        }
    } else {
        $cancel_error = 'Invalid appointment.';
    }
}

// Process appointment rescheduling
$reschedule_success = false;
$reschedule_error = '';

if (isset($_POST['reschedule_appointment']) && isset($_POST['appointment_id']) && isset($_POST['new_date']) && isset($_POST['new_time'])) {
    $appointment_id = $_POST['appointment_id'];
    $new_date = $_POST['new_date'];
    $new_time = $_POST['new_time'];

    // Check if the appointment belongs to the user
    $stmt = $conn->prepare('SELECT * FROM appointments WHERE id = ? AND patient_id = ?');
    $stmt->bind_param('ii', $appointment_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $appointment = $result->fetch_assoc();

        // Check if appointment is not already cancelled
        if ($appointment['status'] != 'cancelled') {
            // Check if the selected time slot is available
            $dermatologist_id = $appointment['dermatologist_id'];

            $stmt = $conn->prepare("SELECT * FROM appointments WHERE dermatologist_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled' AND id != ?");
            $stmt->bind_param('issi', $dermatologist_id, $new_date, $new_time, $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Update appointment date and time
                $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ?, status = 'pending', updated_at = NOW() WHERE id = ?");
                $stmt->bind_param('ssi', $new_date, $new_time, $appointment_id);

                if ($stmt->execute()) {
                    $reschedule_success = true;
                } else {
                    $reschedule_error = 'An error occurred while rescheduling the appointment. Please try again.';
                }
            } else {
                $reschedule_error = 'The selected time slot is already booked. Please choose another time.';
            }
        } else {
            $reschedule_error = 'Cancelled appointments cannot be rescheduled.';
        }
    } else {
        $reschedule_error = 'Invalid appointment.';
    }
}

// Get user's appointments
$stmt = $conn->prepare("SELECT
    a.appointment_id, a.appointment_date, a.appointment_time, a.status, a.notes,
    s.service_name as service_name, s.price as service_price,
    CONCAT(att.first_name, ' ', att.last_name) as attendant_name,
    att.shift_date
FROM
    appointments a
JOIN
    services s ON a.service_id = s.service_id
LEFT JOIN
    attendants att ON a.attendant_id = att.attendant_id
WHERE
    a.patient_id = ?
ORDER BY
    a.appointment_date DESC, a.appointment_time DESC");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

// Updated function to replace dermatologist_id with attendant_id
function getAvailableTimeSlots($conn, $attendant_id, $date, $appointment_id)
{
    $available_slots = [
        '10:00' => '10:00 AM',
        '11:00' => '11:00 AM',
        '12:00' => '12:00 PM',
        '13:00' => '1:00 PM',
        '14:00' => '2:00 PM',
        '15:00' => '3:00 PM',
        '16:00' => '4:00 PM',
        '17:00' => '5:00 PM',
    ];

    // Get booked slots for the selected date and attendant
    $stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE attendant_id = ? AND appointment_date = ? AND status != 'cancelled' AND status != 'cancellation_pending' AND id != ?");
    $stmt->bind_param('isi', $attendant_id, $date, $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Mark booked slots as unavailable
    while ($row = $result->fetch_assoc()) {
        $time = date('H:i', strtotime($row['appointment_time']));
        if (isset($available_slots[$time])) {
            unset($available_slots[$time]);
        }
    }

    return $available_slots;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            display: flex;
            flex-wrap: wrap;
        }
        .sidebar {
            flex: 0 0 250px;
            background-color: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #dee2e6;
        }
        .content {
            flex: 1;
            padding: 20px;
            margin-left: 200px;
        }
        @media (max-width: 768px) {
            .sidebar {
                flex: 1 0 100%;
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
            .content {
                flex: 1 0 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png" alt="Skinovation Logo" class="img-fluid mb-3">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="content" style="margin-top: 20px;">
            <h1 class="mb-4">My Appointments</h1>

            <?php if ($cancel_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Your cancellation request has been sent successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($cancel_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $cancel_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($reschedule_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Your appointment has been rescheduled successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($reschedule_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $reschedule_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Upcoming Appointments</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $has_upcoming = false;
                            foreach ($appointments as $appointment) {
                                $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                $current_datetime = date('Y-m-d H:i:s');
                            
                                if ($appointment_datetime > $current_datetime && $appointment['status'] != 'cancelled') {
                                    $has_upcoming = true;
                                    break;
                                }
                            }
                            ?>

                            <?php if ($has_upcoming): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Cosmetic Nurse</th>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                            <th>Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                        <?php
                                                $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                                $current_datetime = date('Y-m-d H:i:s');

                                                if ($appointment_datetime > $current_datetime && $appointment['status'] != 'cancelled'):
                                                ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['dermatologist_name']); ?></td>
                                            <td>
                                                <?php
                                                echo date('M d, Y', strtotime($appointment['appointment_date']));
                                                echo ' at ';
                                                echo date('h:i A', strtotime($appointment['appointment_time']));
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                switch ($appointment['status']) {
                                                    case 'pending':
                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                        break;
                                                    case 'confirmed':
                                                        echo '<span class="badge bg-success">Confirmed</span>';
                                                        break;
                                                    case 'cancelled':
                                                        echo '<span class="badge bg-danger">Cancelled</span>';
                                                        break;
                                                    case 'cancellation_pending':
                                                        echo '<span class="badge bg-warning">Cancellation Pending</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>₱<?php echo number_format($appointment['service_price'], 2); ?></td>
                                            <td>
                                                <?php
                                                // Fixed syntax error by closing the previous PHP block
                                                ?>
                                                <?php
                                                $appointment_date = $appointment['appointment_date'];
                                                $current_date = date('Y-m-d');
                                                $can_cancel = $appointment_date == $current_date && $appointment['status'] != 'cancelled' && $appointment['status'] != 'cancellation_pending';
                                                ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rescheduleModal<?php echo $appointment['id']; ?>">
                                                    <i class="bi bi-calendar-plus"></i> Reschedule
                                                </button>
                                                <?php if ($can_cancel): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelModal<?php echo $appointment['id']; ?>">
                                                    <i class="bi bi-x-circle"></i> Cancel
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Reschedule Modal -->
                                        <div class="modal fade" id="rescheduleModal<?php echo $appointment['id']; ?>" tabindex="-1"
                                            aria-labelledby="rescheduleModalLabel<?php echo $appointment['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="rescheduleModalLabel<?php echo $appointment['id']; ?>">Reschedule
                                                            Appointment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>You are rescheduling your appointment for:</p>
                                                        <p><strong><?php echo htmlspecialchars($appointment['service_name']); ?></strong> with <?php echo htmlspecialchars($appointment['dermatologist_name']); ?>
                                                        </p>

                                                        <form method="post" action="">
                                                            <input type="hidden" name="appointment_id"
                                                                value="<?php echo $appointment['id']; ?>">

                                                            <div class="mb-3">
                                                                <label for="new_date" class="form-label">Select New
                                                                    Date</label>
                                                                <input type="date" class="form-control" id="new_date"
                                                                    name="new_date" min="<?php echo date('Y-m-d'); ?>"
                                                                    max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="new_time" class="form-label">Select New
                                                                    Time</label>
                                                                <select class="form-select" id="new_time" name="new_time"
                                                                    required>
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

                                                            <div class="d-grid">
                                                                <button type="submit" name="reschedule_appointment"
                                                                    class="btn btn-primary">Confirm Reschedule</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cancel Modal -->
                                        <div class="modal fade" id="cancelModal<?php echo $appointment['id']; ?>" tabindex="-1"
                                            aria-labelledby="cancelModalLabel<?php echo $appointment['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="cancelModalLabel<?php echo $appointment['id']; ?>">
                                                            Request Cancellation</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to request cancellation for this
                                                            appointment?</p>
                                                        <p><strong>Note:</strong> Your cancellation request will need to be
                                                            confirmed by the admin.</p>
                                                        <p>Service: <?php echo htmlspecialchars($appointment['service_name']); ?></p>
                                                        <p>Date: <?php echo date('F d, Y', strtotime($appointment['appointment_date'])); ?></p>
                                                        <p>Time: <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form method="post" action="">
                                                            <input type="hidden" name="appointment_id"
                                                                value="<?php echo $appointment['id']; ?>">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="cancel_appointment"
                                                                class="btn btn-danger">Request Cancellation</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> You don't have any upcoming appointments.
                                <a href="services.php" class="alert-link">Book an appointment now</a>.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Appointment History</h5>
                            <div>
                                <label for="filter-date" class="form-label mb-0 me-2">Date:</label>
                                <input type="date" id="filter-date" class="form-control d-inline-block" style="width: auto;">
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            $has_history = false;
                            foreach ($appointments as $appointment) {
                                $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                $current_datetime = date('Y-m-d H:i:s');
                            
                                if ($appointment_datetime <= $current_datetime || $appointment['status'] == 'cancelled') {
                                    $has_history = true;
                                    break;
                                }
                            }
                            ?>

                            <?php if ($has_history): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Cosmetic Nurse</th>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                            <th>Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                        <?php
                                                $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                                $current_datetime = date('Y-m-d H:i:s');

                                                if ($appointment_datetime <= $current_datetime || $appointment['status'] == 'cancelled'):
                                                ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                            <td>Dr. <?php echo htmlspecialchars($appointment['dermatologist_name']); ?></td>
                                            <td>
                                                <?php
                                                echo date('M d, Y', strtotime($appointment['appointment_date']));
                                                echo ' at ';
                                                echo date('h:i A', strtotime($appointment['appointment_time']));
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($appointment['status']) {
                                                    case 'pending':
                                                        $status_class = 'bg-warning';
                                                        break;
                                                    case 'confirmed':
                                                        $status_class = 'bg-primary';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                            <td>₱<?php echo number_format($appointment['service_price'], 2); ?></td>
                                            <td>
                                                <?php if ($appointment['status'] == 'completed'): ?>
                                                <a href="leave-feedback.php?appointment_id=<?php echo $appointment['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-star"></i> Leave Feedback
                                                </a>
                                                <?php elseif ($appointment['status'] == 'cancelled'): ?>
                                                <a href="services.php" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-calendar-plus"></i> Book Again
                                                </a>
                                                <?php else: ?>
                                                <span class="text-muted">No actions available</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> You don't have any appointment history yet.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // JavaScript to handle date selection and time slot population
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($appointments as $appointment): ?>
            <?php
                $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                $current_datetime = date('Y-m-d H:i:s');

                if ($appointment_datetime > $current_datetime && $appointment['status'] != 'cancelled'):
                ?>
            const dateInput<?php echo $appointment['id']; ?> = document.getElementById('new_date<?php echo $appointment['id']; ?>');
            const timeSelect<?php echo $appointment['id']; ?> = document.getElementById('new_time<?php echo $appointment['id']; ?>');

            if (dateInput<?php echo $appointment['id']; ?>) {
                dateInput<?php echo $appointment['id']; ?>.addEventListener('change', function() {
                    const selectedDate = this.value;
                    const dermatologistId = <?php echo json_encode($appointment['dermatologist_id']); ?>;
                    const appointmentId = <?php echo $appointment['id']; ?>;

                    // Clear current options
                    timeSelect<?php echo $appointment['id']; ?>.innerHTML =
                        '<option value="">Choose a time slot</option>';

                    if (selectedDate) {
                        // AJAX request to get available time slots
                        fetch(
                                `check_availability.php?dermatologist_id=${dermatologistId}&date=${selectedDate}&appointment_id=${appointmentId}`
                            )
                            .then(response => response.json())
                            .then(data => {
                                // Add available time slots
                                data.available_slots.forEach(slot => {
                                    const option = document.createElement('option');
                                    option.value = slot;
                                    option.textContent = formatTime(slot);
                                    timeSelect<?php echo $appointment['id']; ?>.appendChild(option);

                                });
                            })
                            .catch(error => console.error('Error checking availability:', error));
                    }
                });
            }
            <?php endif; ?>
            <?php endforeach; ?>

            // Function to format time from 24-hour to 12-hour format
            function formatTime(timeString) {
                const [hours, minutes] = timeString.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${minutes} ${ampm}`;
            }
        });
    </script>
</body>

</html>
