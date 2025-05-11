<?php
session_start();
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if appointment_id is provided
if (!isset($_GET['appointment_id'])) {
    header('Location: my-appointments.php');
    exit();
}

$appointment_id = $_GET['appointment_id'];

// Check if the appointment belongs to the user and is completed
$stmt = $conn->prepare("
    SELECT
        a.appointment_id, a.appointment_date, a.appointment_time, a.status,
        s.name as service_name,
        att.name as attendant_name
    FROM
        appointments a
    JOIN
        services s ON a.service_id = s.service_id
    JOIN
        attendants att ON a.attendant_id = att.attendant_id
    WHERE
        a.appointment_id = ? AND a.patient_id = ? AND a.status = 'completed'
");
$stmt->bind_param('ii', $appointment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: my-appointments.php');
    exit();
}

$appointment = $result->fetch_assoc();

// Check if feedback already exists
$stmt = $conn->prepare('SELECT feedback_id FROM feedback WHERE appointment_id = ?');
$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$feedback_result = $stmt->get_result();

$feedback_exists = $feedback_result->num_rows > 0;

// Process feedback submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$feedback_exists) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // Validate inputs
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5.';
    } else {
        // Insert feedback
        $stmt = $conn->prepare('INSERT INTO feedback (appointment_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('iiis', $appointment_id, $user_id, $rating, $comment);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'An error occurred while submitting your feedback. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=deviSkinovation Beauty Clinicce-width, initial-scale=1.0">
    <title>Skinovation Beauty Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.5rem;
        }

        .rating input {
            display: none;
        }

        .rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #ccc;
            transition: color 0.2s;
        }

        .rating label:hover,
        .rating label:hover~label,
        .rating input:checked~label {
            color: #ffb700;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Leave Feedback</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                        <div class="alert alert-success text-center">
                            <h4><i class="bi bi-check-circle"></i> Thank You!</h4>
                            <p>Your feedback has been submitted successfully. We appreciate your input!</p>
                            <a href="my-appointments.php" class="btn btn-primary mt-3">Back to My Appointments</a>
                        </div>
                        <?php elseif ($feedback_exists): ?>
                        <div class="alert alert-info text-center">
                            <h4><i class="bi bi-info-circle"></i> Feedback Already Submitted</h4>
                            <p>You have already submitted feedback for this appointment.</p>
                            <a href="my-appointments.php" class="btn btn-primary mt-3">Back to My Appointments</a>
                        </div>
                        <?php else: ?>
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="appointment-details mb-4">
                            <h5>Appointment Details</h5>
                            <p><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?></p>
                            <p><strong>Dermatologist:</strong> Dr. <?php echo htmlspecialchars($appointment['dermatologist_name']); ?></p>
                            <p><strong>Date & Time:</strong>
                                <?php
                                echo date('M d, Y', strtotime($appointment['appointment_date']));
                                echo ' at ';
                                echo date('h:i A', strtotime($appointment['appointment_time']));
                                ?>
                            </p>
                        </div>

                        <form method="post" action="">
                            <div class="mb-4 text-center">
                                <label class="form-label">How would you rate your experience?</label>
                                <div class="rating">
                                    <input type="radio" id="star5" name="rating" value="5" required />
                                    <label for="star5" title="5 stars"><i class="bi bi-star-fill"></i></label>

                                    <input type="radio" id="star4" name="rating" value="4" />
                                    <label for="star4" title="4 stars"><i class="bi bi-star-fill"></i></label>

                                    <input type="radio" id="star3" name="rating" value="3" />
                                    <label for="star3" title="3 stars"><i class="bi bi-star-fill"></i></label>

                                    <input type="radio" id="star2" name="rating" value="2" />
                                    <label for="star2" title="2 stars"><i class="bi bi-star-fill"></i></label>

                                    <input type="radio" id="star1" name="rating" value="1" />
                                    <label for="star1" title="1 star"><i class="bi bi-star-fill"></i></label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Your Comments (Optional)</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4"
                                    placeholder="Share your experience with us..."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                                <a href="my-appointments.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>
