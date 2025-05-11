<?php
include 'db.php';

// Get all products or filter by category
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

function getAllProducts($conn, $category = 'all')
{
    if ($category == 'all') {
        $sql = 'SELECT * FROM products ORDER BY category, name';
        $stmt = $conn->prepare($sql);
    } else {
        $sql = 'SELECT * FROM products WHERE category = ? ORDER BY name';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $category);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    return $products;
}

$products = getAllProducts($conn, $category);

// Get category name for display
function getCategoryName($category)
{
    $categories = [
        'skincare' => 'Skincare Products',
        'facial' => 'Facial Care',
        'body' => 'Body Care',
        'sunscreen' => 'Sun Protection',
        'all' => 'All Products',
    ];

    return isset($categories[$category]) ? $categories[$category] : 'All Products';
}

$categoryName = getCategoryName($category);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Page Header -->
    <div class="section-header">
        <div class="container">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInDown"><?php echo $categoryName; ?></h1>
            <p class="lead animate__animated animate__fadeInUp">Professional skincare products for your beauty routine
            </p>
        </div>
    </div>

    <div class="container">
        <!-- Category Filter -->
        <div class="category-filter">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="?category=all" class="btn <?php echo $category == 'all' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-grid-3x3-gap me-1"></i>All Products
                        </a>
                        <a href="?category=skincare" class="btn <?php echo $category == 'skincare' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-droplet me-1"></i>Skincare
                        </a>
                        <a href="?category=facial" class="btn <?php echo $category == 'facial' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-star me-1"></i>Facial Care
                        </a>
                        <a href="?category=body" class="btn <?php echo $category == 'body' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-hearts me-1"></i>Body Care
                        </a>
                        <a href="?category=sunscreen" class="btn <?php echo $category == 'sunscreen' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-brightness-high me-1"></i>Sun Protection
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4 my-4">
            <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    No products found in this category.
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-3">
                <div class="product-card card h-100">
                    <?php if ($product['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <?php if (!empty($product['description'])): ?>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($product['description']); ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="price mb-0">₱<?php echo number_format($product['price'], 2); ?></p>
                            <button class="btn btn-purple btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal"
                                data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                data-product-price="<?php echo $product['price']; ?>">
                                <i class="bi bi-cart-plus me-1"></i>Order
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
