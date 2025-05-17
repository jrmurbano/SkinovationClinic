<?php
// Check if user is logged in - we don't need session_start() here as it's already started in the main file
if (!isset($_SESSION['patient_id'])) {
    die('<script>window.location.href = "../login.php";</script>');
}
?>

<li class="nav-item">
    <a class="nav-link" href="my-profile.php">My Profile</a>
</li>

<li class="nav-item">
    <a class="nav-link" href="my-appointments.php">
        <i class="fas fa-calendar-check"></i> My Appointments
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="notifications.php">
        <i class="fas fa-bell position-relative">
            <?php
            // Get unread notifications count
            $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE patient_id = ? AND is_read = 0");
            $stmt->execute([$_SESSION['patient_id']]);
            $unread_count = $stmt->fetchColumn();
            if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $unread_count; ?>
                </span>
            <?php endif; ?>
        </i>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="profile.php">
        <i class="fas fa-user"></i> Profile
    </a>
</li>
