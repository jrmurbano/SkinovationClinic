<?php
include '../config.php';

// Owner details
$last_name = 'Polvorido';
$first_name = 'Kranchy';
$username = 'kranchypolvorido';
$password = 'owner123';

// Hash the password properly using password_hash()
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if username already exists
    $stmt = $conn->prepare("SELECT owner_id FROM owner WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        echo "Username already exists!";
        exit();
    }
    
    // Insert the owner
    $stmt = $conn->prepare("
        INSERT INTO owner (last_name, first_name, username, password, created_at, updated_at)
        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");
    
    $stmt->execute([$last_name, $first_name, $username, $hashed_password]);
    
    echo "Owner account created successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 