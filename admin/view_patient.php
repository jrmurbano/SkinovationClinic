<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Check if patient ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: patients.php');
    exit();
}

$patient_id = clean($_GET['id']);

// Fetch patient details
$stmt = $conn->prepare("
    SELECT * FROM patients 
    WHERE patient_id = ?
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: patients.php');
    exit();
}

// Fetch patient's appointments
$stmt = $conn->prepare("
    SELECT a.*, s.service_name, s.price
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$patient_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch patient's package bookings
$stmt = $conn->prepare("
    SELECT pb.*, p.package_name, p.price, p.sessions,
           COUNT(pa.package_appointment_id) as appointments_made,
           MAX(pa.appointment_date) as last_appointment
    FROM package_bookings pb
    JOIN packages p ON pb.package_id = p.package_id
    LEFT JOIN package_appointments pa ON pb.booking_id = pa.booking_id
    WHERE pb.patient_id = ?
    GROUP BY pb.booking_id
    ORDER BY pb.created_at DESC
");
$stmt->execute([$patient_id]);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_appointments = count($appointments);
$completed_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'completed'; }));
$cancelled_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'cancelled'; }));
$total_spent = array_sum(array_map(function($a) { return $a['price']; }, $appointments)) + 
               array_sum(array_map(function($p) { return $p['price']; }, $packages));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user"></i> Patient Details</h1>
                <div>
                    <a href="patients.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Patients
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Patient Information -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Patient Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Name:</th>
                                    <td>
                                        <?php 
                                        echo clean($patient['first_name'] . ' ' . 
                                            ($patient['middle_name'] ? $patient['middle_name'] . ' ' : '') . 
                                            $patient['last_name']); 
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Username:</th>
                                    <td><?php echo clean($patient['username']); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?php echo clean($patient['phone']); ?></td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td><?php echo clean($patient['address'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Joined:</th>
                                    <td><?php echo date('M d, Y', strtotime($patient['created_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded">
                                        <h6>Total Appointments</h6>
                                        <h3 class="text-primary"><?php echo $total_appointments; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded">
                                        <h6>Completed</h6>
                                        <h3 class="text-success"><?php echo $completed_appointments; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h6>Cancelled</h6>
                                        <h3 class="text-danger"><?php echo $cancelled_appointments; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h6>Total Spent</h6>
                                        <h3 class="text-info">₱<?php echo number_format($total_spent, 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments and Packages -->
                <div class="col-md-8">
                    <!-- Appointments -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Appointments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($appointments)): ?>
                            <p class="text-center text-muted">No appointments found</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Service</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                            <td><?php echo clean($appointment['service_name']); ?></td>
                                            <td>₱<?php echo number_format($appointment['price'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $appointment['status'] === 'completed' ? 'success' : 
                                                        ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                                ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                <form method="POST" action="update_appointment_status.php" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                    <button type="submit" name="action" value="confirm" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                    <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Package Bookings -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Package Bookings</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($packages)): ?>
                            <p class="text-center text-muted">No package bookings found</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Package</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($packages as $package): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($package['created_at'])); ?></td>
                                            <td><?php echo clean($package['package_name']); ?></td>
                                            <td>₱<?php echo number_format($package['price'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 