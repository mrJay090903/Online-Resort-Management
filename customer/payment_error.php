<?php
session_start();

// Redirect if no error message
if (!isset($_SESSION['payment_error'])) {
    header('Location: reservations.php');
    exit();
}

$error_data = $_SESSION['payment_error'];
unset($_SESSION['payment_error']); // Clear the message after showing
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Casita De Grands</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include('components/nav.php'); ?>

    <div class="min-h-screen pt-24 pb-16">
        <div class="container mx-auto px-6">
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-8">
                    <!-- Error Icon -->
                    <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>

                    <!-- Error Message -->
                    <h2 class="text-2xl font-semibold text-center text-gray-800 mb-2">
                        <?php echo htmlspecialchars($error_data['title']); ?>
                    </h2>
                    <p class="text-center text-gray-600 mb-6">
                        <?php echo htmlspecialchars($error_data['message']); ?>
                    </p>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <a href="reservations.php" class="block w-full bg-emerald-600 text-white text-center py-3 rounded-lg hover:bg-emerald-700 transition-colors">
                            Try Again
                        </a>
                        <a href="customer_dashboard.php" class="block w-full bg-gray-100 text-gray-700 text-center py-3 rounded-lg hover:bg-gray-200 transition-colors">
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