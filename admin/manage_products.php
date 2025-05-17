<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('product_', true) . '.' . $ext;
                    $target = '../assets/img/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $imagePath = 'assets/img/' . $filename;
                    }
                }
                $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, product_image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['product_name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['stock'],
                    $imagePath
                ]);
                $_SESSION['success'] = "Product added successfully!";
                break;

            case 'edit':
                $imagePath = null;
                if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('product_', true) . '.' . $ext;
                    $target = '../assets/img/' . $filename;
                    if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $target)) {
                        $imagePath = 'assets/img/' . $filename;
                        
                        // Delete old image if exists
                        $stmt = $conn->prepare("SELECT product_image FROM products WHERE product_id = ?");
                        $stmt->execute([$_POST['product_id']]);
                        $oldImage = $stmt->fetchColumn();
                        if ($oldImage && file_exists('../' . $oldImage)) {
                            unlink('../' . $oldImage);
                        }
                    }
                }

                if ($imagePath !== null) {
                    $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, product_image = ? WHERE product_id = ?");
                    $stmt->execute([
                        $_POST['product_name'],
                        $_POST['description'],
                        $_POST['price'],
                        $_POST['stock'],
                        $imagePath,
                        $_POST['product_id']
                    ]);
                } else {
                    $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, stock = ? WHERE product_id = ?");
                    $stmt->execute([
                        $_POST['product_name'],
                        $_POST['description'],
                        $_POST['price'],
                        $_POST['stock'],
                        $_POST['product_id']
                    ]);
                }
                $_SESSION['success'] = "Product updated successfully!";
                break;

            case 'delete':
                // Delete the product image if it exists
                $stmt = $conn->prepare("SELECT product_image FROM products WHERE product_id = ?");
                $stmt->execute([$_POST['product_id']]);
                $image = $stmt->fetchColumn();
                if ($image && file_exists('../' . $image)) {
                    unlink('../' . $image);
                }

                $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
                $stmt->execute([$_POST['product_id']]);
                $_SESSION['success'] = "Product deleted successfully!";
                break;
        }
        header('Location: manage_products.php');
        exit();
    }
}

// Get all products
$stmt = $conn->query("SELECT * FROM products ORDER BY product_name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Dashboard</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-shopping-bag"></i> Manage Products</h1>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['product_image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" alt="Product Image" style="max-width:60px;max-height:60px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image"></i> No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-box"></i> Product Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <input type="text" class="form-control" name="product_name" placeholder="Enter product name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" name="description" rows="3" placeholder="Enter product description"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tag"></i> Price (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="price" step="0.01" placeholder="Enter price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-boxes"></i> Stock</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                            <input type="number" class="form-control" name="stock" placeholder="Enter stock quantity" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-image"></i> Image</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewProductImage(event)">
                        </div>
                        <img id="productImagePreview" src="#" alt="Image Preview" style="display:none;max-width:100%;margin-top:10px;" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-box"></i> Product Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <input type="text" class="form-control" name="product_name" id="edit_product_name" placeholder="Enter product name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="Enter product description"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tag"></i> Price (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" placeholder="Enter price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-boxes"></i> Stock</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                            <input type="number" class="form-control" name="stock" id="edit_stock" placeholder="Enter stock quantity" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-image"></i> Image</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="file" class="form-control" name="edit_image" id="edit_image" accept="image/*" onchange="previewEditProductImage(event)">
                        </div>
                        <img id="editProductImagePreview" src="#" alt="Image Preview" style="display:none;max-width:100%;margin-top:10px;" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Product Form -->
<form id="deleteProductForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="product_id" id="delete_product_id">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewProductImage(event) {
    const [file] = event.target.files;
    if (file) {
        const preview = document.getElementById('productImagePreview');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
}

function previewEditProductImage(event) {
    const [file] = event.target.files;
    if (file) {
        const preview = document.getElementById('editProductImagePreview');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
}

function editProduct(product) {
    document.getElementById('edit_product_id').value = product.product_id;
    document.getElementById('edit_product_name').value = product.product_name;
    document.getElementById('edit_description').value = product.description;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock;
    
    // Show image preview if image exists
    const preview = document.getElementById('editProductImagePreview');
    if (product.product_image) {
        preview.src = '../' + product.product_image;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
    document.getElementById('edit_image').value = '';
    
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        document.getElementById('delete_product_id').value = productId;
        document.getElementById('deleteProductForm').submit();
    }
}
</script>
</body>
</html>