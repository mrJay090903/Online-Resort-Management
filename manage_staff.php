<?php
include 'staff_management.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_name = $_POST['staff_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (addStaff($staff_name, $contact_number, $email, $password)) {
        $success_message = "Staff added successfully!";
    } else {
        $error_message = "Error adding staff!";
    }
}

// Get all staff
$staff_list = getAllStaff();
?>

<!DOCTYPE html>
<html>

<head>
  <title>Staff Account</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Add icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Add the new JavaScript file -->
  <script src="assets/js/staff.js"></script>
  <script src="https://cdn.lordicon.com/lordicon.js"></script>
</head>

<body class="bg-gray-100">
  <div class="flex">
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1">
      <!-- Top Navigation -->
      <div class="bg-white shadow-sm">
        <div class="flex justify-between items-center px-6 py-3">
          <h1 class="text-xl font-semibold">Staff Account</h1>
          <div class="flex items-center space-x-4">
            <div class="relative">
              <input type="text" placeholder="Search"
                class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
              <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <i class="far fa-bell text-gray-600"></i>
            <i class="fas fa-question-circle text-gray-600"></i>
            <img src="https://via.placeholder.com/40" class="w-10 h-10 rounded-full">
          </div>
        </div>
      </div>

      <!-- Content Area -->
      <div class="p-6">
        <div class="flex justify-end mb-6">
          <button onclick="document.getElementById('addStaffModal').classList.remove('hidden')"
            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center">
            <lord-icon src="https://cdn.lordicon.com/dxjqoygy.json" trigger="hover" colors="primary:#ffffff"
              style="width:24px;height:24px">
            </lord-icon>
            <span class="ml-2">Add Staff</span>
          </button>
        </div>

        <!-- Staff Table -->
        <div class="bg-white rounded-lg shadow">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email Address</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Password</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($staff_list as $staff): ?>
              <tr class="hover:bg-gray-50" data-staff-id="<?php echo htmlspecialchars($staff['id']); ?>">
                <td class="px-6 py-4" data-field="name"><?php echo htmlspecialchars($staff['staff_name']); ?></td>
                <td class="px-6 py-4" data-field="contact"><?php echo htmlspecialchars($staff['contact_number']); ?>
                </td>
                <td class="px-6 py-4" data-field="email"><?php echo htmlspecialchars($staff['email']); ?></td>
                <td class="px-6 py-4">**********</td>
                <td class="px-6 py-4 space-x-2 flex items-center">
                  <button onclick="openViewModal(<?php echo htmlspecialchars($staff['id']); ?>)"
                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 flex items-center">
                    <lord-icon src="https://cdn.lordicon.com/bxxnzvfm.json" trigger="hover" colors="primary:#ffffff"
                      style="width:20px;height:20px">
                    </lord-icon>
                    <span class="ml-1">Edit</span>
                  </button>
                  <button onclick="removeStaff(<?php echo htmlspecialchars($staff['id']); ?>)"
                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 flex items-center">
                    <lord-icon src="https://cdn.lordicon.com/jmkrnisz.json" trigger="hover" colors="primary:#ffffff"
                      style="width:20px;height:20px">
                    </lord-icon>
                    <span class="ml-1">Remove</span>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Staff Modal -->
  <div id="addStaffModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 w-1/2">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Add New Staff</h3>
        <button onclick="document.getElementById('addStaffModal').classList.add('hidden')"
          class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form method="POST" action="" class="space-y-4" onsubmit="validateAddStaffForm(event)">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Staff Name:</label>
          <input type="text" name="staff_name" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
          <p class="text-gray-500 text-xs mt-1">Letters only, minimum 2 characters</p>
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Contact Number:</label>
          <input type="text" name="contact_number" required placeholder="09xxxxxxxxx"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
          <p class="text-gray-500 text-xs mt-1">Must start with 09 and be 11 digits long</p>
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Email Address:</label>
          <input type="email" name="email" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
          <p class="text-gray-500 text-xs mt-1">Enter a valid email address</p>
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
          <input type="password" name="password" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
          <p class="text-gray-500 text-xs mt-1">Must be at least 8 characters and contain both letters and numbers</p>
        </div>
        <div class="flex justify-end space-x-3">
          <button type="button" onclick="document.getElementById('addStaffModal').classList.add('hidden')"
            class="px-4 py-2 border rounded-lg hover:bg-gray-50 flex items-center">
            <lord-icon src="https://cdn.lordicon.com/nqtddedc.json" trigger="hover" colors="primary:#333333"
              style="width:20px;height:20px">
            </lord-icon>
            <span class="ml-2">Cancel</span>
          </button>
          <button type="submit"
            class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 flex items-center">
            <lord-icon src="https://cdn.lordicon.com/dxjqoygy.json" trigger="hover" colors="primary:#ffffff"
              style="width:20px;height:20px">
            </lord-icon>
            <span class="ml-2">Add Staff</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Staff Modal -->
  <div id="viewStaffModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 w-1/2">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Edit Staff Details</h3>
        <button onclick="document.getElementById('viewStaffModal').classList.add('hidden')"
          class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="viewStaffForm" class="space-y-4">
        <input type="hidden" id="view-staff-id" name="staff_id">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Staff Name:</label>
          <input type="text" id="view-staff-name" name="staff_name"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500 bg-gray-100" readonly>
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Contact Number:</label>
          <input type="text" id="view-contact-number" name="contact_number"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500 bg-gray-100" readonly>
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Email Address:</label>
          <input type="email" id="view-email" name="email"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500 bg-gray-100" readonly>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" onclick="closeViewModal()"
            class="px-4 py-2 border rounded-lg hover:bg-gray-50 flex items-center">
            <lord-icon src="https://cdn.lordicon.com/nqtddedc.json" trigger="hover" colors="primary:#333333"
              style="width:20px;height:20px">
            </lord-icon>
            <span class="ml-2">Cancel</span>
          </button>
          <button type="button" onclick="updateStaffDetails()"
            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 flex items-center">
            <lord-icon src="https://cdn.lordicon.com/oqdmuxru.json" trigger="hover" colors="primary:#ffffff"
              style="width:20px;height:20px">
            </lord-icon>
            <span class="ml-2">Save Changes</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>