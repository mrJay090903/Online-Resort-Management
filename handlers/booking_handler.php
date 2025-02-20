<?php

// Inside the booking processing logic
foreach ($formData['room'] as $roomId => $quantities) {
    if (!empty($quantities['day']) && $quantities['day'] > 0) {
        $stmt = $conn->prepare("INSERT INTO booking_rooms (booking_id, room_id, time_slot, quantity, price_per_night) 
                               SELECT ?, ?, 'day', ?, day_price 
                               FROM rooms WHERE id = ?");
        $stmt->bind_param("iiii", $bookingId, $roomId, $quantities['day'], $roomId);
        $stmt->execute();
    }
    
    if (!empty($quantities['night']) && $quantities['night'] > 0) {
        $stmt = $conn->prepare("INSERT INTO booking_rooms (booking_id, room_id, time_slot, quantity, price_per_night) 
                               SELECT ?, ?, 'night', ?, night_price 
                               FROM rooms WHERE id = ?");
        $stmt->bind_param("iiii", $bookingId, $roomId, $quantities['night'], $roomId);
        $stmt->execute();
    }
} 