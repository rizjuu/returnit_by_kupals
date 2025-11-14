<?php
if (!isset($active_page)) {
    $active_page = '';
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <button id="toggle-sidebar" class="toggle-btn">☰ <span class="menu-text">MENU</span></button>
  </div>
  <ul>
    <li>
      <a href="security_dashboard.php" class="<?= ($active_page === 'security_dashboard') ? 'active' : '' ?>">
        <img src="icons/dashboard.png" alt="Dashboard" class="icon" width="20" height="20"> <span class="text">Dashboard</span>
      </a>
    </li>
    <li>
      <a href="security_page.php" class="<?= ($active_page === 'security_surrender') ? 'active' : '' ?>">
        <img src="icons/pendingfound.png" alt="Receive Items" class="icon" width="20" height="20"> <span class="text">Receive Items</span>
      </a>
    </li>
    <li>
      <a href="security_release.php" class="<?= ($active_page === 'security_release') ? 'active' : '' ?>">
        <img src="icons/reclaim.png" alt="Release Items" class="icon" width="20" height="20"> <span class="text">Release Items</span>
      </a>
    </li>
  </ul>
  <a href="logout.php" class="logout-btn"><img src="icons/logout.png" alt="Logout" class="icon"> <span class="text">Logout</span></a>
</aside>