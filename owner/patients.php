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
        COALESCE(SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END), 0) as completed_appointments,
        COALESCE(SUM(CASE WHEN a.status = 'completed' THEN 
            CASE 
                WHEN a.service_id IS NOT NULL THEN s.price
                WHEN a.product_id IS NOT NULL THEN pr.price
                WHEN a.package_id IS NOT NULL THEN pk.price
            END
        ELSE 0 END), 0) as total_spent
    FROM patients p
    LEFT JOIN appointments a ON p.patient_id = a.patient_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN products pr ON a.product_id = pr.product_id
    LEFT JOIN packages pk ON a.package_id = pk.package_id
    GROUP BY p.patient_id
    ORDER BY p.last_name, p.first_name
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
        .patient-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background: white;
            margin-bottom: 1rem;
        }
        .patient-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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
        .patient-info {
            color: #666;
            font-size: 0.9rem;
        }
        .patient-stats {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-top: 1px solid #eee;
            margin-top: 10px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value-small {
            font-size: 1.2rem;
            font-weight: bold;
            color: #4a148c;
        }
        .stat-label-small {
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'owner_header.php'; ?>
    
    <div class="container-fluid py-4">
        <h1 class="mb-4">Patients Overview</h1>
        
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
        
        <!-- Patients List -->
        <div class="row">
            <div class="col-12">
                <?php foreach ($patients as $patient): ?>
                <div class="patient-card p-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <h5 class="mb-1"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h5>
                            <p class="patient-info mb-0">
                                <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($patient['phone']); ?><br>
                                <?php if (!empty($patient['email'])): ?>
                                <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($patient['email']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="patient-info mb-0">
                                <?php if (!empty($patient['address'])): ?>
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($patient['address']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="patient-stats">
                                <div class="stat-item">
                                    <div class="stat-value-small"><?php echo $patient['total_appointments']; ?></div>
                                    <div class="stat-label-small">Total Appointments</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value-small"><?php echo $patient['completed_appointments']; ?></div>
                                    <div class="stat-label-small">Completed</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value-small">₱<?php echo number_format($patient['total_spent'], 2); ?></div>
                                    <div class="stat-label-small">Total Spent</div>
                                </div>
                            </div>
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