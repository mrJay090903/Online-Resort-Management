<nav class="mt-4 space-y-2">
    <a href="dashboard" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': '<?php echo basename($_SERVER['PHP_SELF'])?>' === 'dashboard.php'}">
        <!-- ... -->
    </a>

    <a href="bookings" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': '<?php echo basename($_SERVER['PHP_SELF'])?>' === 'bookings.php'}">
        <!-- ... -->
    </a>

    <a href="feedback" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': '<?php echo basename($_SERVER['PHP_SELF'])?>' === 'feedback.php'}">
        <!-- ... -->
    </a>

    <a href="profile" class="flex items-center px-6 py-3 text-white hover:bg-emerald-600"
        :class="{'justify-center': !open, 'bg-emerald-600': '<?php echo basename($_SERVER['PHP_SELF'])?>' === 'profile.php'}">
        <!-- ... -->
    </a>
</nav> 