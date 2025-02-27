<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch monthly revenue data for the last 6 months
$revenue_query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_amount) as revenue
    FROM bookings
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
";
$revenue_result = $conn->query($revenue_query);
$revenue_data = [];
$revenue_labels = [];
while ($row = $revenue_result->fetch_assoc()) {
    $revenue_labels[] = date('M', strtotime($row['month']));
    $revenue_data[] = $row['revenue'];
}

// Fetch booking statistics
$bookings_query = "
    SELECT 
        status,
        COUNT(*) as count
    FROM bookings
    GROUP BY status
";
$bookings_result = $conn->query($bookings_query);
$booking_stats = [
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0
];
while ($row = $bookings_result->fetch_assoc()) {
    $booking_stats[$row['status']] = (int)$row['count'];
}

// Get total users count
$users_query = "SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'";
$users_result = $conn->query($users_query);
$users_count = $users_result->fetch_assoc()['total'];

// Get active reservations count
$active_reservations_query = "SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'";
$active_reservations_result = $conn->query($active_reservations_query);
$active_reservations_count = $active_reservations_result->fetch_assoc()['total'];

// Get available rooms count
$available_rooms_query = "SELECT COUNT(*) as total FROM rooms WHERE status = 'available'";
$available_rooms_result = $conn->query($available_rooms_query);
$available_rooms_count = $available_rooms_result->fetch_assoc()['total'];

// Calculate monthly revenue
$monthly_revenue_query = "
    SELECT SUM(total_amount) as total 
    FROM bookings 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
";
$monthly_revenue_result = $conn->query($monthly_revenue_query);
$monthly_revenue = $monthly_revenue_result->fetch_assoc()['total'] ?? 0;

// Get recent bookings
$recent_bookings_query = "
    SELECT 
        b.id,
        b.created_at,
        b.status,
        b.total_amount,
        c.full_name,
        GROUP_CONCAT(DISTINCT r.room_name) as rooms,
        GROUP_CONCAT(DISTINCT v.name) as venues
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id
    LEFT JOIN booking_rooms br ON b.id = br.booking_id
    LEFT JOIN rooms r ON br.room_id = r.id
    LEFT JOIN booking_venues bv ON b.id = bv.booking_id
    LEFT JOIN venues v ON bv.venue_id = v.id
    GROUP BY b.id
    ORDER BY b.created_at DESC
    LIMIT 5
";
$recent_bookings = $conn->query($recent_bookings_query);

// Get popular rooms
$popular_rooms_query = "
    SELECT 
        r.room_name,
        r.price,
        COUNT(*) as booking_count
    FROM booking_rooms br
    JOIN rooms r ON br.room_id = r.id
    GROUP BY r.id
    ORDER BY booking_count DESC
    LIMIT 5
";
$popular_rooms = $conn->query($popular_rooms_query);

// Get popular venues
$popular_venues_query = "
    SELECT 
        v.name,
        v.price,
        COUNT(*) as booking_count
    FROM booking_venues bv
    JOIN venues v ON bv.venue_id = v.id
    GROUP BY v.id
    ORDER BY booking_count DESC
    LIMIT 5
";
$popular_venues = $conn->query($popular_venues_query);

// Get recent activities
$activities_query = "
    SELECT 
        'booking' as type,
        b.id,
        b.created_at,
        b.status,
        c.full_name as user,
        CONCAT('New booking #', b.id) as details
    FROM bookings b
    JOIN customers c ON b.customer_id = c.id
    UNION ALL
    SELECT 
        'feedback' as type,
        f.id,
        f.created_at,
        f.status,
        c.full_name as user,
        f.message as details
    FROM feedbacks f
    JOIN customers c ON f.customer_id = c.id
    ORDER BY created_at DESC
    LIMIT 10
