<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $reschedule_date_in = $_POST['reschedule_date_in'];
    $reschedule_date_out = $_POST['reschedule_date_out'];
    
    // Get booking number first
    $booking_query = $conn->prepare("SELECT booking_number FROM bookings WHERE id = ?");
    $booking_query->bind_param("i", $booking_id);
    $booking_query->execute();
    $booking = $booking_query->get_result()->fetch_assoc();
    
    // Update booking with reschedule request
    $stmt = $conn->prepare("
        UPDATE bookings 
        SET status = 'reschedule',
            reschedule_date_in = ?,
            reschedule_date_out = ?,
            reschedule_status = 'pending'
        WHERE id = ?
    ");
    
    $stmt->bind_param("ssi", $reschedule_date_in, $reschedule_date_out, $booking_id);
    
    if ($stmt->execute()) {
        // Create notification for admin
        $notify_admin = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type) 
            VALUES (1, 'Booking Reschedule Request', ?, 'reschedule_request')
        ");
        
        $message = "A customer has requested to reschedule booking {$booking['booking_number']} from $reschedule_date_in to $reschedule_date_out";
        $notify_admin->bind_param("s", $message);
        $notify_admin->execute();
        
        echo json_encode(['success' => true, 'message' => 'Reschedule request submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit reschedule request']);
    }
}
?>