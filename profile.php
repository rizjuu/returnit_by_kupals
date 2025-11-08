<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <link rel="stylesheet" href="user.css">
  <script>
    // Add a query parameter to the URL to prevent showing stale data
    if (window.location.search.includes('upload=success')) {
      window.history.replaceState(null, null, window.location.pathname);
    }
  </script>
  <style>
   body {
      font-family: "Poppins", sans-serif;
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: auto; /* Allow scrolling if content overflows */
      /* Remove background color and blur from body */
      background: none;
      backdrop-filter: blur(10px); /* Frosted glass effect */
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Overlay container centered */
   .profile-container {
      width: 800px;
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
    }
    .back-btn:hover { background: #0056b3; }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 30px;
    }

    .profile-header img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #00bfff;
    }

    .profile-header .user-details {
      flex-grow: 1;
    }

    .profile-header .user-details h1 {
      margin: 0 0 5px 0;
    }

    .profile-header .user-details p {
      margin: 0;
      color: #ddd;
    }

    .upload-btn {
      padding: 8px 15px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .upload-btn:hover { background: #218838; }

    .profile-details {
      margin-top: 30px;
    }

    .profile-details h2 {
      color: #fff;
      margin-bottom: 20px;
      border-bottom: 2px solid rgba(255,255,255,0.3);
      padding-bottom: 10px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .info-box {
      padding: 15px;
      border-radius: 10px;
      background: transparent; /* Remove background when not editing */
      transition: background-color 0.3s ease;
    }

    .info-box strong {
      display: block;
      color: #00bfff;
      font-size: 14px;
      margin-bottom: 5px;
    }

    .info-box span {
      font-size: 15px;
      color: #fff;
    }

   form input[type="file"] {
      margin-top: 10px;
      color: #ddd;
    }

    /* Style for the embedded user_page.php */
    #userPageBackground {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1; /* Ensure it stays in the background */
      border: none; /* Remove iframe border */
      pointer-events: none; /* Prevent interaction with the iframe */
      filter: blur(10px); /* Apply a blur effect */
    }
    
    .editing .info-box {
        background: rgba(255, 255, 255, 0.15); /* Add background only when editing */
    }


    .edit-btn {
        background: #ffc107;
        color: #333;
        border: none;
        padding: 8px 15px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: bold;
        margin-left: 10px;
    }
    .edit-btn:hover { background: #e0a800; }

    .save-btn, .cancel-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: bold;
        margin-top: 10px;
    }
    .save-btn { background: #28a745; color: white; }
    .cancel-btn { background: #dc3545; color: white; margin-left: 10px; }

  </style>
</head>
<body>
  <div class="profile-container">
    <button class="back-btn" onclick="window.location.href='user_page.php'">⬅ Back</button>

    <div class="profile-header">
      <div>
        <img src="<?= !empty($user['profile_image']) && file_exists($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'default_user.png' ?>" alt="Profile Picture">
      </div>
      <div class="user-details">
        <h1><?= htmlspecialchars($user['name']) ?></h1>
        <p><?= htmlspecialchars($user['email']) ?></p>
        <form action="upload_profile.php" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
        <input type="file" name="profile_image" accept="image/*" required>
        <button type="submit" class="upload-btn">Upload</button>
      </form>
      </div>
    </div>
    <form action="update_profile.php" method="POST">
      <div class="profile-details">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Student Information</h2>
            <div>
                <button type="button" class="edit-btn" id="edit-profile-btn" onclick="toggleEdit(true)">✏️ Edit</button>
                <button type="submit" class="save-btn" id="save-changes-btn" style="display: none;">Save Changes</button>
                <button type="button" class="cancel-btn" id="cancel-changes-btn" style="display: none;" onclick="toggleEdit(false)">Cancel</button>
            </div>
        </div>
        <div class="info-grid">
          <div class="info-box">
            <strong>Full Name</strong>
            <span class="view-mode"><?= htmlspecialchars($user['name']) ?></span>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="edit-mode" style="display:none; width: 95%; background: rgba(255,255,255,0.3); border: 1px solid #fff; border-radius: 5px; padding: 8px; color: white;" required>
          </div>
          <div class="info-box">
            <strong>Student ID</strong>
            <span class="view-mode"><?= htmlspecialchars($user['student_id'] ?? 'Not set') ?></span>
            <input type="text" name="student_id" value="<?= htmlspecialchars($user['student_id'] ?? '') ?>" class="edit-mode" style="display:none; width: 95%; background: rgba(255,255,255,0.3); border: 1px solid #fff; border-radius: 5px; padding: 8px; color: white;" required>
          </div>
          <div class="info-box">
            <strong>Program</strong>
            <span class="view-mode"><?= htmlspecialchars($user['program'] ?? 'Not set') ?></span>
            <input type="text" name="program" value="<?= htmlspecialchars($user['program'] ?? '') ?>" class="edit-mode" style="display:none; width: 95%; background: rgba(255,255,255,0.3); border: 1px solid #fff; border-radius: 5px; padding: 8px; color: white;" required>
          </div>
          <div class="info-box">
            <strong>Year Level</strong>
            <span class="view-mode"><?= htmlspecialchars($user['year_level'] ?? 'Not set') ?></span>
            <select name="year_level" class="edit-mode" style="display:none; width: 100%; background: rgba(255,255,255,0.3); border: 1px solid #fff; border-radius: 5px; padding: 8px; color: white;" required>
                <option value="1st Year" style="color: black;" <?= ($user['year_level'] ?? '') === '1st Year' ? 'selected' : '' ?>>1st Year</option>
                <option value="2nd Year" style="color: black;" <?= ($user['year_level'] ?? '') === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                <option value="3rd Year" style="color: black;" <?= ($user['year_level'] ?? '') === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                <option value="4th Year" style="color: black;" <?= ($user['year_level'] ?? '') === '4th Year' ? 'selected' : '' ?>>4th Year</option>
            </select>
          </div>
          <div class="info-box">
            <strong>Campus</strong>
            <span class="view-mode"><?= htmlspecialchars($user['campus'] ?? 'Not set') ?></span>
            <input type="text" name="campus" value="<?= htmlspecialchars($user['campus'] ?? '') ?>" class="edit-mode" style="display:none; width: 95%; background: rgba(255,255,255,0.3); border: 1px solid #fff; border-radius: 5px; padding: 8px; color: white;" required>
          </div>
        </div>
      </div>
    </form>
  </div>

   <!-- Embed user_page.php as a blurred background -->
  <iframe id="userPageBackground" src="user_page.php" title="Background"></iframe>

  <script>
    function toggleEdit(isEditing) {
      document.querySelectorAll('.view-mode').forEach(el => el.style.display = isEditing ? 'none' : 'block');
      document.querySelectorAll('.edit-mode').forEach(el => el.style.display = isEditing ? 'block' : 'none');
      document.getElementById('edit-profile-btn').style.display = isEditing ? 'none' : 'inline-block';
      document.getElementById('save-changes-btn').style.display = isEditing ? 'inline-block' : 'none';
      document.getElementById('cancel-changes-btn').style.display = isEditing ? 'inline-block' : 'none';
      document.querySelector('.profile-details').classList.toggle('editing', isEditing);
    }
  </script>
</body>
</html>
