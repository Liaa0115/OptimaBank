<?php
session_start();
include_once("conn.php");

// Pastikan user dah login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Ambil cart user  
$cart_items = [];
$total_points = 0;

$selected = $_POST['selected'] ?? []; // sentiasa ada array walaupun kosong

if (!empty($selected)) {
    $placeholders = implode(',', array_fill(0, count($selected), '?')); // ?,?,?
    $types = str_repeat('i', count($selected)); // all integers

    $sql = "SELECT c.id, c.voucher_id, v.name, v.image, v.description, v.points_required, c.quantity 
            FROM cart c 
            JOIN vouchers v ON c.voucher_id = v.id 
            WHERE c.email = ? AND c.id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s" . $types, $email, ...$selected);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['points_required'] * $row['quantity'];
        $total_points += $subtotal;
        $cart_items[] = $row;
    }
}


// Dapatkan baki points user
$pointStmt = $conn->prepare("SELECT points FROM Points WHERE email = ?");
$pointStmt->bind_param("s", $email);
$pointStmt->execute();
$pointResult = $pointStmt->get_result()->fetch_assoc();
$points = $pointResult ? $pointResult['points'] : 0;

// Handle checkout confirm
$notifMessage = "";
if (isset($_POST['confirm_checkout'])) {
  if ($total_points > $points) {
      $notifMessage = "⚠️ You don't have enough points to checkout.";
      $notifType = "danger";
  } elseif (empty($cart_items)) {
      $notifMessage = "Your cart is empty.";
      $notifType = "warning";
  } else {
      $conn->begin_transaction();
      try {
          // 1) Tolak points
          $updatePoints = $conn->prepare("UPDATE Points SET points = points - ? WHERE email = ?");
          $updatePoints->bind_param("is", $total_points, $email);
          if (!$updatePoints->execute()) throw new Exception($updatePoints->error);

          // 2) Batch ID + Expiry (same as redeem_process.php)
          $batch_id = bin2hex(random_bytes(8)); // 16 hex chars
          $expiry_date = date('Y-m-d H:i:s', strtotime('+14 days'));

          // 3) Prepare insert
          $insertRedeem = $conn->prepare("INSERT INTO redeemed_vouchers 
              (batch_id, email, voucher_id, voucher_code, points_spent, redeemed_at, expiry_date, status) 
              VALUES (?, ?, ?, ?, ?, NOW(), ?, 'Active')");

          // 4) Loop each item in cart
          foreach ($cart_items as $item) {
              $voucher_id   = $item['voucher_id'];
              $qty          = $item['quantity'];
              $pointsEach   = $item['points_required'];

              // Insert one row per quantity
              for ($i = 0; $i < $qty; $i++) {
                  $voucher_code = strtoupper(bin2hex(random_bytes(4))); // 8 hex chars
                  $insertRedeem->bind_param(
                      "ssisis",
                      $batch_id,
                      $email,
                      $voucher_id,
                      $voucher_code,
                      $pointsEach,
                      $expiry_date
                  );
                  if (!$insertRedeem->execute()) throw new Exception($insertRedeem->error);
              }

              // 5) Kurangkan stok dari vouchers
              $updateVoucher = $conn->prepare("UPDATE vouchers SET quantity = quantity - ? WHERE id = ?");
              $updateVoucher->bind_param("ii", $qty, $voucher_id);
              if (!$updateVoucher->execute()) throw new Exception($updateVoucher->error);
          }

          // 6) Kosongkan cart
          $placeholders = implode(',', array_fill(0, count($selected), '?'));
$types = str_repeat('i', count($selected));
$deleteCart = $conn->prepare("DELETE FROM cart WHERE email = ? AND id IN ($placeholders)");
$deleteCart->bind_param("s" . $types, $email, ...$selected);

          if (!$deleteCart->execute()) throw new Exception($deleteCart->error);

          $conn->commit();
          $notifMessage = "🎉 Checkout successful! Your vouchers have been redeemed.";
          $notifType = "success";

          // Refresh balance in session
          $points -= $total_points;
          $cart_items = [];
          $total_points = 0;
      } catch (Exception $e) {
          $conn->rollback();
          $notifMessage = "❌ Checkout failed. Error: " . $e->getMessage();
          $notifType = "danger";
      }
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  
  <style>
    :root {
      --mb-yellow: #ffc600;
      --mb-black: #0f6f4a;
      --card-radius: 12px;
    }
    body {
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
      background: #f5f5f5;
      color: #222;
    }
  
    .points-badge { background-color: var(--mb-yellow); color: var(--mb-black) !important; font-weight: 600; border-radius: 20px; padding: 0.3rem 0.75rem; margin: 0.25rem 0; }
    .checkout-container { max-width: 1000px; margin: auto; }
    .voucher-card { display: flex; align-items: flex-start; background: #fff; border-radius: var(--card-radius); padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    .voucher-card img { width: 90px; height: 90px; border-radius: 8px; object-fit: cover; margin-right: 15px; }
    .voucher-info h5 { margin: 0; font-size: 1rem; font-weight: 600; }
    .voucher-info p { font-size: 0.9rem; margin: 5px 0; color: #555; }
    .voucher-summary { margin-left: auto; text-align: right; }
    .voucher-summary .price { font-weight: bold; color: var(--mb-black); }
    .summary-box { background: #fff; border-radius: var(--card-radius); padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
    .summary-row strong { color: var(--mb-black); }
    .checkout-footer { position: sticky; bottom: 0; background: #fff; padding: 15px 20px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -2px 8px rgba(0,0,0,0.05); }
    .checkout-footer .total { font-size: 1.2rem; font-weight: 700; color: var(--mb-black); }
  </style>
</head>
<body>
  <!-- Navbar -->
  <?php include 'navbar.php'; ?>

  <!-- Checkout container -->
  <div class="checkout-container my-4">
    <h2 class="mb-3">🛒 Checkout</h2>

    <?php if (!empty($cart_items)) { ?>
      <?php foreach ($cart_items as $row):
          $subtotal = $row['points_required'] * $row['quantity'];
      ?>
        <div class="voucher-card">
          <img src="<?= htmlspecialchars($row['image']); ?>" alt="">
          <div class="voucher-info">
            <h5><?= htmlspecialchars($row['name']); ?></h5>
            <p><?= htmlspecialchars($row['description']); ?></p>
            <p>Quantity: <?= $row['quantity']; ?></p>
          </div>
          <div class="voucher-summary">
            <div><?= $row['points_required']; ?> pts x <?= $row['quantity']; ?></div>
            <div class="price"><?= $subtotal; ?> pts</div>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="summary-box mt-4">
        <div class="summary-row"><span>Total Points Used</span><strong><?= $total_points; ?> pts</strong></div>
        <div class="summary-row"><span>Your Balance</span><strong><?= $points; ?> pts</strong></div>
        <div class="summary-row"><span>Balance After Checkout</span><strong><?= $points - $total_points; ?> pts</strong></div>
      </div>
    <?php } else { ?>
      <div class="alert alert-warning">Your cart is empty.</div>
    <?php } ?>
  </div>

  <?php if (!empty($cart_items)) { ?>
  <div class="checkout-footer">
    <div class="total">Total: <?= $total_points; ?> pts</div>
    <form method="POST">
  <?php foreach ($selected as $sid): ?>
      <input type="hidden" name="selected[]" value="<?= $sid ?>">
  <?php endforeach; ?>
  <button type="submit" name="confirm_checkout" class="btn btn-success btn-lg">
    <i class="fa fa-check-circle"></i> Confirm Checkout
  </button>
</form>

  </div>
  <?php } ?>

<!-- Notification Modal -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="notifModalLabel">Notification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="notifModalBody"></div>
      <div class="modal-footer d-flex justify-content-center gap-3">
        <a id="downloadPdfBtn" class="btn btn-success" href="#" target="_blank" style="display:none;">
          <i class="fa-solid fa-file-pdf me-2"></i> Download Voucher PDF
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Show notification modal if $notifMessage is set -->
  <?php if(!empty($notifMessage)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var notifModalBody = document.getElementById('notifModalBody');
    notifModalBody.innerHTML = "<?= addslashes($notifMessage) ?>";
    var notifModal = new bootstrap.Modal(document.getElementById('notifModal'));
    notifModal.show();

    // If checkout success, show PDF button
    <?php if ($notifType === "success"): ?>
        document.getElementById('downloadPdfBtn').style.display = 'inline-block';
        document.getElementById('downloadPdfBtn').href = "generate_voucher_pdf.php?batch=<?= $batch_id ?? '' ?>";
    <?php endif; ?>
});
</script>
<?php endif; ?>


</body>
</html>
