<?php
session_start();

// Prevent browser caching to ensure strict authentication
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../config.php';

// Fetch appointments for the logged-in patient
$patient_id = $_SESSION['patient_id'];

// First get regular appointments
$sql = "SELECT 
            'regular' as appointment_type,
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            COALESCE(s.service_name, '') as name,
            COALESCE(s.price, 0) as price,
            COALESCE(att.first_name, '') as attendant_first_name,
            COALESCE(att.last_name, '') as attendant_last_name,
            NULL as product_id
        FROM appointments a
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN attendants att ON a.attendant_id = att.attendant_id
        WHERE a.patient_id = ? AND a.product_id IS NULL AND a.service_id IS NOT NULL
        
        UNION ALL
        
        SELECT 
            'package' as appointment_type,
            pa.package_appointment_id as appointment_id,
            pa.appointment_date,
            pa.appointment_time,
            pa.status,
            COALESCE(p.package_name, '') as name,
            COALESCE(p.price, 0) as price,
            COALESCE(att.first_name, '') as attendant_first_name,
            COALESCE(att.last_name, '') as attendant_last_name,
            NULL as product_id
        FROM package_appointments pa
        LEFT JOIN package_bookings pb ON pa.booking_id = pb.booking_id
        LEFT JOIN packages p ON pb.package_id = p.package_id
        LEFT JOIN attendants att ON pa.attendant_id = att.attendant_id
        WHERE pb.patient_id = ?

        UNION ALL

        SELECT 
            'product' as appointment_type,
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            COALESCE(p.product_name, '') as name,
            COALESCE(p.price, 0) as price,
            COALESCE(att.first_name, '') as attendant_first_name,
            COALESCE(att.last_name, '') as attendant_last_name,
            p.product_id
        FROM appointments a
        LEFT JOIN products p ON a.product_id = p.product_id
        LEFT JOIN attendants att ON a.attendant_id = att.attendant_id
        WHERE a.patient_id = ? AND a.product_id IS NOT NULL
        
        ORDER BY appointment_date DESC, appointment_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(1, $patient_id, PDO::PARAM_INT);
