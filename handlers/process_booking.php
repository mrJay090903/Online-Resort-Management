// After successful booking insertion, add this code:

// Get the inserted booking ID
$booking_id = $conn->insert_id; // Get the ID of the last inserted booking

// Create notification for admin
$notification_query = $conn->prepare("
    INSERT INTO notifications (user_id, title, message, type, created_at, is_read) 
    VALUES (?, ?, ?, 'new_booking', NOW(), 0)
");

// Get customer name and booking details
$booking_query = $conn->prepare("
    SELECT b.*, c.full_name 
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id 
    WHERE b.id = ?
");
$booking_query->bind_param("i", $booking_id);
$booking_query->execute();
$booking = $booking_query->get_result()->fetch_assoc();

// Format booking details for notification
$booking_details = sprintf(
    "New booking received from %s\n\n" .
    "Booking Details:\n" .
    "• Booking Number: %s\n" .
    "• Check-in: %s\n" .
    "• Check-out: %s\n" .
    "• Total Amount: ₱%s\n" .
    "• Down Payment: ₱%s\n\n" .
    "Please review and confirm this booking.",
    $booking['full_name'],
    $booking['booking_number'],
    date('M d, Y', strtotime($booking['check_in_date'])),
    date('M d, Y', strtotime($booking['check_out_date'])),
    number_format($booking['total_amount'], 2),
    number_format($booking['down_payment'], 2)
);

// Send notification to admin (assuming admin user_id is 1)
$admin_user_id = 1;
$notification_title = "New Booking Request 🏨";
$notification_query->bind_param("iss", $admin_user_id, $notification_title, $booking_details);
$notification_query->execute(); 