<?php
session_start();
include 'db.php';

// Check if user is already logged in
if (isset($_SESSION['patient_id'])) {
    // Redirect patients to their home page
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header('Location: dashboard.php');
    } else {
        header('Location: patient/home.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['email'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare('SELECT patient_id, first_name, middle_name, last_name, username, password FROM patients WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['patient_id'] = $user['patient_id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'];
                $_SESSION['user_username'] = $user['username'];

                // Check if there's a pending booking
                if (isset($_GET['redirect']) && $_GET['redirect'] == 'booking' && isset($_SESSION['pending_booking'])) {
                    header('Location: booking.php?service_id=' . $_SESSION['pending_booking']['service_id']);
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Username not found.';
        }
    }
}
?>

<?php
if (isset($_GET['redirect']) && $_GET['redirect'] === 'booking') {
    echo "<script>alert('Please log-in or register first to book');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .services-header h1 {
            text-align: center;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
        }
    </style>


</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Login</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="email" name="email"
                                        maxlength="50" placeholder="Enter your username" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                        maxlength="255" placeholder="Enter your password" required>
                                    <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                                        <i id="toggleIcon" class="bi bi-eye"></i>
                                    </span>
                                </div>
                            </div>



                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                            </div>

                            <div class="mt-3 text-center">
                                <p>New to the clinic? <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . $_GET['redirect'] : ''; ?>">Register here!</a></p>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("bi-eye");
                toggleIcon.classList.add("bi-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("bi-eye-slash");
                toggleIcon.classList.add("bi-eye");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
