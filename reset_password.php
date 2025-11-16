<?php
session_start();
require_once 'config.php';
date_default_timezone_set('Asia/Manila');

$token = $_GET['token'] ?? '';
$msg = '';
$msg_type = '';
$token_valid = false;

if (empty($token)) {
    $msg = "No token provided. Please use the link from your email.";
    $msg_type = 'error';
} else {
    $hashed_token = hash('sha256', $token);

    // Check if token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $hashed_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token_valid = true;
        $reset_request = $result->fetch_assoc();
        $email = $reset_request['email'];

        // Handle form submission for new password
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $msg = "Passwords do not match.";
                $msg_type = 'error';
            } elseif (strlen($password) < 6) {
                $msg = "Password must be at least 6 characters long.";
                $msg_type = 'error';
            } else {
                // Update user's password
                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $new_hashed_password, $email);
                
                if ($update_stmt->execute()) {
                    // Expire the token immediately so it cannot be reused.
                    $delete_stmt = $conn->prepare("UPDATE password_resets SET expires_at = NOW() WHERE token = ?");
                    $delete_stmt->bind_param("s", $hashed_token);
                    $delete_stmt->execute();

                    $_SESSION['register_success'] = "Password has been reset successfully. You can now log in with your new password.";
                    $_SESSION['active_form'] = 'login'; // Ensure login form is active
                    header("Location: login_register.php");
                    exit;
                } else {
                    $msg = "Failed to update password. Please try again.";
                    $msg_type = 'error';
                }
            }
        }
    } else {
        $msg = "Invalid or expired token. Please request a new reset link.";
        $msg_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="bg-overlay"></div>
  <div class="form-wrapper">
    <div class="form-box active" id="reset-password-form">
      <form method="POST">
        <h2>Reset Your Password</h2>
        <?php if ($msg): ?>
            <p class="<?= $msg_type === 'success' ? 'success-message' : 'error-message' ?>" style="display:block;"><?= $msg ?></p>
        <?php endif; ?>

        <?php if ($token_valid): ?>
            <div class="form-group">
              <label for="password">New Password</label>
              <input type="password" id="password" name="password" placeholder="Enter your new password" required>
            </div>
            <div class="form-group">
              <label for="confirm_password">Confirm New Password</label>
              <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
            </div>
            <button type="submit">Reset Password</button>
        <?php endif; ?>
      </form>
    </div>
  </div>
</body>
</html>