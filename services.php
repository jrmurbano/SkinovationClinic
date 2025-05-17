<?php
session_start();
include 'db.php';

// Get category from URL parameter
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Function to get services by category
function getServicesByCategory($conn, $category)
{
    if ($category == 'all') {
        $sql = 'SELECT * FROM services ORDER BY service_name';
    } else {
        $sql = 'SELECT * FROM services WHERE category_id = ? ORDER BY service_name';
    }

    $stmt = $conn->prepare($sql);

    if ($category != 'all') {
        $stmt->bind_param('s', $category);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }

    return $services;
}

// Fetch categories from the database
function getCategories($conn) {
    $sql = 'SELECT DISTINCT category_id, name AS category_name FROM service_categories';
    $result = $conn->query($sql);

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[$row['category_id']] = $row['category_name'];
    }

    return $categories;
}

$categories = getCategories($conn);

// Get services
$services = getServicesByCategory($conn, $category);

// Get category name for display
function getCategoryName($category)
{
    $categories = [
        'facials' => 'Facials',
        'lightening' => 'Lightening Treatments',
        'pimple' => 'Pimple Treatments',
        'slimming' => 'Body Slimming',
        'hair-removal' => 'Hair Removal (IPL)',
        'other' => 'Other Services',
        'all' => 'All Services',
    ];

    return isset($categories[$category]) ? $categories[$category] : 'All Services';
}

$categoryName = getCategoryName($category);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $categoryName; ?> - Services</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
    <?php include 'header.php'; ?>

    <!-- Page Header -->
    <div class="section-header">
        <div class="container text-center mt-5 mb-5">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInDown"><?php echo $categoryName; ?></h1>
            <p class="lead animate__animated animate__fadeInUp">Experience our premium beauty and skincare treatments
            </p>
        </div>
    </div>

    <!-- Filter Dropdown -->
    <div class="container">
        <div class="mb-4">
            <label for="category" class="form-label">Filter by Category:</label>
            <select id="category" class="form-select" onchange="location = this.value;">
                <option value="services.php?category=all" <?php echo $category == 'all' ? 'selected' : ''; ?>>All Services</option>
                <?php foreach ($categories as $id => $name): ?>
                    <option value="services.php?category=<?php echo $id; ?>" <?php echo $category == $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="container">
        <div class="row g-4 my-4">
            <?php if (empty($services)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    No services found in this category.
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($services as $service): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 service-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($service['service_name'] ?? ''); ?></h5>
                        <?php if ($service['description']): ?>
                        <p class="card-text text-muted service-description">
                            <?php echo htmlspecialchars($service['description']); ?>
                        </p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-end mt-3">
                            <div>
                                <p class="service-price mb-1">₱<?php echo number_format($service['price'], 2); ?></p>
                                <p class="service-duration mb-0">
                                    <i class="bi bi-clock me-1"></i>
                                    <?php echo $service['duration']; ?> minutes
                                </p>
                            </div>
                            <a href="<?php echo isset($_SESSION['patient_id']) 
                                ? 'patient/calendar_view.php?service_id=' . htmlspecialchars($service['id'] ?? '0') 
                                : 'login.php?redirect=booking&service_id=' . htmlspecialchars($service['id'] ?? '0'); ?>" class="btn btn-purple">
                                <i class="bi bi-calendar-plus me-1"></i>Book Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>
