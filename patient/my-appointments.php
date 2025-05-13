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
            a.notes,
            s.service_name as name,
            s.price,
            att.first_name as attendant_first_name,
            att.last_name as attendant_last_name
        FROM appointments a
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN attendants att ON a.attendant_id = att.attendant_id
        WHERE a.patient_id = ?
        
        UNION ALL
        
        SELECT 
            'package' as appointment_type,
            pa.package_appointment_id as appointment_id,
            pa.appointment_date,
            pa.appointment_time,
            pa.status,
            '' as notes,
            p.package_name as name,
            p.price,
            att.first_name as attendant_first_name,
            att.last_name as attendant_last_name
        FROM package_appointments pa
        JOIN package_bookings pb ON pa.booking_id = pb.booking_id
        JOIN packages p ON pb.package_id = p.package_id
        LEFT JOIN attendants att ON pa.attendant_id = att.attendant_id
        WHERE pb.patient_id = ?
        
        ORDER BY appointment_date DESC, appointment_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(1, $patient_id, PDO::PARAM_INT);
$stmt->bindValue(2, $patient_id, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Skinovation Clinic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
    </style>
</head>

<body>
    <?php include 'patient_header.php'; ?> <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10 card shadow-sm">
                <h2 class="text-center mb-4">My Appointments</h2>

                <?php if (count($appointments) > 0): ?> <div class="appointments-list">
                    <?php foreach ($appointments as $appointment): ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <h3><?php echo clean($appointment['name']); ?></h3>
                            <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                <?php echo ucfirst($appointment['status']); ?>
                                <?php echo $appointment['appointment_type'] === 'package' ? ' (Package)' : ''; ?>
                            </span>
                        </div>
                        <div class="appointment-details">
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                            <p><strong>Attendant:</strong> <?php echo clean($appointment['attendant_first_name'] . ' ' . $appointment['attendant_last_name']); ?></p>
                            <p><strong>Price:</strong> ₱<?php echo number_format($appointment['price'], 2); ?></p>
                            <?php if (!empty($appointment['notes'])): ?>
                            <p><strong>Notes:</strong> <?php echo clean($appointment['notes']); ?></p>
                            <?php endif; ?>
                        </div> <?php if ($appointment['status'] === 'pending'): ?>
                        <div class="mt-3">
                            <a href="cancel_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to cancel this appointment?' + 
                                      '<?php echo $appointment['appointment_type'] === 'package' ? '\nYour session will be returned to your package.' : ''; ?>');">
                                Cancel Appointment
                            </a>
                        </div>
                        <?php endif; ?>
                    </div> <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="card shadow-sm text-center">
                    <div class="card-body no-appointments text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-calendar-alt fa-4x text-muted"></i>
                        </div>
                        <h3 class="mb-3">No Appointments Yet</h3>
                        <p class="text-muted mb-4">You haven't booked any appointments yet. Start your journey to better
                            skin today!</p>
                        <a href="booking.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus-circle me-2"></i>Book Your First Appointment
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>
