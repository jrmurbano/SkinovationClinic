<?php
// Check if user is logged in - we don't need session_start() here as it's already started in the main file
if (!isset($_SESSION['patient_id'])) {
    die('<script>window.location.href = "../login.php";</script>');
}
?>

<li class="nav-item">
    <a class="nav-link" href="my-profile.php">My Profile</a>
</li>
