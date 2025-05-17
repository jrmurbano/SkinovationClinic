<?php
session_start();
include '../../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Fetch all appointments with patient and service details
$stmt = $conn->query("
    SELECT a.*, p.first_name, p.last_name, p.phone, s.service_name, s.price
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN services s ON a.service_id = s.service_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_appointments = count($appointments);
$pending_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'pending'; }));
$confirmed_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'confirmed'; }));
$cancelled_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'cancelled'; }));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Management</title>
    <link rel="icon" type="image/png" href="../../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/admin.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include '../admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-calendar-alt"></i> Appointments Management</h1>
                <div>
                    <a href="manage.php" class="btn btn-primary">
                        <i class="fas fa-tasks"></i> Manage Appointments
                    </a>
                    <a href="calendar.php" class="btn btn-info">
                        <i class="fas fa-calendar"></i> View Calendar
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Appointments</h5>
                            <h2 class="mb-0"><?php echo $total_appointments; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2 class="mb-0"><?php echo $pending_appointments; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Confirmed</h5>
                            <h2 class="mb-0"><?php echo $confirmed_appointments; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Cancelled</h5>
                            <h2 class="mb-0"><?php echo $cancelled_appointments; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Appointments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_appointments = array_slice($appointments, 0, 5);
                                foreach ($recent_appointments as $appointment): 
                                ?>
                                <tr>
                                    <td><?php echo clean($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                    <td><?php echo clean($appointment['service_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="manage.php" class="btn btn-primary">View All Appointments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Add real-time notification check
function checkNewAppointments() {
    fetch('check_new.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                // Show notification
                const notification = new Notification('New Appointment', {
                    body: 'A new appointment has been booked!',
                    icon: '../../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png'
                });
                
                // Reload the page to show new appointment
                location.reload();
            }
        });
}

// Check for new appointments every 30 seconds
setInterval(checkNewAppointments, 30000);

// Request notification permission
if (Notification.permission !== 'granted') {
    Notification.requestPermission();
}
</script>
</body>
</html> 