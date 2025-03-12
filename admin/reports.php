<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index');
    exit();
}

// Get date range from request, default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Revenue Statistics
$revenue_query = "
    SELECT 
        SUM(total_amount) as total_revenue,
        COUNT(*) as total_bookings,
        AVG(total_amount) as average_booking_value,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_revenue
    FROM bookings 
    WHERE created_at BETWEEN ? AND ?
";
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$revenue_stats = $stmt->get_result()->fetch_assoc();

// Room Performance
$room_performance_query = "
    SELECT 
        r.room_name,
        COUNT(br.booking_id) as booking_count,
        SUM(b.total_amount) as revenue,
        ROUND(COUNT(br.booking_id) * 100.0 / (
            SELECT COUNT(*) FROM booking_rooms
            JOIN bookings b2 ON booking_rooms.booking_id = b2.id
            WHERE b2.created_at BETWEEN ? AND ?
        ), 1) as booking_percentage
    FROM rooms r
    LEFT JOIN booking_rooms br ON r.id = br.room_id
    LEFT JOIN bookings b ON br.booking_id = b.id AND b.created_at BETWEEN ? AND ?
    GROUP BY r.id
    ORDER BY booking_count DESC
";
$stmt = $conn->prepare($room_performance_query);
$stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$room_performance = $stmt->get_result();

// Booking Status Distribution
$booking_status_query = "
    SELECT 
        status,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM bookings WHERE created_at BETWEEN ? AND ?), 1) as percentage
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
    GROUP BY status
";
$stmt = $conn->prepare($booking_status_query);
$stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$booking_status = $stmt->get_result();

// Customer Demographics
$customer_demo_query = "
    SELECT 
        COUNT(DISTINCT c.id) as total_customers,
        ROUND(AVG(b.total_amount), 2) as avg_customer_spend,
        COUNT(b.id) / COUNT(DISTINCT c.id) as bookings_per_customer
    FROM customers c
    LEFT JOIN bookings b ON c.id = b.customer_id AND b.created_at BETWEEN ? AND ?
";
$stmt = $conn->prepare($customer_demo_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$customer_demo = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="src/output.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body class="bg-gray-50">
  <div class="flex">
    <?php include('components/sidebar.php'); ?>

    <div class="flex-1">
      <?php include('components/header.php'); ?>

      <main class="p-8">
        <div class="max-w-7xl mx-auto">
          <!-- Header -->
          <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Resort Performance Report</h1>

            <!-- Date Range Picker -->
            <div class="flex items-center gap-4">
              <form class="flex items-center gap-4" method="GET">
                <div class="flex items-center gap-2">
                  <input type="date" name="start_date" value="<?php echo $start_date; ?>"
                    class="px-3 py-2 border rounded-lg">
                  <span class="text-gray-500">to</span>
                  <input type="date" name="end_date" value="<?php echo $end_date; ?>"
                    class="px-3 py-2 border rounded-lg">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                  Apply
                </button>
              </form>
              <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Print Report
              </button>
            </div>
          </div>

          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
              <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
              <p class="text-2xl font-bold text-gray-900 mt-2">
                ₱<?php echo number_format($revenue_stats['total_revenue'], 2); ?>
              </p>
              <p class="text-sm text-gray-600 mt-1">
                <?php echo $revenue_stats['total_bookings']; ?> total bookings
              </p>
            </div>

            <!-- Average Booking Value -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
              <h3 class="text-sm font-medium text-gray-500">Average Booking Value</h3>
              <p class="text-2xl font-bold text-gray-900 mt-2">
                ₱<?php echo number_format($revenue_stats['average_booking_value'], 2); ?>
              </p>
              <p class="text-sm text-gray-600 mt-1">Per booking</p>
            </div>

            <!-- Completed Revenue -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
              <h3 class="text-sm font-medium text-gray-500">Completed Revenue</h3>
              <p class="text-2xl font-bold text-gray-900 mt-2">
                ₱<?php echo number_format($revenue_stats['completed_revenue'], 2); ?>
              </p>
              <p class="text-sm text-gray-600 mt-1">From completed bookings</p>
            </div>

            <!-- Customer Metrics -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
              <h3 class="text-sm font-medium text-gray-500">Customer Metrics</h3>
              <p class="text-2xl font-bold text-gray-900 mt-2">
                <?php echo $customer_demo['total_customers']; ?>
              </p>
              <p class="text-sm text-gray-600 mt-1">
                <?php echo number_format($customer_demo['bookings_per_customer'], 1); ?> bookings/customer
              </p>
            </div>
          </div>

          <!-- Charts Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Room Performance Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Room Performance</h3>
              <canvas id="roomPerformanceChart" height="300"></canvas>
            </div>

            <!-- Booking Status Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Booking Status Distribution</h3>
              <canvas id="bookingStatusChart" height="300"></canvas>
            </div>
          </div>

          <!-- Detailed Tables -->
          <div class="grid grid-cols-1 gap-6">
            <!-- Room Performance Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Detailed Room Performance</h3>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Room Name
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Bookings
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Revenue
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Booking %
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($room = $room_performance->fetch_assoc()): ?>
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($room['room_name']); ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo $room['booking_count']; ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ₱<?php echo number_format($room['revenue'], 2); ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo $room['booking_percentage']; ?>%
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
  // Initialize date pickers
  flatpickr("input[type=date]", {
    dateFormat: "Y-m-d"
  });

  // Room Performance Chart
  const roomCtx = document.getElementById('roomPerformanceChart').getContext('2d');
  new Chart(roomCtx, {
    type: 'bar',
    data: {
      labels: [<?php 
                $room_performance->data_seek(0);
                $labels = [];
                while ($room = $room_performance->fetch_assoc()) {
                    $labels[] = "'" . $room['room_name'] . "'";
                }
                echo implode(',', $labels);
            ?>],
      datasets: [{
        label: 'Bookings',
        data: [<?php 
                    $room_performance->data_seek(0);
                    $data = [];
                    while ($room = $room_performance->fetch_assoc()) {
                        $data[] = $room['booking_count'];
                    }
                    echo implode(',', $data);
                ?>],
        backgroundColor: '#60A5FA'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });

  // Booking Status Chart
  const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
  new Chart(statusCtx, {
    type: 'doughnut',
    data: {
      labels: [<?php 
                $labels = [];
                $data = [];
                $colors = [];
                while ($status = $booking_status->fetch_assoc()) {
                    $labels[] = "'" . ucfirst($status['status']) . "'";
                    $data[] = $status['count'];
                    switch($status['status']) {
                        case 'pending': $colors[] = "'#FCD34D'"; break;
                        case 'confirmed': $colors[] = "'#34D399'"; break;
                        case 'completed': $colors[] = "'#60A5FA'"; break;
                        case 'reschedule': $colors[] = "'#C084FC'"; break;
                    }
                }
                echo implode(',', $labels);
            ?>],
      datasets: [{
        data: [<?php echo implode(',', $data); ?>],
        backgroundColor: [<?php echo implode(',', $colors); ?>]
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }
  });
  </script>
</body>

</html>