<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $message = $_POST['message'];
    
    // Get customer_id from the customers table using the user_idInvalid or expired reset code.
    $stmt = $conn->prepare("SELECT id FROM customers WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    
    if ($customer) {
        $stmt = $conn->prepare("INSERT INTO feedbacks (customer_id, rating, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $customer['id'], $rating, $message);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = true;
            $_SESSION['message'] = "Thank you for your feedback!";
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Error submitting feedback. Please try again.";
        }
    }
    
    // Send JSON response instead of redirecting
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $_SESSION['success'],
        'message' => $_SESSION['message']
    ]);
    exit();
} 