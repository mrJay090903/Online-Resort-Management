<?php
session_start();
require_once '../config/database.php';

// Function to validate inputs
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle staff creation
if (isset($_POST['add_staff'])) {
    // Validate inputs
    $errors = [];
    $staff_name = trim($conn->real_escape_string($_POST['staff_name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $contact_number = trim($conn->real_escape_string($_POST['contact_number']));
    $password = $_POST['password'];

    // Validation
    if (empty($staff_name)) $errors[] = "Staff name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($contact_number)) $errors[] = "Contact number is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";

    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Check if email exists
            $check_sql = "SELECT id FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Email already exists");
            }

            // Insert into users table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $user_sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'staff')";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("ss", $email, $hashed_password);
            
            if (!$user_stmt->execute()) {
                throw new Exception("Error creating user account");
            }
            
            $user_id = $conn->insert_id;

            // Insert into staff table
            $staff_sql = "INSERT INTO staff (user_id, staff_name, contact_number) VALUES (?, ?, ?)";
            $staff_stmt = $conn->prepare($staff_sql);
            $staff_stmt->bind_param("iss", $user_id, $staff_name, $contact_number);
            
            if (!$staff_stmt->execute()) {
                throw new Exception("Error creating staff record");
            }

            $conn->commit();
            $_SESSION['success'] = "Staff account created successfully";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }

    header("Location: staff_account.php");
    exit();
}

// Delete staff
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->begin_transaction();
    
    try {
        // Get user_id first
        $sql = "SELECT user_id FROM staff WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_assoc();
        
        if ($staff) {
            // Delete from users table (will cascade to staff table)
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $staff['user_id']);
            
            if ($stmt->execute()) {
                $conn->commit();
                $_SESSION['success'] = true;
                $_SESSION['message'] = "Staff deleted successfully!";
            } else {
                throw new Exception("Error deleting staff");
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['success'] = false;
        $_SESSION['message'] = $e->getMessage();
    }
    header("Location: staff_account.php");
    exit();
}

// Update staff
if (isset($_POST['edit_staff'])) {
    $staff_id = $_POST['staff_id'];
    $staff_name = $_POST['staff_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn->begin_transaction();

    try {
        // First get the user_id for this staff member
        $get_user_id = "SELECT user_id FROM staff WHERE id = ?";
        $stmt = $conn->prepare($get_user_id);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Staff not found");
        }
        
        $staff = $result->fetch_assoc();
        $user_id = $staff['user_id'];

        // Update staff information (name and contact)
        $update_staff = "UPDATE staff SET staff_name = ?, contact_number = ? WHERE id = ?";
        $stmt = $conn->prepare($update_staff);
        $stmt->bind_param("ssi", $staff_name, $contact_number, $staff_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating staff information");
        }

        // Update email in users table
        $update_email = "UPDATE users SET email = ? WHERE id = ?";
        $stmt = $conn->prepare($update_email);
        $stmt->bind_param("si", $email, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating email");
        }

        // Update password if provided
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_password = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_password);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error updating password");
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Staff updated successfully";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: staff_account.php");
    exit();
}

// Fetch existing staff members
$staff_query = "SELECT s.*, u.email 
                FROM staff s 
                JOIN users u ON s.user_id = u.id 
                WHERE u.user_type = 'staff'";
