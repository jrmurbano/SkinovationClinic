<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch attendants
$stmt = $conn->prepare('SELECT * FROM attendants');
$stmt->execute();
$attendants = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendants</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include 'admin_sidebar.php'; ?>
    <div class="content p-4">
        <div class="container mt-5">
            <h1>Manage Attendants</h1>
            <a href="add_attendant.php" class="btn btn-primary mb-3">Add New Attendant</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendants as $attendant): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($attendant['id']); ?></td>
                            <td><?php echo htmlspecialchars($attendant['name']); ?></td>
                            <td><?php echo htmlspecialchars($attendant['email']); ?></td>
                            <td><?php echo htmlspecialchars($attendant['phone']); ?></td>
                            <td>
                                <a href="edit_attendant.php?id=<?php echo $attendant['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_attendant.php?id=<?php echo $attendant['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this attendant?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
