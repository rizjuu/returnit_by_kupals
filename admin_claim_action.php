<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'security')) {
    header("Location: admin_claims.php"); // Redirect to the new claims page
    exit;
}

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: admin_page.php");
    exit;
}

$action = $_GET['action'];
$claim_id = intval($_GET['id']);

// Get claim details
$stmt = $conn->prepare("SELECT * FROM claims WHERE id = ?");
$stmt->bind_param("i", $claim_id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();

if (!$claim) {
    $_SESSION['alert'] = "❌ Claim not found."; // Keep original alert
    header("Location: admin_page.php");
    exit;
}

if ($action === 'approve') {
    // ✅ Update claim status
    $conn->query("UPDATE claims SET status='approved' WHERE id=$claim_id");

    // ❌ Set related item to inactive
    $conn->query("UPDATE items SET status='inactive' WHERE id={$claim['item_id']}");

    // Fetch item title for the approval notification message
    $item_title_stmt = $conn->prepare("SELECT title FROM items WHERE id = ?");
    $item_title_stmt->bind_param("i", $claim['item_id']);
    $item_title_stmt->execute();
    $item_title_result = $item_title_stmt->get_result()->fetch_assoc();
    $item_title = $item_title_result['title'] ?? 'an item'; // Default if title not found

    // 🔍 Get user_id via email (more reliable)
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $user_stmt->bind_param("s", $claim['claimant_email']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();

    if ($user_result) {
        $user_id = $user_result['id'];

        // 🕒 Move to claim history
        $history = $conn->prepare("INSERT INTO claim_history (user_id, item_id, date_claimed, status) VALUES (?, ?, NOW(), 'approved')");
        $history->bind_param("ii", $user_id, $claim['item_id']);
        $history->execute();

        // 🔔 Create a notification for the user
        $message = "✅ Your claim for item '{$item_title}' has been approved! You can now claim it at the Security Desk.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $user_id, $message);
        $notif->execute();

        $_SESSION['alert'] = "✅ Claim approved, user notified, and added to claim history.";
    } else {
        $_SESSION['alert'] = "⚠️ Claim approved, but user not found for notification.";
    }

} elseif ($action === 'reject') {
    // Notify user about rejection first
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $user_stmt->bind_param("s", $claim['claimant_email']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();

    if ($user_result) {
        // Fetch item title for the rejection notification message
        $item_title_stmt = $conn->prepare("SELECT title FROM items WHERE id = ?");
        $item_title_stmt->bind_param("i", $claim['item_id']);
        $item_title_stmt->execute();
        $item_title_result = $item_title_stmt->get_result()->fetch_assoc();
        $item_title = $item_title_result['title'] ?? 'an item'; // Default if title not found
        
        $user_id = $user_result['id'];
        $message = "❌ Your claim for item '{$item_title}' was rejected by the admin.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $user_id, $message);
        $notif->execute();
    }

    $_SESSION['alert'] = "❌ Claim rejected and user notified.";

    // ❌ Update claim status to 'rejected'
    $conn->query("UPDATE claims SET status='rejected' WHERE id=$claim_id");

    // 🧹 Now, remove the rejected claim from the pending list
    $conn->query("DELETE FROM claims WHERE id=$claim_id");
}

header("Location: admin_claims.php"); // Redirect to the new claims page
exit;
?>
