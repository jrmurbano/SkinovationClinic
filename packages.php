<?php
include 'db.php';

// Get package categories
function getPackageCategories($conn)
{
    $sql = "SELECT DISTINCT CASE 
                WHEN name LIKE '%Facial%' OR name LIKE '%Casmara%' OR name LIKE '%Diamond%' THEN 'facial'
                WHEN name LIKE '%Whitening%' OR name LIKE '%Cavitation%' THEN 'body'
                ELSE 'other'
            END as category,
            CASE 
                WHEN name LIKE '%Facial%' OR name LIKE '%Casmara%' OR name LIKE '%Diamond%' THEN 'Facial Packages'
                WHEN name LIKE '%Whitening%' OR name LIKE '%Cavitation%' THEN 'Body Treatment Packages'
                ELSE 'Other Packages'
            END as category_name
            FROM packages";

    $result = $conn->query($sql);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[$row['category']] = $row['category_name'];
    }
    return $categories;
}

// Get packages by category
function getPackagesByCategory($conn, $category)
{
    $sql = '';
    if ($category == 'facial') {
        $sql = "SELECT * FROM packages WHERE name LIKE '%Facial%' OR name LIKE '%Casmara%' OR name LIKE '%Diamond%'";
    } elseif ($category == 'body') {
        $sql = "SELECT * FROM packages WHERE name LIKE '%Whitening%' OR name LIKE '%Cavitation%'";
    } else {
        $sql = "SELECT * FROM packages WHERE name NOT LIKE '%Facial%' AND name NOT LIKE '%Casmara%' 
                AND name NOT LIKE '%Diamond%' AND name NOT LIKE '%Whitening%' AND name NOT LIKE '%Cavitation%'";
    }

    $result = $conn->query($sql);
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    return $packages;
}

$categories = getPackageCategories($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Packages - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Page Header -->
    <div class="section-header">
        <div class="container">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Beauty Packages</h1>
            <p class="lead animate__animated animate__fadeInUp">Experience premium treatments at great value</p>
            <div class="animate__animated animate__fadeInUp animate__delay-1s">
                <p class="mt-4">
                    <i class="bi bi-gift text-white me-2"></i>Book 3 sessions, get 1 free!
                    <i class="bi bi-clock ms-4 me-2"></i>Flexible scheduling
                    <i class="bi bi-shield-check ms-4 me-2"></i>All treatments by certified professionals
                </p>
            </div>
        </div>
    </div>

    <!-- Package Categories -->
    <?php foreach ($categories as $cat_key => $cat_name): ?>
    <?php $packages = getPackagesByCategory($conn, $cat_key); ?>
    <?php if (!empty($packages)): ?>
    <section class="py-5 <?php echo $cat_key == 'body' ? 'bg-light' : ''; ?>">
        <div class="container">
            <div class="section-title slide-in-left">
                <h2><?php echo $cat_name; ?></h2>
                <p><?php echo $cat_key == 'facial' ? 'Premium facial treatments for radiant, healthy skin' : ($cat_key == 'body' ? 'Comprehensive treatments for total body wellness' : 'Specialized treatments for your unique needs'); ?></p>
            </div>
            <div class="row g-4 stagger-fade-in">
                <?php foreach ($packages as $package): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 package-card hover-lift">
                        <div class="card-body text-center p-4">
                            <?php
                            $icon = $cat_key == 'facial' ? 'bi-stars' : ($cat_key == 'body' ? 'bi-droplet' : 'bi-gem');
                            ?>
                            <i class="bi <?php echo $icon; ?> display-4 text-purple mb-3"></i>
                            <h5 class="card-title h4 mb-3"><?php echo htmlspecialchars($package['name']); ?></h5>
                            <p class="card-text text-muted mb-4">
                                <?php echo htmlspecialchars($package['description']); ?>
                            </p>
                            <hr class="my-4">
                            <div class="features mb-4">
                                <p class="mb-2">
                                    <i class="bi bi-check2-circle text-purple me-2"></i>
                                    <?php echo $package['sessions']; ?> Sessions
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar-check text-purple me-2"></i>
                                    <?php echo $package['duration_days']; ?> Days Duration
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-clock-history text-purple me-2"></i>
                                    <?php echo $package['grace_period_days']; ?> Days Grace Period
                                </p>
                            </div>
                            <div class="price-tag bg-light p-3 rounded-3 mb-4">
                                <h3 class="text-purple mb-0">₱<?php echo number_format($package['price'], 2); ?></h3>
                                <small class="text-muted">Total Package Price</small>
                            </div>
                            <a href="book_package.php?id=<?php echo $package['id']; ?>" class="btn btn-purple btn-lg w-100">
                                <i class="bi bi-calendar-plus me-2"></i>Book Package
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <?php endforeach; ?>

    <!-- Benefits Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="section-title slide-in-left">
                <h2>Package Benefits</h2>
                <p>Enjoy exclusive advantages with our treatment packages</p>
            </div>
            <div class="row g-4 stagger-fade-in">
                <div class="col-md-4">
                    <div class="benefit-card text-center p-4">
                        <i class="bi bi-piggy-bank text-purple display-4 mb-3"></i>
                        <h4>Save More</h4>
                        <p>Get significant discounts compared to individual sessions</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card text-center p-4">
                        <i class="bi bi-calendar-check text-purple display-4 mb-3"></i>
                        <h4>Flexible Scheduling</h4>
                        <p>Book your sessions anytime within the package duration</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card text-center p-4">
                        <i class="bi bi-trophy text-purple display-4 mb-3"></i>
                        <h4>Better Results</h4>
                        <p>Achieve optimal results with consistent treatment sessions</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5">
        <div class="container">
            <div class="section-title slide-in-left">
                <h2>What Our Clients Say</h2>
                <p>Read about the experiences of our valued clients</p>
            </div>
            <div class="row g-4 stagger-fade-in">
                <div class="col-md-4">
                    <div class="testimonial-item">
                        <img src="assets/img/testimonial1.jpg" alt="Client 1" class="testimonial-avatar shine">
                        <h5 class="mt-3">Sarah Johnson</h5>
                        <p class="text-muted">Package Client</p>
                        <div class="rating text-warning mb-2">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p>"I've been using their facial package and the results are amazing. Great value for money!"
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-item">
                        <img src="assets/img/testimonial2.jpg" alt="Client 2" class="testimonial-avatar shine">
                        <h5 class="mt-3">Maria Garcia</h5>
                        <p class="text-muted">Regular Client</p>
                        <div class="rating text-warning mb-2">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                        <p>"The body treatment packages are fantastic. I've seen significant improvements!"</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-item">
                        <img src="assets/img/testimonial3.jpg" alt="Client 3" class="testimonial-avatar shine">
                        <h5 class="mt-3">Emily Chen</h5>
                        <p class="text-muted">VIP Client</p>
                        <div class="rating text-warning mb-2">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p>"Their package deals are the best in town. The results speak for themselves!"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-purple text-white">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <h2 class="mb-4">Ready to Start Your Beauty Journey?</h2>
                    <p class="lead mb-4">Book your package now and take the first step towards radiant skin and lasting
                        beauty.</p>
                    <a href="#" class="btn btn-light btn-lg px-5 btn-pulse">
                        <i class="bi bi-calendar-check me-2"></i>Book Your Package
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>
