<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has appropriate access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'staff'])) {
    header('Location: ../index.php');
    exit();
}

// Pagination variables
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the WHERE clause
$where_clause = [];
if ($search) {
    $where_clause[] = "(c.full_name LIKE '%$search%' OR b.booking_number LIKE '%$search%')";
}
if ($status_filter !== 'all') {
    $where_clause[] = "b.status = '$status_filter'";
}

$where_sql = !empty($where_clause) ? "WHERE " . implode(' AND ', $where_clause) : '';

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
    $where_sql
    GROUP BY b.id
    ORDER BY b.created_at DESC
    LIMIT $limit OFFSET $offset";

$bookings = $conn->query($bookings_query);

// Get booking statistics
$stats_query = "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
        COUNT(CASE WHEN status = 'reschedule' THEN 1 END) as reschedule
    FROM bookings
";
$stats = $conn->query($stats_query)->fetch_assoc();

// Get total for pagination
$total_query = "SELECT COUNT(DISTINCT b.id) as total FROM bookings b JOIN customers c ON b.customer_id = c.id $where_sql";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_bookings = $total_row['total'];
$total_pages = ceil($total_bookings / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Reservations - Admin</title>
  <link href="../src/output.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50">
  <div class="flex h-screen">
    <?php include('components/sidebar.php'); ?>
    <div class="flex-1 flex flex-col overflow-hidden">
      <?php include('components/header.php'); ?>

      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="container mx-auto px-6 py-8">
          <div class="mb-8">
            <h1 class="text-3xl font-semibold text-gray-800">Manage Reservations</h1>
            <p class="text-gray-600 mt-2">Overview and management of all bookings</p>
          </div>

          <!-- Statistics Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-500">Total</p>
                  <p class="text-lg font-semibold"><?php echo $stats['total']; ?></p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-500">Pending</p>
                  <p class="text-lg font-semibold"><?php echo $stats['pending']; ?></p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-500">Confirmed</p>
                  <p class="text-lg font-semibold"><?php echo $stats['confirmed']; ?></p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-500">Reschedule</p>
                  <p class="text-lg font-semibold"><?php echo $stats['reschedule']; ?></p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-500">Rejected</p>
                  <p class="text-lg font-semibold"><?php echo $stats['rejected']; ?></p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-500">Completed</p>
                  <p class="text-lg font-semibold"><?php echo $stats['completed']; ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Filters and Search -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div class="flex flex-wrap gap-2">
                <button onclick="filterBookings('all')"
                  class="px-4 py-2 rounded-lg transition-colors <?php echo !isset($_GET['status']) || $_GET['status'] === 'all' ? 'bg-gray-200 text-gray-800' : 'hover:bg-gray-100 text-gray-600'; ?>">
                  All
                </button>
                <button onclick="filterBookings('pending')"
                  class="px-4 py-2 rounded-lg transition-colors <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'hover:bg-yellow-50 text-gray-600'; ?>">
                  Pending
                </button>
                <button onclick="filterBookings('confirmed')"
                  class="px-4 py-2 rounded-lg transition-colors <?php echo isset($_GET['status']) && $_GET['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'hover:bg-green-50 text-gray-600'; ?>">
                  Confirmed
                </button>
                <button onclick="filterBookings('reschedule')"
                  class="px-4 py-2 rounded-lg transition-colors <?php echo isset($_GET['status']) && $_GET['status'] === 'reschedule' ? 'bg-purple-100 text-purple-800' : 'hover:bg-purple-50 text-gray-600'; ?>">
                  Reschedule
                </button>
                <button onclick="filterBookings('completed')"
                  class="px-4 py-2 rounded-lg transition-colors <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'hover:bg-blue-50 text-gray-600'; ?>">
                  Completed
                </button>
                <button onclick="filterBookings('rejected')"
                  class="px-4 py-2 rounded-lg transition-colors <?php echo isset($_GET['status']) && $_GET['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'hover:bg-red-50 text-gray-600'; ?>">
                  Rejected
                </button>
              </div>

              <div class="flex gap-2">
                <div class="relative">
                  <input type="text" placeholder="Search bookings..."
                    class="w-full md:w-64 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value="<?php echo htmlspecialchars($search); ?>" onkeyup="searchBookings(this.value)">
                  <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Bookings Table -->
          <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking
                      Info</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                      Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking
                      Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php while ($booking = $bookings->fetch_assoc()): ?>
                  <tr>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-gray-900">
                        #<?php echo htmlspecialchars($booking['booking_number']); ?>
                      </div>
                      <div class="text-sm text-gray-500">
                        <?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900"><?php echo htmlspecialchars($booking['customer_name']); ?>
                      </div>
                      <div class="text-sm text-gray-500"><?php echo htmlspecialchars($booking['contact_number']); ?>
                      </div>
                      <div class="text-sm text-gray-500"><?php echo htmlspecialchars($booking['email']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900">
                        <?php if (!empty($booking['room_names'])): ?>
                        <div>Rooms: <?php echo htmlspecialchars($booking['room_names']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($booking['venue_names'])): ?>
                        <div>Venues: <?php echo htmlspecialchars($booking['venue_names']); ?></div>
                        <?php endif; ?>
                      </div>
                      <div class="text-sm text-gray-500">
                        Check-in: <?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?><br>
                        Check-out: <?php echo date('M j, Y', strtotime($booking['check_out_date'])); ?>
                      </div>
                      <div class="text-sm font-medium text-gray-900 mt-1">
                        ₱<?php echo number_format($booking['total_amount'], 2); ?>
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
                    </td>
                    <td class="px-6 py-4 text-sm font-medium">
                      <div class="flex space-x-2">
                        <?php if ($booking['status'] === 'pending'): ?>
                        <button onclick="confirmBooking(<?php echo $booking['id']; ?>)"
                          class="text-green-600 hover:text-green-900">
                          Confirm
                        </button>
                        <button onclick="rejectBooking(<?php echo $booking['id']; ?>)"
                          class="text-red-600 hover:text-red-900">
                          Reject
                        </button>
                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                        <button onclick="completeBooking(<?php echo $booking['id']; ?>)"
                          class="text-blue-600 hover:text-blue-900">
                          Complete
                        </button>
                        <?php elseif ($booking['status'] === 'reschedule'): ?>
                        <button onclick="approveReschedule(<?php echo $booking['id']; ?>)"
                          class="text-green-600 hover:text-green-900">
                          Approve
                        </button>
                        <button onclick="rejectReschedule(<?php echo $booking['id']; ?>)"
                          class="text-red-600 hover:text-red-900">
                          Reject
                        </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
              <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                  Previous
                </a>
                <?php endif; ?>
                <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                  Next
                </a>
                <?php endif; ?>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium"><?php echo $offset + 1; ?></span>
                    to
                    <span class="font-medium"><?php echo min($offset + $limit, $total_bookings); ?></span>
                    of
                    <span class="font-medium"><?php echo $total_bookings; ?></span>
                    results
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50
                                            <?php echo $i === $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : ''; ?>">
                      <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                  </nav>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
  function filterBookings(status) {
    window.location.href = `?status=${status}&search=<?php echo urlencode($search); ?>`;
  }

  function searchBookings(term) {
    if (event.key === 'Enter') {
      window.location.href = `?search=${encodeURIComponent(term)}&status=<?php echo $status_filter; ?>`;
    }
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
        fetch('../handlers/update_booking_status.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              booking_id: bookingId,
              status: 'rejected'
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                title: 'Success',
                text: 'Booking has been rejected',
                icon: 'success',
                confirmButtonColor: '#059669'
              }).then(() => {
                window.location.reload();
              });
            } else {
              throw new Error(data.message || 'Failed to reject booking');
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

  function approveReschedule(bookingId) {
    Swal.fire({
      title: 'Approve Reschedule',
      text: 'Are you sure you want to approve this reschedule request?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#059669',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, approve it'
    }).then((result) => {
      if (result.isConfirmed) {
        updateBookingStatus(bookingId, 'confirmed');
      }
    });
  }

  function rejectReschedule(bookingId) {
    Swal.fire({
      title: 'Reject Reschedule',
      text: 'Are you sure you want to reject this reschedule request?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, reject it'
    }).then((result) => {
      if (result.isConfirmed) {
        updateBookingStatus(bookingId, 'rejected');
      }
    });
  }
  </script>
</body>

</html>