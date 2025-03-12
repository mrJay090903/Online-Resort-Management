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

// Get monthly revenue (only from completed bookings)
$revenue_query = "
    SELECT 
        MONTH(check_in_date) as month,
        YEAR(check_in_date) as year,
        SUM(total_amount) as total
    FROM bookings 
    WHERE status = 'completed' 
    AND YEAR(check_in_date) = YEAR(CURRENT_DATE)
    GROUP BY MONTH(check_in_date), YEAR(check_in_date)
    ORDER BY YEAR(check_in_date), MONTH(check_in_date)
";

$revenue_result = $conn->query($revenue_query);
$monthly_revenue = array_fill(0, 12, 0); // Initialize all months to 0

while ($row = $revenue_result->fetch_assoc()) {
    $month_index = intval($row['month']) - 1; // Convert to 0-based index
    $monthly_revenue[$month_index] = floatval($row['total']);
}

// Get total revenue for current year (only from completed bookings)
$total_revenue_query = "
    SELECT SUM(total_amount) as total 
    FROM bookings 
    WHERE status = 'completed'
    AND YEAR(check_in_date) = YEAR(CURRENT_DATE)
";

$total_result = $conn->query($total_revenue_query);
$total_revenue = $total_result->fetch_assoc()['total'] ?? 0;

// Get booking statistics
$booking_stats_query = "
    SELECT 
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_count,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
        COUNT(CASE WHEN status = 'reschedule' THEN 1 END) as reschedule_count
    FROM bookings
    WHERE YEAR(check_in_date) = YEAR(CURRENT_DATE)
";

$stats_result = $conn->query($booking_stats_query);
$booking_stats = $stats_result->fetch_assoc();

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

// Add new queries for enhanced statistics
$daily_revenue_query = "
    SELECT 
        DATE(check_in_date) as date,
        SUM(total_amount) as total
    FROM bookings 
    WHERE status = 'completed'
    AND check_in_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    GROUP BY DATE(check_in_date)
    ORDER BY date DESC
";
$daily_revenue = $conn->query($daily_revenue_query);

// Get customer demographics
$customer_stats_query = "
    SELECT 
        COUNT(DISTINCT c.id) as total_customers,
        COUNT(DISTINCT b.id) as total_bookings,
        ROUND(COUNT(DISTINCT b.id) / COUNT(DISTINCT c.id), 2) as booking_per_customer
    FROM customers c
    LEFT JOIN bookings b ON c.id = b.customer_id
";
$customer_stats = $conn->query($customer_stats_query)->fetch_assoc();

// Get recent feedback
$recent_feedback_query = "
    SELECT 
        f.id,
        f.message,
        f.rating,
        f.created_at,
        c.full_name as customer_name
    FROM feedbacks f
    JOIN customers c ON f.customer_id = c.id
    ORDER BY f.created_at DESC
    LIMIT 5
";
$recent_feedback = $conn->query($recent_feedback_query);

// Get available rooms with details
$available_rooms_query = "
    SELECT 
        r.id,
        r.room_name,
        r.price,
        r.status,
        r.description,
        r.capacity
    FROM rooms r
    WHERE r.status = 'available'
    ORDER BY r.room_name
";
$available_rooms = $conn->query($available_rooms_query);

// Get available venues with details
$available_venues_query = "
    SELECT 
        v.id,
        v.name,
        v.price,
        v.status,
        v.description,
        v.capacity
    FROM venues v
    WHERE v.status = 'available'
    ORDER BY v.name
