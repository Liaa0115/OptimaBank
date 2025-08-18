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
      <form action="checkout.php" method="POST" id="cart-form">
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
                <td>
                    <input type="checkbox" name="selected[]" value="<?= $item['cart_id'] ?>">
                </td>
                <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="70"></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= number_format($item['points_required']) ?></td>
                <td>
                    <input type="hidden" name="quantity[<?= $item['cart_id'] ?>]" value="<?= $item['cart_qty'] ?>">
                    <?= $item['cart_qty'] ?>
                </td>
                <td class="total-points"><?= $item['cart_qty'] * $item['points_required'] ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btn-delete">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Alert box (hidden by default) -->
    <div id="alert-box" class="alert alert-danger d-none">
        ⚠️ Please select at least one item before proceeding to checkout.
    </div>

    <div class="checkout-box d-flex justify-content-end align-items-center mt-4">
    <button type="submit" class="btn btn-success btn-lg">
        <i class="fa fa-credit-card"></i> Proceed to Checkout
    </button>
</div>
</form>


    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("cart-form").addEventListener("submit", function(event) {
    let checkboxes = document.querySelectorAll('input[name="selected[]"]:checked');
    let alertBox = document.getElementById("alert-box");

    if (checkboxes.length === 0) {
        event.preventDefault(); // Stop form submission
        alertBox.classList.remove("d-none"); // Show alert
        window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll to top for visibility
    } else {
        alertBox.classList.add("d-none"); // Hide alert if already shown
    }
});

// Select all checkbox
document.getElementById("select-all").addEventListener("change", function() {
    let checkboxes = document.querySelectorAll('input[name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>

</body>
</html>
