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
            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            Add Staff!
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
                <td class="px-6 py-4 space-x-2">
                    <button onclick="openViewModal(<?php echo htmlspecialchars($staff['id']); ?>)" 
                        class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                        Edit
                    </button>
                    <button onclick="removeStaff(<?php echo htmlspecialchars($staff['id']); ?>)"
                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                        Remove
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
      <form method="POST" action="" class="space-y-4">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Staff Name:</label>
          <input type="text" name="staff_name" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Contact Number:</label>
          <input type="text" name="contact_number" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Email Address:</label>
          <input type="email" name="email" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
          <input type="password" name="password" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-emerald-500">
        </div>
        <div class="flex justify-end space-x-3">
          <button type="button" onclick="document.getElementById('addStaffModal').classList.add('hidden')"
            class="px-4 py-2 border rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600">
            Add Staff
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
            class="px-4 py-2 border rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button type="button" onclick="updateStaffDetails()"
            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
  async function removeStaff(staffId) {
    // Show confirmation dialog
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
      return;
    }

    try {
      const response = await fetch('staff_management.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove_staff&staff_id=${staffId}`
      });

      const data = await response.json();

      if (data.success) {
        // Remove the row and close modal
        document.querySelector(`tr[data-staff-id="${staffId}"]`).remove();
        document.getElementById('viewStaffModal').classList.add('hidden');

        // Show success message
        await Swal.fire({
          title: 'Deleted!',
          text: 'Staff has been removed successfully.',
          icon: 'success',
          timer: 1500
        });
      } else {
        // Show error message
        await Swal.fire({
          title: 'Error!',
          text: 'Failed to remove staff.',
          icon: 'error'
        });
      }
    } catch (error) {
      console.error('Error:', error);
      await Swal.fire({
        title: 'Error!',
        text: 'An error occurred while removing staff.',
        icon: 'error'
      });
    }
  }

  function openViewModal(staffId) {
    // Find staff data from the table
    const staffRow = document.querySelector(`tr[data-staff-id="${staffId}"]`);
    const staffName = staffRow.querySelector('[data-field="name"]').textContent;
    const staffContact = staffRow.querySelector('[data-field="contact"]').textContent;
    const staffEmail = staffRow.querySelector('[data-field="email"]').textContent;
    
    // Fill the modal with data
    document.getElementById('view-staff-name').value = staffName;
    document.getElementById('view-contact-number').value = staffContact;
    document.getElementById('view-email').value = staffEmail;
    
    // Store the staff ID in the form
    document.getElementById('view-staff-id').value = staffId;
    
    // Start in edit mode
    const inputs = ['view-staff-name', 'view-contact-number', 'view-email'];
    inputs.forEach(id => {
        const input = document.getElementById(id);
        input.readOnly = false;
        input.classList.remove('bg-gray-100');
        input.classList.add('bg-white');
    });
    
    // Show the modal
    document.getElementById('viewStaffModal').classList.remove('hidden');
  }

  function closeViewModal() {
    document.getElementById('viewStaffModal').classList.add('hidden');
  }

  async function updateStaffDetails() {
    const staffId = document.getElementById('view-staff-id').value;
    const staffName = document.getElementById('view-staff-name').value;
    const contactNumber = document.getElementById('view-contact-number').value;
    const email = document.getElementById('view-email').value;

    try {
        const response = await fetch('staff_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_staff&staff_id=${staffId}&staff_name=${encodeURIComponent(staffName)}&contact_number=${encodeURIComponent(contactNumber)}&email=${encodeURIComponent(email)}`
        });

        const data = await response.json();

        if (data.success) {
            // Update the table row
            const row = document.querySelector(`tr[data-staff-id="${staffId}"]`);
            row.querySelector('[data-field="name"]').textContent = staffName;
            row.querySelector('[data-field="contact"]').textContent = contactNumber;
            row.querySelector('[data-field="email"]').textContent = email;

            // Show success message
            await Swal.fire({
                title: 'Updated!',
                text: 'Staff details have been updated successfully.',
                icon: 'success',
                timer: 1500
            });

            // Close the modal
            document.getElementById('viewStaffModal').classList.add('hidden');
        } else {
            await Swal.fire({
                title: 'Error!',
                text: 'Failed to update staff details.',
                icon: 'error'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            title: 'Error!',
            text: 'An error occurred while updating staff details.',
            icon: 'error'
        });
    }
  }
  </script>
</body>

</html>