<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch attendants
$attendants = [];
$stmt = $conn->prepare("SELECT * FROM attendants");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $attendants[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendants - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Manage Attendants</h1>
        <a href="add_attendant.php" class="btn btn-primary mb-3">
            <i class="fas fa-user-plus"></i> Add New Attendant
        </a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendants as $attendant): ?>
                <tr>
                    <td><?php echo $attendant['id']; ?></td>
                    <td><?php echo htmlspecialchars($attendant['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($attendant['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($attendant['contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($attendant['email']); ?></td>
                    <td>
                        <a href="edit_attendant.php?id=<?php echo $attendant['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_attendant.php?id=<?php echo $attendant['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this attendant?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
