<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_type'] === 'admin') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $booking_id = $data['booking_id'];
        $status = $data['status'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update booking status
            $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $status, $booking_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update booking status");
            }

            // Get customer ID and details for notification
            $booking_query = "SELECT b.*, c.user_id, c.full_name 
                            FROM bookings b 
                            JOIN customers c ON b.customer_id = c.id 
                            WHERE b.id = ?";
            $booking_stmt = $conn->prepare($booking_query);
            $booking_stmt->bind_param("i", $booking_id);
            $booking_stmt->execute();
            $booking = $booking_stmt->get_result()->fetch_assoc();

            // Create notification for customer
            $notif_query = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
            $notif_stmt = $conn->prepare($notif_query);

            switch($status) {
                case 'confirmed':
                    $title = "Booking Confirmed";
                    $message = "Your booking #" . str_pad($booking_id, 8, '0', STR_PAD_LEFT) . " has been confirmed.";
                    $type = 'booking_confirmed';
                    break;
                case 'cancelled':
                    $title = "Booking Rejected";
                    $message = "Your booking #" . str_pad($booking_id, 8, '0', STR_PAD_LEFT) . " has been rejected.";
                    $type = 'booking_rejected';
                    break;
                case 'completed':
                    $title = "Booking Completed";
                    $message = "Your booking #" . str_pad($booking_id, 8, '0', STR_PAD_LEFT) . " has been marked as completed.";
                    $type = 'booking_completed';
                    break;
            }

            $notif_stmt->bind_param("isss", $booking['user_id'], $title, $message, $type);
            $notif_stmt->execute();

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