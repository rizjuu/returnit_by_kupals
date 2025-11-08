<?php
if (!isset($active_page)) {
    $active_page = '';
}

// Fetch counts for pending items to display as badges.
// Assumes $conn is available from the parent script (which includes config.php).
$pending_claims_count = 0;
$pending_reports_count = 0;
if (isset($conn)) {
    $claims_result = $conn->query("SELECT COUNT(*) AS c FROM claims WHERE status='pending'");
    if ($claims_result) {
        $pending_claims_count = $claims_result->fetch_assoc()['c'];
    }
    $reports_result = $conn->query("SELECT COUNT(*) AS c FROM found_reports WHERE status='pending'");
    if ($reports_result) {
        $pending_reports_count = $reports_result->fetch_assoc()['c'];
    }
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <button id="toggle-sidebar" class="toggle-btn">☰ <span class="menu-text">MENU</span></button>
  </div>
  <ul>
    <li>
      <a href="admin_page.php" class="<?= ($active_page === 'admin_dashboard') ? 'active' : '' ?>">
        <img src="icons/dashboard.png" alt="Dashboard" class="icon" width="20" height="20"> <span class="text">Dashboard</span>
      </a>
    </li>
    <li>
      <a href="admin_users.php" class="<?= ($active_page === 'admin_users') ? 'active' : '' ?>">
        <img src="icons/users.png" alt="Manage Users" class="icon" width="20" height="20"> <span class="text">Manage Users</span>
      </a>
    </li>
    <li>
      <a href="admin_claims.php" class="<?= ($active_page === 'admin_claims') ? 'active' : '' ?>">
        <img src="icons/pendingclaims.png" alt="Pending Claims" class="icon" width="20" height="20"> 
        <span class="text">Pending Claims</span> <?php if ($pending_claims_count > 0) echo "<span class='badge'>$pending_claims_count</span>"; ?>
      </a>
    </li>
    <li>
      <a href="admin_found_reports.php" class="<?= ($active_page === 'admin_found_reports') ? 'active' : '' ?>">
        <img src="icons/pendingfound.png" alt="Pending Found Reports" class="icon" width="20" height="20"> 
        <span class="text">Pending Found</span> <?php if ($pending_reports_count > 0) echo "<span class='badge'>$pending_reports_count</span>"; ?>
      </a>
    </li>
  </ul>
  <a href="logout.php" class="logout-btn"><img src="icons/logout.png" alt="Logout" class="icon"></a>
</aside>