<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

if (!isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID not provided.']);
    exit;
}

$notification_id = intval($_POST['notification_id']);
$user_id = $_SESSION['user_id'];

// Prepare and execute the delete statement
$stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $user_id);

if ($stmt->execute()) {
    // Recalculate unread count to keep the sidebar badge updated
    $count_result = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id={$user_id} AND status='unread'");
    $new_unread_count = $count_result ? $count_result->fetch_assoc()['c'] : 0;
    echo json_encode(['success' => true, 'new_unread_count' => $new_unread_count]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete notification.']);
}

$stmt->close();
$conn->close();
?>