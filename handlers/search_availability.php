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

        // Get rooms that are available
        $rooms_query = "
            SELECT DISTINCT r.*
            FROM rooms r
            WHERE r.status = 'available'
            AND r.capacity >= ?
            AND NOT EXISTS (
                SELECT 1
                FROM booking_rooms br
                JOIN bookings b ON br.booking_id = b.id
                WHERE br.room_id = r.id
                AND b.status IN ('pending', 'confirmed', 'reschedule')  -- Exclude completed bookings
                AND (
                    (b.check_in_date <= ? AND b.check_out_date >= ?)   -- Booking overlaps with check-in
                    OR (b.check_in_date <= ? AND b.check_out_date >= ?) -- Booking overlaps with check-out
                    OR (b.check_in_date >= ? AND b.check_out_date <= ?) -- Booking is within the dates
                )
            )
            ORDER BY r.room_name ASC";

        $stmt = $conn->prepare($rooms_query);
        $stmt->bind_param("issssss", 
            $guests,
            $check_out, $check_in,
            $check_out, $check_out,
            $check_in, $check_out
        );
        $stmt->execute();
        $rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get venues that are available
        $venues_query = "
            SELECT DISTINCT v.*
            FROM venues v
            WHERE v.status = 'available'
            AND v.capacity >= ?
            AND NOT EXISTS (
                SELECT 1
                FROM booking_venues bv
                JOIN bookings b ON bv.booking_id = b.id
                WHERE bv.venue_id = v.id
                AND b.status IN ('pending', 'confirmed', 'reschedule')  -- Exclude completed bookings
                AND (
                    (b.check_in_date <= ? AND b.check_out_date >= ?)   -- Booking overlaps with check-in
                    OR (b.check_in_date <= ? AND b.check_out_date >= ?) -- Booking overlaps with check-out
                    OR (b.check_in_date >= ? AND b.check_out_date <= ?) -- Booking is within the dates
                )
            )
            ORDER BY v.name ASC";

        $stmt = $conn->prepare($venues_query);
        $stmt->bind_param("issssss", 
            $guests,
            $check_out, $check_in,
            $check_out, $check_out,
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
                'rooms' => array_values($rooms),
                'venues' => $venues,
                'booking_type' => $booking_type
            ]);
        }

    } catch (Exception $e) {
        error_log("Search Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while searching for availability: ' . $e->getMessage()
        ]);
    }
}
?>