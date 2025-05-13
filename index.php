<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6f42c1;
        }

        .hero-cover {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/img/hero-bg.jpg');
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
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .testimonial-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .testimonial-item::before {
            content: '"';
            position: absolute;
            top: 1rem;
            left: 1rem;
            font-size: 4rem;
            font-family: Georgia, serif;
            color: var(--primary-color);
            opacity: 0.1;
            line-height: 1;
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
    <?php include 'header.php'; ?> <!-- Hero Section -->
    <div class="hero-cover">
        <div class="hero-content">
            <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInDown">Skinovation Beauty Clinic</h1>
            <p class="lead fs-4 mb-4 animate__animated animate__fadeInUp">Your trusted destination for premium beauty
                and skincare services.<br>Where beauty meets excellence.</p>
            <div class="animate__animated animate__fadeInUp animate__delay-1s">
                <a href="services.php" class="btn btn-purple btn-lg px-5 py-3 me-3 btn-pulse">Explore Services</a>
            </div>
        </div>
    </div>

    <!-- What We Offer Section -->
    <section id="what-we-offer" class="py-5">
        <div class="container">
            <div class="section-title slide-in-left">
                <h2>What We Offer</h2>
                <p>Discover our comprehensive range of beauty and skincare solutions</p>
            </div>
            <div class="row g-4 stagger-fade-in">
                <div class="col-md-4">
                    <div class="service-card">
                        <i class="bi bi-box service-icon rotate-in"></i>
                        <h3 class="h4 mb-3">Packages</h3>
                        <p class="mb-4">Save more with our special package deals. Get the fourth session free when you
                            book three sessions.</p>
                        <a href="packages.php" class="btn btn-outline-purple">View Packages</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <i class="bi bi-star service-icon"></i>
                        <h3 class="h4 mb-3">Services</h3>
                        <p class="mb-4">From facials to body treatments, we offer a wide range of professional beauty
                            services.</p>
                        <a href="services.php" class="btn btn-outline-purple">Explore Services</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <i class="bi bi-bag service-icon"></i>
                        <h3 class="h4 mb-3">Products</h3>
                        <p class="mb-4">Take home our professional skincare products to maintain your beauty routine.
                        </p>
                        <a href="products.php" class="btn btn-outline-purple">Shop Products</a>
                    </div>
                </div>
            </div>
        </div>
    </section> <!-- Why Choose Us -->
    <section id="why-choose-us" class="py-5 bg-light">
        <div class="container">
            <div class="section-title slide-in-right">
                <h2>Why Choose Us</h2>
                <p>Experience excellence in beauty and skincare with our unique advantages</p>
            </div>
            <div class="row g-4 stagger-fade-in">
                <div class="col-md-3">
                    <div class="feature-box shine">
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
    
    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">What Our Clients Say</h2>
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="testimonial-item text-center">
                                    <p class="testimonial-text">"The facial treatment was amazing! My skin feels so refreshed and rejuvenated. The staff was very professional and friendly."</p>
                                    <h5 class="client-name">Maria Santos</h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-item text-center">
                                    <p class="testimonial-text">"I've been struggling with acne for years, and after just three sessions of their anti-acne treatment, I've seen significant improvement!"</p>
                                    <h5 class="client-name">John Rivera</h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-item text-center">
                                    <p class="testimonial-text">"The online booking system is so convenient. I love being able to schedule my appointments anytime without having to call."</p>
                                    <h5 class="client-name">Anna Lee</h5>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!isset($_SESSION['patient_id'])): ?>
            const reminderModal = document.createElement('div');
            reminderModal.innerHTML = `
                <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="reminderModalLabel">Welcome to Skinovation Clinic!</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Please log-in or register to book a service, package, or product.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Got it!</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(reminderModal);
            const modal = new bootstrap.Modal(document.getElementById('reminderModal'));
            modal.show();
            <?php endif; ?>
        });
    </script>
</body>

</html>
