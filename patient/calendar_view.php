<?php
session_start();
include '../db.php';

// Check if patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php?redirect=booking');
    exit();
}

// Fetch staff members
$staff = [];
$stmt = $conn->prepare("SELECT id, first_name, last_name FROM attendants");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $staff[] = $row;
}

// Fetch booked slots for the selected day
$booked_slots = [];
if (isset($_GET['date'])) {
    $selected_date = $_GET['date'];
    $stmt = $conn->prepare("SELECT appointment_time, attendant_id FROM appointments WHERE appointment_date = ? AND status != 'cancelled'");
    $stmt->bind_param('s', $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $booked_slots[$row['attendant_id']][] = $row['appointment_time'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .calendar-day {
            padding: 20px;
            text-align: center;
            border: 1px solid #ddd;
            cursor: pointer;
        }
        .calendar-day:hover {
            background-color: #f0f0f0;
        }
        .time-slot {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            margin: 5px;
            cursor: pointer;
        }
        .time-slot.booked {
            background-color: #f8d7da;
            color: #721c24;
            cursor: not-allowed;
        }
        .time-slot.available {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Appointment Calendar</h1>

        <!-- Calendar View -->
        <div class="calendar mb-5">
            <?php for ($i = 1; $i <= 30; $i++): ?>
                <div class="calendar-day" onclick="selectDate('<?php echo date('Y-m-d', strtotime("+{$i} days")); ?>')">
                    <?php echo date('D, M j', strtotime("+{$i} days")); ?>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Time Slots View -->
        <?php if (isset($selected_date)): ?>
        <h2 class="mb-4">Available Slots for <?php echo date('F j, Y', strtotime($selected_date)); ?></h2>
        <div class="d-flex">
            <?php foreach ($staff as $member): ?>
            <div class="flex-grow-1">
                <h4 class="text-center">Dr. <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h4>
                <?php for ($hour = 9; $hour <= 17; $hour++): ?>
                    <?php $time = sprintf('%02d:00:00', $hour); ?>
                    <div class="time-slot <?php echo in_array($time, $booked_slots[$member['id']] ?? []) ? 'booked' : 'available'; ?>">
                        <?php echo date('h:i A', strtotime($time)); ?>
                    </div>
                <?php endfor; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function selectDate(date) {
            window.location.href = '?date=' + date;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
