<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    try {
        $update_query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Error marking notifications as read: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating notifications']);
    }
}
?> 