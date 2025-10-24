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
    // Update claim status
    $conn->query("UPDATE claims SET status='approved' WHERE id=$claim_id");

    // Set related item to inactive
    $conn->query("UPDATE items SET status='inactive' WHERE id={$claim['item_id']}");

    // Get user_id by email instead of name (more reliable)
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $user_stmt->bind_param("s", $claim['claimant_email']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();

    if ($user_result) {
        $user_id = $user_result['id'];

        // Add to claim_history
        $history = $conn->prepare("INSERT INTO claim_history (user_id, item_id, date_claimed) VALUES (?, ?, NOW())");
        $history->bind_param("ii", $user_id, $claim['item_id']);
        $history->execute();

        $_SESSION['alert'] = "✅ Claim approved and added to user's history.";
    } else {
        $_SESSION['alert'] = "⚠️ Claim approved, but user not found.";
    }

} elseif ($action === 'reject') {
    $conn->query("UPDATE claims SET status='rejected' WHERE id=$claim_id");
    $_SESSION['alert'] = "❌ Claim rejected.";
}

header("Location: admin_page.php");
exit;
?>
