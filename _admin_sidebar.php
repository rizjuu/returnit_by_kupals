<?php
if (!isset($active_page)) {
    $active_page = '';
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2><span class="icon">🛡️</span> <span class="title">Admin Panel</span></h2>
    <button id="toggle-sidebar" class="toggle-btn">↔</button>
  </div>
  <ul>
    <li>
      <a href="admin_page.php" class="<?= ($active_page === 'admin_dashboard') ? 'active' : '' ?>">
        <span class="icon">📊</span> <span class="text">Dashboard</span>
      </a>
    </li>
    <li>
      <a href="admin_users.php" class="<?= ($active_page === 'admin_users') ? 'active' : '' ?>">
        <span class="icon">👤</span> <span class="text">Manage Users</span>
      </a>
    </li>
  </ul>
  <a href="logout.php" class="logout-btn"><span class="icon">🚪</span> <span class="text">Logout</span></a>
</aside>