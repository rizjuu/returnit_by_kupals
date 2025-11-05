<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'security')) {
    header("Location: login_register.php");
    exit;
}

// Get dashboard counts
$totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'] ?? 0;
$totalItems = $conn->query("SELECT COUNT(*) AS cnt FROM items")->fetch_assoc()['cnt'] ?? 0;
$activeItems = $conn->query("SELECT COUNT(*) AS cnt FROM items WHERE status='active'")->fetch_assoc()['cnt'] ?? 0;
$pendingClaims = $conn->query("SELECT COUNT(*) AS cnt FROM claims WHERE status='pending'")->fetch_assoc()['cnt'] ?? 0;

// Fetch all active items
$items = $conn->query("SELECT * FROM items WHERE status='active' ORDER BY created_at DESC");

// Fetch pending claims
$claims = $conn->query("
  SELECT c.id, i.title, c.claimant_name, c.claimant_email, c.status, i.image, c.proof_image
  FROM claims c 
  JOIN items i ON c.item_id = i.id
  WHERE c.status = 'pending'
");

$alert = $_SESSION['alert'] ?? '';
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="user.css"> <!-- Sidebar styles -->
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="container">
    <?php $active_page = 'admin_dashboard'; include '_admin_sidebar.php'; ?>

    <main class="main-content">
      <h1>Admin / Security Dashboard</h1>

      <?php if ($alert): ?>
        <p style="text-align:center; background:#e0f7e9; color:#333; padding:10px; border-radius:8px;">
          <?= htmlspecialchars($alert) ?>
        </p>
      <?php endif; ?>

      <div class="cards">
        <div class="card"><h3>Total Users</h3><div class="value"><?= (int)$totalUsers; ?></div></div>
        <div class="card"><h3>Total Items</h3><div class="value"><?= (int)$totalItems; ?></div></div>
        <div class="card"><h3>Active Items</h3><div class="value"><?= (int)$activeItems; ?></div></div>
        <div class="card"><h3>Pending Claims</h3><div class="value"><?= (int)$pendingClaims; ?></div></div>
      </div>

      <!-- ITEMS TABLE -->
      <section>
        <h2>📦 Items Reported</h2>
        <table>
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Title</th>
            <th>Type</th>
            <th>Status</th>
          </tr>
          <?php while($i = $items->fetch_assoc()): ?>
          <tr>
            <td><?= $i['id'] ?></td>
            <td>
              <?php if (!empty($i['image']) && file_exists($i['image'])): ?>
                <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($i['image']) ?>')">🔍 View</button>
              <?php else: ?>
                <span>No Image</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($i['title']) ?></td>
            <td><?= ucfirst($i['type']) ?></td>
            <td><?= ucfirst($i['status']) ?></td>
          </tr>
          <?php endwhile; ?>
        </table>
      </section>

      <!-- CLAIMS TABLE -->
      <section>
      <h2>📋 Pending Claims</h2>
      <table>
        <tr>
          <th>ID</th>
          <th>Item</th>
          <th>Claimant</th>
          <th>Proof Image</th>
          <th>Item Image</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php while($c = $claims->fetch_assoc()): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td><?= htmlspecialchars($c['title']) ?></td>
          <td><?= htmlspecialchars($c['claimant_name']) ?><br><small><?= htmlspecialchars($c['claimant_email']) ?></small></td>
          <td>
            <?php if (!empty($c['proof_image']) && file_exists($c['proof_image'])): ?>
              <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($c['proof_image']) ?>')">🔍 View Proof</button>
            <?php else: ?>
              <span>No Proof</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($c['image']) && file_exists($c['image'])): ?>
              <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($c['image']) ?>')">🔍 View Item</button>
            <?php else: ?>
              <span>No Image</span>
            <?php endif; ?>
          </td>
          <td><?= ucfirst($c['status']) ?></td>
          <td class="action-links">
            <a href="admin_claim_action.php?action=approve&id=<?= $c['id'] ?>">✅</a>
            <a href="admin_claim_action.php?action=reject&id=<?= $c['id'] ?>">❌</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    </section>
    </main>
  </div>

<!-- Image Viewer Modal -->
<div id="imageModal" class="modal" onclick="closeModal()">
  <span class="close" onclick="closeModal()">&times;</span>
  <img id="modalImage" class="modal-content">
</div>

<script>
function viewImage(src) {
  const modal = document.getElementById('imageModal');
  const img = document.getElementById('modalImage');
  img.src = src;
  modal.style.display = 'block';
}
function closeModal() {
  document.getElementById('imageModal').style.display = 'none';
}
</script>
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

<style>
.modal {
  display:none;
  position:fixed;
  z-index:999;
  padding-top:50px;
  left:0;top:0;width:100%;height:100%;
  background-color:rgba(0,0,0,0.9);
}
.modal-content {
  margin:auto;
  display:block;
  max-width:90%;
  max-height:90%;
}
.close {
  position:absolute;
  top:20px;right:35px;
  color:white;
  font-size:40px;
  font-weight:bold;
  cursor:pointer;
}
.view-btn {
  background:#ffcc33;
  border:none;
  padding:5px 10px;
  border-radius:6px;
  cursor:pointer;
}
.view-btn:hover {
  background:#e6b800;
}
</style>
</body>
</html>
