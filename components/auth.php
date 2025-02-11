<?php
session_start();
include 'database.php';

// Handle Signup
if (isset($_POST['signup'])) {
    $customerName = $_POST['fullName'];
    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    $stmt = $pdo->prepare("INSERT INTO users (customer_name, contact_number, email, password) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$customerName, $contactNumber, $email, $password])) {
        echo "User  registered successfully!";
    } else {
        echo "Error registering user.";
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Start session and set user data
        $_SESSION['user_id'] = $user['id'];
        echo "Login successful!";
    } else {
        echo "Invalid email or password.";
    }
}
?>