$stmt->bindValue(2, $patient_id, PDO::PARAM_INT);
$stmt->bindValue(3, $patient_id, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .row.justify-content-center {
            min-height: calc(100vh - 200px);
            /* Adjust based on your header/footer height */
        }

        .no-appointments {
            background-color: white;
            border-radius: 10px;
            padding: 3rem;
        }

        .empty-state-icon {
            color: #6c757d;
        }

        .no-appointments h3 {
            color: #2c3e50;
            font-weight: 600;
        }

        .no-appointments .btn-primary {
            padding: 12px 30px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .no-appointments .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .appointment-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: white;
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completed {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
        }

    </style>
</head>

<body>
<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4">My Appointments</h1>
        <p class="lead">Manage your upcoming and past appointments with ease.</p>
    </div>

    <?php
    // Added success message display for reschedule and cancel actions
    if (isset($_GET['message'])) {
        echo '<div class="alert alert-success text-center">' . htmlspecialchars($_GET['message']) . '</div>';
    }
    ?>

    <?php if (count($appointments) > 0): ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #4a148c 0%, #6a1b9a 100%) !important; color: white;">
                    <h3 class="card-title mb-0">Upcoming Service Appointments</h3>
                </div>
                <div class="card-body">
                    <div class="appointments-list">
                        <?php 
                        $hasUpcomingServices = false;
                        foreach ($appointments as $appointment): 
                            if ($appointment['appointment_type'] === 'regular' && strtotime($appointment['appointment_date']) >= strtotime(date('Y-m-d'))): 
                                $hasUpcomingServices = true;
                        ?>
                                <div class="appointment-card mb-3">
                                    <h5 style="color: #4a148c;">Service: <?php echo clean($appointment['name']); ?></h5>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                                    <p><strong>Attendant:</strong> <?php echo clean($appointment['attendant_first_name'] . ' ' . $appointment['attendant_last_name']); ?></p>
                                    <p><strong>Price:</strong> ₱<?php echo number_format((float)$appointment['price'], 2); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <?php if ($appointment['status'] === 'confirmed' || $appointment['status'] === 'pending'): ?>
                                            <a href="reschedule_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" 
                                               class="btn btn-warning btn-sm" 
                                               <?php echo (strtotime($appointment['appointment_date']) <= strtotime(date('Y-m-d'))) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-calendar-alt"></i> Reschedule
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cancelModal<?php echo $appointment['appointment_id']; ?>"
                                                    <?php echo (strtotime($appointment['appointment_date']) <= strtotime('+1 day')) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-times"></i> Request Cancellation
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (!$hasUpcomingServices): ?>
                            <div class="text-center text-muted">
                                <p>No upcoming service appointments</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%) !important; color: white;">
                    <h3 class="card-title mb-0">Upcoming Package Appointments</h3>
                </div>
                <div class="card-body">
                    <div class="appointments-list">
                        <?php 
                        $hasUpcomingPackages = false;
                        foreach ($appointments as $appointment): 
                            if ($appointment['appointment_type'] === 'package' && strtotime($appointment['appointment_date']) >= strtotime(date('Y-m-d'))): 
                                $hasUpcomingPackages = true;
                        ?>
                                <div class="appointment-card mb-3">
                                    <h5 style="color: #1b5e20;">Package: <?php echo clean($appointment['name']); ?></h5>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                                    <p><strong>Attendant:</strong> <?php echo clean($appointment['attendant_first_name'] . ' ' . $appointment['attendant_last_name']); ?></p>
                                    <p><strong>Price:</strong> ₱<?php echo number_format((float)$appointment['price'], 2); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <?php if ($appointment['status'] === 'confirmed' || $appointment['status'] === 'pending'): ?>
                                            <a href="reschedule_appointment.php?id=<?php echo $appointment['appointment_id']; ?>&type=package" 
                                               class="btn btn-warning btn-sm" 
                                               <?php echo (strtotime($appointment['appointment_date']) <= strtotime(date('Y-m-d'))) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-calendar-alt"></i> Reschedule
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cancelModal<?php echo $appointment['appointment_id']; ?>"
                                                    <?php echo (strtotime($appointment['appointment_date']) <= strtotime('+1 day')) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-times"></i> Request Cancellation
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (!$hasUpcomingPackages): ?>
                            <div class="text-center text-muted">
                                <p>No upcoming package appointments</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #0288d1 0%, #039be5 100%) !important; color: white;">
                    <h3 class="card-title mb-0">Pre-ordered Products</h3>
                </div>
                <div class="card-body">
                    <div class="appointments-list">
                        <?php foreach ($appointments as $appointment): ?>
                            <?php if ($appointment['appointment_type'] === 'product' && strtotime($appointment['appointment_date']) >= strtotime(date('Y-m-d'))): ?>
                                <div class="appointment-card mb-3">
                                    <h5 style="color: #0288d1;">Product: <?php echo clean($appointment['name']); ?></h5>
                                    <p><strong>Pickup Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    <p><strong>Pickup Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                                    <p><strong>Staff:</strong> <?php echo clean($appointment['attendant_first_name'] . ' ' . $appointment['attendant_last_name']); ?></p>
                                    <p><strong>Price:</strong> ₱<?php echo number_format($appointment['price'], 2); ?></p>
                                    <?php if ($appointment['appointment_type'] !== 'product'): ?>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <?php if ($appointment['status'] === 'confirmed' || $appointment['status'] === 'pending'): ?>
                                            <a href="reschedule_appointment.php?id=<?php echo $appointment['appointment_id']; ?>&type=product" 
                                               class="btn btn-warning btn-sm" 
                                               <?php echo (strtotime($appointment['appointment_date']) <= strtotime(date('Y-m-d'))) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-calendar-alt"></i> Reschedule
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cancelModal<?php echo $appointment['appointment_id']; ?>"
                                                    <?php echo (strtotime($appointment['appointment_date']) <= strtotime('+1 day')) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-times"></i> Request Cancellation
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">History</h3>
                </div>
                <div class="card-body">
                    <div class="appointments-list">
                        <?php foreach ($appointments as $appointment): ?>
                            <?php if (strtotime($appointment['appointment_date']) < strtotime(date('Y-m-d'))): ?>
                                <div class="appointment-card mb-3">
                                    <h5 class="text-secondary">
                                        <?php if ($appointment['appointment_type'] === 'product'): ?>
                                            Product: 
                                        <?php elseif ($appointment['appointment_type'] === 'package'): ?>
                                            Package: 
                                        <?php else: ?>
                                            Service: 
                                        <?php endif; ?>
                                        <?php echo clean($appointment['name']); ?>
                                    </h5>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                                    <p><strong>Staff:</strong> <?php echo clean($appointment['attendant_first_name'] . ' ' . $appointment['attendant_last_name']); ?></p>
                                    <p><strong>Price:</strong> ₱<?php echo number_format($appointment['price'], 2); ?></p>
                                    <p><strong>Status:</strong> <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>"> <?php echo ucfirst($appointment['status']); ?></span></p>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="text-center">
        <div class="card shadow-sm">
            <div class="card-body py-5">
                <div class="empty-state-icon mb-4">
                    <i class="fas fa-calendar-alt fa-4x text-muted"></i>
                </div>
                <h3 class="mb-3">No Appointments Yet</h3>
                <p class="text-muted mb-4">You haven't booked any appointments yet. Start your journey to better skin today!</p>
                <a href="booking.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>Book Your First Appointment
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

    <?php include '../footer.php'; ?>
    
    <!-- Add Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Add success/error message handling -->
    <script>
    $(document).ready(function() {
        // Show success message if exists
        <?php if (isset($_SESSION['success'])): ?>
            $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                '<?php echo $_SESSION['success']; ?>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>').insertAfter('.container h1').delay(5000).fadeOut();
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        // Show error message if exists
        <?php if (isset($_SESSION['error'])): ?>
            $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                '<?php echo $_SESSION['error']; ?>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>').insertAfter('.container h1').delay(5000).fadeOut();
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    });
    </script>

    <!-- Cancellation Request Modal -->
    <?php foreach ($appointments as $appointment): ?>
    <div class="modal fade" id="cancelModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?php echo $appointment['appointment_id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel<?php echo $appointment['appointment_id']; ?>">
                        <?php if ($appointment['appointment_type'] === 'product'): ?>
                            Request Product Pre-order Cancellation
                        <?php elseif ($appointment['appointment_type'] === 'package'): ?>
                            Request Package Cancellation
                        <?php else: ?>
                            Request Appointment Cancellation
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="cancel_appointment.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                        <input type="hidden" name="appointment_type" value="<?php echo $appointment['appointment_type']; ?>">
                        <p>Are you sure you want to request cancellation for this <?php echo $appointment['appointment_type']; ?>?</p>
                        <div class="mb-3">
                            <label for="reason<?php echo $appointment['appointment_id']; ?>" class="form-label">Reason for cancellation (optional):</label>
                            <textarea class="form-control" id="reason<?php echo $appointment['appointment_id']; ?>" name="reason" rows="3"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Your cancellation request will be reviewed by the admin. You will be notified once it's approved or rejected.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</body>

</html>

<?php
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
