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
          Didn't receive the code? 
          <a href="#" id="resend-link" style="color:#fff; text-decoration:underline; display:none;">Resend OTP</a>
          <span id="resend-timer" style="color:#ccc;"></span>
        </p>
      </form>
    </div>
  </div>
  <script src="script.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const resendLink = document.getElementById('resend-link');
        const resendTimer = document.getElementById('resend-timer');
        let countdown = 300; // 5 minutes in seconds

        function startTimer() {
            resendLink.style.display = 'none';
            resendTimer.style.display = 'inline';

            const interval = setInterval(() => {
                countdown--;
                const minutes = Math.floor(countdown / 60);
                const seconds = countdown % 60;
                resendTimer.textContent = `Resend in ${minutes}:${seconds.toString().padStart(2, '0')}`;

                if (countdown <= 0) {
                    clearInterval(interval);
                    resendTimer.style.display = 'none';
                    resendLink.style.display = 'inline';
                    countdown = 300; // Reset for next time
                }
            }, 1000);
        }

        startTimer(); // Start timer on page load

        resendLink.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            fetch('resend_otp.php')
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Show success or error message from server
                    if (data.success) {
                        startTimer(); // Restart the timer only on success
                    }
                }).catch(error => alert('An error occurred. Please try again.'));
        });
    });

    // Prevent back button from leaving the page
    (function (window, location) {
        history.replaceState(null, document.title, location.pathname + "#!/stealingyourhistory");
        history.pushState(null, document.title, location.pathname);
        window.addEventListener("popstate", function () {
            if (location.hash === "#!/stealingyourhistory") {
                history.replaceState(null, document.title, location.pathname);
                setTimeout(function () {
                    location.replace("verify_otp.php");
                }, 0);
            }
        }, false);
    }(window, location));
  </script>
</body>
</html>
