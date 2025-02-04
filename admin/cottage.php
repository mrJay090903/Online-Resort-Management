<?php
include('../db.php');
session_start();

// Add this at the beginning of your PHP code
if (!file_exists('../uploads/cottages')) {
    mkdir('../uploads/cottages', 0777, true);
}

// Add new cottage/hall
if (isset($_POST['add_cottage'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    
    // Handle image upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $new_filename = time() . '.' . $filetype;
            $upload_path = '../uploads/cottages/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
            } else {
                $_SESSION['success'] = false;
                $_SESSION['message'] = "Error uploading image. Error: " . $_FILES['image']['error'];
                header("Location: cottage.php");
                exit();
            }
        } else {
            $_SESSION['success'] = false;
            $_SESSION['message'] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            header("Location: cottage.php");
            exit();
        }
    }
    
    $sql = "INSERT INTO cottages (name, price, capacity, description, type, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdisss", $name, $price, $capacity, $description, $type, $image);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = ucfirst($type) . " added successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error adding " . $type;
    }
    header("Location: cottage.php");
    exit();
}

// Delete cottage/hall
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image filename before deleting record
    $sql = "SELECT image, type FROM cottages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cottage = $result->fetch_assoc();
    
    // Delete the record
    $sql = "DELETE FROM cottages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists
        if($cottage['image'] && file_exists('../uploads/cottages/' . $cottage['image'])) {
            unlink('../uploads/cottages/' . $cottage['image']);
        }
        $_SESSION['success'] = true;
        $_SESSION['message'] = ucfirst($cottage['type']) . " deleted successfully!";
    } else {
        $_SESSION['success'] = false;
        $_SESSION['message'] = "Error deleting " . $cottage['type'];
    }
    header("Location: cottage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cottage/Hall Management</title>
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
            <h1 class="text-2xl font-semibold text-gray-900">Cottage/Hall Management</h1>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
              class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
              <lord-icon src="https://cdn.lordicon.com/mecwbjnp.json" trigger="hover" colors="primary:#ffffff"
                style="width:24px;height:24px">
              </lord-icon>
              Add New
            </button>
          </div>

          <!-- Table -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
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
                                $sql = "SELECT * FROM cottages ORDER BY type, id DESC";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['name']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $row['type'] === 'cottage' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                      <?php echo ucfirst($row['type']); ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <p class="text-sm text-gray-900">Price: ₱<?php echo number_format($row['price'], 2); ?></p>
                    <p class="text-sm text-gray-600">Capacity: <?php echo $row['capacity']; ?> persons</p>
                    <p class="text-sm text-gray-600"><?php echo $row['description']; ?></p>
                  </td>
                  <td class="px-6 py-4">
                    <?php 
                                        $image_path = "/Online-Resort-Management/uploads/cottages/" . $row['image'];
                                        if (!empty($row['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $image_path)): ?>
                    <img src="<?php echo $image_path; ?>" alt="Image" class="h-20 w-20 object-cover rounded">
                    <?php else: ?>
                    <div class="h-20 w-20 bg-gray-200 rounded flex items-center justify-center">
                      <lord-icon src="https://cdn.lordicon.com/dnmvmpfk.json" trigger="hover" colors="primary:#9ca3af"
                        style="width:32px;height:32px">
                      </lord-icon>
                    </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button onclick="editItem(<?php echo $row['id']; ?>)"
                      class="flex items-center gap-1 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1 rounded-md mb-2">
                      <lord-icon src="https://cdn.lordicon.com/wloilxuq.json" trigger="hover" colors="primary:#ffffff"
                        style="width:20px;height:20px">
                      </lord-icon>
                    </button>
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

          <!-- Add Modal -->
          <div id="addModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2">
              <div class="bg-blue-500 text-white p-4 rounded-t-lg">
                <h3 class="text-lg font-semibold">Add New Cottage/Hall</h3>
              </div>
              <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                <div class="grid grid-cols-2 gap-6">
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Name
                    </label>
                    <input type="text" name="name" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Type
                    </label>
                    <select name="type" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="cottage">Cottage</option>
                      <option value="hall">Hall</option>
                    </select>
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
                      Description
                    </label>
                    <textarea name="description" rows="3" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                  </div>
                  <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                      Image
                    </label>
                    <input type="file" name="image" accept="image/*" required
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                  <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                  </button>
                  <button type="submit" name="add_cottage"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Add
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

  // Close modal when clicking outside
  window.onclick = function(event) {
    let modal = document.getElementById('addModal');
    if (event.target == modal) {
      modal.classList.add('hidden');
    }
  }
  </script>
</body>

</script>
</body>

</html>