<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$appointment_id = clean($_GET['id']);

// Fetch appointment details with patient and service information
$stmt = $conn->prepare("
    SELECT a.*, p.first_name, p.last_name, p.email, p.phone, p.address,
           s.service_name, s.price, s.description as service_description
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.appointment_id = ?
");
$stmt->execute([$appointment_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header('Location: index.php');
    exit();
}

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = clean($_POST['action']);
    
    if ($action === 'confirm' || $action === 'cancel') {
        $conn->beginTransaction();
        try {
            // Update appointment status
            $status = ($action === 'confirm') ? 'confirmed' : 'cancelled';
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->execute([$status, $appointment_id]);

            // Create notification for patient
            $title = "Appointment " . ucfirst($status);
            $message = sprintf(
                "Your appointment for %s on %s at %s has been %s.",
                $appointment['service_name'],
                date('F j, Y', strtotime($appointment['appointment_date'])),
                date('g:i A', strtotime($appointment['appointment_time'])),
                $status
            );

            createNotification($conn, 'appointment', $appointment_id, $title, $message, $appointment['patient_id']);

            $conn->commit();
            $_SESSION['success'] = "Appointment has been " . $status . " successfully.";
            
            // Refresh appointment data
            $stmt = $conn->prepare("
                SELECT a.*, p.first_name, p.last_name, p.email, p.phone, p.address,
                       s.service_name, s.price, s.description as service_description
                FROM appointments a
                JOIN patients p ON a.patient_id = p.patient_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.appointment_id = ?
            ");
            $stmt->execute([$appointment_id]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://t3.ftcdn.net/jpg/06/19/41/14/360_F_619411419_TI1j5q8ItTz6VTFxFtSUm0m8n5wYSWNy.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }

        .content {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
        }

        .card-header {
            background-color: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-calendar-check"></i> Appointment Details</h1>
                <div>
                    <a href="manage.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Appointments
                    </a>
                </div>
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

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Patient Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?php echo clean($appointment['first_name'] . ' ' . $appointment['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo clean($appointment['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo clean($appointment['phone']); ?></p>
                            <p><strong>Address:</strong> <?php echo clean($appointment['address']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Appointment Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Service:</strong> <?php echo clean($appointment['service_name']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                            <p><strong>Price:</strong> ₱<?php echo number_format($appointment['price'], 2); ?></p>
                            <p>
                                <strong>Status:</strong>
                                <span class="badge bg-<?php 
                                    echo $appointment['status'] === 'confirmed' ? 'success' : 
                                        ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Service Description</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(clean($appointment['service_description'])); ?></p>
                </div>
            </div>

            <?php if ($appointment['status'] === 'pending'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="d-inline">
                        <button type="submit" name="action" value="confirm" class="btn btn-success">
                            <i class="fas fa-check"></i> Confirm Appointment
                        </button>
                        <button type="submit" name="action" value="cancel" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel Appointment
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide alerts after 5 seconds
setTimeout(function() {
    $('.alert').alert('close');
}, 5000);
</script>
</body>
</html> 