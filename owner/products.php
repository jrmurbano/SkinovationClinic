<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}

// Fetch all products with their statistics
$stmt = $conn->query("
    SELECT 
        p.*,
        COUNT(DISTINCT a.appointment_id) as total_sales,
        COALESCE(SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END), 0) as completed_sales,
        COALESCE(SUM(CASE WHEN a.status = 'completed' THEN p.price ELSE 0 END), 0) as total_revenue
    FROM products p
    LEFT JOIN appointments a ON p.product_id = a.product_id
    GROUP BY p.product_id
    ORDER BY p.product_name
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Overview - Owner Dashboard</title>
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
        .product-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background: white;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .product-stats {
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
        .product-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .product-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #4a148c;
        }
        .stock-status {
            font-size: 0.9rem;
            padding: 4px 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
        .in-stock {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .low-stock {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        .out-of-stock {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'owner_header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products Overview</h1>
        </div>
        
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3">
                <div class="product-card p-3">
                    <img src="../<?php echo htmlspecialchars($product['product_image'] ?? 'assets/img/default-product.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                         class="img-fluid product-image mb-3">
                    
                    <h5 class="mb-2"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <p class="product-price mb-2">₱<?php echo number_format($product['price'], 2); ?></p>
                    
                    <div class="stock-status <?php 
                        echo $product['stock'] > 10 ? 'in-stock' : 
                            ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock'); 
                    ?>">
                        <?php 
                            echo $product['stock'] > 10 ? 'In Stock' : 
                                ($product['stock'] > 0 ? 'Low Stock' : 'Out of Stock'); 
                        ?>
                    </div>
                    
                    <div class="product-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $product['total_sales']; ?></div>
                            <div class="stat-label">Total Sales</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $product['completed_sales']; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">₱<?php echo number_format($product['total_revenue'], 2); ?></div>
                            <div class="stat-label">Revenue</div>
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