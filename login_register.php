<?php
session_start();
require_once 'config.php';

$errors = [
  'login' => $_SESSION['login_error'] ?? '',
  'register' => $_SESSION['register_error'] ?? '',
];
$success_msg = $_SESSION['register_success'] ?? '';
$activeForm = $_SESSION['active_form'] ?? 'login';
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['active_form'], $_SESSION['register_success']);

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

    <?php if ($success_msg): ?>
      <div class="success-message">
        <?= $success_msg ?>
      </div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
      <form action="login_register_action.php" method="post">
        <h2>Login</h2>
        <?= showError($errors['login']); ?>
        <div class="form-group">
          <label for="login-email">Email Address</label>
          <input type="email" id="login-email" name="email" placeholder="e.g. juan@delacruz.com" required>
        </div>
        <div class="form-group">
          <label for="login-password">Password</label>
          <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="form-group-inline">
          <input type="checkbox" id="remember-me" name="remember_me" value="1">
          <label for="remember-me">Remember Me</label>
        </div>
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
        <div class="form-group">
          <label for="reg-name">Full Name</label>
          <input type="text" id="reg-name" name="name" placeholder="e.g. Juan Dela Cruz" required>
        </div>
        <div class="form-group">
          <label for="reg-email">Email Address</label>
          <input type="email" id="reg-email" name="email" placeholder="e.g. juan@delacruz.com" required>
        </div>
        <div class="form-group">
          <label for="reg-password">Password</label>
          <input type="password" id="reg-password" name="password" placeholder="Create a strong password" required>
        </div>
        <div class="form-group">
          <label for="reg-studentid">Student ID</label>
          <input type="text" id="reg-studentid" name="student_id" placeholder="e.g. 2021-12345" required>
        </div>
        <div class="form-group">
          <label for="reg-program">Program</label>
          <input type="text" id="reg-program" name="program" placeholder="e.g. BSIT" required>
        </div>
        <div class="form-group">
          <label for="reg-year">Year Level</label>
          <select id="reg-year" name="year_level" required>
            <option value="" disabled selected>Select your year level</option>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
          </select>
        </div>
        <div class="form-group">
          <label for="reg-campus">School Campus</label>
          <input type="text" id="reg-campus" name="campus" placeholder="e.g. Main Campus" required>
        </div>
        <button type="submit" name="register">Register</button>
        <p class="switch-text">Already have an account?</p>
        <button type="button" class="switch-btn" onclick="showForm('login-form')">Login</button>
      </form>
    </div>
  </div>
  <script src="script.js"></script>
</body>
</html>
