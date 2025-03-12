<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $check_in = $_GET['check_in'];
    $check_out = $_GET['check_out'];

    // Get venues that are available or have completed bookings
    $query = "
        SELECT DISTINCT v.* 
        FROM venues v
        LEFT JOIN booking_venues bv ON v.id = bv.venue_id
        LEFT JOIN bookings b ON bv.booking_id = b.id
        WHERE (
            b.id IS NULL  -- Venue has no bookings
            OR b.status = 'completed'  -- Venue has completed bookings
            OR (
                b.status = 'confirmed'
                AND NOT (
                    (b.check_in_date <= ? AND b.check_out_date >= ?)
                    OR (b.check_in_date <= ? AND b.check_out_date >= ?)
                    OR (b.check_in_date >= ? AND b.check_out_date <= ?)
                )
            )
        )
        AND v.status = 'active'
        ORDER BY v.name ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", 
        $check_in, $check_in,
        $check_out, $check_out,
        $check_in, $check_out
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $venues = array();
    while ($row = $result->fetch_assoc()) {
        $venues[] = $row;
    }

    echo json_encode(['success' => true, 'venues' => $venues]);
}
?>