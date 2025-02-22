<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get customer_id from user_id
$customer_query = "SELECT id FROM customers WHERE user_id = ?";
$customer_stmt = $conn->prepare($customer_query);
$customer_stmt->bind_param("i", $_SESSION['user_id']);
$customer_stmt->execute();
$customer = $customer_stmt->get_result()->fetch_assoc();

// Get all bookings for this customer
$bookings_query = "
    SELECT 
        b.*,
        GROUP_CONCAT(DISTINCT r.room_name) as room_names,
        GROUP_CONCAT(DISTINCT br.time_slot) as time_slots,
        GROUP_CONCAT(DISTINCT br.price_per_night) as room_prices,
        GROUP_CONCAT(DISTINCT v.name) as venue_names,
        GROUP_CONCAT(DISTINCT v.price) as venue_prices
    FROM bookings b
    LEFT JOIN booking_rooms br ON b.id = br.booking_id
    LEFT JOIN rooms r ON br.room_id = r.id
    LEFT JOIN booking_venues bv ON b.id = bv.booking_id
    LEFT JOIN venues v ON bv.venue_id = v.id
    WHERE b.customer_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC";

$bookings_stmt = $conn->prepare($bookings_query);
$bookings_stmt->bind_param("i", $customer['id']);
$bookings_stmt->execute();
$bookings = $bookings_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Casita De Grands</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Add SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <?php include('components/nav.php'); ?>

    <div class="container mx-auto px-4 py-24">
        <h1 class="text-3xl font-semibold text-gray-800 mb-8">My Bookings</h1>

        <?php if ($bookings->num_rows > 0): ?>
            <div class="grid gap-6">
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($booking['booking_number'] ?? 'BK'.str_pad($booking['id'], 8, '0', STR_PAD_LEFT)); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Booked on <?php echo date('M d, Y h:i A', strtotime($booking['created_at'])); ?>
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    <?php
                                    switch($booking['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'confirmed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'completed':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                    }
                                    ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <h4 class="font-medium text-gray-700">Booking Details</h4>
                                    <div class="mt-2 space-y-2 text-sm">
                                        <?php 
                                        $room_names = explode(',', $booking['room_names']);
                                        $time_slots = explode(',', $booking['time_slots']);
                                        $room_prices = explode(',', $booking['room_prices']);
                                        $venue_names = explode(',', $booking['venue_names']);
                                        $venue_prices = explode(',', $booking['venue_prices']);

                                        // Display Rooms
                                        if (!empty($booking['room_names'])): 
                                            foreach($room_names as $index => $room): ?>
                                                <div class="mb-2">
                                                    <p class="text-gray-900">
                                                        <span class="font-medium">Room:</span> <?php echo htmlspecialchars($room); ?>
                                                    </p>
                                                    <?php if (isset($time_slots[$index])): ?>
                                                        <p class="text-gray-600 text-xs">
                                                            Type: <?php echo ucfirst($time_slots[$index]); ?> Use
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (isset($room_prices[$index])): ?>
                                                        <p class="text-gray-600 text-xs">
                                                            Price: ₱<?php echo number_format($room_prices[$index], 2); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach;
                                        endif;

                                        // Display Venues
                                        if (!empty($booking['venue_names'])): 
                                            foreach($venue_names as $index => $venue): ?>
                                                <div class="mb-2 <?php echo !empty($booking['room_names']) ? 'mt-3' : ''; ?>">
                                                    <p class="text-gray-900">
                                                        <span class="font-medium">Venue:</span> <?php echo htmlspecialchars($venue); ?>
                                                    </p>
                                                    <?php if (isset($venue_prices[$index])): ?>
                                                        <p class="text-gray-600 text-xs">
                                                            Price: ₱<?php echo number_format($venue_prices[$index], 2); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach;
                                        endif;

                                        if (empty($booking['room_names']) && empty($booking['venue_names'])): ?>
                                            <p class="text-gray-500 italic">No room/venue selected</p>
                                        <?php endif; ?>

                                        <div class="mt-3">
                                            <p>Check-in: <?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></p>
                                            <p>Check-out: <?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></p>
                                            <p>Guests: <?php echo $booking['total_guests']; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-700">Payment Details</h4>
                                    <div class="mt-2 space-y-2 text-sm">
                                        <p>Total Amount: ₱<?php echo number_format($booking['total_amount'], 2); ?></p>
                                        <p>Down Payment: ₱<?php echo number_format($booking['down_payment'], 2); ?></p>
                                        <p>Payment Status: <?php echo ucfirst($booking['payment_status']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <?php if ($booking['status'] === 'pending'): ?>
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <button onclick="cancelBooking(<?php echo $booking['id']; ?>)" 
                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Cancel Booking
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No bookings yet</h3>
                <p class="text-gray-500 mb-6">You haven't made any bookings yet.</p>
                <a href="reservations.php" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
                    Make a Reservation
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include('components/footer.php'); ?>

    <script>
    function cancelBooking(bookingId) {
        Swal.fire({
            title: 'Cancel Booking',
            text: 'Are you sure you want to cancel this booking?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send cancellation request
                fetch('../handlers/cancel_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        booking_id: bookingId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Booking Cancelled',
                            text: 'Your booking has been cancelled successfully.',
                            icon: 'success',
                            confirmButtonColor: '#059669'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to cancel booking');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error',
                        confirmButtonColor: '#059669'
                    });
                });
            }
        });
    }
    </script>
</body>
</html> 