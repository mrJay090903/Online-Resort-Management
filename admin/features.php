<?php
include('../config/database.php');
session_start();

// Create uploads directory if it doesn't exist
if (!file_exists('../uploads/features')) {
    mkdir('../uploads/features', 0777, true);
}

// Update header redirect
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index');
    exit();
}

// Add new feature
if (isset($_POST['add_feature'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    // Handle image upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $new_filename = time() . '.' . $filetype;
            $upload_path = '../uploads/features/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
            } else {
                $_SESSION['success'] = false;
                $_SESSION['message'] = "Error uploading image. Error: " . $_FILES['image']['error'];
                header("Location: features.php");
                exit();
            }
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            header("Location: features.php");
            exit();
        }
    }
    
    $sql = "INSERT INTO features (title, description, image_url, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $title, $description, $image, $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Feature added successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error adding feature";
    }
    header("Location: features.php");
    exit();
}

// Edit feature
if (isset($_POST['edit_feature'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    // Handle image upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $new_filename = time() . '.' . $filetype;
            $upload_path = '../uploads/features/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
            } else {
                $_SESSION['success'] = false;
                $_SESSION['message'] = "Error uploading image. Error: " . $_FILES['image']['error'];
                header("Location: features.php");
                exit();
            }
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            header("Location: features.php");
            exit();
        }
    }

    // Update the feature details
    $sql = "UPDATE features SET title = ?, description = ?, status = ?" . ($image ? ", image_url = ?" : "") . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($image) {
        $stmt->bind_param("ssssi", $title, $description, $status, $image, $id);
    } else {
        $stmt->bind_param("sssi", $title, $description, $status, $id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Feature updated successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error updating feature";
    }
    header("Location: features.php");
    exit();
}

// Delete feature
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image filename before deleting record
    $sql = "SELECT image_url FROM features WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $feature = $result->fetch_assoc();
    
    // Delete the record
    $sql = "DELETE FROM features WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists
        if($feature['image_url'] && file_exists('../uploads/features/' . $feature['image_url'])) {
            unlink('../uploads/features/' . $feature['image_url']);
        }
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Feature deleted successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error deleting feature";
    }
    header("Location: features.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feature Management</title>
  <link href="../src/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link href="src/output.css" rel="stylesheet">
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
            <h1 class="text-2xl font-semibold text-gray-900">Feature Management</h1>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
              class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
              <span class="material-symbols-outlined">add</span>
              Add New
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

          <!-- Features Table -->
          <div id="features-table" class="hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Title
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Picture
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php
            $sql = "SELECT * FROM features ORDER BY id DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['title']); ?></td>
                  <td class="px-6 py-4"><?php echo htmlspecialchars($row['description']); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php if (!empty($row['image_url'])): ?>
                    <img src="../uploads/features/<?php echo htmlspecialchars($row['image_url']); ?>"
                      alt="Feature Image" class="h-20 w-20 object-cover rounded">
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                               <?php echo $row['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                      <?php echo ucfirst($row['status']); ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-3">
                      <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                        class="text-yellow-500 hover:text-yellow-600">
                        <span class="material-symbols-outlined">edit</span>
                      </button>
                      <button onclick="confirmDelete(<?php echo $row['id']; ?>)"
                        class="text-red-500 hover:text-red-600">
                        <span class="material-symbols-outlined">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <!-- Add Modal -->
          <div id="addModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2">
              <div class="bg-blue-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Add New Feature</h3>
              </div>
              <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                <div class="grid grid-cols-1 gap-6">
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Title
                    </label>
                    <input type="text" name="title" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Description
                    </label>
                    <textarea name="description" rows="3" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Image
                    </label>
                    <input type="file" name="image" accept="image/*" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select name="status" required class="shadow border rounded w-full py-2 px-3 text-gray-700">
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                    </select>
                  </div>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                  <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                  </button>
                  <button type="submit" name="add_feature"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Add
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Edit Modal -->
          <div id="editModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2">
              <div class="bg-blue-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Edit Feature</h3>
              </div>
              <form id="editForm" action="" method="POST" enctype="multipart/form-data" class="p-6">
                <input type="hidden" name="id" id="editId">
                <div class="grid grid-cols-1 gap-6">
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Title
                    </label>
                    <input type="text" name="title" id="editTitle" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Description
                    </label>
                    <textarea name="description" id="editDescription" rows="3" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Image (optional)
                    </label>
                    <input type="file" name="image" accept="image/*"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select name="status" id="editStatus" required
                      class="shadow border rounded w-full py-2 px-3 text-gray-700">
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                    </select>
                  </div>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                  <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                  </button>
                  <button type="submit" name="edit_feature"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update
                  </button>
                </div>
              </form>
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
  function confirmDelete(id) {
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
        window.location.href = `?delete=${id}`;
      }
    });
  }

  function openEditModal(feature) {
    document.getElementById('editId').value = feature.id;
    document.getElementById('editTitle').value = feature.title;
    document.getElementById('editDescription').value = feature.description;
    document.getElementById('editStatus').value = feature.status;
    document.getElementById('editModal').classList.remove('hidden');
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    let addModal = document.getElementById('addModal');
    let editModal = document.getElementById('editModal');
    if (event.target == addModal) {
      addModal.classList.add('hidden');
    }
    if (event.target == editModal) {
      editModal.classList.add('hidden');
    }
  }

  // Show loading screen initially
  document.getElementById('loading').style.display = 'block';
  document.getElementById('features-table').style.display = 'none';

  // Simulate data loading
  window.addEventListener('load', function() {
    setTimeout(() => {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('features-table').style.display = 'block';
    }, 1500); // Show loading for 1.5 seconds
  });
  </script>
</body>

</html>