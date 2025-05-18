<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO packages (package_name, description, price, sessions, duration_days, grace_period_days) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['package_name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['sessions'],
                    $_POST['duration_days'],
                    $_POST['grace_period_days']
                ]);
                $_SESSION['success'] = "Package added successfully!";
                break;

            case 'edit':
                $stmt = $conn->prepare("UPDATE packages SET package_name = ?, description = ?, price = ?, sessions = ?, duration_days = ?, grace_period_days = ? WHERE package_id = ?");
                $stmt->execute([
                    $_POST['package_name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['sessions'],
                    $_POST['duration_days'],
                    $_POST['grace_period_days'],
                    $_POST['package_id']
                ]);
                $_SESSION['success'] = "Package updated successfully!";
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM packages WHERE package_id = ?");
                $stmt->execute([$_POST['package_id']]);
                $_SESSION['success'] = "Package deleted successfully!";
                break;

            case 'confirm_package':
                $stmt = $conn->prepare("
                    SELECT pa.*, pb.patient_id, p.package_name, pt.first_name, pt.last_name
                    FROM package_appointments pa 
                    JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
                    JOIN packages p ON pb.package_id = p.package_id
                    JOIN patients pt ON pb.patient_id = pt.patient_id
                    WHERE pa.package_appointment_id = ?
                ");
                $stmt->execute([$_POST['package_appointment_id']]);
                $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($appointment) {
                    $stmt = $conn->prepare("UPDATE package_appointments SET status = 'confirmed' WHERE package_appointment_id = ?");
                    $stmt->execute([$_POST['package_appointment_id']]);

                    // Create notification for patient
                    $title = "Package Appointment Confirmed";
                    $message = sprintf(
                        "Your package appointment for %s on %s at %s has been confirmed.",
                        $appointment['package_name'],
                        date('F j, Y', strtotime($appointment['appointment_date'])),
                        date('g:i A', strtotime($appointment['appointment_time']))
                    );
                    createNotification($conn, 'package', $_POST['package_appointment_id'], $title, $message, $appointment['patient_id']);
                }
                $_SESSION['success'] = "Package appointment confirmed successfully!";
                break;

            case 'cancel_package':
                $stmt = $conn->prepare("
                    SELECT pa.*, pb.patient_id, p.package_name, pt.first_name, pt.last_name
                    FROM package_appointments pa 
                    JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
                    JOIN packages p ON pb.package_id = p.package_id
                    JOIN patients pt ON pb.patient_id = pt.patient_id
                    WHERE pa.package_appointment_id = ?
                ");
                $stmt->execute([$_POST['package_appointment_id']]);
                $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($appointment) {
                    $stmt = $conn->prepare("UPDATE package_appointments SET status = 'cancelled' WHERE package_appointment_id = ?");
                    $stmt->execute([$_POST['package_appointment_id']]);

                    // Create notification for patient
                    $title = "Package Appointment Cancelled";
                    $message = sprintf(
                        "Your package appointment for %s on %s at %s has been cancelled.",
                        $appointment['package_name'],
                        date('F j, Y', strtotime($appointment['appointment_date'])),
                        date('g:i A', strtotime($appointment['appointment_time']))
                    );
                    createNotification($conn, 'package', $_POST['package_appointment_id'], $title, $message, $appointment['patient_id']);
                }
                $_SESSION['success'] = "Package appointment cancelled successfully!";
                break;
        }
        header('Location: manage_packages.php');
        exit();
    }
}

