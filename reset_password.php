<?php
session_start();
$token = $_GET['token'] ?? '';

if (!$token) {
    die("No token provided in URL.");
}

$conn = new mysqli('localhost', 'root', '', 'optimabank');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Compare with PHP time (fix timezone mismatch issues)
$now = date("Y-m-d H:i:s");

$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token=? AND token_expires > ?");
$stmt->bind_param("ss", $token, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invalid or expired token.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expires=NULL WHERE reset_token=?");
    $stmt->bind_param("ss", $new_pass, $token);
    $stmt->execute();

    echo "<script>alert('Password successfully updated! Redirecting to login.');window.location.href='Authentication/login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - OptimaBank</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 
  <style>
    body {
      font-family: Arial, sans-serif;
      background: 
        linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), /* dark overlay */
        url('images/background.png') no-repeat center center fixed;
      background-size: cover; /* make it fill full screen */
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .reset-card {
      background: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      width: 380px;
      text-align: center;
      animation: fadeIn 0.6s ease-in-out;
    }

    .reset-card h2 {
      margin-bottom: 10px;
      color: #189d82;
    }

    .reset-card p {
      font-size: 14px;
      color: #555;
      margin-bottom: 25px;
    }

    .input-group {
      position: relative;
      margin-bottom: 20px;
      width: 85%;
    }

    .input-group input {
      width: 100%;
      padding: 12px 40px 12px 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      outline: none;
      font-size: 14px;
    }

    .input-group .icon {
      position: absolute;
      left: 105%;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
      font-size: 16px;
    }

    .reset-card button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: #189d82;
      color: #fff;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .reset-card button:hover {
      background-color: #147f6b;
    }

    .back-link {
      margin-top: 15px;
      display: block;
      font-size: 14px;
      color: #189d82;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  
  <div class="reset-card">
    <h2>Reset Password</h2>
    <p>Please enter your new password below</p>

    <form method="POST">
      <div class="input-group">
        <input type="password" name="password" placeholder="New Password" required>
        <span class="icon"><i class="fa-solid fa-lock"></i></span>
      </div>
      <button type="submit">Update Password</button>
    </form>

    <a href="Authentication/login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
  </div>
</body>
</html>
