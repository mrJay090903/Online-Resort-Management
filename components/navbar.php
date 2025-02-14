<!-- Navbar -->
<nav class="fixed top-0 left-0 right-0 bg-white shadow-lg z-50">
  <div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-20">
      <!-- Logo - Left Side -->
      <div class="flex-shrink-0 ml-0">
        <a href="index.php">
          <img src="assets/casitalogo-removebg-preview.png" alt="Casita De Grands"
            class="h-16 w-auto transition-transform duration-300 hover:scale-105">
        </a>
      </div>

      <!-- Navigation Links - Centered -->
      <div class="hidden md:flex items-center justify-center flex-1">
        <div class="flex space-x-8">
          <a href="index.php"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Home
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="#rooms"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Rooms
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="#reservations"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Reservations
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="#features"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            Features
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>

          <a href="about-us.php"
            class="relative font-medium text-gray-800 hover:text-gray-600 transition-colors duration-300 group">
            About us
            <span
              class="absolute inset-x-0 bottom-0 h-0.5 bg-black transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
          </a>
        </div>
      </div>

      <!-- Right Side: Login Button -->
      <div class="flex items-center space-x-4">
        <?php if(isset($_SESSION['user_id'])): ?>
          <a href="<?php echo $_SESSION['user_type'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php'; ?>" 
            class="font-['Lexend'] px-6 py-2 border-2 border-gray-800 text-gray-800 rounded-md transition-all duration-300 hover:bg-gray-800 hover:text-white">
            Dashboard
          </a>
        <?php else: ?>
          <button onclick="toggleModal()" 
            class="font-['Lexend'] px-6 py-2 border-2 border-gray-800 text-gray-800 rounded-md transition-all duration-300 hover:bg-gray-800 hover:text-white">
            LOGIN
          </button>
        <?php endif; ?>

        <!-- Mobile Menu Button -->
        <div class="md:hidden">
          <button type="button" onclick="toggleMobileMenu()" 
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-800 hover:text-gray-600 hover:bg-gray-100">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Menu (Hidden by default) -->
  <div id="mobileMenu" class="hidden md:hidden">
    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white">
      <a href="index.php" class="block px-3 py-2 text-base font-medium text-gray-800 hover:bg-gray-100 rounded-md">
        Home
      </a>
      <a href="#" class="block px-3 py-2 text-base font-medium text-gray-800 hover:bg-gray-100 rounded-md">
        Rooms
      </a>
      <a href="#" class="block px-3 py-2 text-base font-medium text-gray-800 hover:bg-gray-100 rounded-md">
        Reservations
      </a>
      <a href="index.php#features"
        class="block px-3 py-2 text-base font-medium text-gray-800 hover:bg-gray-100 rounded-md">
        Features
      </a>
      <a href="about-us.php" class="block px-3 py-2 text-base font-medium text-gray-800 hover:bg-gray-100 rounded-md">
        About us
      </a>
    </div>
  </div>
</nav>

<script>
function toggleMobileMenu() {
  const mobileMenu = document.getElementById('mobileMenu');
  mobileMenu.classList.toggle('hidden');
}
</script>