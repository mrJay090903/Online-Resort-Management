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
          <button @click="open = !open"
            class="p-1 rounded-full text-gray-600 hover:text-gray-700 focus:outline-none relative">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
              </path>
            </svg>
            <?php
            // Get unread notifications count
            $notif_count_query = "
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE is_read = 0 
                AND type IN ('new_booking', 'reschedule_request')
            ";
            $notif_count = $conn->query($notif_count_query)->fetch_assoc()['count'];
            
            if ($notif_count > 0): ?>
            <span
              class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
              <?php echo $notif_count; ?>
            </span>
            <?php endif; ?>
          </button>

          <!-- Dropdown panel -->
          <div x-show="open" @click.away="open = false"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50">
            <div class="py-2">
              <div class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50">
                Recent Notifications
              </div>
              <?php
              // Get recent notifications with booking details
              $recent_notif_query = "
                  SELECT n.*, b.booking_number 
                  FROM notifications n
                  LEFT JOIN bookings b ON b.id = SUBSTRING_INDEX(n.message, '#', -1)
                  WHERE n.type IN ('new_booking', 'reschedule_request')
                  ORDER BY n.created_at DESC 
                  LIMIT 5
              ";
              $recent_notifications = $conn->query($recent_notif_query);
              
              if ($recent_notifications->num_rows > 0):
                while ($notif = $recent_notifications->fetch_assoc()): ?>
              <a href="notifications"
                class="block px-4 py-3 hover:bg-gray-50 transition <?php echo $notif['is_read'] ? 'opacity-75' : ''; ?>">
                <p class="text-sm font-medium text-gray-900">
                  <?php echo htmlspecialchars($notif['title']); ?>
                </p>
                <p class="text-sm text-gray-500 truncate">
                  <?php 
                  // Replace the ID with booking number in the message
                  $message = preg_replace(
                      '/Booking ID: #(\d+)/', 
                      'Booking Number: ' . $notif['booking_number'], 
                      $notif['message']
                  );
                  echo htmlspecialchars($message); 
                  ?>
                </p>
                <p class="mt-1 text-xs text-gray-400">
                  <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                </p>
              </a>
              <?php endwhile;
              else: ?>
              <div class="px-4 py-3 text-sm text-gray-500">
                No new notifications
              </div>
              <?php endif; ?>

              <div class="border-t border-gray-100 mt-2">
                <a href="notifications" class="block px-4 py-2 text-sm text-center text-emerald-600 hover:bg-gray-50">
                  View All Notifications
                </a>
              </div>
            </div>
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
            <a href="profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              Profile Settings
            </a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              Help & Support
            </a>
            <div class="border-t border-gray-200"></div>
            <a href="../handlers/logout_handler" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
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
          <a href="dashboard" class="text-gray-500 hover:text-gray-700">Dashboard</a>
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

<script>
// ... existing script content ...

// Initialize date pickers with flatpickr
const checkInPicker = flatpickr("#reschedule_date_in", {
  minDate: "today",
  dateFormat: "Y-m-d",
  onChange: function(selectedDates) {
    // Update the minimum date of check-out to be after check-in
    checkOutPicker.set("minDate", selectedDates[0]);
  }
});

const checkOutPicker = flatpickr("#reschedule_date_out", {
  minDate: "today",
  dateFormat: "Y-m-d"
});

function openRescheduleModal(bookingId) {
  document.getElementById('booking_id').value = bookingId;
  document.getElementById('rescheduleModal').classList.remove('hidden');
  document.getElementById('rescheduleModal').classList.add('flex');
  // Reset form
  document.getElementById('rescheduleForm').reset();
  // Reset date pickers
  checkInPicker.clear();
  checkOutPicker.clear();
}

function closeRescheduleModal() {
  document.getElementById('rescheduleModal').classList.add('hidden');
  document.getElementById('rescheduleModal').classList.remove('flex');
}

function submitReschedule(event) {
  event.preventDefault();
  const formData = new FormData(event.target);

  // Validate dates
  const checkIn = new Date(formData.get('reschedule_date_in'));
  const checkOut = new Date(formData.get('reschedule_date_out'));

  if (checkOut <= checkIn) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Dates',
      text: 'Check-out date must be after check-in date'
    });
    return;
  }

  Swal.fire({
    title: 'Confirm Reschedule Request',
    text: 'Are you sure you want to request these new dates?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#f97316',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, submit request'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('../handlers/reschedule_booking', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Request Submitted!',
              text: data.message,
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              window.location.reload();
            });
          } else {
            throw new Error(data.message || 'Failed to submit reschedule request');
          }
        })
        .catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message
          });
        });
    }
  });
}
</script>