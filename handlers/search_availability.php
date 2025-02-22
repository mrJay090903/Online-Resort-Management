<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $guests = intval($_POST['guests']);
        $booking_type = $_POST['booking_type'];

        // Debug logging
        error_log("Search Parameters - Check In: $check_in, Check Out: $check_out, Guests: $guests, Type: $booking_type");

        // First, let's check the structure of the bookings table
        $table_check = $conn->query("DESCRIBE bookings");
        $columns = [];
        while($row = $table_check->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        error_log("Bookings table columns: " . implode(", ", $columns));

        // Get rooms that are not booked for the selected dates
        $rooms_query = "
            SELECT r.*
            FROM rooms r
            WHERE r.status = 'available'
            AND r.capacity >= ?
            AND NOT EXISTS (
                SELECT 1
                FROM bookings b
                JOIN booking_rooms br ON b.id = br.booking_id
                WHERE br.room_id = r.id
                AND b.status NOT IN ('cancelled')
                AND (
                    (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                    (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                    (b.check_in_date >= ? AND b.check_out_date <= ?)
                )
            )";

        $stmt = $conn->prepare($rooms_query);
        $stmt->bind_param("issssss", 
            $guests,
            $check_out, $check_in,  // Covers end of existing booking overlapping with start of new booking
            $check_in, $check_in,   // Covers start of existing booking overlapping with new booking
            $check_in, $check_out   // Covers existing booking completely within new booking
        );
        $stmt->execute();
        $rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get venues that are not booked for the selected dates
        $venues_query = "
            SELECT v.*
            FROM venues v
            WHERE v.status = 'available'
            AND v.capacity >= ?
            AND NOT EXISTS (
                SELECT 1
                FROM bookings b
                WHERE b.venue_id = v.id
                AND b.status NOT IN ('cancelled')
                AND (
                    (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                    (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                    (b.check_in_date >= ? AND b.check_out_date <= ?)
                )
            )";

        $stmt = $conn->prepare($venues_query);
        $stmt->bind_param("issssss", 
            $guests,
            $check_out, $check_in,
            $check_in, $check_in,
            $check_in, $check_out
        );
        $stmt->execute();
        $venues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Debug logging
        error_log("Found Rooms: " . count($rooms));
        error_log("Found Venues: " . count($venues));

        if (empty($rooms) && empty($venues)) {
            echo json_encode([
                'success' => false,
                'message' => 'No available rooms or venues found for the selected criteria.'
            ]);
        } else {
            // Filter rooms based on booking type
            if ($booking_type === 'day') {
                $rooms = array_filter($rooms, function($room) {
                    return isset($room['day_price']) && $room['day_price'] > 0;
                });
            } else {
                $rooms = array_filter($rooms, function($room) {
                    return isset($room['night_price']) && $room['night_price'] > 0;
                });
            }

            echo json_encode([
                'success' => true,
                'rooms' => array_values($rooms), // Reset array keys
                'venues' => $venues,
                'debug_info' => [
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'guests' => $guests,
                    'booking_type' => $booking_type
                ]
            ]);
        }

    } catch (Exception $e) {
        error_log("Search Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while searching for availability: ' . $e->getMessage(),
            'debug_info' => [
                'check_in' => $check_in ?? null,
                'check_out' => $check_out ?? null,
                'guests' => $guests ?? null,
                'booking_type' => $booking_type ?? null
            ]
        ]);
    }
}
?> 