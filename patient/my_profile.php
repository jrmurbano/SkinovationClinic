<?php
session_start();
include '../db.php';

// Prevent browser caching to ensure strict authentication
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login.php');
    exit();
}

$patient_id = $_SESSION['patient_id'];

// Fetch user profile data
$stmt = $conn->prepare("SELECT first_name, last_name, middle_name, username, phone FROM patients WHERE patient_id = ?");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
$update_success = false;
$update_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE patients SET first_name = ?, last_name = ?, middle_name = ?, username = ?, phone = ? WHERE patient_id = ?");
    $stmt->bind_param('sssssi', $first_name, $last_name, $middle_name, $username, $phone, $patient_id);

    if ($stmt->execute()) {
        $update_success = true;
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['middle_name'] = $middle_name;
        $user['username'] = $username;
        $user['phone'] = $phone;
    } else {
        $update_error = 'An error occurred while updating your profile. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        .container {
            display: flex;
            flex-wrap: wrap;
        }
        .sidebar {
            flex: 0 0 250px;
            background-color: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #dee2e6;
        }
        .content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }
        @media (max-width: 768px) {
            .sidebar {
                flex: 1 0 100%;
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
            .content {
                flex: 1 0 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container d-flex">
        <div class="sidebar">
            <img src="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png" alt="Skinovation Logo" class="img-fluid mb-3">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="content flex-grow-1">
            <h1 class="mb-4">My Profile</h1>

            <?php if ($update_success): ?>
            <div class="alert alert-success">Profile updated successfully.</div>
            <?php elseif ($update_error): ?>
            <div class="alert alert-danger"><?php echo $update_error; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="first_name" class="form-label">
                        <i class="fas fa-user"></i> First Name
                    </label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">
                        <i class="fas fa-user"></i> Last Name
                    </label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="middle_name" class="form-label">
                        <i class="fas fa-user"></i> Middle Name
                    </label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user-circle"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i> Phone
                    </label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>