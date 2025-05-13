<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle settings update
$update_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clinic_name = $_POST['clinic_name'];
    $contact_phone = $_POST['contact_phone'];

    $stmt = $conn->prepare("UPDATE settings SET clinic_name = ?, contact_phone = ? WHERE id = 1");
    $stmt->bind_param('ss', $clinic_name, $contact_phone);

    if ($stmt->execute()) {
        $update_success = true;
    }
}

// Fetch current settings
$stmt = $conn->prepare("SELECT clinic_name, contact_phone FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Settings</h1>
        <?php if ($update_success): ?>
        <div class="alert alert-success">Settings updated successfully.</div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="clinic_name" class="form-label">Clinic Name</label>
                <input type="text" class="form-control" id="clinic_name" name="clinic_name" value="<?php echo htmlspecialchars($settings['clinic_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="contact_phone" class="form-label">Contact Phone</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Settings</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
