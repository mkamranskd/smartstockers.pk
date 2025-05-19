<?php
require_once '../php_action/db_connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];

    // Check order status
    $getStatus = mysqli_query($connect, "SELECT order_status FROM orders WHERE order_id = '$orderId'");
    $order = mysqli_fetch_assoc($getStatus);

    if (!$order) {
        echo "Order not found.";
        exit;
    }

    $status = $order['order_status'];


        $getItems = mysqli_query($connect, "SELECT product_id, quantity FROM order_items WHERE order_id = '$orderId'");
        while ($item = mysqli_fetch_assoc($getItems)) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            $updateStock = "UPDATE products SET stock_quantity = stock_quantity + $quantity WHERE product_id = '$productId'";
            mysqli_query($connect, $updateStock);
        }
    

    // Delete from order_items first due to foreign key constraint
    $deleteItems = mysqli_query($connect, "DELETE FROM order_items WHERE order_id = '$orderId'");
    $deleteOrder = mysqli_query($connect, "DELETE FROM orders WHERE order_id = '$orderId'");

    if ($deleteOrder && $deleteItems) {
        echo "Order and items deleted successfully.";
    } else {
        echo "Error deleting order.";
    }
} else {
    echo "Invalid request.";
}
?>