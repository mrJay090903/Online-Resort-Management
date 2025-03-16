<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has appropriate access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'staff'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $conn->begin_transaction();

    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    // Update user table
    $update_user = "UPDATE users SET email = ? WHERE id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();

    // Update profile based on user type
    if ($_SESSION['user_type'] === 'staff') {
        $update_profile = "UPDATE staff SET staff_name = ?, contact_number = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_profile);
        $stmt->bind_param("ssi", $full_name, $contact_number, $user_id);
        $stmt->execute();
    }

    // Update password if provided
    if (!empty($current_password) && !empty($new_password)) {
        // Verify current password
        $verify_sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($verify_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!password_verify($current_password, $result['password'])) {
            throw new Exception("Current password is incorrect");
        }

        // Update to new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_password);
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();
    }

    $conn->commit();

    // Update session data
    $_SESSION['email'] = $email;
    if ($_SESSION['user_type'] === 'staff') {
        $_SESSION['staff_name'] = $full_name;
        $_SESSION['contact_number'] = $contact_number;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (Exception $e) {
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 