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

// Insert into database
$stmt = $conn->prepare("INSERT INTO users (email, username, phone, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $username, $phone, $hashed_password);

if ($stmt->execute()) {
    header("Location: login.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>