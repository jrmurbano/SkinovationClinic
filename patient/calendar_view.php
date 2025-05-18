<?php
session_start();
include '../db.php';

// Check if patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php?redirect=booking');
    exit();
}

// Handle incoming service or product selection
$selected_service = null;
$selected_product = null;
$selected_package = null;

if (isset($_GET['service_id'])) {
    // Get service details
    $stmt = $conn->prepare('SELECT * FROM services WHERE service_id = ?');
    $stmt->bind_param('i', $_GET['service_id']);
    $stmt->execute();
    $selected_service = $stmt->get_result()->fetch_assoc();
} elseif (isset($_GET['product_id'])) {
    // Get product details
    $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $_GET['product_id']);
    $stmt->execute();
    $selected_product = $stmt->get_result()->fetch_assoc();
} elseif (isset($_GET['package_id'])) {
    // Get package details
    $stmt = $conn->prepare('SELECT * FROM packages WHERE package_id = ?');
    $stmt->bind_param('i', $_GET['package_id']);
    $stmt->execute();
    $selected_package = $stmt->get_result()->fetch_assoc();
}

// Get admin info
$admin = [
    'id' => 1,
    'first_name' => 'Admin',
    'last_name' => 'Skinovation',
];

// Fetch booked slots from both regular appointments and package appointments
$booked_slots = [];

// Get regular appointments
$stmt = $conn->prepare("SELECT appointment_date, appointment_time, attendant_id FROM appointments WHERE status != 'cancelled'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $date = $row['appointment_date'];
    $time = $row['appointment_time'];
    $attendant = $row['attendant_id'];
    if (!isset($booked_slots[$attendant])) {
        $booked_slots[$attendant] = [];
    }
    if (!isset($booked_slots[$attendant][$date])) {
        $booked_slots[$attendant][$date] = [];
    }
    $booked_slots[$attendant][$date][] = $time;
}

// Get package appointments
$stmt = $conn->prepare("SELECT appointment_date, appointment_time, attendant_id FROM package_appointments WHERE status != 'cancelled'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $date = $row['appointment_date'];
    $time = $row['appointment_time'];
    $attendant = $row['attendant_id'];
    if (!isset($booked_slots[$attendant])) {
        $booked_slots[$attendant] = [];
    }
    if (!isset($booked_slots[$attendant][$date])) {
        $booked_slots[$attendant][$date] = [];
    }
    $booked_slots[$attendant][$date][] = $time;
}

