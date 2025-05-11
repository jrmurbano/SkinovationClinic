<?php
session_start();
include 'db.php';

// Get category from URL parameter
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Function to get services by category
function getServicesByCategory($conn, $category)
{
    if ($category == 'all') {
        $sql = 'SELECT * FROM services ORDER BY category, name';
    } else {
        $sql = 'SELECT * FROM services WHERE category = ? ORDER BY name';
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
    <title><?php echo $categoryName; ?> - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Page Header -->
    <div class="section-header">
        <div class="container">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInDown"><?php echo $categoryName; ?></h1>
            <p class="lead animate__animated animate__fadeInUp">Experience our premium beauty and skincare treatments
            </p>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="container">
        <div class="category-filter">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="?category=all" class="btn <?php echo $category == 'all' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-grid-3x3-gap me-1"></i>All
                        </a>
                        <a href="?category=facials" class="btn <?php echo $category == 'facials' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-stars me-1"></i>Facials
                        </a>
                        <a href="?category=lightening" class="btn <?php echo $category == 'lightening' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-brightness-high me-1"></i>Lightening
                        </a>
                        <a href="?category=pimple" class="btn <?php echo $category == 'pimple' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-patch-check me-1"></i>Pimple Treatments
                        </a>
                        <a href="?category=slimming" class="btn <?php echo $category == 'slimming' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-hourglass-split me-1"></i>Body Slimming
                        </a>
                        <a href="?category=hair-removal" class="btn <?php echo $category == 'hair-removal' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-lightning me-1"></i>Hair Removal
                        </a>
                        <a href="?category=other" class="btn <?php echo $category == 'other' ? 'btn-purple' : 'btn-outline-purple'; ?>">
                            <i class="bi bi-plus-circle me-1"></i>Other Services
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Grid -->
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
                        <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
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
                            <a href="<?php echo isset($_SESSION['user_id']) ? 'patient/booking.php?service_id=' . $service['id'] : 'login.php?redirect=booking&service_id=' . $service['id']; ?>" class="btn btn-purple">
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
