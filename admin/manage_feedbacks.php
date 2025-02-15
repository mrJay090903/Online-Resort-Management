<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
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
    
    header('Location: manage_feedbacks.php');
    exit();
}

// Fetch all feedbacks with customer details
$sql = "SELECT f.*, c.full_name, c.contact_number 
        FROM feedbacks f 
        JOIN customers c ON f.customer_id = c.id 
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedbacks - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
    <div class="flex">
        <?php include('components/sidebar.php'); ?>

        <div class="flex-1">
            <?php include('components/header.php'); ?>

            <main class="p-8">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-semibold text-gray-900">Customer Feedbacks</h1>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                            <div class="text-gray-500"><?php echo htmlspecialchars($row['contact_number']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo str_repeat('⭐', $row['rating']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $row['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                    ($row['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                    'bg-yellow-100 text-yellow-800'); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                            <?php if ($row['status'] === 'pending'): ?>
                                            <button type="submit" name="status" value="approved" 
                                                class="text-green-600 hover:text-green-900 mr-2">
                                                Approve
                                            </button>
                                            <button type="submit" name="status" value="rejected" 
                                                class="text-red-600 hover:text-red-900">
                                                Reject
                                            </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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