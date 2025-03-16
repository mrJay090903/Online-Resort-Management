<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>

<head>
  <!-- ... other head elements ... -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
</head>

<div x-data="{ 
    open: localStorage.getItem('sidebarOpen') === 'true' || true,
    activeDropdown: null
}" class="relative">
  <!-- Sidebar -->
  <div :class="open ? 'w-64' : 'w-16'"
    class="fixed top-0 left-0 h-full bg-emerald-500 text-white transition-all duration-300 z-50">
    <div class="p-4 flex items-center justify-between">
      <img src="../assets/logos.png" alt="Logo" class="w-36 h-13" x-show="open" x-cloak>
      <button @click="open = !open; localStorage.setItem('sidebarOpen', open)"
        class="p-2 rounded-lg hover:bg-emerald-600 transition-colors focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Navigation Links -->
    <nav class="mt-4 space-y-2">
      <!-- Staff & Admin Access -->
      <a href="dashboard.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('dashboard.php')}">
        <span class="material-symbols-outlined">dashboard</span>
        <span x-show="open" x-cloak class="ml-3">Dashboard</span>
      </a>

      <a href="reservations.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('reservations.php')}">
        <span class="material-symbols-outlined">book_online</span>
        <span x-show="open" x-cloak class="ml-3">Reservations</span>
      </a>

      <a href="rooms.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('rooms.php')}">
        <span class="material-symbols-outlined">hotel</span>
        <span x-show="open" x-cloak class="ml-3">Rooms</span>
      </a>

      <a href="cottage.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('cottage.php')}">
        <span class="material-symbols-outlined">house</span>
        <span x-show="open" x-cloak class="ml-3">Cottage</span>
      </a>

      <a href="venues.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('venues.php')}">
        <span class="material-symbols-outlined">location_on</span>
        <span x-show="open" x-cloak class="ml-3">Venues</span>
      </a>

      <a href="reports.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('reports.php')}">
        <span class="material-symbols-outlined">monitoring</span>
        <span x-show="open" x-cloak class="ml-3">Reports</span>
      </a>

      <?php if ($_SESSION['user_type'] === 'admin'): ?>
      <!-- Admin-only sections -->
      <a href="features.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('features.php')}">
        <span class="material-symbols-outlined">stars</span>
        <span x-show="open" x-cloak class="ml-3">Features</span>
      </a>

      <div x-data="{ dropdownOpen: false }" class="relative">
        <button @click="dropdownOpen = !dropdownOpen"
          class="flex items-center w-full px-6 py-3 text-white hover:bg-emerald-600">
          <span class="material-symbols-outlined">manage_accounts</span>
          <span x-show="open" x-cloak class="ml-3">User Account</span>
          <span x-show="open" class="material-symbols-outlined ml-auto"
            :class="{'rotate-180': dropdownOpen}">expand_more</span>
        </button>
        <div x-show="dropdownOpen && open" @click.away="dropdownOpen = false" class="pl-12 bg-emerald-600">
          <a href="customer_account.php" class="block py-2 text-white hover:bg-emerald-700">Customer Account</a>
          <a href="staff_account.php" class="block py-2 text-white hover:bg-emerald-700">Staff Account</a>
        </div>
      </div>

      <a href="guest_feedback.php" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': window.location.pathname.includes('guest_feedback.php')}">
        <span class="material-symbols-outlined">feedback</span>
        <span x-show="open" x-cloak class="ml-3">Guest Feedback</span>
      </a>
      <?php endif; ?>
    </nav>

    <!-- User Profile Section -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-emerald-600">
      <div class="flex items-center" :class="{'justify-center': !open}">
        <span class="material-symbols-outlined text-2xl">account_circle</span>
        <div x-show="open" class="ml-3">
          <p class="text-sm font-medium"><?php echo $_SESSION['email']; ?></p>
          <p class="text-xs text-emerald-200"><?php echo ucfirst($_SESSION['user_type']); ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content Wrapper -->
  <div :class="open ? 'ml-64' : 'ml-16'" class="flex-1 transition-all duration-300">
    <div class="w-full">
      <!-- This div will be used to wrap the main content -->
      <?php if(isset($content)) echo $content; ?>
    </div>
  </div>
</div>

</html>