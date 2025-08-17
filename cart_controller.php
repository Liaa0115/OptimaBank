<?php
session_start();
require_once __DIR__ . '/conn.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $voucher_id = isset($_POST['voucher_id']) ? intval($_POST['voucher_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    function getCartCount($conn, $email) {
        $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'] ?? 0;
    }

    $updated_quantity = 0; // default for response
    $deleted = false;




    
    switch ($action) {
        case "increase":
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE email = ? AND voucher_id = ?");
            $stmt->bind_param("si", $email, $voucher_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $updated_quantity = $row['quantity'] + $quantity;

                $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $update->bind_param("ii", $updated_quantity, $row['id']);
                $update->execute();
            } else {
                $insert = $conn->prepare("INSERT INTO cart (email, voucher_id, quantity) VALUES (?, ?, ?)");
                $insert->bind_param("sii", $email, $voucher_id, $quantity);
                $insert->execute();
                $updated_quantity = $quantity;
            }
            break;

        case "decrease":
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE email = ? AND voucher_id = ?");
            $stmt->bind_param("si", $email, $voucher_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $updated_quantity = $row['quantity'] - $quantity;

                if ($updated_quantity <= 0) {
                    $delete = $conn->prepare("DELETE FROM cart WHERE id = ?");
                    $delete->bind_param("i", $row['id']);
                    $delete->execute();
                    $updated_quantity = 0;
                    $deleted = true;
                } else {
                    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                    $update->bind_param("ii", $updated_quantity, $row['id']);
                    $update->execute();
                }
            }
            break;

        case "delete":
            $cart_id = intval($_POST['cart_id']);
            $delete = $conn->prepare("DELETE FROM cart WHERE id = ? AND email = ?");
            $delete->bind_param("is", $cart_id, $email);
            $delete->execute();
            $deleted = true;
            break;

        default:
            header("Content-Type: application/json");
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
            exit();
    }

    $cartCount = getCartCount($conn, $email);

    header("Content-Type: application/json");
    echo json_encode([
        "status" => "success",
        "cart_count" => $cartCount,
        "updated_quantity" => $updated_quantity,
        "deleted" => $deleted
    ]);
    exit();
}
