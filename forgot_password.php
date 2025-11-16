<?php
session_start();
require_once 'config.php';
require_once 'send_reset_link.php'; // We will create this file next
date_default_timezone_set('Asia/Manila');

$msg = '';
$msg_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Check for recent password reset requests to implement rate limiting
        $rate_limit_stmt = $conn->prepare("SELECT expires_at FROM password_resets WHERE email = ? AND expires_at > NOW() - INTERVAL 11 HOUR");
        $rate_limit_stmt->bind_param("s", $email);
        $rate_limit_stmt->execute();
        $rate_limit_result = $rate_limit_stmt->get_result();

        if ($rate_limit_result->num_rows > 0) {
            $msg = "A password reset link has already been sent recently. Please check your email or try again later.";
            $msg_type = 'error';
        } else {
            // Generate a unique token
            if (function_exists('random_bytes')) {
                $token = bin2hex(random_bytes(50));
            } else {
                // Fallback for environments where random_bytes is not available
                $token = bin2hex(openssl_random_pseudo_bytes(50));
            }
            $expires = new DateTime('+1 hour');

            // Delete any old, expired tokens for this email to keep the table clean
            $del_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $del_stmt->bind_param("s", $email);
            $del_stmt->execute();

            // Store the new token in the database
            $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            
            if ($insert_stmt) {
                $hashed_token = hash('sha256', $token);
                $expires_formatted = $expires->format('Y-m-d H:i:s');
                $insert_stmt->bind_param("sss", $email, $hashed_token, $expires_formatted);
                
                if ($insert_stmt->execute() && sendPasswordResetLink($email, $token)) {
                    $msg = "A password reset link has been sent to your email address.";
                    $msg_type = 'success';
                } else {
                    $msg = "Failed to send reset link. Please try again later.";
                    $msg_type = 'error';
                }
            } else {
                $msg = "Database error. Could not prepare the password reset request.";
                $msg_type = 'error';
            }
        }
    } else {
        $msg = "No account found with that email address.";
        $msg_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="bg-overlay"></div>
  <div class="form-wrapper">
    <h1 class="main-title">CAMPUS LOST & FOUND</h1>
    <p class="subtitle">by Kupal Company</p>

    <div class="form-box active" id="forgot-password-form">
      <form method="POST">
        <h2>Forgot Password</h2>
        <p style="color: #eee; text-align: center; margin-bottom: 15px;">Enter your email address and we will send you a link to reset your password.</p>
        
        <?php if ($msg): ?>
            <p class="<?= $msg_type === 'success' ? 'success-message' : 'error-message' ?>" style="display:block;"><?= $msg ?></p>
        <?php endif; ?>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input id="email" type="email" name="email" placeholder="Enter your registered email" required>
        </div>
        <button type="submit">Send Reset Link</button>
        <p class="switch-text" style="margin-top: 15px;">
          Remember your password? <a href="login_register.php" style="color:#fff; text-decoration:underline;">Login</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>