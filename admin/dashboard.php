<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
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
  <title>Admin Dashboard - Casita de Grands</title>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <script src="https://cdn.lordicon.com/bhenfmcm.js"></script>
  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
</head>

<body class="bg-gray-50">
  <div class="flex">
    <?php include('components/sidebar.php'); ?>

    <div class="flex-1">
      <?php include('components/header.php'); ?>

      <main class="p-8">
        <div class="max-w-7xl mx-auto">
          <!-- Header -->
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard Overview</h1>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Users Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                  <lord-icon src="https://cdn.lordicon.com/dxjqoygy.json" trigger="hover" colors="primary:#3b82f6"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Total Users</h2>
                  <p class="text-2xl font-semibold">150</p>
                </div>
              </div>
            </div>

            <!-- Active Reservations -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                  <lord-icon src="https://cdn.lordicon.com/uukerzzv.json" trigger="hover" colors="primary:#22c55e"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Active Reservations</h2>
                  <p class="text-2xl font-semibold">25</p>
                </div>
              </div>
            </div>

            <!-- Available Rooms -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                  <lord-icon src="https://cdn.lordicon.com/gmzxduhd.json" trigger="hover" colors="primary:#eab308"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Available Rooms</h2>
                  <p class="text-2xl font-semibold">8</p>
                </div>
              </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                  <lord-icon src="https://cdn.lordicon.com/qhviklyi.json" trigger="hover" colors="primary:#9333ea"
                    style="width:32px;height:32px">
                  </lord-icon>
                </div>
                <div class="ml-4">
                  <h2 class="text-gray-600">Monthly Revenue</h2>
                  <p class="text-2xl font-semibold">₱150,000</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity Table -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-semibold text-gray-800">Recent Activity</h2>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Time</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">New Reservation</td>
                    <td class="px-6 py-4 whitespace-nowrap">John Doe</td>
                    <td class="px-6 py-4">Booked Room 101 for March 15-17</td>
                    <td class="px-6 py-4 whitespace-nowrap">5 mins ago</td>
                  </tr>
                  <!-- Add more rows as needed -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>

</html>