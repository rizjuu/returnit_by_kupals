<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'security') {
    header("Location: login_register.php");
    exit;
}

// Handle marking an item as released
if (isset($_GET['action']) && $_GET['action'] === 'release' && isset($_GET['claim_id'])) {
    $claim_id_to_release = (int)$_GET['claim_id'];

    // Find the claim details to get the item_id and user_id
    $claim_stmt = $conn->prepare("SELECT item_id, claimant_email FROM claims WHERE id = ? AND status = 'approved'");
    $claim_stmt->bind_param("i", $claim_id_to_release);
    $claim_stmt->execute();
    $claim_result = $claim_stmt->get_result()->fetch_assoc();

    if ($claim_result) {
        $item_id = $claim_result['item_id'];
        $claimant_email = $claim_result['claimant_email'];

        // Find the corresponding user_id from the email
        $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $user_stmt->bind_param("s", $claimant_email);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result()->fetch_assoc();
        $user_id = $user_result['id'] ?? null;

        // Update the claim_history status to 'released'
        if ($user_id) {
            $history_stmt = $conn->prepare("UPDATE claim_history SET status = 'released', date_released = NOW() WHERE item_id = ? AND user_id = ? AND status = 'approved'");
            $history_stmt->bind_param("ii", $item_id, $user_id);
            $history_stmt->execute();
        }

        // Finally, delete the claim from the pending claims table
        $delete_stmt = $conn->prepare("DELETE FROM claims WHERE id = ?");
        $delete_stmt->bind_param("i", $claim_id_to_release);
        $delete_stmt->execute();

        $_SESSION['alert'] = "✅ Item has been marked as released.";
    }

    // Redirect back to the same page to see the updated list
    header("Location: security_release.php");
    exit;
}

// Fetch items that have been approved for claiming but not yet released
$claims = $conn->query("
    SELECT 
        c.id as claim_id, 
        i.id as item_id, 
        i.title, 
        i.image, 
        u.name as claimant_name,
        u.student_id, 
        COALESCE(ch.date_claimed, c.created_at) as date_approved -- Fallback to claim creation date if history is missing
    FROM claims c
    LEFT JOIN items i ON c.item_id = i.id
    LEFT JOIN users u ON c.claimant_email = u.email
    LEFT JOIN claim_history ch ON c.item_id = ch.item_id AND ch.status = 'approved' AND u.id = ch.user_id
    WHERE c.status = 'approved'
    ORDER BY c.created_at ASC
");

$alert = $_SESSION['alert'] ?? '';
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security - Release Items</title>
    <!-- Load user.css for sidebar structure, then admin.css for dashboard theme -->
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .release-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .release-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #218838, #1e7e34);
        }
        .release-btn img { filter: brightness(0) invert(1); }
    </style>
</head>
<body>
    <div class="container">
        <?php $active_page = 'security_release'; include '_security_sidebar.php'; ?>

        <main class="main-content">
            <h1>Items Awaiting Release to Claimant</h1>
            <?php if ($alert): ?>
                <p style="text-align:center; background:#e0f7e9; color:#333; padding:10px; border-radius:8px;"><?= htmlspecialchars($alert) ?></p>
            <?php endif; ?>
            <p style="text-align: center; margin-bottom: 20px;">Verify the claimant's identity before releasing the item. Once released, the item will be removed from this list.</p>            
            <section>
                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Claimant Name</th>
                            <th>Claimant Student ID</th>
                            <th>Date Approved</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($claims && $claims->num_rows > 0): ?>
                            <?php while ($claim = $claims->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($claim['item_id']) ?></td>
                                    <td><?= htmlspecialchars($claim['title']) ?></td>
                                    <td><img src="<?= htmlspecialchars($claim['image'] ?? 'default_item.png') ?>" alt="Item Image" style="width: 80px; height: 80px; object-fit: cover;"></td>
                                    <td><?= htmlspecialchars($claim['claimant_name'] ?? 'User Deleted') ?></td>
                                    <td><?= htmlspecialchars($claim['student_id'] ?? 'N/A') ?></td>
                                    <td><?= date("M d, Y h:i A", strtotime($claim['date_approved'])) ?></td>
                                    <td class="action-links">
                                        <a href="?action=release&claim_id=<?= $claim['claim_id'] ?>" class="release-btn" onclick="return confirm('Confirm that you have released this item to the claimant?')">
                                            <img src="icons/reclaim.png" alt="" width="16" height="16"> Mark as Released
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center;">No items are currently awaiting release.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <script src="sidebar.js"></script>
</body>
</html>