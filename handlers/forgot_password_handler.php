<?php
session_start();
require_once '../config/database.php';
require '../vendor/autoload.php'; // Make sure you have PHPMailer installed via composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Email not found in our records.";
        header('Location: ../index.php');
        exit();
    }
    
    // Generate reset code
    $reset_code = sprintf("%06d", mt_rand(0, 999999));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    error_log("Generated reset code: " . $reset_code . " for email: " . $email);
    
    // Clear any existing reset codes first
    $clear_stmt = $conn->prepare("UPDATE users SET reset_code = NULL, reset_code_expiry = NULL WHERE email = ?");
    $clear_stmt->bind_param("s", $email);
    $clear_stmt->execute();
    
    // Store new reset code
    $stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_code_expiry = ? WHERE email = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['error'] = "Database error occurred";
        header('Location: ../index.php');
        exit();
    }
    
    $stmt->bind_param("sss", $reset_code, $expiry, $email);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $_SESSION['error'] = "Failed to store reset code";
        header('Location: ../index.php');
        exit();
    }
    
    // Verify the code was stored correctly
    $verify_stmt = $conn->prepare("SELECT reset_code, reset_code_expiry FROM users WHERE email = ?");
    $verify_stmt->bind_param("s", $email);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $stored_data = $verify_result->fetch_assoc();
    
    error_log("Stored reset code: " . $stored_data['reset_code'] . ", Expiry: " . $stored_data['reset_code_expiry']);
    
    if ($stored_data['reset_code'] !== $reset_code) {
        error_log("Code mismatch - Stored: " . $stored_data['reset_code'] . ", Generated: " . $reset_code);
        $_SESSION['error'] = "Error generating reset code";
        header('Location: ../index.php');
        exit();
    }

    // Send email with PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jb3.jcope2024@nrgitii.edu.ph'; // Your Gmail address
        $mail->Password = 'ynddhincexjfiukn'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('jb3.jcope2024@nrgitii.edu.ph', 'Casita De Grands');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code - Casita De Grands';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>Password Reset Request</h2>
                <p>You have requested to reset your password. Here is your reset code:</p>
                <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; 
                            letter-spacing: 5px; margin: 20px 0;'>
                    {$reset_code}
                </div>
                <p>This code will expire in 1 hour.</p>
                <p>If you did not request this reset, please ignore this email.</p>
                <p style='color: #666; font-size: 14px;'>
                    Best regards,<br>
                    Casita De Grands Team
                </p>
            </div>
        ";
        
        $mail->send();
        $_SESSION['success'] = "Reset code has been sent to your email.";
        $_SESSION['reset_email'] = $email; // Store email for reset process
        
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    
    header('Location: ../index.php');
    exit();
}
?>