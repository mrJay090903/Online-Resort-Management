<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// At the top of the file, add:
if (!file_exists('../uploads/venues')) {
    mkdir('../uploads/venues', 0777, true);
}

// Handle venue operations (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $picture_name = '';
        
        // Handle file upload
        if (isset($_FILES['venue_picture']) && $_FILES['venue_picture']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['venue_picture']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $picture_name = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['venue_picture']['tmp_name'], '../uploads/venues/' . $picture_name);
            }
        }
        
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO venues (type, name, description, capacity, price, picture, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiiss", $_POST['type'], $_POST['name'], $_POST['description'], $_POST['capacity'], $_POST['price'], $picture_name, $_POST['status']);
                break;
                
            case 'edit':
                $update_fields = "type=?, name=?, description=?, capacity=?, price=?, status=?";
                $params = [$_POST['type'], $_POST['name'], $_POST['description'], $_POST['capacity'], $_POST['price'], $_POST['status']];
                $types = "sssiis";
                
                if (!empty($picture_name)) {
                    $update_fields .= ", picture=?";
                    $params[] = $picture_name;
                    $types .= "s";
                }
                
                $params[] = $_POST['venue_id'];
                $types .= "i";
                
                $stmt = $conn->prepare("UPDATE venues SET {$update_fields} WHERE id=?");
                $stmt->bind_param($types, ...$params);
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM venues WHERE id=?");
                $stmt->bind_param("i", $_POST['venue_id']);
                break;
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = true;
            $_SESSION['message'] = "Venue " . ($_POST['action'] === 'add' ? 'added' : ($_POST['action'] === 'edit' ? 'updated' : 'deleted')) . " successfully!";
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        
        header('Location: venues.php');
        exit();
    }
}

// Fetch all venues
$sql = "SELECT * FROM venues ORDER BY type, name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Venues - Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <h1 class="text-2xl font-semibold text-gray-900">Manage Venues</h1>
            <button onclick="openAddModal()"
              class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
              Add New Venue
            </button>
          </div>

          <!-- Skeleton Loading Screen -->
          <div id="loading" class="grid md:grid-cols-3 gap-6">
            <?php for($i = 0; $i < 6; $i++): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
              <div class="w-full h-48 bg-gray-200"></div>
              <div class="p-4">
                <div class="h-6 bg-gray-200 rounded w-1/2 mb-2"></div>
                <div class="h-5 bg-gray-200 rounded w-20 mb-4"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
                <div class="h-8 bg-gray-200 rounded w-16"></div>
              </div>
            </div>
            <?php endfor; ?>
          </div>

          <!-- Venues Grid -->
          <div id="venues-grid" class="hidden grid md:grid-cols-3 gap-6">
            <?php while ($venue = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <img src="../uploads/venues/<?php echo htmlspecialchars($venue['picture']); ?>"
                alt="<?php echo htmlspecialchars($venue['name']); ?>" class="w-full h-48 object-cover">

              <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($venue['name']); ?></h3>
                    <span class="text-sm text-gray-500"><?php echo ucfirst($venue['type']); ?></span>
                  </div>
                  <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        <?php echo $venue['status'] === 'available' ? 'bg-green-100 text-green-800' : 
                                                ($venue['status'] === 'occupied' ? 'bg-red-100 text-red-800' : 
                                                'bg-yellow-100 text-yellow-800'); ?>">
                    <?php echo ucfirst($venue['status']); ?>
                  </span>
                </div>

                <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($venue['description']); ?></p>

                <div class="space-y-2 text-sm">
                  <p>Capacity: <?php echo $venue['capacity']; ?> persons</p>
                  <p>Price: ₱<?php echo number_format($venue['price'], 2); ?></p>
                </div>

                <div class="mt-4 flex justify-end space-x-2">
                  <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($venue)); ?>)"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Edit
                  </button>
                  <button onclick="confirmDelete(<?php echo $venue['id']; ?>)"
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

  <!-- Add/Edit Venue Modal -->
  <div id="venueModal"
    class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2">
      <!-- Modal Header -->
      <div class="bg-emerald-600 text-white p-4 rounded-t-lg">
        <h3 class="text-lg font-semibold" id="modalTitle">Add New Venue</h3>
      </div>

      <!-- Modal Body -->
      <form id="venueForm" method="POST" enctype="multipart/form-data" class="p-6">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="venue_id" id="venueId">

        <div class="grid grid-cols-2 gap-6">
          <!-- Left Column -->
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Type</label>
              <select name="type" id="type" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="cottage">Cottage</option>
                <option value="hall">Hall</option>
              </select>
            </div>

            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
              <input type="text" name="name" id="name" required
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
              <label class="block text-gray-700 text-sm font-bold mb-2">Price</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">₱</span>
                <input type="number" name="price" id="price" required min="0" step="0.01"
                  class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-emerald-500">
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
              <input type="file" name="venue_picture" id="venuePicture" accept="image/*"
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
    document.getElementById('modalTitle').textContent = 'Add New Venue';
    document.getElementById('formAction').value = 'add';
    document.getElementById('venueForm').reset();
    document.getElementById('venueModal').classList.remove('hidden');
  }

  function openEditModal(venue) {
    document.getElementById('modalTitle').textContent = 'Edit Venue';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('venueId').value = venue.id;
    document.getElementById('type').value = venue.type;
    document.getElementById('name').value = venue.name;
    document.getElementById('description').value = venue.description;
    document.getElementById('capacity').value = venue.capacity;
    document.getElementById('price').value = venue.price;
    document.getElementById('status').value = venue.status;
    document.getElementById('venueModal').classList.remove('hidden');
  }

  function closeModal() {
    document.getElementById('venueModal').classList.add('hidden');
  }

  function confirmDelete(venueId) {
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
                        <input type="hidden" name="venue_id" value="${venueId}">
                    `;
        document.body.appendChild(form);
        form.submit();
      }
    });
  }

  document.getElementById('venuePicture').addEventListener('change', function(e) {
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

  // Show loading screen initially
  document.getElementById('loading').style.display = 'grid';
  document.getElementById('venues-grid').style.display = 'none';

  // Simulate data loading
  window.addEventListener('load', function() {
    setTimeout(() => {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('venues-grid').style.display = 'grid';
    }, 1500); // Show loading for 1.5 seconds
  });
  </script>
</body>

</html>