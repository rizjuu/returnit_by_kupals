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
    <button id="toggle-sidebar" class="toggle-btn">☰</button>
  </div>
  <ul>
    <li><a href="user_page.php" class="<?= ($active_page === 'user_page') ? 'active' : '' ?>"><img src="icons/dashboard.png" alt="All Items" class="icon"><span class="text">All Items</span></a></li>
    <li><a href="report_item.php" class="<?= ($active_page === 'report_item') ? 'active' : '' ?>"><img src="icons/report.png" alt="Report Item" class="icon"><span class="text">Report Item</span></a></li>
    <li><a href="claim_history.php" class="<?= ($active_page === 'claim_history') ? 'active' : '' ?>"><img src="icons/history.png" alt="Claim History" class="icon"><span class="text">Claim History</span></a></li>
    <li><a href="notifications.php" class="<?= ($active_page === 'notifications') ? 'active' : '' ?>"><img src="icons/notification.png" alt="Notifications" class="icon"><span class="text">Notifications</span> <?php if ($notification_count > 0) echo "<span class='badge'>$notification_count</span>"; ?></a></li>
  </ul>
</aside>