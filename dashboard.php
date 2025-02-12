<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
<h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>

    <a href="logout.php">Logout</a>
</body>
</html>
