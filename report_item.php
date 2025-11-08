<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $type = $_POST['type'];
    $loc = $_POST['location'];
    $date = $_POST['date'];
    $email = $_POST['contact_info'];

    // 🔹 Handle Image Upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // 🔹 Insert data into the database
    $stmt = $conn->prepare("INSERT INTO items (title, description, image, location, date_lost_found, type, contact_info, reporter_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $title, $desc, $imagePath, $loc, $date, $type, $email, $email);
    $msg = $stmt->execute() ? "✅ Item reported successfully." : "❌ Error reporting item.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report Lost/Found - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">
  <style>
    .report-layout {
      display: flex;
      gap: 30px;
      align-items: flex-start;
    }
    .glass-form {
      flex: 1.5; /* Give the form a bit more space */
      max-width: 700px;
    }
    .reminders-section {
      flex: 1;
      padding: 25px;
      background: rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      color: #f0f8ff;
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .reminders-section h3 {
      margin-top: 0;
      color: #00BFFF;
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
      padding-bottom: 10px;
      font-size: 1.4rem;
    }
    .reminders-section p {
      line-height: 1.7;
      font-size: 0.95rem;
      margin-bottom: 15px;
    }
    .reminders-section strong {
      color: #ffdd40; /* A highlight color */
    }
  </style>
</head>
<body>
  <div class="container">
    <?php $active_page = 'report_item'; include '_sidebar.php'; ?>

    <main class="main-content">
      <h2>Report Lost or Found Item</h2>
      <?php if (isset($msg)) echo "<p class='alert'>$msg</p>"; ?>
      <div class="report-layout">
        <form method="POST" enctype="multipart/form-data" class="glass-form">
          <input type="text" name="title" placeholder="Item Title (e.g., Black Jansport Backpack)" required>
          <textarea name="description" placeholder="Detailed Description (include brand, color, unique marks)" rows="3" required></textarea>
          <input type="text" name="location" placeholder="Last Known Location (e.g., Library 2nd Floor)" required>
          <input type="date" name="date" required>
          <select name="type" required>
            <option value="" disabled selected hidden>-- Select Report Type --</option>
            <option value="lost" style="color: black;">I lost something</option>
            <option value="found" style="color: black;">I found something</option>
          </select>
          <input type="email" name="contact_info" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" placeholder="Your Email" required readonly>
          <label for="image" style="margin-top: 10px; font-size: 0.9rem;">Upload an Image (Highly Recommended)</label>
          <input type="file" name="image" id="image" accept="image/*">
          <button type="submit">Submit Report</button>
        </form>
        <div class="reminders-section">
            <h3>✨ A Few Reminders</h3>
            <p><strong>Be Honest:</strong> Provide accurate details. Honesty helps build a trustworthy community and speeds up the process of reuniting items with their rightful owners.</p>
            <p><strong>Be Descriptive:</strong> The more detail you provide, the better. Include the brand, color, size, and any unique identifying marks or scratches.</p>
            <p><strong>Be Helpful:</strong> Your report could make someone's day. Thank you for taking the time to contribute to our campus community!</p>
            <p><strong>Check Your Email:</strong> Ensure your contact email is correct. This is how you'll be notified about your item's status and when it's ready for pickup.</p>
        </div>
      </div>
    </main>
  </div>
  <script src="script.js"></script>
  <script src="sidebar.js"></script>
</body>
</html>
