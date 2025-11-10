<?php
session_start();
require_once 'config.php';

// 1. Check if user is logged in and has the 'security' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'security') {
    header("Location: login_register.php");
    exit;
}

// 2. Fetch items that have been marked as 'surrendered' (received by security)
$items = $conn->query("
    SELECT 
        i.id, 
        i.title, 
        i.description, 
        i.location, 
        i.date_lost_found, 
        i.image, 
        u.name as reporter_name,
        i.surrendered_at
    FROM items i
    JOIN users u ON i.reporter_email = u.email
    WHERE i.status = 'surrendered'
    ORDER BY i.surrendered_at DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Dashboard</title>
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <?php $active_page = 'security_dashboard'; include '_security_sidebar.php'; ?>

        <main class="main-content">
            <h1>Security Dashboard: Received Items</h1>
            <p>This page lists all items that have been physically surrendered to the security desk by finders.</p>

            <section class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Surrendered By (Finder)</th>
                            <th>Date Surrendered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items && $items->num_rows > 0): ?>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td><?= htmlspecialchars($item['title']) ?></td>
                                    <td>
                                        <?php if ($item['image']): ?>
                                            <button class="view-btn" onclick="viewImage('<?= htmlspecialchars($item['image']) ?>')">🔍 View</button>
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['reporter_name']) ?></td>
                                    <td><?= date("M d, Y h:i A", strtotime($item['surrendered_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No items have been surrendered yet.</td>
                            </tr>
                        <?php endif; ?>
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

    <script src="script.js"></script>
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
</body>
</html>