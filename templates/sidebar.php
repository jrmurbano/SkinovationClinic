<?php
// Check if user should have a sidebar
$show_sidebar = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

if ($show_sidebar):
?>
<nav class="side-nav">
    <div class="py-2">
        <ul class="nav flex-column">
            <?php if ($is_admin): ?>
            <li class="nav-item">
                <a href="<?php echo getPath('admin/dashboard.php'); ?>" class="nav-link">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo getPath('admin/clients.php'); ?>" class="nav-link">
                    <i class="bi bi-people"></i> Manage Clients
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo getPath('admin/services.php'); ?>" class="nav-link">
                    <i class="bi bi-card-checklist"></i> Manage Services
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo getPath('admin/packages.php'); ?>" class="nav-link">
                    <i class="bi bi-box"></i> Manage Packages
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo getPath('admin/products.php'); ?>" class="nav-link">
                    <i class="bi bi-bag"></i> Manage Products
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo getPath('admin/appointments.php'); ?>" class="nav-link">
                    <i class="bi bi-calendar2-check"></i> Manage Appointments
                </a>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <a href="<?php echo getPath('patient/my-appointments.php'); ?>" class="nav-link">
                    <i class="bi bi-calendar2"></i> My Appointments
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo getPath('patient/my-profile.php'); ?>" class="nav-link">
                    <i class="bi bi-person"></i> My Profile
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<?php endif; ?>
