<?php
session_start();

// Clear payment session data
unset($_SESSION['payment_source_id']);
unset($_SESSION['pending_booking']);

$_SESSION['error'] = "Payment failed or was cancelled. Please try again.";
header('Location: reservations.php');
exit();
?> 