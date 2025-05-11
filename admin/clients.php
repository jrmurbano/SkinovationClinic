<?php
session_start();
include '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Handle account status toggle (active/inactive)
if (isset($_POST['toggle_status']) && isset($_POST['client_id'])) {
    $client_id = intval($_POST['client_id']);
    $current_status = intval($_POST['current_status']);
    $new_status = $current_status ? 0 : 1;

    $stmt = $conn->prepare('UPDATE users SET is_active = ? WHERE id = ? AND is_admin = 0');
    $stmt->bind_param('ii', $new_status, $client_id);
    $stmt->execute();

    // Redirect to prevent form resubmission
    header('Location: clients.php');
    exit();
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Build search condition
$search_condition = '';
$search_params = [];
$where_conditions = ['u.is_admin = 0'];

if (!empty($search)) {
    $where_conditions[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $search_params = ["%$search%", "%$search%", "%$search%"];
}

if ($status_filter !== 'all') {
    $is_active = $status_filter === 'active' ? 1 : 0;
    $where_conditions[] = 'u.is_active = ' . $is_active;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total number of clients (for pagination)
$count_sql = 'SELECT COUNT(*) as total FROM users u WHERE ' . $where_clause;

if (!empty($search_params)) {
    $stmt = $conn->prepare($count_sql);
    $types = str_repeat('s', count($search_params));
    $stmt->bind_param($types, ...$search_params);
    $stmt->execute();
    $total_records = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $result = $conn->query($count_sql);
    $total_records = $result->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $records_per_page);

// Validate sort parameters
$valid_sort_fields = ['name', 'email', 'created_at', 'last_login', 'appointment_count', 'total_spent'];
$valid_sort_orders = ['asc', 'desc'];

if (!in_array($sort_by, $valid_sort_fields)) {
    $sort_by = 'name';
}

if (!in_array($sort_order, $valid_sort_orders)) {
    $sort_order = 'asc';
}

// Get clients with appointment counts, total spending, and last login
$sql = "SELECT
            u.id,
            u.name,
            u.email,
            u.phone,
            u.created_at,
            u.last_login,
            u.is_active,
            COUNT(DISTINCT a.id) as appointment_count,
            SUM(CASE WHEN a.status = 'completed' THEN s.price ELSE 0 END) as total_spent,
            MAX(a.appointment_date) as last_appointment
        FROM
            users u
        LEFT JOIN
            appointments a ON u.id = a.user_id
        LEFT JOIN
            services s ON a.service_id = s.id
        WHERE
            $where_clause
        GROUP BY
            u.id
        ORDER BY
            $sort_by $sort_order
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!empty($search_params)) {
    $types = str_repeat('s', count($search_params)) . 'ii';
    $params = array_merge($search_params, [$offset, $records_per_page]);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $offset, $records_per_page);
}

$stmt->execute();
$result = $stmt->get_result();

$clients = [];
while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
}

