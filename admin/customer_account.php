<?php
include('../db.php');
session_start();

// Delete customer
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM customers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Customer deleted successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error deleting customer.";
    }
    header("Location: customer_account.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Account</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.lordicon.com/bhenfmcm.js"></script>
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
            <h1 class="text-2xl font-semibold text-gray-900">Customer Account</h1>
          </div>

          <!-- Table -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Customer Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contact Number
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email Address
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Password
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php
                                $sql = "SELECT * FROM customers ORDER BY id DESC";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['customer_name']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['contact_number']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['email']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap">********</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button onclick="confirmDelete(<?php echo $row['id']; ?>)"
                      class="flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded-md">
                      <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="hover" colors="primary:#ffffff"
                        style="width:20px;height:20px">
                      </lord-icon>
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
  // Function to show delete confirmation
  function confirmDelete(customerId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `?delete=${customerId}`;
      }
    });
  }
  </script>
</body>

</html>