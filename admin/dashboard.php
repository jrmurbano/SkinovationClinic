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

// Get appointment statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'today' => 0
];

// Get total appointments
$stmt = $conn->query("SELECT COUNT(*) as count FROM appointments");
$stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get appointments by status
$stmt = $conn->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['status']] = $row['count'];
}

// Get today's appointments
$stmt = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()");
$stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent appointments
$stmt = $conn->query("
    SELECT a.*, p.first_name, p.last_name, s.service_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN services s ON a.service_id = s.service_id
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>

body {
            background-image: url('https://cdn.vectorstock.com/i/500p/99/24/molecules-inside-bubbles-on-blue-background-water-vector-53889924.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
    /* Card styling */
    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Statistics cards */
    .card.bg-primary {
        background: linear-gradient(135deg, #4a148c 0%, #6a1b9a 100%) !important;
    }

    .card.bg-warning {
        background: linear-gradient(135deg, #ff6f00 0%, #ff8f00 100%) !important;
    }

    .card.bg-success {
        background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%) !important;
    }

    .card.bg-info {
        background: linear-gradient(135deg, #01579b 0%, #0277bd 100%) !important;
    }

    .card-title {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .card-text {
        color: #ffffff !important;
        font-weight: 700;
        font-size: 2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* Table styling */
    .table {
        color: #333333;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #4a148c;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
    }

    .table tbody td {
        vertical-align: middle;
    }

    /* Badge styling */
    .badge {
        padding: 0.5em 0.8em;
        font-weight: 500;
    }

    .badge.bg-success {
        background-color: #2e7d32 !important;
    }

    .badge.bg-warning {
        background-color: #ff6f00 !important;
        color: #ffffff !important;
    }

    .badge.bg-danger {
        background-color: #c62828 !important;
    }

    /* Page title */
    h1 {
        color: #4a148c;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* Card header */
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
    }

    .card-header h5 {
        color: #4a148c;
        font-weight: 600;
        margin: 0;
    }

    /* Recent appointments table */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(74, 20, 140, 0.05);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(74, 20, 140, 0.1);
    }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Appointments</h5>
                            <h2 class="card-text"><?php echo $stats['total']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Pending Appointments</h5>
                            <h2 class="card-text"><?php echo $stats['pending']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Today's Appointments</h5>
                            <h2 class="card-text"><?php echo $stats['today']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Confirmed Appointments</h5>
                            <h2 class="card-text"><?php echo $stats['confirmed']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Appointments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_appointments as $appointment): ?>
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
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
