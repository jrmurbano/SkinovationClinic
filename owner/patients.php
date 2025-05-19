<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}

// Fetch all patients with their statistics
$stmt = $conn->query("
    SELECT 
        p.*,
        COUNT(DISTINCT a.appointment_id) as total_appointments,
        COUNT(DISTINCT pb.booking_id) as total_packages,
        SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
        SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
        MAX(COALESCE(a.appointment_date, pb.created_at)) as last_visit
    FROM patients p
    LEFT JOIN appointments a ON p.patient_id = a.patient_id
    LEFT JOIN package_bookings pb ON p.patient_id = pb.patient_id
    GROUP BY p.patient_id
    ORDER BY p.created_at DESC
");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient statistics
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_patients,
        COUNT(DISTINCT CASE WHEN a.appointment_id IS NOT NULL THEN p.patient_id END) as active_patients,
        COUNT(DISTINCT CASE WHEN a.appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) THEN p.patient_id END) as recent_patients
    FROM patients p
    LEFT JOIN appointments a ON p.patient_id = a.patient_id
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients Overview - Owner Dashboard</title>
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
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background: white;
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
        .patient-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-active {
            background-color: #28a745;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'owner_header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-users"></i> Patients Overview</h1>
            <div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-file-export"></i> Export Data
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_patients']; ?></div>
                    <div class="stat-label">Total Patients</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['active_patients']; ?></div>
                    <div class="stat-label">Active Patients</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['recent_patients']; ?></div>
                    <div class="stat-label">Recent Patients (30 days)</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Appointments</th>
                                <th>Packages</th>
                                <th>Last Visit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No patients found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?php echo $patient['patient_id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
                                        <div>
                                            <div class="fw-bold">
                                                <?php 
                                                echo htmlspecialchars($patient['first_name'] . ' ' . 
                                                    ($patient['middle_name'] ? $patient['middle_name'] . ' ' : '') . 
                                                    $patient['last_name']); 
                                                ?>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($patient['username']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="fas fa-phone text-muted me-2"></i><?php echo htmlspecialchars($patient['phone']); ?></div>
                                    <?php if (!empty($patient['email'])): ?>
                                    <div><i class="fas fa-envelope text-muted me-2"></i><?php echo htmlspecialchars($patient['email']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-primary mb-1">
                                            Total: <?php echo $patient['total_appointments']; ?>
                                        </span>
                                        <span class="badge bg-success mb-1">
                                            Completed: <?php echo $patient['completed_appointments']; ?>
                                        </span>
                                        <span class="badge bg-danger">
                                            Cancelled: <?php echo $patient['cancelled_appointments']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $patient['total_packages']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($patient['last_visit']) {
                                        echo date('M d, Y', strtotime($patient['last_visit']));
                                    } else {
                                        echo '<span class="text-muted">No visits yet</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="view_patient.php?id=<?php echo $patient['patient_id']; ?>" 
                                       class="btn btn-info btn-sm" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Patient Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="export_patients.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Export Format</label>
                            <select name="format" class="form-select">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Range</label>
                            <div class="row">
                                <div class="col">
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                                <div class="col">
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Export</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 