// Get all packages
$stmt = $conn->query("SELECT * FROM packages ORDER BY package_name");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-box-open"></i> Manage Packages</h1>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                    <i class="fas fa-plus"></i> Add New Package
                </button>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Package Name</th>
                                    <th>Price</th>
                                    <th>Sessions</th>
                                    <th>Duration</th>
                                    <th>Grace Period</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packages as $package): ?>
                                <tr>
                                    <td><?php echo clean($package['package_name']); ?></td>
                                    <td>₱<?php echo number_format($package['price'], 2); ?></td>
                                    <td><?php echo $package['sessions']; ?></td>
                                    <td><?php echo $package['duration_days']; ?> days</td>
                                    <td><?php echo $package['grace_period_days']; ?> days</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="editPackage(<?php echo htmlspecialchars(json_encode($package)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deletePackage(<?php echo $package['package_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Pending Package Appointments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php
                        $stmt = $conn->query("
                            SELECT pa.*, pb.patient_id, p.package_name, pt.first_name, pt.last_name, att.first_name as attendant_first_name, att.last_name as attendant_last_name
                            FROM package_appointments pa 
                            JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
                            JOIN packages p ON pb.package_id = p.package_id
                            JOIN patients pt ON pb.patient_id = pt.patient_id
                            JOIN attendants att ON pa.attendant_id = att.attendant_id
                            WHERE pa.status = 'pending'
                            ORDER BY pa.appointment_date ASC, pa.appointment_time ASC
                        ");
                        $pending_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php if (empty($pending_appointments)): ?>
                            <p class="text-center text-muted mb-0">No pending package appointments</p>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Package</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Attendant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo clean($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                        <td><?php echo clean($appointment['package_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo clean($appointment['attendant_first_name'] . ' ' . $appointment['attendant_last_name']); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="confirm_package">
                                                <input type="hidden" name="package_appointment_id" value="<?php echo $appointment['package_appointment_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to confirm this package appointment?')">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="cancel_package">
                                                <input type="hidden" name="package_appointment_id" value="<?php echo $appointment['package_appointment_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this package appointment?')">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Package Modal -->
<div class="modal fade" id="addPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Package Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box-open"></i></span>
                            <input type="text" class="form-control" name="package_name" placeholder="Enter package name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" name="description" rows="3" placeholder="Enter package description"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="price" step="0.01" placeholder="Enter price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Number of Sessions</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                            <input type="number" class="form-control" name="sessions" placeholder="Enter number of sessions" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (days)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                            <input type="number" class="form-control" name="duration_days" placeholder="Enter duration in days" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grace Period (days)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-hourglass-half"></i></span>
                            <input type="number" class="form-control" name="grace_period_days" placeholder="Enter grace period in days" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Add Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Package Modal -->
<div class="modal fade" id="editPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="package_id" id="edit_package_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-box-open"></i> Package Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box-open"></i></span>
                            <input type="text" class="form-control" name="package_name" id="edit_package_name" placeholder="Enter package name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="Enter package description"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tag"></i> Price (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" placeholder="Enter price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-list-ol"></i> Number of Sessions</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                            <input type="number" class="form-control" name="sessions" id="edit_sessions" placeholder="Enter number of sessions" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-calendar-day"></i> Duration (days)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                            <input type="number" class="form-control" name="duration_days" id="edit_duration_days" placeholder="Enter duration in days" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-hourglass-half"></i> Grace Period (days)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-hourglass-half"></i></span>
                            <input type="number" class="form-control" name="grace_period_days" id="edit_grace_period_days" placeholder="Enter grace period in days" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Package Form -->
<form id="deletePackageForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="package_id" id="delete_package_id">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editPackage(package) {
    document.getElementById('edit_package_id').value = package.package_id;
    document.getElementById('edit_package_name').value = package.package_name;
    document.getElementById('edit_description').value = package.description;
    document.getElementById('edit_price').value = package.price;
    document.getElementById('edit_sessions').value = package.sessions;
    document.getElementById('edit_duration_days').value = package.duration_days;
    document.getElementById('edit_grace_period_days').value = package.grace_period_days;
    
    new bootstrap.Modal(document.getElementById('editPackageModal')).show();
}

function deletePackage(packageId) {
    if (confirm('Are you sure you want to delete this package?')) {
        document.getElementById('delete_package_id').value = packageId;
        document.getElementById('deletePackageForm').submit();
    }
}
</script>
</body>
</html> 