<?php
session_start();
include '../db.php';

$GLOBALS['is_admin'] = false; // Set this flag for proper path resolution

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

// Get all products
function getAllProducts($conn)
{
    $sql = 'SELECT * FROM products ORDER BY name';
    $result = $conn->query($sql);

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    return $products;
}

// Get services and products
$services = getServicesByCategory($conn, $category);
$products = getAllProducts($conn);

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
    <title>Services - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body> <?php include '../header.php'; ?>

    <!-- Services Header -->
    <div class="services-header py-5 bg-light">
        <div class="container">
            <h1 class="text-center"><?php echo $categoryName; ?></h1>
            <p class="text-center lead">Choose from our wide range of professional treatments</p>

            <!-- Category Filter -->
            <div class="category-filter my-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap justify-content-center gap-2"> <a
                                href="../services.php?category=all" class="btn <?php echo $category == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                            <a href="../services.php?category=facials" class="btn <?php echo $category == 'facials' ? 'btn-primary' : 'btn-outline-primary'; ?>">Facials</a>
                            <a href="../services.php?category=lightening" class="btn <?php echo $category == 'lightening' ? 'btn-primary' : 'btn-outline-primary'; ?>">Lightening</a>
                            <a href="../services.php?category=pimple" class="btn <?php echo $category == 'pimple' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pimple
                                Treatments</a>
                            <a href="../services.php?category=slimming" class="btn <?php echo $category == 'slimming' ? 'btn-primary' : 'btn-outline-primary'; ?>">Body
                                Slimming</a> <a href="../services.php?category=hair-removal"
                                class="btn <?php echo $category == 'hair-removal' ? 'btn-primary' : 'btn-outline-primary'; ?>">Hair
                                Removal</a>
                            <a href="../services.php?category=other" class="btn <?php echo $category == 'other' ? 'btn-primary' : 'btn-outline-primary'; ?>">Other Services</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services List -->
    <section class="services-list py-5">
        <div class="container">
            <?php if (empty($services)): ?>
            <div class="alert alert-info text-center">No services found in this category.</div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card service-item h-100">
                        <div class="card-body">
                            <h5 class="card-title fs-5"><?php echo htmlspecialchars($service['name']); ?></h5>
                            <p class="service-description small"><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="service-price fw-bold">₱<?php echo number_format($service['price'], 2); ?></span>
                                <span class="service-duration small"><i class="bi bi-clock"></i> <?php echo $service['duration']; ?>
                                    min</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <button class="btn btn-primary w-100" onclick="confirmBooking(<?php echo $service['id']; ?>)">Book
                                Now</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="bookingConfirmationModal" tabindex="-1" aria-labelledby="bookingConfirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingConfirmationModalLabel">Confirm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to proceed to booking?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="confirmBookingBtn">Yes</button>
                </div>
            </div>
        </div>
    </div> <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        let selectedServiceId = null;
        const bookingModal = new bootstrap.Modal(document.getElementById('bookingConfirmationModal'));

        function confirmBooking(serviceId) {
            selectedServiceId = serviceId;
            bookingModal.show();
        }

        document.getElementById('confirmBookingBtn').addEventListener('click', function() {
            if (selectedServiceId) {
                window.location.href = 'booking.php?service_id=' + selectedServiceId;
            }
        });
    </script>
</body>

</html>
