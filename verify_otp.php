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
    INSERT INTO users (name, email, password, student_id, program, year_level, campus, role)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'user')
");
$stmt->bind_param(
    "sssssss",
    $_SESSION['pending_name'],
    $_SESSION['pending_email'],
    $_SESSION['pending_password'],
    $_SESSION['pending_student_id'],
    $_SESSION['pending_program'],
    $_SESSION['pending_year_level'],
    $_SESSION['pending_campus']
);
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
  <div class="bg-overlay"></div>
  <div class="form-wrapper">
    <h1 class="main-title">CAMPUS LOST & FOUND</h1>
    <p class="subtitle">by Kupal Company</p>

    <div class="form-box active" id="verify-form">
      <form method="POST">
        <h2>Email Verification</h2>
        <p style="color: #eee; text-align: center; margin-bottom: 15px;">Enter the 6-digit OTP sent to your email.</p>
        <?php if ($msg): ?><p class="error-message" style="display:block;"><?= $msg ?></p><?php endif; ?>
        <div class="form-group">
          <label for="otp-code">One-Time Password</label-for>
          <input id="otp-code" type="text" name="otp" placeholder="Enter 6-digit code" required maxlength="6" pattern="\d{6}" title="OTP must be 6 digits.">
        </div>
        <button type="submit">Verify</button>
        <p class="switch-text" style="margin-top: 15px;">
          <a href="login_register.php" style="color:#fff; text-decoration:underline;">← Back to Login/Register</a>
        </p>
      </form>
    </div>
  </div>
  <script src="script.js"></script>
</body>
</html>
