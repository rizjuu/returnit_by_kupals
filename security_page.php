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
$items = $conn->query("
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
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="user.css">
    <style>
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .item-table th, .item-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .item-table th {
            background-color: #f2f2f2;
        }
        .item-image {
            max-width: 100px;
            max-height: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php $active_page = 'security_surrender'; include '_security_sidebar.php'; ?>

        <main class="main-content">
            <h1>Items Awaiting Surrender</h1>
            <table class="item-table" id="items-awaiting-surrender">
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
                    <?php if ($items && $items->num_rows > 0): ?>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['id']) ?></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td><?= htmlspecialchars($item['location']) ?></td>
                                <td><?= htmlspecialchars($item['date_lost_found']) ?></td>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="Item Image" class="item-image">
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
        </main>
    </div>
    <script src="script.js"></script>
</body>
</html>