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

<header class="bg-white shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
    <div class="flex items-center">
      <img src="../assets/casitalogo-removebg-preview.png" alt="Logo" class="h-12 w-auto">
    </div>

    <!-- User Menu -->
    <div class="relative" x-data="{ open: false }">
      <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
        <span class="font-medium"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div x-show="open" @click.away="open = false"
        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
        <a href="../handlers/logout_handler.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
          Logout
        </a>
      </div>
    </div>
  </div>
</header>