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
$stmt = $conn->prepare('SELECT id, name, email, phone, is_active FROM users WHERE id = ? AND is_admin = 0');
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: clients.php');
    exit();
}

$client = $result->fetch_assoc();

// Process form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = trim($_POST['password']); // Validate inputs
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Name, email, and phone are required fields.';
    } elseif (strlen($name) > 100) {
        $error = 'Name must not exceed 100 characters.';
    } elseif (strlen($username) > 50) {
        $error = 'Username must not exceed 50 characters.';
    } elseif (strlen($phone) > 20 || !ctype_digit($phone)) {
        $error = 'Phone number must be numeric and not longer than 20 digits.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists (excluding current client)
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $client_id);
        $stmt->execute();
        $email_result = $stmt->get_result();

        if ($email_result->num_rows > 0) {
            $error = 'Email address is already in use by another account.';
        } else {
            // Update client information
            if (empty($password)) {
                // Update without changing password
                $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ?, is_active = ? WHERE id = ?');
                $stmt->bind_param('sssii', $name, $email, $phone, $is_active, $client_id);
            } else {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ?, is_active = ?, password = ? WHERE id = ?');
                $stmt->bind_param('sssisi', $name, $email, $phone, $is_active, $hashed_password, $client_id);
            }

            if ($stmt->execute()) {
                // Log the action
                $admin_id = $_SESSION['user_id'];
                $action = "Updated client account (ID: $client_id)";
                $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_id, target_type, created_at) VALUES (?, ?, ?, 'user', NOW())");
                $stmt->bind_param('isi', $admin_id, $action, $client_id);
                $stmt->execute();

                $success = true;

                // Update client data for display
                $client['name'] = $name;
                $client['email'] = $email;
                $client['phone'] = $phone;
                $client['is_active'] = $is_active;
            } else {
                $error = 'An error occurred while updating the client information.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client - Skinovation Beauty Clinic</title>
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
                    <h1 class="h2">Edit Client</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="clients.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Clients
                        </a>
                    </div>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> Client information has been updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Edit Client Information</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="mb-3"> <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="<?php echo htmlspecialchars($client['name']); ?>" maxlength="100" required>
                                        <div class="form-text">Maximum 100 characters allowed.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo htmlspecialchars($client['email']); ?>" required>
                                    </div>

                                    <div class="mb-3"> <label for="phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            value="<?php echo htmlspecialchars($client['phone']); ?>" maxlength="20" pattern="\d+" required
                                            oninput="this.value = this.value.replace(/\D/g,'')">
                                        <div class="form-text">Phone number must be numeric and not longer than 20
                                            digits.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <div class="form-text">Leave blank to keep the current password.</div>
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active"
                                            name="is_active" <?php echo $client['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Account is active</label>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i> Save Changes
                                        </button>
                                        <a href="clients.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-2">
                                    <a href="client-services.php?client_id=<?php echo $client_id; ?>" class="btn btn-info">
                                        <i class="bi bi-list-check me-1"></i> View Services
                                    </a>
                                    <a href="client-activity.php?client_id=<?php echo $client_id; ?>"
                                        class="btn btn-secondary">
                                        <i class="bi bi-activity me-1"></i> View Activity
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal">
                                        <i class="bi bi-trash me-1"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the client <strong><?php echo htmlspecialchars($client['name']); ?></strong>?</p>
                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All client data,
                        appointments, and feedback will be permanently deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="delete-client.php?client_id=<?php echo $client_id; ?>" class="btn btn-danger">Delete
                        Client</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
