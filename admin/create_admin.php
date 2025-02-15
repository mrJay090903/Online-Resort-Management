<?php
require_once '../config/database.php';

// Admin credentials
$admin_email = 'admin@casitadegrands.com';
$admin_password = 'admin123';

try {
    // Check if admin exists
    $sql = "SELECT id FROM users WHERE email = ? AND user_type = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Create admin user
        $sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $admin_email, $admin_password);
        
        if ($stmt->execute()) {
            echo "Admin account created successfully!<br>";
            echo "Email: " . $admin_email . "<br>";
            echo "Password: " . $admin_password;
        }
    } else {
        echo "Admin account already exists!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 