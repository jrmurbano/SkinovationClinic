<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $appointment_id = clean($_POST['appointment_id']);
    $action = clean($_POST['action']);
    $type = clean($_POST['type']);
    
    if ($action === 'confirm' || $action === 'cancel') {
        $conn->beginTransaction();
        try {
            // Get appointment details based on type
            if ($type === 'service') {
                $stmt = $conn->prepare("
                    SELECT a.*, p.patient_id, p.first_name, p.last_name, s.service_name as name
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
            } else if ($type === 'package') {
                $stmt = $conn->prepare("
                    SELECT pa.*, pb.patient_id, p.first_name, p.last_name, pk.package_name as name
                    FROM package_appointments pa
                    JOIN package_bookings pb ON pa.booking_id = pb.booking_id
                    JOIN packages pk ON pb.package_id = pk.package_id
                    JOIN patients p ON pb.patient_id = p.patient_id
                    WHERE pa.package_appointment_id = ?
                ");
                $stmt->execute([$appointment_id]);
                $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$appointment) {
                    throw new Exception('Package appointment not found');
                }

                // Update package appointment status
                $status = ($action === 'confirm') ? 'confirmed' : 'cancelled';
                $stmt = $conn->prepare("UPDATE package_appointments SET status = ? WHERE package_appointment_id = ?");
                $stmt->execute([$status, $appointment_id]);
            } else {
                throw new Exception('Invalid appointment type');
            }

            // Create notification for patient
            $title = ucfirst($type) . " Appointment " . ucfirst($status);
            $message = sprintf(
                "Your %s appointment for %s on %s at %s has been %s.",
                $type,
                $appointment['name'],
                date('F j, Y', strtotime($appointment['appointment_date'])),
                date('g:i A', strtotime($appointment['appointment_time'])),
                $status
            );

            createNotification($conn, 'appointment', $appointment_id, $title, $message, $appointment['patient_id']);

            $conn->commit();
            $_SESSION['success'] = ucfirst($type) . " appointment has been " . $status . " successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
        
        // Redirect to prevent form resubmission
        header('Location: appointments.php');
        exit();
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? clean($_GET['status']) : '';
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Build the query
$query = "
    SELECT 
        'service' as type,
        a.appointment_id as id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        p.first_name,
        p.last_name,
        p.phone,
        s.service_name as name,
        s.price,
        NULL as package_id
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.product_id IS NULL

    UNION ALL

    SELECT 
        'package' as type,
        pa.package_appointment_id as id,
        pa.appointment_date,
        pa.appointment_time,
        pa.status,
        p.first_name,
        p.last_name,
        p.phone,
        pk.package_name as name,
        pk.price,
        pk.package_id
    FROM package_appointments pa
    JOIN package_bookings pb ON pa.booking_id = pb.booking_id
    JOIN packages pk ON pb.package_id = pk.package_id
    JOIN patients p ON pb.patient_id = p.patient_id

    UNION ALL

    SELECT 
        'product' as type,
        a.appointment_id as id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        p.first_name,
        p.last_name,
        p.phone,
        pr.product_name as name,
        pr.price,
        NULL as package_id
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN products pr ON a.product_id = pr.product_id
    WHERE a.product_id IS NOT NULL
";

$params = [];

if ($status_filter) {
    $query .= " AND (a.status = ? OR pa.status = ?)";
    $params[] = $status_filter;
    $params[] = $status_filter;
}

if ($search) {
    $query .= " AND (
        p.first_name LIKE ? OR 
        p.last_name LIKE ? OR 
        p.phone LIKE ? OR 
        s.service_name LIKE ? OR
        pk.package_name LIKE ?
    )";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

$query .= " ORDER BY appointment_date DESC, appointment_time DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_appointments = count($appointments);
$pending_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'pending'; }));
$confirmed_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'confirmed'; }));
$cancelled_appointments = count(array_filter($appointments, function($a) { return $a['status'] === 'cancelled'; }));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments and Products</title>
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
    /* Card styling */
    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Statistics cards */
    .card.bg-primary {
        background: linear-gradient(135deg, #4a148c 0%, #6a1b9a 100%) !important;
    }

    .card.bg-warning {
        background: linear-gradient(135deg, #ff6f00 0%, #ff8f00 100%) !important;
    }

    .card.bg-success {
        background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%) !important;
    }

    .card.bg-danger {
        background: linear-gradient(135deg, #b71c1c 0%, #c62828 100%) !important;
    }

    .card-title {
        color: #ffffff !important;
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .card-text {
        color: #ffffff !important;
        font-weight: 700;
        font-size: 2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* Table styling */
    .table {
        color: #333333;
        font-size: 0.95rem;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #4a148c;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
        padding: 12px;
    }

    .table tbody td {
        vertical-align: middle;
        padding: 12px;
        color: #333333;
    }

    /* Badge styling */
    .badge {
        padding: 8px 12px;
        font-weight: 500;
        font-size: 0.85rem;
        text-transform: capitalize;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .badge.bg-success {
        background-color: #2e7d32 !important;
        color: #ffffff !important;
    }

    .badge.bg-warning {
        background-color: #ff6f00 !important;
        color: #ffffff !important;
    }

    .badge.bg-danger {
        background-color: #c62828 !important;
        color: #ffffff !important;
    }

    /* Button styling */
    .btn {
        font-weight: 500;
    }

    .btn-primary {
        background-color: #4a148c;
        border-color: #4a148c;
    }

    .btn-primary:hover {
        background-color: #6a1b9a;
        border-color: #6a1b9a;
    }

    .btn-info {
        background-color: #01579b;
        border-color: #01579b;
        color: #ffffff;
    }

    .btn-info:hover {
        background-color: #0277bd;
        border-color: #0277bd;
        color: #ffffff;
    }

    /* Form controls */
    .form-control, .form-select {
        border: 1px solid #ced4da;
        color: #333333;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #6a1b9a;
        box-shadow: 0 0 0 0.2rem rgba(106, 27, 154, 0.25);
    }

    .form-label {
        color: #4a148c;
        font-weight: 500;
        font-size: 0.95rem;
    }

    /* Alert styling */
    .alert {
        border: none;
        border-radius: 4px;
        font-size: 0.95rem;
    }

    .alert-success {
        background-color: #e8f5e9;
        color:rgb(0, 0, 0);
    }

    .alert-danger {
        background-color: #ffebee;
        color: #c62828;
    }

    /* Page title */
    h1 {
        color: #4a148c;
        font-weight: 600;
        font-size: 1.75rem;
    }

    /* Table hover effect */
    .table-hover tbody tr:hover {
        background-color: rgba(74, 20, 140, 0.05);
    }

    /* Price formatting */
    td:nth-child(6) {
        font-weight: 600;
        color:rgb(104, 24, 24);
    }

    /* Action buttons */
    .btn-group .btn {
        padding: 0.375rem 0.75rem;
    }

    .btn-group .btn i {
        font-size: 0.9rem;
    }

    /* Add custom badge color */
    .bg-purple {
        background: linear-gradient(135deg, #6f42c1 0%, #8250c8 100%) !important;
        color: white;
    }

    .bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }

    .bg-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
    }

    /* Add new badge style for products */
    .badge.bg-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%) !important;
        color: white;
    }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-calendar-alt"></i> Manage Appointments and Products</h1>
                <div>
                    
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Appointments</h5>
                            <h2 class="mb-0"><?php echo $total_appointments; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2 class="mb-0"><?php echo $pending_appointments; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Confirmed</h5>
                            <h2 class="mb-0"><?php echo $confirmed_appointments; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Cancelled</h5>
                            <h2 class="mb-0"><?php echo $cancelled_appointments; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
             
                    <form method="GET" class="row g-3">
                         
                        
                       
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Patient Name</th>
                                    <th>Contact</th>
                                    <th>Service/Package</th>
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
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $appointment['type'] === 'service' ? 'primary' : 
                                                ($appointment['type'] === 'package' ? 'success' : 
                                                ($appointment['type'] === 'product' ? 'info' : '')); 
                                        ?>" style="<?php 
                                            echo $appointment['type'] === 'service' ? 'background: linear-gradient(135deg, #4a148c 0%, #6a1b9a 100%) !important;' : 
                                                ($appointment['type'] === 'package' ? 'background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%) !important;' : 
                                                ($appointment['type'] === 'product' ? 'background: linear-gradient(135deg, #0288d1 0%, #039be5 100%) !important;' : '')); 
                                        ?>">
                                            <?php echo ucfirst($appointment['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo clean($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                    <td>
                                        <i class="fas fa-phone"></i> <?php echo clean($appointment['phone']); ?>
                                    </td>
                                    <td><?php echo clean($appointment['name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    <td>₱<?php echo number_format($appointment['price'], 2); ?></td>
                                    <td>
                                        <?php if ($appointment['type'] !== 'product'): ?>
                                        <span class="badge bg-<?php 
                                            echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                        <?php else: ?>
                                        <span>Pre-ordered</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($appointment['status'] === 'pending' && $appointment['type'] !== 'product'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="confirm">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <input type="hidden" name="type" value="<?php echo $appointment['type']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to confirm this appointment?')">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <input type="hidden" name="type" value="<?php echo $appointment['type']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        <?php elseif ($appointment['type'] === 'product'): ?>
                                            <span>-</span>
                                        <?php endif; ?>
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

// Add real-time notification check
function checkNewAppointments() {
    fetch('check_new_appointments.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                // Show notification
                const notification = new Notification('New Appointment', {
                    body: 'A new appointment has been booked!',
                    icon: '../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png'
                });
                
                // Reload the page to show new appointment
                location.reload();
            }
        });
}

// Check for new appointments every 30 seconds
setInterval(checkNewAppointments, 30000);

// Request notification permission
if (Notification.permission !== 'granted') {
    Notification.requestPermission();
}
</script>
</body>
</html>
