<?php
session_start();
require_once 'config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['patient_id']);
$headerFile = $isLoggedIn ? 'patient/patient_header.php' : 'header.php';
function getAllProducts($conn)
{
    try {
        $sql = 'SELECT * FROM products ORDER BY product_name';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching products: ' . $e->getMessage());
        return [];
    }
}

$products = getAllProducts($conn);

// Removed category name logic
$categoryName = 'All Products';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .services-header h1 {
            text-align: center;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
        }
    </style>
</head>

<body>
    <?php include 'shared_header.php'; ?>

    <!-- Page Header -->
    <div class="section-header">
        <div class="container text-center mt-5 mb-5">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInDown"><?php echo $categoryName; ?></h1>
            <p class="lead animate__animated animate__fadeInUp">Professional skincare products for your beauty routine
            </p>
        </div>
    </div>

    <div class="container">
        <!-- Products Grid -->
        <div class="row g-4 my-4">
            <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    No products found.
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-4">
                <div class="product-card card h-100">
                    <?php if (!empty($product['product_image'])): ?>
                    <img src="<?php echo htmlspecialchars($product['product_image'] ?? ''); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"> <?php echo htmlspecialchars($product['product_name'] ?? ''); ?> </h5>
                        <?php if (!empty($product['description'])): ?>
                        <p class="card-text text-muted"> <?php echo htmlspecialchars($product['description']); ?> </p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="price mb-0">₱<?php echo number_format($product['price'], 2); ?></p>
                            <button class="btn btn-purple btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal"
                                data-product-id="<?php echo $product['product_id']; ?>" data-product-name="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>"
                                data-product-price="<?php echo $product['price']; ?>">
                                <i class="bi bi-cart-plus me-1"></i>Pre-Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel">Order Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="orderForm">
                        <input type="hidden" id="productId" name="product_id">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product</label>
                            <input type="text" class="form-control" id="productName" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1"
                                value="1">
                        </div>
                        <div class="mb-3">
                            <label for="totalPrice" class="form-label">Total Price</label>
                            <input type="text" class="form-control" id="totalPrice" readonly>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-purple" id="submitOrder">Place Order</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle product order modal
        const orderModal = document.getElementById('orderModal');
        if (orderModal) {
            orderModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const productId = button.getAttribute('data-product-id');
                const productName = button.getAttribute('data-product-name');
                const productPrice = parseFloat(button.getAttribute('data-product-price'));

                const modalProductId = orderModal.querySelector('#productId');
                const modalProductName = orderModal.querySelector('#productName');
                const modalQuantity = orderModal.querySelector('#quantity');
                const modalTotalPrice = orderModal.querySelector('#totalPrice');

                modalProductId.value = productId;
                modalProductName.value = productName;
                updateTotalPrice(productPrice, modalQuantity.value);

                modalQuantity.addEventListener('change', () => {
                    updateTotalPrice(productPrice, modalQuantity.value);
                });

                function updateTotalPrice(price, quantity) {
                    const total = price * quantity;
                    modalTotalPrice.value = '₱' + total.toFixed(2);
                }
            });
        }
    </script>
</body>

</html>
