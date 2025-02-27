<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Delete customer
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // First get the user_id associated with this customer
    $sql = "SELECT user_id FROM customers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    
    if ($customer) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete from customers table first
            $sql = "DELETE FROM customers WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Then delete from users table
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer['user_id']);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = true;
            $_SESSION['message'] = "Customer deleted successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Error deleting customer: " . $e->getMessage();
        }
    }
    
    header("Location: customer_account.php");
    exit();
}

// Fetch all customers with their user data
$sql = "SELECT c.*, u.email 
        FROM customers c 
        JOIN users u ON c.user_id = u.id 
        ORDER BY c.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Accounts - Admin Dashboard</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
            <h1 class="text-2xl font-semibold text-gray-900">Customer Accounts</h1>
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

          <!-- Customer Table -->
          <div id="customer-table" class="hidden">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact Number</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="border-t border-gray-200">
                  <td class="px-6 py-4 relative">
                    <button onclick="toggleDropdown(<?php echo $row['id']; ?>)"
                      class="text-left hover:text-gray-700 focus:outline-none">
                      <?php echo htmlspecialchars($row['full_name']); ?>
                      <span class="material-icons text-sm align-middle ml-1">expand_more</span>
                    </button>
                    <div id="dropdown-<?php echo $row['id']; ?>"
                      class="hidden absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                      <div class="py-1">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Details</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Customer</a>
                        <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>); return false;"
                          class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete Customer</a>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <?php echo htmlspecialchars($row['email']); ?>
                  </td>
                  <td class="px-6 py-4">
                    <?php echo htmlspecialchars($row['contact_number']); ?>
                  </td>
                  <td class="px-6 py-4">
                    <button onclick="confirmDelete(<?php echo $row['id']; ?>)"
                      class="text-red-600 hover:text-red-900 transition-colors duration-200">
                      <span class="material-icons">delete</span>
                    </button>
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

  <script>
  let activeDropdown = null;

  function toggleDropdown(customerId) {
    const dropdown = document.getElementById(`dropdown-${customerId}`);

    // Close previously opened dropdown
    if (activeDropdown && activeDropdown !== dropdown) {
      activeDropdown.classList.add('hidden');
    }

    dropdown.classList.toggle('hidden');
    activeDropdown = dropdown;
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
    if (!event.target.closest('button') && activeDropdown) {
      activeDropdown.classList.add('hidden');
      activeDropdown = null;
    }
  });

  function confirmDelete(customerId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `?delete=${customerId}`;
      }
    });
  }

  // Show loading screen initially
  document.getElementById('loading').style.display = 'block';
  document.getElementById('customer-table').style.display = 'none';

  // Simulate data loading
  window.addEventListener('load', function() {
    setTimeout(() => {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('customer-table').style.display = 'block';
    }, 1500); // Show loading for 1.5 seconds
  });
  </script>
</body>

</html>