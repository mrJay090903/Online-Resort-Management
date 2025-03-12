<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index');
    exit();
}

// Get customer's bookings
$customer_id = $_SESSION['customer_id'];
$bookings_query = "
    SELECT 
        b.*,
        GROUP_CONCAT(DISTINCT r.room_name) as rooms,
        GROUP_CONCAT(DISTINCT v.name) as venues
    FROM bookings b
    LEFT JOIN booking_rooms br ON b.id = br.booking_id
    LEFT JOIN rooms r ON br.room_id = r.id
    LEFT JOIN booking_venues bv ON b.id = bv.booking_id
    LEFT JOIN venues v ON bv.venue_id = v.id
    WHERE b.customer_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC";

$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Include flatpickr for date picking -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link href="src/output.css" rel="stylesheet">
</head>

<body class="bg-gray-50">
  <?php include('components/header.php'); ?>

  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">My Bookings</h1>

    <div class="grid gap-6">
      <?php while ($booking = $bookings->fetch_assoc()): ?>
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-lg font-semibold">Booking #<?php echo $booking['booking_number']; ?></h3>
            <p class="text-gray-600">
              <?php if ($booking['rooms']): ?>
              <span class="block">Rooms: <?php echo $booking['rooms']; ?></span>
              <?php endif; ?>
              <?php if ($booking['venues']): ?>
              <span class="block">Venues: <?php echo $booking['venues']; ?></span>
              <?php endif; ?>
            </p>
            <div class="mt-2">
              <p class="text-sm">Check-in: <?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?></p>
              <p class="text-sm">Check-out: <?php echo date('M j, Y', strtotime($booking['check_out_date'])); ?></p>
            </div>
            <?php if ($booking['status'] === 'reschedule' && $booking['reschedule_status'] === 'pending'): ?>
            <div class="mt-2 text-sm text-orange-600">
              Reschedule requested for: <?php echo date('M j, Y', strtotime($booking['reschedule_date_in'])); ?>
              to <?php echo date('M j, Y', strtotime($booking['reschedule_date_out'])); ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="text-right">
            <span class="inline-block px-3 py-1 rounded-full text-sm
                            <?php
                            switch($booking['status']) {
                                case 'pending':
                                    echo 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'confirmed':
                                    echo 'bg-green-100 text-green-800';
                                    break;
                                case 'reschedule':
                                    echo 'bg-orange-100 text-orange-800';
                                    break;
                                case 'completed':
                                    echo 'bg-blue-100 text-blue-800';
                                    break;
                            }
                            ?>">
              <?php echo ucfirst($booking['status']); ?>
            </span>
            <p class="mt-2 font-semibold">₱<?php echo number_format($booking['total_amount'], 2); ?></p>
          </div>
        </div>

        <?php if ($booking['status'] === 'confirmed'): ?>
        <div class="mt-4 flex justify-end space-x-2">
          <button onclick="openRescheduleModal(<?php echo $booking['id']; ?>)"
            class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 transition-colors">
            Request Reschedule
          </button>
        </div>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Reschedule Modal -->
  <div id="rescheduleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h2 class="text-xl font-semibold mb-4">Request Reschedule</h2>
      <form id="rescheduleForm" onsubmit="submitReschedule(event)">
        <input type="hidden" id="booking_id" name="booking_id">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">New Check-in Date</label>
          <input type="date" id="reschedule_date_in" name="reschedule_date_in" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">New Check-out Date</label>
          <input type="date" id="reschedule_date_out" name="reschedule_date_out" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeRescheduleModal()"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
            Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function openRescheduleModal(bookingId) {
    document.getElementById('booking_id').value = bookingId;
    document.getElementById('rescheduleModal').classList.remove('hidden');
    document.getElementById('rescheduleModal').classList.add('flex');
  }

  function closeRescheduleModal() {
    document.getElementById('rescheduleModal').classList.add('hidden');
    document.getElementById('rescheduleModal').classList.remove('flex');
  }

  function submitReschedule(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    fetch('../handlers/reschedule_booking', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: data.message,
            showConfirmButton: false,
            timer: 1500
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: data.message
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'An error occurred while processing your request.'
        });
      });
  }

  // Initialize date pickers
  flatpickr("#reschedule_date_in", {
    minDate: "today",
    onChange: function(selectedDates) {
      // Update the minimum date of check-out to be after check-in
      checkOutPicker.set("minDate", selectedDates[0]);
    }
  });

  const checkOutPicker = flatpickr("#reschedule_date_out", {
    minDate: "today"
  });
  </script>
</body>

</html>