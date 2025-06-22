<?php
session_start();
include 'conn.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Get user info
$stmt = $conn->prepare("SELECT username, fullname, phone, address, about, profile_image FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get points
$pointStmt = $conn->prepare("SELECT points FROM Points WHERE email = ?");
$pointStmt->bind_param("s", $email);
$pointStmt->execute();
$pointResult = $pointStmt->get_result()->fetch_assoc();
$points = $pointResult ? $pointResult['points'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <link rel="stylesheet" href="css/navbarProfile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f1fdfb;
      margin: 0;
      padding-top: 80px;
    }
    .profile-container {
      display: flex;
      justify-content: center;
      gap: 30px;
      padding: 40px;
      flex-wrap: wrap;
    }
    .card {
      background: white;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .card.profile {
      text-align: center;
      width: 300px;
    }
    .card.profile img {
      width: 180px;
      height: 180px;
      border-radius: 50%;
      object-fit: cover;
    }
    .card.profile h3 {
      margin-top: 20px;
      font-size: 20px;
    }
    .card.profile .balance-box {
      background: #e6faf5;
      padding: 15px;
      border-radius: 12px;
      margin-top: 25px;
      font-size: 18px;
      font-weight: 600;
    }
    .card.details {
      flex-grow: 1;
      max-width: 600px;
    }
    .card.details form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .card.details label {
      font-size: 13px;
      color: #189d82;
      font-weight: 600;
    }
    .card.details input, .card.details textarea {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      width: 100%;
    }
    .card.details button {
      padding: 12px;
      background-color: #189d82;
      border: none;
      color: white;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
    }
    .card.details button:hover {
      background-color: #147f6b;
    }
  </style>
</head>
<body>

<nav class="top-navbar">
  <div class="logo">
    <a href="index.html">
      <img src="images/logo.png" alt="OptimaBank Logo" style="height: 35px;">
    </a>
  </div>
  <ul>
    <li><a href="index.html">Home Page</a></li>
    <li><a href="voucher.html">Voucher</a></li>
    <li class="points-badge"><a href="#">Point Balance: <?= $points ?></a></li>
    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i></a></li>
    <li><a href="logout.php">Sign Out</a></li>
  </ul>
</nav>

<div class="profile-container">
  <div class="card profile">
    <img src="uploads/<?= htmlspecialchars($user['profile_image'] ?: 'default-profile.jpg') ?>" alt="Profile Picture">
    <h3><?= htmlspecialchars($user['username']) ?></h3>
    <div class="balance-box">
      Points Balance<br>
      <?= $points ?>
    </div>
  </div>

  <div class="card details">
    <form method="post" action="update_profile.php" enctype="multipart/form-data">
      <div>
        <label>Full Name</label>
        <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>">
      </div>
      <div>
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>">
      </div>
      <div>
        <label>Email (readonly)</label>
        <input type="email" value="<?= htmlspecialchars($email) ?>" readonly>
      </div>
      <div>
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
      </div>
      <div>
        <label>Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
      </div>
      <div>
        <label>About Me</label>
        <textarea name="about"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
      </div>
      <div>
        <label>Profile Image</label>
        <input type="file" name="profile_image">
      </div>
      <button type="submit">Save</button>
    </form>
  </div>
</div>

</body>
</html>
