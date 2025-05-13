<?php
session_start();
include 'db.php';

// Check if user is already logged in
if (isset($_SESSION['patient_id'])) {
    // Redirect to appropriate page
    if (isset($_GET['redirect']) && $_GET['redirect'] == 'booking' && isset($_SESSION['pending_booking'])) {
        header('Location: booking.php?service_id=' . $_SESSION['pending_booking']['service_id']);
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';
$success = false;

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Validate inputs
    if (empty($first_name) || empty($middle_name) || empty($last_name) || empty($username) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($first_name) > 100 || strlen($middle_name) > 100 || strlen($last_name) > 100) {
        $error = 'Name fields must not exceed 100 characters each.';
    } elseif (strlen($username) > 50) {
        $error = 'Username must not exceed 50 characters.';
    } elseif (strlen($phone) !== 11 || !ctype_digit($phone)) {
        $error = 'Phone number must be exactly 11 digits.';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $stmt = $conn->prepare('SELECT patient_id FROM patients WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username already exists. Please use a different username or login.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare('INSERT INTO patients (first_name, middle_name, last_name, username, phone, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->bind_param('ssssss', $first_name, $middle_name, $last_name, $username, $phone, $hashed_password);

            if ($stmt->execute()) {
                $patient_id = $conn->insert_id;

                // Set session variables
                $_SESSION['patient_id'] = $patient_id;
                $_SESSION['user_name'] = $first_name . ' ' . $middle_name . ' ' . $last_name;
                $_SESSION['user_username'] = $username;

                // If there's a pending booking, process it
                if (isset($_SESSION['pending_booking'])) {
                    $pending_booking = $_SESSION['pending_booking'];

                    // Check if the selected time slot is still available
                    $stmt = $conn->prepare("SELECT * FROM appointments WHERE dermatologist_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
                    $stmt->bind_param('iss', $pending_booking['dermatologist_id'], $pending_booking['appointment_date'], $pending_booking['appointment_time']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows == 0) {
                        // Insert the booking
                        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, service_id, dermatologist_id, appointment_date, appointment_time, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
                        $stmt->bind_param('iiisss', $patient_id, $pending_booking['service_id'], $pending_booking['dermatologist_id'], $pending_booking['appointment_date'], $pending_booking['appointment_time'], $pending_booking['notes']);

                        if ($stmt->execute()) {
                            // Clear pending booking from session
                            unset($_SESSION['pending_booking']);
                            // Redirect to my-appointments page
                            header('Location: my-appointments.php');
                            exit();
                        }
                    } else {
                        // Time slot is no longer available
                        unset($_SESSION['pending_booking']);
                        $error = 'The selected time slot is no longer available. Please book a new appointment.';
                    }
                }

                // If no pending booking or booking failed, redirect to index
                header('Location: index.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
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
        <div class="row">
            <div class="col-md-6 mx-auto">
                <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    <h4><i class="bi bi-check-circle"></i> Registration Successful!</h4>
                    <p>Your account has been created successfully. You are now logged in.</p>
                    <p>Redirecting you in a few seconds...</p>
                </div>
                <?php else: ?>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Create an Account</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                        maxlength="100" placeholder="Enter your first name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="middle_name" name="middle_name"
                                        maxlength="100" placeholder="Enter your middle name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                        maxlength="100" placeholder="Enter your last name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" class="form-control" id="username" name="username"
                                        maxlength="50" placeholder="Enter username" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        pattern="\d{11}" maxlength="11" placeholder="Enter your phone number" required
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        title="Please enter exactly 11 digits">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                        maxlength="255" minlength="6" placeholder="Enter your password" required>
                                    <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                                        <i id="toggleIcon" class="bi bi-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" maxlength="255" placeholder="Enter your password again" minlength="6" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>

                        <div class="mt-3 text-center">
                            <p>Already have an account? <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . $_GET['redirect'] : ''; ?>">Login here</a></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const firstNameInput = document.getElementById('first_name');
            const middleNameInput = document.getElementById('middle_name');
            const lastNameInput = document.getElementById('last_name');
            const usernameInput = document.getElementById('username');
            const phoneInput = document.getElementById('phone');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            form.addEventListener('submit', function(event) {
                let isValid = true;

                // Validate name fields
                if (firstNameInput.value.length > 100 || middleNameInput.value.length > 100 || lastNameInput.value.length > 100) {
                    isValid = false;
                    firstNameInput.classList.add('is-invalid');
                    middleNameInput.classList.add('is-invalid');
                    lastNameInput.classList.add('is-invalid');
                    showError(firstNameInput, 'Name fields must not exceed 100 characters each');
                } else {
                    firstNameInput.classList.remove('is-invalid');
                    middleNameInput.classList.remove('is-invalid');
                    lastNameInput.classList.remove('is-invalid');
                }

                // Validate username
                if (usernameInput.value.length > 50) {
                    isValid = false;
                    usernameInput.classList.add('is-invalid');
                    showError(usernameInput, 'Username must not exceed 50 characters');
                } else {
                    usernameInput.classList.remove('is-invalid');
                }

                // Validate phone
                if (phoneInput.value.length !== 11 || !/^\d+$/.test(phoneInput.value)) {
                    isValid = false;
                    phoneInput.classList.add('is-invalid');
                    showError(phoneInput, 'Phone number must be exactly 11 digits');
                } else {
                    phoneInput.classList.remove('is-invalid');
                }

                // Validate password
                if (passwordInput.value.length < 6) {
                    isValid = false;
                    passwordInput.classList.add('is-invalid');
                    showError(passwordInput, 'Password must be at least 6 characters long');
                } else {
                    passwordInput.classList.remove('is-invalid');
                }

                // Validate password confirmation
                if (passwordInput.value !== confirmPasswordInput.value) {
                    isValid = false;
                    confirmPasswordInput.classList.add('is-invalid');
                    showError(confirmPasswordInput, 'Passwords do not match');
                } else {
                    confirmPasswordInput.classList.remove('is-invalid');
                }

                if (!isValid) {
                    event.preventDefault();
                }
            });

            // Function to show error message
            function showError(input, message) {
                // Find the form-text element after the input or create a new one
                let formText = input.parentElement.querySelector('.invalid-feedback');
                if (!formText) {
                    formText = document.createElement('div');
                    formText.className = 'invalid-feedback';
                    input.parentElement.appendChild(formText);
                }
                formText.textContent = message;
            }
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                confirmPasswordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                confirmPasswordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
