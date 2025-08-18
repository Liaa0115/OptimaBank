<?php
session_start();
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
<link rel="stylesheet" href="cart.css">
<script src="cart.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">🛒 Your Cart</h2>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">Your cart is empty!</div>
    <?php else: ?>
        <form id="cart-form">
            <table class="table table-bordered cart-table align-middle">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Points</th>
                        <th>Quantity</th>
                        <th>Total Points</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr data-cart-id="<?= $item['cart_id'] ?>" data-voucher-id="<?= $item['voucher_id'] ?>">
                        <td><input type="checkbox" name="selected[]" value="<?= $item['voucher_id'] ?>"></td>
                        <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['points_required']) ?></td>
                        <td>
                            <div class="quantity-input">
                                <span class="quantity-btn btn-decrease">-</span>
                                <input type="text" class="form-control" value="<?= $item['cart_qty']?>" readonly>
                                <span class="quantity-btn btn-increase">+</span>
                            </div>
                        </td>
                        <td class="total-points"><?= $item['cart_qty'] * $item['points_required'] ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btn-delete">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="text-end mt-3">
                <div id="total-points-used" class="mb-2 fw-bold">Total Points: 0</div>
                <button type="button" id="checkout-btn" class="btn btn-success btn-lg">Checkout</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
