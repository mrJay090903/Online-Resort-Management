<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/database.php';
require_once(__DIR__ . '/../vendor/autoload.php');

// Check if user is logged in and has appropriate access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'staff'])) {
    header('Location: ../index.php');
    exit();
}

// Get date range from request, default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Revenue Statistics
$revenue_query = "
    SELECT 
        COALESCE(SUM(total_amount), 0) as total_revenue,
        COUNT(*) as total_bookings,
        COALESCE(AVG(total_amount), 0) as average_booking_value,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) as completed_revenue,
        COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END), 0) as pending_revenue,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings
    FROM bookings 
    WHERE created_at BETWEEN ? AND ?
";
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$revenue_stats = $stmt->get_result()->fetch_assoc();

// Room Performance with Revenue
$room_performance_query = "
    SELECT 
        r.room_name,
        r.base_price,
        COUNT(DISTINCT b.id) as booking_count,
        COALESCE(SUM(b.total_amount), 0) as revenue,
        COUNT(DISTINCT CASE WHEN b.status = 'completed' THEN b.id END) as completed_bookings,
        COUNT(DISTINCT CASE WHEN b.status = 'cancelled' THEN b.id END) as cancelled_bookings,
        COALESCE(AVG(b.total_amount), 0) as average_revenue_per_booking,
        COALESCE(
            ROUND(
                COUNT(DISTINCT CASE WHEN b.status = 'completed' THEN b.id END) * 100.0 / 
                NULLIF(COUNT(DISTINCT b.id), 0), 
                1
            ), 
            0
        ) as completion_rate,
        (
            SELECT COUNT(DISTINCT bb.id) 
            FROM bookings bb 
            JOIN booking_rooms br2 ON bb.id = br2.booking_id 
            WHERE br2.room_id = r.id 
            AND bb.status = 'completed'
            AND bb.created_at BETWEEN ? AND ?
        ) as total_completed_bookings
    FROM rooms r
    LEFT JOIN booking_rooms br ON r.id = br.room_id
    LEFT JOIN bookings b ON br.booking_id = b.id 
        AND b.created_at BETWEEN ? AND ?
    GROUP BY r.id, r.room_name, r.base_price
    ORDER BY revenue DESC
";

$stmt = $conn->prepare($room_performance_query);
$stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$room_performance = $stmt->get_result();

// Debug room performance data
if ($room_performance->num_rows === 0) {
    error_log("No room performance data found for date range: $start_date to $end_date");
}

// Verify data is present before rendering chart
$has_room_data = false;
$room_performance->data_seek(0);
while ($room = $room_performance->fetch_assoc()) {
    if ($room['booking_count'] > 0 || $room['revenue'] > 0) {
        $has_room_data = true;
        break;
    }
}

// If no data, show a message instead of empty chart
if (!$has_room_data) {
    echo '<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        No booking data available for the selected date range.
                    </p>
                </div>
            </div>
          </div>';
}

// Daily Revenue Trend
$daily_revenue_query = "
    SELECT 
        DATE(created_at) as booking_date,
        COUNT(*) as total_bookings,
        COALESCE(SUM(total_amount), 0) as daily_revenue,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY booking_date
";
$stmt = $conn->prepare($daily_revenue_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_revenue = $stmt->get_result();

// Customer Demographics
$customer_demo_query = "
    SELECT 
        COUNT(DISTINCT c.id) as total_customers,
        COALESCE(ROUND(AVG(b.total_amount), 2), 0) as avg_customer_spend,
        COALESCE(COUNT(b.id) / NULLIF(COUNT(DISTINCT c.id), 0), 0) as bookings_per_customer,
        COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as repeat_customers
    FROM customers c
    LEFT JOIN bookings b ON c.id = b.customer_id AND b.created_at BETWEEN ? AND ?
";
$stmt = $conn->prepare($customer_demo_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$customer_demo = $stmt->get_result()->fetch_assoc();

// Booking Status Distribution
$booking_status_query = "
    SELECT 
        status,
        COUNT(*) as count,
        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM bookings WHERE created_at BETWEEN ? AND ?)), 1) as percentage
    FROM bookings 
    WHERE created_at BETWEEN ? AND ?
    GROUP BY status
