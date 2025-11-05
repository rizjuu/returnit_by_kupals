<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifications - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">
</head>
<body>
  <div class="container">
    <?php $active_page = 'notifications'; include '_sidebar.php'; ?>

    <main class="main-content">
      <h2>🔔 Your Notifications</h2>
      <div class="notifications-list">
        <?php if ($notifications->num_rows > 0): ?>
          <?php while($n = $notifications->fetch_assoc()): ?>
            <div class="notification-card <?= $n['status'] === 'unread' ? 'unread' : '' ?>">
              <p><?= htmlspecialchars($n['message']) ?></p>
              <span class="time"><?= date("F j, Y, g:i a", strtotime($n['created_at'])) ?></span>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No notifications yet.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script src="script.js"></script>
  <script src="sidebar.js"></script>
</body>
</html>