";
$available_venues = $conn->query($available_venues_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Casita de Grands</title>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="src/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <script src="https://cdn.lordicon.com/bhenfmcm.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <style>
  [x-cloak] {
    display: none !important;
  }
  /* Custom Scrollbar */
  .scrollbar-thin::-webkit-scrollbar {
    width: 6px;
  }
  
  .scrollbar-thin::-webkit-scrollbar-track {
    background: #f7f7f7;
    border-radius: 3px;
  }
  
  .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 3px;
  }
  
  .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
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

          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-emerald-500">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Total Revenue (YTD)</p>
                  <h3 class="text-2xl font-bold text-gray-800">₱<?php echo number_format($total_revenue, 2); ?></h3>
                  <p class="text-xs text-gray-400 mt-1">From completed bookings</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-lg">
                  <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Booking Statistics Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Active Bookings</p>
                  <h3 class="text-2xl font-bold text-gray-800"><?php echo $booking_stats['confirmed_count']; ?></h3>
                  <p class="text-xs text-gray-400 mt-1">Currently confirmed</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                  <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Customer Stats Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Total Customers</p>
                  <h3 class="text-2xl font-bold text-gray-800"><?php echo $customer_stats['total_customers']; ?></h3>
                  <p class="text-xs text-gray-400 mt-1">Avg <?php echo $customer_stats['booking_per_customer']; ?>
                    bookings/customer</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg">
                  <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Room Occupancy Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Available Rooms</p>
                  <h3 class="text-2xl font-bold text-gray-800"><?php echo $available_rooms_count; ?></h3>
                  <p class="text-xs text-gray-400 mt-1">Ready for booking</p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg">
                  <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Revenue Trend -->
            <div class="bg-white rounded-xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Revenue Trend</h3>
                <div class="text-sm text-gray-500">Monthly</div>
              </div>
              <canvas id="revenueChart" height="250"></canvas>
            </div>

            <!-- Booking Status Distribution -->
            <div class="bg-white rounded-xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Booking Status</h3>
                <div class="text-sm text-gray-500">Current Year</div>
              </div>
              <canvas id="bookingStatusChart" height="250"></canvas>
            </div>

            <!-- Daily Revenue Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Daily Revenue</h3>
                <div class="text-sm text-gray-500">Last 7 Days</div>
              </div>
              <canvas id="dailyRevenueChart" height="250"></canvas>
            </div>
          </div>

          <!-- Recent Activity and Stats -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Bookings</h3>
                <a href="bookings.php" class="text-sm text-emerald-600 hover:text-emerald-700">View All</a>
              </div>
              <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-gray-50">
                <?php 
                $count = 0;
                while ($booking = $recent_bookings->fetch_assoc()): 
                    if ($count >= 3) echo '<div class="border-t border-gray-100 pt-2 mt-2"></div>';
                ?>
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
                            case 'reschedule': echo 'bg-purple-100 text-orange-800'; break;
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
                <?php 
                    $count++;
                endwhile; 
                ?>
              </div>
            </div>

            <!-- Popular Rooms -->
            <div class="bg-white rounded-xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Popular Rooms</h3>
                <a href="rooms.php" class="text-sm text-emerald-600 hover:text-emerald-700">View All</a>
              </div>
              <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-gray-50">
                <?php 
                $count = 0;
                while ($room = $popular_rooms->fetch_assoc()): 
                    if ($count >= 3) echo '<div class="border-t border-gray-100 pt-2 mt-2"></div>';
                ?>
                <div class="flex justify-between items-center">
                  <div>
                    <h3 class="font-medium"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                    <p class="text-sm text-gray-600"><?php echo $room['booking_count']; ?> bookings</p>
                  </div>
                  <span class="text-sm font-medium text-gray-900">₱<?php echo number_format($room['price']); ?></span>
                </div>
                <?php 
                    $count++;
                endwhile; 
                ?>
              </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
                <div class="text-sm text-gray-500">Last 10 Activities</div>
              </div>
              <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-gray-50">
                <?php 
                $count = 0;
                while ($activity = $activities->fetch_assoc()): 
                    if ($count >= 3) echo '<div class="border-t border-gray-100 pt-2 mt-2"></div>';
                ?>
                <div class="border-b border-gray-200 pb-3">
                  <div class="flex justify-between items-start">
                    <div>
                      <h3 class="font-medium"><?php echo htmlspecialchars($activity['user']); ?></h3>
                      <p class="text-sm text-gray-600"><?php echo htmlspecialchars($activity['details']); ?></p>
                    </div>
                    <div class="text-right">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?php
                          switch($activity['status']) {
                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                            case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                            case 'reschedule': echo 'bg-purple-100 text-purple-800'; break;
                            case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                          }
                        ?>">
                        <?php echo ucfirst($activity['status']); ?>
                      </span>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">
                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                  </p>
                </div>
                <?php 
                    $count++;
                endwhile; 
                ?>
              </div>
            </div>

            <!-- Customer Feedback -->
            <div class="bg-white rounded-xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Customer Feedback</h3>
                <a href="guest_feedback.php" class="text-sm text-emerald-600 hover:text-emerald-700">View All</a>
              </div>
              <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-gray-50">
                <?php 
                $count = 0;
                while ($feedback = $recent_feedback->fetch_assoc()): 
                    if ($count >= 3) echo '<div class="border-t border-gray-100 pt-2 mt-2"></div>';
                ?>
                <div class="border-b border-gray-200 pb-3">
                  <div class="flex justify-between items-start">
                    <div>
                      <h3 class="font-medium"><?php echo htmlspecialchars($feedback['customer_name']); ?></h3>
                      <p class="text-sm text-gray-600"><?php echo htmlspecialchars($feedback['message']); ?></p>
                    </div>
                    <div class="text-right">
                      <div class="flex items-center">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                        <svg
                          class="w-4 h-4 <?php echo $i <= $feedback['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"
                          fill="currentColor" viewBox="0 0 20 20">
                          <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <?php endfor; ?>
                      </div>
                      <p class="text-xs text-gray-500 mt-1">
                        <?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?>
                      </p>
                    </div>
                  </div>
                </div>
                <?php 
                    $count++;
                endwhile; 
                ?>
                <?php if ($recent_feedback->num_rows === 0): ?>
                <div class="text-center text-gray-500 py-4">
                  <p>No feedback available</p>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Available Rooms and Venues -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <!-- Available Rooms -->
              <div class="bg-white rounded-xl shadow-lg p-6">
                  <div class="flex items-center justify-between mb-4">
                      <h3 class="text-lg font-semibold text-gray-800">Available Rooms</h3>
                      <a href="rooms.php" class="text-sm text-emerald-600 hover:text-emerald-700">Manage Rooms</a>
                  </div>
                  <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-gray-50">
                      <?php if ($available_rooms->num_rows === 0): ?>
                      <div class="text-center text-gray-500 py-4">
                          <p>No rooms available at the moment</p>
                      </div>
                      <?php else: ?>
                          <?php while ($room = $available_rooms->fetch_assoc()): ?>
                          <div class="border-b border-gray-200 pb-4">
                              <div class="flex justify-between items-start">
                                  <div>
                                      <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($room['room_name']); ?></h4>
                                      <p class="text-sm text-gray-600 mt-1">Capacity: <?php echo $room['capacity']; ?> persons</p>
                                      <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?php echo htmlspecialchars($room['description']); ?></p>
                                  </div>
                                  <div class="text-right">
                                      <span class="text-lg font-semibold text-gray-900">₱<?php echo number_format($room['price'], 2); ?></span>
                                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                          Available
                                      </span>
                                  </div>
                              </div>
                          </div>
                          <?php endwhile; ?>
                      <?php endif; ?>
                  </div>
              </div>

              <!-- Available Venues -->
              <div class="bg-white rounded-xl shadow-lg p-6">
                  <div class="flex items-center justify-between mb-4">
                      <h3 class="text-lg font-semibold text-gray-800">Available Venues</h3>
                      <a href="venues.php" class="text-sm text-emerald-600 hover:text-emerald-700">Manage Venues</a>
                  </div>
                  <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-gray-50">
                      <?php if ($available_venues->num_rows === 0): ?>
                      <div class="text-center text-gray-500 py-4">
                          <p>No venues available at the moment</p>
                      </div>
                      <?php else: ?>
                          <?php while ($venue = $available_venues->fetch_assoc()): ?>
                          <div class="border-b border-gray-200 pb-4">
                              <div class="flex justify-between items-start">
                                  <div>
                                      <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($venue['name']); ?></h4>
                                      <p class="text-sm text-gray-600 mt-1">Capacity: <?php echo $venue['capacity']; ?> persons</p>
                                      <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?php echo htmlspecialchars($venue['description']); ?></p>
                                  </div>
                                  <div class="text-right">
                                      <span class="text-lg font-semibold text-gray-900">₱<?php echo number_format($venue['price'], 2); ?></span>
                                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                          Available
                                      </span>
                                  </div>
                              </div>
                          </div>
                          <?php endwhile; ?>
                      <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
  // Revenue Trend Chart (existing)
  const revenueChart = new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
      labels: <?php echo json_encode(array_map(function($m) { return date('M', mktime(0, 0, 0, $m + 1, 1)); }, array_keys($monthly_revenue))); ?>,
      datasets: [{
        label: 'Monthly Revenue',
        data: <?php echo json_encode(array_values($monthly_revenue)); ?>,
        borderColor: '#059669',
        tension: 0.4,
        fill: false
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top'
        }
      }
    }
  });

  // Booking Status Chart
  const bookingStatusChart = new Chart(document.getElementById('bookingStatusChart'), {
    type: 'doughnut',
    data: {
      labels: ['Pending', 'Confirmed', 'Completed', 'Reschedule'],
      datasets: [{
        data: [
          <?php echo $booking_stats['pending_count']; ?>,
          <?php echo $booking_stats['confirmed_count']; ?>,
          <?php echo $booking_stats['completed_count']; ?>,
          <?php echo $booking_stats['reschedule_count']; ?>
        ],
        backgroundColor: [
          '#FCD34D', // yellow for pending
          '#34D399', // green for confirmed
          '#60A5FA', // blue for completed
          '#C084FC' // purple for reschedule
        ]
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

  // Daily Revenue Chart
  const dailyRevenueChart = new Chart(document.getElementById('dailyRevenueChart'), {
    type: 'bar',
    data: {
      labels: [
        <?php 
        $daily_labels = [];
        $daily_data = [];
        while ($day = $daily_revenue->fetch_assoc()) {
          $daily_labels[] = date('M d', strtotime($day['date']));
          $daily_data[] = $day['total'];
        }
        echo "'" . implode("','", $daily_labels) . "'";
        ?>
      ],
      datasets: [{
        label: 'Daily Revenue',
        data: [<?php echo implode(',', $daily_data); ?>],
        backgroundColor: '#EC4899',
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top'
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
  </script>
</body>

</html>