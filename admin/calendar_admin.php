<?php
// Admin Calendar View: Attendants as columns, time as rows
session_start();
include '../config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
// Get all attendants
$stmt = $conn->query("SELECT * FROM attendants ORDER BY shift_date, shift_time");
$attendants = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get all time slots (10:00 to 18:00)
$time_slots = [];
for ($h = 10; $h <= 18; $h++) {
    $time_slots[] = sprintf('%02d:00:00', $h);
}
// Get today's date
$today = date('Y-m-d');
// Get all appointments for today
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_date = ?");
$stmt->execute([$today]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Map: [attendant_id][time] = appointment
$calendar = [];
foreach ($appointments as $a) {
    $calendar[$a['attendant_id']][$a['appointment_time']] = $a;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Calendar View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .calendar-table th, .calendar-table td { text-align: center; vertical-align: middle; }
        .calendar-table th { background: #6f42c1; color: #fff; }
        .calendar-table td.booked { background: #f8d7da; color: #721c24; }
        .calendar-table td.available { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4"><i class="fas fa-calendar-alt"></i> Attendant Schedule (Today)</h2>
    <div class="table-responsive">
        <table class="table table-bordered calendar-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <?php foreach ($attendants as $att): ?>
                        <th><?= htmlspecialchars($att['first_name'] . ' ' . $att['last_name']) ?><br><small><?= htmlspecialchars($att['shift_date']) ?> <?= htmlspecialchars($att['shift_time']) ?></small></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($time_slots as $slot): ?>
                <tr>
                    <td><?= date('g:i A', strtotime($slot)) ?></td>
                    <?php foreach ($attendants as $att): ?>
                        <?php
                        $is_booked = isset($calendar[$att['attendant_id']][$slot]);
                        ?>
                        <td class="<?= $is_booked ? 'booked' : 'available' ?>">
                            <?= $is_booked ? 'Booked' : 'Available' ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
