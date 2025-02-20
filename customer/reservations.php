<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Make a Reservation - Casita De Grands</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/second-quotes" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
  <?php include('components/nav.php'); ?>

  <!-- Booking Section -->
  <section class="pt-24 pb-16">
    <div class="container mx-auto px-6">
      <h2 class="text-4xl font-['Second_Quotes'] text-gray-800 mb-2 text-center">Make a Reservation</h2>
      <p class="text-gray-500 uppercase text-xs tracking-[0.5em] mb-12 text-center font-['Raleway']">Book Your Stay</p>

      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
        <form id="bookingForm" action="../handlers/booking_handler.php" method="POST" class="space-y-6">
          <!-- Dates Selection -->
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
              <input type="date" name="check_in" required min="<?php echo date('Y-m-d'); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
              <input type="date" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
          </div>

          <!-- Number of Guests -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Guests</label>
            <input type="number" name="guests" min="1" required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          </div>

          <!-- Rooms Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Rooms</label>
            <div class="grid md:grid-cols-2 gap-6">
              <?php
                            $rooms_query = "SELECT * FROM rooms WHERE status = 'available'";
                            $rooms_result = $conn->query($rooms_query);
                            while ($room = $rooms_result->fetch_assoc()):
                            ?>
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="aspect-w-16 aspect-h-9 mb-4">
                  <img src="../uploads/rooms/<?php echo htmlspecialchars($room['picture']); ?>"
                    alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                    class="object-cover rounded-lg w-full h-48">
                </div>
                <h3 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($room['description']); ?></p>
                <p class="text-sm mb-2">Capacity: <?php echo $room['capacity']; ?> persons</p>

                <!-- Price Display -->
                <div class="mb-4 space-y-2">
                  <p class="text-sm font-medium text-gray-600">Rates:</p>
                  <div class="flex justify-between items-center px-3 py-2 bg-white rounded">
                    <span class="text-sm">Day Use (8AM - 5PM)</span>
                    <span class="font-semibold">₱<?php echo number_format($room['day_price'], 2); ?></span>
                  </div>
                  <div class="flex justify-between items-center px-3 py-2 bg-white rounded">
                    <span class="text-sm">Night Use (6PM - 7AM)</span>
                    <span class="font-semibold">₱<?php echo number_format($room['night_price'], 2); ?></span>
                  </div>
                </div>

                <!-- Room Selection -->
                <div class="space-y-3">
                  <!-- Day Use -->
                  <div class="flex items-center justify-between bg-white p-2 rounded">
                    <label class="text-sm text-gray-700">Day Use:</label>
                    <div class="flex items-center space-x-2">
                      <input type="number" name="room[<?php echo $room['id']; ?>][day]" min="0" value="0"
                        class="w-16 px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                  </div>

                  <!-- Night Use -->
                  <div class="flex items-center justify-between bg-white p-2 rounded">
                    <label class="text-sm text-gray-700">Night Use:</label>
                    <div class="flex items-center space-x-2">
                      <input type="number" name="room[<?php echo $room['id']; ?>][night]" min="0" value="0"
                        class="w-16 px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                  </div>
                </div>
              </div>
              <?php endwhile; ?>
            </div>
          </div>

          <!-- Venues Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Venues</label>
            <div class="grid md:grid-cols-2 gap-6">
              <?php
                            $venues_query = "SELECT * FROM venues WHERE status = 'available'";
                            $venues_result = $conn->query($venues_query);
                            while ($venue = $venues_result->fetch_assoc()):
                            ?>
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="aspect-w-16 aspect-h-9 mb-4">
                  <img src="../uploads/venues/<?php echo htmlspecialchars($venue['picture']); ?>"
                    alt="<?php echo htmlspecialchars($venue['name']); ?>" class="object-cover rounded-lg w-full h-48">
                </div>
                <h3 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($venue['name']); ?></h3>
                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($venue['description']); ?></p>
                <p class="text-sm mb-2">Capacity: <?php echo $venue['capacity']; ?> persons</p>
                <p class="font-semibold mb-3">₱<?php echo number_format($venue['price'], 2); ?></p>
                <div class="flex items-center">
                  <input type="checkbox" name="venue[<?php echo $venue['id']; ?>]" value="1"
                    class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                  <label class="ml-2 text-sm text-gray-700">Select this venue</label>
                </div>
              </div>
              <?php endwhile; ?>
            </div>
          </div>

          <button type="submit"
            class="w-full bg-emerald-600 text-white py-3 rounded-lg hover:bg-emerald-700 transition-colors duration-200">
            Proceed to Book
          </button>
        </form>
      </div>
    </div>
  </section>

  <?php include('components/footer.php'); ?>

  <!-- Copy the booking-related JavaScript from customer_dashboard.php -->
  <script>
  // ... Booking JavaScript code ...
  </script>

  <!-- Add this at the bottom of the file, before </body> -->
  <script>
  // Copy the booking form validation and submission JavaScript from customer_dashboard.php
  document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const checkIn = new Date(formData.get('check_in'));
    const checkOut = new Date(formData.get('check_out'));
    const guests = parseInt(formData.get('guests'));

    // Validation
    if (checkIn >= checkOut) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid Dates',
        text: 'Check-out date must be after check-in date',
        confirmButtonColor: '#059669'
      });
      return;
    }

    let hasSelection = false;
    let totalGuests = 0;

    // Check room selections
    document.querySelectorAll('input[type="number"]').forEach(input => {
      if (input.name.includes('room') && parseInt(input.value) > 0) {
        hasSelection = true;
        // Calculate total guests based on room capacity
        const roomCapacity = parseInt(input.closest('.bg-gray-50').querySelector('.mb-2').textContent.match(
          /\d+/)[0]);
        totalGuests += roomCapacity * parseInt(input.value);
      }
    });

    if (!hasSelection && !document.querySelector('input[type="checkbox"]:checked')) {
      Swal.fire({
        icon: 'error',
        title: 'No Selection',
        text: 'Please select at least one room or venue',
        confirmButtonColor: '#059669'
      });
      return;
    }

    if (totalGuests < guests) {
      Swal.fire({
        icon: 'error',
        title: 'Insufficient Capacity',
        text: 'The selected rooms cannot accommodate all guests. Please select more rooms.',
        confirmButtonColor: '#059669'
      });
      return;
    }

    // Show confirmation
    Swal.fire({
      title: 'Confirm Booking',
      text: 'Would you like to proceed with this booking?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#059669',
      cancelButtonColor: '#dc2626',
      confirmButtonText: 'Yes, proceed'
    }).then((result) => {
      if (result.isConfirmed) {
        // Submit booking
        fetch(this.action, {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Booking Submitted!',
                text: data.message,
                confirmButtonColor: '#059669'
              }).then(() => {
                window.location.href = 'my_bookings.php';
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Booking Failed',
                text: data.message,
                confirmButtonColor: '#059669'
              });
            }
          })
          .catch(error => {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'An error occurred. Please try again.',
              confirmButtonColor: '#059669'
            });
          });
      }
    });
  });

  // Date validation
  document.querySelector('input[name="check_in"]').addEventListener('change', function() {
    const checkOutInput = document.querySelector('input[name="check_out"]');
    checkOutInput.min = this.value;
    if (checkOutInput.value && checkOutInput.value <= this.value) {
      checkOutInput.value = '';
    }
  });
  </script>
</body>

</html>