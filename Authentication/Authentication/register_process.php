<?php
include '../conn.php'; // connection to your DB

// Sanitize user input
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Password match check
if ($password !== $confirm_password) {
    die("Passwords do not match.");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Email already registered.");
}

// Insert into users table
$stmt = $conn->prepare("INSERT INTO users (email, username, phone, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $username, $phone, $hashed_password);

if ($stmt->execute()) {
    // ✅ Insert 1000 points only if user is newly registered
    $checkPoints = $conn->prepare("SELECT * FROM Points WHERE email = ?");
    $checkPoints->bind_param("s", $email);
    $checkPoints->execute();
    $pointsResult = $checkPoints->get_result();

    if ($pointsResult->num_rows === 0) {
        $insertPoints = $conn->prepare("INSERT INTO Points (email, points) VALUES (?, 1000)");
        $insertPoints->bind_param("s", $email);
        $insertPoints->execute();
        $insertPoints->close();
    }

    header("Location: login.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

// Cleanup
$stmt->close();
$conn->close();
?>
