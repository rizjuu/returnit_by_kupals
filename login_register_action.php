<?php
session_start();
require_once 'config.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $name = $_POST['name'] ?? '';

    if (isset($_POST['login'])) {
        // LOGIN
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                header("Location: " . ($user['role'] === 'admin' ? 'admin_page.php' : 'user_page.php'));
                exit;
            } else {
                $_SESSION['login_error'] = "Incorrect password.";
                $_SESSION['active_form'] = 'login';
            }
        } else {
            $_SESSION['login_error'] = "Email not found.";
            $_SESSION['active_form'] = 'login';
        }
    }

    elseif (isset($_POST['register'])) {
        // REGISTER
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['register_error'] = "All fields are required.";
        } else {
            // Check if already registered
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $_SESSION['register_error'] = "Email already registered.";
            } else {
                // Delete old OTPs for this email
                $del = $conn->prepare("DELETE FROM email_verification WHERE email = ?");
                $del->bind_param("s", $email);
                $del->execute();

                // Generate new OTP
                $otp = rand(100000, 999999);
                $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

                $stmt = $conn->prepare("INSERT INTO email_verification (email, otp_code, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $otp, $expires);
                $stmt->execute();

                require_once 'send_otp.php';
                if (sendVerificationOTP($email, $otp)) {
                    // Store pending user info
                    $_SESSION['pending_name'] = $name;
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['pending_password'] = password_hash($password, PASSWORD_DEFAULT);

                    header("Location: verify_otp.php");
                    exit;
                } else {
                    $_SESSION['register_error'] = "Failed to send OTP. Please try again.";
                }
            }
        }

        $_SESSION['active_form'] = 'register';
    }

    header("Location: login_register.php");
    exit;
}
?>
