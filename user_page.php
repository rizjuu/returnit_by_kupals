<?php
session_start();
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
    $stmt = $conn->prepare("SELECT * FROM items WHERE status='active' AND title LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $search_query);
    $stmt->execute();
    $items = $stmt->get_result();
} else {
    $items = $conn->query("SELECT * FROM items WHERE status='active' ORDER BY created_at DESC");
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
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 10px;
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
      background-color: #0056b3;
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
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <h2><span class="icon">🧭</span> <span class="title">Campus L&F</span></h2>
        <button id="toggle-sidebar" class="toggle-btn">↔</button>
      </div>
      <ul>
        <li><a href="user_page.php" class="active"><span class="icon">📄</span> <span class="text">All Items</span></a></li>
        <li><a href="report_item.php"><span class="icon">✏️</span> <span class="text">Report Item</span></a></li>
        <li><a href="claim_item.php"><span class="icon">🙌</span> <span class="text">Claim Item</span></a></li>
        <li><a href="claim_history.php"><span class="icon">📜</span> <span class="text">Claim History</span></a></li> 
        <li><a href="notifications.php"><span class="icon">🔔</span> <span class="text">Notifications</span> 
<?php
$count = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id={$_SESSION['user_id']} AND status='unread'")->fetch_assoc()['c'];
if ($count > 0) echo "<span class='badge'>$count</span>";
?>
</a></li>
      </ul>
      <a href="logout.php" class="logout-btn"><span class="icon">🚪</span> <span class="text">Logout</span></a>
    </aside>

    <main class="main-content">
      <header class="user-header">
        <div class="welcome-user">
          <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
          <button class="profile-btn" onclick="toggleProfile()">
            <img src="<?= $profile_image_url ?>" alt="Profile" class="profile-btn-img">
            <span>Profile</span>
          </button>
        </div>
        <div class="header-right">
          <form method="GET" action="user_page.php" class="search-form">
            <input type="text" name="search" placeholder="Search items by title..." value="<?= htmlspecialchars($search_term) ?>">
            <button type="submit">🔍</button>
            <?php if (!empty($search_term)): ?>
              <a href="user_page.php" class="clear-search">Clear</a>
            <?php endif; ?>
          </form>
        </div>
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
<script>
function toggleProfile() {
  window.location.href = "profile.php";
}
</script>
<script src="sidebar.js"></script>


</body>
</html>
