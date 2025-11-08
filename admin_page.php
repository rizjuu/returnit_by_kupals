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

// Fetch all active items
$items = $conn->query("
  SELECT i.*, u.name as reporter_name 
  FROM items i
  LEFT JOIN users u ON i.reporter_email = u.email
  WHERE i.status = 'active' ORDER BY i.created_at DESC
");

// Fetch ALL items for the modal
$all_items_result = $conn->query("
  SELECT 
    i.*, 
    reporter.name as reporter_name,
    CASE 
        WHEN i.type = 'found' AND i.status = 'inactive' THEN claimant.name
        WHEN i.type = 'lost' AND i.status = 'found' THEN finder.name
        ELSE NULL
    END AS solver_name
  FROM items i
  LEFT JOIN users reporter ON i.reporter_email = reporter.email
  LEFT JOIN claim_history ch ON i.id = ch.item_id AND i.type = 'found' AND i.status = 'inactive'
  LEFT JOIN users claimant ON ch.user_id = claimant.id
  LEFT JOIN found_reports fr ON i.id = fr.item_id AND i.type = 'lost' AND fr.status = 'approved'
  LEFT JOIN users finder ON fr.finder_user_id = finder.id
  ORDER BY i.created_at DESC
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
        <div class="card clickable-card" id="total-items-card" onclick="openAllItemsModal()"><h3>Total Items</h3><div class="value"><?= (int)$totalItems; ?></div></div>
        <div class="card"><h3>Active Items</h3><div class="value"><?= (int)$activeItems; ?></div></div>        
      </div>

      <!-- ITEMS TABLE -->
      <section>
        <h2>Items Reported</h2>
        <table>
          <tr>
            <th>Reporter</th>
            <th>Image</th>
            <th>Title</th>
            <th>Type</th>
            <th>Status</th>
          </tr>
          <?php while($i = $items->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($i['reporter_name'] ?? 'N/A') ?></td>
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

    </main>
  </div>

<!-- All Items Modal -->
<div id="allItemsModal" class="modal-gradient">
  <div class="modal-gradient-content">
    <span class="close-gradient" onclick="closeAllItemsModal()">&times;</span>
    <h2>All Reported Items</h2>
    <div class="modal-search-container">
        <input type="text" id="itemSearchInput" onkeyup="filterItemsTable()" placeholder="Search by item name...">
    </div>
    <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Reporter</th>
              <th>Image</th>
              <th>Title</th>
              <th>Type</th>
              <th>Status</th>
              <th>Solved By</th>
              <th>Date Reported</th>
            </tr>
          </thead>
          <tbody>
            <?php while($item = $all_items_result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($item['reporter_name'] ?? 'N/A') ?></td>
              <td>
                <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                  <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($item['image']) ?>')">🔍 View</button>
                <?php else: ?>
                  <span>No Image</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($item['title']) ?></td>
              <td><?= ucfirst($item['type']) ?></td>
              <td>
                <?php
                  $display_status = ucfirst($item['status']);
                  if ($item['type'] === 'lost' && $item['status'] === 'found') {
                      $display_status = 'Inactive'; // Display 'Inactive' for solved lost items
                  }
                  echo $display_status;
                ?>
              </td>
              <td><?= htmlspecialchars($item['solver_name'] ?? 'N/A') ?></td>
              <td><?= date("M d, Y", strtotime($item['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
    </div>
  </div>
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

// All Items Modal Logic
function openAllItemsModal() {
  document.getElementById('allItemsModal').style.display = 'flex';
}

function closeAllItemsModal() {
  document.getElementById('allItemsModal').style.display = 'none';
}

// Search logic for All Items Modal
function filterItemsTable() {
    const input = document.getElementById("itemSearchInput");
    const filter = input.value.toUpperCase();
    const table = document.querySelector("#allItemsModal table");
    const tr = table.getElementsByTagName("tr");

    // Loop through all table rows, and hide those who don't match the search query
    for (let i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
        const td = tr[i].getElementsByTagName("td")[2]; // Column for Item Title
        if (td) {
            const txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
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
  z-index:1100; /* Ensure it's on top of the gradient modal */
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

/* New Modal Styles */
.modal-gradient {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background: rgba(0, 0, 0, 0.5); /* Dark, semi-transparent background */
  backdrop-filter: blur(8px); /* Apply the blur effect */
  align-items: center;
  justify-content: center;
}
.modal-gradient-content {
  background: rgba(0, 0, 0, 0.6);
  margin: auto;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: 80%;
  max-width: 1200px;
  border-radius: 15px;
  box-shadow: 0 5px 25px rgba(0,0,0,0.4);
  position: relative;
  color: #fff;
}
.close-gradient {
  color: #fff;
  position: absolute;
  top: 10px;
  right: 25px;
  font-size: 35px;
  font-weight: bold;
  cursor: pointer;
  transition: color 0.3s;
}
.close-gradient:hover,
.close-gradient:focus {
  color: #ffdd40;
}
.table-wrapper {
  max-height: 60vh;
  overflow-y: auto;
  margin-top: 20px;
}
.modal-search-container {
    margin-bottom: 15px;
}

.modal-search-container input {
    width: 100%;
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    background: rgba(0, 0, 0, 0.4);
    color: #fff;
    font-size: 1rem;
    outline: none;
}
.modal-search-container input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}
</style>
<script>
    // Prevent back button from leaving the page
    (function (window, location) {
        history.replaceState(null, document.title, location.pathname + "#!/stealingyourhistory");
        history.pushState(null, document.title, location.pathname);
        window.addEventListener("popstate", function () {
            if (location.hash === "#!/stealingyourhistory") {
                history.replaceState(null, document.title, location.pathname);
                setTimeout(function () {
                    location.replace("admin_page.php");
                }, 0);
            }
        }, false);
    }(window, location));
</script>
</body>
</html>
