<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Help & Support - Admin Dashboard</title>
  <link href="../src/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body class="bg-gray-50">
  <div class="flex">
    <?php include('components/sidebar.php'); ?>

    <div class="flex-1">
      <?php include('components/header.php'); ?>

      <main class="p-8">
        <div class="max-w-4xl mx-auto">
          <!-- Help & Support Content -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Help & Support</h2>

            <!-- FAQ Section -->
            <div class="space-y-6">
              <div>
                <h3 class="text-lg font-medium text-gray-700 mb-4">Frequently Asked Questions</h3>
                
                <div class="space-y-4">
                  <!-- FAQ Item -->
                  <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-4 py-3 text-left focus:outline-none flex justify-between items-center">
                      <span class="font-medium text-gray-700">How do I manage bookings?</span>
                      <span class="material-symbols-outlined">expand_more</span>
                    </button>
                    <div class="px-4 pb-3">
                      <p class="text-gray-600">You can manage bookings through the Reservations page. There you can view, confirm, or cancel bookings, and communicate with guests.</p>
                    </div>
                  </div>

                  <!-- More FAQ Items -->
                  <!-- Add more FAQ items as needed -->
                </div>
              </div>

              <!-- Contact Support -->
              <div class="pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Contact Support</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Email Support -->
                  <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex items-center mb-3">
                      <span class="material-symbols-outlined text-emerald-500 mr-2">email</span>
                      <h4 class="font-medium text-gray-700">Email Support</h4>
                    </div>
                    <p class="text-gray-600 mb-3">Send us an email and we'll get back to you within 24 hours.</p>
                    <a href="mailto:support@example.com" class="text-emerald-500 hover:text-emerald-600">
                      support@example.com
                    </a>
                  </div>

                  <!-- Phone Support -->
                  <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex items-center mb-3">
                      <span class="material-symbols-outlined text-emerald-500 mr-2">phone</span>
                      <h4 class="font-medium text-gray-700">Phone Support</h4>
                    </div>
                    <p class="text-gray-600 mb-3">Available Monday to Friday, 9am - 5pm</p>
                    <a href="tel:+1234567890" class="text-emerald-500 hover:text-emerald-600">
                      +1 (234) 567-890
                    </a>
                  </div>
                </div>
              </div>

              <!-- Documentation -->
              <div class="pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Documentation</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <!-- User Guide -->
                  <a href="#" class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <div class="flex items-center mb-2">
                      <span class="material-symbols-outlined text-emerald-500 mr-2">book</span>
                      <h4 class="font-medium text-gray-700">User Guide</h4>
                    </div>
                    <p class="text-sm text-gray-600">Complete guide to using the admin dashboard</p>
                  </a>

                  <!-- Video Tutorials -->
                  <a href="#" class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <div class="flex items-center mb-2">
                      <span class="material-symbols-outlined text-emerald-500 mr-2">play_circle</span>
                      <h4 class="font-medium text-gray-700">Video Tutorials</h4>
                    </div>
                    <p class="text-sm text-gray-600">Step-by-step video guides</p>
                  </a>

                  <!-- API Documentation -->
                  <a href="#" class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <div class="flex items-center mb-2">
                      <span class="material-symbols-outlined text-emerald-500 mr-2">code</span>
                      <h4 class="font-medium text-gray-700">API Docs</h4>
                    </div>
                    <p class="text-sm text-gray-600">Technical documentation and APIs</p>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>

</html> 