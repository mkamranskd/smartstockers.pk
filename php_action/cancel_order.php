<?php
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);

    $query = "UPDATE orders SET status = 7 WHERE id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }

    $stmt->close();
}
?>
