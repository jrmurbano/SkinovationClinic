<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}

// Fetch total appointments
$stmt = $conn->query("SELECT COUNT(*) FROM appointments");
$total_appointments = $stmt->fetchColumn();

// Fetch total patients
$stmt = $conn->query("SELECT COUNT(*) FROM patients");
$total_patients = $stmt->fetchColumn();

// Fetch total services
$stmt = $conn->query("SELECT COUNT(*) FROM services");
$total_services = $stmt->fetchColumn();

// Fetch total products
$stmt = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $stmt->fetchColumn();

// Fetch monthly revenue data for chart
$stmt = $conn->query("
    SELECT 
        DATE_FORMAT(a.appointment_date, '%Y-%m') as month,
        SUM(CASE 
            WHEN a.service_id IS NOT NULL THEN s.price 
            WHEN a.product_id IS NOT NULL THEN p.price 
            WHEN a.package_id IS NOT NULL THEN pk.price
        END) as revenue
    FROM appointments a
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN products p ON a.product_id = p.product_id
    LEFT JOIN packages pk ON a.package_id = pk.package_id
    WHERE a.status = 'completed'
    GROUP BY DATE_FORMAT(a.appointment_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$revenue_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch service popularity data
$stmt = $conn->query("
    SELECT 
        s.service_name,
        COUNT(*) as count
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.status = 'completed'
    GROUP BY s.service_id
    ORDER BY count DESC
    LIMIT 5
");
$service_popularity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch product sales data
$stmt = $conn->query("
    SELECT 
        p.product_name,
        COUNT(*) as count
    FROM appointments a
    JOIN products p ON a.product_id = p.product_id
    WHERE a.status = 'completed'
    GROUP BY p.product_id
    ORDER BY count DESC
    LIMIT 5
");
$product_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .stat-card {
            border-left: 4px solid;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        .stat-card.appointments {
            border-left-color: #4a148c;
        }
        .stat-card.patients {
            border-left-color: #2e7d32;
        }
        .stat-card.services {
            border-left-color: #1565c0;
        }
        .stat-card.products {
            border-left-color: #c62828;
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1rem;
        }
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        .chart-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 1rem;
            }
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <?php include 'owner_header.php'; ?>
    
    <div class="container-fluid py-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Dashboard Overview</h1>
            <div class="text-muted">
                <i class="fas fa-calendar-alt me-2"></i>
                <?php echo date('F d, Y'); ?>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="dashboard-card stat-card appointments">
                    <i class="fas fa-calendar-check stat-icon text-primary"></i>
                    <div class="stat-value"><?php echo number_format($total_appointments); ?></div>
                    <div class="stat-label">Total Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card patients">
                    <i class="fas fa-users stat-icon text-success"></i>
                    <div class="stat-value"><?php echo number_format($total_patients); ?></div>
                    <div class="stat-label">Total Patients</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card services">
                    <i class="fas fa-concierge-bell stat-icon text-info"></i>
                    <div class="stat-value"><?php echo number_format($total_services); ?></div>
                    <div class="stat-label">Total Services</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card products">
                    <i class="fas fa-shopping-bag stat-icon text-danger"></i>
                    <div class="stat-value"><?php echo number_format($total_products); ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row g-4">
            <!-- Revenue Chart -->
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="chart-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Monthly Revenue
                    </div>
                    <div class="chart-subtitle">Revenue trends over the last 12 months</div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Service Popularity -->
            <div class="col-lg-4">
                <div class="dashboard-card">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Popular Services
                    </div>
                    <div class="chart-subtitle">Top 5 most requested services</div>
                    <div class="chart-container">
                        <canvas id="serviceChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Product Sales -->
            <div class="col-lg-6">
                <div class="dashboard-card">
                    <div class="chart-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Product Sales
                    </div>
                    <div class="chart-subtitle">Top 5 best-selling products</div>
                    <div class="chart-container">
                        <canvas id="productChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column(array_reverse($revenue_data), 'month')); ?>,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: <?php echo json_encode(array_column(array_reverse($revenue_data), 'revenue')); ?>,
                    borderColor: '#4a148c',
                    backgroundColor: 'rgba(74, 20, 140, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Service Popularity Chart
        new Chart(document.getElementById('serviceChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($service_popularity, 'service_name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($service_popularity, 'count')); ?>,
                    backgroundColor: ['#4a148c', '#6a1b9a', '#8e24aa', '#ab47bc', '#ce93d8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    }
                }
            }
        });

        // Product Sales Chart
        new Chart(document.getElementById('productChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($product_sales, 'product_name')); ?>,
                datasets: [{
                    label: 'Units Sold',
                    data: <?php echo json_encode(array_column($product_sales, 'count')); ?>,
                    backgroundColor: '#4a148c'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 