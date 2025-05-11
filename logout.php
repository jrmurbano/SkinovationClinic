<?php
// Start the session
session_start();
include 'db.php';

// Log user logout if activity_id exists
if (isset($_SESSION['user_id']) && isset($_SESSION['activity_id'])) {
    $user_id = $_SESSION['user_id'];
    $activity_id = $_SESSION['activity_id'];

    // Update the activity record with logout time and session duration
    $stmt = $conn->prepare("
        UPDATE user_activity
        SET logout_time = NOW(),
            session_duration = TIME_TO_SEC(TIMEDIFF(NOW(), login_time))
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param('ii', $activity_id, $user_id);
    $stmt->execute();

    // Insert a new logout activity
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, ip_address, user_agent, login_time) VALUES (?, 'logout', ?, ?, NOW())");
    $stmt->bind_param('iss', $user_id, $ip_address, $user_agent);
    $stmt->execute();
}

// Unset all session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Finally, destroy the session
session_destroy();

// Redirect to the home page
header('Location: patient/index.php');
exit();
?>
