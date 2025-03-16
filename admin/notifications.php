<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has appropriate access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'staff'])) {
    header('Location: ../index.php');
    exit();
}

// Mark notifications as read
if (isset($_GET['mark_read'])) {
    if ($_SESSION['user_type'] === 'admin') {
        $conn->query("UPDATE notifications SET is_read = 1 WHERE type IN ('reschedule_request', 'new_booking')");
    } else {
        $conn->query("UPDATE notifications SET is_read = 1 WHERE type IN ('new_booking')");
    }
    header('Location: notifications');
    exit();
}

// Get all notifications based on user type
if ($_SESSION['user_type'] === 'admin') {
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
} else {
    $notifications_query = "
        SELECT n.*, b.booking_number 
        FROM notifications n
        LEFT JOIN bookings b ON CASE 
            WHEN n.message LIKE '%booking #%' THEN b.id = SUBSTRING_INDEX(SUBSTRING_INDEX(n.message, '#', -1), ' ', 1)
            WHEN n.message LIKE '%booking %' THEN b.booking_number = SUBSTRING_INDEX(SUBSTRING_INDEX(n.message, 'booking ', -1), ' ', 1)
            ELSE NULL
        END
        WHERE n.type IN ('new_booking')
        ORDER BY n.created_at DESC
    ";
}

$notifications = $conn->query($notifications_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo ucfirst($_SESSION['user_type']); ?> Dashboard</title>
    <link href="../src/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php include('components/sidebar.php'); ?>
        
        <div class="flex-1">
            <?php include('components/header.php'); ?>
            
            <main class="p-8">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-800">Notifications</h2>
                            <a href="?mark_read=1" class="text-sm text-emerald-600 hover:text-emerald-700">
                                Mark all as read
                            </a>
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            <?php if ($notifications->num_rows > 0):
                                while ($notif = $notifications->fetch_assoc()): ?>
                                <div class="p-4 <?php echo $notif['is_read'] ? 'bg-white' : 'bg-emerald-50'; ?>">
                                    <div class="flex items-start">
                                        <span class="material-symbols-outlined text-emerald-500 mr-3">
                                            <?php 
                                            switch($notif['type']) {
                                                case 'new_booking': echo 'event_available'; break;
                                                case 'reschedule_request': echo 'schedule'; break;
                                                case 'payment_received': echo 'payments'; break;
                                                case 'booking_completed': echo 'check_circle'; break;
                                                default: echo 'notifications';
                                            }
                                            ?>
                                        </span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">
                                                <?php echo htmlspecialchars($notif['title']); ?>
                                            </p>
                                            <p class="text-gray-600 mt-1">
                                                <?php 
                                                $message = preg_replace(
                                                    '/Booking ID: #(\d+)/', 
                                                    'Booking Number: ' . $notif['booking_number'], 
                                                    $notif['message']
                                                );
                                                echo nl2br(htmlspecialchars($message)); 
                                                ?>
                                            </p>
                                            <p class="text-sm text-gray-500 mt-2">
                                                <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile;
                            else: ?>
                                <div class="p-8 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2">notifications_off</span>
                                    <p>No notifications found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 