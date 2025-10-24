<?php
session_start();
require_once 'config.php';

// Handle POST only
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
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $_SESSION['register_error'] = "Email already registered.";
                unset($_SESSION['register_success']);
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->bind_param("sss", $name, $email, $hash);
                $stmt->execute();
                $_SESSION['register_success'] = "Registration successful! You can login now.";
                unset($_SESSION['register_error']);
            }
        }
        $_SESSION['active_form'] = 'register';
    }

    header("Location: login_register.php");
    exit;
}
?>
