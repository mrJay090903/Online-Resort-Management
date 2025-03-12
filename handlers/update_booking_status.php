<?php
// Prevent any output before our JSON response
ob_start();

session_start();
require_once '../config/database.php';

// Ensure only JSON is output
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id']) || !isset($data['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit();
}

$booking_id = $data['booking_id'];
$status = $data['status'];

try {
    // Begin transaction
    $conn->begin_transaction();

    // Get booking and user details
    $stmt = $conn->prepare("
        SELECT b.id, b.booking_number, c.user_id, c.full_name
        FROM bookings b 
        JOIN customers c ON b.customer_id = c.id 
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking_result = $stmt->get_result();
    $booking_data = $booking_result->fetch_assoc();
    
    if (!$booking_data) {
        throw new Exception("Booking not found");
    }

    // Update booking status
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        // Create notification for customer
        $notification_title = "";
        $notification_message = "";
        $notification_type = "";
        
        if ($status === 'rejected') {
            $notification_title = "Booking Rejected";
            $notification_message = "Your booking #{$booking_data['booking_number']} has been rejected.";
            $notification_type = "booking_rejected";
            
            // Free up the rooms and venues
            $stmt = $conn->prepare("DELETE FROM booking_rooms WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("DELETE FROM booking_venues WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
        } elseif ($status === 'confirmed') {
            $notification_title = "Booking Confirmed";
            $notification_message = "Your booking #{$booking_data['booking_number']} has been confirmed. We're excited to welcome you!";
            $notification_type = "booking_status";
        } elseif ($status === 'completed') {
            $notification_title = "Booking Completed";
            $notification_message = "Your booking #{$booking_data['booking_number']} has been marked as completed. Thank you for choosing our service!";
            $notification_type = "booking_completed";
        }

        // Insert notification using your existing table structure
        if ($notification_message && $notification_type) {
            $stmt = $conn->prepare("
                INSERT INTO notifications 
                (user_id, title, message, type, is_read, created_at) 
                VALUES (?, ?, ?, ?, 0, NOW())
            ");
            $stmt->bind_param("isss", 
                $booking_data['user_id'], 
                $notification_title,
                $notification_message,
                $notification_type
            );
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking status updated successfully'
        ]);
    } else {
        throw new Exception("Failed to update booking status");
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>