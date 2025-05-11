<?php
session_start();
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skinovation Beauty Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: #6f42c1;
        }

        .hero-cover {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../assets/img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            padding: 2rem;
        }

        .service-card {
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 2rem;
            background: white;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .service-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .feature-box {
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            text-align: center;
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .testimonial-item {
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 1rem 0;
            text-align: center;
        }

        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            object-fit: cover;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        .footer a:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <?php include '../header.php'; ?>

    <!-- Hero Section -->
    <div class="hero-cover">
        <div class="hero-content">
            <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInDown">Skinovation Beauty Clinic</h1>
            <p class="lead fs-4 mb-4 animate__animated animate__fadeInUp">Your trusted destination for premium beauty
                and skincare services.<br>Where beauty meets excellence.</p>
            <div class="animate__animated animate__fadeInUp animate__delay-1s">
                <a href="#what-we-offer" class="btn btn-purple btn-lg px-5 py-3 me-3">Explore Services</a>
                <a href="../services.php" class="btn btn-outline-light btn-lg px-5 py-3">Book Now</a>
            </div>
        </div>
    </div>

    <!-- What We Offer Section -->
    <section id="what-we-offer" class="py-5">
        <div class="container">
            <div class="section-title">
                <h2>What We Offer</h2>
                <p>Discover our comprehensive range of beauty and skincare solutions</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <i class="bi bi-box service-icon"></i>
                        <h3 class="h4 mb-3">Packages</h3>
                        <p class="mb-4">Save more with our special package deals. Get the fourth session free when you
                            book three sessions.</p>
                        <a href="../packages.php" class="btn btn-outline-purple">View Packages</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <i class="bi bi-star service-icon"></i>
                        <h3 class="h4 mb-3">Services</h3>
                        <p class="mb-4">From facials to body treatments, we offer a wide range of professional beauty
                            services.</p>
                        <a href="../services.php" class="btn btn-outline-purple">Explore Services</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <i class="bi bi-bag service-icon"></i>
                        <h3 class="h4 mb-3">Products</h3>
                        <p class="mb-4">Take home our professional skincare products to maintain your beauty routine.
                        </p>
                        <a href="../products.php" class="btn btn-outline-purple">Shop Products</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section id="why-choose-us" class="py-5 bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Us</h2>
                <p>Experience excellence in beauty and skincare with our unique advantages</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="bi bi-award feature-icon"></i>
                        <h4>Expert Care</h4>
                        <p>Professional and experienced beauty specialists</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="bi bi-gear feature-icon"></i>
                        <h4>Modern Equipment</h4>
                        <p>State-of-the-art beauty and skincare technology</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="bi bi-shield-check feature-icon"></i>
                        <h4>Safe Treatments</h4>
                        <p>FDA-approved products and procedures</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <i class="bi bi-heart feature-icon"></i>
                        <h4>Client Satisfaction</h4>
                        <p>Dedicated to exceeding your expectations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-5">
        <div class="container">
            <div class="section-title">
                <h2>What Our Clients Say</h2>
                <p>Read about the experiences of our valued clients</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-item">
                        <img src="../assets/img/testimonial1.jpg" alt="Client 1" class="testimonial-avatar">
                        <h5 class="mt-3">Sarah Johnson</h5>
                        <p class="text-muted">Regular Client</p>
                        <p>"The services are exceptional! My skin has never looked better since I started their facial
                            treatments."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-item">
                        <img src="../assets/img/testimonial2.jpg" alt="Client 2" class="testimonial-avatar">
                        <h5 class="mt-3">Maria Garcia</h5>
                        <p class="text-muted">Package Client</p>
                        <p>"Their package deals are amazing value. The staff is professional and the results are
                            outstanding."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-item">
                        <img src="../assets/img/testimonial3.jpg" alt="Client 3" class="testimonial-avatar">
                        <h5 class="mt-3">Emily Chen</h5>
                        <p class="text-muted">Loyal Customer</p>
                        <p>"I love their skincare products! Using them daily has made such a difference in my
                            complexion."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
