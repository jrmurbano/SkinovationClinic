<?php
// Function to get the correct path
function getPath($path)
{
    $isAdmin = isset($GLOBALS['is_admin']) && $GLOBALS['is_admin'];
    $isPatient = strpos($_SERVER['PHP_SELF'], '/patient/') !== false;

    if ($isAdmin) {
        return '../' . $path;
    } elseif ($isPatient) {
        return '../' . $path;
    } else {
        return $path;
    }
}

// Set body class based on sidebar state
$show_sidebar = isset($_SESSION['user_id']);
$body_class = $show_sidebar ? 'has-sidebar' : '';
?>
<header class="main-header">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo getPath('index.php'); ?>">
                <img src="<?php echo getPath('assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png'); ?>" alt="Skinovation Clinic Logo" height="40" class="me-2">
                <span class="brand-text">Skinovation Beauty Clinic</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('index.php'); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('services.php'); ?>">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('packages.php'); ?>">Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('products.php'); ?>">Products</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('patient/my-appointments.php'); ?>">My Appointments</a>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($is_admin) ? 'dashboard.php' : 'admin/dashboard.php'; ?>">Admin Dashboard</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('logout.php'); ?>">Logout</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('login.php'); ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('register.php'); ?>">Register</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
