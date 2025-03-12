<?php
session_start();

// Redirect if no success message
if (!isset($_SESSION['payment_success'])) {
    header('Location: reservations.php');
    exit();
}

$success_data = $_SESSION['payment_success'];
unset($_SESSION['payment_success']); // Clear the message after showing

// Get booking details
require_once '../config/database.php';
$booking_query = $conn->prepare("SELECT booking_number FROM bookings WHERE id = ?");
$booking_query->bind_param("i", $success_data['booking_id']);
$booking_query->execute();
$booking = $booking_query->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Successful - Casita De Grands</title>
  <link href="../src/output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="src/output.css" rel="stylesheet">
</head>

<body class="bg-gray-50">
  <?php include('components/nav.php'); ?>

  <div class="min-h-screen pt-24 pb-16">
    <div class="container mx-auto px-6">
      <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-8">
          <!-- Success Icon -->
          <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>

          <!-- Success Message -->
          <h2 class="text-2xl font-semibold text-center text-gray-800 mb-2">
            <?php echo htmlspecialchars($success_data['title']); ?>
          </h2>
          <p class="text-center text-gray-600 mb-6">
            <?php echo htmlspecialchars($success_data['message']); ?>
          </p>

          <!-- Payment Details -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between mb-2">
              <span class="text-gray-600">Amount Paid:</span>
              <span class="font-semibold">₱<?php echo number_format($success_data['amount'], 2); ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Booking Reference:</span>
              <span class="font-semibold"><?php echo $booking['booking_number']; ?></span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-3">
            <a href="my_bookings.php"
              class="block w-full bg-emerald-600 text-white text-center py-3 rounded-lg hover:bg-emerald-700 transition-colors">
              View My Bookings
            </a>
            <a href="customer_dashboard.php"
              class="block w-full bg-gray-100 text-gray-700 text-center py-3 rounded-lg hover:bg-gray-200 transition-colors">
              Return to Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include('components/footer.php'); ?>
</body>

</html>