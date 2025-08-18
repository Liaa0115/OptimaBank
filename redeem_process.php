<?php
// redeem_process.php
session_start();
header('Content-Type: application/json');
require 'conn.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'reason' => 'auth', 'message' => 'Please login first.']);
    exit;
}

$email      = $_SESSION['email'];
$voucher_id = isset($_POST['voucher_id']) ? (int)$_POST['voucher_id'] : 0;
$quantity   = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
$agreedTnC  = isset($_POST['agreed']) && $_POST['agreed'] === '1';

if ($voucher_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid voucher.']);
    exit;
}

if (!$agreedTnC) {
    echo json_encode(['status' => 'fail', 'reason' => 'tnc', 'message' => 'Please agree to the Terms and Conditions.']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1) Lock user points row
    $ps = $conn->prepare("SELECT points FROM Points WHERE email = ? FOR UPDATE");
    $ps->bind_param("s", $email);
    $ps->execute();
    $psRes = $ps->get_result();
    if (!$psRes || $psRes->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Points record not found.']);
        exit;
    }
    $userPoints = (int)$psRes->fetch_assoc()['points'];

    // 2) Lock voucher row
    $vs = $conn->prepare("SELECT id, name, points_required, quantity FROM vouchers WHERE id = ? FOR UPDATE");
    $vs->bind_param("i", $voucher_id);
    $vs->execute();
    $vRes = $vs->get_result();
    if (!$vRes || $vRes->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Voucher not found.']);
        exit;
    }
    $voucher = $vRes->fetch_assoc();

    $pointsPerUnit = (int)$voucher['points_required'];
    $stock         = (int)$voucher['quantity'];
    $totalRequired = $pointsPerUnit * $quantity;

    // 3) Validate points & stock
    if ($userPoints < $totalRequired) {
        $conn->rollback();
        echo json_encode([
            'status'   => 'fail',
            'reason'   => 'insufficient_points',
            'message'  => 'Not enough points.',
            'required' => $totalRequired,
            'current'  => $userPoints,
            'shortage' => $totalRequired - $userPoints
        ]);
        exit;
    }

    if ($stock < $quantity) {
        $conn->rollback();
        echo json_encode([
            'status'  => 'fail',
            'reason'  => 'out_of_stock',
            'message' => "Only $stock item(s) left."
        ]);
        exit;
    }

    // 4) Deduct points
    $newBalance = $userPoints - $totalRequired;
    $up = $conn->prepare("UPDATE Points SET points = ? WHERE email = ?");
    $up->bind_param("is", $newBalance, $email);
    $up->execute();

    // 5) Decrease stock
    $uq = $conn->prepare("UPDATE vouchers SET quantity = quantity - ? WHERE id = ?");
    $uq->bind_param("ii", $quantity, $voucher_id);
    $uq->execute();

    // 6) Insert codes (one row per quantity) with expiry = NOW() + 14 days
    $batch_id = bin2hex(random_bytes(8)); // 16 hex chars
    $expiry   = date('Y-m-d H:i:s', strtotime('+14 days'));

    $ins = $conn->prepare("INSERT INTO redeemed_vouchers
        (batch_id, email, voucher_id, voucher_code, points_spent, redeemed_at, expiry_date, status)
        VALUES (?, ?, ?, ?, ?, NOW(), ?, 'Active')");

    for ($i = 0; $i < $quantity; $i++) {
        $code = strtoupper(bin2hex(random_bytes(4))); // 8 hex chars
        $ins->bind_param("ssisis", $batch_id, $email, $voucher_id, $code, $pointsPerUnit, $expiry);
        $ins->execute();
    }

    $conn->commit();

    echo json_encode([
        'status'        => 'success',
        'message'       => 'Voucher redeemed successfully.',
        'batch_id'      => $batch_id,
        'voucher_id'    => $voucher['id'],
        'voucher_name'  => $voucher['name'],
        'quantity'      => $quantity,
        'points_spent'  => $totalRequired,
        'new_balance'   => $newBalance,
        'expiry_date'   => $expiry
    ]);
} catch (Throwable $e) {
    if ($conn->errno) { $conn->rollback(); }
    echo json_encode(['status' => 'error', 'message' => 'Server error.', 'detail' => $e->getMessage()]);
}
