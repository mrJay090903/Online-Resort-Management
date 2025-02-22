<head>
  <!-- ... other head elements ... -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
</head>

<div x-data="{ 
    open: localStorage.getItem('sidebarOpen') === 'true', 
    reservationOpen: localStorage.getItem('reservationOpen') === 'true',
    userAccountOpen: localStorage.getItem('userAccountOpen') === 'true',
    toggleSidebar() {
        this.open = !this.open;
        // Close dropdowns when sidebar is closed
        if (!this.open) {
            this.reservationOpen = false;
            this.userAccountOpen = false;
            localStorage.setItem('reservationOpen', false);
            localStorage.setItem('userAccountOpen', false);
        }
        localStorage.setItem('sidebarOpen', this.open);
    },
    toggleReservation() {
        this.reservationOpen = !this.reservationOpen;
        localStorage.setItem('reservationOpen', this.reservationOpen);
        if (!this.open) {
            this.open = true;
            localStorage.setItem('sidebarOpen', true);
        }
    },
    toggleUserAccount() {
        this.userAccountOpen = !this.userAccountOpen;
        localStorage.setItem('userAccountOpen', this.userAccountOpen);
        if (!this.open) {
            this.open = true;
            localStorage.setItem('sidebarOpen', true);
        }
    }
}" x-cloak class="flex">
  <!-- Sidebar -->
  <div :class="open ? 'w-64' : 'w-16'" class="min-h-screen bg-emerald-500 text-white transition-all duration-300">
    <div class="p-4 flex items-center justify-between">
      <img src="../assets/logos.png" alt="Logo" class="w-36 h-13" x-show="open" x-cloak>
      <button @click="toggleSidebar()" class="text-white">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
          :class="{'rotate-180': !open}">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Navigation Links -->
    <nav class="mt-4 space-y-2">

      <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': '<?php echo basename($_SERVER['PHP_SELF'])?>' === 'dashboard.php'}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 01-2-2v-2z" />
        </svg>
        <span x-show="open" x-cloak class="ml-3">Dashboard</span>
      </a>

      <!-- Move Reservation Dropdown here -->
      <div class="relative" x-data="{ dropdownOpen: false }">
        <button @click="toggleReservation()" class="flex items-center w-full px-6 py-3 hover:bg-emerald-600"
          :class="{'justify-center': !open}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span x-show="open" x-cloak class="ml-3">Reservation</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-auto transition-transform duration-200"
            :class="{ 'rotate-180': reservationOpen, 'hidden': !open }" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div x-show="reservationOpen" x-cloak x-transition class="pl-11" :class="{'pl-0': !open}">
          <a href="reservations.php" 
             class="block px-6 py-2 hover:bg-emerald-600 <?php echo basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'bg-emerald-600' : ''; ?>">
              All Reservations
          </a>
          <a href="new_reservation.php" class="block px-6 py-2 hover:bg-emerald-600">Status</a>
          <a href="reservation_list.php" class="block px-6 py-2 hover:bg-emerald-600">Pending</a>
          <a href="reservation_list.php" class="block px-6 py-2 hover:bg-emerald-600">Reserved</a>
          <a href="reservation_list.php" class="block px-6 py-2 hover:bg-emerald-600">Fullpaid</a>
          <a href="reservation_list.php" class="block px-6 py-2 hover:bg-emerald-600">Reschedule</a>
        </div>
      </div>

      <a href="rooms.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': '<?php echo basename($_SERVER['PHP_SELF'])?>' === 'rooms.php'}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span x-show="open" x-cloak class="ml-3">Rooms</span>
      </a>

      <a href="venues.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span x-show="open" x-cloak class="ml-3">Venues</span>
      </a>

      <a href="features.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
        </svg>
        <span x-show="open" x-cloak class="ml-3">Features</span>
      </a>

      <!-- User Account Dropdown -->
      <div class="relative">
        <button @click="toggleUserAccount()" class="flex items-center w-full px-6 py-3 hover:bg-emerald-600"
          :class="{'justify-center': !open}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span x-show="open" x-cloak class="ml-3">User Account</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-auto transition-transform duration-200"
            :class="{ 'rotate-180': userAccountOpen, 'hidden': !open }" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div x-show="userAccountOpen" x-cloak x-transition class="pl-11" :class="{'pl-0': !open}">
          <a href="customer_account.php"
            class="block px-6 py-2 hover:bg-emerald-600 <?php echo strpos($_SERVER['PHP_SELF'], 'customer_account.php') !== false ? 'bg-emerald-600' : ''; ?>">
            Customer
          </a>
          <a href="staff_account.php"
            class="block px-6 py-2 hover:bg-emerald-600 <?php echo strpos($_SERVER['PHP_SELF'], 'staff_account.php') !== false ? 'bg-emerald-600' : ''; ?>">
            Staff
          </a>
        </div>
      </div>

      <a href="reports.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span x-show="open" x-cloak class="ml-3">Reports</span>
      </a>

      <a href="guest_feedback.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <span x-show="open" x-cloak class="ml-3">Guest Feedback</span>
      </a>

    </nav>
  </div>
</div>