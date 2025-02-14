<header class="bg-white shadow-lg">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center">

      </div>
      <div class="flex items-center space-x-4">
        <span class="text-gray-700">Admin: <?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="../handlers/logout_handler.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
          Logout
        </a>
      </div>
    </div>
  </div>
</header>