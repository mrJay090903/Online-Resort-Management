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
$booking_query = "
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
    GROUP BY b.id, b.booking_number
    ORDER BY b.created_at DESC";

$bookings_stmt = $conn->prepare($booking_query);
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
  <link href="../src/output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Add SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Add flatpickr for better date picking -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <!-- Add these in the head section if not already present -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
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
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-lg font-semibold">
                Booking Reference: <?php echo htmlspecialchars($booking['booking_number']); ?>
              </h3>
              <p class="text-gray-600">
                <?php if (!empty($booking['room_names'])): ?>
                <span class="block">Rooms: <?php echo htmlspecialchars($booking['room_names']); ?></span>
                <?php endif; ?>
                <?php if (!empty($booking['venue_names'])): ?>
                <span class="block">Venues: <?php echo htmlspecialchars($booking['venue_names']); ?></span>
                <?php endif; ?>
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
                                        case 'rejected':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'completed':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'reschedule':
                                            echo 'bg-purple-100 text-purple-800';
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
                                        $room_names = !empty($booking['room_names']) ? explode(',', $booking['room_names']) : [];
                                        $time_slots = !empty($booking['time_slots']) ? explode(',', $booking['time_slots']) : [];
                                        $room_prices = !empty($booking['room_prices']) ? explode(',', $booking['room_prices']) : [];
                                        $venue_names = !empty($booking['venue_names']) ? explode(',', $booking['venue_names']) : [];
                                        $venue_prices = !empty($booking['venue_prices']) ? explode(',', $booking['venue_prices']) : [];

                                        // Display rooms if any
                                        if (!empty($room_names)): 
                                            foreach($room_names as $index => $room): ?>
                <div class="mb-1">
                  <span class="font-medium">Room:</span> <?php echo htmlspecialchars($room); ?>
                  <?php if (isset($room_prices[$index])): ?>
                  <div class="text-xs text-gray-500">
                    ₱<?php echo number_format((float)$room_prices[$index], 2); ?>
                    <?php echo isset($time_slots[$index]) ? ' - ' . $time_slots[$index] : ''; ?>
                  </div>
                  <?php endif; ?>
                </div>
                <?php endforeach;
                                        endif;

                                        // Display venues if any
                                        if (!empty($venue_names)): 
                                            foreach($venue_names as $index => $venue): ?>
                <div class="mb-1">
                  <span class="font-medium">Venue:</span> <?php echo htmlspecialchars($venue); ?>
                  <?php if (isset($venue_prices[$index])): ?>
                  <div class="text-xs text-gray-500">
                    ₱<?php echo number_format((float)$venue_prices[$index], 2); ?>
                  </div>
                  <?php endif; ?>
                </div>
                <?php endforeach;
                                        endif;

                                        if (empty($room_names) && empty($venue_names)): ?>
                <p class="text-gray-500 italic">No room/venue selected</p>
                <?php endif; ?>

                <div class="mt-3">
                  <p>Check-in: <?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></p>
                  <p>Check-out: <?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></p>
                  <p>Guests: <?php echo htmlspecialchars($booking['total_guests']); ?></p>
                </div>
              </div>
            </div>

            <div>
              <h4 class="font-medium text-gray-700">Payment Details</h4>
              <div class="mt-2 space-y-2 text-sm">
                <p>Total Amount: ₱<?php echo number_format($booking['total_amount'], 2); ?></p>
                <p>Down Payment: ₱<?php echo number_format($booking['down_payment'], 2); ?></p>
                <?php 
                                        $remaining = $booking['total_amount'] - $booking['down_payment'];
                                        $payment_percentage = ($booking['down_payment'] / $booking['total_amount']) * 100;
                                        ?>

                <?php if ($booking['status'] === 'confirmed'): ?>
                <p class="font-medium text-red-600">
                  Remaining Balance: ₱<?php echo number_format($remaining, 2); ?>
                </p>
                <?php if ($remaining > 0): ?>
                <p class="text-xs text-gray-500 mt-1">
                  Please pay the remaining balance upon check-in
                </p>
                <?php endif; ?>
                <?php endif; ?>

                <p class="mt-2">
                  <span class="font-medium">Payment Status:</span>
                  <span class="font-medium 
                                                <?php
                                                if ($booking['status'] === 'rejected') {
                                                    echo 'text-red-600';
                                                } elseif ($booking['status'] === 'completed' || $payment_percentage >= 100) {
                                                    echo 'text-green-600';
                                                } elseif ($payment_percentage >= 50) {
                                                    echo 'text-orange-600';
                                                } else {
                                                    echo 'text-red-600';
                                                }
                                                ?>">
                    <?php
                                                if ($booking['status'] === 'rejected') {
                                                    echo 'Booking Rejected';
                                                } elseif ($booking['status'] === 'completed' || $payment_percentage >= 100) {
                                                    echo 'Fully Paid';
                                                } elseif ($payment_percentage >= 50) {
                                                    echo '50% Paid';
                                                } else {
                                                    echo 'Partially Paid';
                                                }
                                                ?>
                  </span>
                </p>

                <?php if ($booking['status'] === 'completed'): ?>
                <div class="mt-2 text-xs text-green-600">
                  <p>✓ Booking completed</p>
                  <p>✓ Payment completed</p>
                </div>
                <?php elseif ($payment_percentage >= 50 && $payment_percentage < 100): ?>
                <div class="mt-2 text-xs text-gray-600">
                  <p>✓ 50% Down payment received</p>
                  <p>• Remaining balance due upon check-in</p>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php if ($booking['status'] === 'confirmed'): ?>
          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <div>
                <?php if ($remaining > 0): ?>
                <p class="text-sm text-gray-600">
                  Remaining balance of ₱<?php echo number_format($remaining, 2); ?>
                  is due upon check-in
                </p>
                <?php endif; ?>
              </div>
              <button onclick="openRescheduleModal(<?php echo $booking['id']; ?>)"
                class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 transition-colors">
                Request Reschedule
              </button>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($booking['status'] === 'rejected'): ?>
          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center text-red-600">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm">This booking has been rejected. Please make a new booking if you wish to proceed.</p>
            </div>
            <div class="mt-4">
              <a href="reservations.php" 
                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M12 4v16m8-8H4" />
                </svg>
                Make New Booking
              </a>
            </div>
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
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
          </path>
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

  <!-- Footer -->
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

  function openRescheduleModal(bookingId) {
    document.getElementById('booking_id').value = bookingId;
    document.getElementById('rescheduleModal').classList.remove('hidden');
    document.getElementById('rescheduleModal').classList.add('flex');
    // Reset form
    document.getElementById('rescheduleForm').reset();
    // Reset date pickers
    checkInPicker.clear();
    checkOutPicker.clear();
  }

  function closeRescheduleModal() {
    document.getElementById('rescheduleModal').classList.add('hidden');
    document.getElementById('rescheduleModal').classList.remove('flex');
  }

  function submitReschedule(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    // Validate dates
    const checkIn = new Date(formData.get('reschedule_date_in'));
    const checkOut = new Date(formData.get('reschedule_date_out'));

    if (checkOut <= checkIn) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid Dates',
        text: 'Check-out date must be after check-in date'
      });
      return;
    }

    Swal.fire({
      title: 'Confirm Reschedule Request',
      text: 'Are you sure you want to request these new dates?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#f97316',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, submit request'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('../handlers/reschedule_booking', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Request Submitted!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
              }).then(() => {
                window.location.reload();
              });
            } else {
              throw new Error(data.message || 'Failed to submit reschedule request');
            }
          })
          .catch(error => {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: error.message
            });
          });
      }
    });
  }

  // Initialize date pickers with flatpickr
  const checkInPicker = flatpickr("#reschedule_date_in", {
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function(selectedDates) {
      // Update the minimum date of check-out to be after check-in
      checkOutPicker.set("minDate", selectedDates[0]);
    }
  });

  const checkOutPicker = flatpickr("#reschedule_date_out", {
    minDate: "today",
    dateFormat: "Y-m-d"
  });
  </script>

  <!-- Reschedule Modal -->
  <div id="rescheduleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h2 class="text-xl font-semibold mb-4">Request Reschedule</h2>
      <form id="rescheduleForm" onsubmit="submitReschedule(event)">
        <input type="hidden" id="booking_id" name="booking_id">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">New Check-in Date</label>
          <input type="date" id="reschedule_date_in" name="reschedule_date_in" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">New Check-out Date</label>
          <input type="date" id="reschedule_date_out" name="reschedule_date_out" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Reason for Reschedule</label>
          <textarea name="reason" id="reason" rows="3" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            placeholder="Please provide a reason for rescheduling"></textarea>
        </div>
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeRescheduleModal()"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
            Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>