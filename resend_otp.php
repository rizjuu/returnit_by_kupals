<?php
session_start();
require_once 'config.php';
require_once 'send_otp.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

if (!isset($_SESSION['pending_email'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start the registration process again.']);
    exit;
}

$email = $_SESSION['pending_email'];

// --- Rate Limiting ---
$resend_limit = 300; // 5 minutes in seconds
if (isset($_SESSION['last_resend_time']) && (time() - $_SESSION['last_resend_time'] < $resend_limit)) {
    echo json_encode(['success' => false, 'message' => 'Please wait before requesting another OTP.']);
    exit;
}

// --- Database Operations ---
$conn->begin_transaction();
try {
    // Delete any existing OTP for this email
    $del_stmt = $conn->prepare("DELETE FROM email_verification WHERE email = ?");
    if ($del_stmt === false) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    if (!$del_stmt->bind_param("s", $email) || !$del_stmt->execute()) {
        throw new Exception("Database execute failed: " . $del_stmt->error);
    }

    // Generate and insert new OTP
    try {
        $otp = random_int(100000, 999999);
    } catch (Exception $e) {
        $otp = rand(100000, 999999); // Fallback for environments where random_int is not available
    }
    $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));
    $insert_stmt = $conn->prepare("INSERT INTO email_verification (email, otp_code, expires_at) VALUES (?, ?, ?)");
    if ($insert_stmt === false) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    if (!$insert_stmt->bind_param("sis", $email, $otp, $expires) || !$insert_stmt->execute()) {
        throw new Exception("Database execute failed: " . $insert_stmt->error);
    }
    // --- Send Email ---
    if (sendVerificationOTP($email, $otp)) {
        $conn->commit();
        $_SESSION['last_resend_time'] = time(); // Update rate limit timestamp
        echo json_encode(['success' => true, 'message' => 'A new OTP has been sent to your email.']);
    } else {
        throw new Exception("Failed to send OTP email.");
    }
} catch (Exception $e) {
    $conn->rollback();
    error_log("Resend OTP Error: " . $e->getMessage()); // Log the actual error for debugging
    echo json_encode(['success' => false, 'message' => 'Could not resend OTP. Please try again later.']);
}

exit;
?>