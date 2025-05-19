<?php
session_start();

// Clear owner session variables
unset($_SESSION['owner_id']);
unset($_SESSION['owner_username']);

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: owner_login.php');
exit();
?> 