<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $appointment_id = clean($_POST['appointment_id']);
    $action = clean($_POST['action']);
    
    if ($action === 'confirm' || $action === 'cancel') {
        $conn->beginTransaction();
        try {
            // Get appointment details
            $stmt = $conn->prepare("
                SELECT a.*, p.patient_id, p.first_name, p.last_name, s.service_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.patient_id
                JOIN services s ON a.service_id = s.service_id
                WHERE a.appointment_id = ?
            ");
            $stmt->execute([$appointment_id]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$appointment) {
                throw new Exception('Appointment not found');
            }

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
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
        
        // Redirect to prevent form resubmission
        header('Location: manage.php');
        exit();
    }
}

// Fetch all appointments with patient and service details
$stmt = $conn->query("
    SELECT a.*, p.first_name, p.last_name, p.phone, s.service_name, s.price
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN services s ON a.service_id = s.service_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link rel="icon" type="image/png" href="../../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/admin.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include '../admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-calendar-check"></i> Manage Appointments</h1>
                <div>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Contact</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo clean($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                    <td>
                                        <i class="fas fa-phone"></i> <?php echo clean($appointment['phone']); ?>
                                    </td>
                                    <td><?php echo clean($appointment['service_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    <td>₱<?php echo number_format($appointment['price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($appointment['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                <button type="submit" name="action" value="confirm" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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