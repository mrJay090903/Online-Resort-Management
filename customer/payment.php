<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has booking data
if (!isset($_SESSION['user_id']) || !isset($_POST['booking_data'])) {
    header('Location: reservations.php');
    exit();
}

$booking_data = json_decode($_POST['booking_data'], true);
$down_payment = $booking_data['total_amount'] * 0.5;

// PayMongo API Keys
define('PAYMONGO_SECRET_KEY', 'sk_test_PLPKHXfcCfZFc5xNHpSDZi9b');
define('PAYMONGO_PUBLIC_KEY', 'pk_test_NewY1j9hH13RknRJyTHCCcxB');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment - Casita De Grands</title>
  <link href="../src/output.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Add PayMongo Elements -->
  <script src="https://js.paymongo.com/v2/paymongo.js"></script>
  <!-- Add SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Add these in the head section if not already present -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</head>

<body class="bg-gray-50">
  <?php include('components/nav.php'); ?>

  <div class="pt-24 pb-16">
    <div class="container mx-auto px-6">
      <div class="max-w-2xl mx-auto">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Payment Details</h2>

        <!-- Booking Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
          <h3 class="text-lg font-semibold mb-4">Booking Summary</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">Guest Name:</span>
              <span class="font-medium"><?php echo $booking_data['customer_name']; ?></span>
            </div>

            <?php if ($booking_data['room_id']): ?>
            <div class="flex justify-between">
              <span class="text-gray-600">Room:</span>
              <span class="font-medium"><?php echo $booking_data['room_name']; ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($booking_data['venues'])): ?>
            <div class="flex justify-between">
              <span class="text-gray-600">Venues:</span>
              <span class="font-medium">
                <?php echo implode(', ', array_map(function($venue) {
                  return $venue['name'];
                }, $booking_data['venues'])); ?>
              </span>
            </div>
            <?php endif; ?>

            <div class="flex justify-between">
              <span class="text-gray-600">Check-in:</span>
              <span class="font-medium"><?php echo $booking_data['check_in']; ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Check-out:</span>
              <span class="font-medium"><?php echo $booking_data['check_out']; ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Booking Type:</span>
              <span class="font-medium">
                <?php echo $booking_data['booking_type'] === 'day' ? 'Day Use (8AM-5PM)' : 'Night Use (6PM-7AM)'; ?>
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Number of Guests:</span>
              <span class="font-medium"><?php echo $booking_data['guests']; ?></span>
            </div>

            <!-- Price Breakdown -->
            <div class="pt-4 border-t">
              <?php if ($booking_data['room_id']): ?>
              <div class="flex justify-between text-sm">
                <span>Room Rate
                  (<?php echo $booking_data['booking_type'] === 'day' ? 'Day Use' : 'Night Use'; ?>):</span>
                <span>₱<?php echo number_format($booking_data['price_per_day'], 2); ?> ×
                  <?php echo $booking_data['days']; ?> days</span>
              </div>
              <div class="flex justify-between text-sm pl-4 text-gray-600">
                <span>Total Room Cost:</span>
                <span>₱<?php echo number_format($booking_data['room_price'], 2); ?></span>
              </div>
              <?php endif; ?>

              <?php if (!empty($booking_data['venues'])): ?>
              <div class="flex justify-between text-sm mt-2">
                <span>Venue Fees:</span>
                <span>₱<?php echo number_format($booking_data['venue_total'], 2); ?></span>
              </div>
              <?php foreach ($booking_data['venues'] as $venue): ?>
              <div class="flex justify-between text-xs text-gray-600 pl-4">
                <span><?php echo $venue['name']; ?>:</span>
                <span>₱<?php echo number_format($venue['price'], 2); ?> × <?php echo $booking_data['days']; ?> days =
                  ₱<?php echo number_format($venue['total_price'], 2); ?></span>
              </div>
              <?php endforeach; ?>
              <?php endif; ?>

              <div class="flex justify-between font-medium mt-2 pt-2 border-t border-gray-100">
                <span>Total Amount (<?php echo $booking_data['days']; ?> days):</span>
                <span>₱<?php echo number_format($booking_data['total_amount'], 2); ?></span>
              </div>
              <div class="flex justify-between text-emerald-600 font-medium">
                <span>Required Down Payment (50%):</span>
                <span>₱<?php echo number_format($down_payment, 2); ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold mb-4">Select Payment Method</h3>

          <!-- GCash Button -->
          <button id="gcashButton" class="w-full bg-blue-500 text-white py-3 rounded-lg mb-3 hover:bg-blue-600">
            Pay with GCash
          </button>

          <!-- Credit/Debit Card Button -->
          <button id="cardButton" class="w-full bg-gray-800 text-white py-3 rounded-lg hover:bg-gray-900">
            Pay with Card
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php include('components/footer.php'); ?>

  <script>
  let paymongoInstance;

  // Initialize PayMongo Elements
  function initializePayMongo() {
    paymongoInstance = new PayMongo('<?php echo PAYMONGO_PUBLIC_KEY; ?>');
  }

  // Handle GCash payment
  document.getElementById('gcashButton').addEventListener('click', async () => {
    try {
      // Show loading
      Swal.fire({
        title: 'Processing Payment',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Create payment source
      const response = await fetch('../handlers/create_payment_intent.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          amount: <?php echo $down_payment; ?>,
          booking_data: <?php echo json_encode($booking_data); ?>
        })
      });

      const data = await response.json();
      console.log('Payment response:', data);

      if (!data.success) {
        throw new Error(data.message || 'Failed to create payment');
      }

      // Store booking data
      const sessionResponse = await fetch('store_booking_session.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          booking_data: <?php echo json_encode($booking_data); ?>,
          source_id: data.source_id
        })
      });

      const sessionResult = await sessionResponse.json();
      if (!sessionResult.success) {
        throw new Error('Failed to store booking data');
      }

      // Redirect to GCash
      window.location.href = data.checkout_url;

    } catch (error) {
      console.error('Payment Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Payment Error',
        text: error.message || 'An error occurred while processing your payment',
        confirmButtonColor: '#059669'
      });
    }
  });

  // Hide card payment button for now
  document.getElementById('cardButton').style.display = 'none';

  // Initialize PayMongo when page loads
  initializePayMongo();
  </script>
</body>

</html>