<?php
//session_start();
include 'conn.php';

$points = 0;

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Fetch user points
    $pointStmt = $conn->prepare("SELECT points FROM Points WHERE email = ?");
    $pointStmt->bind_param("s", $email);
    $pointStmt->execute();
    $pointResult = $pointStmt->get_result()->fetch_assoc();
    $points = $pointResult ? $pointResult['points'] : 0;
}

// Fetch cart items for this user
$cartItems = [];
$cartStmt = $conn->prepare("
    SELECT c.id AS cart_id, c.quantity AS cart_qty, v.id AS voucher_id, v.name, v.image, v.price, v.points_required
    FROM cart c
    JOIN vouchers v ON c.voucher_id = v.id
    WHERE c.email = ?
");
$cartStmt->bind_param("s", $email);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
while ($row = $cartResult->fetch_assoc()) {
    $cartItems[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Cart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background-color: #f9f9f9; }
    
    .redeem-card-container {
        width: 900px;
        margin: 120px auto; 
        background-color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 2.5rem;
        border: 1px solid #e0e0e0;
    }
    .redeem-card-content {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 2rem;
    }
    .product-image-section {
        flex: 1;
        text-align: center;
    }
    .product-image-section img {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
    }
    .product-details-section {
        flex: 1;
        padding-left: 2rem;
        border-left: 1px solid #e0e0e0;
    }
    .price-text {
        font-size: 1.5rem;
        font-weight: bold;
        color: #ffc600;
    }
    .points-text {
        font-size: 1.25rem;
        font-weight: bold;
        color: #0f6f4a;
    }
    .quantity-input {
        display: flex;
        align-items: center;
    }
    .quantity-input .form-control {
        width: 60px;
        text-align: center;
        border: 1px solid #ddd;
        margin: 0 5px;
    }
    .quantity-btn {
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 5px 10px;
        cursor: pointer;
    }
    .quantity-btn:hover {
        background-color: #e0e0e0;
    }
    .btn-redeem-now {
        background-color: #ffc600;
        border-color: #ffc600;
        color: #0f6f4a;
        font-weight: bold;
        padding: 0.75rem 1.5rem;
    }
    .btn-add-to-cart {
        background-color: #0f6f4a;
        border-color: #0f6f4a;
        color: #fff;
        font-weight: bold;
        padding: 0.75rem 1.5rem;
    }
    .points-badge {
        background-color: #d4a373;
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        margin-left: 15px;
        font-weight: 600;
    }
    .cart-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f6f4a;
        margin-left: 10px;
    }
    .cart-icon i {
        font-size: 18px;
    }
    .cart-icon:hover {
        background-color: #ffc600;
        color: #0f6f4a; 
    }
    .product-details-section p {
        font-size: 1rem;
        line-height: 1.5;
        color: #555;
    }


.cart-table img { width: 80px; border-radius: 8px; }
.quantity-btn { cursor: pointer; padding: 4px 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f0f0f0; }
.quantity-btn:hover { background-color: #e0e0e0; }
</style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0f6f4a;">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="images/logo.png" alt="Logo" height="36" style="border-radius:6px; background:white; padding:4px;">
      <span style="margin-left:6px; color:#ffc600; font-weight:700;">TREATS POINTS</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php" style="color: #f9f9f9;">Home Page</a></li>
        <li class="nav-item"><a class="nav-link" href="voucher_list.php" style="color: #f9f9f9;">Voucher</a></li>
        <?php if (isset($_SESSION['email'])): ?>
          <li class="nav-item"><a class="nav-link" href="profile.php" style="color: #f9f9f9;">Profile</a></li>
          <li class="nav-item points-badge d-flex align-items-center" style="text-decoration: none; color: #0f6f4a; background-color: #ffc600 !important; border-color: #ffc600 !important;">
            Point Balance: <?= $points ?>
          </li>
          <li class="nav-item">
            <a class="nav-link cart-icon" href="cart.php">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="logout.php" style="color: #f9f9f9;">Sign Out</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-item points-badge d-flex align-items-center" style="text-decoration: none; color: #0f6f4a; background-color: #ffc600 !important; border-color: #ffc600 !important;" href="authentication/login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

</html>
