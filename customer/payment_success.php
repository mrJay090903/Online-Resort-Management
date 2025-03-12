<?php
session_start();
require_once '../config/database.php';

// Debug logging
error_log("\n\n=== PAYMENT SUCCESS START ===");
error_log("Current Time: " . date('Y-m-d H:i:s'));
error_log("Session Data: " . print_r($_SESSION, true));

// Check if we have the necessary session data
if (!isset($_SESSION['payment_source_id']) || !isset($_SESSION['pending_booking'])) {
    error_log("ERROR: Missing session data");
    $_SESSION['payment_error'] = [
        'title' => 'Payment Session Error',
        'message' => 'Your payment session has expired or is invalid. Please try booking again.'
    ];
    header('Location: payment_error.php');
    exit();
}

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $source_id = $_SESSION['payment_source_id'];
    $booking_data = $_SESSION['pending_booking'];
    $down_payment = $booking_data['total_amount'] * 0.5;

    error_log("Processing payment for source_id: " . $source_id);
    error_log("Booking data: " . print_r($booking_data, true));

    // Verify payment with PayMongo
    $ch = curl_init('https://api.paymongo.com/v1/sources/' . $source_id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode('sk_test_PLPKHXfcCfZFc5xNHpSDZi9b:')
        ]
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new Exception("Payment verification failed: " . $err);
    }

    $payment_data = json_decode($response, true);
    error_log("PayMongo Response: " . print_r($payment_data, true));

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get customer_id from user_id
        $customer_query = "SELECT id FROM customers WHERE user_id = ?";
        $customer_stmt = $conn->prepare($customer_query);
        $customer_stmt->bind_param("i", $booking_data['user_id']);
        $customer_stmt->execute();
        $customer_result = $customer_stmt->get_result();
        $customer = $customer_result->fetch_assoc();
        
        if (!$customer) {
            throw new Exception("Customer not found");
        }

        // Format dates properly
        try {
            if (empty($booking_data['check_in']) || empty($booking_data['check_out'])) {
                error_log("Missing dates in booking data: " . print_r($booking_data, true));
                throw new Exception("Missing check-in or check-out date in booking data");
            }

            // Format dates using DateTime
            $check_in = DateTime::createFromFormat('Y-m-d', $booking_data['check_in']);
            $check_out = DateTime::createFromFormat('Y-m-d', $booking_data['check_out']);
            
            if (!$check_in || !$check_out) {
                error_log("Invalid date format in booking data: " . print_r($booking_data, true));
                throw new Exception("Invalid date format in booking data");
            }
            
            $formatted_check_in = $check_in->format('Y-m-d');
            $formatted_check_out = $check_out->format('Y-m-d');

            error_log("Formatted dates for insert: Check-in: $formatted_check_in, Check-out: $formatted_check_out");

            if (empty($formatted_check_out) || $formatted_check_out === '0000-00-00') {
                throw new Exception("Invalid check-out date detected after formatting");
            }
        } catch (Exception $e) {
            error_log("Date formatting error: " . $e->getMessage());
            throw new Exception("Failed to process booking dates: " . $e->getMessage());
        }

        // Insert main booking record
        $booking_query = "INSERT INTO bookings (
            customer_id,
            booking_number,
            check_in_date,
            check_out_date,
            total_guests,
            total_amount,
            down_payment,
            payment_source_id,
            payment_status,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'pending')";

        $stmt = $conn->prepare($booking_query);
        if (!$stmt) {
            throw new Exception("Failed to prepare booking statement: " . $conn->error);
        }

        $stmt->bind_param(
            "isssidss",
            $customer['id'],
            $booking_data['booking_number'],
            $formatted_check_in,
            $formatted_check_out,
            $booking_data['guests'],
            $booking_data['total_amount'],
            $down_payment,
            $source_id
        );

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Failed to save booking: " . $stmt->error);
        }

        $booking_id = $conn->insert_id;
        if (!$booking_id) {
            throw new Exception("Failed to get booking ID after insert");
        }

        // Verify the booking was inserted
        $verify_query = "SELECT * FROM bookings WHERE id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("i", $booking_id);
        $verify_stmt->execute();
        $booking_result = $verify_stmt->get_result()->fetch_assoc();

        if (!$booking_result) {
            throw new Exception("Failed to verify booking insertion");
        }

        error_log("Booking inserted successfully with ID: " . $booking_id);

        // Insert booking rooms if a room was selected
        if (!empty($booking_data['room_id'])) {
            $room_query = "INSERT INTO booking_rooms (booking_id, room_id, time_slot, quantity, price_per_night) 
                          VALUES (?, ?, ?, 1, ?)";

            $room_stmt = $conn->prepare($room_query);
            if (!$room_stmt) {
                throw new Exception("Failed to prepare room booking statement");
            }

            $room_stmt->bind_param("iisd", 
                $booking_id,
                $booking_data['room_id'],
                $booking_data['booking_type'],
                $booking_data['room_price']
            );

            if (!$room_stmt->execute()) {
                throw new Exception("Failed to save room booking: " . $room_stmt->error);
            }

            error_log("Room booking inserted successfully");
        }

        // Insert booking venues if any were selected
        if (!empty($booking_data['venues'])) {
            $venue_query = "INSERT INTO booking_venues (booking_id, venue_id) VALUES (?, ?)";
            $venue_stmt = $conn->prepare($venue_query);
            if (!$venue_stmt) {
                throw new Exception("Failed to prepare venue booking statement");
            }

            foreach ($booking_data['venues'] as $venue) {
                $venue_stmt->bind_param("ii", $booking_id, $venue['id']);
                if (!$venue_stmt->execute()) {
                    throw new Exception("Failed to save venue booking: " . $venue_stmt->error);
                }
            }

            error_log("Venue bookings inserted successfully");
        }

        // Create notification for admin about new pending booking
        $admin_query = "SELECT id FROM users WHERE user_type = 'admin' LIMIT 1";
        $admin_result = $conn->query($admin_query);
        $admin = $admin_result->fetch_assoc();

        if ($admin) {
            $notif_query = "INSERT INTO notifications (
                user_id, 
                title, 
                message, 
                type, 
                is_read
            ) VALUES (?, ?, ?, 'new_booking', 0)";

            $notif_stmt = $conn->prepare($notif_query);
            $notif_title = "New Booking Requires Confirmation";
            $notif_message = sprintf(
                "New booking %s from %s requires your confirmation. Check-in: %s, Check-out: %s",
                $booking_data['booking_number'],
                $booking_data['customer_name'],
                date('M d, Y', strtotime($booking_data['check_in'])),
                date('M d, Y', strtotime($booking_data['check_out']))
            );

            $notif_stmt->bind_param("iss", $admin['id'], $notif_title, $notif_message);
            $notif_stmt->execute();
        }

        // Create notification for customer
        $customer_notif_query = "INSERT INTO notifications (
            user_id, 
            title, 
            message, 
            type, 
            is_read
        ) VALUES (?, ?, ?, 'booking_pending', 0)";

        $customer_notif_stmt = $conn->prepare($customer_notif_query);
        $customer_title = "Booking Payment Received";
        $customer_message = sprintf(
            "Your payment for booking %s has been received and is pending confirmation. We'll notify you once it's confirmed.",
            $booking_data['booking_number']
        );

        $customer_notif_stmt->bind_param(
            "iss", 
            $booking_data['user_id'], 
            $customer_title, 
            $customer_message
        );
        $customer_notif_stmt->execute();

        // If everything is successful, commit the transaction
        $conn->commit();
        error_log("Transaction committed successfully");

        // Clear session data and redirect
        unset($_SESSION['payment_source_id']);
        unset($_SESSION['pending_booking']);
        
        $_SESSION['payment_success'] = [
            'title' => 'Payment Successful!',
            'message' => 'Your payment has been received. Your booking is pending confirmation from our staff.',
            'amount' => $down_payment,
            'booking_id' => $booking_id
        ];
        
        header('Location: payment_confirmation.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Payment Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $_SESSION['payment_error'] = [
            'title' => 'Payment Processing Error',
            'message' => $e->getMessage()
        ];
        header('Location: payment_error.php');
        exit();
    }

} catch (Exception $e) {
    error_log("PAYMENT ERROR: " . $e->getMessage());
    error_log("=== PAYMENT SUCCESS END WITH ERROR ===\n");
    
    $_SESSION['payment_error'] = [
        'title' => 'Payment Processing Error',
        'message' => $e->getMessage()
    ];
    header('Location: payment_error.php');
    exit();
}
?>