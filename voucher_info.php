<?php
session_start();
include 'conn.php';

$points = 0;
$voucher = null;

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    $pointStmt = $conn->prepare("SELECT points FROM Points WHERE email = ?");
    $pointStmt->bind_param("s", $email);
    $pointStmt->execute();
    $pointResult = $pointStmt->get_result()->fetch_assoc();
    $points = $pointResult ? $pointResult['points'] : 0;
}

if (isset($_GET['id'])) {
    $voucher_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT id, name, image, price, points_required, description, quantity FROM vouchers WHERE id = ?");
    $stmt->bind_param("i", $voucher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $voucher = $result->fetch_assoc();
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redeem Item</title>
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

<div class="container redeem-card-container">
    <?php if ($voucher): ?>
    <div class="row redeem-card-content">
        <div class="col-md-6 product-image-section">
            <img src="images/food/<?= htmlspecialchars($voucher['image']) ?>" alt="<?= htmlspecialchars($voucher['name']) ?>">
        </div>

        <div class="col-md-6 product-details-section">
            <h2 class="fw-bold"><?= htmlspecialchars($voucher['name']) ?></h2>
            <p class="text-muted mt-2"><?= nl2br(htmlspecialchars($voucher['description'])) ?></p>

            <div class="my-4">
                <div class="row mb-2">
                    <div class="col-3 fw-bold">Price</div>
                    <div class="col-auto price-text">RM <?= number_format($voucher['price'], 2) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-3 fw-bold">Points</div>
                    <div class="col-auto points-text"><?= number_format($voucher['points_required']) ?></div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-4">
                <span class="me-3 fw-bold">Quantity</span>
                <div class="quantity-input">
                    <button class="quantity-btn" id="minus-btn">-</button>
                    <input type="text" class="form-control" value="1" readonly id="quantity-field">
                    <button class="quantity-btn" id="plus-btn">+</button>
                </div>
                <small class="text-muted" style="margin-left: 10px;">Only <?= $voucher['quantity'] ?? 'a few' ?> available</small>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" value="" id="termsCheck">
                <label class="form-check-label text-muted" for="termsCheck">
                    I agree to the 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                </label>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                <button class="btn btn-redeem-now" type="button" id="redeemBtn">REDEEM NOW</button>
                <button class="btn btn-add-to-cart" type="button" data-voucher-id="<?php echo $voucher['id']; ?>">
                    ADD TO CART
                </button>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="text-center py-5">
            <p class="lead">Item not found. Please go back to the voucher list.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="cartSuccessModal" tabindex="-1" aria-labelledby="cartSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4" style="border-radius: 1rem;">
      <div class="modal-body">
        <div class="mb-3">
          <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: #0f6f4a;"></i>
        </div>
        <h5 class="fw-bold mb-3">Successfully Added to Cart</h5>
        <div class="d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-success" id="goToCartBtn">
            <i class="fa-solid fa-cart-shopping me-2"></i> Go To Cart
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Redeem Success Modal -->
<div class="modal fade" id="redeemSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4" style="border-radius: 1rem;">
      <div class="modal-body">
        <div class="mb-3"><i class="fa-solid fa-gift" style="font-size: 3rem; color: #0f6f4a;"></i></div>
        <h5 class="fw-bold mb-2">Redemption Successful!</h5>
        <p class="text-muted" id="redeemMessage"></p>
        <div class="d-flex justify-content-center gap-3 mt-3">
          <a id="downloadPdfBtn" class="btn btn-success" href="#" target="_blank">
            <i class="fa-solid fa-file-pdf me-2"></i> Download Voucher PDF
          </a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Redeem Fail Modal -->
<div class="modal fade" id="redeemFailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4" style="border-radius: 1rem;">
      <div class="modal-body">
        <div class="mb-3"><i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem;"></i></div>
        <h5 class="fw-bold mb-2">Redemption Failed</h5>
        <p class="text-muted" id="redeemFailMsg"></p>
        <button type="button" class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Terms & Conditions Modal -->
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
    document.addEventListener("DOMContentLoaded", function() {
        const minusBtn = document.getElementById('minus-btn');
        const plusBtn = document.getElementById('plus-btn');
        const quantityField = document.getElementById('quantity-field');

        // Quantity increase/decrease
        minusBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityField.value);
            if (currentValue > 1) {
                quantityField.value = currentValue - 1;
            }
        });

        plusBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityField.value);
            quantityField.value = currentValue + 1;
        });

        // Handle Add to Cart
        document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
            btn.addEventListener('click', function() {
                const voucherId = this.dataset.voucherId;
                const quantity = parseInt(quantityField.value) || 1;

                fetch('cart_controller.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'increase',
                        voucher_id: voucherId,
                        quantity: quantity
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Show success modal
                        const successModal = new bootstrap.Modal(document.getElementById('cartSuccessModal'));
                        successModal.show();
                    } else {
                        alert(data.message || 'Something went wrong');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Request failed.');
                });
            });
        });

        // Redirect to cart.php when "Go To Cart" clicked
        document.getElementById('goToCartBtn').addEventListener('click', function() {
            window.location.href = 'cart.php';
        });

        // Redeem Now
        document.getElementById('redeemBtn')?.addEventListener('click', function() {
            if (!termsCheck.checked) {
                const fm = new bootstrap.Modal(document.getElementById('redeemFailModal'));
                document.getElementById('redeemFailMsg').innerText = 'Please agree to the Terms and Conditions.';
                fm.show();
                return;
            }

            const quantity = parseInt(quantityField.value, 10) || 1;
            const voucherId = <?= $voucher ? (int)$voucher['id'] : 0 ?>;

            fetch('redeem_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    voucher_id: voucherId,
                    quantity: quantity,
                    agreed: 1
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update points balance in navbar if present
                    const pb = document.getElementById('points-balance');
                    if (pb && typeof data.new_balance !== 'undefined') {
                        pb.textContent = data.new_balance;
                    }

                    const sm = new bootstrap.Modal(document.getElementById('redeemSuccessModal'));
                    const msg = `You redeemed ${data.quantity} × ${data.voucher_name}. 
                                Total points spent: ${data.points_spent}. 
                                Expires on: ${data.expiry_date}.`;
                    document.getElementById('redeemMessage').innerText = msg;

                    // PDF download link
                    const pdfBtn = document.getElementById('downloadPdfBtn');
                    pdfBtn.href = 'generate_voucher_pdf.php?batch=' + encodeURIComponent(data.batch_id);

                    sm.show();
                } else if (data.status === 'fail') {
                    const fm = new bootstrap.Modal(document.getElementById('redeemFailModal'));
                    let text = data.message || 'Redemption failed.';
                    if (data.reason === 'insufficient_points' && typeof data.shortage !== 'undefined') {
                        text += ` You need ${data.shortage} more point(s).`;
                    }
                    if (data.reason === 'out_of_stock') {
                        // Optional: you can also reflect the new stock on page after refresh
                    }
                    document.getElementById('redeemFailMsg').innerText = text;
                    fm.show();
                } else {
                    alert(data.message || 'Server error.');
                }
            })
            .catch(() => alert('Request failed.'));
        });
    });
</script>
</body>
</html>
