<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_POST['user_id'];
        $room_id = $_POST['selected_room'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $guests = $_POST['guests'];
        $booking_type = $_POST['booking_type'];
        $selected_venues = json_decode($_POST['selected_venues'] ?? '[]', true);

        // Get customer details
        $customer_query = "SELECT full_name FROM customers WHERE user_id = ?";
        $stmt = $conn->prepare($customer_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();

        // Get room details with proper join
        $room_details = null;
        if ($room_id) {
            $room_query = "
                SELECT 
                    r.room_name, 
                    r.day_price, 
                    r.night_price,
                    r.description,
                    r.capacity
                FROM rooms r 
                WHERE r.id = ?";
            $stmt = $conn->prepare($room_query);
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $room_details = $stmt->get_result()->fetch_assoc();
        }

        // Get venue details
        $venue_details = [];
        if (!empty($selected_venues)) {
            $venue_ids = implode(',', array_map('intval', $selected_venues));
            $venue_query = "SELECT * FROM venues WHERE id IN ($venue_ids)";
            $venue_result = $conn->query($venue_query);
            while ($venue = $venue_result->fetch_assoc()) {
                $venue_details[] = $venue;
            }
        }

        // Calculate total amount
        $price = 0;
        if ($room_details) {
            $price = $booking_type === 'day' ? $room_details['day_price'] : $room_details['night_price'];
        }
        $venue_total = 0;
        foreach ($venue_details as $venue) {
            $venue_total += $venue['price'];
        }

        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        $interval = $check_in_date->diff($check_out_date);
        $days = $interval->days > 0 ? $interval->days : 1;
        $total_amount = ($price + $venue_total) * $days;

        echo json_encode([
            'success' => true,
            'customer_name' => $customer['full_name'],
            'check_in' => date('F j, Y', strtotime($check_in)),
            'check_out' => date('F j, Y', strtotime($check_out)),
            'room_name' => $room_details ? $room_details['room_name'] : null,
            'room_description' => $room_details ? $room_details['description'] : null,
            'room_capacity' => $room_details ? $room_details['capacity'] : null,
            'venues' => $venue_details,
            'booking_type' => $booking_type,
            'guests' => $guests,
            'total_amount' => $total_amount,
            'days' => $days,
            'price_per_day' => $price,
            'venue_total' => $venue_total
        ]);

    } catch (Exception $e) {
        error_log("Error getting booking details: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while retrieving booking details.',
            'debug_error' => $e->getMessage()
        ]);
    }
}
?> 