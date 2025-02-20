<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Add x-data to the main nav element -->
<nav x-data="{ mobileMenu: false, userDropdown: false }" class="bg-white shadow-lg">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between h-16">
      <!-- Logo -->
      <div class="flex-shrink-0 flex items-center">
        <a href="customer_dashboard.php">
          <img class="h-12 w-auto" src="../assets/casitalogo-removebg-preview.png" alt="Casita De Grands">
        </a>
      </div>

      <!-- Navigation Links -->
      <div class="hidden md:flex space-x-8 items-center">
        <a href="customer_dashboard.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) === 'customer_dashboard.php' ? 'text-emerald-500' : 'text-gray-600'; ?> hover:text-emerald-500 px-3 py-2 rounded-md text-sm font-medium">
          Dashboard
        </a>
        <a href="my_bookings.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) === 'my_bookings.php' ? 'text-emerald-500' : 'text-gray-600'; ?> hover:text-emerald-500 px-3 py-2 rounded-md text-sm font-medium">
          My Bookings
        </a>
        <a href="settings.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'text-emerald-500' : 'text-gray-600'; ?> hover:text-emerald-500 px-3 py-2 rounded-md text-sm font-medium">
          Settings
        </a>
        <a href="about-us.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) === 'about-us.php' ? 'text-emerald-500' : 'text-gray-600'; ?> hover:text-emerald-500 px-3 py-2 rounded-md text-sm font-medium">
          About Us
        </a>
        <a href="reservations.php"
          class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
          Reservations
          <span
            class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
        </a>
      </div>

      <!-- User Menu -->
      <div class="flex items-center space-x-4">
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" @click.away="open = false"
            class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
            <span class="font-medium"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <svg class="h-5 w-5" :class="{'transform rotate-180': open}" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <div x-show="open" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              Account Settings
            </a>
            <a href="../handlers/logout_handler.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
              Logout
            </a>
          </div>
        </div>
      </div>

      <!-- Mobile menu button -->
      <div class="md:hidden flex items-center">
        <button @click="mobileMenu = !mobileMenu"
          class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"
              x-show="!mobileMenu" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"
              x-show="mobileMenu" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile menu -->
  <div x-show="mobileMenu" class="md:hidden">
    <div class="px-2 pt-2 pb-3 space-y-1">
      <a href="customer_dashboard.php"
        class="<?php echo basename($_SERVER['PHP_SELF']) === 'customer_dashboard.php' ? 'text-emerald-500' : 'text-gray-600'; ?> block px-3 py-2 rounded-md text-base font-medium">
        Dashboard
      </a>
      <a href="my_bookings.php"
        class="<?php echo basename($_SERVER['PHP_SELF']) === 'my_bookings.php' ? 'text-emerald-500' : 'text-gray-600'; ?> block px-3 py-2 rounded-md text-base font-medium">
        My Bookings
      </a>

      <a href="about-us.php"
        class="<?php echo basename($_SERVER['PHP_SELF']) === 'about-us.php' ? 'text-emerald-500' : 'text-gray-600'; ?> block px-3 py-2 rounded-md text-base font-medium">
        About Us
      </a>
    </div>
  </div>
</nav>
<!-- Make sure Alpine.js is loaded -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>