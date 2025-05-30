<?php
// filepath: c:/laragon/www/SkinovationClinic/admin/reschedule_requests.php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch reschedule requests from appointments table where status is 'Reschedule Requested'
$stmt = $conn->prepare('
    SELECT a.*, s.service_name, CONCAT(p.first_name, " ", p.last_name) AS patient_name
    FROM appointments a
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN patients p ON a.patient_id = p.patient_id
    WHERE a.status = "Reschedule Requested"
    ORDER BY a.updated_at DESC
');
$stmt->execute();
$reschedule_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function clean($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
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
        .appointment-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .patient-info {
            background-color: var(--light-bg);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
        }
        .price-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 10px 0;
        }
        .price-value {
            font-size: 1.2em;
            font-weight: 600;
            color: var(--primary-color);
        }
        .btn-action {
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-confirm {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        .btn-cancel {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-calendar-check"></i> Manage Appointments</h1>
                <a href="appointments.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Appointments
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (count($reschedule_requests) > 0): ?>
                <?php foreach ($reschedule_requests as $appointment): ?>
                    <div class="appointment-card">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="patient-info">
                                    <h5 class="mb-2"><?php echo htmlspecialchars($appointment['patient_name']); ?></h5>
                                    <?php if (!empty($appointment['phone'])): ?>
                                    <p class="mb-1"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['phone']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <h5 class="text-primary"><?php echo htmlspecialchars($appointment['service_name']); ?></h5>
                                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                                <div class="price-section">
                                    <?php if (isset($appointment['price'])): ?>
                                    <p class="mb-0"><strong>Price:</strong> <span class="price-value">₱<?php echo number_format($appointment['price'], 2); ?></span></p>
                                    <?php endif; ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                        <input type="hidden" name="appointment_type" value="reschedule">
                                        <button type="submit" name="action" value="confirm" class="btn btn-action btn-confirm">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                        <button type="submit" name="action" value="cancel" class="btn btn-action btn-cancel">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No reschedule requests to manage.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
    </script>
</body>
</html>