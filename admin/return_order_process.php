<?php
require_once '../php_action/db_connect.php';

$orderId = intval($_POST['order_id']);

// Fetch order items
$orderItems = mysqli_query($connect, "SELECT product_id, quantity FROM order_items WHERE order_id = $orderId");

if (!$orderItems || mysqli_num_rows($orderItems) == 0) {
    echo "No items found.";
    exit;
}

// Restore stock
while ($item = mysqli_fetch_assoc($orderItems)) {
    $productId = $item['product_id'];
    $quantity = $item['quantity'];

    mysqli_query($connect, "UPDATE products SET stock_quantity = stock_quantity + $quantity WHERE product_id = '$productId'");
}

// Update order status
mysqli_query($connect, "UPDATE orders SET status = 6 WHERE order_id = $orderId");

echo "success";