<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $check_in = $_GET['check_in'];
    $check_out = $_GET['check_out'];

    // Get rooms that are available or have completed bookings
    $query = "
        SELECT DISTINCT r.* 
        FROM rooms r
        LEFT JOIN booking_rooms br ON r.id = br.room_id
        LEFT JOIN bookings b ON br.booking_id = b.id
        WHERE (
            b.id IS NULL  -- Room has no bookings
            OR b.status = 'completed'  -- Room has completed bookings
            OR (
                b.status = 'confirmed'
                AND NOT (
                    (b.check_in_date <= ? AND b.check_out_date >= ?)
                    OR (b.check_in_date <= ? AND b.check_out_date >= ?)
                    OR (b.check_in_date >= ? AND b.check_out_date <= ?)
                )
            )
        )
        AND r.status = 'active'
        ORDER BY r.room_name ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", 
        $check_in, $check_in,
        $check_out, $check_out,
        $check_in, $check_out
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $rooms = array();
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }

    echo json_encode(['success' => true, 'rooms' => $rooms]);
}
?> 