// Fetch all attendants with shift ranges and closed days for backend availability logic only
$attendants = [];
$attendant_map = [];
$stmt = $conn->query("SELECT * FROM attendants ORDER BY first_name, last_name");
while ($row = $stmt->fetch_assoc()) {
    $attendants[] = $row;
    $attendant_map[$row['attendant_id']] = $row;
}
$closed_days = [];
$stmt = $conn->query("SELECT * FROM closed_dates");
while ($row = $stmt->fetch_assoc()) {
    $closed_days[] = $row;
}
function is_closed_day($date, $closed_days) {
    foreach ($closed_days as $c) {
        if ($date >= $c['start_date'] && $date <= $c['end_date']) return true;
    }
    return false;
}
function is_attendant_available($attendant, $date, $time, $closed_days) {
    if (is_closed_day($date, $closed_days)) return false;
    // Defensive: check if shift keys exist
    if (!isset($attendant['shift_date_start']) || !isset($attendant['shift_date_end']) || !isset($attendant['shift_time_start']) || !isset($attendant['shift_time_end'])) {
        return false;
    }
    if ($date < $attendant['shift_date_start'] || $date > $attendant['shift_date_end']) return false;
    if ($time < $attendant['shift_time_start'] || $time > $attendant['shift_time_end']) return false;
    return true;
}
// Only keep booked slots for attendants that are available on that date/time
$filtered_booked_slots = [];
foreach ($booked_slots as $attendant_id => $dates) {
    if (!isset($attendant_map[$attendant_id])) continue;
    $attendant = $attendant_map[$attendant_id];
    foreach ($dates as $date => $times) {
        foreach ($times as $time) {
            if (is_attendant_available($attendant, $date, $time, $closed_days)) {
                $filtered_booked_slots[$attendant_id][$date][] = $time;
            }
        }
    }
}
$booked_slots = $filtered_booked_slots;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.js'></script>
    <style>
        .fc-event {
            cursor: pointer;
        }
        .fc-day:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .fc-day-sun {
            background-color: #e9ecef !important; /* Bootstrap gray-100 */
            color: #adb5bd !important; /* Bootstrap gray-500 */
            opacity: 1 !important;
        }
        .fc-day-disabled {
            background-color: #e9ecef !important;
            color: #adb5bd !important;
            opacity: 1 !important;
        }
        .time-slot {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .time-slot.available {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .time-slot.booked {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            cursor: not-allowed;
        }
        .staff-column {
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .staff-name {
            font-weight: bold;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Book an Appointment</h1>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <?php if ($selected_service): ?>
        <div class="alert alert-info">
            <h5>Selected Service: <?php echo htmlspecialchars($selected_service['service_name']); ?></h5>
            <p>Duration: <?php echo $selected_service['duration']; ?> minutes | Price: ₱<?php echo number_format($selected_service['price'], 2); ?></p>
        </div>
        <?php elseif ($selected_product): ?>
        <div class="alert alert-info">
            <h5>Selected Product: <?php echo htmlspecialchars($selected_product['product_name']); ?></h5>
            <p>Price: ₱<?php echo number_format($selected_product['price'], 2); ?></p>
        </div>
        <?php elseif ($selected_package): ?>
        <div class="alert alert-info">
            <h5>Selected Package: <?php echo htmlspecialchars($selected_package['package_name']); ?></h5>
            <p>Price: ₱<?php echo number_format($selected_package['price'], 2); ?></p>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div id="calendar"></div>
            </div>
            <div class="col-md-4" id="timeSlotContainer" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Select Appointment Time</h5>
                        <small id="selectedDate"></small>
                    </div>
                    <div class="card-body">
                        <form id="timeSelectForm">
                            <div class="mb-3">
                                <label for="timeSelect" class="form-label">Available Time Slots</label>
                                <select class="form-select" id="timeSelect" required>
                                    <option value="">Choose a time...</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Continue to Booking</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bookingForm" method="POST" action="process_booking.php">
                    <div class="modal-body">
                        <input type="hidden" name="appointment_date" id="appointment_date">
                        <input type="hidden" name="appointment_time" id="appointment_time">
                        <input type="hidden" name="attendant_id" id="attendant_id">
                        <?php if ($selected_service): ?>
                        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($selected_service['service_id']); ?>">
                        <?php elseif ($selected_product): ?>
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($selected_product['product_id']); ?>">
                        <?php elseif ($selected_package): ?>
                        <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($selected_package['package_id']); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-calendar-alt"></i> Selected Date:</label>
                            <p id="selected_date_display" class="form-control-plaintext" placeholder="Select a date"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-clock"></i> Selected Time:</label>
                            <p id="selected_time_display" class="form-control-plaintext" placeholder="Select a time"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-tag"></i> <?php 
                                if ($selected_service) echo 'Selected Service:';
                                elseif ($selected_product) echo 'Selected Product:';
                                elseif ($selected_package) echo 'Selected Package:';
                                else echo 'Selected Service/Product/Package:';
                            ?></label>
                            <p class="form-control-plaintext" id="selected_item_display" placeholder="Select a service/product/package">
                                <?php if ($selected_service): ?>
                                    <?php echo htmlspecialchars($selected_service['service_name']); ?>
                                <?php elseif ($selected_product): ?>
                                    <?php echo htmlspecialchars($selected_product['product_name']); ?>
                                <?php elseif ($selected_package): ?>
                                    <?php echo htmlspecialchars($selected_package['package_name']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-money-bill-wave"></i> Amount to be paid on clinic premises (₱):</label>
                            <p class="form-control-plaintext" id="selected_amount_display" placeholder="Amount">
                                <?php if ($selected_service): ?>
                                    <?php echo number_format($selected_service['price'], 2); ?>
                                <?php elseif ($selected_product): ?>
                                    <?php echo number_format($selected_product['price'], 2); ?>
                                <?php elseif ($selected_package): ?>
                                    <?php echo number_format($selected_package['price'], 2); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="policyAgree" required>
                            <label class="form-check-label" for="policyAgree">
                                I agree with the clinic's <a href="#" id="policyLink">policy</a> in appointments.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Policy Modal -->
    <div class="modal fade" id="policyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Clinic Appointment Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>Appointments must be rescheduled or cancelled at least 1 day before the scheduled date.</li>
                        <li>Failure to attend without prior notice may result in forfeiture of your slot and/or payment.</li>
                        <li>Please arrive at least 10 minutes before your scheduled appointment.</li>
                        <li>Late arrivals may result in reduced service time or rescheduling.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store selected item info from PHP for JS use
            const selectedItem = {
                name: <?php
                    if ($selected_service) echo json_encode($selected_service['service_name']);
                    elseif ($selected_product) echo json_encode($selected_product['product_name']);
                    elseif ($selected_package) echo json_encode($selected_package['package_name']);
                    else echo 'null';
                ?>,
                amount: <?php
                    if ($selected_service) echo json_encode(number_format($selected_service['price'], 2));
                    elseif ($selected_product) echo json_encode(number_format($selected_product['price'], 2));
                    elseif ($selected_package) echo json_encode(number_format($selected_package['price'], 2));
                    else echo 'null';
                ?>,
                label: <?php
                    if ($selected_service) echo json_encode('Selected Service:');
                    elseif ($selected_product) echo json_encode('Selected Product:');
                    elseif ($selected_package) echo json_encode('Selected Package:');
                    else echo json_encode('Selected Service/Product/Package:');
                ?>
            };

            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                selectAllow: function(selectInfo) {
                    // Disable Sundays
                    var day = selectInfo.start.getDay();
                    return day !== 0;
                },
                dayCellDidMount: function(arg) {
                    // Gray out Sundays like past days, but keep the date visible
                    if (arg.date.getDay() === 0) {
                        arg.el.classList.add('fc-day-sun');
                    }
                    // Gray out past days (except today)
                    var today = new Date();
                    today.setHours(0,0,0,0);
                    var cellDate = new Date(arg.date);
                    cellDate.setHours(0,0,0,0);
                    if (cellDate < today) {
                        arg.el.classList.add('fc-day-disabled');
                    }
                },
                select: function(info) {
                    // Prevent selection on Sundays
                    if (info.start.getDay() === 0) return;
                    showTimeSlots(info.start);
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                validRange: {
                    start: new Date()
                }
            });
            calendar.render();

            // Store the booked slots in JavaScript
            const bookedSlots = <?php echo json_encode($booked_slots); ?>;
            let selectedISODate = null;
            let selectedDateObj = null; // Store the original JS Date object

            function showTimeSlots(date) {
                // Always use ISO string for date
                const formattedDate = date.toISOString().split('T')[0];
                selectedISODate = formattedDate;
                selectedDateObj = date; // Save the original Date object
                const timeSelect = document.getElementById('timeSelect');
                const timeSlotContainer = document.getElementById('timeSlotContainer');
                document.getElementById('selectedDate').textContent = date.toLocaleDateString(
                    'en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                // Clear existing options except the first default one
                timeSelect.innerHTML = '<option value="">Choose a time...</option>';

                // Generate time slots from 10 AM to 5 PM
                for (let hour = 10; hour <= 17; hour++) {
                    const time = `${hour.toString().padStart(2, '0')}:00:00`;
                    const isBooked = bookedSlots[1]?.[formattedDate]?.includes(time);

                    if (!isBooked) {
                        const displayTime = new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: 'numeric',
                            hour12: true
                        });

                        const option = document.createElement('option');
                        option.value = time;
                        option.textContent = displayTime;
                        timeSelect.appendChild(option);
                    }
                }

                timeSlotContainer.style.display = 'block';
            }

            // Handle form submission
            document.getElementById('timeSelectForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const timeSelect = document.getElementById('timeSelect');
                const time = timeSelect.value;

                if (!time) {
                    alert('Please select a time slot');
                    return;
                }

                // Use the stored ISO date
                selectTimeSlot(
                    selectedISODate,
                    time,
                    '1', // Admin ID 
                    'Skinovation Clinic'
                );
            });

            window.selectTimeSlot = function(date, time, attendantId, doctorName) {
                // Set the values in the form with the raw date
                document.getElementById('appointment_date').value = date;
                document.getElementById('appointment_time').value = time;
                document.getElementById('attendant_id').value = attendantId;

                // Update modal with selected item info
                document.querySelector('#selected_item_display').textContent = selectedItem.name || '';
                document.querySelector('#selected_amount_display').textContent = selectedItem.amount || '';
                document.querySelector('#selected_item_display').previousElementSibling.innerHTML = '<i class="fas fa-tag"></i> ' + (selectedItem.label || 'Selected Service/Product/Package:');

                // Use the original Date object for display to avoid timezone issues
                let displayDateObj = selectedDateObj;
                document.getElementById('selected_date_display').textContent = displayDateObj.toLocaleDateString(
                    'en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                // Format the time for display
                const displayTime = new Date('2000-01-01T' + time).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true
                });
                document.getElementById('selected_time_display').textContent = displayTime;

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
                modal.show();
            }

            // Policy modal logic
            document.getElementById('policyLink').addEventListener('click', function(e) {
                e.preventDefault();
                const policyModal = new bootstrap.Modal(document.getElementById('policyModal'));
                policyModal.show();
            });
        });
    </script>
</body>

</html>
