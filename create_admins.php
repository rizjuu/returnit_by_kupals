<?php
require_once 'config.php';

function createUserIfNotExists($conn, $name, $email, $passwordPlain, $role) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "A user with role '$role' already exists.<br>";
        $stmt->close();
        return;
    }
    $stmt->close();

    $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
    $stmt2 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("ssss", $name, $email, $hash, $role);
    $stmt2->execute();
    $stmt2->close();

    echo "✅ Created $role: $email (password: $passwordPlain)<br>";
}

createUserIfNotExists($conn, 'Main Admin', 'rizju@gmail.com', '12345', 'admin');
createUserIfNotExists($conn, 'Security Personnel', 'securitynisya@gmail.com', '123456', 'security');

echo "<hr>Done. Delete this file after use.";
?>
