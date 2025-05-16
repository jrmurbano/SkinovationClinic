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
        /* Ensure the content is responsive and doesn't hide behind the sidebar */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            margin-left: 250px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<?php
include 'admin_sidebar.php';
?>

<div class="container mt-5">
    <h1 class="text-center">Maintenance</h1>
    <p class="text-center">Manage all services, packages, and products from this page.</p>
    <div class="row">
        <div class="col-md-4">
            <a href="manage_services.php" class="btn btn-primary btn-block">Services</a>
        </div>
        <div class="col-md-4">
            <a href="manage_packages.php" class="btn btn-primary btn-block">Packages</a>
        </div>
        <div class="col-md-4">
            <a href="manage_products.php" class="btn btn-primary btn-block">Products</a>
        </div>
    </div>
</div>
</body>
</html>