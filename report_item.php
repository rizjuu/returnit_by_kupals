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
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h2>🏫 User Panel</h2>
      <ul>
        <li><a href="user_page.php">All Items</a></li>
        <li><a href="report_item.php" class="active">Report Item</a></li>
        <li><a href="claim_item.php">Claim Item</a></li>
        <li><a href="claim_history.php">Claim History</a></li> 
      </ul>
      <a href="logout.php" class="logout-btn">Logout</a>
    </aside>

    <main class="main-content">
      <h2>📋 Report Lost or Found Item</h2>
      <?php if (isset($msg)) echo "<p class='alert'>$msg</p>"; ?>
      <form method="POST" enctype="multipart/form-data" class="glass-form">
        <input type="text" name="title" placeholder="Item Title" required>
        <textarea name="description" placeholder="Description" rows="3"></textarea>
        <input type="text" name="location" placeholder="Location">
        <input type="date" name="date">
        <select name="type">
          <option value="lost">Lost</option>
          <option value="found">Found</option>
        </select>
        <input type="email" name="contact_info" placeholder="Your Email" required>
        <input type="file" name="image" accept="image/*">
        <button type="submit">Submit</button>
      </form>
    </main>
  </div>
</body>
</html>
