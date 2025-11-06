<?php
session_start();
require_once 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Sanitize and retrieve form data
    $name = trim($_POST['name']);
    $student_id = trim($_POST['student_id']);
    $program = trim($_POST['program']);
    $year_level = trim($_POST['year_level']);
    $campus = trim($_POST['campus']);

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE users SET name = ?, student_id = ?, program = ?, year_level = ?, campus = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $student_id, $program, $year_level, $campus, $user_id);

    // Execute the statement and redirect
    if ($stmt->execute()) {
        // Optionally, set a success message in the session
        $_SESSION['profile_update_success'] = "Profile updated successfully!";
    }

    header("Location: profile.php");
    exit;
}
?>