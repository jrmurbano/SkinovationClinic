<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Automatically update all attendants' shift_date to today and set shift_time to 10:00
$today = date('Y-m-d');
$conn->query("UPDATE attendants SET shift_date = '$today', shift_time = '10:00'");

// Handle attendant add
if (isset($_POST['add_attendant'])) {
    $first_name = trim($_POST['attendant_first_name']);
    $last_name = trim($_POST['attendant_last_name']);
    $shift_date = $_POST['shift_date'];
    $shift_time = $_POST['shift_time'];
    if ($first_name && $last_name && $shift_date && $shift_time) {
        $stmt = $conn->prepare("INSERT INTO attendants (first_name, last_name, shift_date, shift_time, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$first_name, $last_name, $shift_date, $shift_time]);
    }
    header('Location: settings.php');
    exit();
}
// Handle closed day add
if (isset($_POST['add_closed'])) {
    $start = $_POST['closed_start'];
    $end = $_POST['closed_end'];
    $reason = trim($_POST['closed_reason']);
    if ($start && $end && $reason) {
        $stmt = $conn->prepare("INSERT INTO closed_dates (start_date, end_date, reason, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$start, $end, $reason]);
    }
    header('Location: settings.php');
    exit();
}
// Handle attendant remove
if (isset($_POST['remove_attendant']) && isset($_POST['attendant_id'])) {
    $attendant_id = intval($_POST['attendant_id']);
    $stmt = $conn->prepare("DELETE FROM attendants WHERE attendant_id = ?");
    $stmt->execute([$attendant_id]);
    header('Location: settings.php');
    exit();
}
// Handle attendant edit
if (isset($_POST['edit_attendant']) && isset($_POST['attendant_id'])) {
    $attendant_id = intval($_POST['attendant_id']);
    $first_name = trim($_POST['edit_first_name']);
    $last_name = trim($_POST['edit_last_name']);
    $shift_date = $_POST['edit_shift_date'];
    $shift_time = $_POST['edit_shift_time'];
    if ($first_name && $last_name && $shift_date && $shift_time) {
        $stmt = $conn->prepare("UPDATE attendants SET first_name=?, last_name=?, shift_date=?, shift_time=?, updated_at=NOW() WHERE attendant_id=?");
        $stmt->execute([$first_name, $last_name, $shift_date, $shift_time, $attendant_id]);
    }
    header('Location: settings.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <style>
        body {
            background-image: url('https://cdn.vectorstock.com/i/500p/99/24/molecules-inside-bubbles-on-blue-background-water-vector-53889924.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
    </style>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container mt-5">
            <h1><i class="fas fa-cogs"></i> Settings</h1>
            <p>UI for settings will go here.</p>
        </div>
        <div class="container mt-5 border border-2 border-primary rounded p-4 mb-5">
            <label>Create account for admin: </label> <br>
            <a href="register_admin.php" class="btn btn-secondary">Create Account</a>
        </div>
        <!-- Attendant Management -->
        <div class="container mt-4 border border-2 border-success rounded p-4 mb-5 bg-white bg-opacity-75">
            <h4 class="mb-3"><i class="fas fa-user-nurse"></i> Manage Attendants</h4>
            <form method="POST" action="settings.php" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" name="attendant_first_name" class="form-control" placeholder="First Name" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="attendant_last_name" class="form-control" placeholder="Last Name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shift Date</label>
                    <input type="date" name="shift_date" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shift Time</label>
                    <input type="time" name="shift_time" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" name="add_attendant" class="btn btn-success"><i class="fas fa-plus"></i> Add Attendant</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Shift Date</th>
                            <th>Shift Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->query("SELECT * FROM attendants ORDER BY first_name, last_name");
                        $attendants = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($attendants as $i => $att): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($att['first_name'] . ' ' . $att['last_name']) ?></td>
                                <td><?= htmlspecialchars($att['shift_date']) ?></td>
                                <td><?= htmlspecialchars($att['shift_time']) ?></td>
                                <td>
                                    <!-- Edit Button triggers modal -->
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editAttendantModal<?= $att['attendant_id'] ?>"><i class="fas fa-edit"></i></button>
                                    <!-- Remove Button -->
                                    <form method="POST" action="settings.php" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this attendant?');">
                                        <input type="hidden" name="attendant_id" value="<?= $att['attendant_id'] ?>">
                                        <button type="submit" name="remove_attendant" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editAttendantModal<?= $att['attendant_id'] ?>" tabindex="-1" aria-labelledby="editAttendantLabel<?= $att['attendant_id'] ?>" aria-hidden="true">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <form method="POST" action="settings.php">
                                            <div class="modal-header">
                                              <h5 class="modal-title" id="editAttendantLabel<?= $att['attendant_id'] ?>">Edit Attendant</h5>
                                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                              <input type="hidden" name="attendant_id" value="<?= $att['attendant_id'] ?>">
                                              <div class="mb-3">
                                                <label class="form-label">First Name</label>
                                                <input type="text" name="edit_first_name" class="form-control" value="<?= htmlspecialchars($att['first_name']) ?>" required>
                                              </div>
                                              <div class="mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" name="edit_last_name" class="form-control" value="<?= htmlspecialchars($att['last_name']) ?>" required>
                                              </div>
                                              <div class="mb-3">
                                                <label class="form-label">Shift Date</label>
                                                <input type="date" name="edit_shift_date" class="form-control" value="<?= htmlspecialchars($att['shift_date']) ?>" required>
                                              </div>
                                              <div class="mb-3">
                                                <label class="form-label">Shift Time</label>
                                                <input type="time" name="edit_shift_time" class="form-control" value="<?= htmlspecialchars($att['shift_time']) ?>" required>
                                              </div>
                                            </div>
                                            <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                              <button type="submit" name="edit_attendant" class="btn btn-primary">Save Changes</button>
                                            </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Closed Days Management -->
        <div class="container mt-4 border border-2 border-danger rounded p-4 mb-5 bg-white bg-opacity-75">
            <h4 class="mb-3"><i class="fas fa-calendar-times"></i> Set Closed Clinic Days</h4>
            <form method="POST" action="settings.php" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="date" name="closed_start" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <input type="date" name="closed_end" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="closed_reason" class="form-control" placeholder="Reason (e.g. Holiday)" required>
                </div>
                <div class="col-12">
                    <button type="submit" name="add_closed" class="btn btn-danger"><i class="fas fa-plus"></i> Add Closed Day</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->query("SELECT * FROM closed_dates ORDER BY start_date DESC");
                        $closed = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($closed as $i => $c): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($c['start_date']) ?></td>
                                <td><?= htmlspecialchars($c['end_date']) ?></td>
                                <td><?= htmlspecialchars($c['reason']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- TODO: Add calendar view for admin here -->
        <div class="container mt-5 mb-5">
            <h4 class="mb-3"><i class="fas fa-calendar-alt"></i> Attendant Calendar View</h4>
            <iframe src="calendar_admin.php" style="width:100%;height:600px;border:none;"></iframe>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
