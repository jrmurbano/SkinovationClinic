<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch history log entries
$history_stmt = $conn->query("SELECT * FROM history_log ORDER BY datetime DESC LIMIT 100");
$history_logs = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <style>
        body {
            background-image: url('https://cdn.vectorstock.com/i/500p/99/24/molecules-inside-bubbles-on-blue-background-water-vector-53889924.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
    </style>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <h1 class="mb-4"><i class="fas fa-cogs"></i> Maintenance</h1>
            
            <div class="row g-4">
                <!-- Services Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-spa fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">Manage Services</h5>
                            <p class="card-text">Add, edit, or remove services offered by the clinic.</p>
                            <a href="manage_services.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Manage Services
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Packages Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-box-open fa-3x mb-3 text-success"></i>
                            <h5 class="card-title">Manage Packages</h5>
                            <p class="card-text">Create and manage service packages and promotions.</p>
                            <a href="manage_packages.php" class="btn btn-success">
                                <i class="fas fa-edit"></i> Manage Packages
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Products Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-bag fa-3x mb-3 text-info"></i>
                            <h5 class="card-title">Manage Products</h5>
                            <p class="card-text">Manage clinic products.</p>
                            <a href="manage_products.php" class="btn btn-info">
                                <i class="fas fa-edit"></i> Manage Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- History Log Table -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="mb-3"><i class="fas fa-history"></i> Treatment & Product History Log</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                        <th>Performed By</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($history_logs)): ?>
                                        <?php foreach ($history_logs as $log): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($log['datetime']) ?></td>
                                                <td><?= htmlspecialchars($log['type']) ?></td>
                                                <td><?= htmlspecialchars($log['name']) ?></td>
                                                <td><?= htmlspecialchars($log['action']) ?></td>
                                                <td><?= htmlspecialchars($log['performed_by']) ?></td>
                                                <td><?= htmlspecialchars($log['details']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted">No history log entries found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
