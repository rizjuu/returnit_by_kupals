<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'security')) {
    header("Location: login_register.php");
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
    $_SESSION['alert'] = "❌ Claim not found.";
    header("Location: admin_page.php");
    exit;
}

if ($action === 'approve') {
    // ✅ Update claim status
    $conn->query("UPDATE claims SET status='approved' WHERE id=$claim_id");

    // ❌ Set related item to inactive
    $conn->query("UPDATE items SET status='inactive' WHERE id={$claim['item_id']}");

    // 🔍 Get user_id via email (more reliable)
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $user_stmt->bind_param("s", $claim['claimant_email']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();

    if ($user_result) {
        $user_id = $user_result['id'];

        // 🕒 Move to claim history
        $history = $conn->prepare("INSERT INTO claim_history (user_id, item_id, date_claimed) VALUES (?, ?, NOW())");
        $history->bind_param("ii", $user_id, $claim['item_id']);
        $history->execute();

        // 🔔 Create a notification for the user
        $message = "✅ Your claim for item '{$claim['claimant_name']}' has been approved! You can now claim it at the Security Desk.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $user_id, $message);
        $notif->execute();

        $_SESSION['alert'] = "✅ Claim approved, user notified, and added to claim history.";
    } else {
        $_SESSION['alert'] = "⚠️ Claim approved, but user not found for notification.";
    }

    // 🧹 Remove claim from pending list
    $conn->query("DELETE FROM claims WHERE id=$claim_id");

} elseif ($action === 'reject') {
    // ❌ Reject claim
    $conn->query("UPDATE claims SET status='rejected' WHERE id=$claim_id");

    // 🧹 Remove rejected claim
    $conn->query("DELETE FROM claims WHERE id=$claim_id");

    // Optional: Notify user about rejection
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $user_stmt->bind_param("s", $claim['claimant_email']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();

    if ($user_result) {
        $user_id = $user_result['id'];
        $message = "❌ Your claim for item '{$claim['claimant_name']}' was rejected by the admin.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $user_id, $message);
        $notif->execute();
    }

    $_SESSION['alert'] = "❌ Claim rejected and user notified.";
}

header("Location: admin_page.php");
exit;
?>
