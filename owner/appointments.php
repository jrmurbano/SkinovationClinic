<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}

// Fetch all appointments with related information
$stmt = $conn->query("
    SELECT 
        a.*,
        p.first_name as patient_first_name,
        p.last_name as patient_last_name,
        p.phone as patient_phone,
        s.service_name,
        pr.product_name,
        pk.package_name
    FROM appointments a
    LEFT JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN products pr ON a.product_id = pr.product_id
    LEFT JOIN packages pk ON a.package_id = pk.package_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get appointment statistics
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_appointments,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_appointments
    FROM appointments
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Overview - Owner Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://cdn.vectorstock.com/i/500p/99/24/molecules-inside-bubbles-on-blue-background-water-vector-53889924.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .appointment-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background: white;
            margin-bottom: 1rem;
        }
        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: white;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #4a148c;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'owner_header.php'; ?>
    
    <div class="container-fluid py-4">
        <h1 class="mb-4">Appointments Overview</h1>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_appointments']; ?></div>
                    <div class="stat-label">Total Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['completed_appointments']; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['pending_appointments']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['cancelled_appointments']; ?></div>
                    <div class="stat-label">Cancelled</div>
                </div>
            </div>
        </div>
        
        <!-- Appointments List -->
        <div class="row">
            <div class="col-12">
                <?php foreach ($appointments as $appointment): ?>
                <div class="appointment-card p-3">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <h6 class="mb-1"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></h6>
                            <p class="mb-0 text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="mb-1"><?php echo htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']); ?></h6>
                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($appointment['patient_phone']); ?></p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="mb-1">Service/Product</h6>
                            <p class="mb-0">
                                <?php
                                    if ($appointment['service_id']) {
                                        echo htmlspecialchars($appointment['service_name']);
                                    } elseif ($appointment['product_id']) {
                                        echo htmlspecialchars($appointment['product_name']);
                                    } elseif ($appointment['package_id']) {
                                        echo htmlspecialchars($appointment['package_name']);
                                    }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-3 text-end">
                            <span class="status-badge status-<?php echo strtolower($appointment['status'] ?? 'pending'); ?>">
                                <?php echo ucfirst($appointment['status'] ?? 'pending'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 