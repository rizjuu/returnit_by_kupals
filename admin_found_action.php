<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'security')) {
    header("Location: admin_found_reports.php"); // Redirect to the new found reports page
    exit;
}

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: admin_page.php");
    exit;
}

$action = $_GET['action'];
$report_id = intval($_GET['id']);

// Get report details
$stmt = $conn->prepare("
    SELECT fr.*, i.title as item_title, i.reporter_email as owner_email, u.id as finder_user_id, u.email as finder_email
    FROM found_reports fr
    JOIN items i ON fr.item_id = i.id
    JOIN users u ON fr.finder_user_id = u.id
    WHERE fr.id = ?
");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    $_SESSION['alert'] = "❌ Found report not found."; // Keep original alert
    header("Location: admin_page.php");
    exit;
}

$item_title = $report['item_title'];
$item_id = $report['item_id'];

if ($action === 'approve') {
    // Update found_reports status
    $conn->query("UPDATE found_reports SET status='approved' WHERE id=$report_id");

    // Update the original item's status to 'found'
    $conn->query("UPDATE items SET status='found' WHERE id=$item_id");

    // --- Notify both users ---

    // 1. Notify the original owner
    $owner_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $owner_stmt->bind_param("s", $report['owner_email']);
    $owner_stmt->execute();
    $owner_user = $owner_stmt->get_result()->fetch_assoc();
    if ($owner_user) {
        $owner_id = $owner_user['id'];
        $message_owner = "🎉 Good news! Your lost item '{$item_title}' has been reported as found. Please coordinate at the Security Desk.";
        $notif_owner = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif_owner->bind_param("is", $owner_id, $message_owner);
        $notif_owner->execute();
    }

    // 2. Notify the finder
    $finder_id = $report['finder_user_id'];
    $message_finder = "✅ Thank you! Your report for finding the item '{$item_title}' has been approved. Please bring the item to the Security Desk.";
    $notif_finder = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif_finder->bind_param("is", $finder_id, $message_finder);
    $notif_finder->execute();

    $_SESSION['alert'] = "✅ Report approved. The item status is now 'Found', and both users have been notified.";

} elseif ($action === 'reject') {
    // Reject the report
    $conn->query("UPDATE found_reports SET status='rejected' WHERE id=$report_id");
    $_SESSION['alert'] = "❌ Report has been rejected.";
}

header("Location: admin_found_reports.php"); // Redirect to the new found reports page
exit;
?>