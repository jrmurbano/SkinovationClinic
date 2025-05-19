<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config.php';

// Fetch unread notifications count
$stmt = $conn->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0");
$unread_count = $stmt->fetchColumn();

// Fetch latest unread notifications for bell dropdown (limit 5)
$stmt = $conn->query("
    SELECT n.*, a.appointment_date, s.service_name, CONCAT(pt.first_name, ' ', pt.last_name) as patient_name
    FROM notifications n
    LEFT JOIN appointments a ON n.appointment_id = a.appointment_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN patients pt ON a.patient_id = pt.patient_id
    WHERE n.is_read = 0
    ORDER BY n.created_at DESC
    LIMIT 5
");
$unread_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<button class="sidebar-toggler" onclick="toggleSidebar()">☰</button>
<nav class="sidebar bg-dark text-white p-3">
    <div class="text-center mb-4">
        <img src="../assets/img/ISCAP1-303-Skinovation-Clinic-WHITE-Logo.png" alt="Skinovation Clinic Logo" class="img-fluid" style="max-width: 150px;">
    </div>
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
        <h3 class="text-light mb-0">Admin Panel</h3>
        <!-- Add notification bell -->
        <div class="dropdown">
            <a class="text-light position-relative" href="#" id="adminNotificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell fa-lg"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger admin-notification-count">
                    0
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="adminNotificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                <div class="p-2 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notifications</h6>
                    <button class="btn btn-sm btn-link text-decoration-none mark-all-read">Mark All as Read</button>
                </div>
                <div class="admin-notifications-list">
                    <!-- Admin notifications will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    <div class="position-relative mb-3">
        
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notifBell" style="min-width: 350px;">
            <li class="dropdown-header fw-bold">Unread Notifications</li>
            <?php if (empty($unread_notifications)): ?>
                <li><span class="dropdown-item text-muted">No new notifications</span></li>
            <?php else: ?>
                <?php foreach ($unread_notifications as $notif): ?>
                    <li>
                        <div class="dropdown-item">
                            <div class="fw-bold"><?php echo clean($notif['patient_name']); ?></div>
                            <div class="small text-muted">
                                <?php echo clean($notif['service_name']); ?> &middot; 
                                <?php echo $notif['appointment_date'] ? date('M d, Y h:i A', strtotime($notif['appointment_date'])) : ''; ?>
                            </div>
                            <div class="small text-secondary mt-1">
                                <?php echo clean($notif['message']); ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a href="dashboard.php" class="nav-link text-white"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="nav-item"><a href="maintenance.php" class="nav-link text-white"><i class="fas fa-tools"></i> Maintenance</a></li>
        <li class="nav-item"><a href="appointments.php" class="nav-link text-white"><i class="fas fa-calendar-check"></i> Appointments</a></li>
        <li class="nav-item"><a href="patients.php" class="nav-link text-white"><i class="fas fa-user-friends"></i> Patients</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li class="nav-item"><a href="settings.php" class="nav-link text-white"><i class="fas fa-cog"></i> Settings</a></li>
        <li class="nav-item"><a href="logout.php" class="nav-link text-white"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<style>
    .sidebar {
        background-color: rgba(74, 20, 140, 0.95);
        backdrop-filter: blur(5px);
        min-height: 100vh;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        width: 300px; /* widened from default */
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.9);
        padding: 0.8rem 1rem;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .sidebar .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }

    .sidebar .nav-link i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .sidebar-header {
        padding: 1.5rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header img {
        max-width: 150px;
        height: auto;
    }

    .dropdown-menu {
        background-color: #ffffff;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .dropdown-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #333333;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-header {
        background-color: #6a1b9a;
        font-size: 0.9rem;
        padding: 10px 15px;
        color: #ffffff;
        font-weight: 600;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .text-secondary {
        color: #495057 !important;
    }

    #notifBell {
        outline: none;
        background: transparent;
        border: none;
        color: #ffffff;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    #notifBell:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    #notifBell .badge {
        font-size: 0.75rem;
        padding: 4px 7px;
        background-color: #ff4081;
        border: 2px solid #6a1b9a;
        color: #ffffff;
    }

    .sidebar-toggler {
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1000;
        background: #6a1b9a;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    .sidebar-toggler:hover {
        background: #8e24aa;
    }

    /* Improve dropdown text readability */
    .dropdown-item .fw-bold {
        color: #6a1b9a;
        font-size: 1rem;
        font-weight: 600;
    }

    .dropdown-item .small {
        font-size: 0.875rem;
        line-height: 1.4;
    }

    /* Add hover effect to dropdown items */
    .dropdown-item:hover {
        background-color: #f3e5f5;
    }

    /* Logo container */
    .sidebar .text-center {
        background-color: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    /* Notification badge */
    .badge {
        background-color: #ff4081 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/notifications.js"></script>
</body>
</html>
