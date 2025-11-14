<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'security') {
    header("Location: login_register.php");
    exit;
}

// Get dashboard counts
$items_to_receive = $conn->query("SELECT COUNT(*) as cnt FROM items WHERE status = 'found'")->fetch_assoc()['cnt'] ?? 0;
$items_to_release = $conn->query("SELECT COUNT(*) as cnt FROM claims WHERE status = 'approved'")->fetch_assoc()['cnt'] ?? 0;


$alert = $_SESSION['alert'] ?? '';
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Security Dashboard</title>
  <link rel="stylesheet" href="user.css"> <!-- Sidebar styles -->
  <link rel="stylesheet" href="admin.css"> 
</head>
<body>
  <div class="container">
    <?php $active_page = 'security_dashboard'; include '_security_sidebar.php'; ?>

    <main class="main-content">
      <h1>Security Dashboard</h1>
      <?php if ($alert): ?>
        <p style="text-align:center; background:#e0f7e9; color:#333; padding:10px; border-radius:8px;">
          <?= htmlspecialchars($alert) ?>
        </p>
      <?php endif; ?>

      <div class="cards">
        <div class="card clickable-card" onclick="window.location.href='security_page.php'">
            <h3>Items to Receive</h3><div class="value"><?= (int)$items_to_receive ?></div></div>
        <div class="card clickable-card" onclick="window.location.href='security_release.php'">
            <h3>Items to Release</h3><div class="value"><?= (int)$items_to_release ?></div></div>
      </div>
      <p style="margin-top: 20px;">Welcome to the security dashboard. From here you can manage items being surrendered by finders and items being released to claimants.</p>

    </main>
  </div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById('sidebar');
      const toggleBtn = document.getElementById('toggle-sidebar');
      const mainContent = document.querySelector('.main-content');

      // Check for saved sidebar state
      if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
      }

      if (toggleBtn) {
          toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
          });
      }
  });
</script>

</body>
</html>