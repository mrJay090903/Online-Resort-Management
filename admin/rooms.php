<?php
// Add this at the top of your file temporarily for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
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

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// At the top of the file, add:
if (!file_exists('../assets/rooms')) {
    mkdir('../assets/rooms', 0777, true);
}

// Handle room operations (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $picture_name = '';
        
        // Handle file upload
        if (isset($_FILES['room_picture']) && $_FILES['room_picture']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['room_picture']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $picture_name = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['room_picture']['tmp_name'], '../uploads/rooms/' . $picture_name);
            }
        }
        
        switch ($_POST['action']) {
            case 'add':
                if (empty($picture_name)) {
                    $_SESSION['success'] = false;
                    $_SESSION['message'] = "Please upload a room picture";
                    header('Location: rooms.php');
                    exit();
                }
                
                $stmt = $conn->prepare("INSERT INTO rooms (room_name, description, capacity, base_price, day_price, night_price, picture, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiiddss", $_POST['room_name'], $_POST['description'], $_POST['capacity'], $_POST['base_price'], $_POST['day_price'], $_POST['night_price'], $picture_name, $_POST['status']);
                break;
                
            case 'edit':
                $update_fields = "room_name=?, description=?, capacity=?, base_price=?, day_price=?, night_price=?, status=?";
                $params = [$_POST['room_name'], $_POST['description'], $_POST['capacity'], $_POST['base_price'], $_POST['day_price'], $_POST['night_price'], $_POST['status']];
                $types = "ssiidds";
                
                if (!empty($picture_name)) {
                    $update_fields .= ", picture=?";
                    $params[] = $picture_name;
                    $types .= "s";
                }
                
                $params[] = $_POST['room_id'];
                $types .= "i";
                
                $stmt = $conn->prepare("UPDATE rooms SET {$update_fields} WHERE id=?");
                $stmt->bind_param($types, ...$params);
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM rooms WHERE id=?");
                $stmt->bind_param("i", $_POST['room_id']);
                break;
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = true;
            $_SESSION['message'] = "Room " . ($_POST['action'] === 'add' ? 'added' : ($_POST['action'] === 'edit' ? 'updated' : 'deleted')) . " successfully!";
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        
        header('Location: rooms.php');
        exit();
    }
}

// Fetch all rooms
$sql = "SELECT * FROM rooms ORDER BY room_name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Rooms - Admin Dashboard</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.lordicon.com/bhenfmcm.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
</head>

