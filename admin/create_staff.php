<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_name = $conn->real_escape_string($_POST['staff_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact_number = $conn->real_escape_string($_POST['contact_number']);
    $password = $_POST['password'];

    $conn->begin_transaction();

    try {
        // Insert into users table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'staff')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        
        $user_id = $conn->insert_id;

        // Insert into staff table
        $sql = "INSERT INTO staff (user_id, staff_name, contact_number) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $staff_name, $contact_number);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Staff account created successfully";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Failed to create staff account: " . $e->getMessage();
    }
} 