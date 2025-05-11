<?php
include '../db.php';

$username = 'Adminuser';
$stmt = $conn->prepare('SELECT id FROM patients WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();

    $password = password_hash('Admin123', PASSWORD_DEFAULT);
    $name = 'Admin';
    $is_admin = 1;

    $stmt = $conn->prepare('INSERT INTO patients (username, password, name, is_admin) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $username, $password, $name, $is_admin);

    if ($stmt->execute()) {
        echo 'Admin account created successfully.';
    } else {
        echo 'Error: ' . $stmt->error;
    }
} else {
    echo 'Admin account already exists.';
}

$stmt->close();
$conn->close();
?>
