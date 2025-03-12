<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['booking_data'])) {
            throw new Exception('Missing booking data');
        }

        $booking_data = $data['booking_data'];

        // Validate required booking fields
        if (!isset($booking_data['check_in']) || !isset($booking_data['check_out']) || !isset($booking_data['guests'])) {
            throw new Exception('Missing required booking fields');
        }

        // Check room availability again before storing in session
        if (!empty($booking_data['selected_rooms'])) {
            $room_ids = array_column($booking_data['selected_rooms'], 'id');
            $room_ids_str = implode(',', $room_ids);
            
            // Check if rooms are available (either no bookings or only completed bookings)
            $check_bookings_query = "
                SELECT r.id, r.room_name 
                FROM rooms r
                WHERE r.id IN ($room_ids_str)
                AND r.status = 'available'
                AND NOT EXISTS (
                    SELECT 1 
                    FROM bookings b
                    INNER JOIN booking_rooms br ON b.id = br.booking_id
                    WHERE br.room_id = r.id
                    AND b.status NOT IN ('completed')
                    AND (
                        (b.check_in_date <= ? AND b.check_out_date >= ?)
                        OR (b.check_in_date <= ? AND b.check_out_date >= ?)
                        OR (b.check_in_date >= ? AND b.check_out_date <= ?)
                    )
                )";

            $stmt = $conn->prepare($check_bookings_query);
            $stmt->bind_param("ssssss", 
                $booking_data['check_in'], $booking_data['check_in'],
                $booking_data['check_out'], $booking_data['check_out'],
                $booking_data['check_in'], $booking_data['check_out']
            );
            $stmt->execute();
            $available_rooms = $stmt->get_result();
            
            // If we don't find all the rooms, some are unavailable
            if ($available_rooms->num_rows < count($room_ids)) {
                // Get the unavailable room names
                $found_room_ids = [];
                while ($row = $available_rooms->fetch_assoc()) {
                    $found_room_ids[] = $row['id'];
                }
                
                // Find which rooms are unavailable
                $unavailable_query = "
                    SELECT room_name 
                    FROM rooms 
                    WHERE id IN ($room_ids_str) 
                    AND id NOT IN (" . implode(',', $found_room_ids ?: [0]) . ")";
                $unavailable_result = $conn->query($unavailable_query);
                
                $unavailable = [];
                while ($row = $unavailable_result->fetch_assoc()) {
                    $unavailable[] = $row['room_name'];
                }
                throw new Exception('The following rooms are not available: ' . implode(', ', $unavailable));
            }
        }

        // Check venue availability similarly
        if (!empty($booking_data['selected_venues'])) {
            $venue_ids = array_column($booking_data['selected_venues'], 'id');
            $venue_ids_str = implode(',', $venue_ids);
            
            $check_bookings_query = "
                SELECT v.id, v.name 
                FROM venues v
                WHERE v.id IN ($venue_ids_str)
                AND v.status = 'available'
                AND NOT EXISTS (
                    SELECT 1 
                    FROM bookings b
                    INNER JOIN booking_venues bv ON b.id = bv.booking_id
                    WHERE bv.venue_id = v.id
                    AND b.status NOT IN ('completed')
                    AND (
                        (b.check_in_date <= ? AND b.check_out_date >= ?)
                        OR (b.check_in_date <= ? AND b.check_out_date >= ?)
                        OR (b.check_in_date >= ? AND b.check_out_date <= ?)
                    )
                )";

            $stmt = $conn->prepare($check_bookings_query);
            $stmt->bind_param("ssssss", 
                $booking_data['check_in'], $booking_data['check_in'],
                $booking_data['check_out'], $booking_data['check_out'],
                $booking_data['check_in'], $booking_data['check_out']
            );
            $stmt->execute();
            $available_venues = $stmt->get_result();
            
            if ($available_venues->num_rows < count($venue_ids)) {
                // Get the unavailable venue names
                $found_venue_ids = [];
                while ($row = $available_venues->fetch_assoc()) {
                    $found_venue_ids[] = $row['id'];
                }
                
                // Find which venues are unavailable
                $unavailable_query = "
                    SELECT name 
                    FROM venues 
                    WHERE id IN ($venue_ids_str) 
                    AND id NOT IN (" . implode(',', $found_venue_ids ?: [0]) . ")";
                $unavailable_result = $conn->query($unavailable_query);
                
                $unavailable = [];
                while ($row = $unavailable_result->fetch_assoc()) {
                    $unavailable[] = $row['name'];
                }
                throw new Exception('The following venues are not available: ' . implode(', ', $unavailable));
            }
        }

        // Store in session
        $_SESSION['pending_booking'] = $booking_data;
        if (isset($data['source_id'])) {
            $_SESSION['payment_source_id'] = $data['source_id'];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking data stored successfully'
        ]);

    } catch (Exception $e) {
        error_log("Store Booking Session Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>