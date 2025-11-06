<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$item_id = $_GET['item_id'] ?? null;
if (!$item_id) {
    header("Location: user_page.php");
    exit;
}

// Fetch item details to display
$stmt = $conn->prepare("SELECT title FROM items WHERE id = ? AND type = 'lost'");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    // Item not found or is not a 'lost' item
    header("Location: user_page.php");
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $finder_id = $_SESSION['user_id'];
    $description = $_POST['description'] ?? '';
    $proofPath = null;

    // 🔹 Check if user has already reported this item as found
    $check_report = $conn->prepare("SELECT id FROM found_reports WHERE item_id = ? AND finder_user_id = ?");
    $check_report->bind_param("ii", $item_id, $finder_id);
    $check_report->execute();
    if ($check_report->get_result()->num_rows > 0) {
        $msg = '⚠️ You have already reported this item as found. The owner will be notified once the admin approves your report.';
        // To prevent further execution and just show the message, we stop here.
        goto end_of_post;
    }
    $check_report->close();

    // Handle proof image upload
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/found_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['proof_image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetFile)) {
            $proofPath = $targetFile;
        } else {
            $msg = "❌ Failed to upload proof image.";
        }
    }

    if (empty($msg)) {
        // Insert into the new found_reports table
        $stmt = $conn->prepare("INSERT INTO found_reports (item_id, finder_user_id, description, proof_image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $item_id, $finder_id, $description, $proofPath);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Thank you for your report! The admin will review it and notify the owner.'); window.location.href='user_page.php';</script>";
            exit;
        } else {
            $msg = "❌ Failed to submit report. Please try again.";
        }
    }

    end_of_post: // Label to jump to for displaying messages without exiting
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report Found Item - Campus Lost & Found</title>
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

    <h2>🤝 You Found: "<?= htmlspecialchars($item['title']) ?>"</h2>
    <p>Thank you for helping a fellow student! Please provide some details below.</p>

    <?php if ($msg) echo "<p class='alert'>$msg</p>"; ?>

    <form method="POST" enctype="multipart/form-data" class="glass-form">
      <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id) ?>">

      <label for="description">Where did you find it? (e.g., Library 2nd floor, near the cafe)</label>
      <textarea name="description" id="description" rows="4" placeholder="Provide details here..." required></textarea>

      <label for="proof_image">Upload a Photo (Optional, but helpful)</label>
      <input type="file" name="proof_image" id="proof_image" accept="image/*">

      <button type="submit">Submit Found Report</button>
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