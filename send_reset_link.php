<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

function sendPasswordResetLink($email, $token) {
    $mail = new PHPMailer(true);
    try {
        // SMTP setup
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jeogardones@gmail.com'; // your Gmail
        $mail->Password = 'uebd uewe nasy kowg';   // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email content
        $mail->setFrom('jeogardones@gmail.com', 'Campus Lost & Found');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        
        // Construct the reset link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

        $mail->Body = "
            <h2>Password Reset Request</h2>
            <h2>From Rizjuu</h2>
            <p>You requested a password reset. Click the link below to set a new password:</p>
            <p><a href='{$reset_link}'>{$reset_link}</a></p>
            <p>This link will expire in 1 hour. If you did not request this, please ignore this email.</p>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        // For debugging: error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}