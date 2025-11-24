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
    SELECT fr.*, i.title as item_title, 
           owner.id as owner_user_id, owner.name as owner_name, owner.email as owner_email,
           finder.id as finder_user_id, finder.name as finder_name, finder.email as finder_email
    FROM found_reports fr
    JOIN items i ON fr.item_id = i.id
    JOIN users finder ON fr.finder_user_id = finder.id
    JOIN users owner ON i.reporter_email = owner.email
    WHERE fr.id = ? AND fr.status = 'pending'
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
    
    // Set the original item to 'inactive' as it's now considered claimed and off the public board
    $conn->query("UPDATE items SET status='inactive' WHERE id=$item_id");
    
    // --- Create an automatic 'approved' claim for the owner ---
    $claim_stmt = $conn->prepare("INSERT INTO claims (item_id, claimant_name, claimant_email, status, created_at) VALUES (?, ?, ?, 'approved', NOW())");
    $claim_stmt->bind_param("iss", $item_id, $report['owner_name'], $report['owner_email']);
    $claim_stmt->execute();
    
    // --- Add to claim history for the owner ---
    $owner_id = $report['owner_user_id'];
    $history_stmt = $conn->prepare("INSERT INTO claim_history (user_id, item_id, date_claimed, status) VALUES (?, ?, NOW(), 'approved')");
    $history_stmt->bind_param("ii", $owner_id, $item_id);
    $history_stmt->execute();
    
    // --- Notify both users with updated messages ---
    
    // 1. Notify the original owner that their item is ready for pickup
    $message_owner = "✅ Great news! Your lost item '{$item_title}' has been found and your claim is automatically approved. You can now pick it up at the Security Desk.";
    $notif_owner = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif_owner->bind_param("is", $owner_id, $message_owner);
    $notif_owner->execute();
    
    // 2. Notify the finder to surrender the item
    $finder_id = $report['finder_user_id'];
    $message_finder = "✅ Thank you! Your report for finding the item '{$item_title}' has been approved. Please bring the item to the Security Desk.";
    $notif_finder = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif_finder->bind_param("is", $finder_id, $message_finder);
    $notif_finder->execute();
    
    $_SESSION['alert'] = "✅ Report approved. An automatic claim has been created for the owner, and both users are notified.";
    
} elseif ($action === 'reject') {
    // Reject the report
    $conn->query("UPDATE found_reports SET status='rejected' WHERE id=$report_id");
    // You might want to notify the finder of the rejection here as well.
    $_SESSION['alert'] = "❌ Report has been rejected.";
}

header("Location: admin_found_reports.php"); // Redirect to the new found reports page
exit;
?>