";
$stmt = $conn->prepare($booking_status_query);
$stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$booking_status = $stmt->get_result();

// Payment Method Statistics
$payment_stats_query = "
    SELECT 
        payment_status as payment_method,
        COUNT(*) as count,
        SUM(total_amount) as total_amount,
        ROUND(AVG(total_amount), 2) as average_amount
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
        AND payment_status IS NOT NULL
    GROUP BY payment_status
";
$stmt = $conn->prepare($payment_stats_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$payment_stats = $stmt->get_result();

// Peak Booking Times
$peak_times_query = "
    SELECT 
        HOUR(created_at) as booking_hour,
        COUNT(*) as booking_count,
        ROUND(COUNT(*) * 100.0 / (
            SELECT COUNT(*) 
            FROM bookings 
            WHERE created_at BETWEEN ? AND ?
        ), 1) as percentage
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
    GROUP BY HOUR(created_at)
    ORDER BY booking_count DESC
";
$stmt = $conn->prepare($peak_times_query);
$stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$peak_times = $stmt->get_result();

// Booking Lead Time Analysis
$lead_time_query = "
    SELECT 
        CASE 
            WHEN DATEDIFF(check_in_date, created_at) <= 1 THEN 'Same day/Next day'
            WHEN DATEDIFF(check_in_date, created_at) <= 7 THEN 'Within a week'
            WHEN DATEDIFF(check_in_date, created_at) <= 30 THEN 'Within a month'
            ELSE 'More than a month'
        END as lead_time,
        COUNT(*) as booking_count,
        ROUND(AVG(total_amount), 2) as avg_booking_value
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
    GROUP BY 
        CASE 
            WHEN DATEDIFF(check_in_date, created_at) <= 1 THEN 'Same day/Next day'
            WHEN DATEDIFF(check_in_date, created_at) <= 7 THEN 'Within a week'
            WHEN DATEDIFF(check_in_date, created_at) <= 30 THEN 'Within a month'
            ELSE 'More than a month'
        END
    ORDER BY booking_count DESC
";
$stmt = $conn->prepare($lead_time_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$lead_time_analysis = $stmt->get_result();

// Length of Stay Analysis
$stay_duration_query = "
    SELECT 
        CASE 
            WHEN DATEDIFF(check_out_date, check_in_date) = 1 THEN '1 night'
            WHEN DATEDIFF(check_out_date, check_in_date) <= 3 THEN '2-3 nights'
            WHEN DATEDIFF(check_out_date, check_in_date) <= 7 THEN '4-7 nights'
            ELSE '8+ nights'
        END as stay_duration,
        COUNT(*) as booking_count,
        ROUND(AVG(total_amount), 2) as avg_revenue,
        ROUND(AVG(total_amount / DATEDIFF(check_out_date, check_in_date)), 2) as avg_daily_rate
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
    GROUP BY 
        CASE 
            WHEN DATEDIFF(check_out_date, check_in_date) = 1 THEN '1 night'
            WHEN DATEDIFF(check_out_date, check_in_date) <= 3 THEN '2-3 nights'
            WHEN DATEDIFF(check_out_date, check_in_date) <= 7 THEN '4-7 nights'
            ELSE '8+ nights'
        END
    ORDER BY booking_count DESC
";
$stmt = $conn->prepare($stay_duration_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$stay_duration_analysis = $stmt->get_result();

// Generate PDF Report
if (isset($_GET['export_pdf'])) {
    $mpdf = new \Mpdf\Mpdf([
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'default_font_size' => 10
    ]);

    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; color: #333; margin-bottom: 30px; }
        .header h1 { color: #10B981; font-size: 24px; margin-bottom: 5px; }
        .section { margin-bottom: 30px; }
        .section-title { 
            color: #1f2937; 
            font-size: 16px; 
            font-weight: bold; 
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #10B981;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            font-size: 9pt;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f3f4f6;
            color: #4B5563;
            font-weight: bold;
        }
        .text-right { text-align: right; }
    </style>

    <div class="header">
        <h1>CASITA DE GRANDS</h1>
        <h2>Performance Report</h2>
        <p>'.date('F d, Y', strtotime($start_date)).' - '.date('F d, Y', strtotime($end_date)).'</p>
    </div>

    <div class="section">
        <div class="section-title">Revenue Summary</div>
        <table>
            <tr>
                <th>Total Revenue</th>
                <td class="text-right">₱'.number_format($revenue_stats['total_revenue'], 2).'</td>
                <th>Total Bookings</th>
                <td class="text-right">'.$revenue_stats['total_bookings'].'</td>
            </tr>
            <tr>
                <th>Completed Revenue</th>
                <td class="text-right">₱'.number_format($revenue_stats['completed_revenue'], 2).'</td>
                <th>Completed Bookings</th>
                <td class="text-right">'.$revenue_stats['completed_bookings'].'</td>
            </tr>
            <tr>
                <th>Average Booking Value</th>
                <td class="text-right">₱'.number_format($revenue_stats['average_booking_value'], 2).'</td>
                <th>Cancelled Bookings</th>
                <td class="text-right">'.$revenue_stats['cancelled_bookings'].'</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Booking Status</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Percentage</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>';

    $booking_status->data_seek(0);
    while ($status = $booking_status->fetch_assoc()) {
        $html .= '
            <tr>
                <td>'.ucfirst($status['status']).'</td>
                <td class="text-right">'.$status['count'].'</td>
                <td class="text-right">'.$status['percentage'].'%</td>
                <td class="text-right">₱'.number_format($status['revenue'] ?? 0, 2).'</td>
            </tr>';
    }

    $html .= '
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Customer Summary</div>
        <table>
            <tr>
                <th>Total Customers</th>
                <td class="text-right">'.$customer_demo['total_customers'].'</td>
                <th>Average Spend</th>
                <td class="text-right">₱'.number_format($customer_demo['avg_customer_spend'], 2).'</td>
            </tr>
            <tr>
                <th>Bookings per Customer</th>
                <td class="text-right">'.number_format($customer_demo['bookings_per_customer'], 1).'</td>
                <th>Repeat Customers</th>
                <td class="text-right">'.$customer_demo['repeat_customers'].'</td>
            </tr>
        </table>
    </div>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('Casita_de_grands_Report_'.date('Y-m-d').'.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link href="../src/output.css" rel="stylesheet">
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
                <!-- Date Range Filter -->
                <div class="mb-8 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>"
                            class="border-gray-300 rounded-md shadow-sm">
                        <span>to</span>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>"
                            class="border-gray-300 rounded-md shadow-sm">
                        <button onclick="updateDateRange()"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                            Apply
                        </button>
                    </div>
                    
                    <a href="?export_pdf=1&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Export PDF
                    </a>
                </div>

                <!-- Revenue Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Revenue</h3>
                        <p class="text-2xl font-bold text-gray-900">₱<?php echo number_format($revenue_stats['total_revenue'], 2); ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            From <?php echo $revenue_stats['total_bookings']; ?> bookings
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Completed Revenue</h3>
                        <p class="text-2xl font-bold text-gray-900">₱<?php echo number_format($revenue_stats['completed_revenue'], 2); ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            From <?php echo $revenue_stats['completed_bookings']; ?> completed bookings
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Average Booking Value</h3>
                        <p class="text-2xl font-bold text-gray-900">₱<?php echo number_format($revenue_stats['average_booking_value'], 2); ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            Per booking average
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Booking Success Rate</h3>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php 
                            $success_rate = $revenue_stats['total_bookings'] > 0 ? 
                                round(($revenue_stats['completed_bookings'] / $revenue_stats['total_bookings']) * 100, 1) : 0;
                            echo $success_rate . '%';
                            ?>
                        </p>
                        <div class="mt-2 text-xs text-gray-500">
                            Completion rate
                        </div>
                    </div>
                </div>

                <!-- Booking Status Distribution -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Booking Status Distribution</h3>
                        <div class="space-y-4">
                            <?php 
                            $booking_status->data_seek(0);
                            while ($status = $booking_status->fetch_assoc()):
                                $color = match($status['status']) {
                                    'completed' => 'bg-green-500',
                                    'confirmed' => 'bg-blue-500',
                                    'pending' => 'bg-yellow-500',
                                    'cancelled' => 'bg-red-500',
                                    default => 'bg-gray-500'
                                };
                            ?>
                            <div>
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span class="capitalize"><?php echo $status['status']; ?></span>
                                    <span><?php echo $status['count']; ?> (<?php echo $status['percentage']; ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="<?php echo $color; ?> h-2 rounded-full" style="width: <?php echo $status['percentage']; ?>%"></div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Status</h3>
                        <div class="space-y-4">
                            <?php 
                            $payment_stats->data_seek(0);
                            while ($payment = $payment_stats->fetch_assoc()):
                                $status_color = match($payment['payment_method']) {
                                    'paid' => 'text-green-600',
                                    'pending' => 'text-yellow-600',
                                    'cancelled' => 'text-red-600',
                                    default => 'text-gray-600'
                                };
                            ?>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-900 capitalize"><?php echo $payment['payment_method']; ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $payment['count']; ?> bookings</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium <?php echo $status_color; ?>">₱<?php echo number_format($payment['total_amount'], 2); ?></p>
                                    <p class="text-sm text-gray-500">avg: ₱<?php echo number_format($payment['average_amount'], 2); ?></p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Additional Customer Insights -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer Insights</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Total Customers</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $customer_demo['total_customers']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Average Spend</p>
                            <p class="text-2xl font-bold text-gray-900">₱<?php echo number_format($customer_demo['avg_customer_spend'], 2); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Bookings per Customer</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($customer_demo['bookings_per_customer'], 1); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Repeat Customers</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $customer_demo['repeat_customers']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Room Performance Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Room Performance</h3>
                        <canvas id="roomPerformanceChart" height="300"></canvas>
                    </div>

                    <!-- Daily Revenue Trend -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Revenue Trend</h3>
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Booking Patterns Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Peak Booking Times -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Peak Booking Hours</h3>
                        <div class="space-y-4">
                            <?php while ($time = $peak_times->fetch_assoc()): ?>
                            <div>
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span><?php echo sprintf('%02d:00 - %02d:00', $time['booking_hour'], ($time['booking_hour'] + 1) % 24); ?></span>
                                    <span><?php echo $time['booking_count']; ?> bookings (<?php echo $time['percentage']; ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $time['percentage']; ?>%"></div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Booking Lead Time -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Booking Lead Time</h3>
                        <div class="space-y-6">
                            <?php while ($lead = $lead_time_analysis->fetch_assoc()): ?>
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-900"><?php echo $lead['lead_time']; ?></span>
                                    <span class="text-sm text-gray-500"><?php echo $lead['booking_count']; ?> bookings</span>
                                </div>
                                <p class="text-xs text-gray-500">Average booking value: ₱<?php echo number_format($lead['avg_booking_value'], 2); ?></p>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Length of Stay Analysis -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Length of Stay Analysis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <?php while ($stay = $stay_duration_analysis->fetch_assoc()): ?>
                        <div class="p-4 border rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900"><?php echo $stay['stay_duration']; ?></h4>
                            <p class="text-sm text-gray-500 mt-1"><?php echo $stay['booking_count']; ?> bookings</p>
                            <div class="mt-2">
                                <p class="text-xs text-gray-500">Avg. Revenue: ₱<?php echo number_format($stay['avg_revenue'], 2); ?></p>
                                <p class="text-xs text-gray-500">Avg. Daily Rate: ₱<?php echo number_format($stay['avg_daily_rate'], 2); ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
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

    function updateDateRange() {
        const start_date = document.getElementById('start_date').value;
        const end_date = document.getElementById('end_date').value;
        window.location.href = `?start_date=${start_date}&end_date=${end_date}`;
    }

    // Room Performance Chart
    const roomCtx = document.getElementById('roomPerformanceChart').getContext('2d');
    <?php if ($has_room_data): ?>
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
                label: 'Total Revenue',
                data: [<?php 
                    $room_performance->data_seek(0);
                    $data = [];
                    while ($room = $room_performance->fetch_assoc()) {
                        $data[] = $room['revenue'];
                    }
                    echo implode(',', $data);
                ?>],
                backgroundColor: '#60A5FA',
                order: 2,
                yAxisID: 'y',
                barPercentage: 0.6
            },
            {
                label: 'Average Daily Rate',
                data: [<?php 
                    $room_performance->data_seek(0);
                    $avgData = [];
                    while ($room = $room_performance->fetch_assoc()) {
                        // Calculate average daily rate
                        $avgData[] = $room['booking_count'] > 0 ? 
                            round($room['revenue'] / $room['booking_count'], 2) : 0;
                    }
                    echo implode(',', $avgData);
                ?>],
                backgroundColor: '#34D399',
                order: 1,
                yAxisID: 'y',
                barPercentage: 0.6
            },
            {
                label: 'Occupancy Rate (%)',
                data: [<?php 
                    $room_performance->data_seek(0);
                    $occupancyData = [];
                    while ($room = $room_performance->fetch_assoc()) {
                        // Calculate occupancy rate based on completed bookings
                        $totalDays = ceil((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24));
                        $occupancyRate = ($room['completed_bookings'] / $totalDays) * 100;
                        $occupancyData[] = round($occupancyRate, 1);
                    }
                    echo implode(',', $occupancyData);
                ?>],
                type: 'line',
                borderColor: '#F59E0B',
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointStyle: 'circle',
                pointRadius: 4,
                pointHoverRadius: 6,
                order: 0,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue (₱)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    grid: {
                        borderDash: [2, 4]
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Occupancy Rate (%)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    min: 0,
                    max: 100,
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'start',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1F2937',
                    bodyColor: '#1F2937',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y') {
                                label += '₱' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y + '%';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    <?php else: ?>
    // Display a message on the canvas when no data is available
    const noDataMsg = new Chart(roomCtx, {
        type: 'bar',
        data: {
            labels: ['No Data Available'],
            datasets: [{
                data: [0],
                backgroundColor: '#E5E7EB'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'No booking data available for the selected date range',
                    font: {
                        size: 14
                    }
                }
            },
            scales: {
                y: {
                    display: false
                }
            }
        }
    });
    <?php endif; ?>

    // Daily Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: [<?php 
                $daily_revenue->data_seek(0);
                $dates = [];
                while ($day = $daily_revenue->fetch_assoc()) {
                    $dates[] = "'" . date('M d', strtotime($day['booking_date'])) . "'";
                }
                echo implode(',', $dates);
            ?>],
            datasets: [{
                label: 'Daily Revenue',
                data: [<?php 
                    $daily_revenue->data_seek(0);
                    $revenues = [];
                    while ($day = $daily_revenue->fetch_assoc()) {
                        $revenues[] = $day['daily_revenue'];
                    }
                    echo implode(',', $revenues);
                ?>],
                borderColor: '#10B981',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
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