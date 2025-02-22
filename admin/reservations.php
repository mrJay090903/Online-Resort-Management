<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get all bookings with customer details
$bookings_query = "
    SELECT 
        b.*,
        c.full_name as customer_name,
        c.contact_number,
        u.email,
        GROUP_CONCAT(DISTINCT r.room_name) as room_names,
        GROUP_CONCAT(DISTINCT br.time_slot) as time_slots,
        GROUP_CONCAT(DISTINCT v.name) as venue_names,
        GROUP_CONCAT(DISTINCT v.price) as venue_prices
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id
    JOIN users u ON c.user_id = u.id
    LEFT JOIN booking_rooms br ON b.id = br.booking_id
    LEFT JOIN rooms r ON br.room_id = r.id
    LEFT JOIN booking_venues bv ON b.id = bv.booking_id
    LEFT JOIN venues v ON bv.venue_id = v.id
    GROUP BY b.id
    ORDER BY b.created_at DESC";

$bookings = $conn->query($bookings_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Reservations - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include('components/sidebar.php'); ?>
    <div class="flex-1">
      <?php include('components/header.php'); ?>
      <!-- Main Content -->
      <div class="flex-1 overflow-auto">
        <div class="p-8">
          <h1 class="text-3xl font-semibold text-gray-800 mb-8">Manage Reservations</h1>

          <!-- Filters -->
          <div class="mb-6 flex gap-4">
            <button onclick="filterBookings('all')" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">
              All
            </button>
            <button onclick="filterBookings('pending')" class="px-4 py-2 rounded-lg bg-yellow-100 hover:bg-yellow-200">
              Pending
            </button>
            <button onclick="filterBookings('confirmed')" class="px-4 py-2 rounded-lg bg-green-100 hover:bg-green-200">
              Confirmed
            </button>
            <button onclick="filterBookings('cancelled')" class="px-4 py-2 rounded-lg bg-red-100 hover:bg-red-200">
              Cancelled
            </button>
            <button onclick="filterBookings('completed')" class="px-4 py-2 rounded-lg bg-blue-100 hover:bg-blue-200">
              Completed
            </button>
          </div>

          <!-- Bookings Table -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Booking ID
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Customer
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Room/Venue
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Dates
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Payment
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                <tr class="booking-row" data-status="<?php echo $booking['status']; ?>">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php echo htmlspecialchars($booking['booking_number']); ?>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">
                      <?php echo htmlspecialchars($booking['customer_name']); ?>
                    </div>
                    <div class="text-sm text-gray-500">
                      <?php echo htmlspecialchars($booking['email']); ?>
                    </div>
                    <div class="text-sm text-gray-500">
                      <?php echo htmlspecialchars($booking['contact_number']); ?>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <?php 
                    $room_names = explode(',', $booking['room_names']);
                    $time_slots = explode(',', $booking['time_slots']);
                    $venue_names = explode(',', $booking['venue_names']);
                    $venue_prices = explode(',', $booking['venue_prices']);
                    
                    // Display Rooms
                    if (!empty($booking['room_names'])): 
                        foreach($room_names as $index => $room): ?>
                    <div class="text-sm text-gray-900 mb-1">
                      <span class="font-medium">Room:</span> <?php echo htmlspecialchars($room); ?>
                      <?php if (isset($time_slots[$index])): ?>
                      <div class="text-xs text-gray-500">
                        <?php echo ucfirst($time_slots[$index]); ?> Use
                      </div>
                      <?php endif; ?>
                    </div>
                    <?php endforeach;
                    endif;

                    // Display Venues
                    if (!empty($booking['venue_names'])): 
                        foreach($venue_names as $index => $venue): ?>
                    <div class="text-sm text-gray-900 <?php echo !empty($booking['room_names']) ? 'mt-2' : ''; ?> mb-1">
                      <span class="font-medium">Venue:</span> <?php echo htmlspecialchars($venue); ?>
                      <?php if (isset($venue_prices[$index])): ?>
                      <div class="text-xs text-gray-500">
                        ₱<?php echo number_format($venue_prices[$index], 2); ?>
                      </div>
                      <?php endif; ?>
                    </div>
                    <?php endforeach;
                    endif;

                    // Show message if no rooms or venues
                    if (empty($booking['room_names']) && empty($booking['venue_names'])): ?>
                    <div class="text-sm text-gray-500 italic">
                      No room/venue selected
                    </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">
                      Check-in: <?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?>
                    </div>
                    <div class="text-sm text-gray-900">
                      Check-out: <?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
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
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">
                      Total: ₱<?php echo number_format($booking['total_amount'], 2); ?>
                    </div>
                    <div class="text-sm text-gray-500">
                      Down: ₱<?php echo number_format($booking['down_payment'], 2); ?>
                    </div>
                    <div class="text-xs text-gray-500">
                      <?php echo ucfirst($booking['payment_status']); ?>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm font-medium">
                    <?php if ($booking['status'] === 'pending'): ?>
                    <button onclick="confirmBooking(<?php echo $booking['id']; ?>)"
                      class="text-green-600 hover:text-green-900 mr-3">
                      Confirm
                    </button>
                    <button onclick="rejectBooking(<?php echo $booking['id']; ?>)"
                      class="text-red-600 hover:text-red-900">
                      Reject
                    </button>
                    <?php elseif ($booking['status'] === 'confirmed'): ?>
                    <button onclick="completeBooking(<?php echo $booking['id']; ?>)"
                      class="text-blue-600 hover:text-blue-900">
                      Mark Complete
                    </button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>


  <script>
  function filterBookings(status) {
    const rows = document.querySelectorAll('.booking-row');
    rows.forEach(row => {
      if (status === 'all' || row.dataset.status === status) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }

  function confirmBooking(bookingId) {
    Swal.fire({
      title: 'Confirm Booking',
      text: 'Are you sure you want to confirm this booking?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#059669',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, confirm it'
    }).then((result) => {
      if (result.isConfirmed) {
        updateBookingStatus(bookingId, 'confirmed');
      }
    });
  }

  function rejectBooking(bookingId) {
    Swal.fire({
      title: 'Reject Booking',
      text: 'Are you sure you want to reject this booking?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, reject it'
    }).then((result) => {
      if (result.isConfirmed) {
        updateBookingStatus(bookingId, 'cancelled');
      }
    });
  }

  function completeBooking(bookingId) {
    Swal.fire({
      title: 'Complete Booking',
      text: 'Mark this booking as completed?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, complete it'
    }).then((result) => {
      if (result.isConfirmed) {
        updateBookingStatus(bookingId, 'completed');
      }
    });
  }

  function updateBookingStatus(bookingId, status) {
    fetch('../handlers/update_booking_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          booking_id: bookingId,
          status: status
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: 'Success',
            text: 'Booking status updated successfully',
            icon: 'success',
            confirmButtonColor: '#059669'
          }).then(() => {
            window.location.reload();
          });
        } else {
          throw new Error(data.message || 'Failed to update booking status');
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
  </script>
</body>

</html>