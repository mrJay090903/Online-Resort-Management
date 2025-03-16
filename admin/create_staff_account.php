<?php
require_once '../config/database.php';

try {
    $conn->begin_transaction();

    // Create user account
    $email = 'staff@example.com';
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $staff_name = 'Staff Member'; // Define staff name
    
    $sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'staff')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    
    $user_id = $conn->insert_id;

    // Create staff profile
    $sql = "INSERT INTO staff (user_id, staff_name, contact_number) VALUES (?, ?, '09123456789')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $staff_name);
    $stmt->execute();

    $conn->commit();
    echo "Staff account created successfully!<br>";
    echo "Email: staff@example.com<br>";
    echo "Password: password123<br>";
    echo "Staff Name: $staff_name";

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?> 