$staff_result = $conn->query($staff_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Account</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <!-- Add SweetAlert2 CSS and JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Add Lordicon -->
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
            <h1 class="text-2xl font-semibold text-gray-900">Staff Account</h1>
            <button onclick="document.getElementById('addStaffModal').classList.remove('hidden')"
              class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
              <lord-icon src="https://cdn.lordicon.com/mecwbjnp.json" trigger="hover" colors="primary:#ffffff"
                style="width:24px;height:24px">
              </lord-icon>
              Add Staff
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

          <!-- Staff Table -->
          <div id="staff-table" class="hidden">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact Number</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $staff_result->fetch_assoc()): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['staff_name']); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['email']); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                    <button
                      onclick="editStaff(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['staff_name']); ?>', '<?php echo htmlspecialchars($row['contact_number']); ?>', '<?php echo htmlspecialchars($row['email']); ?>')"
                      class="text-yellow-500 hover:text-yellow-600 p-1 rounded-md">
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button onclick="confirmDelete(<?php echo $row['id']; ?>)"
                      class="text-red-500 hover:text-red-600 p-1 rounded-md ml-2">
                      <span class="material-symbols-outlined">delete</span>
                    </button>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <!-- Add Staff Modal -->
          <div id="addStaffModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/3">
              <!-- Modal Header -->
              <div class="bg-blue-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Add New Staff</h3>
              </div>
              <!-- Modal Body -->
              <div class="p-6">
                <form action="" method="POST" id="addStaffForm">
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="staff_name">
                      Staff Name
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                      type="text" id="staff_name" name="staff_name" required oninput="validateStaffName()">
                    <p id="staff_name_error" class="text-red-500 text-xs hidden">Only letters and white space allowed.
                    </p>
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_number">
                      Contact Number
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                      type="text" id="contact_number" name="contact_number" required oninput="validateContact()">
                    <p id="contact_error" class="text-red-500 text-xs hidden">Invalid contact number format (must be 11
                      digits).</p>
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                      Email Address
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                      type="email" id="email" name="email" required oninput="validateEmail()">
                    <p id="email_error" class="text-red-500 text-xs hidden">Invalid email format.</p>
                  </div>
                  <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                      Password
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                      type="password" id="password" name="password" required oninput="validatePassword()">
                    <p id="password_error" class="text-red-500 text-xs hidden">Password must be at least 8 characters.
                    </p>
                  </div>
                  <!-- Modal Footer -->
                  <div class="flex items-center justify-end gap-4">
                    <button type="button" onclick="document.getElementById('addStaffModal').classList.add('hidden')"
                      class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                      Cancel
                    </button>
                    <button
                      class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      type="submit" name="add_staff" id="add_staff_button">
                      Add Staff
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>


          <!-- Edit Staff Modal -->
          <div id="editStaffModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/3">
              <div class="bg-yellow-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Edit Staff</h3>
              </div>
              <div class="p-6">
                <form action="staff_account.php" method="POST" id="editStaffForm">
                  <input type="hidden" id="edit_staff_id" name="staff_id">
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Staff Name</label>
                    <input type="text" id="edit_staff_name" name="staff_name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Contact Number</label>
                    <input type="text" id="edit_contact_number" name="contact_number" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="edit_email" name="email" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                  </div>
                  <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('editStaffModal').classList.add('hidden')"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Cancel</button>
                    <button type="submit" name="edit_staff"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">Update Staff</button>
                  </div>
                </form>
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
  // Function to show delete confirmation
  function confirmDelete(staffId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `?delete=${staffId}`;
      }
    });
  }

  // Function to open edit modal and populate fields
  function editStaff(id, name, contact, email) {
    document.getElementById('edit_staff_id').value = id;
    document.getElementById('edit_staff_name').value = name;
    document.getElementById('edit_contact_number').value = contact;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_password').value = ''; // Clear password field
    document.getElementById('editStaffModal').classList.remove('hidden');
  }

  // Function to close modals when clicking outside
  window.onclick = function(event) {
    let addModal = document.getElementById('addStaffModal');
    let editModal = document.getElementById('editStaffModal');
    if (event.target == addModal) {
      addModal.classList.add('hidden');
    }
    if (event.target == editModal) {
      editModal.classList.add('hidden');
    }
  }

  function validateStaffName() {
    const staffNameInput = document.getElementById('staff_name');
    const staffNameError = document.getElementById('staff_name_error');
    const regex = /^[a-zA-Z ]*$/;

    if (staffNameInput.value.trim() === '') {
      staffNameError.textContent = "Staff name is required.";
      staffNameError.classList.remove('hidden');
      staffNameInput.classList.add('border-red-500');
      staffNameInput.classList.remove('border-green-500');
    } else if (!regex.test(staffNameInput.value)) {
      staffNameError.textContent = "Only letters and white space allowed.";
      staffNameError.classList.remove('hidden');
      staffNameInput.classList.add('border-red-500');
      staffNameInput.classList.remove('border-green-500');
    } else {
      staffNameError.classList.add('hidden');
      staffNameInput.classList.add('border-green-500');
      staffNameInput.classList.remove('border-red-500');
    }
  }

  function validateContact() {
    const contactInput = document.getElementById('contact_number');
    const contactError = document.getElementById('contact_error');
    const regex = /^[0-9]{11}$/;

    if (contactInput.value.trim() === '') {
      contactError.textContent = "Contact number is required.";
      contactError.classList.remove('hidden');
      contactInput.classList.add('border-red-500');
      contactInput.classList.remove('border-green-500');
    } else if (!regex.test(contactInput.value)) {
      contactError.textContent = "Invalid contact number format (must be 11 digits).";
      contactError.classList.remove('hidden');
      contactInput.classList.add('border-red-500');
      contactInput.classList.remove('border-green-500');
    } else {
      contactError.classList.add('hidden');
      contactInput.classList.add('border-green-500');
      contactInput.classList.remove('border-red-500');
    }
  }

  function validateEmail() {
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email_error');
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (emailInput.value.trim() === '') {
      emailError.textContent = "Email is required.";
      emailError.classList.remove('hidden');
      emailInput.classList.add('border-red-500');
      emailInput.classList.remove('border-green-500');
    } else if (!regex.test(emailInput.value)) {
      emailError.textContent = "Invalid email format.";
      emailError.classList.remove('hidden');
      emailInput.classList.add('border-red-500');
      emailInput.classList.remove('border-green-500');
    } else {
      emailError.classList.add('hidden');
      emailInput.classList.add('border-green-500');
      emailInput.classList.remove('border-red-500');
    }
  }

  function validatePassword() {
    const passwordInput = document.getElementById('password');
    const passwordError = document.getElementById('password_error');

    if (passwordInput.value.length < 8) {
      passwordError.classList.remove('hidden');
      passwordInput.classList.add('border-red-500');
      passwordInput.classList.remove('border-green-500');
    } else {
      passwordError.classList.add('hidden');
      passwordInput.classList.add('border-green-500');
      passwordInput.classList.remove('border-red-500');
    }
  }

  // Add form submission validation
  document.getElementById('addStaffForm').addEventListener('submit', function(e) {
    const staffName = document.getElementById('staff_name').value.trim();
    const contactNumber = document.getElementById('contact_number').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    let isValid = true;
    let errors = [];

    // Validate staff name
    if (!/^[a-zA-Z ]*$/.test(staffName)) {
      isValid = false;
      errors.push("Staff name should only contain letters and spaces");
    }

    // Validate contact number
    if (!/^[0-9]{11}$/.test(contactNumber)) {
      isValid = false;
      errors.push("Contact number must be 11 digits");
    }

    // Validate email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      isValid = false;
      errors.push("Invalid email format");
    }

    // Validate password
    if (password.length < 8) {
      isValid = false;
      errors.push("Password must be at least 8 characters");
    }

    if (!isValid) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: errors.join('<br>'),
        confirmButtonColor: '#3085d6'
      });
    }
  });

  // Add edit form validation
  document.getElementById('editStaffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const staffName = document.getElementById('edit_staff_name').value.trim();
    const contactNumber = document.getElementById('edit_contact_number').value.trim();
    const email = document.getElementById('edit_email').value.trim();
    const password = document.getElementById('edit_password').value;

    let isValid = true;
    let errors = [];

    if (!staffName) {
        errors.push("Staff name is required");
        isValid = false;
    }

    if (!contactNumber) {
        errors.push("Contact number is required");
        isValid = false;
    }

    if (!email) {
        errors.push("Email is required");
        isValid = false;
    }

    if (password && password.length < 8) {
        errors.push("Password must be at least 8 characters");
        isValid = false;
    }

    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: errors.join('<br>'),
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    // Show confirmation before submitting
    Swal.fire({
        title: 'Update Staff',
        text: 'Are you sure you want to update this staff member?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
  });

  // Show loading screen initially
  document.getElementById('loading').style.display = 'block';
  document.getElementById('staff-table').style.display = 'none';

  // Simulate data loading
  window.addEventListener('load', function() {
    setTimeout(() => {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('staff-table').style.display = 'block';
    }, 1500); // Show loading for 1.5 seconds
  });
  </script>
</body>

</html>