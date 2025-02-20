<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reset_code = $conn->real_escape_string($_POST['reset_code']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_new_password'];
    
    // Debug logging
    error_log("Reset attempt with code: " . $reset_code);
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header('Location: ../index.php');
        exit();
    }
    
    // First, let's check if the code exists and is valid
    $check_stmt = $conn->prepare("SELECT reset_code, reset_code_expiry FROM users WHERE reset_code = ?");
    $check_stmt->bind_param("s", $reset_code);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        error_log("No reset code found matching: " . $reset_code);
        $_SESSION['error'] = "Invalid reset code.";
        header('Location: ../index.php');
        exit();
    }
    
    $row = $check_result->fetch_assoc();
    $current_time = date('Y-m-d H:i:s');
    error_log("Found code: " . $row['reset_code'] . ", Expires: " . $row['reset_code_expiry'] . ", Current time: " . $current_time);
    
    // Check if code has expired
    if (strtotime($row['reset_code_expiry']) < strtotime($current_time)) {
        error_log("Reset code expired. Expiry: " . $row['reset_code_expiry'] . ", Current: " . $current_time);
        $_SESSION['error'] = "Reset code has expired. Please request a new one.";
        header('Location: ../index.php');
        exit();
    }
    
    // Get user ID for valid and non-expired code
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_code = ? AND reset_code_expiry > ?");
    $stmt->bind_param("ss", $reset_code, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Reset code expired or invalid. Code: " . $reset_code);
        $_SESSION['error'] = "Reset code has expired. Please request a new one.";
        header('Location: ../index.php');
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_code_expiry = NULL WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_password, $user['id']);
    
    if ($update_stmt->execute()) {
        error_log("Password successfully reset for user ID: " . $user['id']);
        $_SESSION['success'] = "Password has been reset successfully. Please login with your new password.";
    } else {
        error_log("Password update failed: " . $update_stmt->error);
        $_SESSION['error'] = "Error resetting password. Please try again.";
    }
    
    header('Location: ../index.php');
    exit();
}
?>