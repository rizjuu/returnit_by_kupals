<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit;
}

// Create user
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_users.php");
    exit;
}

// Update user
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $role, $id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin_users.php");
    exit;
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $_SESSION['user_id']) { // prevent admin from deleting self
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_users.php");
    exit;
}

// Fetch all users
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link rel="stylesheet" href="crud.css">
</head>
<body>
  <h1>Admin User Management</h1>
  <a href="admin_page.php" class="back">← Back to Dashboard</a>
  <a href="logout.php" class="logout">Logout</a>

  <section class="form-container">
    <h2>Add New User</h2>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role">
        <option value="user">User</option>
        <option value="security">Security</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit">Add User</button>
    </form>
  </section>

  <section>
    <h2>Existing Users</h2>
    <table>
      <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
      <?php while ($u = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td>
          <button onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', '<?= htmlspecialchars($u['email']) ?>', '<?= $u['role'] ?>')">✏️ Edit</button>
          <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete this user?')" style="color:red;">🗑 Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </section>

  <div id="editModal" class="form-container" style="display:none;">
    <h2>Edit User</h2>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="editId">
      <input type="text" name="name" id="editName" placeholder="Name" required>
      <input type="email" name="email" id="editEmail" placeholder="Email" required>
      <input type="password" name="password" placeholder="New Password (optional)">
      <select name="role" id="editRole">
        <option value="user">User</option>
        <option value="security">Security</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit">Update</button>
      <button type="button" onclick="closeModal()">Cancel</button>
    </form>
  </div>

  <script src="script.js"></script>
</body>
</html>
