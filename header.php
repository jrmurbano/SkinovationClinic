<?php
// Function to get the correct path
function getPath($path)
{
    $isAdmin = isset($GLOBALS['is_admin']) && $GLOBALS['is_admin'];
    $isPatient = strpos($_SERVER['PHP_SELF'], '/patient/') !== false;

    if ($isAdmin || $isPatient) {
        return '../' . $path;
    } else {
        return $path;
    }
}

// Set body class based on sidebar state
$show_sidebar = isset($_SESSION['patient_id']);
$body_class = $show_sidebar ? 'has-sidebar' : '';
?>
<header class="main-header">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo getPath('index.php'); ?>">
                <img src="<?php echo getPath('assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png'); ?>" alt="Skinovation Clinic Logo" height="55" class="me-2">
                <span class="brand-text">Skinovation Beauty Clinic</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('index.php'); ?>">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('services.php'); ?>">
                            <i class="fas fa-concierge-bell"></i> Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('packages.php'); ?>">
                            <i class="fas fa-box"></i> Packages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('products.php'); ?>">
                            <i class="fas fa-shopping-bag"></i> Products
                        </a>
                    </li>
                    <?php if (isset($_SESSION['patient_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('patient/my-appointments.php'); ?>">
                            <i class="fas fa-calendar-check"></i> My Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('patient/my-profile.php'); ?>">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
                                0
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                            <div class="p-2 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Notifications</h6>
                                <button class="btn btn-sm btn-link text-decoration-none mark-all-read">Mark All as Read</button>
                            </div>
                            <div class="notifications-list">
                                <!-- Notifications will be loaded here -->
                            </div>
                        </div>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($is_admin) ? 'dashboard.php' : 'admin/dashboard.php'; ?>">
                            <i class="fas fa-user-shield"></i> Admin Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('logout.php'); ?>">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary me-2" href="<?php echo getPath('login.php'); ?>">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-success" href="<?php echo getPath('register.php'); ?>">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize Bootstrap dropdowns
    document.addEventListener('DOMContentLoaded', function() {
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
</script>
<script src="<?php echo getPath('assets/js/notifications.js'); ?>"></script>
