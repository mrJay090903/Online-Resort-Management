<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}

// Fetch current user data based on user type
$user_id = $_SESSION['user_id'];
$sql = "SELECT c.*, u.email 
        FROM customers c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Only process password changes
    if (!empty($current_password)) {
        try {
            // Verify current password
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();

            if (!password_verify($current_password, $user_data['password'])) {
                throw new Exception("Current password is incorrect");
            }

            if (empty($new_password) || empty($confirm_password)) {
                throw new Exception("New password and confirmation are required");
            }

            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match");
            }

            // Update password in users table
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();

            $_SESSION['success'] = "Password updated successfully!";
            header("Location: settings.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: settings.php");
            exit();
        }
    } else {
        $_SESSION['info'] = "No changes were made";
        header("Location: settings.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Settings - Casita De Grands</title>
  <link href="../src/output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-['Lexend']">
  <?php include('components/nav.php'); ?>

  <div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Account Settings</h1>

      <form method="POST" class="space-y-6">
        <!-- Personal Information -->
        <div class="space-y-4">
          <h2 class="text-xl font-semibold text-gray-700">Personal Information</h2>

          <div>
            <label class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly
              class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm cursor-not-allowed">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
              class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm cursor-not-allowed">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
            <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>"
              readonly class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm cursor-not-allowed">
          </div>
        </div>

        <!-- Change Password -->
        <div class="space-y-4 pt-6 border-t">
          <h2 class="text-xl font-semibold text-gray-700">Change Password</h2>

          <div>
            <label class="block text-sm font-medium text-gray-700">Current Password</label>
            <input type="password" name="current_password"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" name="new_password"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
            <input type="password" name="confirm_password"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
          </div>
        </div>

        <div class="flex justify-end space-x-4">
          <a href="customer_dashboard.php"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
            Cancel
          </a>
          <button type="submit" class="px-4 py-2 bg-emerald-500 text-white rounded-md hover:bg-emerald-600">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
  <?php if (isset($_SESSION['success'])): ?>
  Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?php echo $_SESSION['success']; ?>',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
  <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
  Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: '<?php echo $_SESSION['error']; ?>',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
  <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

    <
    script >
    document.querySelector('form').addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent form submission initially

      const currentPassword = document.querySelector('input[name="current_password"]').value;
      const newPassword = document.querySelector('input[name="new_password"]').value;
      const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

      // Only validate if user is trying to change password
      if (currentPassword || newPassword || confirmPassword) {
        // Check if current password is provided
        if (!currentPassword) {
          Swal.fire({
            icon: 'error',
            title: 'Current Password Required',
            text: 'Please enter your current password',
            confirmButtonColor: '#10B981'
          });
          return;
        }

        // Check if new password is provided
        if (!newPassword) {
          Swal.fire({
            icon: 'error',
            title: 'New Password Required',
            text: 'Please enter your new password',
            confirmButtonColor: '#10B981'
          });
          return;
        }

        // Validate password length
        if (newPassword.length < 8) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password',
            text: 'New password must be at least 8 characters long',
            confirmButtonColor: '#10B981'
          });
          return;
        }

        // Validate password format (letters and numbers)
        if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(newPassword)) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Password Format',
            text: 'Password must contain at least one letter and one number',
            confirmButtonColor: '#10B981'
          });
          return;
        }

        // Check if passwords match
        if (newPassword !== confirmPassword) {
          Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'New password and confirmation do not match',
            confirmButtonColor: '#10B981'
          });
          return;
        }
      } else {
        Swal.fire({
          icon: 'info',
          title: 'No Changes',
          text: 'No password changes detected',
          confirmButtonColor: '#10B981'
        });
        return;
      }

      // If all validations pass, show confirmation dialog
      Swal.fire({
        title: 'Change Password?',
        text: 'Are you sure you want to update your password?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#EF4444',
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Show loading state
          Swal.fire({
            title: 'Updating...',
            text: 'Please wait while we update your password',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
              Swal.showLoading();
            }
          });
          // Submit the form
          this.submit();
        }
      });
    });
  </script>
  </script>
</body>

</html>