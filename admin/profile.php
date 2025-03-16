<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has appropriate access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'staff'])) {
    header('Location: ../index.php');
    exit();
}

// Get user details based on user type
$user_id = $_SESSION['user_id'];
if ($_SESSION['user_type'] === 'staff') {
    $query = "
        SELECT u.*, s.staff_name as full_name, s.contact_number 
        FROM users u 
        LEFT JOIN staff s ON u.id = s.user_id 
        WHERE u.id = ? AND u.user_type = 'staff'
    ";
} else {
    $query = "
        SELECT u.*, s.staff_name as full_name, s.contact_number 
        FROM users u 
        LEFT JOIN staff s ON u.id = s.user_id 
        WHERE u.id = ? AND u.user_type = 'admin'
    ";
}

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// If no result found, set default values
if (!$user) {
    $user = [
        'full_name' => $_SESSION['staff_name'] ?? $_SESSION['email'] ?? '',
        'contact_number' => $_SESSION['contact_number'] ?? '',
        'email' => $_SESSION['email'] ?? ''
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Settings - <?php echo ucfirst($_SESSION['user_type']); ?> Dashboard</title>
  <link href="../src/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <!-- Add SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
  <div class="flex">
    <?php include('components/sidebar.php'); ?>

    <div class="flex-1">
      <?php include('components/header.php'); ?>

      <main class="p-8">
        <div class="max-w-2xl mx-auto">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Profile Settings</h2>

            <form id="profileForm" class="space-y-6">
              <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
              </div>

              <div>
                <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number"
                  value="<?php echo htmlspecialchars($user['contact_number']); ?>"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
              </div>

              <div class="space-y-2">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" id="current_password" name="current_password"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="Enter current password to change">
              </div>

              <div class="space-y-2">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="new_password" name="new_password"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="Enter new password">
              </div>

              <div class="pt-4">
                <button type="submit"
                  class="w-full px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                  Update Profile
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
  document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('../handlers/update_profile.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Profile updated successfully',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          throw new Error(data.message || 'Failed to update profile');
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: error.message
        });
      });
  });
  </script>
</body>

</html>