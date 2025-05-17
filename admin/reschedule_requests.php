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
    <title>Reschedule Requests - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
<main class="content" style="background: url('../assets/img/hero-bg.jpg') no-repeat center center fixed, linear-gradient(135deg, #f8fafc 0%, #ede7f6 100%); background-size: cover, auto; min-height: 100vh;">
    <div class="container-fluid">
        <h1 class="mb-4" style="color: #6f42c1; font-size: 2.2rem; font-weight: 700; letter-spacing: 1px;"><i class="fas fa-calendar-alt"></i> Reschedule Requests</h1>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr style="font-size: 1.08rem;">
                                <th>#</th>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Current Date</th>
                                <th>Requested Date</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($reschedule_requests)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No reschedule requests found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reschedule_requests as $i => $req): ?>
                                <tr style="font-size: 1.05rem;">
                                    <td><?= $i+1 ?></td>
                                    <td><?= clean($req['patient_name']) ?></td>
                                    <td><?= clean($req['service_name']) ?></td>
                                    <td><?= $req['appointment_date'] ? date('M d, Y h:i A', strtotime($req['appointment_date'])) : '-' ?></td>
                                    <td><?= $req['requested_date'] ? date('M d, Y h:i A', strtotime($req['requested_date'])) : '-' ?></td>
                                    <td><?= isset($req['reschedule_reason']) ? clean($req['reschedule_reason']) : '-' ?></td>
                                    <td><?= clean($req['status']) ?></td>
                                    <td><?= isset($req['updated_at']) ? date('M d, Y h:i A', strtotime($req['updated_at'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
