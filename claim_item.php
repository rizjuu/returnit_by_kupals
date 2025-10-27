<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'] ?? null;
    $claimant_name = $_SESSION['user_name'] ?? 'Unknown User';
    $claimant_email = $_SESSION['user_email'] ?? 'unknown@user.com';
    $message = $_POST['message'] ?? '';
    $proofPath = null;

    // 🔹 Check if item exists and is FOUND
    $check = $conn->prepare("SELECT type FROM items WHERE id = ?");
    $check->bind_param("i", $item_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('❌ Item not found. Please check the Item ID.'); window.location.href='claim_item.php';</script>";
        exit;
    }

    $item = $result->fetch_assoc();
    if ($item['type'] === 'lost') {
        echo "<script>alert('❌ You can only claim items that are marked as FOUND.'); window.location.href='claim_item.php';</script>";
        exit;
    }

    // 🔹 Handle proof image upload
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['proof_image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetFile)) {
            $proofPath = $targetFile;
        } else {
            echo "<script>alert('❌ Failed to upload proof image.'); window.location.href='claim_item.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('❌ Please upload a proof image.'); window.location.href='claim_item.php';</script>";
        exit;
    }

    // 🔹 Insert claim into database
    $stmt = $conn->prepare("
        INSERT INTO claims (item_id, claimant_name, claimant_email, proof_image, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("issss", $item_id, $claimant_name, $claimant_email, $proofPath, $message);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Claim submitted successfully! Please wait for admin approval.'); window.location.href='claim_history.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to submit claim. Please try again.'); window.location.href='claim_item.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Claim Item - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h2> User Panel</h2>
      <ul>
        <li><a href="user_page.php">All Items</a></li>
        <li><a href="report_item.php">Report Item</a></li>
        <li><a href="claim_item.php" class="active">Claim Item</a></li>
        <li><a href="claim_history.php">Claim History</a></li> 
      </ul>
      <a href="logout.php" class="logout-btn">Logout</a>
    </aside>

    <main class="main-content">
      <h2>📦 Claim an Item</h2>

      <form method="POST" enctype="multipart/form-data" class="glass-form">
        <label for="item_id">Enter Item ID:</label>
        <input type="number" name="item_id" id="item_id" required>

        <label for="proof_image">Upload Proof of Ownership (e.g., photo, receipt):</label>
        <input type="file" name="proof_image" id="proof_image" accept="image/*" required>

        <label for="message">Message (optional):</label>
        <textarea name="message" id="message" rows="3" placeholder="Additional info about your claim..."></textarea>

        <button type="submit">Submit Claim</button>
      </form>
    </main>
  </div>
</body>
</html>
