<?php
session_start();
require_once 'config.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['pending_email'])) {
    header("Location: login_register.php");
    exit;
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['pending_email'];
    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("
    SELECT * FROM email_verification 
    WHERE email = ? AND otp_code = ? AND expires_at > NOW()
    LIMIT 1
");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verified, create user
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES (?, ?, ?, 'user')
    ");
    $stmt->bind_param("sss", $_SESSION['pending_name'], $_SESSION['pending_email'], $_SESSION['pending_password']);
    $stmt->execute();

    // Clean up
    $del = $conn->prepare("DELETE FROM email_verification WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();

    session_unset();
    $_SESSION['register_success'] = "Email verified successfully. You can now log in.";
    header("Location: login_register.php");
    exit;
} else {
    $msg = "Invalid or expired OTP.";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify OTP</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="form-wrapper">
    <h1>Email Verification</h1>
    <p>Enter the 6-digit OTP sent to your email.</p>
    <?php if ($msg): ?><p class="error-message"><?= $msg ?></p><?php endif; ?>
    <form method="POST">
      <input type="text" name="otp" placeholder="Enter OTP" required maxlength="6">
      <button type="submit">Verify</button>
      <p style="margin-top: 10px;">
  <a href="login_register.php" style="color:#fff; text-decoration:underline;">← Back to Login</a>
</p>
    </form>
  </div>
</body>
</html>
