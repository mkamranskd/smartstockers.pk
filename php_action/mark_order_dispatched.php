<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    error_log("Raw POST data: " . print_r($_POST, true));
    $data = json_decode(file_get_contents("php://input"), true);

$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;

error_log("Decoded JSON - Order ID: $order_id, Product ID: $product_id, Quantity: $quantity");

    // Start transaction
    $connect->begin_transaction();
    
    try {
        // Check if the product exists and get its current stock
        echo "Checking stock for product: $product_id";
        $checkStockQuery = "SELECT stock_quantity FROM products WHERE product_id = $product_id";
        $result = $connect->query($checkStockQuery);
        
        if ($result->num_rows === 0) {
            throw new Exception("Product not found in database.");
        }

        $row = $result->fetch_assoc();
        $stock_quantity = $row['stock_quantity'];

        if ($stock_quantity < $quantity) {
            throw new Exception("--$product_id-- : Not enough stock available. Stock: $stock_quantity, Requested: $quantity");
        }

        // Update stock for the specific product
        echo "--$product_id-- : Stock update successful. Stock: $stock_quantity,  Requested: $quantity";
        $updateStockQuery = "UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE product_id = $product_id";
        $connect->query($updateStockQuery);

        if ($connect->affected_rows === 0) {
            throw new Exception("Stock update failed. No rows affected.");
        }

        // Change order status to dispatched (2)
        $updateOrderQuery = "UPDATE orders SET status = 2 WHERE id = $order_id";
        $connect->query($updateOrderQuery);

        // Commit transaction
        $connect->commit();
        echo "    success";
    } catch (Exception $e) {
        $connect->rollback();
        error_log("Error: " . $e->getMessage());
        echo "error: " . $e->getMessage();
    }

    $connect->close();
} else {
    echo "invalid_request";
}
?>