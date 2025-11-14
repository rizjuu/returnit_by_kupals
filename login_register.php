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
  <style>
    /* This container will now only wrap the input and the icon */
    .password-container {
      position: relative;
    }

    .password-toggle-icon {
      position: absolute;
      right: 15px; /* Adjust based on input padding */
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: rgba(255, 255, 255, 0.7);
      font-size: 1.2em;
      z-index: 10; /* Ensure it's above the input field */
      padding: 5px; /* Make it easier to click */
    }
    /* Adjust input padding to prevent text from going under the icon */
    .password-container input[type="password"], .password-container input[type="text"] {
      padding-right: 40px; /* Enough space for the icon */
    }
  </style>
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
          <input type="email" id="login-email" name="email" placeholder="e.g. rzzjeoo@gmail.com" required>
        </div>
        <div class="form-group">
          <label for="login-password">Password</label>
          <div class="password-container">
            <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
            <span class="password-toggle-icon" id="toggleLoginPassword">
              &#x1F441; <!-- Eye emoji -->
            </span>
          </div>
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
        <?=showError($errors['register']); ?>
        <div class="grid-container">
          <div class="form-group">
            <label for="reg-name">Full Name</label>
            <input type="text" id="reg-name" name="name" placeholder="e.g. Rizju Honculada" required>
          </div>
          <div class="form-group">
            <label for="reg-email">Email Address</label>
            <input type="email" id="reg-email" name="email" placeholder="e.g. rzzjeoo@gmail.com" required>
          </div>
          <div class="form-group">
            <label for="reg-password">Password</label>
            <div class="password-container">
              <input type="password" id="reg-password" name="password" placeholder="Create a strong password" required>
              <span class="password-toggle-icon" id="toggleRegPassword">
                &#x1F441; <!-- Eye emoji -->
              </span>
            </div>
          </div>
          <div class="form-group">
            <label for="reg-confirm-password">Confirm Password</label>
            <div class="password-container">
              <input type="password" id="reg-confirm-password" name="confirm_password" placeholder="Confirm your password" required>
              <span class="password-toggle-icon" id="toggleRegConfirmPassword">
                &#x1F441; <!-- Eye emoji -->
              </span>
            </div>
          </div>          
          <div class="form-group">
            <label for="reg-studentid">Student ID</label>
            <input type="text" id="reg-studentid" name="student_id" placeholder="e.g. 2023304220" required>
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
          <input type="text" id="reg-campus" name="campus" placeholder="e.g. USTP CDO Main Campus" required>
        </div>
        <div class="form-group-inline">
          <input type="checkbox" id="reg-terms" name="terms" required>
          <label for="reg-terms">I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a></label>
        </div>
        <button type="submit" name="register">Register</button>
        <p class="switch-text">Already have an account?</p>
        <button type="button" class="switch-btn" onclick="showForm('login-form')">Login</button>
      </form>
    </div>
   </div>
  <script src="script.js"></script>
  <script src="password_toggle.js"></script>
</body>
</html>
