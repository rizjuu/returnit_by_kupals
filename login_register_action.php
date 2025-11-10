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

                // Handle "Remember Me"
                if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
                    $selector = bin2hex(random_bytes(16));
                    $token = random_bytes(32);
                    $expires = new DateTime('+1 month');

                    // Store token in database
                    $stmt_token = $conn->prepare("INSERT INTO auth_tokens (user_id, selector, token, expires) VALUES (?, ?, ?, ?)");
                    $hashed_token = hash('sha256', $token);
                    $stmt_token->bind_param("isss", $user['id'], $selector, $hashed_token, $expires->format('Y-m-d H:i:s'));
                    $stmt_token->execute();

                    // Set cookie
                    setcookie('remember_me', $selector . ':' . bin2hex($token), $expires->getTimestamp(), '/', '', false, true);
                }

                if ($user['role'] === 'admin') {
                    header("Location: admin_page.php");
                } elseif ($user['role'] === 'security') {
                    header("Location: security_dashboard.php");
                } else {
                    header("Location: user_page.php");
                }
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
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        $student_id = trim($_POST['student_id'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $year_level = trim($_POST['year_level'] ?? '');
        $campus = trim($_POST['campus'] ?? '');
        $terms_agreed = isset($_POST['terms']);

        if (empty($name) || empty($email) || empty($password) || empty($student_id) || empty($program) || empty($year_level) || empty($campus)) {
            $_SESSION['register_error'] = "All fields are required.";
        } elseif (!$terms_agreed) {
            $_SESSION['register_error'] = "You must agree to the Terms and Conditions to register.";
        } else {
            if ($password !== $confirm_password) {
                $_SESSION['register_error'] = "Passwords do not match.";
                goto end_registration_check;
            }
            // Check if already registered
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $_SESSION['register_error'] = "Email already registered.";
            } else {
                // Delete any existing OTP for this email
                $del = $conn->prepare("DELETE FROM email_verification WHERE email = ?");
                $del->bind_param("s", $email);
                $del->execute();

                // Generate OTP
                $otp = rand(100000, 999999);
                $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                $stmt = $conn->prepare("INSERT INTO email_verification (email, otp_code, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $otp, $expires);
                $stmt->execute();

                require_once 'send_otp.php';
                if (sendVerificationOTP($email, $otp)) {
                    // Save pending user info temporarily
                    $_SESSION['pending_name'] = $name;
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['pending_password'] = password_hash($password, PASSWORD_DEFAULT);
                    $_SESSION['pending_student_id'] = $student_id;
                    $_SESSION['pending_program'] = $program;
                    $_SESSION['pending_year_level'] = $year_level;
                    $_SESSION['pending_campus'] = $campus;

                    header("Location: verify_otp.php");
                    exit;
                } else {
                    $_SESSION['register_error'] = "Failed to send OTP. Please try again.";
                }
            }
        }
        end_registration_check:

        $_SESSION['active_form'] = 'register';
    }

    header("Location: login_register.php");
    exit;
}
?>
