<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Our Rooms - Casita Palmera</title>
  <link href="src/output.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body class="bg-gray-50">
  <?php include('components/navbar.php'); ?>

  <!-- Hero Section -->
  <div
    class="relative pt-24 pb-24 flex content-center items-center justify-center min-h-[50vh] bg-gradient-to-b from-gray-900/90 to-gray-900/70 mt-16">
    <!-- Background Image -->
    <div class="absolute top-0 w-full h-full bg-center bg-cover z-[-1]"
      style="background-image: url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop');">
      <!-- Overlay Pattern -->
      <div class="absolute inset-0 bg-black opacity-50"></div>
    </div>

    <!-- Large Watermark Text -->
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden">
      <span class="text-[8rem] font-bold text-white/5 transform -rotate-12">
        CASITA
      </span>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 relative">
      <div class="flex flex-col items-center text-center">
        <!-- Decorative Element -->
        <div class="mb-3">
          <span
            class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-300 text-xl rounded-full border border-emerald-500/20">
            Welcome to Casita De Grands
          </span>
        </div>

        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 tracking-tight">
          Our Rooms & Cottages
        </h1>

        <div class="w-20 h-1 bg-emerald-500 rounded mb-6"></div>

        <p class="text-lg md:text-xl text-white max-w-xl font-light">
          Experience comfort and luxury in our carefully designed accommodations
        </p>

        <!-- Quick Stats with Enhanced Design -->
        <div class="flex flex-wrap justify-center gap-6 mt-8">
          <div class="text-center bg-white/10 backdrop-blur-sm px-5 py-3 rounded-lg">
            <p class="text-3xl font-bold text-emerald-400 mb-1">3</p>
            <p class="text-gray-300 uppercase tracking-wider text-xs">Room Types</p>
          </div>
          <div class="text-center bg-white/10 backdrop-blur-sm px-5 py-3 rounded-lg">
            <p class="text-3xl font-bold text-emerald-400 mb-1">24/7</p>
            <p class="text-gray-300 uppercase tracking-wider text-xs">Room Service</p>
          </div>
          <div class="text-center bg-white/10 backdrop-blur-sm px-5 py-3 rounded-lg">
            <p class="text-3xl font-bold text-emerald-400 mb-1">100%</p>
            <p class="text-gray-300 uppercase tracking-wider text-xs">Satisfaction</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Decorative Corner Elements -->
    <div class="absolute top-6 left-6 w-16 h-16 border-l-2 border-t-2 border-emerald-500/30"></div>
    <div class="absolute bottom-6 right-6 w-16 h-16 border-r-2 border-b-2 border-emerald-500/30"></div>
  </div>

  <!-- Rooms Section -->
  <section id="rooms" class="py-20 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Room Type Filter -->
      <div class="flex flex-wrap justify-center gap-4 mb-12">
        <button data-filter="all"
          class="filter-btn px-6 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 
                       transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
          All Rooms
        </button>
        <button data-filter="standard"
          class="filter-btn px-6 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 
                       transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2">
          Standard
        </button>
        <button data-filter="deluxe"
          class="filter-btn px-6 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 
                       transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2">
          Deluxe
        </button>
        <button data-filter="cottage"
          class="filter-btn px-6 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 
                       transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2">
          Cottages
        </button>
      </div>

      <!-- Rooms Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Standard Room -->
        <div
          class="room-card bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1"
          data-type="standard">
          <div class="relative h-64">
            <img
              src="https://cache.marriott.com/marriottassets/marriott/HNMSI/hnmsi-ocean-guestroom-4648-hor-clsc.jpg?interpolation=progressive-bilinear&"
              alt="Standard Room" class="w-full h-full object-cover">
            <div class="absolute top-4 right-4">
              <span class="px-3 py-1 bg-emerald-500 text-white text-sm font-medium rounded-full">
                ₱2,500/night
              </span>
            </div>
          </div>
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Standard Room</h3>
                <p class="text-sm text-gray-500">Standard</p>
              </div>
              <div class="flex items-center">
                <span class="material-symbols-outlined text-emerald-500">person</span>
                <span class="ml-1 text-gray-600">2 Guests</span>
              </div>
            </div>
            <div class="space-y-3 mb-6">
              <p class="text-gray-600 text-sm line-clamp-2">
                Comfortable room with essential amenities perfect for a relaxing stay.
              </p>
              <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Air Conditioning</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">TV</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Private Bathroom</span>
              </div>
            </div>
            <!-- Room Details Grid -->
            <div class="grid grid-cols-2 gap-4 my-6 p-4 bg-gray-50 rounded-lg border border-gray-100">
              <div class="flex items-center p-2 bg-white rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-500 text-2xl mr-3">bed</span>
                <div>
                  <p class="text-sm font-medium text-gray-900">Bed Type</p>
                  <p class="text-sm text-gray-600">1 Queen Bed</p>
                </div>
              </div>
              <div class="flex items-center p-2 bg-white rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-500 text-2xl mr-3">square_foot</span>
                <div>
                  <p class="text-sm font-medium text-gray-900">Room Size</p>
                  <p class="text-sm text-gray-600">28 m²</p>
                </div>
              </div>
              <div class="flex items-center p-2 bg-white rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-500 text-2xl mr-3">wifi</span>
                <div>
                  <p class="text-sm font-medium text-gray-900">Internet</p>
                  <p class="text-sm text-gray-600">Free WiFi</p>
                </div>
              </div>
              <div class="flex items-center p-2 bg-white rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-500 text-2xl mr-3">ac_unit</span>
                <div>
                  <p class="text-sm font-medium text-gray-900">Climate</p>
                  <p class="text-sm text-gray-600">Air Conditioning</p>
                </div>
              </div>
            </div>

            <!-- Pricing Details -->
            <div class="bg-gradient-to-br from-emerald-50 to-gray-50 rounded-lg border border-emerald-100 p-4 mb-6">
              <h4 class="font-medium text-gray-900 mb-4 flex items-center">
                <span class="material-symbols-outlined mr-2 text-emerald-500">payments</span>
                Room Rates
              </h4>
              <div class="space-y-3">
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                  <div>
                    <p class="text-sm font-medium text-gray-900">Day Rate</p>
                    <p class="text-xs text-gray-600">8AM - 5PM</p>
                  </div>
                  <span class="text-lg font-semibold text-emerald-600">₱1,500</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                  <div>
                    <p class="text-sm font-medium text-gray-900">Night Rate</p>
                    <p class="text-xs text-gray-600">5PM - 8AM</p>
                  </div>
                  <span class="text-lg font-semibold text-emerald-600">₱2,500</span>
                </div>
                <div class="text-xs text-gray-500 pt-2">
                  <p class="flex items-center">
                    <span class="material-symbols-outlined text-emerald-500 text-sm mr-1">info</span>
                    Rates are subject to 12% VAT and service charge
                  </p>
                </div>
              </div>
            </div>

            <!-- Room Features -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
              <h4 class="font-medium text-gray-900 mb-4 flex items-center">
                <span class="material-symbols-outlined mr-2 text-emerald-500">hotel_class</span>
                Room Features
              </h4>
              <div class="grid grid-cols-2 gap-3">
                <div class="flex items-center space-x-2 bg-gray-50 p-2 rounded-lg">
                  <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                  <span class="text-sm text-gray-700">32" Smart TV</span>
                </div>
                <div class="flex items-center space-x-2 bg-gray-50 p-2 rounded-lg">
                  <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                  <span class="text-sm text-gray-700">Mini Fridge</span>
                </div>
                <div class="flex items-center space-x-2 bg-gray-50 p-2 rounded-lg">
                  <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                  <span class="text-sm text-gray-700">Work Desk</span>
                </div>
                <div class="flex items-center space-x-2 bg-gray-50 p-2 rounded-lg">
                  <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                  <span class="text-sm text-gray-700">Hot Shower</span>
                </div>
              </div>
            </div>

            <!-- Update Action Buttons -->
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <div class="mt-6 space-y-3">
              <a href="booking.php?room_type=standard"
                class="block w-full text-center bg-emerald-500 text-white py-2 rounded-lg hover:bg-emerald-600 
                          transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Book Now
              </a>
              <button onclick="showRoomDetails('standard')" class="block w-full text-center border border-emerald-500 text-emerald-500 py-2 rounded-lg 
                               hover:bg-emerald-50 transition-colors duration-200 focus:outline-none focus:ring-2 
                               focus:ring-emerald-500 focus:ring-offset-2">
                View Details
              </button>
            </div>
            <?php else: ?>
            <div class="mt-6 space-y-3">
              <button onclick="toggleModal()" class="block w-full bg-emerald-500 text-white py-2 rounded-lg hover:bg-emerald-600 
                               transition-all duration-200 transform hover:scale-[1.02] focus:outline-none 
                               focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Login to Book
              </button>
              <button onclick="showRoomDetails('standard')" class="block w-full text-center border border-emerald-500 text-emerald-500 py-2 rounded-lg 
                               hover:bg-emerald-50 transition-colors duration-200 focus:outline-none focus:ring-2 
                               focus:ring-emerald-500 focus:ring-offset-2">
                View Details
              </button>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Deluxe Room -->
        <div
          class="room-card bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1"
          data-type="deluxe">
          <div class="relative h-64">
            <img
              src="https://cache.marriott.com/marriottassets/marriott/HNMSI/hnmsi-ocean-guestroom-4648-hor-clsc.jpg?interpolation=progressive-bilinear&"
              alt="Deluxe Room" class="w-full h-full object-cover">
            <div class="absolute top-4 right-4">
              <span class="px-3 py-1 bg-emerald-500 text-white text-sm font-medium rounded-full">
                ₱3,500/night
              </span>
            </div>
          </div>
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Deluxe Room</h3>
                <p class="text-sm text-gray-500">Deluxe</p>
              </div>
              <div class="flex items-center">
                <span class="material-symbols-outlined text-emerald-500">person</span>
                <span class="ml-1 text-gray-600">4 Guests</span>
              </div>
            </div>
            <div class="space-y-3 mb-6">
              <p class="text-gray-600 text-sm line-clamp-2">
                Spacious room with premium amenities and beautiful views.
              </p>
              <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Air Conditioning</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Smart TV</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Mini Bar</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Balcony</span>
              </div>
            </div>
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <a href="booking.php?room_type=deluxe"
              class="block w-full text-center bg-emerald-500 text-white py-2 rounded-lg hover:bg-emerald-600 
                        transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
              Book Now
            </a>
            <?php else: ?>
            <button onclick="toggleModal()"
              class="w-full bg-emerald-500 text-white py-2 rounded-lg hover:bg-emerald-600 
                             transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
              Login to Book
            </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Cottage -->
        <div
          class="room-card bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1"
          data-type="cottage">
          <div class="relative h-64">
            <img src="assets/cottage.jpg" alt="Cottage" class="w-full h-full object-cover">
            <div class="absolute top-4 right-4">
              <span class="px-3 py-1 bg-emerald-500 text-white text-sm font-medium rounded-full">
                ₱4,500/night
              </span>
            </div>
          </div>
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Luxury Cottage</h3>
                <p class="text-sm text-gray-500">Cottage</p>
              </div>
              <div class="flex items-center">
                <span class="material-symbols-outlined text-emerald-500">person</span>
                <span class="ml-1 text-gray-600">6 Guests</span>
              </div>
            </div>
            <div class="space-y-3 mb-6">
              <p class="text-gray-600 text-sm line-clamp-2">
                Private cottage with full amenities and garden views.
              </p>
              <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Air Conditioning</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Kitchen</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Living Area</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">Private Garden</span>
              </div>
            </div>
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <a href="booking.php?room_type=cottage"
              class="block w-full text-center bg-emerald-500 text-white py-2 rounded-lg hover:bg-emerald-600 
                        transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
              Book Now
            </a>
            <?php else: ?>
            <button onclick="toggleModal()"
              class="w-full bg-emerald-500 text-white py-2 rounded-lg hover:bg-emerald-600 
                             transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
              Login to Book
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const roomCards = document.querySelectorAll('.room-card');

    filterButtons.forEach(button => {
      button.addEventListener('click', () => {
        // Remove active class from all buttons
        filterButtons.forEach(btn => {
          btn.classList.remove('bg-emerald-500', 'text-white');
          btn.classList.add('bg-white', 'text-gray-700');
        });

        // Add active class to clicked button
        button.classList.remove('bg-white', 'text-gray-700');
        button.classList.add('bg-emerald-500', 'text-white');

        const filterValue = button.dataset.filter;

        // Filter rooms
        roomCards.forEach(card => {
          if (filterValue === 'all' || card.dataset.type === filterValue) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  });

  function showRoomDetails(roomType) {
    Swal.fire({
      title: roomType.charAt(0).toUpperCase() + roomType.slice(1) + ' Room Details',
      html: `
        <div class="text-left">
          <h3 class="font-semibold mb-2">Room Policies:</h3>
          <ul class="list-disc list-inside mb-4 text-sm">
            <li>Check-in: 2:00 PM</li>
            <li>Check-out: 12:00 PM</li>
            <li>No smoking</li>
            <li>Maximum occupancy: ${roomType === 'standard' ? '2' : roomType === 'deluxe' ? '4' : '6'} guests</li>
          </ul>
          
          <h3 class="font-semibold mb-2">Rescheduling Policy:</h3>
          <ul class="list-disc list-inside mb-4 text-sm">
            <li>Rescheduling is allowed up to 24 hours before check-in</li>
            <li>Subject to room availability</li>
            <li>One-time rescheduling only</li>
            <li>Must be used within 3 months from original booking date</li>
          </ul>
          
          <h3 class="font-semibold mb-2">Additional Information:</h3>
          <ul class="list-disc list-inside text-sm">
            <li>Valid ID required upon check-in</li>
            <li>Credit card or cash deposit required</li>
            <li>Pet-friendly (additional fee applies)</li>
            <li>Extra bed available upon request</li>
          </ul>
        </div>
      `,
      confirmButtonText: 'Close',
      confirmButtonColor: '#10B981',
      width: '600px'
    });
  }

  // Add smooth scroll for room filtering
  document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', () => {
      const roomsSection = document.querySelector('#rooms');
      roomsSection.scrollIntoView({
        behavior: 'smooth'
      });
    });
  });
  </script>
</body>

</html>