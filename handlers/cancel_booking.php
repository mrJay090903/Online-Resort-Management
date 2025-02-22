<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $booking_id = $data['booking_id'];

        // Get customer_id from user_id
        $customer_query = "SELECT id FROM customers WHERE user_id = ?";
        $customer_stmt = $conn->prepare($customer_query);
        $customer_stmt->bind_param("i", $_SESSION['user_id']);
        $customer_stmt->execute();
        $customer = $customer_stmt->get_result()->fetch_assoc();

        // Verify booking belongs to this customer
        $verify_query = "SELECT id FROM bookings WHERE id = ? AND customer_id = ? AND status = 'pending'";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("ii", $booking_id, $customer['id']);
        $verify_stmt->execute();

        if ($verify_stmt->get_result()->num_rows === 0) {
            throw new Exception("Invalid booking or not authorized to cancel");
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update booking status
            $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $booking_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to cancel booking");
            }

            // Create notification for admin
            $admin_query = "SELECT id FROM users WHERE user_type = 'admin' LIMIT 1";
            $admin_result = $conn->query($admin_query);
            $admin = $admin_result->fetch_assoc();

            if ($admin) {
                $notif_query = "INSERT INTO notifications (user_id, title, message, type) 
                               VALUES (?, 'Booking Cancelled', ?, 'booking_cancelled')";
                $notif_stmt = $conn->prepare($notif_query);
                $message = "Booking #" . str_pad($booking_id, 8, '0', STR_PAD_LEFT) . " has been cancelled by the customer.";
                $notif_stmt->bind_param("is", $admin['id'], $message);
                $notif_stmt->execute();
            }

            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?> 