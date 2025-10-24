<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

// Fetch active items
$items = $conn->query("SELECT * FROM items WHERE status='active' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">
  
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h2>🧭 User Panel</h2>
      <ul>
        <li><a href="user_page.php" class="active">All Items</a></li>
        <li><a href="report_item.php">Report Item</a></li>
        <li><a href="claim_item.php">Claim Item</a></li>
        <li><a href="claim_history.php">Claim History</a></li> 
      </ul>
      <a href="logout.php" class="logout-btn">Logout</a>
    </aside>

    <main class="main-content">
      <header>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
        <p>Here are all active lost & found reports.</p>
      </header>

      <section class="table-section">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Title</th>
              <th>Type</th>
              <th>Location</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $items->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td>
  <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
    <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($row['image']) ?>')">🔍 View</button>
  <?php else: ?>
    <span class="no-image">No Image</span>
  <?php endif; ?>
</td>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= ucfirst($row['type']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td><?= $row['date_lost_found'] ?></td>
              <td><span class="badge"><?= ucfirst($row['status']) ?></span></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
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
