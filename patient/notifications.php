<?php
session_start();
include '../config.php';

// Check if patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

$patient_id = $_SESSION['patient_id'];

// Handle marking notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['notification_id'])) {
    $notification_id = clean($_POST['notification_id']);
    $action = clean($_POST['action']);
    
    if ($action === 'mark_read') {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND patient_id = ?");
        $stmt->execute([$notification_id, $patient_id]);
    }
    
    header('Location: notifications.php');
    exit();
}

// Fetch notifications for the patient
$stmt = $conn->prepare("
    SELECT n.*, 
           CASE 
               WHEN n.type = 'appointment' THEN a.appointment_date
               WHEN n.type = 'package' THEN pa.appointment_date
           END as appointment_date,
           CASE 
               WHEN n.type = 'appointment' THEN a.appointment_time
               WHEN n.type = 'package' THEN pa.appointment_time
           END as appointment_time,
           CASE 
               WHEN n.type = 'appointment' THEN s.service_name
               WHEN n.type = 'package' THEN p.package_name
           END as service_name
    FROM notifications n
    LEFT JOIN appointments a ON n.appointment_id = a.appointment_id AND n.type = 'appointment'
    LEFT JOIN package_appointments pa ON n.appointment_id = pa.package_appointment_id AND n.type = 'package'
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN package_bookings pb ON pa.booking_id = pb.booking_id
    LEFT JOIN packages p ON pb.package_id = p.package_id
    WHERE n.patient_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$patient_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread notifications
$unread_count = count(array_filter($notifications, function($n) { return !$n['is_read']; }));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notification-item {
            border-left: 4px solid #6f42c1;
            margin-bottom: 10px;
            padding: 15px;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .notification-item.unread {
            background-color: #f8f4ff;
            border-left-color: #6f42c1;
        }

        .notification-item .time {
            font-size: 0.85em;
            color: #6c757d;
        }

        .notification-item .title {
            color: #6f42c1;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .notification-item .message {
            color: #495057;
            margin-bottom: 10px;
        }

        .notification-item .service-name {
            color: #28a745;
            font-weight: 500;
        }

        .mark-read-btn {
            padding: 2px 8px;
            font-size: 0.8em;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 4em;
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>My Notifications</h1>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-primary"><?php echo $unread_count; ?> unread</span>
                    <?php endif; ?>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h3>No Notifications Yet</h3>
                        <p class="text-muted">You don't have any notifications at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="title"><?php echo clean($notification['title']); ?></h5>
                                        <p class="message"><?php echo clean($notification['message']); ?></p>
                                        <?php if ($notification['service_name']): ?>
                                            <p class="service-name mb-2">
                                                <?php echo clean($notification['service_name']); ?>
                                                <?php if ($notification['appointment_date']): ?>
                                                    on <?php echo date('F j, Y', strtotime($notification['appointment_date'])); ?>
                                                    at <?php echo date('g:i A', strtotime($notification['appointment_time'])); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                        <span class="time">
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                                        </span>
                                    </div>
                                    <?php if (!$notification['is_read']): ?>
                                        <form method="POST" class="ms-3">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                            <button type="submit" class="btn btn-outline-primary btn-sm mark-read-btn">
                                                <i class="fas fa-check"></i> Mark as Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 