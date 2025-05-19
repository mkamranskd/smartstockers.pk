<?php
require_once '../php_action/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$orderId = intval($data['order_id']);
$deliveryCharges = floatval($data['delivery_charges']);

$response = ['success' => false];

// Fetch current total_amount
$sqlFetch = "SELECT total_amount FROM orders WHERE id = ?";
$stmtFetch = $connect->prepare($sqlFetch);
$stmtFetch->bind_param("i", $orderId);
$stmtFetch->execute();
$stmtFetch->bind_result($currentTotal);
$stmtFetch->fetch();
$stmtFetch->close();

if ($currentTotal !== null) {
    $newTotal = $currentTotal + $deliveryCharges;

    // Update total_amount
    $sqlUpdate = "UPDATE orders SET total_amount = ? WHERE id = ?";
    $stmtUpdate = $connect->prepare($sqlUpdate);
    $stmtUpdate->bind_param("di", $newTotal, $orderId);

    if ($stmtUpdate->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = $stmtUpdate->error;
    }

    $stmtUpdate->close();
} else {
    $response['error'] = "Order not found.";
}

echo json_encode($response);
?>