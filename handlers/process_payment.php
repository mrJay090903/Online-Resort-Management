<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get the booking details with booking_number
        $booking_query = $conn->prepare("
            SELECT 
                b.id,
                b.booking_number,
                b.total_amount,
                c.user_id,
                c.full_name 
            FROM bookings b 
            JOIN customers c ON b.customer_id = c.id 
            WHERE b.id = ?
        ");
        $booking_query->bind_param("i", $_POST['booking_id']);
        $booking_query->execute();
        $booking = $booking_query->get_result()->fetch_assoc();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Create notification for customer
        $notify_customer = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, created_at) 
            VALUES (?, ?, ?, 'payment', NOW())
        ");

        $customer_title = "Payment Received";
        $customer_message = "Your payment for booking {$booking['booking_number']} has been received and is pending confirmation.";
        
        $notify_customer->bind_param("iss", 
            $booking['user_id'],
            $customer_title, 
            $customer_message
        );
        $notify_customer->execute();

        // Create notification for admin
        $notify_admin = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, created_at) 
            VALUES (1, ?, ?, 'payment', NOW())
        ");

        $admin_title = "New Payment Received";
        $admin_message = "Payment received for booking {$booking['booking_number']} from {$booking['full_name']}";
        
        $notify_admin->bind_param("ss", 
            $admin_title, 
            $admin_message
        );
        $notify_admin->execute();

        // Update payment status
        $update_payment = $conn->prepare("
            UPDATE bookings 
            SET payment_status = 'paid'
            WHERE id = ?
        ");
        $update_payment->bind_param("i", $_POST['booking_id']);
        $update_payment->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Payment processed successfully',
            'booking_reference' => $booking['booking_number']
        ]);

    } catch (Exception $e) {
        // Rollback on error
        if ($conn) {
            $conn->rollback();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>