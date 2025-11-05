<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error: " . $file['error']);
    }

    $targetDir = "uploads/profile_images/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowed)) {
        die("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
    }

    $fileName = uniqid("profile_") . "." . $fileExt;
    $targetPath = $targetDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Save to DB
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("si", $targetPath, $user_id);
        $stmt->execute();

        // Redirect back to profile
        header("Location: profile.php?upload=success");
        exit;
    } else {
        die("Failed to move uploaded file.");
    }
} else {
    die("No file uploaded.");
}
?>
