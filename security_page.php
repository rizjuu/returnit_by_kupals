<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'security') {
    header("Location: login_register.php");
    exit;
}

// Handle marking an item as surrendered
if (isset($_GET['action']) && $_GET['action'] === 'surrender' && isset($_GET['item_id'])) {
    $item_id_to_surrender = (int)$_GET['item_id'];
    
    // Update the item's status to 'surrendered' and record the timestamp
    $stmt = $conn->prepare("UPDATE items SET status = 'surrendered', surrendered_at = NOW() WHERE id = ? AND status = 'found'");
    $stmt->bind_param("i", $item_id_to_surrender);
    $stmt->execute();
    
    // Redirect back to the same page to see the updated list
    header("Location: security_page.php");
    exit;
}

// Fetch items that have been approved as 'found' but not yet surrendered
$items_query = $conn->query("
    SELECT i.id, i.title, i.description, i.location, i.date_lost_found, i.image, u.name as reporter_name
    FROM items i
    JOIN users u ON i.reporter_email = u.email
    WHERE i.status = 'found'
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Personnel Page</title>
    <!-- Load user.css for sidebar structure, then admin.css for dashboard theme -->
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <?php $active_page = 'security_surrender'; include '_security_sidebar.php'; ?>

        <main class="main-content">
            <h1>Items Awaiting Surrender</h1>
            <section>
                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Location Found</th>
                            <th>Date Found</th>
                            <th>Image</th>
                            <th>Reporter Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items_query && $items_query->num_rows > 0): ?>
                        <?php while ($item = $items_query->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['id']) ?></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td><?= htmlspecialchars($item['location']) ?></td>
                                <td><?= htmlspecialchars($item['date_lost_found']) ?></td>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="Item Image" style="width: 80px; height: 80px; object-fit: cover;">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['reporter_name']) ?></td>
                                <td class="action-links">
                                    <a href="?action=surrender&item_id=<?= $item['id'] ?>" class="approve-btn" onclick="return confirm('Confirm that you have received this item from the finder?')">Mark as Received</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center;">No items are currently awaiting surrender.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <script src="sidebar.js"></script>
</body>
</html>