<?php
if (!isset($active_page)) {
    $active_page = '';
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <button id="toggle-sidebar" class="toggle-btn">☰</button>
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
  </ul>
  <a href="logout.php" class="logout-btn"><img src="icons/logout.png" alt="Logout" class="icon"></a>
</aside>