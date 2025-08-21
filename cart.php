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
  <style>
    .cart-table tbody td {
    text-align: center;
    vertical-align: middle;
}
.cart-table thead th {
    text-align: center;
    vertical-align: middle;
}

  </style>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Cart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="cart.css">
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
            <td>
                <img src="images/food/<?= htmlspecialchars($item['image']) ?>" 
                     alt="<?= htmlspecialchars($item['name']) ?>" 
                     width="70" class="mx-auto d-block">
            </td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['points_required']) ?></td>
            <td>
                <div class="d-flex justify-content-center align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-decrease" data-voucher-id="<?= $item['voucher_id'] ?>">-</button>
                    <span class="mx-2 quantity"><?= $item['cart_qty'] ?></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-increase" data-voucher-id="<?= $item['voucher_id'] ?>">+</button>
                </div>
                <input type="hidden" name="quantity[<?= $item['cart_id'] ?>]" value="<?= $item['cart_qty'] ?>" class="hidden-quantity">
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
    
    <div class="form-check me-4">
        <input class="form-check-input" type="checkbox" id="agreeTerms" required>
        <label class="form-check-label" for="agreeTerms">
            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a>
        </label>
    </div>

    <div class="checkout-box d-flex justify-content-end align-items-center mt-4">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fa fa-credit-card"></i> Proceed to Checkout
        </button>
    </div>
</form>
    <?php endif; ?>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>1. Voucher is valid for 14 days from redemption date.</p>
                <p>2. Voucher is non-refundable and cannot be exchanged for cash.</p>
                <p>3. Only valid for the registered user’s account.</p>
                <p>4. Other terms and conditions may apply.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="color: white; background-color: #0f6f4a !important; border-color: #0f6f4a !important;">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validate form before checkout
document.getElementById("cart-form").addEventListener("submit", function(event) {
    let checkboxes = document.querySelectorAll('input[name="selected[]"]:checked');
    let alertBox = document.getElementById("alert-box");

    let termsAlert = document.createElement('div');
    termsAlert.id = 'terms-alert';
    termsAlert.className = 'alert alert-warning d-none';
    termsAlert.innerHTML = '⚠️ Please agree to the Terms & Conditions before proceeding.';
    

    if (checkboxes.length === 0) {
        event.preventDefault(); 
        alertBox.classList.remove("d-none"); 
        window.scrollTo({ top: 0, behavior: 'smooth' }); 
    } else {
        alertBox.classList.add("d-none"); 
    }
});

// Select all checkbox
document.getElementById("select-all").addEventListener("change", function() {
    let checkboxes = document.querySelectorAll('input[name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Update cart via AJAX
function updateCart(action, voucherId, row) {
    fetch("cart_controller.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: action,
            voucher_id: voucherId,
            quantity: 1
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            if (data.deleted) {
                row.remove();
            } else {
                let qtyElem = row.querySelector(".quantity");
                let pointsElem = row.querySelector(".total-points");
                let hiddenInput = row.querySelector(".hidden-quantity");

                let pointsPerItem = parseInt(pointsElem.innerText) / parseInt(qtyElem.innerText);

                qtyElem.innerText = data.updated_quantity;
                pointsElem.innerText = data.updated_quantity * pointsPerItem;
                hiddenInput.value = data.updated_quantity;
            }
        }
    });
}

// Increase button
document.querySelectorAll(".btn-increase").forEach(btn => {
    btn.addEventListener("click", function() {
        let row = this.closest("tr");
        let voucherId = this.getAttribute("data-voucher-id");
        updateCart("increase", voucherId, row);
    });
});

// Decrease button
document.querySelectorAll(".btn-decrease").forEach(btn => {
    btn.addEventListener("click", function() {
        let row = this.closest("tr");
        let voucherId = this.getAttribute("data-voucher-id");
        updateCart("decrease", voucherId, row);
    });
});

// Delete button
document.querySelectorAll(".btn-delete").forEach(btn => {
    btn.addEventListener("click", function() {
        let row = this.closest("tr");
        let cartId = row.getAttribute("data-cart-id");

        fetch("cart_controller.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "delete",
                cart_id: cartId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success" && data.deleted) {
                row.remove();
            }
        });
    });
});
</script>
</body>
</html>
