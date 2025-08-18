<?php
// generate_voucher_pdf.php
session_start();
require 'conn.php';

if (!isset($_SESSION['email'])) {
    die('Please login first.');
}

$email   = $_SESSION['email'];
$batchId = isset($_GET['batch']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['batch']) : '';

if ($batchId === '') {
    die('Invalid batch.');
}

// Fetch redeemed vouchers for this batch
$q = $conn->prepare("
    SELECT rv.*, v.name AS voucher_name, v.image, v.price
    FROM redeemed_vouchers rv
    JOIN vouchers v ON v.id = rv.voucher_id
    WHERE rv.email = ? AND rv.batch_id = ?
    ORDER BY rv.id ASC
");
$q->bind_param("ss", $email, $batchId);
$q->execute();
$res = $q->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }

if (empty($rows)) {
    die('No vouchers found for this batch.');
}

// ✅ Fetch current remaining points from points table
$pointStmt = $conn->prepare("SELECT points FROM points WHERE email = ?");
$pointStmt->bind_param("s", $email);
$pointStmt->execute();
$pointRow = $pointStmt->get_result()->fetch_assoc();
$currentBalance = $pointRow ? $pointRow['points'] : 0;

$voucher      = $rows[0];
$userName     = $email; // you can replace with fullname if stored in users table
$redeemAt     = date("d M Y", strtotime($voucher['redeemed_at']));
$expiry       = date("d M Y", strtotime($voucher['expiry_date']));
$voucherName  = $voucher['voucher_name'];
$price        = "RM " . number_format($voucher['price'], 2);

$baseUrl = "http://localhost/OptimaBank/"; // adjust if project path differs

require __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    .voucher-box { border:2px solid #000; border-radius:10px; padding:10px; margin-bottom:25px; }
    .header td { font-size:14px; font-weight:bold; }
    .header .left { text-align:left; }
    .header .right { text-align:right; }
    .main td { vertical-align:top; padding:10px; }
    .image img { width:150px; border-radius:6px; }
    .label { font-size:11px; color:#555; }
    .value { font-size:12px; font-weight:bold; margin-bottom:5px; }
    .gap { margin:12px 0; }
    .terms { font-size:10px; }
</style>
</head>
<body>
';

foreach ($rows as $row) {
    $code   = htmlspecialchars($row['voucher_code']);
    $imgSrc = $baseUrl . $row['image'];
    $remaining = $currentBalance; // ✅ use user’s current balance from DB

    $html .= '
    <div class="voucher-box">
        <!-- Header -->
        <table class="header" width="100%">
            <tr>
                <td class="left">VOUCHER</td>
                <td class="right">OptimaBank</td>
            </tr>
        </table>

        <!-- Image + Info -->
        <table class="main" width="100%">
            <tr>
                <td class="image" width="35%">
                    <img src="' . $imgSrc . '" alt="Voucher Image">
                </td>
                <td width="65%">
                    <div><span class="label">VOUCHER CODE</span><div class="value">' . $code . '</div></div>
                    <div><span class="label">NAME</span><div class="value">' . htmlspecialchars($voucherName) . '</div></div>
                    <div><span class="label">VALUE</span><div class="value">' . $price . '</div></div>
                    <div><span class="label">REMAINING POINT BALANCE</span><div class="value">' . $remaining . '</div></div>
                    
                    <div class="gap"></div>
                    
                    <div><span class="label">USER</span><div class="value">' . htmlspecialchars($userName) . '</div></div>
                    <div><span class="label">REDEEM DATE</span><div class="value">' . $redeemAt . '</div></div>
                    <div><span class="label">EXPIRY DATE</span><div class="value">' . $expiry . '</div></div>
                </td>
            </tr>
        </table>

        <!-- Bottom row -->
        <table class="main" width="100%">
            <tr>
                <td width="35%">
                    <span class="label">POINTS SPENT</span><br>
                    <div class="value">' . $row['points_spent'] . '</div>
                </td>
                <td width="65%" class="terms">
                    <strong>TERMS & CONDITIONS</strong><br>
                    Valid for 14 days<br>
                    Not refundable
                </td>
            </tr>
        </table>
    </div>';
}

$html .= '</body></html>';

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('voucher-'.$batchId.'.pdf', ['Attachment' => false]);
