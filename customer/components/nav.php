<?php

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../../config/database.php';

// Initialize notifications variables
$unread_count = 0;
$notifications = [];

// Only try to access notifications if we have a database connection
if (isset($conn)) {
    try {
        // First check if notifications table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($table_check->num_rows == 0) {
            // Create notifications table if it doesn't exist
            $create_table = "CREATE TABLE IF NOT EXISTS notifications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(50) NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )";
            $conn->query($create_table);
        }

        // Get unread notifications count
        if (isset($_SESSION['user_id'])) {
            $notif_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param("i", $_SESSION['user_id']);
            $notif_stmt->execute();
            $unread_count = $notif_stmt->get_result()->fetch_assoc()['count'];

            // Get notifications
            $notifications_query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
            $notifications_stmt = $conn->prepare($notifications_query);
            $notifications_stmt->bind_param("i", $_SESSION['user_id']);
            $notifications_stmt->execute();
            $notifications = $notifications_stmt->get_result();
        }
    } catch (Exception $e) {
        error_log("Notifications Error: " . $e->getMessage());
    }
}
?>

<!-- Add x-data to the main nav element -->
<nav x-data="{ mobileMenu: false, userDropdown: false }" class="bg-white shadow-lg fixed w-full z-10">
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

        <a href="reservations.php"
          class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
          Reservations
          <span
            class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
        </a>
        <a href="my_bookings.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) === 'my_bookings.php' ? 'text-emerald-500' : 'text-gray-600'; ?> hover:text-emerald-500 px-3 py-2 rounded-md text-sm font-medium">
          My Bookings
        </a>
        <a href="about-us.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) === 'about-us.php' ? 'text-emerald-500' : 'text-gray-600'; ?> hover:text-emerald-500 px-3 py-2 rounded-md text-sm font-medium">
          About Us
        </a>
      </div>

      <!-- User Menu -->
      <div class="hidden md:flex items-center space-x-4">
        <!-- Notifications icon - Moved here -->
        <div class="relative">
          <?php if (isset($conn) && $notifications && $unread_count > 0): ?>
          <button id="notificationButton" class="relative p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
              </path>
            </svg>
            <span
              class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
              <?php echo $unread_count; ?>
            </span>
          </button>
          <?php else: ?>
          <button id="notificationButton" class="relative p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
              </path>
            </svg>
          </button>
          <?php endif; ?>

          <!-- Notifications Dropdown -->
          <div id="notificationsDropdown"
            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50">
            <div class="p-4 border-b">
              <h3 class="text-lg font-semibold">Notifications</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
              <?php if (isset($conn) && $notifications && $notifications->num_rows > 0): ?>
              <?php while ($notification = $notifications->fetch_assoc()): ?>
              <div class="p-4 border-b hover:bg-gray-50 <?php echo $notification['is_read'] ? 'opacity-75' : ''; ?>">
                <h4 class="font-semibold"><?php echo htmlspecialchars($notification['title']); ?></h4>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($notification['message']); ?></p>
                <p class="text-xs text-gray-400 mt-1">
                  <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                </p>
              </div>
              <?php endwhile; ?>
              <?php else: ?>
              <div class="p-4 text-center text-gray-500">
                No notifications yet
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- User menu -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
            <span class="font-medium">
              <?php 
                // Use full_name from session, fallback to email if name not available
                echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['email'] ?? 'Guest'); 
                ?>
            </span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

<script>
// Add this to your existing JavaScript
document.getElementById('notificationButton').addEventListener('click', function() {
  const dropdown = document.getElementById('notificationsDropdown');
  dropdown.classList.toggle('hidden');

  // Mark notifications as read
  if (!dropdown.classList.contains('hidden')) {
    fetch('../handlers/mark_notifications_read.php', {
      method: 'POST'
    });
  }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
  const dropdown = document.getElementById('notificationsDropdown');
  const button = document.getElementById('notificationButton');

  if (!dropdown.contains(event.target) && !button.contains(event.target)) {
    dropdown.classList.add('hidden');
  }
});
</script>