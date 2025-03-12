<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index');
    exit();
}

// Handle feedback status updates
if (isset($_POST['feedback_id']) && isset($_POST['status'])) {
    $feedback_id = $_POST['feedback_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE feedbacks SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $feedback_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Feedback status updated successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error updating feedback status.";
    }
    
    header('Location: guest_feedback');
    exit();
}

// Get the current filter from URL parameter, default to 'all'
$current_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Fetch all feedbacks with customer details
$sql = "SELECT f.*, c.full_name, c.contact_number 
        FROM feedbacks f 
        JOIN customers c ON f.customer_id = c.id ";

// Add WHERE clause based on filter
if ($current_filter !== 'all') {
    $sql .= "WHERE f.status = '$current_filter' ";
}

$sql .= "ORDER BY f.created_at DESC";
$result = $conn->query($sql);

// Get feedback statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
    ROUND(AVG(rating), 1) as avg_rating
FROM feedbacks";
$stats = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Feedbacks - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="src/output.css" rel="stylesheet">
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
                        <h1 class="text-2xl font-bold text-gray-900">Guest Feedback Management</h1>
                        <div class="flex items-center space-x-4">
                            <div class="bg-white rounded-lg shadow px-4 py-2">
                                <span class="text-sm text-gray-500">Average Rating</span>
                                <div class="flex items-center mt-1">
                                    <span class="text-xl font-bold text-gray-900"><?php echo $stats['avg_rating']; ?></span>
                                    <svg class="w-5 h-5 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Total Feedback</h3>
                                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Pending</h3>
                                    <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                                </div>
                                <div class="p-3 bg-yellow-100 rounded-lg">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Approved</h3>
                                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['approved']; ?></p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Rejected</h3>
                                    <p class="text-3xl font-bold text-red-600"><?php echo $stats['rejected']; ?></p>
                                </div>
                                <div class="p-3 bg-red-100 rounded-lg">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback List -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-wrap gap-4 mb-6">
                                <a href="?filter=all" 
                                    class="px-4 py-2 rounded-lg transition-colors <?php echo $current_filter === 'all' ? 
                                        'bg-gray-100 text-gray-700 font-medium' : 
                                        'hover:bg-gray-100 text-gray-600'; ?>">
                                    All Feedbacks
                                </a>
                                <a href="?filter=pending" 
                                    class="px-4 py-2 rounded-lg transition-colors <?php echo $current_filter === 'pending' ? 
                                        'bg-yellow-100 text-yellow-700 font-medium' : 
                                        'hover:bg-yellow-100 text-gray-600'; ?>">
                                    Pending
                                </a>
                                <a href="?filter=approved" 
                                    class="px-4 py-2 rounded-lg transition-colors <?php echo $current_filter === 'approved' ? 
                                        'bg-green-100 text-green-700 font-medium' : 
                                        'hover:bg-green-100 text-gray-600'; ?>">
                                    Approved
                                </a>
                                <a href="?filter=rejected" 
                                    class="px-4 py-2 rounded-lg transition-colors <?php echo $current_filter === 'rejected' ? 
                                        'bg-red-100 text-red-700 font-medium' : 
                                        'hover:bg-red-100 text-gray-600'; ?>">
                                    Rejected
                                </a>
                            </div>

                            <?php if ($result->num_rows === 0): ?>
                            <div class="text-center py-8">
                                <p class="text-gray-500">No feedback found for this filter.</p>
                            </div>
                            <?php endif; ?>

                            <div class="space-y-6">
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="bg-gray-50 rounded-xl p-6 transition-all hover:shadow-md">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <div class="flex items-center gap-3">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                                </h3>
                                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                                    <?php echo $row['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                            ($row['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                            'bg-yellow-100 text-yellow-800'); ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <?php echo date('F j, Y g:i A', strtotime($row['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div class="flex items-center">
                                            <?php for($i = 0; $i < $row['rating']; $i++): ?>
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($row['message']); ?></p>

                                    <?php if ($row['status'] === 'pending'): ?>
                                    <div class="flex gap-3">
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="status" value="approved"
                                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="status" value="rejected"
                                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
    <script>
    Swal.fire({
        icon: '<?php echo $_SESSION['success'] ? 'success' : 'error'; ?>',
        title: '<?php echo $_SESSION['message']; ?>',
        showConfirmButton: false,
        timer: 2000,
        toast: true,
        position: 'top-end'
    });
    </script>
    <?php 
        unset($_SESSION['message']);
        unset($_SESSION['success']);
    endif; ?>
</body>

</html>