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
        <img src="icons/report.png" alt="Items to Surrender" class="icon" width="20" height="20"> <span class="text">Awaiting Surrender</span>
      </a>
    </li>
  </ul>
  <a href="logout.php" class="logout-btn"><img src="icons/logout.png" alt="Logout" class="icon"> <span class="text">Logout</span></a>
</aside>