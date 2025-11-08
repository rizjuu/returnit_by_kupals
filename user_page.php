<?php
session_start();
require_once 'auth_check.php'; // Auto-login check

require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

// Fetch user data for profile picture
$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();
$profile_image_url = !empty($user_data['profile_image']) && file_exists($user_data['profile_image']) ? htmlspecialchars($user_data['profile_image']) : 'default_user.png';


// Get search term if any
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch active items, filtering by title if a search term is provided
if (!empty($search_term)) {
    $search_query = "%" . $search_term . "%";
    $stmt = $conn->prepare("
      SELECT i.*, u.name as reporter_name 
      FROM items i 
      LEFT JOIN users u ON i.reporter_email = u.email 
      WHERE i.status='active' AND i.title LIKE ? 
      ORDER BY i.created_at DESC
    ");
    $stmt->bind_param("s", $search_query);
    $stmt->execute();
    $items = $stmt->get_result();
} else {
    $items = $conn->query("
      SELECT i.*, u.name as reporter_name 
      FROM items i LEFT JOIN users u ON i.reporter_email = u.email 
      WHERE i.status='active' ORDER BY i.created_at DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">

  <style>
    .user-header {
      display: flex;
      justify-content: space-between; /* This will push children to the edges */
      align-items: center;
      gap: 20px; /* Add some space between elements */
    }
    .welcome-user {
      display: flex;
      align-items: center;
      gap: 15px; /* Space between welcome message and button */
      flex-grow: 1; /* Allow this to take up available space */
    }
    .profile-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      background-color: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 6px 12px;
      border-radius: 50px; /* Pill shape */
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .profile-btn-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
      vertical-align: middle;
    }
    .profile-btn:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }
    
    /* === Profile Dropdown === */
    .profile-container {
      position: relative;
      display: inline-block;
    }
    .profile-dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: rgba(30, 40, 50, 0.9);
      backdrop-filter: blur(10px);
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.4);
      z-index: 1;
      border-radius: 10px;
      overflow: hidden; /* Ensures children conform to border-radius */
    }
    .profile-dropdown-content a {
      color: white;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      transition: background-color 0.2s;
    }
    .profile-dropdown-content a:hover {
      background-color: rgba(0, 191, 255, 0.3);
    }
    .show {
      display: block;
      animation: fadeIn 0.3s;
    }

    .dropdown-icon {
      width: 16px;
      height: 16px;
      margin-right: 10px;
      vertical-align: text-bottom;
      filter: invert(1); /* Make icon white */
    }


    /* === Search Section === */
    .search-form {
      display: flex;
      gap: 10px;
      background: rgba(255, 255, 255, 0.1); /* Keep the background */
      padding: 10px; /* Adjust padding */
      border-radius: 10px;
    }

    .search-form input {
      flex-grow: 1;
      padding: 10px;
      border: none;
      border-radius: 6px;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      outline: none;
    }

    .search-form input::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .search-form button, .search-form .clear-search {
      padding: 10px 15px;
      border: none;
      border-radius: 6px;
      background: linear-gradient(to right, #00BFFF, #87CEEB);
      color: white;
      cursor: pointer;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <?php $active_page = 'user_page'; include '_sidebar.php'; ?>

    <main class="main-content">
      <header class="user-header">
        <div class="welcome-user">
          <h1 style="font-size: 1.8rem; color: #f0f8ff; letter-spacing: 1px; margin: 0; text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">CAMPUS LOST & FOUND</h1>
          <p style="margin: 0; color: #ccc;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>
        <div class="header-actions">
          <form method="GET" action="user_page.php" class="search-form">
            <input type="text" name="search" placeholder="Search items by title..." value="<?= htmlspecialchars($search_term) ?>">
            <button type="submit">🔍</button>
            <?php if (!empty($search_term)): ?>
              <a href="user_page.php" class="clear-search">Clear</a>
            <?php endif; ?>
          </form>
          <div class="profile-container">
            <button onclick="toggleDropdown()" class="profile-btn">
              <img src="<?= $profile_image_url ?>" alt="Profile" class="profile-btn-img">
              <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </button>
            <div id="myDropdown" class="profile-dropdown-content">
              <a href="profile.php"><img src="icons/profile.png" alt="Profile" class="dropdown-icon"> Profile</a>
              <a href="logout.php"><img src="icons/logout.png" alt="Logout" class="dropdown-icon"> Logout</a>
            </div>
          </div>
        </div>
      </header> <!-- This was missing -->

      <section class="table-section">
        <table>
          <thead>
            <tr>
              <th>Reporter</th>
              <th>Image</th>
              <th>Title</th>
              <th>Type</th>
              <th>Location</th>
              <th>Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $items->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['reporter_name'] ?? 'N/A') ?></td>
              <td>
  <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
    <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($row['image']) ?>')">View</button>
  <?php else: ?>
    <span class="no-image">No Image</span>
  <?php endif; ?>
</td>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= ucfirst($row['type']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td><?= $row['date_lost_found'] ?></td>
              <td><span class="badge"><?= ucfirst($row['status']) ?></span></td>
              <td>
                <?php if ($row['type'] === 'lost' && $row['status'] === 'active'): ?>
                  <a href="report_found_item.php?item_id=<?= $row['id'] ?>" class="view-btn found-it-btn">Found</a>
                <?php elseif ($row['type'] === 'found' && $row['status'] === 'active'): ?>
                  <a href="claim_item.php?item_id=<?= $row['id'] ?>" class="view-btn claim-now-btn">Claim</a>
                <?php endif; ?>
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

/* === Dropdown Logic === */
function toggleDropdown() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.profile-btn, .profile-btn *')) {
    var dropdowns = document.getElementsByClassName("profile-dropdown-content");
    for (var i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
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
.found-it-btn {
    background: linear-gradient(135deg, #28a745, #2dc468); /* Vibrant Green */
    color: white;
    text-decoration: none;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    border: none;
}
.found-it-btn:hover {
    background: linear-gradient(135deg, #218838, #28a745);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}
.claim-now-btn {
    background: linear-gradient(135deg, #ff6b6b, #ff8e53); /* Vibrant Orange/Red */
    color: white;
    text-decoration: none;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    border: none;
}
.claim-now-btn:hover {
    background: linear-gradient(135deg, #e05252, #ff6b6b);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
<script src="sidebar.js"></script>
<script>
    // Prevent back button from leaving the page
    (function (window, location) {
        history.replaceState(null, document.title, location.pathname + "#!/stealingyourhistory");
        history.pushState(null, document.title, location.pathname);
        window.addEventListener("popstate", function () {
            if (location.hash === "#!/stealingyourhistory") {
                history.replaceState(null, document.title, location.pathname);
                setTimeout(function () {
                    location.replace("user_page.php");
                }, 0);
            }
        }, false);
    }(window, location));
</script>


</body>
</html>
