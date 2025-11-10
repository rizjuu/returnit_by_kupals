<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$item_id_from_get = $_GET['item_id'] ?? null;
$item_title = null;

if ($item_id_from_get) {
    // Fetch item details to display
    $stmt = $conn->prepare("SELECT title FROM items WHERE id = ? AND type = 'found'");
    $stmt->bind_param("i", $item_id_from_get);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if ($item) {
        $item_title = $item['title'];
    }
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'] ?? null;
    $claimant_name = $_SESSION['user_name'] ?? 'Unknown User';
    $claimant_email = $_SESSION['user_email'] ?? 'unknown@user.com';
    $message = $_POST['message'] ?? '';
    $proofPath = null;

    // 🔹 Check if user has already claimed this item
    $check_claim = $conn->prepare("SELECT id FROM claims WHERE item_id = ? AND claimant_email = ?");
    $check_claim->bind_param("is", $item_id, $claimant_email);
    $check_claim->execute();
    if ($check_claim->get_result()->num_rows > 0) {
        $msg = '⚠️ You have already submitted a claim for this item. Please check your Claim History.';
        // To prevent further execution and just show the message, we stop here.
        goto end_of_post;
    }
    $check_claim->close();

    // 🔹 Check if item exists and is FOUND
    $check = $conn->prepare("SELECT type FROM items WHERE id = ?");
    $check->bind_param("i", $item_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $msg = '❌ Item not found. Please check the Item ID.';
        exit;
    }

    $item = $result->fetch_assoc();
    if ($item['type'] === 'lost') {
        $msg = '❌ You can only claim items that are marked as FOUND.';
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
            $msg = '❌ Failed to upload proof image.';
            exit;
        }
    } else {
        $msg = '❌ Please upload a proof image.';
        exit;
    }

    // 🔹 Insert claim into database
    $stmt = $conn->prepare("
        INSERT INTO claims (item_id, claimant_name, claimant_email, proof_image, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("issss", $item_id, $claimant_name, $claimant_email, $proofPath, $message);

    if ($stmt->execute()) {
        // --- 🚀 Notification Logic ---
        $claimant_id = $_SESSION['user_id'];

        // 1. Get item title and reporter's email
        $item_info_stmt = $conn->prepare("SELECT title, reporter_email FROM items WHERE id = ?");
        $item_info_stmt->bind_param("i", $item_id);
        $item_info_stmt->execute();
        $item_info = $item_info_stmt->get_result()->fetch_assoc();
        $item_title = $item_info['title'] ?? 'Unknown Item';
        $reporter_email = $item_info['reporter_email'];

        // 2. Notify the claimant (the person who just submitted the claim)
        $message_claimant = "Your claim for the item '{$item_title}' has been submitted. Please wait for admin approval.";
        $notif_claimant = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif_claimant->bind_param("is", $claimant_id, $message_claimant);
        $notif_claimant->execute();

        // 3. Notify the reporter (the person who found the item)
        $reporter_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $reporter_stmt->bind_param("s", $reporter_email);
        $reporter_stmt->execute();
        $reporter_user = $reporter_stmt->get_result()->fetch_assoc();
        if ($reporter_user) {
            $reporter_id = $reporter_user['id'];
            $message_reporter = "Someone has claimed the item '{$item_title}' that you reported found, please surrender it to the security desk.";
            $notif_reporter = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_reporter->bind_param("is", $reporter_id, $message_reporter);
            $notif_reporter->execute();
        }
        // --- End Notification Logic ---
        echo "<script>alert('✅ Claim submitted successfully! Please wait for admin approval.'); window.location.href='claim_history.php';</script>";
    } else {
        $msg = '❌ Failed to submit claim. Please try again.';
    }

    end_of_post: // Label to jump to for displaying messages without exiting
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Claim Item - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css"> <!-- For .glass-form styles -->
  <style>
    body {
      font-family: "Poppins", sans-serif;
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden; /* Prevent scrolling on the body */
      background: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .modal-container {
      width: 600px;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(25px);
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.3);
      padding: 30px 40px;
      color: white;
      position: relative;
      animation: fadeIn 0.5s ease-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
    .back-btn {
      position: absolute;
      top: 20px;
      left: 25px;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 8px 14px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s;
      text-decoration: none;
    }
    .back-btn:hover { background: #0056b3; }
    #pageBackground {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      border: none;
      pointer-events: none;
      filter: blur(10px);
    }
    .glass-form { max-width: 100%; } /* Override max-width for this context */

    /* Spinner Styles */
    .spinner-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      display: none; /* Hidden by default */
      align-items: center;
      justify-content: center;
      border-radius: 20px; /* Match modal container */
      z-index: 10;
    }
    .spinner {
      width: 50px;
      height: 50px;
      border: 5px solid rgba(255, 255, 255, 0.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div class="modal-container">
    <a href="user_page.php" class="back-btn">⬅ Back</a>

    <?php if ($item_title): ?>
      <h2>📦 Claiming: "<?= htmlspecialchars($item_title) ?>"</h2>
    <?php else: ?>
      <h2>📦 Claim an Item</h2>
    <?php endif; ?>

    <?php if ($msg) echo "<p class='alert'>$msg</p>"; ?>

    <form method="POST" enctype="multipart/form-data" class="glass-form">
      <label for="item_id">Item ID:</label>
      <input type="number" name="item_id" id="item_id" value="<?= htmlspecialchars($item_id_from_get ?? '') ?>" required <?= $item_id_from_get ? 'readonly' : '' ?>>

      <label for="proof_image">Upload Proof of Ownership (e.g., photo, receipt):</label>
      <input type="file" name="proof_image" id="proof_image" accept="image/*" required>

      <label for="message">Message (optional):</label>
      <textarea name="message" id="message" rows="3" placeholder="Additional info about your claim..."></textarea>

      <button type="submit">Submit Claim</button>
    </form>

    <div class="spinner-overlay">
      <div class="spinner"></div>
    </div>
  </div>
  <iframe id="pageBackground" src="user_page.php" title="Background"></iframe>
  <script>
    document.querySelector('.glass-form').addEventListener('submit', function() {
      const spinner = document.querySelector('.spinner-overlay');
      if (spinner) {
        spinner.style.display = 'flex';
      }
    });
  </script>
</body>
</html>
