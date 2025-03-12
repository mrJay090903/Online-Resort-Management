<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index');
    exit();
}

// Mark notifications as read
if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE type IN ('reschedule_request', 'new_booking')");
    header('Location: notifications');
    exit();
}

// Get all notifications
$notifications_query = "
    SELECT n.*, b.booking_number 
    FROM notifications n
    LEFT JOIN bookings b ON CASE 
        WHEN n.message LIKE '%booking #%' THEN b.id = SUBSTRING_INDEX(SUBSTRING_INDEX(n.message, '#', -1), ' ', 1)
        WHEN n.message LIKE '%booking %' THEN b.booking_number = SUBSTRING_INDEX(SUBSTRING_INDEX(n.message, 'booking ', -1), ' ', 1)
        ELSE NULL
    END
    WHERE n.type IN ('reschedule_request', 'new_booking', 'payment_received', 'booking_status', 'booking_completed')
    ORDER BY n.created_at DESC
";
$notifications = $conn->query($notifications_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="src/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include('components/header.php'); ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
            <a href="?mark_read=1" class="text-sm text-emerald-600 hover:text-emerald-700">
                Mark all as read
            </a>
        </div>

        <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
            <?php if ($notifications->num_rows > 0):
                while ($notif = $notifications->fetch_assoc()): ?>
                    <div class="p-6 <?php echo $notif['is_read'] ? 'opacity-50' : ''; ?>">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <?php if ($notif['type'] === 'reschedule_request'): ?>
                                    <span class="p-2 bg-orange-100 rounded-full">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                <?php else: ?>
                                    <span class="p-2 bg-blue-100 rounded-full">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($notif['title']); ?>
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    <?php 
                                    // Replace the ID with booking number in the message
                                    $message = preg_replace(
                                        '/Booking ID: #(\d+)/', 
                                        'Booking Number: ' . $notif['booking_number'], 
                                        $notif['message']
                                    );
                                    echo nl2br(htmlspecialchars($message)); 
                                    ?>
                                </p>
                                <p class="mt-2 text-xs text-gray-400">
                                    <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="p-6 text-center text-gray-500">
                    No notifications found
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 