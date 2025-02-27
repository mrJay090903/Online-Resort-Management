<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>

<header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
  <div class="px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Left side -->
      <div class="flex items-center">
        <h2 class="text-xl font-semibold text-gray-800">
          <?php
            $current_page = basename($_SERVER['PHP_SELF'], '.php');
            echo ucfirst(str_replace('_', ' ', $current_page));
          ?>
        </h2>
      </div>

      <!-- Right side -->
      <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
            <span class="material-symbols-outlined">notifications</span>
            <span
              class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
              3
            </span>
          </button>

          <!-- Notifications dropdown -->
          <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute right-0 w-80 mt-2 bg-white rounded-lg shadow-lg py-2 z-50">
            <div class="px-4 py-2 font-semibold text-gray-800 border-b border-gray-200">
              Notifications
            </div>
            <!-- Notification items -->
            <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
              <p class="text-sm font-medium text-gray-900">New Booking Request</p>
              <p class="text-xs text-gray-600 mt-1">John Doe booked Room 101</p>
              <p class="text-xs text-gray-500 mt-1">2 minutes ago</p>
            </a>
            <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
              <p class="text-sm font-medium text-gray-900">Payment Received</p>
              <p class="text-xs text-gray-600 mt-1">Payment for booking #123 received</p>
              <p class="text-xs text-gray-500 mt-1">1 hour ago</p>
            </a>
            <!-- View all link -->
            <a href="#"
              class="block px-4 py-2 text-sm text-blue-600 hover:text-blue-800 text-center border-t border-gray-200">
              View all notifications
            </a>
          </div>
        </div>

        <!-- User Menu -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
            <img src="../assets/profile.jpg" alt="Profile" class="w-8 h-8 rounded-full object-cover">
            <span class="text-sm font-medium text-gray-700">
              <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>
            </span>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>

          <!-- User dropdown -->
          <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute right-0 w-48 mt-2 bg-white rounded-lg shadow-lg py-2 z-50">
            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              Profile Settings
            </a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              Help & Support
            </a>
            <div class="border-t border-gray-200"></div>
            <a href="../handlers/logout_handler.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
              Sign out
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Breadcrumbs -->
<div class="bg-gray-50 border-b border-gray-200">
  <div class="px-6 py-2">
    <nav class="flex" aria-label="Breadcrumb">
      <ol class="flex items-center space-x-2">
        <li>
          <a href="dashboard.php" class="text-gray-500 hover:text-gray-700">Dashboard</a>
        </li>
        <?php if (basename($_SERVER['PHP_SELF']) !== 'dashboard.php'): ?>
        <li class="flex items-center">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
          <span class="ml-2 text-gray-700">
            <?php echo ucfirst(str_replace('_', ' ', basename($_SERVER['PHP_SELF'], '.php'))); ?>
          </span>
        </li>
        <?php endif; ?>
      </ol>
    </nav>
  </div>
</div>