<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

// Check if client_id is provided
if (!isset($_GET['client_id'])) {
    header('Location: clients.php');
    exit();
}

$client_id = intval($_GET['client_id']);

// Get client information
$stmt = $conn->prepare('SELECT patient_id, name, username, phone, created_at, updated_at FROM patients WHERE patient_id = ?');
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: clients.php');
    exit();
}

$client = $result->fetch_assoc();

// Get client activity logs
$stmt = $conn->prepare("
    SELECT
        id,
        activity_type,
        ip_address,
        user_agent,
        login_time,
        logout_time,
        session_duration
    FROM
        user_activity
    WHERE
        user_id = ?
    ORDER BY
        login_time DESC
    LIMIT 100
");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$activity_result = $stmt->get_result();

$activities = [];
while ($row = $activity_result->fetch_assoc()) {
    $activities[] = $row;
}

// Get client's recent appointments
$stmt = $conn->prepare("
    SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.created_at as booked_on,
        s.name as service_name,
        d.name as dermatologist_name
    FROM
        appointments a
    JOIN
        services s ON a.service_id = s.id
    JOIN
        dermatologists d ON a.dermatologist_id = d.id
    WHERE
        a.user_id = ?
    ORDER BY
        a.created_at DESC
    LIMIT 10
");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$appointments_result = $stmt->get_result();

$recent_appointments = [];
while ($row = $appointments_result->fetch_assoc()) {
    $recent_appointments[] = $row;
}

// Get login statistics
$stmt = $conn->prepare("
    SELECT
        COUNT(*) as total_logins,
        MAX(login_time) as last_login,
        MIN(login_time) as first_login,
        AVG(session_duration) as avg_session_duration
    FROM
        user_activity
    WHERE
        user_id = ? AND
        activity_type = 'login'
");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get login activity by month (for chart)
$stmt = $conn->prepare("
    SELECT
        DATE_FORMAT(login_time, '%Y-%m') as month,
        COUNT(*) as login_count
    FROM
        user_activity
    WHERE
        user_id = ? AND
        activity_type = 'login' AND
        login_time >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY
        DATE_FORMAT(login_time, '%Y-%m')
    ORDER BY
        month ASC
");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$monthly_result = $stmt->get_result();

$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $month_name = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_data[$month_name] = $row['login_count'];
}

// Fill in missing months with zero
$end_date = new DateTime();
$start_date = new DateTime();
$start_date->modify('-5 months');

$period = new DatePeriod($start_date, new DateInterval('P1M'), $end_date);

$complete_monthly_data = [];
foreach ($period as $date) {
    $month_name = $date->format('M Y');
    $complete_monthly_data[$month_name] = isset($monthly_data[$month_name]) ? $monthly_data[$month_name] : 0;
}

// Convert to JSON for charts
$monthly_json = json_encode([
    'labels' => array_keys($complete_monthly_data),
    'data' => array_values($complete_monthly_data),
]);

// Get browser statistics
$stmt = $conn->prepare("
    SELECT
        SUBSTRING_INDEX(user_agent, ' ', 1) as browser,
        COUNT(*) as count
    FROM
        user_activity
    WHERE
        user_id = ?
    GROUP BY
        SUBSTRING_INDEX(user_agent, ' ', 1)
    ORDER BY
        count DESC
");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$browser_result = $stmt->get_result();

$browser_data = [];
while ($row = $browser_result->fetch_assoc()) {
    $browser_data[$row['browser']] = $row['count'];
}

$browser_json = json_encode([
    'labels' => array_keys($browser_data),
    'data' => array_values($browser_data),
]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Activity - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Beauty Clinic</h5>
                        <p class="text-white-50">Admin Dashboard</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="appointments.php">
                                <i class="bi bi-calendar-check me-2"></i>
                                Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services.php">
                                <i class="bi bi-list-check me-2"></i>
                                Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dermatologists.php">
                                <i class="bi bi-person-badge me-2"></i>
                                Dermatologists
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="clients.php">
                                <i class="bi bi-people me-2"></i>
                                Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="bi bi-graph-up me-2"></i>
                                Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Client Activity</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="exportActivityData()">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                        </div>
                        <a href="clients.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Clients
                        </a>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Client Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 fw-bold">Name:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($client['name']); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 fw-bold">Email:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($client['email']); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 fw-bold">Phone:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($client['phone']); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 fw-bold">Status:</div>
                                    <div class="col-md-8">
                                        <span class="badge <?php echo $client['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $client['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 fw-bold">Registered On:</div>
                                    <div class="col-md-8"><?php echo date('M d, Y', strtotime($client['created_at'])); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 fw-bold">Last Login:</div>
                                    <div class="col-md-8">
                                        <?php if ($client['last_login']): ?>
                                        <?php echo date('M d, Y g:i A', strtotime($client['last_login'])); ?>
                                        <?php else: ?>
                                        <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex gap-2">
                                    <a href="client-services.php?client_id=<?php echo $client_id; ?>"
                                        class="btn btn-primary btn-sm">
                                        <i class="bi bi-list-check me-1"></i> View Services
                                    </a>
                                    <a href="edit-client.php?client_id=<?php echo $client_id; ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil me-1"></i> Edit Client
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Login Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="stat-item">
                                            <h6 class="text-muted">Total Logins</h6>
                                            <h3><?php echo $stats['total_logins'] ?: 0; ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="stat-item">
                                            <h6 class="text-muted">First Login</h6>
                                            <h3>
                                                <?php if ($stats['first_login']): ?>
                                                <?php echo date('M d, Y', strtotime($stats['first_login'])); ?>
                                                <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="stat-item">
                                            <h6 class="text-muted">Last Login</h6>
                                            <h3>
                                                <?php if ($stats['last_login']): ?>
                                                <?php echo date('M d, Y', strtotime($stats['last_login'])); ?>
                                                <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="stat-item">
                                            <h6 class="text-muted">Avg. Session Duration</h6>
                                            <h3>
                                                <?php if ($stats['avg_session_duration']): ?>
                                                <?php
                                                $minutes = floor($stats['avg_session_duration'] / 60);
                                                $seconds = $stats['avg_session_duration'] % 60;
                                                echo $minutes . 'm ' . $seconds . 's';
                                                ?>
                                                <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Monthly Login Activity</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="loginChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Browser Usage</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="browserChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Appointments</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_appointments)): ?>
                                <div class="alert alert-info">No appointments found for this client.</div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Booked On</th>
                                                <th>Appointment Date</th>
                                                <th>Service</th>
                                                <th>Dermatologist</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y g:i A', strtotime($appointment['booked_on'])); ?></td>
                                                <td>
                                                    <?php
                                                    echo date('M d, Y', strtotime($appointment['appointment_date']));
                                                    echo ' at ';
                                                    echo date('g:i A', strtotime($appointment['appointment_time']));
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                                <td>Dr. <?php echo htmlspecialchars($appointment['dermatologist_name']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($appointment['status']) {
                                                        case 'pending':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'confirmed':
                                                            $status_class = 'bg-primary';
                                                            break;
                                                        case 'completed':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'cancelled':
                                                            $status_class = 'bg-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Activity Log</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="refreshActivityBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activities)): ?>
                        <div class="alert alert-info">No activity logs found for this client.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Activity</th>
                                        <th>IP Address</th>
                                        <th>Browser</th>
                                        <th>Session Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y g:i A', strtotime($activity['login_time'])); ?></td>
                                        <td>
                                            <?php
                                            switch ($activity['activity_type']) {
                                                case 'login':
                                                    echo '<span class="badge bg-success">Login</span>';
                                                    break;
                                                case 'logout':
                                                    echo '<span class="badge bg-secondary">Logout</span>';
                                                    break;
                                                case 'password_reset':
                                                    echo '<span class="badge bg-warning">Password Reset</span>';
                                                    break;
                                                case 'profile_update':
                                                    echo '<span class="badge bg-info">Profile Update</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-primary">' . ucfirst($activity['activity_type']) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['user_agent']); ?></td>
                                        <td>
                                            <?php
                                            if ($activity['session_duration']) {
                                                $minutes = floor($activity['session_duration'] / 60);
                                                $seconds = $activity['session_duration'] % 60;
                                                echo $minutes . 'm ' . $seconds . 's';
                                            } elseif ($activity['activity_type'] == 'login' && !$activity['logout_time']) {
                                                echo '<span class="badge bg-primary">Active</span>';
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Login Chart
            const loginData = <?php echo $monthly_json; ?>;
            const loginCtx = document.getElementById('loginChart').getContext('2d');
            const loginChart = new Chart(loginCtx, {
                type: 'bar',
                data: {
                    labels: loginData.labels,
                    datasets: [{
                        label: 'Login Count',
                        data: loginData.data,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Browser Chart
            const browserData = <?php echo $browser_json; ?>;
            const browserCtx = document.getElementById('browserChart').getContext('2d');
            const browserChart = new Chart(browserCtx, {
                type: 'pie',
                data: {
                    labels: browserData.labels,
                    datasets: [{
                        data: browserData.data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });

            // Refresh activity log
            document.getElementById('refreshActivityBtn').addEventListener('click', function() {
                window.location.reload();
            });
        });

        // Function to export activity data
        function exportActivityData() {
            window.location.href = 'export-client-activity.php?client_id=<?php echo $client_id; ?>';
        }
    </script>
</body>

</html>
