<?php
// Add this at the top of your file temporarily for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../db.php');
session_start();

// Add this at the beginning of your PHP code
if (!file_exists('../uploads/rooms')) {
    mkdir('../uploads/rooms', 0777, true);
}

// Check upload directory
$upload_dir = '../uploads/rooms';
if (!file_exists($upload_dir)) {
    echo "Upload directory doesn't exist!";
    mkdir($upload_dir, 0777, true);
    echo "Created directory: $upload_dir";
}

// Add new room
if (isset($_POST['add_room'])) {
    $room_name = $_POST['room_name'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $inclusions = $_POST['inclusions'];
    
    // Handle image upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Online-Resort-Management/uploads/rooms';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = time() . '.' . $filetype;
            $upload_path = $upload_dir . '/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
            } else {
                $_SESSION['success'] = false;
                $_SESSION['message'] = "Error uploading image. Error: " . $_FILES['image']['error'];
                header("Location: rooms.php");
                exit();
            }
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            header("Location: rooms.php");
            exit();
        }
    }
    
    $sql = "INSERT INTO rooms (room_name, price, capacity, inclusions, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdiss", $room_name, $price, $capacity, $inclusions, $image);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Room added successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error adding room.";
    }
    header("Location: rooms.php");
    exit();
}

// Edit room
if (isset($_POST['edit_room'])) {
    $id = $_POST['id'];
    $room_name = $_POST['room_name'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $inclusions = $_POST['inclusions'];
    $image = $_POST['current_image'];

    // Handle image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Online-Resort-Management/uploads/rooms';
            $new_filename = time() . '.' . $filetype;
            $upload_path = $upload_dir . '/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
            } else {
                $_SESSION['success'] = false;
                $_SESSION['message'] = "Error uploading image. Error: " . $_FILES['image']['error'];
                header("Location: rooms.php");
                exit();
            }
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            header("Location: rooms.php");
            exit();
        }
    }

    $sql = "UPDATE rooms SET room_name = ?, price = ?, capacity = ?, inclusions = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdissi", $room_name, $price, $capacity, $inclusions, $image, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Room updated successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error updating room.";
    }
    header("Location: rooms.php");
    exit();
}

// Delete room
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image filename before deleting record
    $sql = "SELECT image FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    
    // Delete the record
    $sql = "DELETE FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists
        if($room['image'] && file_exists('../uploads/rooms/' . $room['image'])) {
            unlink('../uploads/rooms/' . $room['image']);
        }
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Room deleted successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error deleting room.";
    }
    header("Location: rooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rooms Management</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
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
            <h1 class="text-2xl font-semibold text-gray-900">Rooms Management</h1>
            <button onclick="document.getElementById('addRoomModal').classList.remove('hidden')"
              class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
              <lord-icon src="https://cdn.lordicon.com/mecwbjnp.json" trigger="hover" colors="primary:#ffffff"
                style="width:24px;height:24px">
              </lord-icon>
              Add Room
            </button>
          </div>

          <!-- Table -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Room Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Picture
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php
                                $sql = "SELECT * FROM rooms ORDER BY id DESC";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['room_name']; ?></td>
                  <td class="px-6 py-4">
                    <p class="text-sm text-gray-900">Price: ₱<?php echo number_format($row['price'], 2); ?></p>
                    <p class="text-sm text-gray-600">Capacity: <?php echo $row['capacity']; ?> persons</p>
                    <p class="text-sm text-gray-600">Inclusions: <?php echo $row['inclusions']; ?></p>
                  </td>
                  <td class="px-6 py-4">
                    <?php 
                    $image_path = "/Online-Resort-Management/uploads/rooms/" . $row['image'];
                    if (!empty($row['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $image_path)): ?>
                    <img src="<?php echo $image_path; ?>" alt="Room Image" class="h-20 w-20 object-cover rounded">
                    <?php else: ?>
                    <div class="h-20 w-20 bg-gray-200 rounded flex items-center justify-center">
                      <lord-icon src="https://cdn.lordicon.com/dnmvmpfk.json" trigger="hover" colors="primary:#9ca3af"
                        style="width:32px;height:32px">
                      </lord-icon>
                    </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap flex items-center">
                    <button
                      onclick="editRoom(<?php echo $row['id']; ?>, '<?php echo $row['room_name']; ?>', <?php echo $row['price']; ?>, <?php echo $row['capacity']; ?>, '<?php echo $row['inclusions']; ?>', '<?php echo $row['image']; ?>')"
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

          <!-- Add Room Modal -->
          <div id="addRoomModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2">
              <div class="bg-blue-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Add New Room</h3>
              </div>
              <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                <div class="grid grid-cols-2 gap-6">
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Room Name
                    </label>
                    <input type="text" name="room_name" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Price (₱)
                    </label>
                    <input type="number" name="price" step="0.01" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Capacity (persons)
                    </label>
                    <input type="number" name="capacity" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Inclusions
                    </label>
                    <textarea name="inclusions" rows="3" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                  </div>
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Room Image
                    </label>
                    <input type="file" name="image" accept="image/*" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                  <button type="button" onclick="document.getElementById('addRoomModal').classList.add('hidden')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                  </button>
                  <button type="submit" name="add_room"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Add Room
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Edit Room Modal -->
          <div id="editRoomModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2">
              <div class="bg-yellow-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Edit Room</h3>
              </div>
              <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                <input type="hidden" name="id" id="editRoomId">
                <div class="grid grid-cols-2 gap-6">
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Room Name
                    </label>
                    <input type="text" name="room_name" id="editRoomName" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Price (₱)
                    </label>
                    <input type="number" name="price" id="editRoomPrice" step="0.01" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Capacity (persons)
                    </label>
                    <input type="number" name="capacity" id="editRoomCapacity" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
                  </div>
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Inclusions
                    </label>
                    <textarea name="inclusions" id="editRoomInclusions" rows="3" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500"></textarea>
                  </div>
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Room Image
                    </label>
                    <input type="file" name="image" accept="image/*"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <input type="hidden" name="current_image" id="editRoomCurrentImage">
                  </div>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                  <button type="button" onclick="document.getElementById('editRoomModal').classList.add('hidden')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                  </button>
                  <button type="submit" name="edit_room"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Update Room
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
  function confirmDelete(roomId) {
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
        window.location.href = `?delete=${roomId}`;
      }
    });
  }

  function editRoom(id, name, price, capacity, inclusions, currentImage) {
    document.getElementById('editRoomId').value = id;
    document.getElementById('editRoomName').value = name;
    document.getElementById('editRoomPrice').value = price;
    document.getElementById('editRoomCapacity').value = capacity;
    document.getElementById('editRoomInclusions').value = inclusions;
    document.getElementById('editRoomCurrentImage').value = currentImage;
    document.getElementById('editRoomModal').classList.remove('hidden');
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    let modal = document.getElementById('editRoomModal');
    if (event.target == modal) {
      modal.classList.add('hidden');
    }
  }
  </script>
</body>

</html>