<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php_action/db_connect.php'; // Ensure this file correctly initializes $connect

if (!isset($connect) || $connect->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $connect->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["orderId"], $data["clientId"], $data["trackingNumber"], $data["orderStatus"], $data["updatedAt"])) {
    $orderId = $data["orderId"];
    $clientId = $data["clientId"];
    $trackingNumber = $data["trackingNumber"];
    $orderStatus = $data["orderStatus"];
    $updatedAt = $data["updatedAt"];

    $query = "UPDATE orders SET order_tracking_id = ?, status = ?, updated_at = ? WHERE order_id = ? AND client_id = ?";
    $stmt = $connect->prepare($query);

    if ($stmt) {
        $stmt->bind_param("sisss", $trackingNumber, $orderStatus, $updatedAt, $orderId, $clientId);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Order updated successfully"]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to update order: " . $stmt->error]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "SQL Prepare Failed: " . $connect->error]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid input data"]);
}
?>