<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $selected_room = $_POST['selected_room'] ?? null;
        $selected_venues = json_decode($_POST['selected_venues'] ?? '[]', true);
        $booking_type = $_POST['booking_type'];
        
        // Debug logging
        error_log("Raw POST data: " . print_r($_POST, true));
        
        // Validate and format dates
        if (empty($_POST['check_in']) || empty($_POST['check_out'])) {
            throw new Exception("Check-in and check-out dates are required");
        }

        // Format dates properly using DateTime
        try {
            $check_in_date = new DateTime($_POST['check_in']);
            $check_out_date = new DateTime($_POST['check_out']);
            
            // Ensure dates are valid
            if (!$check_in_date || !$check_out_date) {
                throw new Exception("Invalid date format");
            }
            
            $check_in = $check_in_date->format('Y-m-d');
            $check_out = $check_out_date->format('Y-m-d');
            
            error_log("Formatted dates in booking handler - Check in: $check_in, Check out: $check_out");

            if ($check_out_date <= $check_in_date) {
                throw new Exception("Check-out date must be after check-in date");
            }
        } catch (Exception $e) {
            error_log("Date parsing error: " . $e->getMessage());
            throw new Exception("Invalid date format provided");
        }

        // Calculate number of days
        $interval = $check_in_date->diff($check_out_date);
        $days = $interval->days > 0 ? $interval->days : 1;

        // Verify availability for room
        if ($selected_room) {
            $room_available = $conn->prepare("
                SELECT 1 FROM rooms r
                WHERE r.id = ?
                AND r.status = 'available'
                AND NOT EXISTS (
                    SELECT 1 FROM bookings b
                    JOIN booking_rooms br ON b.id = br.booking_id
                    WHERE br.room_id = r.id
                    AND b.status NOT IN ('cancelled')
                    AND (
                        (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                        (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                        (b.check_in_date >= ? AND b.check_out_date <= ?)
                    )
                )
            ");
            $room_available->bind_param("issssss", 
                $selected_room,
                $check_out, $check_in,
                $check_in, $check_in,
                $check_in, $check_out
            );
            $room_available->execute();
            if ($room_available->get_result()->num_rows === 0) {
                throw new Exception("Selected room is no longer available for these dates");
            }
        }

        // Verify availability for venues
        if (!empty($selected_venues)) {
            foreach ($selected_venues as $venue_id) {
                $venue_available = $conn->prepare("
                    SELECT 1 FROM venues v
                    WHERE v.id = ?
                    AND v.status = 'available'
                    AND NOT EXISTS (
                        SELECT 1 FROM bookings b
                        WHERE b.venue_id = v.id
                        AND b.status NOT IN ('cancelled')
                        AND (
                            (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                            (b.check_in_date <= ? AND b.check_out_date >= ?) OR
                            (b.check_in_date >= ? AND b.check_out_date <= ?)
                        )
                    )
                ");
                $venue_available->bind_param("issssss", 
                    $venue_id,
                    $check_out, $check_in,
                    $check_in, $check_in,
                    $check_in, $check_out
                );
                $venue_available->execute();
                if ($venue_available->get_result()->num_rows === 0) {
                    throw new Exception("One or more selected venues are no longer available for these dates");
                }
            }
        }

        // Get room details if selected
        $room_data = null;
        $room_price = 0;
        if ($selected_room) {
            $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->bind_param("i", $selected_room);
            $stmt->execute();
            $room_data = $stmt->get_result()->fetch_assoc();
            
            // Get the correct price based on booking type and multiply by days
            $price_per_day = $booking_type === 'day' ? $room_data['day_price'] : $room_data['night_price'];
            $room_price = $price_per_day * $days;
        }

        // Get venue details if selected and multiply by days
        $venue_data = [];
        $venue_total = 0;
        if (!empty($selected_venues)) {
            $venue_ids = implode(',', array_map('intval', $selected_venues));
            $venues_result = $conn->query("SELECT * FROM venues WHERE id IN ($venue_ids)");
            while ($venue = $venues_result->fetch_assoc()) {
                $venue['total_price'] = floatval($venue['price']) * $days; // Add total price for all days
                $venue_data[] = $venue;
                $venue_total += $venue['total_price'];
            }
        }

        // Calculate total amount
        $total_amount = $room_price + $venue_total;

        // Get customer name
        $stmt = $conn->prepare("SELECT full_name FROM customers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();

        // Generate random booking number
        function generateBookingNumber() {
            $prefix = 'BK';
            $year = date('Y');
            $random = mt_rand(100000, 999999);
            return $prefix . $year . $random;
        }

        // Keep trying until we get a unique booking number
        do {
            $booking_number = generateBookingNumber();
            $check = $conn->prepare("SELECT id FROM bookings WHERE booking_number = ?");
            $check->bind_param("s", $booking_number);
            $check->execute();
        } while ($check->get_result()->num_rows > 0);

        // When preparing booking data, validate dates again
        if (empty($check_in) || empty($check_out) || $check_out === '0000-00-00') {
            error_log("Invalid dates detected before creating booking data");
            throw new Exception("Invalid booking dates");
        }

        // Prepare booking data
        $booking_data = [
            'user_id' => $user_id,
            'booking_number' => $booking_number,
            'room_id' => $selected_room,
            'room_name' => $room_data ? $room_data['room_name'] : null,
            'room_price' => $room_price,
            'price_per_day' => $price_per_day ?? 0,
            'venues' => $venue_data,
            'venue_total' => $venue_total,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $_POST['guests'],
            'booking_type' => $booking_type,
            'total_amount' => $total_amount,
            'days' => $days,
            'customer_name' => $customer['full_name'] ?? $_SESSION['name'] ?? 'Guest'
        ];

        error_log("Final booking data: " . print_r($booking_data, true));

        echo json_encode([
            'success' => true,
            'booking_data' => $booking_data
        ]);

    } catch (Exception $e) {
        error_log("Booking Handler Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>