<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Get user from database with role-specific details
    $sql = "SELECT u.*, 
            CASE 
                WHEN u.user_type = 'staff' THEN s.staff_name 
                WHEN u.user_type = 'customer' THEN c.full_name
                ELSE NULL 
            END as name,
            CASE 
                WHEN u.user_type = 'staff' THEN s.contact_number
                WHEN u.user_type = 'customer' THEN c.contact_number
                ELSE NULL 
            END as contact_number
            FROM users u 
            LEFT JOIN staff s ON u.id = s.user_id 
            LEFT JOIN customers c ON u.id = c.user_id 
            WHERE u.email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['contact_number'] = $user['contact_number'];

        // Redirect based on user type
        switch ($user['user_type']) {
            case 'admin':
                header('Location: ../admin/dashboard.php');
                break;
            case 'staff':
                header('Location: ../admin/dashboard.php');
                break;
            case 'customer':
                header('Location: ../customer/customer_dashboard.php');
                break;
            default:
                $_SESSION['error'] = 'Invalid user type';
                header('Location: ../index.php');
        }
        exit();
    } else {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: ../index.php');
        exit();
    }
} 