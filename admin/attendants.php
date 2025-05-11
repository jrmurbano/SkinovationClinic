<?php
session_start();
include '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $bio = trim($_POST['bio']);

                if (!empty($name)) {
                    $stmt = $conn->prepare('INSERT INTO attendants (name, bio) VALUES (?, ?)');
                    $stmt->bind_param('ss', $name, $bio);
                    $stmt->execute();
                }
                break;
            case 'edit':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $bio = trim($_POST['bio']);

                if (!empty($name)) {
                    $stmt = $conn->prepare('UPDATE attendants SET name = ?, bio = ? WHERE attendant_id = ?');
                    $stmt->bind_param('ssi', $name, $bio, $id);
                    $stmt->execute();
                }
                break;
            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare('DELETE FROM attendants WHERE attendant_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                break;
        }

        // Redirect to prevent form resubmission
        header('Location: attendants.php');
        exit();
    }
}

// Get all attendants
$result = $conn->query('SELECT * FROM attendants ORDER BY name');
$attendants = [];
while ($row = $result->fetch_assoc()) {
    $attendants[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendants - Skinovation Beauty Clinic</title>
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
                            <a class="nav-link active" href="attendants.php">
                                <i class="bi bi-person-badge me-2"></i>
                                Attendants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="clients.php">
                                <i class="bi bi-people me-2"></i>
                                Clients
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
                    <h1 class="h2">Manage Attendants</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttendantModal">
                        <i class="bi bi-plus-circle me-2"></i>Add Attendant
                    </button>
                </div>

                <!-- Attendants Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Bio</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendants as $attendant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($attendant['name']); ?></td>
                                        <td><?php echo htmlspecialchars($attendant['bio']); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-warning"
                                                    onclick="editAttendant(<?php echo htmlspecialchars(json_encode($attendant)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteAttendant(<?php echo $attendant['id']; ?>, '<?php echo htmlspecialchars($attendant['name']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
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

    <!-- Add Attendant Modal -->
    <div class="modal fade" id="addAttendantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Attendant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Attendant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Attendant Modal -->
    <div class="modal fade" id="editAttendantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Attendant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required
                                maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="edit_bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="edit_bio" name="bio" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Attendant Modal -->
    <div class="modal fade" id="deleteAttendantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Attendant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the attendant <strong id="delete_name"></strong>?</p>
                        <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Attendant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editAttendant(attendant) {
            document.getElementById('edit_id').value = attendant.id;
            document.getElementById('edit_name').value = attendant.name;
            document.getElementById('edit_bio').value = attendant.bio || '';

            new bootstrap.Modal(document.getElementById('editAttendantModal')).show();
        }

        function deleteAttendant(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = name;

            new bootstrap.Modal(document.getElementById('deleteAttendantModal')).show();
        }
    </script>
</body>

</html>
