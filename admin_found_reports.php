<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'security')) {
    header("Location: login_register.php");
    exit;
}

// Fetch pending found reports
$found_reports = $conn->query("
    SELECT fr.id, i.title, u.name as finder_name, u.email as finder_email, fr.description, fr.proof_image
    FROM found_reports fr
    JOIN items i ON fr.item_id = i.id
    JOIN users u ON fr.finder_user_id = u.id
    WHERE fr.status = 'pending'
");

$alert = $_SESSION['alert'] ?? '';
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Pending Found Reports</title>
  <link rel="stylesheet" href="user.css"> <!-- Sidebar styles -->
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="container">
    <?php $active_page = 'admin_found_reports'; include '_admin_sidebar.php'; ?>

    <main class="main-content">
      <h1>Pending Found Reports</h1>

      <?php if ($alert): ?>
        <p style="text-align:center; background:#e0f7e9; color:#333; padding:10px; border-radius:8px;">
          <?= htmlspecialchars($alert) ?>
        </p>
      <?php endif; ?>

      <!-- PENDING FOUND REPORTS TABLE -->
      <section>
        <h2> Pending Found Reports</h2>
        <table>
          <thead>
            <tr>
              <th>Report ID</th>
              <th>Item Title</th>
              <th>Finder</th>
              <th>Finder's Description</th>
              <th>Proof</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while($fr = $found_reports->fetch_assoc()): ?>
            <tr>
              <td><?= $fr['id'] ?></td>
              <td><?= htmlspecialchars($fr['title']) ?></td>
              <td><?= htmlspecialchars($fr['finder_name']) ?><br><small><?= htmlspecialchars($fr['finder_email']) ?></small></td>
              <td><?= htmlspecialchars($fr['description']) ?></td>
              <td>
                <?php if (!empty($fr['proof_image']) && file_exists($fr['proof_image'])): ?>
                  <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($fr['proof_image']) ?>')">🔍 View</button>
                <?php endif; ?>
              </td>
              <td class="action-links">
                <a href="admin_found_action.php?action=approve&id=<?= $fr['id'] ?>" onclick="return confirm('Approve this report? This will notify both users.')">✅</a>
                <a href="admin_found_action.php?action=reject&id=<?= $fr['id'] ?>" onclick="return confirm('Reject this report?')">❌</a>
              </td>
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
<script>
  document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById('sidebar');
      const toggleBtn = document.getElementById('toggle-sidebar');
      const mainContent = document.querySelector('.main-content');

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