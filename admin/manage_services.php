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
                    $filename = uniqid('service_', true) . '.' . $ext;
                    $target = '../assets/img/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $imagePath = 'assets/img/' . $filename;
                    }
                }
                $stmt = $conn->prepare("INSERT INTO services (service_name, description, price, duration, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['service_name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['duration'],
                    $_POST['category_id'],
                    $imagePath
                ]);
                $_SESSION['success'] = "Service added successfully!";
                break;

            case 'edit':
                $imagePath = null;
                if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('service_', true) . '.' . $ext;
                    $target = '../assets/img/' . $filename;
                    if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $target)) {
                        $imagePath = 'assets/img/' . $filename;
                        
                        // Delete old image if exists
                        $stmt = $conn->prepare("SELECT image FROM services WHERE service_id = ?");
                        $stmt->execute([$_POST['service_id']]);
                        $oldImage = $stmt->fetchColumn();
                        if ($oldImage && file_exists('../' . $oldImage)) {
                            unlink('../' . $oldImage);
                        }
                    }
                }

                if ($imagePath !== null) {
                    $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, price = ?, duration = ?, category_id = ?, image = ? WHERE service_id = ?");
                    $stmt->execute([
                        $_POST['service_name'],
                        $_POST['description'],
                        $_POST['price'],
                        $_POST['duration'],
                        $_POST['category_id'],
                        $imagePath,
                        $_POST['service_id']
                    ]);
                } else {
                    $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, price = ?, duration = ?, category_id = ? WHERE service_id = ?");
                    $stmt->execute([
                        $_POST['service_name'],
                        $_POST['description'],
                        $_POST['price'],
                        $_POST['duration'],
                        $_POST['category_id'],
                        $_POST['service_id']
                    ]);
                }
                $_SESSION['success'] = "Service updated successfully!";
                break;

            case 'delete':
                // Delete the service image if it exists
                $stmt = $conn->prepare("SELECT image FROM services WHERE service_id = ?");
                $stmt->execute([$_POST['service_id']]);
                $image = $stmt->fetchColumn();
                if ($image && file_exists('../' . $image)) {
                    unlink('../' . $image);
                }

                $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
                $stmt->execute([$_POST['service_id']]);
                $_SESSION['success'] = "Service deleted successfully!";
                break;
        }
        header('Location: manage_services.php');
        exit();
    }
}

// Get all services
$stmt = $conn->query("
    SELECT s.*, c.name as category_name 
    FROM services s 
    LEFT JOIN service_categories c ON s.category_id = c.category_id 
    ORDER BY s.service_name
");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories
$stmt = $conn->query("SELECT * FROM service_categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Admin Dashboard</title>
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
                <h1><i class="fas fa-spa"></i> Manage Services</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="fas fa-plus"></i> Add New Service
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
                                    <th>Service Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($service['image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($service['image']); ?>" alt="Service Image" style="max-width:60px;max-height:60px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image"></i> No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo clean($service['service_name']); ?></td>
                                    <td><?php echo clean($service['category_name']); ?></td>
                                    <td>₱<?php echo number_format($service['price'], 2); ?></td>
                                    <td><?php echo $service['duration']; ?> minutes</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteService(<?php echo $service['service_id']; ?>)">
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

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-spa"></i> Service Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-spa"></i></span>
                            <input type="text" class="form-control" name="service_name" placeholder="Enter service name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-list"></i> Category</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list"></i></span>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo clean($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" name="description" rows="3" placeholder="Enter service description"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tag"></i> Price (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-peso-sign"></i></span>
                            <input type="number" class="form-control" name="price" step="0.01" placeholder="Enter price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-clock"></i> Duration (minutes)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="number" class="form-control" name="duration" placeholder="Enter duration in minutes" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-image"></i> Image</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewServiceImage(event)">
                        </div>
                        <img id="serviceImagePreview" src="#" alt="Image Preview" style="display:none;max-width:100%;margin-top:10px;" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function previewServiceImage(event) {
    const [file] = event.target.files;
    if (file) {
        const preview = document.getElementById('serviceImagePreview');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
}

function previewEditServiceImage(event) {
    const [file] = event.target.files;
    if (file) {
        const preview = document.getElementById('editServiceImagePreview');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
}

function editService(service) {
    document.getElementById('edit_service_id').value = service.service_id;
    document.getElementById('edit_service_name').value = service.service_name;
    document.getElementById('edit_category_id').value = service.category_id;
    document.getElementById('edit_description').value = service.description;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_duration').value = service.duration;
    
    // Show image preview if image exists
    const preview = document.getElementById('editServiceImagePreview');
    if (service.image) {
        preview.src = '../' + service.image;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
    document.getElementById('edit_image').value = '';
    
    new bootstrap.Modal(document.getElementById('editServiceModal')).show();
}

function deleteService(serviceId) {
    if (confirm('Are you sure you want to delete this service?')) {
        document.getElementById('delete_service_id').value = serviceId;
        document.getElementById('deleteServiceForm').submit();
    }
}
</script>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="service_id" id="edit_service_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-spa"></i> Service Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-spa"></i></span>
                            <input type="text" class="form-control" name="service_name" id="edit_service_name" placeholder="Enter service name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-list"></i> Category</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list"></i></span>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo clean($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="Enter service description"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tag"></i> Price (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-peso-sign"></i></span>
                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" placeholder="Enter price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-clock"></i> Duration (minutes)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="number" class="form-control" name="duration" id="edit_duration" placeholder="Enter duration in minutes" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-image"></i> Image</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="file" class="form-control" name="edit_image" id="edit_image" accept="image/*" onchange="previewEditServiceImage(event)">
                        </div>
                        <img id="editServiceImagePreview" src="#" alt="Image Preview" style="display:none;max-width:100%;margin-top:10px;" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Service Form -->
<form id="deleteServiceForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="service_id" id="delete_service_id">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>