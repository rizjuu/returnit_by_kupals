<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch claim history records linked by user_id
$stmt = $conn->prepare("
  SELECT ch.id, i.title, i.type, ch.date_claimed 
  FROM claim_history ch
  JOIN items i ON ch.item_id = i.id
  WHERE ch.user_id = ?
  ORDER BY ch.date_claimed DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Claim History - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h2>🧭 User Panel</h2>
      <ul>
        <li><a href="user_page.php">All Items</a></li>
        <li><a href="report_item.php">Report Item</a></li>
        <li><a href="claim_item.php">Claim Item</a></li>
        <li><a href="claim_history.php" class="active">Claim History</a></li>
      </ul>
      <a href="logout.php" class="logout-btn">Logout</a>
    </aside>

    <main class="main-content">
      <header>
        <h1>Claim History</h1>
        <p>These are the items you’ve successfully claimed.</p>
      </header>

      <section class="table-section">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Type</th>
              <th>Date Claimed</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($history->num_rows > 0): ?>
              <?php while($row = $history->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= ucfirst($row['type']) ?></td>
                <td><?= date("M d, Y h:i A", strtotime($row['date_claimed'])) ?></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align:center;">No claim history found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>
