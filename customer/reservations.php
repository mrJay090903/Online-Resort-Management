<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
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
  <!-- Add SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
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
        <!-- Search Form -->
        <form id="searchForm" class="space-y-6 mb-8">
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
              <input type="date" name="check_in" id="check_in" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
              <input type="date" name="check_out" id="check_out" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Number of Guests</label>
              <input type="number" name="guests" min="1" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Booking Type</label>
              <select name="booking_type" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">Select booking type</option>
                <option value="day">Day Use (8AM - 5PM)</option>
                <option value="night">Night Use (6PM - 7AM)</option>
              </select>
            </div>
          </div>

          <button type="submit"
            class="w-full bg-emerald-600 text-white py-3 rounded-lg hover:bg-emerald-700 transition-colors duration-200">
            Search Available Rooms & Venues
          </button>
        </form>

        <!-- Results Section (Initially Hidden) -->
        <div id="searchResults" class="hidden space-y-8">
          <form id="bookingForm" class="space-y-6">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="check_in" id="booking_check_in">
            <input type="hidden" name="check_out" id="booking_check_out">
            <input type="hidden" name="guests" id="booking_guests">
            <input type="hidden" name="booking_type" id="booking_type">

            <!-- Available Rooms Section -->
            <div id="availableRooms" class="space-y-4">
              <h3 class="text-lg font-semibold text-gray-800">Available Rooms</h3>
              <div class="grid md:grid-cols-2 gap-6">
                <!-- Rooms will be populated here -->
              </div>
            </div>

            <!-- Available Venues Section -->
            <div id="availableVenues" class="space-y-4">
              <h3 class="text-lg font-semibold text-gray-800">Available Venues</h3>
              <div class="grid md:grid-cols-2 gap-6">
                <!-- Venues will be populated here -->
              </div>
            </div>

            <button type="submit"
              class="w-full bg-emerald-600 text-white py-3 rounded-lg hover:bg-emerald-700 transition-colors duration-200">
              Proceed to Book
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <?php include('components/footer.php'); ?>

  <script>
  document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    // Get and validate dates
    const checkInDate = new Date(formData.get('check_in'));
    const checkOutDate = new Date(formData.get('check_out'));
    
    // Validate dates
    if (isNaN(checkInDate.getTime()) || isNaN(checkOutDate.getTime())) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Dates',
            text: 'Please select valid check-in and check-out dates',
            confirmButtonColor: '#059669'
        });
        return;
    }

    // Format dates as YYYY-MM-DD
    const checkIn = checkInDate.toISOString().split('T')[0];
    const checkOut = checkOutDate.toISOString().split('T')[0];
    
    // Update hidden fields in booking form
    document.getElementById('booking_check_in').value = checkIn;
    document.getElementById('booking_check_out').value = checkOut;
    document.getElementById('booking_guests').value = formData.get('guests');
    document.getElementById('booking_type').value = formData.get('booking_type');

    console.log('Search form dates:', { 
        checkIn, 
        checkOut, 
        guests: formData.get('guests'),
        bookingType: formData.get('booking_type')
    });

    // Show loading state
    Swal.fire({
      title: 'Searching...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Fetch available rooms and venues
    fetch('../handlers/search_availability.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        Swal.close();

        if (data.success) {
          // Show results section
          document.getElementById('searchResults').classList.remove('hidden');

          // Populate rooms
          const roomsContainer = document.querySelector('#availableRooms .grid');
          roomsContainer.innerHTML = data.rooms.map(room => `
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="aspect-w-16 aspect-h-9 mb-4">
              <img src="../uploads/rooms/${room.picture}" alt="${room.room_name}" 
                   class="object-cover rounded-lg w-full h-48">
            </div>
            <h3 class="font-semibold text-lg mb-2">${room.room_name}</h3>
            <p class="text-sm text-gray-600 mb-2">${room.description}</p>
            <p class="text-sm mb-2">Capacity: ${room.capacity} persons</p>
            
            <!-- Price Display -->
            <div class="mb-4">
              <p class="font-semibold text-lg">
                ₱${data.booking_type === 'day' ? 
                  room.day_price.toLocaleString() : 
                  room.night_price.toLocaleString()}
                <span class="text-sm font-normal text-gray-600">
                  ${data.booking_type === 'day' ? '(8AM - 5PM)' : '(6PM - 7AM)'}
                </span>
              </p>
            </div>

            <!-- Room Selection Radio Button -->
            <div class="flex items-center space-x-2 p-3 bg-white rounded-lg border border-gray-200">
              <input type="radio" 
                     name="selected_room" 
                     value="${room.id}" 
                     id="room_${room.id}"
                     class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500"
                     data-capacity="${room.capacity}">
              <label for="room_${room.id}" class="text-sm text-gray-700">Select this room</label>
            </div>
          </div>
        `).join('');

          // Populate venues
          const venuesContainer = document.querySelector('#availableVenues .grid');
          venuesContainer.innerHTML = data.venues.map(venue => `
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="aspect-w-16 aspect-h-9 mb-4">
              <img src="../uploads/venues/${venue.picture}" alt="${venue.name}" class="object-cover rounded-lg w-full h-48">
            </div>
            <h3 class="font-semibold text-lg mb-2">${venue.name}</h3>
            <p class="text-sm text-gray-600 mb-2">${venue.description}</p>
            <p class="text-sm mb-2">Capacity: ${venue.capacity} persons</p>
            <p class="font-semibold mb-3">₱${venue.price}</p>
            <div class="flex items-center space-x-2 p-3 bg-white rounded-lg border border-gray-200">
              <input type="checkbox" 
                     name="venue_${venue.id}" 
                     value="${venue.id}"
                     id="venue_${venue.id}"
                     data-capacity="${venue.capacity}"
                     class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
              <label for="venue_${venue.id}" class="text-sm text-gray-700">Select this venue</label>
            </div>
          </div>
        `).join('');

          // Scroll to results
          document.getElementById('searchResults').scrollIntoView({
            behavior: 'smooth'
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'No Available Rooms',
            text: data.message || 'No rooms available for the selected criteria.',
            confirmButtonColor: '#059669'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred while searching. Please try again.',
          confirmButtonColor: '#059669'
        });
      });
  });

  // Update the booking form submission handler
  document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();

    // Get values from hidden fields
    const checkIn = document.getElementById('booking_check_in').value;
    const checkOut = document.getElementById('booking_check_out').value;
    const guests = document.getElementById('booking_guests').value;
    const bookingType = document.getElementById('booking_type').value;

    // Validate all required fields
    if (!checkIn || !checkOut || !guests || !bookingType) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#059669'
        });
        return;
    }

    console.log('Booking form data:', { 
        checkIn, 
        checkOut, 
        guests, 
        bookingType 
    });

    // Add to formData
    formData.append('check_in', checkIn);
    formData.append('check_out', checkOut);
    formData.append('guests', guests);
    formData.append('booking_type', bookingType);
    formData.append('user_id', <?php echo $user_id; ?>);

    // Check if either a room or venue is selected
    const selectedRoom = document.querySelector('input[name="selected_room"]:checked');
    const selectedVenues = Array.from(document.querySelectorAll('input[name^="venue_"]:checked'));

    if (!selectedRoom && selectedVenues.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'No Selection',
            text: 'Please select at least one room or venue to continue.',
            confirmButtonColor: '#059669'
        });
        return;
    }

    // Calculate total capacity and gather selections
    let totalCapacity = 0;
    const selections = {
        room: selectedRoom ? {
            id: selectedRoom.value,
            capacity: parseInt(selectedRoom.dataset.capacity)
        } : null,
        venues: selectedVenues.map(venue => ({
            id: venue.value,
            capacity: parseInt(venue.dataset.capacity)
        }))
    };

    // Calculate total capacity
    if (selections.room) {
        totalCapacity += selections.room.capacity;
    }
    selections.venues.forEach(venue => {
        totalCapacity += venue.capacity;
    });

    // Check if total capacity can accommodate guests
    if (totalCapacity < parseInt(guests)) {
        Swal.fire({
            icon: 'error',
            title: 'Insufficient Capacity',
            text: 'The selected room/venues cannot accommodate all guests. Please select more or reduce guest count.',
            confirmButtonColor: '#059669'
        });
        return;
    }

    // Show confirmation dialog with selections
    Swal.fire({
        title: 'Confirm Booking',
        html: `
            <div class="text-left">
                <p class="mb-2"><strong>Check-in:</strong> ${checkIn}</p>
                <p class="mb-2"><strong>Check-out:</strong> ${checkOut}</p>
                <p class="mb-2"><strong>Guests:</strong> ${guests}</p>
                <p class="mb-2"><strong>Type:</strong> ${bookingType === 'day' ? 'Day Use (8AM-5PM)' : 'Night Use (6PM-7AM)'}</p>
                <p class="text-sm text-gray-600 mt-4">* A 50% down payment will be required to confirm your booking.</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Proceed to Payment',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Add selections to form data
            formData.append('selected_room', selectedRoom ? selectedRoom.value : '');
            formData.append('selected_venues', JSON.stringify(selectedVenues.map(v => v.value)));

            // Submit booking and proceed to payment
            fetch('../handlers/booking_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to payment page
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'payment.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'booking_data';
                    input.value = JSON.stringify(data.booking_data);

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    throw new Error(data.message || 'Failed to create booking');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'An error occurred. Please try again.',
                    confirmButtonColor: '#059669'
                });
            });
        }
    });
  });

  // Update the date change handlers
  document.querySelector('input[name="check_in"]').addEventListener('change', function() {
    const checkOutInput = document.querySelector('input[name="check_out"]');
    const nextDay = new Date(this.value);
    nextDay.setDate(nextDay.getDate() + 1);
    const minCheckOut = nextDay.toISOString().split('T')[0];
    
    checkOutInput.min = minCheckOut;
    if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(this.value)) {
        checkOutInput.value = minCheckOut;
        // Update hidden field
        document.getElementById('booking_check_out').value = minCheckOut;
    }
    // Update hidden field
    document.getElementById('booking_check_in').value = this.value;
  });

  document.querySelector('input[name="check_out"]').addEventListener('change', function() {
    // Update hidden field
    document.getElementById('booking_check_out').value = this.value;
  });
  </script>
</body>

</html>