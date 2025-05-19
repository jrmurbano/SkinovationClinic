<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}

// Fetch all services
$stmt = $conn->query("
    SELECT 
        s.service_id,
        s.service_name,
        s.description,
        s.price,
        s.image,
        COUNT(a.appointment_id) as total_bookings,
        COALESCE(SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END), 0) as completed_bookings
    FROM services s
    LEFT JOIN appointments a ON s.service_id = a.service_id
    GROUP BY s.service_id, s.service_name, s.description, s.price, s.image
    ORDER BY s.service_name
");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Overview - Owner Dashboard</title>
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
        .service-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background: white;
            height: 100%;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .service-image {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .service-stats {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-top: 1px solid #eee;
            margin-top: 10px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: #4a148c;
        }
        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }
        .service-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .service-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #4a148c;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'owner_header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Services Overview</h1>
        </div>
        
        <div class="row g-4">
            <?php foreach ($services as $service): ?>
            <div class="col-md-4 col-lg-3">
                <div class="service-card p-3">
                    <img src="../<?php echo htmlspecialchars($service['image'] ?? 'assets/img/default-service.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($service['service_name']); ?>" 
                         class="img-fluid service-image mb-3">
                    
                    <h5 class="mb-2"><?php echo htmlspecialchars($service['service_name']); ?></h5>
                    <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                    <p class="service-price mb-2">₱<?php echo number_format($service['price'], 2); ?></p>
                    
                    <div class="service-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $service['total_bookings']; ?></div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $service['completed_bookings']; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php 
                                    echo $service['total_bookings'] > 0 
                                        ? round(($service['completed_bookings'] / $service['total_bookings']) * 100) 
                                        : 0;
                                ?>%
                            </div>
                            <div class="stat-label">Completion Rate</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 