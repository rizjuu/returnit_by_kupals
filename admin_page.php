<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'security')) {
    header("Location: login_register.php");
    exit;
}

$claims = $conn->query("
  SELECT c.id, i.title, c.claimant_name, c.status, i.image, c.proof_image
  FROM claims c 
  JOIN items i ON c.item_id = i.id
  WHERE c.status = 'pending'
");


$totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'] ?? 0;
$totalItems = $conn->query("SELECT COUNT(*) AS cnt FROM items")->fetch_assoc()['cnt'] ?? 0;
$activeItems = $conn->query("SELECT COUNT(*) AS cnt FROM items WHERE status='active'")->fetch_assoc()['cnt'] ?? 0;
$pendingClaims = $conn->query("SELECT COUNT(*) AS cnt FROM claims WHERE status='pending'")->fetch_assoc()['cnt'] ?? 0;

$items = $conn->query("SELECT * FROM items WHERE status='active' ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin.css">

</head>
<body>
  <h1>Admin / Security Dashboard</h1>

  <div style="text-align: center;">
    <a href="admin_users.php" class="btn">👤 Manage Users</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
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
            <img src="<?= htmlspecialchars($i['image']) ?>" alt="Item Image" class="item-image"style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ccc;">>
          <?php else: ?>
            <span class="no-image">No Image</span>
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
  <h2>📋 Claims</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>Item</th>
      <th>Claimant</th>
      <th>Proof</th>
      <th>Item Image</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php while($c = $claims->fetch_assoc()): ?>
    <tr>
      <td><?= $c['id'] ?></td>
      <td><?= htmlspecialchars($c['title']) ?></td>
      <td><?= htmlspecialchars($c['claimant_name']) ?></td>
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
