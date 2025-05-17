<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle notification deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = clean($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: notifications.php?success=deleted');
    exit();
}

// Handle notification status update
if (isset($_POST['action']) && isset($_POST['notification_id'])) {
    $id = clean($_POST['notification_id']);
    $action = clean($_POST['action']);
    
    if ($action === 'mark_read') {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    header('Location: notifications.php');
    exit();
}

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

// Fetch notifications with related appointment info
$stmt = $conn->query("
    SELECT n.*, 
           a.appointment_id,
           a.appointment_date,
           s.service_name,
           CONCAT(pt.first_name, ' ', pt.last_name) as patient_name
    FROM notifications n
    LEFT JOIN appointments a ON n.appointment_id = a.appointment_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN patients pt ON a.patient_id = pt.patient_id
    ORDER BY n.created_at DESC
");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notifications - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
        body {
            background-image: url('https://cdn.vectorstock.com/i/500p/99/24/molecules-inside-bubbles-on-blue-background-water-vector-53889924.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .notification-bell {
            position: relative;
            display: inline-block;
        }
        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            font-weight: bold;
        }
        .notification-item {
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .notification-item.unread {
            background-color: #e3f2fd;
            border-left-color: #2196f3;
        }
        .notification-item .time {
            font-size: 0.85em;
            color: #6c757d;
        }
        .notification-item .patient-name {
            font-weight: bold;
            color: #2196f3;
        }
        .notification-item .service-name {
            color: #28a745;
        }
        #notifBell {
            outline: none;
        }
        #notifBell .badge {
            font-size: 0.8em;
            padding: 4px 7px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Manage Notifications</h1>
                <div class="position-relative me-3">
                    <button class="btn btn-link p-0 border-0 position-relative" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-2x"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $unread_count; ?>
                            </span>
                        <?php endif; ?>
                    </button>
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
               
            </div>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                switch ($_GET['success']) {
                    case 'added':
                        echo 'Notification added successfully!';
                        break;
                    case 'updated':
                        echo 'Notification updated successfully!';
                        break;
                    case 'deleted':
                        echo 'Notification deleted successfully!';
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                    <p class="text-center text-muted">No notifications found</p>
                    <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1"><?php echo clean($notification['title']); ?></h5>
                                    <p class="mb-1"><?php echo clean($notification['message']); ?></p>
                                    <?php if ($notification['patient_name']): ?>
                                    <p class="mb-1">
                                        <span class="patient-name"><?php echo clean($notification['patient_name']); ?></span>
                                        <?php if ($notification['service_name']): ?>
                                        booked <span class="service-name"><?php echo clean($notification['service_name']); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <?php endif; ?>
                                    <span class="time">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="notification-actions">
                                    <?php if (!$notification['is_read']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                        <button type="submit" name="action" value="mark_read" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Mark as Read
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <a href="edit_notification.php?id=<?php echo $notification['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="notifications.php?delete=<?php echo $notification['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this notification?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
