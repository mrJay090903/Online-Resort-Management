<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has appropriate access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'staff'])) {
    header('Location: ../index.php');
    exit();
}
?>

<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
  <div class="px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Left side - Page Title -->
      <div class="flex items-center">
        <h2 class="text-2xl font-semibold text-gray-800">
          <?php
            $current_page = basename($_SERVER['PHP_SELF'], '.php');
            echo ucfirst(str_replace('_', ' ', $current_page));
          ?>
        </h2>
      </div>

      <!-- Right side - Notifications and Profile -->
      <div class="flex items-center space-x-6">
        <!-- Notifications Dropdown -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open"
            class="relative p-2 text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none">
            <span class="material-symbols-outlined">notifications</span>
            <?php
            // Get unread notifications count based on user type
            if ($_SESSION['user_type'] === 'admin') {
                $notif_count_query = "
                    SELECT COUNT(*) as count 
                    FROM notifications 
                    WHERE is_read = 0 
                    AND type IN ('new_booking', 'reschedule_request')
                ";
            } else { // staff
                $notif_count_query = "
                    SELECT COUNT(*) as count 
                    FROM notifications 
                    WHERE is_read = 0 
                    AND type IN ('new_booking')
                ";
            }
            $notif_count = $conn->query($notif_count_query)->fetch_assoc()['count'];
            
            if ($notif_count > 0): ?>
            <span
              class="absolute top-0 right-0 h-5 w-5 text-xs flex items-center justify-center bg-red-500 text-white rounded-full">
              <?php echo $notif_count; ?>
            </span>
            <?php endif; ?>
          </button>

          <!-- Notifications Panel -->
          <div x-show="open" @click.away="open = false"
            class="absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50">
            <div class="p-3 bg-gray-50 border-b border-gray-200">
              <h3 class="text-sm font-semibold text-gray-700">Notifications</h3>
            </div>

            <div class="divide-y divide-gray-200">
              <?php
              // Get recent notifications based on user type
              if ($_SESSION['user_type'] === 'admin') {
                  $recent_notif_query = "
                      SELECT n.*, b.booking_number 
                      FROM notifications n
                      LEFT JOIN bookings b ON b.id = SUBSTRING_INDEX(n.message, '#', -1)
                      WHERE n.type IN ('new_booking', 'reschedule_request')
                      ORDER BY n.created_at DESC 
                      LIMIT 5
                  ";
              } else { // staff
                  $recent_notif_query = "
                      SELECT n.*, b.booking_number 
                      FROM notifications n
                      LEFT JOIN bookings b ON b.id = SUBSTRING_INDEX(n.message, '#', -1)
                      WHERE n.type IN ('new_booking')
                      ORDER BY n.created_at DESC 
                      LIMIT 5
                  ";
              }
              $recent_notifications = $conn->query($recent_notif_query);
              
              if ($recent_notifications->num_rows > 0):
                while ($notif = $recent_notifications->fetch_assoc()): ?>
              <a href="notifications" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                <div class="flex items-start">
                  <span class="material-symbols-outlined text-emerald-500 mr-3">
                    <?php echo $notif['type'] === 'new_booking' ? 'event_available' : 'schedule'; ?>
                  </span>
                  <div class="flex-1 min-w-0">
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
                  </div>
                  <?php if (!$notif['is_read']): ?>
                  <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                  <?php endif; ?>
                </div>
              </a>
              <?php endwhile;
              else: ?>
              <div class="px-4 py-6 text-center text-gray-500">
                <span class="material-symbols-outlined text-4xl mb-2">notifications_off</span>
                <p>No new notifications</p>
              </div>
              <?php endif; ?>
            </div>

            <div class="p-2 bg-gray-50 border-t border-gray-200">
              <a href="notifications"
                class="block w-full px-4 py-2 text-center text-sm text-emerald-600 hover:bg-gray-100 rounded-md transition-colors">
                View All Notifications
              </a>
            </div>
          </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open"
            class="flex items-center space-x-3 focus:outline-none hover:bg-gray-100 p-2 rounded-lg">
            <?php
            // Get display name based on user type
            if ($_SESSION['user_type'] === 'staff') {
                $display_name = $_SESSION['staff_name'] ?? $_SESSION['full_name'] ?? $_SESSION['email'];
            } else {
                $display_name = $_SESSION['full_name'] ?? $_SESSION['email'];
            }
            
            // Get first letter, with fallback
            $first_letter = !empty($display_name) ? strtoupper(substr($display_name, 0, 1)) : '?';
            ?>
            <div class="h-8 w-8 rounded-full bg-emerald-500 flex items-center justify-center text-white font-semibold">
              <?php echo htmlspecialchars($first_letter); ?>
            </div>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($display_name); ?></p>
              <p class="text-xs text-gray-500"><?php echo ucfirst($_SESSION['user_type']); ?></p>
            </div>
          </button>

          <!-- Profile Dropdown Menu -->
          <div x-show="open" @click.away="open = false"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <span class="material-symbols-outlined mr-2">person</span>
              Profile
            </a>
            <?php if ($_SESSION['user_type'] === 'admin'): ?>
            <a href="help.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <span class="material-symbols-outlined mr-2">help</span>
              Help
            </a>
            <?php endif; ?>
            <a href="../handlers/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
              <span class="material-symbols-outlined mr-2">logout</span>
              Sign out
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Breadcrumbs -->
  <div class="bg-gray-50 px-6 py-2 border-b border-gray-200">
    <nav class="flex" aria-label="Breadcrumb">
      <ol class="flex items-center space-x-2">
        <li>
          <a href="dashboard" class="text-gray-500 hover:text-gray-700">Dashboard</a>
        </li>
        <?php if (basename($_SERVER['PHP_SELF']) !== 'dashboard.php'): ?>
        <li class="flex items-center">
          <span class="material-symbols-outlined text-gray-400 mx-1" style="font-size: 16px;">chevron_right</span>
          <span class="text-gray-700">
            <?php echo ucfirst(str_replace('_', ' ', basename($_SERVER['PHP_SELF'], '.php'))); ?>
          </span>
        </li>
        <?php endif; ?>
      </ol>
    </nav>
  </div>
</header>

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