";
$activities = $conn->query($activities_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Casita de Grands</title>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <script src="https://cdn.lordicon.com/bhenfmcm.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
</head>

<body class="bg-gray-50">
  <div class="flex">
    <?php include('components/sidebar.php'); ?>

    <div class="flex-1">
      <?php include('components/header.php'); ?>

      <main class="p-8">
        <div class="max-w-7xl mx-auto">
          <!-- Header -->
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard Overview</h1>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Users Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                  <lord-icon src="https://cdn.lordicon.com/dxjqoygy.json" trigger="hover" colors="primary:#3b82f6"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Total Users</h2>
                  <p class="text-2xl font-semibold"><?php echo number_format($users_count); ?></p>
                </div>
              </div>
            </div>

            <!-- Active Reservations -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                  <lord-icon src="https://cdn.lordicon.com/uukerzzv.json" trigger="hover" colors="primary:#22c55e"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Active Reservations</h2>
                  <p class="text-2xl font-semibold"><?php echo number_format($active_reservations_count); ?></p>
                </div>
              </div>
            </div>

            <!-- Available Rooms -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                  <lord-icon src="https://cdn.lordicon.com/gmzxduhd.json" trigger="hover" colors="primary:#eab308"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Available Rooms</h2>
                  <p class="text-2xl font-semibold"><?php echo number_format($available_rooms_count); ?></p>
                </div>
              </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                  <lord-icon src="https://cdn.lordicon.com/qhviklyi.json" trigger="hover" colors="primary:#9333ea"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Monthly Revenue</h2>
                  <p class="text-2xl font-semibold">₱<?php echo number_format($monthly_revenue); ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Section -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-xl font-semibold text-gray-800 mb-4">Revenue Overview</h2>
              <canvas id="revenueChart"></canvas>
            </div>

            <!-- Bookings Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-xl font-semibold text-gray-800 mb-4">Booking Statistics</h2>
              <canvas id="bookingsChart"></canvas>
            </div>
          </div>

          <!-- Recent Bookings and Popular Items -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Bookings</h2>
              <div class="space-y-4">
                <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                <div class="border-b border-gray-200 pb-3">
                  <div class="flex justify-between items-start">
                    <div>
                      <h3 class="font-medium"><?php echo htmlspecialchars($booking['full_name']); ?></h3>
                      <p class="text-sm text-gray-600">
                        <?php 
                          echo $booking['rooms'] ? 'Rooms: ' . htmlspecialchars($booking['rooms']) : '';
                          echo $booking['venues'] ? ($booking['rooms'] ? ' | ' : '') . 'Venues: ' . htmlspecialchars($booking['venues']) : '';
                        ?>
                      </p>
                    </div>
                    <div class="text-right">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?php
                          switch($booking['status']) {
                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                            case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                            case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                          }
                        ?>">
                        <?php echo ucfirst($booking['status']); ?>
                      </span>
                      <p class="text-sm text-gray-600 mt-1">₱<?php echo number_format($booking['total_amount']); ?></p>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">
                    <?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?>
                  </p>
                </div>
                <?php endwhile; ?>
              </div>
            </div>

            <!-- Popular Items -->
            <div class="space-y-6">
              <!-- Popular Rooms -->
              <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Popular Rooms</h2>
                <div class="space-y-3">
                  <?php while ($room = $popular_rooms->fetch_assoc()): ?>
                  <div class="flex justify-between items-center">
                    <div>
                      <h3 class="font-medium"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                      <p class="text-sm text-gray-600"><?php echo $room['booking_count']; ?> bookings</p>
                    </div>
                    <span class="text-sm font-medium text-gray-900">₱<?php echo number_format($room['price']); ?></span>
                  </div>
                  <?php endwhile; ?>
                </div>
              </div>

              <!-- Popular Venues -->
              <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Popular Venues</h2>
                <div class="space-y-3">
                  <?php while ($venue = $popular_venues->fetch_assoc()): ?>
                  <div class="flex justify-between items-center">
                    <div>
                      <h3 class="font-medium"><?php echo htmlspecialchars($venue['name']); ?></h3>
                      <p class="text-sm text-gray-600"><?php echo $venue['booking_count']; ?> bookings</p>
                    </div>
                    <span
                      class="text-sm font-medium text-gray-900">₱<?php echo number_format($venue['price']); ?></span>
                  </div>
                  <?php endwhile; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity Table -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-semibold text-gray-800">Recent Activity</h2>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php while ($activity = $activities->fetch_assoc()): ?>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?php echo $activity['type'] === 'booking' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                        <?php echo ucfirst($activity['type']); ?>
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($activity['user']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($activity['details']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?php
                          switch($activity['status']) {
                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                            case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                            case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                          }
                        ?>">
                        <?php echo ucfirst($activity['status']); ?>
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
  // Revenue Chart
  const revenueCtx = document.getElementById('revenueChart').getContext('2d');
  new Chart(revenueCtx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($revenue_labels); ?>,
      datasets: [{
        label: 'Monthly Revenue',
        data: <?php echo json_encode($revenue_data); ?>,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        },
        title: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₱' + value.toLocaleString();
            }
          }
        }
      }
    }
  });

  // Bookings Chart
  const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
  new Chart(bookingsCtx, {
    type: 'bar',
    data: {
      labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
      datasets: [{
        label: 'Number of Bookings',
        data: [<?php echo $booking_stats['pending']; ?>, <?php echo $booking_stats['confirmed']; ?>,
          <?php echo $booking_stats['completed']; ?>, <?php echo $booking_stats['cancelled']; ?>
        ],
        backgroundColor: [
          'rgba(234, 179, 8, 0.5)', // yellow for pending
          'rgba(34, 197, 94, 0.5)', // green for confirmed
          'rgba(59, 130, 246, 0.5)', // blue for completed
          'rgba(239, 68, 68, 0.5)' // red for cancelled
        ],
        borderColor: [
          'rgb(234, 179, 8)',
          'rgb(34, 197, 94)',
          'rgb(59, 130, 246)',
          'rgb(239, 68, 68)'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
  </script>
</body>

</html>