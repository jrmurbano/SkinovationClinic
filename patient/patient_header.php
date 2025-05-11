<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>

<style>
    /* Fixed navbar and sidebar styles */
    .fixed-nav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sidebar {
        position: fixed;
        top: 60px;
        /* Height of navbar */
        left: 0;
        bottom: 0;
        width: 250px;
        background-color: #f8f9fa;
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
        padding-top: 1rem;
        z-index: 1020;
    }

    .main-content {
        margin-left: 250px;
        margin-top: 60px;
        padding: 2rem;
    }

    .nav-link {
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #333;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .nav-link:hover,
    .nav-link.active {
        background-color: #e9ecef;
        color: var(--primary-color);
    }

    .sidebar-nav {
        padding: 0;
        list-style: none;
    }

    /* Logo styles */
    .navbar-brand img {
        height: 40px;
    }

    /* Profile dropdown styles */
    .profile-dropdown .dropdown-toggle::after {
        display: none;
    }

    .profile-dropdown .dropdown-menu {
        min-width: 200px;
    }
</style>

<!-- Fixed Navbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-nav">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png" alt="Skinovation Logo">
        </a>

        <div class="ms-auto">
            <a href="../logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <ul class="sidebar-nav">
        <li>
            <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="bi bi-house"></i>
                Home
            </a>
        </li>
        <li>
            <a href="services.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>">
                <i class="bi bi-stars"></i>
                Services
            </a>
        </li>
        <li>
            <a href="../packages.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'active' : ''; ?>">
                <i class="bi bi-box"></i>
                Packages
            </a>
        </li>
        <li>
            <a href="../products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                <i class="bi bi-bag"></i>
                Products
            </a>
        </li>
        <li>
            <a href="my-appointments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my-appointments.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-check"></i>
                My Appointments
            </a>
        </li>
        <li>
            <a href="profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="bi bi-person"></i>
                My Profile
            </a>
        </li>
    </ul>
</div>
