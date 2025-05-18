<?php
session_start();
include 'db.php';

// Check if user is already logged in
if (isset($_SESSION['patient_id'])) {
    // Redirect patients to their home page
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header('Location: dashboard.php');
    } else {
        header('Location: patient/my-appointments.php');
    }
    exit();
}

$error = '';

// Cleaned up duplicate redirection logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
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

                // Redirect based on booking flow
                if (isset($_GET['redirect']) && $_GET['redirect'] === 'booking') {
                    if (isset($_GET['service_id'])) {
                        header('Location: patient/calendar_view.php?service_id=' . urlencode($_GET['service_id']));
                        exit();
                    } elseif (isset($_GET['product_id'])) {
                        header('Location: patient/calendar_view.php?product_id=' . urlencode($_GET['product_id']));
                        exit();
                    } elseif (isset($_GET['package_id'])) {
                        header('Location: patient/calendar_view.php?package_id=' . urlencode($_GET['package_id']));
                        exit();
                    } elseif (isset($_GET['type']) && isset($_GET['id'])) {
                        header('Location: patient/booking_redirect.php?type=' . urlencode($_GET['type']) . '&id=' . urlencode($_GET['id']));
                        exit();
                    }
                } else {
                    header('Location: patient/my-appointments.php');
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
// Enhanced booking redirect logic for all types (GET only, not after POST)
if (!empty($_GET['redirect']) && $_GET['redirect'] === 'booking' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Service
    if (!empty($_GET['service_id'])) {
        header('Location: patient/calendar_view.php?service_id=' . urlencode($_GET['service_id']));
        exit();
    }
    // Product
    if (!empty($_GET['product_id'])) {
        header('Location: patient/calendar_view.php?product_id=' . urlencode($_GET['product_id']));
        exit();
    }
    // Package
    if (!empty($_GET['package_id'])) {
        header('Location: patient/calendar_view.php?package_id=' . urlencode($_GET['package_id']));
        exit();
    }
    // Fallback: if type/id pattern is used
    if (!empty($_GET['type']) && !empty($_GET['id'])) {
        header('Location: patient/booking_redirect.php?type=' . urlencode($_GET['type']) . '&id=' . urlencode($_GET['id']));
        exit();
    }
}

// Debugging log to trace redirection flow
error_log("Login: Redirect parameter: " . print_r($_GET, true));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
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
                        <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username"
                                        maxlength="50" placeholder="Enter your username" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                        maxlength="255" placeholder="Enter your password" required>
                                    <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                                        <i id="toggleIcon" class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>



                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>

                            <div class="mt-3 text-center">
                                <p>New to the clinic? <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . $_GET['redirect'] : ''; ?>">
                                    <i class="fas fa-user-plus me-1"></i>Register here!</a></p>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Fixed icon class mismatch in togglePassword function
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fas", "fa-eye");
                toggleIcon.classList.add("fas", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fas", "fa-eye-slash");
                toggleIcon.classList.add("fas", "fa-eye");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