// Get activity counts for each client
$client_ids = array_column($clients, 'id');
if (!empty($client_ids)) {
    $placeholders = implode(',', array_fill(0, count($client_ids), '?'));

    $activity_sql = "SELECT
                        user_id,
                        COUNT(*) as login_count,
                        MAX(login_time) as last_activity
                    FROM
                        user_activity
                    WHERE
                        user_id IN ($placeholders)
                    GROUP BY
                        user_id";

    $stmt = $conn->prepare($activity_sql);
    $types = str_repeat('i', count($client_ids));
    $stmt->bind_param($types, ...$client_ids);
    $stmt->execute();
    $activity_result = $stmt->get_result();

    $activity_data = [];
    while ($row = $activity_result->fetch_assoc()) {
        $activity_data[$row['user_id']] = $row;
    }

    // Add activity data to clients array
    foreach ($clients as &$client) {
        $client['login_count'] = isset($activity_data[$client['id']]) ? $activity_data[$client['id']]['login_count'] : 0;
        $client['last_activity'] = isset($activity_data[$client['id']]) ? $activity_data[$client['id']]['last_activity'] : null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Management - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                            <a class="nav-link" href="attendants.php">
                                <i class="bi bi-person-badge me-2"></i>
                                Attendants
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
                    <h1 class="h2">Client Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="exportClientData()">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form action="" method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                placeholder="Search clients..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if (!empty($search) || $status_filter !== 'all' || $sort_by !== 'name' || $sort_order !== 'asc'): ?>
                            <a href="clients.php" class="btn btn-outline-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end mt-3 mt-md-0">
                            <div class="me-3">
                                <select class="form-select form-select-sm" id="statusFilter" onchange="applyFilters()">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Clients</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active Only</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
                                </select>
                            </div>
                            <span class="text-muted align-self-center">Total: <?php echo $total_records; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Clients Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Client List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="clickable" onclick="sortTable('name')">
                                            Name
                                            <?php if ($sort_by === 'name'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php endif; ?>
                                        </th>
                                        <th class="clickable" onclick="sortTable('email')">
                                            Email
                                            <?php if ($sort_by === 'email'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php endif; ?>
                                        </th>
                                        <th>Phone</th>
                                        <th class="clickable" onclick="sortTable('appointment_count')">
                                            Appointments
                                            <?php if ($sort_by === 'appointment_count'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php endif; ?>
                                        </th>
                                        <th class="clickable" onclick="sortTable('total_spent')">
                                            Total Spent
                                            <?php if ($sort_by === 'total_spent'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php endif; ?>
                                        </th>
                                        <th class="clickable" onclick="sortTable('last_login')">
                                            Last Login
                                            <?php if ($sort_by === 'last_login'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php endif; ?>
                                        </th>
                                        <th class="clickable" onclick="sortTable('created_at')">
                                            Registered
                                            <?php if ($sort_by === 'created_at'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'asc' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php endif; ?>
                                        </th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($clients)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No clients found</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($clients as $client): ?>
                                    <tr class="client-row <?php echo $client['is_active'] ? '' : 'inactive'; ?>">
                                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                                        <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                        <td><?php echo $client['appointment_count']; ?></td>
                                        <td>₱<?php echo number_format($client['total_spent'], 2); ?></td>
                                        <td>
                                            <?php if ($client['last_login']): ?>
                                            <?php echo date('M d, Y g:i A', strtotime($client['last_login'])); ?>
                                            <?php else: ?>
                                            <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                                        <td>
                                            <span class="badge status-badge <?php echo $client['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $client['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <div class="btn-group">
                                                <a href="client-services.php?client_id=<?php echo $client['id']; ?>"
                                                    class="btn btn-sm btn-primary" title="View Services">
                                                    <i class="bi bi-list-check"></i>
                                                </a>
                                                <a href="client-activity.php?client_id=<?php echo $client['id']; ?>"
                                                    class="btn btn-sm btn-info" title="View Activity">
                                                    <i class="bi bi-activity"></i>
                                                </a>
                                                <a href="edit-client.php?client_id=<?php echo $client['id']; ?>"
                                                    class="btn btn-sm btn-warning" title="Edit Client">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm <?php echo $client['is_active'] ? 'btn-secondary' : 'btn-success'; ?>"
                                                    title="<?php echo $client['is_active'] ? 'Deactivate' : 'Activate'; ?> Account"
                                                    onclick="toggleStatus(<?php echo $client['id']; ?>, <?php echo $client['is_active']; ?>)">
                                                    <i class="bi bi-<?php echo $client['is_active'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    title="Delete Client"
                                                    onclick="confirmDelete(<?php echo $client['id']; ?>, '<?php echo htmlspecialchars($client['name']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>">Previous</a>
                                </li>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Status Toggle Form (Hidden) -->
    <form id="statusForm" action="" method="POST" style="display: none;">
        <input type="hidden" name="toggle_status" value="1">
        <input type="hidden" name="client_id" id="status_client_id">
        <input type="hidden" name="current_status" id="current_status">
    </form>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the client <strong id="clientName"></strong>?</p>
                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All client data,
                        appointments, and feedback will be permanently deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete Client</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to export client data to CSV
        function exportClientData() {
            window.location.href =
                'export-clients.php?search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>';
        }

        // Function to toggle client status (active/inactive)
        function toggleStatus(clientId, currentStatus) {
            document.getElementById('status_client_id').value = clientId;
            document.getElementById('current_status').value = currentStatus;
            document.getElementById('statusForm').submit();
        }

        // Function to confirm client deletion
        function confirmDelete(clientId, clientName) {
            document.getElementById('clientName').textContent = clientName;
            document.getElementById('confirmDeleteBtn').href = 'delete-client.php?client_id=' + clientId;

            // Show the modal
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        // Function to sort the table
        function sortTable(column) {
            let currentSort = '<?php echo $sort_by; ?>';
            let currentOrder = '<?php echo $sort_order; ?>';

            let newOrder = 'asc';
            if (column === currentSort && currentOrder === 'asc') {
                newOrder = 'desc';
            }

            window.location.href = 'clients.php?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=' +
                column + '&order=' + newOrder;
        }

        // Function to apply filters
        function applyFilters() {
            let status = document.getElementById('statusFilter').value;
            window.location.href = 'clients.php?page=1&search=<?php echo urlencode($search); ?>&status=' + status +
                '&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>';
        }
    </script>
</body>

</html>
