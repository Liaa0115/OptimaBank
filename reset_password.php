<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

$token = $_GET['token'] ?? '';

$conn = new mysqli('localhost', 'root', '', 'optimabank');
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token=? AND token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Invalid token.";
    exit;
}

$user = $result->fetch_assoc();

// Compare expiry in PHP
$current = new DateTime();
$expires = new DateTime($user['token_expires']);

if ($current > $expires) {
    echo "Token expired.";
    exit;
}

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expires=NULL WHERE reset_token=?");
    $stmt->bind_param("ss", $new_pass, $token);
    $stmt->execute();
    header("Location: Authentication/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/authentication.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="images/logo.png" alt="Optima Bank Logo" class="logo">
            <h2>Secure Your Account</h2>
            <p>We're here to help you regain access. Please create a strong, new password below to protect your account.</p>
        </div>
        <div class="right">
            <div class="login-form">
                <h2>Reset Password</h2>
                <form method="POST">
                    <div class="input-group">
                        <label for="password">New Password:</label>
                        <input type="password" id="password" name="password" required>
                        </div>
                    <button type="submit" class="btn">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>