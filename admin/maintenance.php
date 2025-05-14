<?php
// Maintenance Page
// This page allows the management of services, packages, and products.

include '../header.php';
?>

<div class="container mt-5">
    <h1 class="text-center">Maintenance</h1>
    <div class="row">
        <div class="col-md-4">
            <a href="../services.php" class="btn btn-primary btn-block">Manage Services</a>
        </div>
        <div class="col-md-4">
            <a href="../packages.php" class="btn btn-primary btn-block">Manage Packages</a>
        </div>
        <div class="col-md-4">
            <a href="../products.php" class="btn btn-primary btn-block">Manage Products</a>
        </div>
    </div>
</div>

<?php
include '../footer.php';
?>
