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
    
    header('Location: guest_feedback.php');
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
  <title>Guest Feedbacks - Admin Dashboard</title>
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
            <h1 class="text-2xl font-semibold text-gray-900">Guest Feedbacks</h1>
          </div>

          <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
              <!-- Feedback Status Filter -->
              <div class="flex gap-4 mb-6">
                <button class="px-4 py-2 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                  All Feedbacks
                </button>
                <button class="px-4 py-2 rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200">
                  Pending
                </button>
                <button class="px-4 py-2 rounded-lg bg-green-100 text-green-700 hover:bg-green-200">
                  Approved
                </button>
                <button class="px-4 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200">
                  Rejected
                </button>
              </div>

              <!-- Skeleton Loading Screen -->
              <div id="loading" class="bg-white rounded-lg shadow-lg overflow-hidden p-6">
                <div class="animate-pulse">
                  <div class="h-4 bg-gray-200 rounded mb-2"></div>
                  <div class="h-4 bg-gray-200 rounded mb-2"></div>
                  <div class="h-4 bg-gray-200 rounded mb-2"></div>
                  <div class="h-4 bg-gray-200 rounded mb-2"></div>
                  <div class="h-4 bg-gray-200 rounded mb-2"></div>
                </div>
              </div>

              <!-- Feedback Cards -->
              <div id="feedback-cards" class="grid gap-6 hidden">
                <?php while ($row = $result->fetch_assoc()): ?>
                <div class="bg-gray-50 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                  <div class="flex justify-between items-start mb-4">
                    <div>
                      <h3 class="text-lg font-semibold text-gray-900">
                        <?php echo htmlspecialchars($row['full_name']); ?>
                      </h3>
                      <p class="text-sm text-gray-500">
                        <?php echo date('F j, Y g:i A', strtotime($row['created_at'])); ?>
                      </p>
                    </div>
                    <div>
                      <span class="px-3 py-1 rounded-full text-sm font-medium
                                                <?php echo $row['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                        ($row['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                        'bg-yellow-100 text-yellow-800'); ?>">
                        <?php echo ucfirst($row['status']); ?>
                      </span>
                    </div>
                  </div>

                  <div class="mb-4">
                    <div class="flex items-center mb-2">
                      <?php for($i = 0; $i < $row['rating']; $i++): ?>
                      <span class="text-yellow-400 text-xl">⭐</span>
                      <?php endfor; ?>
                    </div>
                    <p class="text-gray-700"><?php echo htmlspecialchars($row['message']); ?></p>
                  </div>

                  <?php if ($row['status'] === 'pending'): ?>
                  <div class="flex gap-2">
                    <form method="POST" class="inline-block">
                      <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                      <button type="submit" name="status" value="approved"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Approve
                      </button>
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

  <script>
  // Show loading screen initially
  document.getElementById('loading').style.display = 'block';
  document.getElementById('feedback-cards').style.display = 'none';

  // Simulate data loading
  window.addEventListener('load', function() {
    setTimeout(() => {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('feedback-cards').style.display = 'grid';
    }, 1500); // Show loading for 1.5 seconds
  });
  </script>
</body>

</html>