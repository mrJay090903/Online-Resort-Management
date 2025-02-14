<?php
include('../config/database.php');
session_start();

// Function to validate inputs
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Add new staff
if (isset($_POST['add_staff'])) {
    $errors = [];
    
    // Validate staff name
    $staff_name = validateInput($_POST['staff_name']);
    if (empty($staff_name)) {
        $errors[] = "Staff name is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $staff_name)) {
        $errors[] = "Only letters and white space allowed in name";
    }
    
    // Validate contact number
    $contact = validateInput($_POST['contact']);
    if (empty($contact)) {
        $errors[] = "Contact number is required";
    } elseif (!preg_match("/^[0-9]{11}$/", $contact)) {
        $errors[] = "Invalid contact number format (must be 11 digits)";
    }
    
    // Validate email
    $email = validateInput($_POST['email']);
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $check_email = "SELECT u.id FROM users u WHERE u.email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // Validate password
    $password = $_POST['password'];
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // First, create user account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'staff')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $hashedPassword);
            $stmt->execute();
            
            $user_id = $conn->insert_id;
            
            // Then, create staff record
            $sql = "INSERT INTO staff (user_id, staff_name, contact_number, email) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $staff_name, $contact, $email);
            
            if ($stmt->execute()) {
                $conn->commit();
                $_SESSION['success'] = true;
                $_SESSION['message'] = "Staff added successfully!";
            } else {
                throw new Exception("Error adding staff");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['success'] = false;
            $_SESSION['message'] = $e->getMessage();
        }
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = implode("<br>", $errors);
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
    $errors = [];
    $id = $_POST['staff_id'];
    
    // Validate staff name
    $staff_name = validateInput($_POST['staff_name']);
    if (empty($staff_name)) {
        $errors[] = "Staff name is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $staff_name)) {
        $errors[] = "Only letters and white space allowed in name";
    }
    
    // Validate contact number
    $contact = validateInput($_POST['contact']);
    if (empty($contact)) {
        $errors[] = "Contact number is required";
    } elseif (!preg_match("/^[0-9]{11}$/", $contact)) {
        $errors[] = "Invalid contact number format (must be 11 digits)";
    }
    
    // Validate email
    $email = validateInput($_POST['email']);
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists for other staff
        $check_email = "SELECT u.id FROM users u 
                        JOIN staff s ON u.id = s.user_id 
                        WHERE u.email = ? AND s.id != ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Update staff information
            $sql = "UPDATE staff SET staff_name=?, contact_number=?, email=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $staff_name, $contact, $email, $id);
            
            if ($stmt->execute()) {
                // Update email in users table
                $sql = "UPDATE users u 
                        JOIN staff s ON u.id = s.user_id 
                        SET u.email = ? 
                        WHERE s.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $email, $id);
                $stmt->execute();

                // If password is provided, update it in users table
                if (!empty($_POST['password'])) {
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = "UPDATE users u 
                            JOIN staff s ON u.id = s.user_id 
                            SET u.password = ? 
                            WHERE s.id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $hashedPassword, $id);
                    $stmt->execute();
                }
                
                $conn->commit();
                $_SESSION['success'] = true;
                $_SESSION['message'] = "Staff updated successfully!";
            } else {
                throw new Exception("Error updating staff");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['success'] = false;
            $_SESSION['message'] = $e->getMessage();
        }
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = implode("<br>", $errors);
    }
    header("Location: staff_account.php");
    exit();
}
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

          <!-- Table -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact
                    Number</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                    Address
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Password
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php
  $sql = "SELECT s.*, u.email as user_email 
          FROM staff s 
          JOIN users u ON s.user_id = u.id 
          ORDER BY s.id DESC";
  $result = $conn->query($sql);
  while ($row = $result->fetch_assoc()):
  ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['staff_name']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['contact_number']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['user_email']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap">********</td>
                  <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                    <button
                      onclick="editStaff(<?php echo $row['id']; ?>, '<?php echo $row['staff_name']; ?>', '<?php echo $row['contact_number']; ?>', '<?php echo $row['user_email']; ?>')"
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
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="contact">
                      Contact Number
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                      type="text" id="contact" name="contact" required oninput="validateContact()">
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
                    <p id="password_error" class="text-red-500 text-xs hidden">Password must be at least 6 characters.
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
              <!-- Modal Header -->
              <div class="bg-yellow-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Edit Staff</h3>
              </div>
              <!-- Modal Body -->
              <div class="p-6">
                <form action="" method="POST">
                  <input type="hidden" id="edit_staff_id" name="staff_id">
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_staff_name">
                      Staff Name
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500"
                      type="text" id="edit_staff_name" name="staff_name" required>
                    <!-- Error message will be inserted here -->
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_contact">
                      Contact Number
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500"
                      type="text" id="edit_contact" name="contact" required>
                    <!-- Error message will be inserted here -->
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_email">
                      Email Address
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500"
                      type="email" id="edit_email" name="email" required>
                    <!-- Error message will be inserted here -->
                  </div>
                  <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_password">
                      Password (Leave blank to keep current password)
                    </label>
                    <input
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500"
                      type="password" id="edit_password" name="password">
                    <!-- Error message will be inserted here -->
                  </div>
                  <!-- Modal Footer -->
                  <div class="flex items-center justify-end gap-4">
                    <button type="button" onclick="document.getElementById('editStaffModal').classList.add('hidden')"
                      class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                      Cancel
                    </button>
                    <button
                      class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500"
                      type="submit" name="edit_staff">
                      Update Staff
                    </button>
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
    document.getElementById('edit_contact').value = contact;
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
    const contactInput = document.getElementById('contact');
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

    if (passwordInput.value.length < 6) {
      passwordError.classList.remove('hidden');
      passwordInput.classList.add('border-red-500');
      passwordInput.classList.remove('border-green-500');
    } else {
      passwordError.classList.add('hidden');
      passwordInput.classList.add('border-green-500');
      passwordInput.classList.remove('border-red-500');
    }
  }
  </script>
</body>

</html>