<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: user_page.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Campus Lost & Found</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>
  <div class="landing-container">
    <div class="overlay"></div>

    <div class="content">
      <h1 class="title">CAMPUS LOST & FOUND</h1>
      <p class="subtitle">Connecting Lost Items Back to Their Owners</p>
      <a href="login_register.php" class="btn">Login / Register</a>
    </div>

    <footer class="footer">Developed by <strong>Kupal Company</strong></footer>
  </div>
</body>
</html>
