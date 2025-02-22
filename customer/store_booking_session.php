<?php
session_start();
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['booking_data'])) {
        throw new Exception('Missing booking data');
    }

    $booking_data = $data['booking_data'];

    // Validate dates
    if (empty($booking_data['check_in']) || empty($booking_data['check_out'])) {
        throw new Exception('Missing check-in or check-out date');
    }

    // Validate date format
    $check_in = DateTime::createFromFormat('Y-m-d', $booking_data['check_in']);
    $check_out = DateTime::createFromFormat('Y-m-d', $booking_data['check_out']);

    if (!$check_in || !$check_out) {
        throw new Exception('Invalid date format');
    }

    // Store formatted dates
    $booking_data['check_in'] = $check_in->format('Y-m-d');
    $booking_data['check_out'] = $check_out->format('Y-m-d');

    // Store in session
    $_SESSION['pending_booking'] = $booking_data;
    $_SESSION['payment_source_id'] = $data['source_id'];

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Store Booking Session Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 