<body class="bg-gray-50">
  <div class="flex">
    <?php include('components/sidebar.php'); ?>

    <div class="flex-1">
      <?php include('components/header.php'); ?>

      <main class="p-8">
        <div class="max-w-7xl mx-auto">
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Manage Rooms</h1>
            <button onclick="openAddModal()"
              class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
              Add New Room
            </button>
          </div>

          <!-- Rooms Grid -->
          <div class="grid md:grid-cols-3 gap-6">
            <?php while ($room = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <img src="../uploads/rooms/<?php echo htmlspecialchars($room['picture']); ?>"
                alt="<?php echo htmlspecialchars($room['room_name']); ?>" class="w-full h-48 object-cover">

              <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                  <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                  <span class="px-2 py-1 rounded-full text-xs font-semibold
                      <?php echo $room['status'] === 'available' ? 'bg-green-100 text-green-800' : 
                              ($room['status'] === 'occupied' ? 'bg-red-100 text-red-800' : 
                              'bg-yellow-100 text-yellow-800'); ?>">
                    <?php echo ucfirst($room['status']); ?>
                  </span>
                </div>

                <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($room['description']); ?></p>

                <div class="space-y-2 text-sm">
                  <p>Capacity: <?php echo $room['capacity']; ?> persons</p>
                  <p>Base Price: ₱<?php echo number_format($room['base_price'], 2); ?></p>
                  <p>Day Price: ₱<?php echo number_format($room['day_price'], 2); ?></p>
                  <p>Night Price: ₱<?php echo number_format($room['night_price'], 2); ?></p>
                </div>

                <div class="mt-4 flex justify-end space-x-2">
                  <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($room)); ?>)"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Edit
                  </button>
                  <button onclick="confirmDelete(<?php echo $room['id']; ?>)"
                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                    Delete
                  </button>
                </div>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Add/Edit Room Modal -->
  <div id="roomModal"
    class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2">
      <!-- Modal Header -->
      <div class="bg-emerald-600 text-white p-4 rounded-t-lg">
        <h3 class="text-lg font-semibold" id="modalTitle">Add New Room</h3>
      </div>

      <!-- Modal Body -->
      <form id="roomForm" method="POST" enctype="multipart/form-data" class="p-6">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="room_id" id="roomId">

        <div class="grid grid-cols-2 gap-6">
          <!-- Left Column -->
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Room Name</label>
              <input type="text" name="room_name" id="roomName" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
              <textarea name="description" id="description" rows="3" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Capacity</label>
              <input type="number" name="capacity" id="capacity" required min="1"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Base Price</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">₱</span>
                <input type="number" name="base_price" id="basePrice" required min="0" step="0.01"
                  class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Day Price</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">₱</span>
                  <input type="number" name="day_price" id="dayPrice" required min="0" step="0.01"
                    class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
              </div>
              <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Night Price</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">₱</span>
                  <input type="number" name="night_price" id="nightPrice" required min="0" step="0.01"
                    class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
              </div>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
              <select name="status" id="status" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Picture</label>
              <input type="file" name="room_picture" id="roomPicture" accept="image/*"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <div id="picturePreview" class="mt-2 hidden">
                <img src="" alt="Preview" class="h-32 w-auto rounded">
              </div>
            </div>
          </div>
        </div>

        <!-- Form Buttons -->
        <div class="flex justify-end gap-4 mt-6">
          <button type="button" onclick="closeModal()"
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
            Cancel
          </button>
          <button type="submit"
            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Room';
    document.getElementById('formAction').value = 'add';
    document.getElementById('roomForm').reset();
    document.getElementById('roomModal').classList.remove('hidden');
  }

  function openEditModal(room) {
    document.getElementById('modalTitle').textContent = 'Edit Room';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('roomId').value = room.id;
    document.getElementById('roomName').value = room.room_name;
    document.getElementById('description').value = room.description;
    document.getElementById('capacity').value = room.capacity;
    document.getElementById('basePrice').value = room.base_price;
    document.getElementById('dayPrice').value = room.day_price;
    document.getElementById('nightPrice').value = room.night_price;
    document.getElementById('status').value = room.status;
    document.getElementById('roomModal').classList.remove('hidden');
  }

  function closeModal() {
    document.getElementById('roomModal').classList.add('hidden');
  }

  function confirmDelete(roomId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#059669',
      cancelButtonColor: '#dc2626',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="room_id" value="${roomId}">
          `;
        document.body.appendChild(form);
        form.submit();
      }
    });
  }

  document.getElementById('roomPicture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('picturePreview');
    const previewImg = preview.querySelector('img');

    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        previewImg.src = e.target.result;
        preview.classList.remove('hidden');
      }
      reader.readAsDataURL(file);
    } else {
      preview.classList.add('hidden');
    }
  });

  const notyf = new Notyf({
    duration: 3000,
    position: {
      x: 'right',
      y: 'top',
    },
    types: [{
        type: 'success',
        background: '#059669',
        icon: false
      },
      {
        type: 'error',
        background: '#DC2626',
        icon: false
      }
    ]
  });

  <?php if (isset($_SESSION['message'])): ?>
  notyf.<?php echo $_SESSION['success'] ? 'success' : 'error'; ?>('<?php echo $_SESSION['message']; ?>');
  <?php 
    unset($_SESSION['message']);
    unset($_SESSION['success']);
  endif; ?>

  window.onclick = function(event) {
    let modal = document.getElementById('roomModal');
    if (event.target == modal) {
        closeModal();
    }
  }
  </script>
</body>

</html>