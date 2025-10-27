<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
require_once 'config.php';

function sendVerificationOTP($email, $otp) {
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
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = "
            <h2>Campus Lost & Found Verification</h2>
            <p>Your verification code is:</p>
            <h3 style='color:#0d47a1;'>$otp</h3>
            <p>This code will expire in 5 minutes.</p>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
