<?php
if (!isset($_SESSION['owner_id'])) {
    header('Location: owner_login.php');
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #4a148c 0%, #6a1b9a 100%);">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="../assets/img/ISCAP1-303-Skinovation-Clinic-WHITE-Logo.png" alt="Skinovation Clinic Logo" height="40" class="me-2">
            <span class="d-none d-sm-inline">Owner Portal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="dashboard.php">
                        <i class="fas fa-chart-line me-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="services.php">
                        <i class="fas fa-concierge-bell me-2"></i>
                        <span>Services</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="packages.php">
                        <i class="fas fa-box me-2"></i>
                        <span>Packages</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="products.php">
                        <i class="fas fa-shopping-bag me-2"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="appointments.php">
                        <i class="fas fa-calendar-check me-2"></i>
                        <span>Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="patients.php">
                        <i class="fas fa-users me-2"></i>
                        <span>Patients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="history.php">
                        <i class="fas fa-history me-2"></i>
                        <span>Treatment History</span>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="nav-link text-light d-flex align-items-center">
                        <i class="fas fa-user-circle me-2"></i>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['owner_name']); ?></span>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
.navbar {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 0.5rem 0;
}

.navbar-brand {
    font-weight: 600;
    font-size: 1.2rem;
}

.nav-link {
    padding: 0.8rem 1rem;
    transition: all 0.3s ease;
    border-radius: 4px;
    margin: 0.2rem 0;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.nav-link i {
    width: 20px;
    text-align: center;
}

@media (max-width: 991.98px) {
    .navbar-collapse {
        padding: 1rem 0;
    }
    
    .nav-link {
        padding: 0.5rem 1rem;
    }
    
    .navbar-nav .nav-link {
        margin: 0.2rem 0;
    }
}

/* Active state for current page */
.nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 500;
}
</style>

<script>
// Add active class to current page link
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });
});
</script> 