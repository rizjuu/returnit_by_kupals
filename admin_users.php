<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit;
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Prevent deleting the main admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && $user['role'] === 'admin' && $user['email'] === 'rizju@gmail.com') {
        exit;
    }
    if ($id !== $_SESSION['user_id']) { // prevent admin from deleting self
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_users.php");
    exit;
}

// Ban/Unban user
if (isset($_GET['ban'])) {
    $id = (int)$_GET['ban'];
    if ($id !== $_SESSION['user_id']) { // prevent admin from banning self
        $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_users.php");
    exit;
}
if (isset($_GET['unban'])) {
    $id = (int)$_GET['unban'];
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_users.php");
    exit;
}

// Get search term if any
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch users, filtering by name if a search term is provided
if (!empty($search_term)) {
    $search_query = "%" . $search_term . "%";
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? ORDER BY id DESC");
    $stmt->bind_param("s", $search_query);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all users if no search
    $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link rel="stylesheet" href="user.css"> <!-- Re-using user.css for sidebar compatibility -->
  <link rel="stylesheet" href="crud.css"> <!-- Keep crud.css for table and form styling -->
  <style>
    .search-container {
      margin-bottom: 20px;
    }
    .search-form {
      display: flex;
      gap: 10px;
      background: rgba(255, 255, 255, 0.1);
      padding: 10px;
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
      text-decoration: none; /* For the 'Clear' link */
    }
    .action-buttons-container {
        margin-bottom: 20px;
    }
    .action-buttons-container button {
        background: linear-gradient(to right, #ff8c00, #ffc107);
        padding: 12px 20px;
    }
    .admin-layout .form-container {
      flex: 0 0 400px; /* Fixed width for the add form */
    }
    .admin-layout .users-table-container {
      flex: 1; /* The table will take the remaining space */
    }
    .action-btn {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        color: white;
        text-decoration: none;
        font-size: 14px;
        text-align: center;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }
    .ban-btn {
        background-color: #ffc107; /* orange/yellow */
        color: #212529;
    }
    .unban-btn {
        background-color: #28a745; /* green */
    }
    .delete-btn {
        background-color: #dc3545; /* red */
    }
    .actions-cell {
        display: flex; gap: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <?php $active_page = 'admin_users'; include '_admin_sidebar.php'; ?>

    <main class="main-content">
      <h1>Admin User Management</h1>

      <div class="action-buttons-container">
        <button>Accidents and Issues</button>
      </div>

      <div class="admin-layout">
        <section class="users-table-container">
          <h2>Existing Users</h2>
          <!-- Search Bar -->
          <div class="search-container">
            <form method="GET" action="admin_users.php" class="search-form">
              <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search_term) ?>">
              <button type="submit">Search</button>
              <?php if (!empty($search_term)): ?>
                <a href="admin_users.php" class="clear-search">Clear</a>
              <?php endif; ?>
            </form>
          </div>
          <table id="users-table">
            <tr><th>Student ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            <?php while ($u = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($u['student_id'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['role']) ?></td>
              <td class="actions-cell">
                <?php if (($u['status'] ?? 'active') === 'active'): ?>
                  <a href="?ban=<?= $u['id'] ?>" onclick="return confirm('Are you sure you want to ban this user?')" class="action-btn ban-btn">Ban</a>
                <?php else: ?>
                  <a href="?unban=<?= $u['id'] ?>" onclick="return confirm('Are you sure you want to unban this user?')" class="action-btn unban-btn">Unban</a>
                <?php endif; ?>
                <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="action-btn delete-btn">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </table>
        </section>
      </div>

    </main>
  </div>

  <script src="script.js"></script>
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
</body>
</html>
