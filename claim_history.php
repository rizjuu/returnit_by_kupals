<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get search term if any
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query
$base_query = "
  -- Part 1: Items this user has successfully claimed
  SELECT i.id AS item_id, i.title, 'Claimed' AS activity_type, ch.date_claimed AS activity_date, ch.status, ch.date_released
  FROM claim_history ch
  JOIN items i ON ch.item_id = i.id
  WHERE ch.user_id = ?
  UNION ALL
  -- Part 2: Items this user has reported as 'found'
  SELECT id, title, 'Reported Found', created_at, status, NULL FROM items WHERE reporter_email = ? AND type = 'found'
";

if (!empty($search_term)) {
    // Wrap the UNION query and apply a WHERE clause to the result
    $query = "SELECT * FROM ({$base_query}) AS combined_history WHERE title LIKE ? ORDER BY activity_date DESC";
    $stmt = $conn->prepare($query);
    $search_param = "%" . $search_term . "%";
    $stmt->bind_param("iss", $user_id, $_SESSION['user_email'], $search_param);
} else {
    // Original query without search
    $query = "SELECT * FROM ({$base_query}) AS combined_history ORDER BY activity_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $_SESSION['user_email']);
}

$stmt->execute();
$history = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Activity History - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">
  <style>
    /* Using styles from admin_users.php for consistency */
    .search-container { margin-bottom: 20px; }
    .search-form { display: flex; gap: 10px; background: rgba(255, 255, 255, 0.1); padding: 10px; border-radius: 10px; max-width: 500px; }
    .search-form input { flex-grow: 1; padding: 10px; border: none; border-radius: 6px; background: rgba(255, 255, 255, 0.2); color: #fff; outline: none; }
    .search-form input::placeholder { color: rgba(255, 255, 255, 0.7); }
    .search-form button, .search-form .clear-search { padding: 10px 15px; border: none; border-radius: 6px; background: linear-gradient(to right, #00BFFF, #87CEEB); color: white; cursor: pointer; text-decoration: none; }
  </style>
</head>
<body>
  <div class="container">
    <?php $active_page = 'claim_history'; include '_sidebar.php'; ?>

    <main class="main-content">
      <header>
        <h1>My Activity History</h1>
        <p>This page shows items you've successfully claimed and items you've reported as found.</p>
      </header>

      <!-- Search Bar -->
      <div class="search-container">
        <form method="GET" action="claim_history.php" class="search-form">
          <input type="text" name="search" placeholder="Search by item title..." value="<?= htmlspecialchars($search_term) ?>">
          <button type="submit">Search</button>
          <?php if (!empty($search_term)): ?>
            <a href="claim_history.php" class="clear-search">Clear</a>
          <?php endif; ?>
        </form>
      </div>

      <section class="table-section">
        <table>
          <thead>
            <tr>
              <th>Item ID</th>
              <th>Item Title</th>
              <th>Activity</th>
              <th>Date</th>
              <th>Status</th>
              <th>Notes / Date Released</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($history->num_rows > 0): ?>
              <?php while($row = $history->fetch_assoc()): ?>
              <tr>
                <td><?= $row['item_id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><span class="badge <?= strtolower(htmlspecialchars($row['activity_type'])) ?>"><?= htmlspecialchars($row['activity_type']) ?></span></td>
                <td><?= date("M d, Y h:i A", strtotime($row['activity_date'])) ?></td>
                <td>
                  <?php if ($row['activity_type'] === 'Reported Found'): ?>
                    <span class="badge waiting">Awaiting Claimant</span>
                  <?php else: ?>
                    <span class="badge <?= htmlspecialchars($row['status']) ?>">
                      <?= ucfirst(htmlspecialchars($row['status'])) ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    if ($row['activity_type'] === 'Claimed') {
                        echo $row['date_released'] ? date("M d, Y h:i A", strtotime($row['date_released'])) : 'Pending Release';
                    } else {
                        echo 'Thank you for reporting!';
                    }
                  ?>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center;">No activity history found. <?= !empty($search_term) ? 'Try a different search term.' : '' ?></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
  <script src="sidebar.js"></script>
</body>
</html>
