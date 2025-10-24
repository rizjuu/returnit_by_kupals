<?php
session_start();
require_once 'config.php';

$errors = [
  'login' => $_SESSION['login_error'] ?? '',
  'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';
session_unset();

function showError($error) {
  return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}
function isActiveForm($formName, $activeForm) {
  return $formName === $activeForm ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Campus Lost & Found Management System</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="bg-overlay"></div>
  <div class="form-wrapper">
    <h1 class="main-title">CAMPUS LOST & FOUND</h1>
    <p class="subtitle">by Kupal Company</p>

    <!-- LOGIN FORM -->
    <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
      <form action="login_register_action.php" method="post">
        <h2>Login</h2>
        <?= showError($errors['login']); ?>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        <p class="switch-text">Don’t have an account?</p>
        <button type="button" class="switch-btn" onclick="showForm('register-form')">Register</button>
      </form>
    </div>

    <!-- REGISTER FORM -->
    <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
      <form action="login_register_action.php" method="post">
        <h2>Register</h2>
        <?= showError($errors['register']); ?>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
        <p class="switch-text">Already have an account?</p>
        <button type="button" class="switch-btn" onclick="showForm('login-form')">Login</button>
      </form>
    </div>
  </div>
  <script src="script.js"></script>
</body>
</html>
