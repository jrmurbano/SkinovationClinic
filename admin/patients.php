<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle patient deletion with confirmation
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = clean($_GET['delete']);
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Delete package appointments first
        $stmt = $conn->prepare("DELETE pa FROM package_appointments pa 
                              INNER JOIN package_bookings pb ON pa.booking_id = pb.booking_id 
                              WHERE pb.patient_id = ?");
        $stmt->execute([$id]);
        
        // Delete package bookings
        $stmt = $conn->prepare("DELETE FROM package_bookings WHERE patient_id = ?");
        $stmt->execute([$id]);
        
        // Delete appointments
        $stmt = $conn->prepare("DELETE FROM appointments WHERE patient_id = ?");
        $stmt->execute([$id]);
        
        // Finally delete the patient
        $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id = ?");
        $stmt->execute([$id]);
        
        // Commit transaction
        $conn->commit();
        
        header('Location: patients.php?success=deleted');
        exit();
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error = "Error deleting patient: " . $e->getMessage();
    }
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - Admin Dashboard</title>
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
        .patient-actions .btn {
            margin: 0 2px;
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
<body>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users"></i> Manage Patients</h1>
                <div>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-file-export"></i> Export Data
                    </button>
                </div>
            </div>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                switch ($_GET['success']) {
                    case 'deleted':
                        echo 'Patient and all related records deleted successfully!';
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

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
                                    <td colspan="8" class="text-center">No patients found</td>
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
                                                    echo clean($patient['first_name'] . ' ' . 
                                                        ($patient['middle_name'] ? $patient['middle_name'] . ' ' : '') . 
                                                        $patient['last_name']); 
                                                    ?>
                                                </div>
                                                <small class="text-muted"><?php echo clean($patient['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-phone text-muted me-2"></i><?php echo clean($patient['phone']); ?></div>
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
                                  
                                    <td class="patient-actions">
                                        <a href="view_patient.php?id=<?php echo $patient['patient_id']; ?>" 
                                           class="btn btn-info btn-sm" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_patient.php?id=<?php echo $patient['patient_id']; ?>" 
                                           class="btn btn-primary btn-sm"
                                           title="Edit Patient">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="patients.php?delete=<?php echo $patient['patient_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this patient? This will also delete all their appointments and package bookings.')"
                                           title="Delete Patient">
                                            <i class="fas fa-trash"></i>
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
