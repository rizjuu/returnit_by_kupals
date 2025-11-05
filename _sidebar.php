<?php
// This partial requires the $conn variable and an $active_page variable to be set.

if (!isset($active_page)) {
    $active_page = ''; // Default to no active page
}

$notification_count = 0;
if (isset($_SESSION['user_id'])) {
    $count_result = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id={$_SESSION['user_id']} AND status='unread'");
    if ($count_result) {
        $notification_count = $count_result->fetch_assoc()['c'];
    }
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2><span class="icon">🧭</span> <span class="title">Campus L&F</span></h2>
    <button id="toggle-sidebar" class="toggle-btn">↔</button>
  </div>
  <ul>
    <li><a href="user_page.php" class="<?= ($active_page === 'user_page') ? 'active' : '' ?>"><span class="icon">📄</span> <span class="text">All Items</span></a></li>
    <li><a href="report_item.php" class="<?= ($active_page === 'report_item') ? 'active' : '' ?>"><span class="icon">✏️</span> <span class="text">Report Item</span></a></li>
    <li><a href="claim_item.php" class="<?= ($active_page === 'claim_item') ? 'active' : '' ?>"><span class="icon">🙌</span> <span class="text">Claim Item</span></a></li>
    <li><a href="claim_history.php" class="<?= ($active_page === 'claim_history') ? 'active' : '' ?>"><span class="icon">📜</span> <span class="text">Claim History</span></a></li>
    <li><a href="notifications.php" class="<?= ($active_page === 'notifications') ? 'active' : '' ?>"><span class="icon">🔔</span> <span class="text">Notifications</span> <?php if ($notification_count > 0) echo "<span class='badge'>$notification_count</span>"; ?></a></li>
  </ul>
  <a href="logout.php" class="logout-btn"><span class="icon">🚪</span> <span class="text">Logout</span></a>
</aside>