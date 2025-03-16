<?php
session_start();
require_once '../config/database.php';

header("Content-Type: application/json");

$sql = "SELECT id, room_name, description, price, capacity, base_price, day_price, night_price, status, inclusions, image FROM rooms WHERE status = 'available'";
$result = $conn->query($sql);

$rooms = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['inclusions'] = json_decode($row['inclusions'], true) ?? [];
        $rooms[] = $row;
    }
} else {
    echo json_encode(["message" => "No rooms available."]);
    exit;
}

echo json_encode($rooms);
$conn->close();
?>