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

// Update notification status to 'read'
$stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Get updated unread count to send back to the client
        $count_result = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id={$user_id} AND status='unread'");
        $new_unread_count = $count_result ? $count_result->fetch_assoc()['c'] : 0;
        echo json_encode(['success' => true, 'new_unread_count' => $new_unread_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Notification not found or already read.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update notification status.']);
}

$stmt->close();
$conn->close();
?>