<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}

// Fetch all completed treatments with related information
$stmt = $conn->query("
    SELECT 
        a.*,
        p.first_name as patient_first_name,
        p.last_name as patient_last_name,
        s.service_name,
        pr.product_name,
        pk.package_name,
        CASE 
            WHEN a.service_id IS NOT NULL THEN s.price
            WHEN a.product_id IS NOT NULL THEN pr.price
            WHEN a.package_id IS NOT NULL THEN pk.price
        END as amount
    FROM appointments a
    LEFT JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN products pr ON a.product_id = pr.product_id
    LEFT JOIN packages pk ON a.package_id = pk.package_id
    WHERE a.status = 'completed'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get treatment statistics
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_treatments,
        COALESCE(SUM(
            CASE 
                WHEN a.service_id IS NOT NULL THEN s.price
                WHEN a.product_id IS NOT NULL THEN pr.price
                WHEN a.package_id IS NOT NULL THEN pk.price
            END
        ), 0) as total_revenue,
        COUNT(DISTINCT a.patient_id) as unique_patients
    FROM appointments a
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN products pr ON a.product_id = pr.product_id
    LEFT JOIN packages pk ON a.package_id = pk.package_id
    WHERE a.status = 'completed'
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment and Product History Log - Owner Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .treatment-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background: white;
            margin-bottom: 1rem;
        }
        .treatment-card:hover {
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
        .treatment-info {
            color: #666;
            font-size: 0.9rem;
        }
        .treatment-type {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 15px;
            background-color: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'owner_header.php'; ?>
    
    <div class="container-fluid py-4">
        <h1 class="mb-4">Treatment and Product History Log</h1>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_treatments']; ?></div>
                    <div class="stat-label">Total Treatments</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">₱<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['unique_patients']; ?></div>
                    <div class="stat-label">Unique Patients</div>
                </div>
            </div>
        </div>
        
        <!-- Treatment History List -->
        <div class="row">
            <div class="col-12">
                <?php foreach ($treatments as $treatment): ?>
                <div class="treatment-card p-3">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <h6 class="mb-1"><?php echo date('M d, Y', strtotime($treatment['appointment_date'])); ?></h6>
                            <p class="mb-0 text-muted"><?php echo date('h:i A', strtotime($treatment['appointment_time'])); ?></p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="mb-1"><?php echo htmlspecialchars($treatment['patient_first_name'] . ' ' . $treatment['patient_last_name']); ?></h6>
                        </div>
                        <div class="col-md-3">
                            <span class="treatment-type">
                                <?php
                                    if ($treatment['service_id']) {
                                        echo 'Service';
                                    } elseif ($treatment['product_id']) {
                                        echo 'Product';
                                    } elseif ($treatment['package_id']) {
                                        echo 'Package';
                                    }
                                ?>
                            </span>
                            <p class="treatment-info mb-0 mt-1">
                                <?php
                                    if ($treatment['service_id']) {
                                        echo htmlspecialchars($treatment['service_name']);
                                    } elseif ($treatment['product_id']) {
                                        echo htmlspecialchars($treatment['product_name']);
                                    } elseif ($treatment['package_id']) {
                                        echo htmlspecialchars($treatment['package_name']);
                                    }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-2">
                            <p class="treatment-info mb-0">
                                <i class="fas fa-comment me-2"></i><?php echo htmlspecialchars($treatment['notes']); ?>
                            </p>
                        </div>
                        <div class="col-md-2 text-end">
                            <h6 class="mb-0">₱<?php echo number_format($treatment['amount'], 2); ?></h6>
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