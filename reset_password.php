<?php
session_start();
$token = $_GET['token'] ?? '';

$conn = new mysqli('localhost', 'root', '', 'optimabank');
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token=? AND token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Invalid or expired token.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expires=NULL WHERE reset_token=?");
    $stmt->bind_param("ss", $new_pass, $token);
    $stmt->execute();
    echo "Password successfully updated! <a href='Authentication/login.php'>Login</a>";
    exit;
}
?>

<form method="POST">
  <label>New Password:</label><br>
  <input type="password" name="password" required><br><br>
  <button type="submit">Reset Password</button>
</form>
