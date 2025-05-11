<?php
session_start();
include '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$is_admin = true; // Set this flag for proper path resolution

// Handle Confirm or Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_id'])) {
        $confirm_id = intval($_POST['confirm_id']);
        $conn->query("UPDATE appointments SET status = 'completed' WHERE id = $confirm_id");
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $conn->query("DELETE FROM appointments WHERE id = $delete_id");
    }

    // Refresh the page after action to avoid resubmission
    header('Location: dashboard.php');
    exit();
}

// Get statistics for dashboard
function getStatistics($conn)
{
    $stats = [];

    // Total appointments
    $result = $conn->query('SELECT COUNT(*) as total FROM appointments');
    $stats['total_appointments'] = $result->fetch_assoc()['total'];

    // Pending appointments
    $result = $conn->query("SELECT COUNT(*) as pending FROM appointments WHERE status = 'pending'");
    $stats['pending_appointments'] = $result->fetch_assoc()['pending'];

    // Completed appointments
    $result = $conn->query("SELECT COUNT(*) as completed FROM appointments WHERE status = 'completed'");
    $stats['completed_appointments'] = $result->fetch_assoc()['completed']; // Total patients
    $result = $conn->query('SELECT COUNT(*) as total FROM patients WHERE is_admin = 0');
    $stats['total_users'] = $result->fetch_assoc()['total'];

    // Total revenue
    $result = $conn->query("SELECT SUM(s.price) as total_revenue FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.status = 'completed'");
    $stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?: 0;

    return $stats;
}

// Get popular services
function getPopularServices($conn)
{
    $sql = "SELECT s.name, COUNT(a.id) as booking_count
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            GROUP BY a.service_id
            ORDER BY booking_count DESC
            LIMIT 5";

    $result = $conn->query($sql);

    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }

    return $services;
}

// Get monthly revenue data for chart
function getMonthlyRevenue($conn)
{
    $sql = "SELECT
                MONTH(a.appointment_date) as month,
                YEAR(a.appointment_date) as year,
                SUM(s.price) as revenue
            FROM
                appointments a
            JOIN
                services s ON a.service_id = s.id
            WHERE
                a.status = 'completed' AND
                a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY
                YEAR(a.appointment_date), MONTH(a.appointment_date)
            ORDER BY
                year, month";

    $result = $conn->query($sql);

    $revenue_data = [];
    while ($row = $result->fetch_assoc()) {
        $month_name = date('M', mktime(0, 0, 0, $row['month'], 1));
        $revenue_data[] = [
            'month' => $month_name,
            'revenue' => $row['revenue'],
        ];
    }

    return $revenue_data;
}

// Get recent appointments
function getRecentAppointments($conn)
{
    $sql = "SELECT
                a.id, a.appointment_date, a.appointment_time, a.status,
                u.name as client_name,
                s.name as service_name,
                d.name as cosmetic nurse_name
            FROM
                appointments a
            JOIN
                users u ON a.user_id = u.id
            JOIN
                services s ON a.service_id = s.id
            JOIN
                cosmetic nurse d ON a.cosmeticnurse_id = d.id
            ORDER BY
                a.created_at DESC
            LIMIT 10";

    $result = $conn->query($sql);

    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    return $appointments;
}

$stats = getStatistics($conn);
$popular_services = getPopularServices($conn);
$monthly_revenue = getMonthlyRevenue($conn);
$recent_appointments = getRecentAppointments($conn);

// Convert monthly revenue data to JSON for chart
$revenue_json = json_encode($monthly_revenue);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Skinovation Beauty Clinic</title>
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
                        <img src="../assets/img/ISCAP1-303-Skinovation-Clinic-WHITE-Logo.png"
                            alt="Skinovation Clinic Logo" height="40" class="mb-2">
                        <h5 class="text-white">Skinovation Beauty Clinic</h5>
                        <p class="text-white-50">Admin Dashboard</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="bi bi-calendar"></i>
                            This Month
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Appointments</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_appointments']; ?></h2>
                                    </div>
                                    <i class="bi bi-calendar-check fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Revenue</h6>
                                        <h2 class="mb-0">₱<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                                    </div>
                                    <i class="bi bi-currency-dollar fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Pending Appointments</h6>
                                        <h2 class="mb-0"><?php echo $stats['pending_appointments']; ?></h2>
                                    </div>
                                    <i class="bi bi-hourglass-split fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Clients</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                                    </div>
                                    <i class="bi bi-people fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Monthly Revenue</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Popular Services</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="servicesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Recent Appointments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Service</th>
                                        <th>Cosmetic Nurse</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Actions</th> <!-- Add an "Actions" column -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['cosmetic nurse_name']); ?></td>
                                        <td>
                                            <?php
                                            echo date('M d, Y', strtotime($appointment['appointment_date']));
                                            echo ' at ';
                                            echo date('h:i A', strtotime($appointment['appointment_time']));
                                            ?>
                                        </td>
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
                                        <td>
                                            <form method="post" class="d-flex flex-column gap-1">
                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                <button type="submit" name="confirm_id" value="<?php echo $appointment['id']; ?>"
                                                    class="btn btn-sm btn-success mb-2">
                                                    Confirm
                                                </button>
                                                <?php endif; ?>
                                                <button type="submit" name="delete_id" value="<?php echo $appointment['id']; ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this appointment?');">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Revenue Chart
        const revenueData = <?php echo $revenue_json; ?>;
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.month),
                datasets: [{
                    label: 'Monthly Revenue (₱)',
                    data: revenueData.map(item => item.revenue),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Services Chart
        const servicesCtx = document.getElementById('servicesChart').getContext('2d');
        const servicesChart = new Chart(servicesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($popular_services, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($popular_services, 'booking_count')); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
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
    </script>
</body>

</html>
